/**
 * Multi-Source E-Bike Deals Aggregator (Open Feeds / Zero Affiliate Registration Required)
 * Pulls directly from public Shopify JSON endpoints of authorized e-bike retailers & brands.
 */

const fs = require('fs');
const path = require('path');

const DEALS_FILE = path.join(__dirname, 'deals.json');

// Your Official Awin Publisher ID
const AWIN_PUBLISHER_ID = '3040709';

function buildAwinLink(rawUrl, awinMid) {
  if (!awinMid || !AWIN_PUBLISHER_ID) return rawUrl;
  return `https://www.awin1.com/cread.php?awinmid=${awinMid}&awinaffid=${AWIN_PUBLISHER_ID}&ued=${encodeURIComponent(rawUrl)}`;
}

// Impact.com Affiliate Deep Links
// NOTE: Engwe does not support Impact deep linking — all clicks go via the
// homepage tracking link which sets the affiliate cookie. Commission still
// fires on any purchase made after landing.
const ENGWE_AFFILIATE_BASE = 'https://engwecom.pxf.io/c/7627881/2782992/30352';

const SOURCES = [
  {
    name: 'E-BikeShop.co.uk',
    retailer: 'E-BikeShop UK',
    country: 'UK',
    currency: 'GBP',
    symbol: '£',
    endpoint: 'https://www.e-bikeshop.co.uk/products.json?limit=250',
    baseUrl: 'https://www.e-bikeshop.co.uk/products/'
  },
  {
    name: 'Engwe UK',
    retailer: 'Engwe UK Official',
    country: 'UK',
    currency: 'GBP',
    symbol: '£',
    endpoint: 'https://engwe-bikes-uk.com/products.json?limit=250',
    baseUrl: 'https://engwe-bikes-uk.com/products/',
    // ⚠️ No affiliate tracking — Impact doesn't support deep linking for Engwe
    // Linking direct to product until a proper solution is available
  },
  {
    name: 'Pure Electric',
    retailer: 'Pure Electric',
    country: 'UK',
    currency: 'GBP',
    symbol: '£',
    endpoint: 'https://www.pureelectric.com/products.json?limit=250',
    baseUrl: 'https://www.pureelectric.com/products/'
  },
  {
    name: 'Heybike UK',
    retailer: 'Heybike UK',
    country: 'UK',
    currency: 'GBP',
    symbol: '£',
    endpoint: 'https://heybike.co.uk/products.json?limit=250',
    baseUrl: 'https://heybike.co.uk/products/'
  },
  {
    name: 'DYU Cycle UK',
    retailer: 'DYU UK Official',
    country: 'UK',
    currency: 'GBP',
    symbol: '£',
    endpoint: 'https://uk.dyucycle.com/products.json?limit=250',
    baseUrl: 'https://uk.dyucycle.com/products/'
  },
  {
    name: 'PedalGo UK',
    retailer: 'PedalGo UK',
    country: 'UK',
    currency: 'GBP',
    symbol: '£',
    endpoint: 'https://pedalgo.co.uk/products.json?limit=250',
    baseUrl: 'https://pedalgo.co.uk/products/'
  },
  {
    name: 'Fiido UK',
    retailer: 'Fiido UK Official',
    country: 'UK',
    currency: 'GBP',
    symbol: '£',
    endpoint: 'https://uk.fiido.com/products.json?limit=250',
    baseUrl: 'https://uk.fiido.com/products/'
  },
  {
    name: 'Eskute UK',
    retailer: 'Eskute UK Official',
    country: 'UK',
    currency: 'GBP',
    symbol: '£',
    endpoint: 'https://www.eskute.co.uk/products.json?limit=250',
    baseUrl: 'https://www.eskute.co.uk/products/'
  },
  {
    name: 'Cyrusher UK',
    retailer: 'Cyrusher UK',
    country: 'UK',
    currency: 'GBP',
    symbol: '£',
    endpoint: 'https://www.cyrusher.co.uk/products.json?limit=250',
    baseUrl: 'https://www.cyrusher.co.uk/products/'
  },
  {
    name: 'Eovolt UK',
    retailer: 'Eovolt UK',
    country: 'UK',
    currency: 'GBP',
    symbol: '£',
    endpoint: 'https://eovolt.co.uk/products.json?limit=250',
    baseUrl: 'https://eovolt.co.uk/products/'
  },
  {
    name: 'Tenways',
    retailer: 'Tenways Direct',
    country: 'UK/EU',
    currency: 'GBP',
    symbol: '£',
    endpoint: 'https://www.tenways.com/products.json?limit=250',
    baseUrl: 'https://www.tenways.com/products/'
  }
];

const ACCESSORY_WORDS = [
  'battery', 'batteries', 'charger', 'lock', 'helmet', 'tracker', 'rack', 'bag', 'bags',
  'tyre', 'tire', 'brake', 'brakes', 'crank', 'bracket', 'pedal', 'pedals', 'adapter', 
  'display', 'sensor', 'extension', 'combo rear', 'cover', 'plate',
  'pump', 'chainring', 'pannier', 'mirror', 'bell', 'kickstand',
  'mudguard', 'grip', 'grips', 'light', 'lights', 'basket', 'controller',
  'wheel', 'wheels', 'fender', 'fenders', 'seat', 'saddle', 'trailer',
  'throttle', 'motor', 'pre-sale', 'inner tube', 'handlebar', 'stand', 'cable'
];

function isActualEBike(product, price) {
  if (price < 350) return false;
  const title = (product.title || '').toLowerCase();
  const type = (product.product_type || '').toLowerCase();
  const handle = (product.handle || '').toLowerCase();

  // If ANY accessory word matches anywhere in title/type/handle, reject immediately
  for (const word of ACCESSORY_WORDS) {
    if (title.includes(word) || type.includes(word) || handle.includes(word)) {
      return false;
    }
  }

  const bikeKeywords = ['bike', 'e-bike', 'ebike', 'cycle', 'step-thru', 'step-through', 'step-over', 'folding', 'mtb', 'ride', 'boost', 'eride', 'diem', 'scooter', 'haibike', 'cube', 'scott', 'orbea'];
  return bikeKeywords.some(k => title.includes(k) || type.includes(k) || handle.includes(k));
}

function detectCategory(title, type) {
  const text = `${title} ${type}`.toLowerCase();
  
  // 1. Fat Tyre & All-Terrain models & keywords (4.0" / 3.0" wide tyres)
  const fatKeywords = [
    'fat', 'fat tyre', 'fat tire', 'fat-tire', 'fat-tyre', 'fatbike',
    'kommoda', 'ranger', 'rover', 'trax', 'roam', 'kuattro', 'ovia', 'xf650', 'xf800', 'xf900',
    'ep-2', 'ep2', 'engine pro', 'engine x', 'l20', 'm20', 'o20', 'e26', 'x26', 'x24', 'x20',
    'mars', 'tyson', 'brawn', 'hero', 'horizon', 'explorer', 'titan', 'm1 pro',
    'all-terrain', 'all terrain', '4.0', '4-inch', '20x4', '26x4', '20*4', '26*4'
  ];
  if (fatKeywords.some(k => text.includes(k))) {
    return 'Fat Tyre';
  }

  // 2. Cargo
  if (text.includes('cargo') || text.includes('glider') || text.includes('hauler') || text.includes('amcargobikes') || text.includes('curve') || text.includes('combo')) {
    return 'Cargo';
  }

  // 3. Folding
  if (text.includes('fold') || text.includes('compact') || text.includes('vektron') || text.includes('tern') || text.includes('zip') || text.includes('sonder') || text.includes('loop') || text.includes('d3f') || text.includes('a1f')) {
    return 'Folding';
  }

  // 4. Mountain
  if (text.includes('mountain') || text.includes('mtb') || text.includes('fs') || text.includes('wild') || text.includes('trail') || text.includes('ams') || text.includes('haibike') || text.includes('hybe') || text.includes('allmtn') || text.includes('alltrail') || text.includes('flex')) {
    return 'Mountain';
  }

  return 'Commuter';
}

async function fetchSourceDeals(source) {
  const deals = [];
  try {
    const res = await fetch(source.endpoint, {
      headers: {
        'User-Agent': 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/122.0.0.0 Safari/537.36',
        'Accept': 'application/json'
      }
    });

    if (!res.ok) {
      console.warn(`[WARN] ${source.name} returned status ${res.status}`);
      return [];
    }

    const data = await res.json();
    const products = data.products || [];

    for (const p of products) {
      const variants = p.variants || [];
      if (!variants.length) continue;

      const v = variants[0];
      if (v.available === false) continue;

      const price = parseFloat(v.price || 0);
      const comparePrice = parseFloat(v.compare_at_price || 0);

      if (!isActualEBike(p, price)) continue;

      // Check if there is an active discount
      if (comparePrice > price && price >= 300) {
        const savings = Math.round(comparePrice - price);
        const discountPct = Math.round((savings / comparePrice) * 100);

        if (discountPct < 5) continue; // Ignore negligible 1-2% fluctuations

        const title = p.title.trim();
        const brand = p.vendor || source.retailer;
        const category = detectCategory(title, p.product_type || '');
        const images = p.images || [];
        const image = images[0]?.src || 'https://images.unsplash.com/photo-1571068316344-75bc76f77890?w=600';
        
        // Deal Score formula: (% discount * 0.5) + (absolute savings factor * 0.5)
        const dealScore = (discountPct * 0.5) + ((savings / 15) * 0.5);

        let dealBucket = 'Standard Deal';
        if (discountPct >= 30 || savings >= 400) dealBucket = '🔥 Mega Deal';
        else if (price <= 900) dealBucket = '⚡ Budget Steal';
        else if (price >= 2500) dealBucket = '💎 Premium Drop';

        let finalUrl = `${source.baseUrl}${p.handle}`;
        if (source.awinMid) {
          finalUrl = buildAwinLink(finalUrl, source.awinMid);
        } else if (source.affiliateWrapper) {
          finalUrl = source.affiliateWrapper(finalUrl);
        } else if (source.affiliateParam) {
          finalUrl = `${finalUrl}${source.affiliateParam}`;
        }

        deals.push({
          id: `${source.name.toLowerCase().replace(/[^a-z0-9]/g, '_')}_${p.id}`,
          title: title,
          brand: brand,
          retailer: source.retailer,
          country: source.country,
          currency: source.currency,
          symbol: source.symbol,
          category: category,
          dealBucket: dealBucket,
          dealScore: parseFloat(dealScore.toFixed(1)),
          motor_power: category === 'Mountain' ? '250W-500W High Torque' : '250W Road Legal',
          battery: '400Wh - 750Wh Lithium-Ion',
          range_miles: '35 - 75 Miles',
          max_speed: source.country === 'UK' ? '15.5 mph' : '20 mph',
          is_uk_legal: source.country === 'UK',
          rrp: comparePrice,
          sale_price: price,
          savings_amount: savings,
          discount_percentage: discountPct,
          image: image,
          url: finalUrl,
          badge_text: `SAVE ${source.symbol}${savings} (${discountPct}% OFF)`,
          first_seen: p.published_at ? p.published_at.slice(0, 10) : new Date().toISOString().slice(0, 10),
          is_new: false
        });
      }
    }
    console.log(`[SUCCESS] ${source.name}: Found ${deals.length} verified discounted e-bikes`);
  } catch (err) {
    console.error(`[ERROR] Fetching ${source.name}:`, err.message);
  }
  return deals;
}

async function runAggregator() {
  console.log('--- Starting Multi-Source E-Bike Deals Aggregation ---');
  
  // Load previous deals to retain first_seen history
  let prevDealsMap = {};
  try {
    if (fs.existsSync(DEALS_FILE)) {
      const prevData = JSON.parse(fs.readFileSync(DEALS_FILE, 'utf-8'));
      (prevData.deals || []).forEach(d => {
        if (d.id) prevDealsMap[d.id] = d.first_seen || d.date_added;
      });
    }
  } catch(e) {}

  const todayStr = new Date().toISOString().slice(0, 10);
  const twoDaysAgo = new Date(Date.now() - 48 * 60 * 60 * 1000).toISOString().slice(0, 10);
  let allDeals = [];

  for (const source of SOURCES) {
    const sourceDeals = await fetchSourceDeals(source);
    allDeals = allDeals.concat(sourceDeals);
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
  console.log(`[COMPLETE] Wrote ${allDeals.length} sorted deals to ${DEALS_FILE} and deals-data.js`);
}

runAggregator();
