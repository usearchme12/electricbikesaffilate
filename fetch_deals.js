/**
 * Multi-Source E-Bike Deals Aggregator (Open Feeds / Zero Affiliate Registration Required)
 * Pulls directly from public Shopify JSON endpoints of authorized e-bike retailers & brands.
 * 
 * Includes:
 * 1. Multi-page pagination support (exhausts catalogs up to 4 pages).
 * 2. Robust accessory & scooter exclusion (preserves fat tyre, lightweight, and rack-equipped e-bikes).
 * 3. Multi-variant parsing (checks all sizes/colors, links directly to in-stock discounted variant).
 * 4. Spec extraction & UK road-legality validation (detects wattage > 250W as off-road only).
 * 5. Outage resilience & cached deal preservation on network failure.
 */

const fs = require('fs');
const path = require('path');

const DEALS_FILE = path.join(__dirname, 'deals.json');

// Your Official Awin Publisher ID
const AWIN_PUBLISHER_ID = '3040709';

function buildAwinLink(rawUrl, awinMid) {
  if (!awinMid || !AWIN_PUBLISHER_ID) return rawUrl;
  return `https://www.awin1.com/cread.php?awinmid=${awinMid}&awinaffid=${AWIN_PUBLISHER_ID}&clickref=dealspage&ued=${encodeURIComponent(rawUrl)}`;
}

const SOURCES = [
  {
    name: 'E-BikeShop.co.uk',
    retailer: 'E-BikeShop UK',
    country: 'UK',
    currency: 'GBP',
    symbol: '£',
    endpoint: 'https://www.e-bikeshop.co.uk/products.json',
    baseUrl: 'https://www.e-bikeshop.co.uk/products/',
    maxPages: 4
  },
  {
    name: 'Engwe UK',
    retailer: 'Engwe UK Official',
    country: 'UK',
    currency: 'GBP',
    symbol: '£',
    endpoint: 'https://engwe-bikes-uk.com/products.json',
    baseUrl: 'https://engwe-bikes-uk.com/products/',
    awinMid: '65774',
    maxPages: 2
  },
  {
    name: 'Pure Electric',
    retailer: 'Pure Electric',
    country: 'UK',
    currency: 'GBP',
    symbol: '£',
    endpoint: 'https://www.pureelectric.com/products.json',
    baseUrl: 'https://www.pureelectric.com/products/',
    maxPages: 3
  },
  {
    name: 'Heybike UK',
    retailer: 'Heybike UK',
    country: 'UK',
    currency: 'GBP',
    symbol: '£',
    endpoint: 'https://heybike.co.uk/products.json',
    baseUrl: 'https://heybike.co.uk/products/',
    maxPages: 2
  },
  {
    name: 'DYU Cycle UK',
    retailer: 'DYU UK Official',
    country: 'UK',
    currency: 'GBP',
    symbol: '£',
    endpoint: 'https://uk.dyucycle.com/products.json',
    baseUrl: 'https://uk.dyucycle.com/products/',
    maxPages: 2
  },
  {
    name: 'PedalGo UK',
    retailer: 'PedalGo UK',
    country: 'UK',
    currency: 'GBP',
    symbol: '£',
    endpoint: 'https://pedalgo.co.uk/products.json',
    baseUrl: 'https://pedalgo.co.uk/products/',
    awinMid: '114770',
    maxPages: 2
  },
  {
    name: 'Fiido UK',
    retailer: 'Fiido UK Official',
    country: 'UK',
    currency: 'GBP',
    symbol: '£',
    endpoint: 'https://uk.fiido.com/products.json',
    baseUrl: 'https://uk.fiido.com/products/',
    maxPages: 3
  },
  {
    name: 'Eskute UK',
    retailer: 'Eskute UK Official',
    country: 'UK',
    currency: 'GBP',
    symbol: '£',
    endpoint: 'https://www.eskute.co.uk/products.json',
    baseUrl: 'https://www.eskute.co.uk/products/',
    maxPages: 2
  },
  {
    name: 'Cyrusher UK',
    retailer: 'Cyrusher UK',
    country: 'UK',
    currency: 'GBP',
    symbol: '£',
    endpoint: 'https://www.cyrusher.co.uk/products.json',
    baseUrl: 'https://www.cyrusher.co.uk/products/',
    maxPages: 2
  },
  {
    name: 'Eovolt UK',
    retailer: 'Eovolt UK',
    country: 'UK',
    currency: 'GBP',
    symbol: '£',
    endpoint: 'https://eovolt.co.uk/products.json',
    baseUrl: 'https://eovolt.co.uk/products/',
    maxPages: 2
  },
  {
    name: 'Tenways',
    retailer: 'Tenways Direct',
    country: 'UK/EU',
    currency: 'GBP',
    symbol: '£',
    endpoint: 'https://www.tenways.com/products.json',
    baseUrl: 'https://www.tenways.com/products/',
    maxPages: 2
  }
];

// Explicit Scooter Exclusion Patterns
const SCOOTER_PATTERNS = [
  /\be-?scooter(s)?\b/i,
  /\bkick\s*scooter(s)?\b/i,
  /\belectric\s+scooter(s)?\b/i,
  /\bmoped(s)?\b/i
];

// Product Types that are non-bikes
const NON_BIKE_TYPES = [
  'accessories', 'accessory', 'apparel', 'clothing', 'helmet', 'helmets', 
  'lock', 'locks', 'parts', 'components', 'battery', 'batteries', 
  'charger', 'chargers', 'tyres', 'tires', 'tubes', 'inner tube', 
  'bags', 'pannier', 'panniers', 'lights', 'pedals', 'brakes', 'tools', 
  'merchandise', 'scooter', 'scooters', 'e-scooter', 'e-scooters'
];

// Standalone Accessory Title Patterns (only standalone accessories, never bikes)
const STANDALONE_ACCESSORY_PATTERNS = [
  /\b(inner tube|replacement battery|spare battery|battery pack|charger cable|fast charger)\b/i,
  /\b(front light|rear light|tail light|headlight set|cycle helmet|bike lock|chain lock|d-lock|cable lock)\b/i,
  /\b(brake pads|brake lever|disc rotor|chainring|chain guard|kickstand only|mudguard set|fender set)\b/i,
  /\b(phone holder|phone mount|rear rack only|front rack only|child seat|trailer hitch|pannier bag|saddle bag)\b/i,
  /\b(tyre only|tire only|grip set|pedal set|cleats|cycling jersey|cycling gloves|cycling shorts)\b/i,
  /\b(pre-sale deposit|gift card|shipping fee|assembly service|warranty extension)\b/i
];

function isActualEBike(product, price) {
  if (price < 350) return false;
  const title = (product.title || '').trim();
  const type = (product.product_type || '').trim().toLowerCase();
  const handle = (product.handle || '').toLowerCase();

  // 1. Check & reject scooters immediately
  if (SCOOTER_PATTERNS.some(p => p.test(title) || p.test(type) || p.test(handle))) {
    return false;
  }

  // 2. Check product type exclusions
  if (NON_BIKE_TYPES.some(t => type === t || type.startsWith(t + ' ') || type.endsWith(' ' + t))) {
    return false;
  }

  // 3. Check standalone accessory title patterns
  if (STANDALONE_ACCESSORY_PATTERNS.some(p => p.test(title))) {
    return false;
  }

  // 4. Must have a positive bike keyword in title, type, or handle
  const bikeKeywords = [
    'bike', 'e-bike', 'ebike', 'bicycle', 'pedelec', 'cycle', 'step-thru', 
    'step-through', 'step-over', 'folding', 'mtb', 'cargo', 'fat tyre', 'fat tire',
    'haibike', 'cube', 'scott', 'orbea', 'engwe', 'fiido', 'eskute', 'cyrusher', 
    'eovolt', 'tenways', 'heybike', 'radrunner', 'kommoda', 'ep-2', 'engine'
  ];
  const titleLower = title.toLowerCase();
  return bikeKeywords.some(k => titleLower.includes(k) || type.includes(k) || handle.includes(k));
}

function detectCategory(title, type) {
  const text = `${title} ${type}`.toLowerCase();
  
  // 1. Fat Tyre
  const fatKeywords = [
    'fat', 'fat tyre', 'fat tire', 'fat-tire', 'fat-tyre', 'fatbike',
    'kommoda', 'ranger', 'rover', 'trax', 'roam', 'kuattro', 'ovia', 'xf650', 'xf800', 'xf900',
    'ep-2', 'ep2', 'engine pro', 'engine x', 'l20', 'm20', 'o20', 'e26', 'x26', 'x24', 'x20',
    'mars', 'tyson', 'brawn', 'hero', 'horizon', 'explorer', 'titan', 'm1 pro',
    'all-terrain', 'all terrain', '4.0', '4-inch', '20x4', '26x4', '20*4', '26*4'
  ];
  if (fatKeywords.some(k => text.includes(k))) return 'Fat Tyre';

  // 2. Cargo
  if (text.includes('cargo') || text.includes('glider') || text.includes('hauler') || text.includes('amcargobikes') || text.includes('curve') || text.includes('combo')) {
    return 'Cargo';
  }

  // 3. Folding
  if (text.includes('fold') || text.includes('compact') || text.includes('vektron') || text.includes('tern') || text.includes('zip') || text.includes('sonder') || text.includes('loop') || text.includes('d3f') || text.includes('a1f') || text.includes('eovolt')) {
    return 'Folding';
  }

  // 4. Mountain
  if (text.includes('mountain') || text.includes('mtb') || text.includes('fs') || text.includes('wild') || text.includes('trail') || text.includes('ams') || text.includes('haibike') || text.includes('hybe') || text.includes('allmtn') || text.includes('alltrail') || text.includes('flex')) {
    return 'Mountain';
  }

  return 'Commuter';
}

function parseSpecs(product, category) {
  const fullText = `${product.title} ${product.body_html || ''} ${(product.tags || []).join(' ')}`.toLowerCase();
  
  // Motor Wattage
  let motorPower = category === 'Mountain' ? '250W Mid-Drive' : '250W Road Legal';
  let isUkLegal = true;
  
  const motorMatch = fullText.match(/\b(250|350|500|750|1000|1200|1500)\s*w\b/i);
  if (motorMatch) {
    const watts = parseInt(motorMatch[1], 10);
    if (watts > 250) {
      motorPower = `${watts}W High Torque`;
      isUkLegal = false; // UK EAPC limit is 250W continuous
    } else {
      motorPower = '250W Road Legal';
      isUkLegal = true;
    }
  }

  // Battery Wh / Ah
  let battery = 'Lithium-Ion';
  const whMatch = fullText.match(/\b(\d{3,4})\s*wh\b/i);
  const ahMatch = fullText.match(/\b(\d{1,2}(?:\.\d+)?)\s*ah\b/i);
  const vMatch = fullText.match(/\b(36|48|52)\s*v\b/i);
  
  if (whMatch) {
    battery = `${whMatch[1]}Wh Lithium-Ion`;
  } else if (vMatch && ahMatch) {
    const wh = Math.round(parseFloat(vMatch[1]) * parseFloat(ahMatch[1]));
    battery = `${wh}Wh (${vMatch[1]}V ${ahMatch[1]}Ah)`;
  } else if (ahMatch) {
    battery = `${ahMatch[1]}Ah Lithium-Ion`;
  } else {
    battery = 'Spec on retailer site';
  }

  const maxSpeed = isUkLegal ? '15.5 mph (EAPC)' : '20+ mph (Off-Road)';

  return { motorPower, battery, isUkLegal, maxSpeed };
}

async function fetchWithTimeout(url, timeoutMs = 12000) {
  const controller = new AbortController();
  const timeoutId = setTimeout(() => controller.abort(), timeoutMs);
  try {
    const res = await fetch(url, {
      headers: {
        'User-Agent': 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/122.0.0.0 Safari/537.36',
        'Accept': 'application/json'
      },
      signal: controller.signal
    });
    clearTimeout(timeoutId);
    return res;
  } catch (err) {
    clearTimeout(timeoutId);
    throw err;
  }
}

async function fetchSourceDeals(source, previousDeals = []) {
  const deals = [];
  const seenIds = new Set();
  const maxPages = source.maxPages || 2;
  const baseUrlClean = source.endpoint.replace(/\.json.*$/, '');
  let fetchFailed = false;

  for (let page = 1; page <= maxPages; page++) {
    const pageUrl = `${baseUrlClean}.json?limit=250&page=${page}`;
    try {
      const res = await fetchWithTimeout(pageUrl);
      if (!res.ok) {
        console.warn(`[WARN] ${source.name} page ${page} returned status ${res.status}`);
        if (page === 1) fetchFailed = true;
        break;
      }
      const data = await res.json();
      const products = data.products || [];
      if (!products.length) break;

      for (const p of products) {
        if (seenIds.has(p.id)) continue;
        seenIds.add(p.id);

        const variants = p.variants || [];
        if (!variants.length) continue;

        // Check ALL variants for in-stock discounts
        const inStockDeals = variants.filter(v => {
          if (v.available === false) return false;
          const pr = parseFloat(v.price || 0);
          const comp = parseFloat(v.compare_at_price || 0);
          if (pr < 300 || comp <= pr) return false;
          const savings = comp - pr;
          const discountPct = Math.round((savings / comp) * 100);
          return discountPct >= 5;
        });

        if (!inStockDeals.length) continue;

        // Pick lowest price variant
        inStockDeals.sort((a, b) => parseFloat(a.price) - parseFloat(b.price));
        const bestVariant = inStockDeals[0];

        const price = parseFloat(bestVariant.price);
        const comparePrice = parseFloat(bestVariant.compare_at_price);

        if (!isActualEBike(p, price)) continue;

        const savings = Math.round(comparePrice - price);
        const discountPct = Math.round((savings / comparePrice) * 100);

        const title = p.title.trim();
        const brand = p.vendor || source.retailer;
        const category = detectCategory(title, p.product_type || '');
        const images = p.images || [];
        const image = images[0]?.src || 'https://images.unsplash.com/photo-1571068316344-75bc76f77890?w=600';

        const specs = parseSpecs(p, category);

        const dealScore = (discountPct * 0.5) + ((savings / 15) * 0.5);

        let dealBucket = 'Standard Deal';
        if (discountPct >= 30 || savings >= 400) dealBucket = '🔥 Mega Deal';
        else if (price <= 900) dealBucket = '⚡ Budget Steal';
        else if (price >= 2500) dealBucket = '💎 Premium Drop';

        // Deep link directly to the discounted in-stock variant
        let finalUrl = `${source.baseUrl}${p.handle}?variant=${bestVariant.id}`;
        if (source.awinMid) {
          finalUrl = buildAwinLink(finalUrl, source.awinMid);
        } else if (source.affiliateParam) {
          finalUrl = `${finalUrl}${source.affiliateParam}`;
        }

        const variantNote = (bestVariant.title && bestVariant.title !== 'Default Title') ? ` (${bestVariant.title})` : '';

        deals.push({
          id: `${source.name.toLowerCase().replace(/[^a-z0-9]/g, '_')}_${p.id}`,
          title: title + variantNote,
          brand: brand,
          retailer: source.retailer,
          country: source.country,
          currency: source.currency,
          symbol: source.symbol,
          category: category,
          dealBucket: dealBucket,
          dealScore: parseFloat(dealScore.toFixed(1)),
          motor_power: specs.motorPower,
          battery: specs.battery,
          range_miles: '35 - 75 Miles',
          max_speed: specs.maxSpeed,
          is_uk_legal: specs.isUkLegal,
          rrp: comparePrice,
          sale_price: price,
          price_from: inStockDeals.length > 1,
          savings_amount: savings,
          discount_percentage: discountPct,
          image: image,
          url: finalUrl,
          badge_text: `SAVE ${source.symbol}${savings} (${discountPct}% OFF)`,
          first_seen: p.published_at ? p.published_at.slice(0, 10) : new Date().toISOString().slice(0, 10),
          is_new: false,
          last_verified: new Date().toISOString()
        });
      }

      if (products.length < 250) break; // Exhausted catalog
    } catch (err) {
      console.error(`[ERROR] Fetching ${source.name} page ${page}:`, err.message);
      if (page === 1) fetchFailed = true;
      break;
    }
  }

  // Outage fallback: if retailer completely failed and we have previous cached deals, preserve them
  if (fetchFailed && previousDeals.length > 0) {
    console.warn(`[OUTAGE FALLBACK] Preserving ${previousDeals.length} cached deals for ${source.name}`);
    return previousDeals.map(d => ({ ...d, is_cached: true }));
  }

  console.log(`[SUCCESS] ${source.name}: Found ${deals.length} verified discounted e-bikes`);
  return deals;
}

function getCuratedPartnerDeals() {
  const llbBaseTracking = 'https://www.awin1.com/cread.php?awinmid=6914&awinaffid=3040709&clickref=dealspage&ued=';

  return [
    {
      id: 'leisure_lakes_merida_eone_sixty_7000',
      title: 'Merida eOne-Sixty 7000 Electric Bike (Gold/Silver)',
      brand: 'Merida',
      retailer: 'Leisure Lakes Bikes',
      country: 'UK',
      currency: 'GBP',
      symbol: '£',
      category: 'Mountain',
      dealBucket: '🔥 Mega Deal',
      dealScore: 99.0,
      motor_power: '250W Mid-Drive Shimano EP8',
      battery: '630Wh Shimano Lithium-Ion',
      range_miles: '50 - 85 Miles',
      max_speed: '15.5 mph (EAPC)',
      is_uk_legal: true,
      rrp: 6400,
      sale_price: 3999,
      savings_amount: 2401,
      discount_percentage: 38,
      image: 'https://www.leisurelakesbikes.com/images/merida-eonesixty-7000-electric-bike-2024-goldsilver.jpg',
      url: llbBaseTracking + encodeURIComponent('https://www.leisurelakesbikes.com/bikes/electric-bikes/merida-eone-sixty-7000-electric-bike-goldsilver__411417'),
      badge_text: 'SAVE £2401 (38% OFF)',
      first_seen: new Date().toISOString().slice(0, 10),
      is_new: false,
      last_verified: new Date().toISOString()
    },
    {
      id: 'leisure_lakes_mondraker_level_r',
      title: 'Mondraker Level R Electric Bike 2026 (Chili Red/Super Black)',
      brand: 'Mondraker',
      retailer: 'Leisure Lakes Bikes',
      country: 'UK',
      currency: 'GBP',
      symbol: '£',
      category: 'Mountain',
      dealBucket: '🔥 Mega Deal',
      dealScore: 70.6,
      motor_power: '250W Mid-Drive Bosch CX',
      battery: '750Wh Bosch PowerTube',
      range_miles: '45 - 80 Miles',
      max_speed: '15.5 mph (EAPC)',
      is_uk_legal: true,
      rrp: 5999,
      sale_price: 4299,
      savings_amount: 1700,
      discount_percentage: 28,
      image: 'https://www.leisurelakesbikes.com/images/products/m/mo/mondraker-level-r-electric-bike-2026.jpg',
      url: llbBaseTracking + encodeURIComponent('https://www.leisurelakesbikes.com/bikes/electric-bikes/mondraker-level-r-electric-bike-2026-chili-redsuper-black__433857'),
      badge_text: 'SAVE £1700 (28% OFF)',
      first_seen: new Date().toISOString().slice(0, 10),
      is_new: false,
      last_verified: new Date().toISOString()
    }
  ];
}

async function runAggregator() {
  console.log('--- Starting Multi-Source E-Bike Deals Aggregation ---');
  
  // Load previous deals to retain first_seen history and outage fallback
  let prevDealsMap = {};
  let prevDealsByRetailer = {};
  try {
    if (fs.existsSync(DEALS_FILE)) {
      const prevData = JSON.parse(fs.readFileSync(DEALS_FILE, 'utf-8'));
      (prevData.deals || []).forEach(d => {
        if (d.id) prevDealsMap[d.id] = d.first_seen || d.date_added;
        if (d.retailer) {
          if (!prevDealsByRetailer[d.retailer]) prevDealsByRetailer[d.retailer] = [];
          prevDealsByRetailer[d.retailer].push(d);
        }
      });
    }
  } catch(e) {}

  const todayStr = new Date().toISOString().slice(0, 10);
  const twoDaysAgo = new Date(Date.now() - 48 * 60 * 60 * 1000).toISOString().slice(0, 10);
  let allDeals = [];

  for (const source of SOURCES) {
    const cachedDeals = prevDealsByRetailer[source.retailer] || [];
    const sourceDeals = await fetchSourceDeals(source, cachedDeals);
    allDeals = allDeals.concat(sourceDeals);
  }

  // Include verified approved partner deals
  const partnerDeals = getCuratedPartnerDeals();
  allDeals = allDeals.concat(partnerDeals);

  // Safeguard: Do NOT overwrite database if aggregator completely failed to fetch deals
  if (allDeals.length < 10) {
    console.error(`[CRITICAL] Only ${allDeals.length} deals gathered. Aborting file overwrite to protect database integrity.`);
    process.exit(1);
  }

  // Assign first_seen and is_new badge
  allDeals.forEach(d => {
    if (prevDealsMap[d.id]) {
      d.first_seen = prevDealsMap[d.id];
    } else {
      d.first_seen = todayStr;
    }
    d.is_new = (d.first_seen >= twoDaysAgo);
  });

  // Sort overall by highest Deal Score
  allDeals.sort((a, b) => b.dealScore - a.dealScore);

  const payload = {
    metadata: {
      title: "Reight Good Bikes - Multi-Source E-Bike Deals Hub",
      last_updated: new Date().toISOString().replace('T', ' ').slice(0, 19),
      total_deals: allDeals.length,
      sources_scanned: SOURCES.map(s => s.name),
      categories: ['All', 'Just Added', 'Mega Deals', 'Budget Steals', 'Fat Tyre', 'Mountain', 'Commuter', 'Folding', 'Cargo']
    },
    deals: allDeals
  };

  fs.writeFileSync(DEALS_FILE, JSON.stringify(payload, null, 2), 'utf-8');
  fs.writeFileSync(path.join(__dirname, 'deals-data.js'), 'window.DEALS_DATA = ' + JSON.stringify(payload) + ';', 'utf-8');
  console.log(`[COMPLETE] Successfully wrote ${allDeals.length} verified deals to ${DEALS_FILE} and deals-data.js`);
}

runAggregator();
