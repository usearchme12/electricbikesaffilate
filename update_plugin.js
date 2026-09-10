const fs = require('fs');
const path = require('path');
const { execSync } = require('child_process');

// 1. Load deals data
const dealsData = JSON.parse(fs.readFileSync('deals.json', 'utf8'));
let php = fs.readFileSync('wordpress-plugin/reight-deals-finder.php', 'utf8');

// Normalize line endings to \n for consistent regex & substring matching
const wasCRLF = php.includes('\r\n');
php = php.replace(/\r\n/g, '\n');

// 2. Bump Version to 2.4.2
php = php.replace(/Version:\s*[0-9\.]+/i, 'Version: 2.4.2');


// 3. Ensure LiteSpeed cache bypass hook is in place
if (!php.includes('rgb_disable_litespeed_cache')) {
  const noCacheHook = `
/**
 * Automatically prevent LiteSpeed Cache from caching the deals page and delaying JS.
 */
function rgb_disable_litespeed_cache() {
    global $post;
    if (
        is_singular() &&
        $post &&
        ( has_shortcode($post->post_content, 'ebike_deals') ||
          has_shortcode($post->post_content, 'ebike_deal_finder') )
    ) {
        if (defined('LSCWP_V')) {
            do_action('litespeed_control_set_nocache', 'RGB Deals Shortcode Active');
        }
        if (!headers_sent()) {
            nocache_headers();
        }
    }
}
add_action('wp', 'rgb_disable_litespeed_cache');
`;
  php = php.replace("add_action('wp_head', 'rgb_noindex_deals_page');", "add_action('wp_head', 'rgb_noindex_deals_page');\n" + noCacheHook);
}

// 4. Update CSS for 30-day low badge, dropped badge, and Road Legal pill styling
if (!php.includes('.rgb-badge-low30')) {
  const lowBadgeCss = `
        .rgb-badge-low30 {
          position: absolute;
          top: 0.75rem;
          right: 0.75rem;
          background: #f59e0b !important;
          color: #000000 !important;
          font-size: 0.72rem;
          font-weight: 800;
          padding: 0.25rem 0.55rem;
          border-radius: 4px;
          text-transform: uppercase;
          z-index: 2;
          box-shadow: 0 0 10px rgba(245, 158, 11, 0.4);
        }
        .rgb-badge-drop {
          position: absolute;
          bottom: 0.75rem;
          right: 0.75rem;
          background: #10b981 !important;
          color: #000000 !important;
          font-size: 0.68rem;
          font-weight: 800;
          padding: 0.2rem 0.5rem;
          border-radius: 4px;
          text-transform: uppercase;
          z-index: 2;
        }
        .rgb-pill.legal.active { background: #10b981 !important; border-color: #10b981 !important; color: #000000 !important; font-weight: 800; }
`;
  php = php.replace('.rgb-badge-new {', lowBadgeCss + '        .rgb-badge-new {');
}

// 4b. Add Sectioned Layout CSS for budget-aware default presentation
if (!php.includes('.rgb-section {')) {
  const sectionCss = `
        .rgb-section {
          margin-bottom: 3rem;
        }
        .rgb-section-header {
          margin-bottom: 1.25rem;
          padding-bottom: 0.65rem;
          border-bottom: 1px solid var(--rgb-border);
        }
        .rgb-section-title-wrap {
          display: flex;
          align-items: center;
          gap: 0.75rem;
          flex-wrap: wrap;
          margin-bottom: 0.35rem;
        }
        .rgb-section-title {
          font-size: 1.35rem !important;
          font-weight: 800 !important;
          color: #ffffff !important;
          margin: 0 !important;
          line-height: 1.3 !important;
        }
        .rgb-section-badge {
          font-size: 0.72rem;
          font-weight: 800;
          padding: 0.2rem 0.6rem;
          border-radius: 9999px;
          text-transform: uppercase;
          letter-spacing: 0.04em;
        }
        .rgb-badge-budget {
          background: rgba(16, 185, 129, 0.15);
          border: 1px solid #10b981;
          color: #10b981;
        }
        .rgb-badge-mid {
          background: rgba(59, 130, 246, 0.15);
          border: 1px solid #3b82f6;
          color: #60a5fa;
        }
        .rgb-badge-premium {
          background: rgba(245, 158, 11, 0.15);
          border: 1px solid #f59e0b;
          color: #f59e0b;
        }
        .rgb-badge-all {
          background: rgba(148, 163, 184, 0.15);
          border: 1px solid #64748b;
          color: #cbd5e1;
        }
        .rgb-section-sub {
          font-size: 0.88rem !important;
          color: #94a3b8 !important;
          margin: 0 !important;
        }
`;
  php = php.replace('#rgb-deal-finder-root .rgb-grid,', sectionCss + '\n        #rgb-deal-finder-root .rgb-grid,');
}

// 5. Add Price Range Select to Search Row if not present
if (!php.includes('rgbPriceSelect')) {
  const priceSelectHtml = `          <select id="rgbPriceSelect" class="rgb-dropdown" onchange="rgbApplyFilters()">
            <option value="all">🏷️ All Price Ranges</option>
            <option value="under1000">⚡ Under £1,000</option>
            <option value="1000-2500">🚲 £1,000 – £2,500</option>
            <option value="2500-5000">💎 £2,500 – £5,000</option>
            <option value="over5000">👑 Over £5,000</option>
          </select>
        </div>`;
  php = php.replace(/\s*<\/select>\s*<\/div>\s*<div class="rgb-pills-row">/, '\n          </select>\n' + priceSelectHtml + '\n\n        <div class="rgb-pills-row">');
}

// 5b. Update Sort Select to feature valueScore as default and savings as separate option
if (php.includes('<option value="dealScore">🏆 Best Deal Score</option>')) {
  php = php.replace(
    '<option value="dealScore">🏆 Best Deal Score</option>',
    '<option value="valueScore">🏆 Best Value Score</option>'
  );
}
// Ensure Biggest Cash Savings is right after Best Value Score
const oldSortBlock = `<select id="rgbSortSelect" class="rgb-dropdown" onchange="rgbApplyFilters()">
            <option value="valueScore">🏆 Best Value Score</option>
            <option value="newest">✨ Newest Drops First</option>
            <option value="discount">🔥 Highest % Discount</option>
            <option value="savings">💰 Biggest Cash Savings (£)</option>`;
const newSortBlock = `<select id="rgbSortSelect" class="rgb-dropdown" onchange="rgbApplyFilters()">
            <option value="valueScore">🏆 Best Value Score</option>
            <option value="savings">💰 Biggest Cash Savings (£)</option>
            <option value="newest">✨ Newest Drops First</option>
            <option value="discount">🔥 Highest % Discount</option>`;
if (php.includes(oldSortBlock)) {
  php = php.replace(oldSortBlock, newSortBlock);
}


// 5c. Ensure deals container has no hardcoded rgb-grid so it can house sections
php = php.replace('<div class="rgb-grid" id="rgbDealsContainer">', '<div id="rgbDealsContainer">');

// 6. Add Road Legal Only Pill if not present
if (!php.includes('data-cat="legal"')) {
  php = php.replace(
    '<button class="rgb-pill active" data-cat="all" onclick="rgbFilter(\'all\', this)">All Deals</button>',
    '<button class="rgb-pill active" data-cat="all" onclick="rgbFilter(\'all\', this)">All Deals</button>\n          <button class="rgb-pill legal" data-cat="legal" onclick="rgbFilter(\'legal\', this)">✅ Road Legal Only</button>'
  );
}

// 7. Ensure script tag has LiteSpeed bypass attributes so it executes immediately on load
php = php.replace(
  /<script(\s+data-no-optimize="1"\s+data-no-defer="1")?>/i,
  '<script data-no-optimize="1" data-no-defer="1">'
);

// 8. Update dealsList and lastUpdatedStr
const dealsJsonStr = JSON.stringify(dealsData.deals);
php = php.replace(/let dealsList = \[.*?\];/s, 'let dealsList = ' + dealsJsonStr + ';');
php = php.replace(/let lastUpdatedStr = ".*?";/, 'let lastUpdatedStr = "' + dealsData.metadata.last_updated + ' (Auto-updated daily)";');

// 9. CLEANLY replace rendering and filter logic with budget-aware ranking, sectioned layout, and price band tracking
const cleanLogic = `        function rgbGetPriceBand(price) {
          if (price <= 1000) return 'Under £1,000';
          if (price <= 1500) return '£1,000 – £1,500';
          if (price <= 3000) return '£1,500 – £3,000';
          return 'Over £3,000';
        }

        window.rgbTrackDealClick = function(dealId, title, retailer, price, valueScore) {
          try {
            const priceBand = rgbGetPriceBand(price);
            const scoreVal = typeof valueScore === 'number' ? valueScore : (parseFloat(valueScore) || 80);
            const payload = {
              event_category: 'Affiliate Outbound',
              event_label: title,
              deal_id: dealId,
              deal_title: title,
              retailer: retailer,
              price: price,
              value_score: scoreVal,
              price_band: priceBand
            };

            if (typeof gtag === 'function') {
              gtag('event', 'outbound_deal_click', payload);
            }
            if (window.dataLayer && Array.isArray(window.dataLayer)) {
              window.dataLayer.push({
                event: 'outbound_deal_click',
                ...payload
              });
            }

            const stats = JSON.parse(localStorage.getItem('rgb_click_stats') || '{"Under £1,000":0,"£1,000 – £1,500":0,"£1,500 – £3,000":0,"Over £3,000":0,"total":0}');
            stats[priceBand] = (stats[priceBand] || 0) + 1;
            stats.total = (stats.total || 0) + 1;
            localStorage.setItem('rgb_click_stats', JSON.stringify(stats));
            console.log('[RGB DEAL CLICK]', payload);
          } catch(e) {
            console.warn('Click tracking error:', e);
          }
        };

        function rgbFilterWithDiversity(deals, maxPerRetailer, maxPerBrand, limit) {
          const retailerCounts = {};
          const brandCounts = {};
          const selected = [];

          for (const deal of deals) {
            const retailer = deal.retailer || 'Unknown';
            const brand = deal.brand || 'Unknown';

            const rCount = retailerCounts[retailer] || 0;
            const bCount = brandCounts[brand] || 0;

            if (rCount >= maxPerRetailer || bCount >= maxPerBrand) {
              continue;
            }

            selected.push(deal);
            retailerCounts[retailer] = rCount + 1;
            brandCounts[brand] = bCount + 1;

            if (limit && selected.length >= limit) {
              break;
            }
          }
          return selected;
        }

        function rgbRenderDealCard(d) {
          const sym = d.symbol || '£';
          const savings = Math.round(d.savings_amount).toLocaleString();
          const score = Math.round(d.valueScore || d.dealScore || 85);
          const safeTitle = (d.title || '').replace(/'/g, "\\\\'");
          return \`
            <article class="rgb-card">
              <div class="rgb-badge-discount">SAVE \${sym}\${savings} (\${d.discount_percentage}% OFF)</div>
              \${d.is_lowest_price_30d ? '<div class="rgb-badge-low30">🔥 30-Day Low</div>' : (d.is_new ? '<div class="rgb-badge-new">✨ Just Added</div>' : '')}
              \${d.price_drop_amount > 0 ? \`<div class="rgb-badge-drop">📉 Dropped \${sym}\${d.price_drop_amount}</div>\` : ''}
              <div class="rgb-card-img-wrap">
                <img src="\${d.image}" alt="\${d.title}" class="rgb-card-img skip-lazy" data-no-lazy="1" width="360" height="220" loading="lazy" decoding="async" onerror="this.src='https://images.unsplash.com/photo-1571068316344-75bc76f77890?w=600'">
              </div>
              <div class="rgb-card-body">
                <div class="rgb-row">
                  <span class="rgb-retailer">\${d.retailer}</span>
                  <span class="rgb-score">Score: \${score}</span>
                </div>
                <h3 class="rgb-card-title">\${d.title}</h3>
                <div class="rgb-specs">
                  <div><span class="rgb-spec-lbl">Category</span><div class="rgb-spec-val">\${d.category}</div></div>
                  <div><span class="rgb-spec-lbl">Motor</span><div class="rgb-spec-val" title="\${d.motor_power}">\${d.motor_power === 'Specification not confirmed' ? '<span style="color:#94a3b8;">Not confirmed</span>' : d.motor_power}</div></div>
                  <div><span class="rgb-spec-lbl">Battery</span><div class="rgb-spec-val" title="\${d.battery}">\${d.battery === 'Specification not confirmed' ? '<span style="color:#94a3b8;">Not confirmed</span>' : d.battery}</div></div>
                  <div><span class="rgb-spec-lbl">UK Status</span><div class="rgb-spec-val">\${d.is_uk_legal ? '<span style="color:#10b981;">✅ Road Legal</span>' : (d.motor_power === 'Specification not confirmed' ? '<span style="color:#f59e0b;">⚠️ Check Retailer</span>' : '<span style="color:#ef4444;">⚠️ Off-Road</span>')}</div></div>
                </div>
                <div class="rgb-price-row">
                  <div>
                    <div class="rgb-sale-price">\${sym}\${d.sale_price.toLocaleString('en-GB', {minimumFractionDigits: 2})}</div>
                    \${d.rrp ? \`<span class="rgb-rrp">Was \${sym}\${d.rrp.toLocaleString('en-GB', {minimumFractionDigits: 2})}</span>\` : ''}
                  </div>
                  <span class="rgb-savings">Save \${sym}\${savings}</span>
                </div>
                <a href="\${d.url}" target="_blank" rel="sponsored nofollow noopener" class="rgb-btn" onclick="rgbTrackDealClick('\${d.id}', '\${safeTitle}', '\${d.retailer}', \${d.sale_price}, \${score})">
                  👉 View Deal at \${d.retailer} ➔
                </a>
              </div>
            </article>
          \`;
        }

        window.rgbApplyFilters = function() {
          const search = (document.getElementById('rgbSearchInput')?.value || '').trim().toLowerCase();
          const sortMode = document.getElementById('rgbSortSelect')?.value || 'valueScore';
          const priceFilter = document.getElementById('rgbPriceSelect')?.value || 'all';
          const container = document.getElementById('rgbDealsContainer');

          let filtered = dealsList.filter(d => {
            if (search) {
              const title = (d.title || '').toLowerCase();
              const brand = (d.brand || '').toLowerCase();
              const ret = (d.retailer || '').toLowerCase();
              if (!title.includes(search) && !brand.includes(search) && !ret.includes(search)) return false;
            }

            if (priceFilter === 'under1000' && d.sale_price > 1000) return false;
            if (priceFilter === '1000-2500' && (d.sale_price < 1000 || d.sale_price > 2500)) return false;
            if (priceFilter === '2500-5000' && (d.sale_price < 2500 || d.sale_price > 5000)) return false;
            if (priceFilter === 'over5000' && d.sale_price < 5000) return false;

            if (curCat === 'legal') return d.is_uk_legal === true;
            if (curCat === 'new') return d.is_new === true;
            if (curCat === 'mega') return d.discount_percentage >= 30 || d.savings_amount >= 400;
            if (curCat === 'budget') return d.sale_price <= 1000;
            if (curCat === 'Fat Tyre') {
              if (d.category === 'Fat Tyre' || d.category === 'Cargo & Fat Tyre') return true;
              const t = (d.title + ' ' + (d.brand || '')).toLowerCase();
              const fatKws = ['fat', 'kommoda', 'ranger', 'rover', 'trax', 'roam', 'kuattro', 'ovia', 'ep-2', 'engine pro', 'l20', 'm20', 'o20', 'e26', 'mars', 'tyson', 'brawn', 'hero', 'all-terrain', '4.0'];
              return fatKws.some(k => t.includes(k));
            }
            if (curCat !== 'all' && d.category !== curCat) return false;
            return true;
          });

          function sortDeals(list, mode) {
            return [...list].sort((a, b) => {
              if (mode === 'valueScore' || mode === 'dealScore') return (b.valueScore || b.dealScore || 0) - (a.valueScore || a.dealScore || 0);
              if (mode === 'savings') return (b.savings_amount || 0) - (a.savings_amount || 0);
              if (mode === 'newest') return (b.first_seen || '').localeCompare(a.first_seen || '') || (b.valueScore || 0) - (a.valueScore || 0);
              if (mode === 'discount') return b.discount_percentage - a.discount_percentage;
              if (mode === 'price-asc') return a.sale_price - b.sale_price;
              if (mode === 'price-desc') return b.sale_price - a.sale_price;
              return 0;
            });
          }

          filtered = sortDeals(filtered, sortMode);

          const statusEl = document.getElementById('rgbStatusMeta');
          if (statusEl) {
            statusEl.innerHTML = 'Showing <strong>' + filtered.length + '</strong> verified deals &bull; <span style="color: #10b981;">⚡ Live Verified: ' + lastUpdatedStr + '</span>';
          }

          const isDefaultView = (!search && curCat === 'all' && priceFilter === 'all' && (sortMode === 'valueScore' || sortMode === 'dealScore'));

          if (isDefaultView && filtered.length > 0) {
            const under1500 = filtered.filter(d => d.sale_price <= 1500);
            const midRange = filtered.filter(d => d.sale_price > 1500 && d.sale_price <= 3000);
            const premium = [...filtered.filter(d => d.sale_price > 3000)].sort((a, b) => (b.savings_amount || 0) - (a.savings_amount || 0));

            const rawUnder1500 = rgbFilterWithDiversity(under1500, 3, 3, 8);
            const rawMid = rgbFilterWithDiversity(midRange, 3, 3, 8);
            const rawPremium = rgbFilterWithDiversity(premium, 3, 3, 8);

            // Safeguard: Only show complete full rows (multiples of 4, minimum 4).
            // Any leftover bikes automatically flow into the Complete E-Bike Deal Directory below
            // so NO section ever has 1, 2, or 3 bikes leaving an awkward empty white gap!
            const featuredUnder1500 = rawUnder1500.length >= 4 ? rawUnder1500.slice(0, Math.floor(rawUnder1500.length / 4) * 4) : [];
            const featuredMid = rawMid.length >= 4 ? rawMid.slice(0, Math.floor(rawMid.length / 4) * 4) : [];
            const featuredPremium = rawPremium.length >= 4 ? rawPremium.slice(0, Math.floor(rawPremium.length / 4) * 4) : [];

            // Exclude already featured bikes so they do not duplicate in the directory below
            const featuredIds = new Set([
              ...featuredUnder1500.map(d => d.id),
              ...featuredMid.map(d => d.id),
              ...featuredPremium.map(d => d.id)
            ]);
            const remainingDeals = filtered.filter(d => !featuredIds.has(d.id));

            let html = '';

            if (featuredUnder1500.length > 0) {
              html += \`
                <section class="rgb-section">
                  <div class="rgb-section-header">
                    <div class="rgb-section-title-wrap">
                      <h3 class="rgb-section-title">⚡ Best Value Under £1,500</h3>
                      <span class="rgb-section-badge rgb-badge-budget">Budget Champions</span>
                    </div>
                    <p class="rgb-section-sub">Highest-scoring road legal & commuter e-bikes for everyday UK riders. Ranked by genuine value, not luxury price tags.</p>
                  </div>
                  <div class="rgb-grid">
                    \${featuredUnder1500.map(rgbRenderDealCard).join('')}
                  </div>
                </section>
              \`;
            }

            if (featuredMid.length > 0) {
              html += \`
                <section class="rgb-section">
                  <div class="rgb-section-header">
                    <div class="rgb-section-title-wrap">
                      <h3 class="rgb-section-title">🚲 Strong Mid-Range Deals (£1,500 – £3,000)</h3>
                      <span class="rgb-section-badge rgb-badge-mid">Performance Value</span>
                    </div>
                    <p class="rgb-section-sub">Upgraded motors, torque sensors, and larger range batteries offering serious long-term value.</p>
                  </div>
                  <div class="rgb-grid">
                    \${featuredMid.map(rgbRenderDealCard).join('')}
                  </div>
                </section>
              \`;
            }

            if (featuredPremium.length > 0) {
              html += \`
                <section class="rgb-section">
                  <div class="rgb-section-header">
                    <div class="rgb-section-title-wrap">
                      <h3 class="rgb-section-title">💎 Premium Clearance Deals (£3,000+)</h3>
                      <span class="rgb-section-badge rgb-badge-premium">Biggest Cash Savings</span>
                    </div>
                    <p class="rgb-section-sub">Massive clearance cuts on high-end carbon e-MTBs and premium European builds.</p>
                  </div>
                  <div class="rgb-grid">
                    \${featuredPremium.map(rgbRenderDealCard).join('')}
                  </div>
                </section>
              \`;
            }

            if (remainingDeals.length > 0) {
              html += \`
                <section class="rgb-section">
                  <div class="rgb-section-header">
                    <div class="rgb-section-title-wrap">
                      <h3 class="rgb-section-title">📋 Complete E-Bike Deal Directory</h3>
                      <span class="rgb-section-badge rgb-badge-all">\${remainingDeals.length} More Deals</span>
                    </div>
                    <p class="rgb-section-sub">Browse every remaining verified price cut currently tracked across UK retailers, ordered by overall value score.</p>
                  </div>
                  <div class="rgb-grid">
                    \${remainingDeals.map(rgbRenderDealCard).join('')}
                  </div>
                </section>
              \`;
            }

            container.innerHTML = html;
          } else {
            if (filtered.length === 0) {
              container.innerHTML = '<div style="text-align: center; padding: 3rem 1rem; color: #94a3b8; font-size: 1.1rem; grid-column: 1 / -1;">No e-bikes found matching your criteria. Try loosening your search filters!</div>';
            } else {
              container.innerHTML = '<div class="rgb-grid">' + filtered.map(rgbRenderDealCard).join('') + '</div>';
            }
          }
        };`;

// Replace from window.rgbApplyFilters or previous helper definitions to the end of rgbApplyFilters
if (php.includes('function rgbFilterWithDiversity')) {
  php = php.replace(
    /function rgbGetPriceBand[\s\S]*?window\.rgbApplyFilters\s*=\s*function\(\)\s*\{[\s\S]*?container\.innerHTML\s*=[\s\S]*?\}\s*\}\s*;/s,
    cleanLogic
  );
} else {
  php = php.replace(
    /window\.rgbApplyFilters\s*=\s*function\(\)\s*\{[\s\S]*?container\.innerHTML\s*=[\s\S]*?\}\)\.join\(''\);\s*\};/s,
    cleanLogic
  );
}

// 10. VALIDATION: Extract the entire JavaScript block and validate with Node.js parser
const jsMatch = php.match(/<script\b[^>]*>([\s\S]*?)<\/script>/i);
if (!jsMatch) {
  console.error('[ERROR] No <script> tag found in plugin PHP!');
  process.exit(1);
}

// Strip out PHP echo tags so JavaScript can be tested cleanly
let testJs = jsMatch[1].replace(/<\?php[\s\S]*?\?>/g, '"https://example.com/deals.json"');

try {
  new Function(testJs);
  console.log('[VALIDATION PASSED] Embedded JavaScript is 100% syntactically valid with zero duplicate declarations!');
} catch (err) {
  console.error('[CRITICAL SYNTAX ERROR] In plugin JavaScript:', err.message);
  process.exit(1);
}

// Restore line endings if originally CRLF
if (wasCRLF) {
  php = php.replace(/\n/g, '\r\n');
}

// 11. Write modified PHP plugin and deals.json
fs.writeFileSync('wordpress-plugin/reight-deals-finder.php', php, 'utf8');
fs.copyFileSync('deals.json', 'wordpress-plugin/deals.json');
console.log('Successfully updated reight-deals-finder.php with v2.4.1 and', dealsData.deals.length, 'deals');


// 12. Re-package zip files
try {
  const pyCmd = process.platform === 'win32' ? 'py' : 'python3';
  execSync(`${pyCmd} -c "import zipfile, os; z = zipfile.ZipFile('reight-deals-finder.zip', 'w', zipfile.ZIP_DEFLATED); [z.write(os.path.join('wordpress-plugin', f), os.path.join('reight-deals-finder', f)) for f in os.listdir('wordpress-plugin')]; z.close()"`);
  fs.copyFileSync('reight-deals-finder.zip', 'wordpress-plugin.zip');
  console.log('Successfully created reight-deals-finder.zip and wordpress-plugin.zip');
} catch (err) {

  console.error('Packaging error:', err);
  process.exit(1);
}
