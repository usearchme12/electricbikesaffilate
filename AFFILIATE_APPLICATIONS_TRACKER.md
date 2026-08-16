# 📋 Affiliate Applications & Store Tracker

This file logs all affiliate programs you have applied to, their network, application status, and how they connect to your Deals Engine.

---

## ⏳ Active Applications (Applied on Awin)

| Retailer / Brand | Website | Network | Status | Deals Ingested? | What to do once approved |
| :--- | :--- | :--- | :--- | :--- | :--- |
| **1. Leisure Lakes Bikes** | [leisurelakesbikes.com](https://www.leisurelakesbikes.com/) | **Awin** | ⏳ **Applied (Pending)** | Waiting for Awin CSV feed | Copy Awin CSV Feed URL from *Toolbox → Create-a-Feed* |
| **2. Ribble Cycles** | [ribblecycles.co.uk](https://www.ribblecycles.co.uk/) | **Awin** | ⏳ **Applied (Pending)** | Waiting for Awin CSV feed | Copy Ribble Awin CSV Feed URL for Hybrid AL e, CGR AL e clearance |
| **3. Heybike UK** | [heybike.co.uk](https://heybike.co.uk/) | **Awin** | ⏳ **Applied (Pending)** | ✅ **Yes (Live)** | Add Awin Publisher ID to `fetch_deals.js` |
| **4. DYU Cycle UK** | [uk.dyucycle.com](https://uk.dyucycle.com/) | **Awin** | ⏳ **Applied (Pending)** | ✅ **Yes (Live)** | Add Awin Publisher ID to `fetch_deals.js` |
| **5. PedalGo UK** | [pedalgo.co.uk](https://pedalgo.co.uk/) | **Awin** | ⏳ **Applied (Pending)** | ✅ **Yes (Live)** | Add Awin Publisher ID to `fetch_deals.js` |

---

## ✅ Direct Brand Feeds Currently Scanned

These stores are already live in your aggregator (`fetch_deals.js`) and updating daily:

| Store Name | Website | Feed Type | Current Deals Found |
| :--- | :--- | :--- | :--- |
| **E-BikeShop UK** | `e-bikeshop.co.uk` | Shopify JSON | 5 deals (Scott, Orbea, Haibike) |
| **Pure Electric** | `pureelectric.com` | Shopify JSON | 7 deals |
| **Velotric** | `velotricbike.com` | Shopify JSON | 6 deals |
| **Engwe UK** | `engwe-bikes-uk.com` | Shopify JSON | 5 deals |
| **DYU Cycle UK** | `uk.dyucycle.com` | Shopify JSON | 3 deals |
| **PedalGo UK** | `pedalgo.co.uk` | Shopify JSON | 2 deals |
| **Heybike UK** | `heybike.co.uk` | Shopify JSON | 1 deal |
| **Tenways** | `tenways.com` | Shopify JSON | 1 deal |

**Total Active Deals:** 30 live discounted e-bikes

---

## 🎯 Quick Instructions for When Awin Approves You:

1. Look in top-right of your Awin dashboard for your **6 or 7-digit Publisher ID** (e.g. `1234567`).
2. Paste that ID into `fetch_deals.js` under the Awin wrapper rule.
3. Every single link across Leisure Lakes, Heybike, DYU, and PedalGo will automatically track commissions to your account!
