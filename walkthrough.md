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

## 6. Build Artifacts & Verification

- **WordPress Plugin**: Updated to version **2.4.0** in `wordpress-plugin/reight-deals-finder.php`.
- **Pre-commit Syntax Validation**: Evaluated embedded JavaScript with Node.js parser (`new Function()`) — passed with 0 errors.
- **Distribution Packages**: Re-packaged into `reight-deals-finder.zip` and `wordpress-plugin.zip`.
- **Automation**: Updated `.github/workflows/daily_deals.yml` to automatically execute `update_plugin.js` and commit updated plugin archives during twice-daily scrapes.
