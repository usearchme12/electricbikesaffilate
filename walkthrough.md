# Walkthrough: Version 2.3.5 Upgrades

We have resolved the JavaScript duplicate variable issue, added LiteSpeed optimization bypass tags, and implemented the full suite of lean upgrades across the crawler, backend database, standalone frontend dashboard, and WordPress plugin.

### Version 2.3.5 Hotfix
* **Syntax Error Fixed**: Removed duplicate `const priceFilter` declaration inside `rgbApplyFilters()`.
* **LiteSpeed Optimization Bypass**: Added `data-no-optimize="1" data-no-defer="1"` to the inline shortcode `<script>` tag so LiteSpeed Cache cannot defer or block deals from loading immediately.
* **Pre-commit Syntax Validation**: Added an automated Node.js JavaScript syntax check into `update_plugin.js` so any future updates are verified error-free before building zip archives.

---

## 1. Rolling 30-Day Price History Engine
* **Decoupled Architecture**: Full time-series history is saved in [price-history.json](file:///c:/Users/jason/Downloads/geministuff/ebike-deal-finder/price-history.json) on the backend (12.7 KB), keeping the public [deals.json](file:///c:/Users/jason/Downloads/geministuff/ebike-deal-finder/deals.json) lightweight (171 KB).
* **Lean Signals Injected into Every Deal**:
  * `lowest_price_30d`: Lowest price seen in the rolling 30-day window.
  * `is_lowest_price_30d`: Boolean flag triggering the card badge `🔥 30-Day Low`.
  * `price_drop_amount`: Calculated cash drop compared to the previous crawl.
  * `last_checked`: Exact ISO timestamp.
  * `is_cached`: Boolean indicating whether the item is live or preserved from cache.

---

## 2. Truthful Specifications & EAPC Legal Compliance
* **No More Guessing**:
  * If motor wattage is not explicitly detected or from an EU/UK certified system (Bosch, Shimano, Brose, Yamaha, Mahle, Fazua), it displays **`Specification not confirmed`** and UK Status displays **`⚠️ Check Retailer`**.
  * Only confirmed $\le 250\text{W}$ motors are marked **`✅ Road Legal`**.
  * Motors $> 250\text{W}$ (e.g. 500W, 750W, 1000W) are marked **`[Watts]W High Torque`** and **`⚠️ Off-Road Only`**.
  * Unconfirmed batteries display **`Specification not confirmed`**, and ranges display **`See retailer listing`**.

---

## 3. Outage Resilience & 72h Stale Cache Expiry
* **Network Retry**: `fetchWithRetry()` automatically retries with a 2-second backoff on 5xx, 429, or network drops.
* **Partial Merge**: If a merchant's pagination encounters an error on later pages, previously cached items from that retailer are merged so products are not arbitrarily lost.
* **Stale Expiry**: Cached deals whose retailer remains offline for more than 72 hours are automatically purged to prevent zombie listings.

---

## 4. UI Filters Added
* **✅ Road Legal Only**: Quick toggle pill in the primary navigation bar.
* **🏷️ Price Ranges Dropdown**: Instant filtering for:
  * Under £1,000
  * £1,000 – £2,500
  * £2,500 – £5,000
  * Over £5,000
* **Badges**: Added `🔥 30-Day Low` indicator on eligible cards.

---

## 5. Cloud Automation Schedule
* **GitHub Action** [.github/workflows/daily_deals.yml](file:///c:/Users/jason/Downloads/geministuff/ebike-deal-finder/.github/workflows/daily_deals.yml) updated to run twice daily:
  * `cron: '0 6,18 * * *'` (06:00 UTC and 18:00 UTC).
  * Automatically commits updates to `deals.json`, `deals-data.js`, and `price-history.json`.

---

## 6. Build Artifacts
* **WordPress Plugin**: Updated to version **2.3.4** in [reight-deals-finder.php](file:///c:/Users/jason/Downloads/geministuff/ebike-deal-finder/wordpress-plugin/reight-deals-finder.php).
* **Compiled Distribution Packages**:
  * [reight-deals-finder.zip](file:///c:/Users/jason/Downloads/geministuff/ebike-deal-finder/reight-deals-finder.zip)
  * [wordpress-plugin.zip](file:///c:/Users/jason/Downloads/geministuff/ebike-deal-finder/wordpress-plugin.zip)
* **Git Status**: Clean working tree, all changes committed and pushed to `main` (commit `0e9c2ae`).
