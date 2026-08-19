const fs = require('fs');
const path = require('path');
const { execSync } = require('child_process');

const dealsData = JSON.parse(fs.readFileSync('deals.json', 'utf8'));
let php = fs.readFileSync('wordpress-plugin/reight-deals-finder.php', 'utf8');

// 1. Update Version to 2.3.0
php = php.replace(/Version:\s*[0-9\.]+/i, 'Version: 2.3.0');

// 2. Add LiteSpeed nocache hook if not present
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

// 3. Update dealsList and lastUpdatedStr
const dealsJsonStr = JSON.stringify(dealsData.deals);
php = php.replace(/let dealsList = \[.*?\];/s, 'let dealsList = ' + dealsJsonStr + ';');
php = php.replace(/let lastUpdatedStr = ".*?";/, 'let lastUpdatedStr = "' + dealsData.metadata.last_updated + ' (Auto-updated daily)";');

// 4. Update fetchTopDeals to add cache-busting query param and prioritize raw.githubusercontent
const oldFetch = `async function fetchTopDeals() {
          const feedUrls = [
            'https://cdn.jsdelivr.net/gh/usearchme12/electricbikesaffilate@main/deals.json',
            'https://raw.githubusercontent.com/usearchme12/electricbikesaffilate/main/deals.json',
            '<?php echo plugins_url('deals.json', __FILE__); ?>'
          ];`;

const newFetch = `async function fetchTopDeals() {
          const cb = '?t=' + Math.floor(Date.now() / 180000);
          const feedUrls = [
            'https://raw.githubusercontent.com/usearchme12/electricbikesaffilate/main/deals.json' + cb,
            'https://cdn.jsdelivr.net/gh/usearchme12/electricbikesaffilate@main/deals.json' + cb,
            '<?php echo plugins_url('deals.json', __FILE__); ?>' + cb
          ];`;

if (php.includes(oldFetch)) {
  php = php.replace(oldFetch, newFetch);
}

fs.writeFileSync('wordpress-plugin/reight-deals-finder.php', php, 'utf8');
fs.copyFileSync('deals.json', 'wordpress-plugin/deals.json');
console.log('Successfully updated reight-deals-finder.php with', dealsData.deals.length, 'deals and timestamp', dealsData.metadata.last_updated);
