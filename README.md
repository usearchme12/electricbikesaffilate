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
