# Walkthrough: Version 2.4.0 Upgrades

Version 2.4.0 redesigns the ranking algorithm, main-page layout, click analytics, and WordPress directory presentation across the E-Bike Deals Finder ecosystem.

Expensive superbikes with large raw cash savings (£4,500+) no longer dominate the default view. High-value, accessible commuter and road-legal bikes now lead the directory, while luxury clearance deals remain showcased in their own dedicated section and available via a separate sort option.

---

## 1. Budget-Aware `valueScore` Ranking Formula

The previous unbounded formula gave unlimited weight to cash savings:
```javascript
// Previous formula (unbounded cash bonus):
(discountPct * 0.5) + ((savings / 15) * 0.5)
```
A £4,500 saving added 150 points by itself, so £5,000–£10,000 bikes naturally took over the top rankings.

### New `valueScore` Implementation:
```javascript
// Budget-aware value score:
const discountScore = Math.min(discountPct, 50) / 50 * 50;  // Up to 50 pts
const savingsScore = Math.min(savings, 750) / 750 * 20;       // Up to 20 pts

const affordabilityScore =
  price <= 1000 ? 30 :
  price <= 1500 ? 25 :
  price <= 2500 ? 15 :
  price <= 4000 ? 5 : 0;                                     // Up to 30 pts

const valueScore = parseFloat((discountScore + savingsScore + affordabilityScore).toFixed(1));
```

### Score Comparison (Before vs After):
| Bike Model | Sale Price | Cash Saving | Old Score | New `valueScore` | Default Position |
| :--- | :--- | :--- | :--- | :--- | :--- |
| **Engwe P275 SE** | £849 | £650 | ~65 | **90.3** | 🏆 **#1 Top Deal** |
| **DYU M20 All-Terrain** | £799 | £600 | ~61 | **89.0** | 🥈 **#2 Top Deal** |
| **Fiido C11 Pro** | £999 | £636 | ~60 | **86.0** | 🥉 **#3 Top Deal** |
| **Fiido C21 Gravel** | £999 | £636 | ~60 | **86.0** | 🎖️ **#4 Top Deal** |
| **Scott Voltage eRide 900** | £5,599 | £4,500 | **172.5** | **65.0** | Moved to Premium Clearance |
| **Orbea Rise M-LTD Carbon** | £5,999 | £4,000 | **153.3** | **60.0** | Moved to Premium Clearance |

---

## 2. Balanced Sectioned Main-Page Layout

In default view, deals are structured into 4 curated sections:
1. **⚡ Best Value Under £1,500 (Budget Champions)**:
   - First and largest section showcasing high-value budget & commuter e-bikes.
   - Enforces retailer and brand diversity limits.
2. **🚲 Strong Mid-Range Deals (£1,500 – £3,000)**:
   - Highlighted mid-tier options with upgraded motors, torque sensors, and larger batteries.
3. **💎 Premium Clearance Deals (£3,000+)**:
   - Dedicated smaller showcase for high-end clearance price cuts.
4. **📋 Complete E-Bike Deal Directory (Deduplicated)**:
   - Excludes bikes already featured in the sections above to avoid repeating cards.
   - Cleanly lists all remaining verified deals ordered by `valueScore`.

> When a user searches, selects a filter pill (e.g. *Road Legal Only*, *Folding*, *Fat Tyre*), or picks a price band, the UI switches to a single unified grid matching their exact query.

---

## 3. Dedicated "Biggest Cash Savings (£)" Sort Option

Shoppers specifically hunting clearance bargains can choose **"💰 Biggest Cash Savings (£)"** from the sort dropdown:
- Sorts strictly by cash savings (`savings_amount`).
- Surfaces the £4,500 off Scott, £4,000 off Orbea, and £2,400 off Merida bikes immediately.

---

## 4. Retailer & Brand Diversity Safeguards

A diversity filter prevents store dominance in the featured sections:
- Under £1,500 section: Max 3 cards per retailer and max 2 per brand.
- Premium clearance section: Max 2 cards per retailer.
- Prevents any single retailer or brand from monopolizing the screen.

---

## 5. Synchronised Outbound Click Tracking

Both `index.html` and the WordPress plugin share identical tracking behavior:
- **Dispatched Analytics Payload**:
  ```javascript
  {
    event_category: 'Affiliate Outbound',
    event_label: title,
    deal_id: dealId,
    deal_title: title,
    retailer: retailer,
    price: price,
    value_score: scoreVal,
    price_band: priceBand
  }
  ```
- **Events**: Sent to GA4 (`gtag`) and GTM (`window.dataLayer`).
- **Local Persistence**: Both persist clicks by price band (`Under £1,000`, `£1,000 – £1,500`, `£1,500 – £3,000`, `Over £3,000`) into `localStorage.rgb_click_stats`.

---

## 6. Version 2.4.1: Compact Menus & Whitespace Optimization

- **Single-Row Dropdowns**: Scoped high-specificity rules to `#rgb-deal-finder-root select.rgb-dropdown` (`width: auto !important`, `min-width: 180px !important`, `max-width: 260px !important`, `height: 42px !important`) to stop the theme's global `select { width: 100%; min-height: 51px; }` from breaking the Search bar and dropdowns onto 3 stacked lines.
- **Theme Font Override Protection**: Scoped sans-serif typography (`-apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif !important`) across all elements in the plugin to prevent the theme's global `Lora, serif` font from leaking into bike titles and buttons.
- **Whitespace Compression**: Reduced vertical padding and margins across the header, newsletter banner, and category pills for a tight, app-like visual hierarchy matching the standalone preview.

---

## 7. Version 2.4.2: Build Artifacts & Automation

- **WordPress Plugin**: Updated to version **2.4.2** in `wordpress-plugin/reight-deals-finder.php`.
- **Pre-commit Syntax Validation**: Evaluated embedded JavaScript with Node.js parser (`new Function()`) — passed with 0 errors.
- **Distribution Packages**: Re-packaged into `reight-deals-finder.zip` and `wordpress-plugin.zip`.
- **Automation**: Updated `.github/workflows/daily_deals.yml` to automatically execute `update_plugin.js` and commit updated plugin archives during twice-daily scrapes.

---

## 8. Version 2.5.0: Justin's Picks, Range Estimator & Advanced Selectors

Version 2.5.0 brings editorial curation, realistic battery range calculations, and enhanced filtering directly into the deals app interface, while cleaning up legacy page content on the live site.

### Key Additions & Refinements:
1. **Integrated "⭐ Justin's Picks"**:
   - Featured directly as a top-level filter pill inside the deals widget (avoiding clutter above the app).
   - Filters exclusively to Justin's hand-picked recommendations:
     - **Cyrusher Roam** (All-terrain folding commuter)
     - **Cyrusher Kommoda Pro** (Full suspension step-through)
     - **Fiido X** (Ultra-sleek torque-sensor magnesium folding bike)
   - Visual highlighting: Displays a prominent gold **`⭐ Justin's Choice`** badge on matching cards.
   - Resolved the blank screen bug in category switching logic so deals load immediately upon clicking.

2. **Real-World Range Estimates (Reight Good Bikes Calculator Integration)**:
   - Evaluated using the official formula from the [Reight Good Bikes Range Calculator](https://reightgoodbikes.co.uk/how-to-calculate-the-range-of-your-electric-battery/):
     - Typical Tour mode: ~13 Wh/mi (conservative real-world lower bound).
     - Eco assistance mode: ~8 Wh/mi (extended range upper bound).
   - `rgbGetCalcRange(batteryStr)` dynamically extracts Watt-hours from battery capacity strings (e.g. `48V 20Ah` = 960 Wh $\rightarrow$ `74 – 120 mi`).
   - Every deal card now features an **`Est. Range`** row in its 2x2 spec grid, giving buyers instant, honest battery range expectations.

3. **New Filter Selectors**:
   - **🏔️ Mid-Drive**: Surfaces bikes powered by mid-drive crank motors (Bosch, Shimano Steps, Yamaha, Brose, Bafang M-series) for mountain, climbing, and serious commuter cyclists (73+ bikes matched).
   - **🛋️ Step-Through**: Isolates low-step and easy-mount frames (10+ bikes matched).

4. **365-Day Price Retention**:
   - Extended price history retention in `fetch_deals.js` from 90 days to 365 days.
   - Removed automatic pruning of out-of-stock items from `price-history.json` to ensure continuous multi-season price-drop history and tracking.

5. **WordPress Page 7687 Cleanup**:
---

## 9. Version 2.5.1: Food Delivery Guide SEO Overhaul (Post ID 922)

- **Target Keyword**: `electric bikes for delivering food` / `best electric bike for food delivery`
- **URL**: `https://reightgoodbikes.co.uk/electric-bikes-for-delivering-food/`
- **Upgrades Deployed**:
  1. **Discontinued Bike Replaced**: Removed obsolete H9 fat bike; added **Cyrusher Kommoda Pro** (1,040Wh monster battery, 80–130 mi range) and **Engwe P275 SE** (fast-payoff budget commuter).
  2. **Gutenberg Courier Comparison Matrix**: Integrated responsive `wp-block-table` comparing battery capacity, tested shift range, legal status, cargo readiness, and current prices.
  3. **Live Courier Deals Showcase**: Embedded compact in-content deal cards for top active workhorses with direct links to pre-filtered deal searches and the main `/ebike-deals/` engine.
  4. **Courier Practical Guides**: Added practical guidance on Watt-hour battery shift calculations, 250W EAPC vs 1000W illegal motor platform speed bans, and food delivery bag setup.
  5. **Expanded Rank Math FAQs**: Added courier-specific questions and structured FAQ answers.

