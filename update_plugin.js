const fs = require('fs');
const path = require('path');
const { execSync } = require('child_process');

// 1. Load deals data
const dealsData = JSON.parse(fs.readFileSync('deals.json', 'utf8'));
let php = fs.readFileSync('wordpress-plugin/reight-deals-finder.php', 'utf8');

// Normalize line endings to \n for consistent regex & substring matching
const wasCRLF = php.includes('\r\n');
php = php.replace(/\r\n/g, '\n');

// 2. Bump Version to 2.3.4
php = php.replace(/Version:\s*[0-9\.]+/i, 'Version: 2.3.4');

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

// 4. Update CSS for 30-day low badge and Road Legal pill styling
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
        .rgb-pill.legal.active { background: #10b981 !important; border-color: #10b981 !important; color: #000000 !important; font-weight: 800; }
`;
  php = php.replace('.rgb-badge-new {', lowBadgeCss + '        .rgb-badge-new {');
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

// 6. Add Road Legal Only Pill if not present
if (!php.includes('data-cat="legal"')) {
  php = php.replace(
    '<button class="rgb-pill active" data-cat="all" onclick="rgbFilter(\'all\', this)">All Deals</button>',
    '<button class="rgb-pill active" data-cat="all" onclick="rgbFilter(\'all\', this)">All Deals</button>\n          <button class="rgb-pill legal" data-cat="legal" onclick="rgbFilter(\'legal\', this)">✅ Road Legal Only</button>'
  );
}

// 7. Update dealsList and lastUpdatedStr
const dealsJsonStr = JSON.stringify(dealsData.deals);
php = php.replace(/let dealsList = \[.*?\];/s, 'let dealsList = ' + dealsJsonStr + ';');
php = php.replace(/let lastUpdatedStr = ".*?";/, 'let lastUpdatedStr = "' + dealsData.metadata.last_updated + ' (Auto-updated daily)";');

// 8. Update filter logic inside rgbApplyFilters to handle legal and price range filters
const oldFilterLogic = `            if (curCat === 'new') return d.is_new === true;
            if (curCat === 'mega') return d.discount_percentage >= 30 || d.savings_amount >= 400;
            if (curCat === 'budget') return d.sale_price <= 1000;
            if (curCat === 'Fat Tyre') {
              if (d.category === 'Fat Tyre' || d.category === 'Cargo & Fat Tyre') return true;
              const t = (d.title + ' ' + (d.brand || '')).toLowerCase();
              const fatKws = ['fat', 'kommoda', 'ranger', 'rover', 'trax', 'roam', 'kuattro', 'ovia', 'ep-2', 'engine pro', 'l20', 'm20', 'o20', 'e26', 'mars', 'tyson', 'brawn', 'hero', 'all-terrain', '4.0'];
              return fatKws.some(k => t.includes(k));
            }
            if (curCat !== 'all' && d.category !== curCat) return false;
            return true;`;

const newFilterLogic = `            const priceFilter = document.getElementById('rgbPriceSelect')?.value || 'all';
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
            return true;`;

if (php.includes(oldFilterLogic)) {
  php = php.replace(oldFilterLogic, newFilterLogic);
}

// 9. Update card markup in container.innerHTML for badges and truthful specs
const oldCardMarkup = `              <article class="rgb-card">
                <div class="rgb-badge-discount">SAVE \${sym}\${savings} (\${d.discount_percentage}% OFF)</div>
                \${d.is_new ? '<div class="rgb-badge-new">✨ Just Added</div>' : ''}
                <div class="rgb-card-img-wrap">
                  <img src="\${d.image}" alt="\${d.title}" class="rgb-card-img skip-lazy" data-no-lazy="1" width="360" height="220" loading="lazy" decoding="async" onerror="this.src='https://images.unsplash.com/photo-1571068316344-75bc76f77890?w=600'">
                </div>
                <div class="rgb-card-body">
                  <div class="rgb-row">
                    <span class="rgb-retailer">\${d.retailer}</span>
                    <span class="rgb-score">Score: \${d.dealScore || 85}</span>
                  </div>
                  <h3 class="rgb-card-title">\${d.title}</h3>
                  <div class="rgb-specs">
                    <div><span class="rgb-spec-lbl">Category</span><div class="rgb-spec-val">\${d.category}</div></div>
                    <div><span class="rgb-spec-lbl">Motor</span><div class="rgb-spec-val">\${d.motor_power}</div></div>
                    <div><span class="rgb-spec-lbl">Battery</span><div class="rgb-spec-val">\${d.battery || 'Spec on site'}</div></div>
                    <div><span class="rgb-spec-lbl">UK Status</span><div class="rgb-spec-val">\${d.is_uk_legal ? '✅ Road Legal' : '⚠️ Off-Road'}</div></div>
                  </div>`;

const newCardMarkup = `              <article class="rgb-card">
                <div class="rgb-badge-discount">SAVE \${sym}\${savings} (\${d.discount_percentage}% OFF)</div>
                \${d.is_lowest_price_30d ? '<div class="rgb-badge-low30">🔥 30-Day Low</div>' : (d.is_new ? '<div class="rgb-badge-new">✨ Just Added</div>' : '')}
                <div class="rgb-card-img-wrap">
                  <img src="\${d.image}" alt="\${d.title}" class="rgb-card-img skip-lazy" data-no-lazy="1" width="360" height="220" loading="lazy" decoding="async" onerror="this.src='https://images.unsplash.com/photo-1571068316344-75bc76f77890?w=600'">
                </div>
                <div class="rgb-card-body">
                  <div class="rgb-row">
                    <span class="rgb-retailer">\${d.retailer}</span>
                    <span class="rgb-score">Score: \${d.dealScore || 85}</span>
                  </div>
                  <h3 class="rgb-card-title">\${d.title}</h3>
                  <div class="rgb-specs">
                    <div><span class="rgb-spec-lbl">Category</span><div class="rgb-spec-val">\${d.category}</div></div>
                    <div><span class="rgb-spec-lbl">Motor</span><div class="rgb-spec-val" title="\${d.motor_power}">\${d.motor_power === 'Specification not confirmed' ? '<span style="color:#94a3b8;">Not confirmed</span>' : d.motor_power}</div></div>
                    <div><span class="rgb-spec-lbl">Battery</span><div class="rgb-spec-val" title="\${d.battery}">\${d.battery === 'Specification not confirmed' ? '<span style="color:#94a3b8;">Not confirmed</span>' : d.battery}</div></div>
                    <div><span class="rgb-spec-lbl">UK Status</span><div class="rgb-spec-val">\${d.is_uk_legal ? '<span style="color:#10b981;">✅ Road Legal</span>' : (d.motor_power === 'Specification not confirmed' ? '<span style="color:#f59e0b;">⚠️ Check Retailer</span>' : '<span style="color:#ef4444;">⚠️ Off-Road</span>')}</div></div>
                  </div>`;

if (php.includes(oldCardMarkup)) {
  php = php.replace(oldCardMarkup, newCardMarkup);
}

// Restore line endings if originally CRLF
if (wasCRLF) {
  php = php.replace(/\n/g, '\r\n');
}

// 10. Write modified PHP plugin and deals.json
fs.writeFileSync('wordpress-plugin/reight-deals-finder.php', php, 'utf8');
fs.copyFileSync('deals.json', 'wordpress-plugin/deals.json');
console.log('Successfully updated reight-deals-finder.php with v2.3.4 and', dealsData.deals.length, 'deals');

// 11. Re-package zip files
try {
  execSync('py -c "import zipfile, os; z = zipfile.ZipFile(\'reight-deals-finder.zip\', \'w\', zipfile.ZIP_DEFLATED); [z.write(os.path.join(\'wordpress-plugin\', f), os.path.join(\'reight-deals-finder\', f)) for f in os.listdir(\'wordpress-plugin\')]; z.close()"');
  fs.copyFileSync('reight-deals-finder.zip', 'wordpress-plugin.zip');
  console.log('Successfully created reight-deals-finder.zip and wordpress-plugin.zip');
} catch (err) {
  console.error('Packaging error:', err);
}
