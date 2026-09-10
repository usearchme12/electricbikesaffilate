const fs = require('fs');
const path = require('path');
const { execSync } = require('child_process');

// 1. Load deals data
const dealsData = JSON.parse(fs.readFileSync('deals.json', 'utf8'));
let php = fs.readFileSync('wordpress-plugin/reight-deals-finder.php', 'utf8');

// Normalize line endings to \n for consistent regex & substring matching
const wasCRLF = php.includes('\r\n');
php = php.replace(/\r\n/g, '\n');

// 2. Bump Version to 2.3.3
php = php.replace(/Version:\s*[0-9\.]+/i, 'Version: 2.3.3');

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

// 4. Update CSS for rock-solid grid and image sizing (prevents theme overrides & giant images on back navigation)
const newCssBlock = `        #rgb-deal-finder-root .rgb-grid,
        .rgb-deal-finder-wrapper .rgb-grid {
          display: grid !important;
          grid-template-columns: repeat(auto-fill, minmax(290px, 1fr)) !important;
          gap: 1.5rem !important;
          margin-bottom: 2.5rem !important;
          width: 100% !important;
        }

        #rgb-deal-finder-root .rgb-card,
        .rgb-deal-finder-wrapper .rgb-card {
          background: var(--rgb-card-bg) !important;
          border: 1px solid var(--rgb-border) !important;
          border-radius: 12px !important;
          overflow: hidden !important;
          display: flex !important;
          flex-direction: column !important;
          position: relative !important;
          transition: transform 0.2s, border-color 0.2s !important;
          min-width: 0 !important;
          max-width: 100% !important;
          box-sizing: border-box !important;
        }
        #rgb-deal-finder-root .rgb-card:hover,
        .rgb-deal-finder-wrapper .rgb-card:hover { transform: translateY(-3px) !important; border-color: var(--rgb-primary) !important; }

        .rgb-badge-discount {
          position: absolute;
          top: 0.75rem;
          left: 0.75rem;
          background: var(--rgb-red) !important;
          color: #ffffff !important;
          font-size: 0.72rem;
          font-weight: 800;
          padding: 0.25rem 0.55rem;
          border-radius: 4px;
          text-transform: uppercase;
          z-index: 2;
        }

        .rgb-badge-new {
          position: absolute;
          top: 0.75rem;
          right: 0.75rem;
          background: #10b981 !important;
          color: #000000 !important;
          font-size: 0.72rem;
          font-weight: 800;
          padding: 0.25rem 0.55rem;
          border-radius: 4px;
          text-transform: uppercase;
          z-index: 2;
          box-shadow: 0 0 10px rgba(16, 185, 129, 0.4);
        }

        /* Bulletproof Image Wrapper: Strict 220px fixed viewport */
        .rgb-card-img-wrap,
        #rgb-deal-finder-root .rgb-card-img-wrap,
        .rgb-deal-finder-wrapper .rgb-card-img-wrap {
          position: relative !important;
          width: 100% !important;
          height: 220px !important;
          min-height: 220px !important;
          max-height: 220px !important;
          background: #000000 !important;
          overflow: hidden !important;
          display: flex !important;
          align-items: center !important;
          justify-content: center !important;
          flex-shrink: 0 !important;
          padding: 0 !important;
          margin: 0 !important;
          box-sizing: border-box !important;
        }

        /* Bulletproof Card Image: Immune to theme rules (.entry-content img { height: auto !important; }) */
        .rgb-card-img,
        #rgb-deal-finder-root .rgb-card-img,
        .rgb-deal-finder-wrapper .rgb-card-img,
        #rgb-deal-finder-root img.rgb-card-img,
        .rgb-deal-finder-wrapper img.rgb-card-img,
        .entry-content #rgb-deal-finder-root img.rgb-card-img,
        .entry-content .rgb-deal-finder-wrapper img.rgb-card-img,
        .site-main #rgb-deal-finder-root img.rgb-card-img {
          display: block !important;
          width: 100% !important;
          max-width: 100% !important;
          height: 220px !important;
          min-height: 220px !important;
          max-height: 220px !important;
          object-fit: contain !important;
          object-position: center !important;
          background: #000000 !important;
          margin: 0 auto !important;
          padding: 8px !important;
          border: none !important;
          border-radius: 0 !important;
          box-shadow: none !important;
          box-sizing: border-box !important;
          transform: none;
          transition: transform 0.25s ease !important;
        }
        #rgb-deal-finder-root .rgb-card:hover img.rgb-card-img,
        .rgb-deal-finder-wrapper .rgb-card:hover img.rgb-card-img {
          transform: scale(1.05) !important;
        }`;

// Replace CSS from .rgb-grid to .rgb-card:hover .rgb-card-img { transform: scale(1.04); }
php = php.replace(
  /\.rgb-grid\s*\{[\s\S]*?\.rgb-card:hover \.rgb-card-img \{ transform: scale\(1\.04\); \}/,
  newCssBlock
);

// 5. Update dealsList and lastUpdatedStr
const dealsJsonStr = JSON.stringify(dealsData.deals);
php = php.replace(/let dealsList = \[.*?\];/s, 'let dealsList = ' + dealsJsonStr + ';');
php = php.replace(/let lastUpdatedStr = ".*?";/, 'let lastUpdatedStr = "' + dealsData.metadata.last_updated + ' (Auto-updated daily)";');

// 6. Update fetchTopDeals to eliminate scroll flashing
const newFetchFunc = `        async function fetchTopDeals() {
          const cb = '?t=' + Math.floor(Date.now() / 180000);
          const feedUrls = [
            'https://raw.githubusercontent.com/usearchme12/electricbikesaffilate/main/deals.json' + cb,
            'https://cdn.jsdelivr.net/gh/usearchme12/electricbikesaffilate@main/deals.json' + cb,
            '<?php echo plugins_url('deals.json', __FILE__); ?>' + cb
          ];

          for (const url of feedUrls) {
            try {
              const res = await fetch(url, { cache: 'no-store' });
              if (res.ok) {
                const data = await res.json();
                if (data && data.deals && data.deals.length > 0) {
                  // CRITICAL FLASH FIX: Check if remote data actually differs from current deals
                  const isSameCount = dealsList && dealsList.length === data.deals.length;
                  if (isSameCount) {
                    // Feed is identical: do NOT wipe and re-render the DOM! Eliminates scroll flash entirely!
                    if (data.metadata && data.metadata.last_updated) {
                      lastUpdatedStr = formatLastUpdated(data.metadata.last_updated);
                      const badge = document.getElementById('rgbHeaderBadge');
                      if (badge) badge.innerText = '⚡ Last Refreshed: ' + lastUpdatedStr;
                    }
                    return;
                  }

                  dealsList = data.deals;
                  if (data.metadata && data.metadata.last_updated) {
                    lastUpdatedStr = formatLastUpdated(data.metadata.last_updated);
                    const badge = document.getElementById('rgbHeaderBadge');
                    if (badge) badge.innerText = '⚡ Last Refreshed: ' + lastUpdatedStr;
                  }
                  rgbApplyFilters();
                  return;
                }
              }
            } catch(e) {
              console.warn('Feed fetch attempt failed:', url, e);
            }
          }
        }`;

php = php.replace(
  /async function fetchTopDeals\(\)\s*\{[\s\S]*?console\.warn\('Feed fetch attempt failed:', url, e\);\s*\}\s*\}\s*\}/,
  newFetchFunc
);

// 7. Update card image tag to include dimensions, decoding="async", and LiteSpeed lazy-load bypass flags
php = php.replace(
  /<img src="\${d\.image}" alt="\${d\.title}" class="rgb-card-img"[^>]*>/,
  '<img src="${d.image}" alt="${d.title}" class="rgb-card-img skip-lazy" data-no-lazy="1" width="360" height="220" loading="lazy" decoding="async" onerror="this.src=\'https://images.unsplash.com/photo-1571068316344-75bc76f77890?w=600\'">'
);

// 8. Add BFCache pageshow listener if not present
if (!php.includes('pageshow')) {
  const oldFooter = `        rgbApplyFilters();\n        fetchTopDeals();\n      })();`;
  const newFooter = `        rgbApplyFilters();
        fetchTopDeals();

        // BFCache (Back-Forward Cache) safeguard: ensures layout and cards restore properly
        window.addEventListener('pageshow', function(e) {
          const container = document.getElementById('rgbDealsContainer');
          if (container && (!container.children || container.children.length === 0)) {
            rgbApplyFilters();
          }
        });
      })();`;

  php = php.replace(oldFooter, newFooter);
}

// Restore line endings if originally CRLF
if (wasCRLF) {
  php = php.replace(/\n/g, '\r\n');
}

// 9. Write modified PHP plugin and deals.json
fs.writeFileSync('wordpress-plugin/reight-deals-finder.php', php, 'utf8');
fs.copyFileSync('deals.json', 'wordpress-plugin/deals.json');
console.log('Successfully updated reight-deals-finder.php with', dealsData.deals.length, 'deals and timestamp', dealsData.metadata.last_updated);

// 10. Re-package zip files
try {
  execSync('py -c "import zipfile, os; z = zipfile.ZipFile(\'reight-deals-finder.zip\', \'w\', zipfile.ZIP_DEFLATED); [z.write(os.path.join(\'wordpress-plugin\', f), os.path.join(\'reight-deals-finder\', f)) for f in os.listdir(\'wordpress-plugin\')]; z.close()"');
  fs.copyFileSync('reight-deals-finder.zip', 'wordpress-plugin.zip');
  console.log('Successfully created reight-deals-finder.zip and wordpress-plugin.zip');
} catch (err) {
  console.error('Packaging error:', err);
}
