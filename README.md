# 🚴 Electric Bikes Affiliate & Deal Finder Engine

An automated, multi-source UK & Global electric bike deals aggregator and price drop finder. It pulls structured product data directly from live brand and retailer JSON feeds without relying on brittle HTML scrapers or requiring complex affiliate account setup upfront.

---

## 📁 Project Structure & File Map

| File / Folder | Purpose |
| :--- | :--- |
| **`fetch_deals.js`** | **Core Aggregator Engine** — Scans open JSON feeds from multiple stores, strips out non-bike accessories, calculates discount percentages and Deal Scores, and outputs `deals.json` and `deals-data.js`. |
| **`deals.json`** | **Active Deals Database** — The normalized JSON payload containing all verified, discounted e-bikes, specs, prices, and links. |
| **`deals-data.js`** | **Offline / Static Fallback** — Bundles the deals into a global JS variable so the frontend works both via web server and direct file opening. |
| **`index.html`** | **Deals Hub Frontend** — Responsive, dark-mode dashboard with instant search, category filters (Mega Deals, Budget Steals, Mountain, Folding, Cargo), and Deal Score sorting. |
| **`serve.js`** | **Local Preview Server** — Lightweight zero-dependency HTTP server to preview the dashboard at `http://localhost:3000`. |
| **`.github/workflows/daily_deals.yml`** | **Automated Cloud Cron** — GitHub Action that runs `fetch_deals.js` twice daily (06:00 & 18:00 UTC) and auto-commits price updates. |

---

## ⚡ How It Works

### 1. Data Ingestion (No Fragile HTML Scraping)
Instead of scraping HTML classes that break on site redesigns, the engine connects to public `/products.json` feeds from authorized brands:
* **E-BikeShop.co.uk** (Premium: Scott, Orbea, Haibike, Cube)
* **Engwe UK** (Fat tyre & commuter folding e-bikes)
* **Pure Electric** (City commuters & e-scooters)
* **Tenways** (Belt-drive urban e-bikes)
* **Velotric** (Step-thru, commuter & utility e-bikes)

### 2. Cleaning & Noise Removal
The engine automatically filters out low-value items and accessories:
* Rejects any item under £350.
* Rejects non-bike keywords (`battery`, `charger`, `lock`, `helmet`, `rack`, `pedal`, `brake`, etc.).
* Verifies `compare_at_price > price` with a minimum 5% real markdown.

### 3. Deal Scoring Formula
Deals are ranked using a weighted deal score to balance high % discounts with big cash savings:
$$\text{Deal Score} = (\text{Discount \%} \times 0.5) + \left(\frac{\text{Cash Savings (\pounds)}}{15} \times 0.5\right)$$

---

## 💰 How to Add Your Affiliate IDs (Automatic Monetization)

You only need **one ID per brand/network**. You never need to generate links one-by-one.

Open `fetch_deals.js` and add an `affiliateWrapper` function to any store in the `SOURCES` array:

```javascript
// Example: Direct Brand Affiliate (Engwe / Velotric / Tenways)
{
  name: 'Engwe UK',
  retailer: 'Engwe UK Official',
  endpoint: 'https://engwe-bikes-uk.com/products.json?limit=250',
  baseUrl: 'https://engwe-bikes-uk.com/products/',
  // Automatically attaches your affiliate tag to EVERY Engwe bike:
  affiliateWrapper: (url) => `${url}?ref=YOUR_ENGWE_TAG`
}

// Example: Awin Network (Halfords / Tredz / Pure Electric)
{
  name: 'Pure Electric',
  retailer: 'Pure Electric',
  endpoint: 'https://www.pureelectric.com/products.json?limit=250',
  baseUrl: 'https://www.pureelectric.com/products/',
  // Wraps link through your Awin publisher account:
  affiliateWrapper: (url) => `https://www.awin1.com/cread.php?awinmid=MERCHANT_ID&awinaffid=YOUR_AWIN_ID&ued=${encodeURIComponent(url)}`
}
```

---

## 🚀 Running Locally

### 1. Fetch Latest Live Deals:
```bash
node fetch_deals.js
```

### 2. Start the Preview Server:
```bash
node serve.js
# Open http://localhost:3000 in your browser
```

---

## 🤖 GitHub Automation (Cloud Cron)
The repository is configured to run automatically via GitHub Actions:
* **Schedule:** Every day at **06:00 UTC** and **18:00 UTC**.
* **Manual Refresh:** Go to the **Actions** tab on GitHub $\rightarrow$ click **"Daily Multi-Source E-Bike Deals Refresh"** $\rightarrow$ click **"Run workflow"**.

---

## 🔗 Affiliate Deep-Linking Architecture & Retailer Guide

### Core Tracking Architecture (Awin Network)
All Awin-monetized retailers use your registered Publisher ID: **`3040709`**.
Links are dynamically constructed using the standard Awin redirect wrapper:
```text
https://www.awin1.com/cread.php?awinmid=[MERCHANT_ID]&awinaffid=3040709&clickref=dealspage&ued=[ENCODED_DESTINATION_URL]
```

---

### ⚠️ Retailer-by-Retailer URL Rules & 404 Prevention

To avoid 404 errors, wrong landing pages, or losing user intent, follow these specific retailer rules:

#### 1. 🇬🇧 **Ribble Cycles** (Awin MID: `5923`)
* **The 404 / Wrong Page Issue:**
  * Ribble runs on a custom Magento platform with strict routing. 
  * Passing legacy model slugs (e.g. `/ribble-endurance-al-e/` or `/ribble-hybrid-al-e/`) returns an immediate **404 Not Found**.
  * Passing top-level model overview pages (e.g. `/allroad-sl-r-e/`) lands the user on a 3D marketing showroom rather than a direct bike purchase page with a "Buy Now" / "Choose Size" button.
* **The Required Alteration:**
  * Always use the exact **`/build-v2/`** direct-purchase sub-route with the colour and groupset slug:
    ```text
    https://www.ribblecycles.co.uk/ribble-allroad-sl-r-e-pro/build-v2/?colour=midnight-blue
    https://www.ribblecycles.co.uk/ribble-allroad-e-al-105/build-v2/?colour=midnight-metallic-blue
    https://www.ribblecycles.co.uk/ribble-cgr-e-al-105-v3/build-v2/?colour=black
    https://www.ribblecycles.co.uk/ribble-cgr-e-carbon-x-105-di2-v3/build-v2/?colour=champagne-green
    ```

#### 2. ⚡ **Leisure Lakes Bikes** (Awin MID: `6914`)
* **The 404 / Wrong Item Issue:**
  * Leisure Lakes Bikes appends a unique numeric product ID at the end of every URL (e.g., `__411417` or `__433857`).
  * If you guess the URL or use a generic search snippet, an incorrect ID will either 404 or redirect to an unrelated accessory (such as a CamelBak backpack or helmet).
* **The Required Alteration:**
  * Always copy the exact live product URL including the trailing `__[product_id]` suffix directly from their live electric bikes catalog:
    ```text
    https://www.leisurelakesbikes.com/bikes/electric-bikes/merida-eone-sixty-7000-electric-bike-goldsilver__411417
    https://www.leisurelakesbikes.com/bikes/electric-bikes/mondraker-level-r-electric-bike-2026-chili-redsuper-black__433857
    ```

#### 3. 🚴 **Engwe UK** (Awin MID: `65774`)
* **The Homepage Redirect Issue:**
  * Static Awin shortlinks (e.g. `tidd.ly` links) only point to the store homepage (`uk.engwe.com/`).
  * If used for every deal card, users clicking on an *L20 3.0 Boost* or *EP-2 Pro* are dumped on the homepage rather than the specific bike page.
* **The Required Alteration:**
  * In `fetch_deals.js`, we dynamically append the individual product handle (`/products/engwe-[model-slug]`) into the `&ued=` parameter of the Awin link so that every deal card deep-links to that exact bike:
    ```text
    https://www.awin1.com/cread.php?awinmid=65774&awinaffid=3040709&clickref=dealspage&ued=https%3A%2F%2Fengwe-bikes-uk.com%2Fproducts%2Fengwe-l20-3-0-boost
    ```

#### 4. 🛞 **Pedal Go UK** (Awin MID: `114770`)
* **Stock & Inventory Filtering:**
  * Pedal Go offers high-commission e-cargo bikes (11.11% commission).
  * Our automated aggregator scans `https://pedalgo.co.uk/products.json` and verifies `available: true` across all variants before adding them to the feed, preventing out-of-stock bikes (like Emotorad) from cluttering the deals board.

---

### Version 2.3.3 (Current Production Release)
* **⚡ Eradicated Scroll Page Flashing / Flicker**:
  * Fixed asynchronous re-rendering in `fetchTopDeals()`: when remote feeds match the bundled 138 deals, the DOM is no longer wiped and rebuilt, stopping the white flash/blink on scroll.
  * Added explicit `width="360" height="220"` and `decoding="async"` to every card image, eliminating layout recalculations.
  * Added `skip-lazy` and `data-no-lazy="1"` attributes to bypass aggressive LiteSpeed Cache LQIP placeholder transitions.
* **🛡️ Bulletproof BFCache & Theme Image Sizing (Giant Image Bug Fix)**:
  * Overcame WordPress theme `.entry-content img { height: auto !important; }` overrides with multi-layer high-specificity CSS: `#rgb-deal-finder-root .rgb-card-img`, `.entry-content #rgb-deal-finder-root img.rgb-card-img`.
  * Hard-locked image containers to `height: 220px !important; max-height: 220px !important; object-fit: contain !important;` so raw 2000px Shopify images can never blow out the card grid when returning from another page.
  * Added `pageshow` event handler for seamless browser Back/Forward Cache (BFCache) layout restoration.

### Version 2.3.2
* **📊 2x2 Deal Specification Grid Overhaul**:
  * Restructured specifications display on every deal card: **Category**, **Motor**, **Battery**, and **UK Legal Status** (`✅ Road Legal` vs `⚠️ Off-Road`).
  * Replaced speculative defaults with strict verified spec extractors.
* **🔍 Google Search Console & Indexability Compliance**:
  * Removed legacy `noindex` directives from `reight-deals-finder.php`.
  * Verified passing status in Google Search Console URL Inspection tool.
  * Enforced `rel="sponsored nofollow noopener"` on all outbound affiliate links to maintain full Google spam-policy compliance.

### Version 2.3.1 & 2.3.0
* **📈 Crawler Multi-Page Pagination Expansion**:
  * Upgraded `fetch_deals.js` to paginate through retailer JSON feeds up to 4 pages per merchant.
  * Verified deals surged from 43 to 138 active, in-stock e-bike bargains.
* **🛞 Rescued Fat Tyre E-Bikes**: Refined negative keyword filters to ensure legitimate 4-inch fat-tyre electric bikes are not falsely discarded.
* **🛡️ Outage & Failure Protection**: Added request timeouts and cached deal preservation so upstream retailer API downtime cannot wipe the active deals feed.



