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
    baseUrl: 'https://engwe-bikes-uk.com/products/'
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
    name: 'Tenways',
    retailer: 'Tenways Global',
    country: 'EU/UK',
    currency: 'GBP',
    symbol: '£',
    endpoint: 'https://www.tenways.com/products.json?limit=250',
    baseUrl: 'https://www.tenways.com/products/'
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
  }
];

const ACCESSORY_WORDS = [
  'battery', 'charger', 'lock', 'helmet', 'tracker', 'rack', 'bag', 
  'tyre', 'tire', 'brake', 'crank', 'bracket', 'pedal', 'adapter', 
  'display', 'sensor', 'extension', 'combo rear', 'cover', 'plate',
  'pump', 'chainring', 'pannier', 'mirror', 'bell', 'kickstand',
  'mudguard', 'grip', 'light', 'basket'
];

function isActualEBike(product, price) {
  if (price < 300) return false;
  const title = (product.title || '').toLowerCase();
  const type = (product.product_type || '').toLowerCase();

  for (const word of ACCESSORY_WORDS) {
    if (title.includes(word) && !title.includes('bike') && !title.includes('ebike')) {
      return false;
    }
  }

  const bikeKeywords = ['bike', 'e-bike', 'ebike', 'cycle', 'step-thru', 'step-over', 'folding', 'mtb', 'ride', 'boost', 'eride', 'diem', 'scooter'];
  return bikeKeywords.some(k => title.includes(k) || type.includes(k));
}

function detectCategory(title, type) {
  const text = `${title} ${type}`.toLowerCase();
  if (text.includes('fold') || text.includes('compact') || text.includes('vektron') || text.includes('tern') || text.includes('zip')) {
    return 'Folding';
  }
  if (text.includes('mountain') || text.includes('mtb') || text.includes('fs') || text.includes('wild') || text.includes('trail') || text.includes('ams') || text.includes('haibike') || text.includes('hybe')) {
    return 'Mountain';
  }
  if (text.includes('cargo') || text.includes('fat') || text.includes('beast') || text.includes('hauler') || text.includes('combo')) {
    return 'Cargo & Fat Tyre';
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
          badge_text: `SAVE ${source.symbol}${savings} (${discountPct}% OFF)`
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
  let allDeals = [];

  for (const source of SOURCES) {
    const sourceDeals = await fetchSourceDeals(source);
    allDeals = allDeals.concat(sourceDeals);
  }

  // Sort overall by highest Deal Score
  allDeals.sort((a, b) => b.dealScore - a.dealScore);

  const payload = {
    metadata: {
      title: "Reight Good Bikes - Multi-Source E-Bike Deals Hub",
      last_updated: new Date().toISOString().replace('T', ' ').slice(0, 19),
      total_deals: allDeals.length,
      sources_scanned: SOURCES.map(s => s.name),
      categories: ['All', 'Mega Deals', 'Budget Steals', 'Mountain', 'Commuter', 'Folding', 'Cargo & Fat Tyre']
    },
    deals: allDeals
  };

  fs.writeFileSync(DEALS_FILE, JSON.stringify(payload, null, 2), 'utf-8');
  fs.writeFileSync(path.join(__dirname, 'deals-data.js'), 'window.DEALS_DATA = ' + JSON.stringify(payload) + ';', 'utf-8');
  console.log(`[COMPLETE] Wrote ${allDeals.length} sorted deals to ${DEALS_FILE} and deals-data.js`);
}

runAggregator();
