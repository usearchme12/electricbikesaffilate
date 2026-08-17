<?php
/**
 * Plugin Name: Reight Good Bikes - E-Bike Deals Finder
 * Plugin URI: https://reightgoodbikes.co.uk/
 * Description: Embeds an interactive, multi-source UK Electric Bike Deals & Clearance Offers page via shortcode [ebike_deals]. Automatically syncs with the live cloud aggregator. Zero iframe layout, 100% mobile-optimized.
 * Version: 2.2.0
 * Author: Reight Good Bikes
 * Text Domain: reight-deals
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

// Impact.com Publisher ID
define('RGB_IMPACT_PUBLISHER_ID', 'P-A7627881-cae8-4568-9e08-026bbcec06071');

/**
 * Output the Impact.com Universal Tracking Tag (UTT) in <head> on every page.
 * Required for commission tracking across all affiliate links.
 */
function rgb_impact_tracking_tag() {
    ?>
    <!-- Impact.com Universal Tracking Tag | Reight Good Bikes | <?php echo RGB_IMPACT_PUBLISHER_ID; ?> -->
    <script type="text/javascript">(function(i,m,p,a,c,t){c.ire_o=p;c[p]=c[p]||function(){(c[p].a=c[p].a||[]).push(arguments)};t=a.createElement(m);var z=a.getElementsByTagName(m)[0];t.async=1;t.src=i;z.parentNode.insertBefore(t,z)})('https://utt.impactcdn.com/<?php echo RGB_IMPACT_PUBLISHER_ID; ?>1.js','script','impactStat',document,window);impactStat('trackImpression');</script>
    <?php
}
add_action('wp_head', 'rgb_impact_tracking_tag');

/**
 * Output noindex meta tag ONLY on pages containing the [ebike_deals] shortcode.
 * Prevents Google from indexing the deals aggregator page while keeping
 * the rest of the site fully crawlable.
 */
function rgb_noindex_deals_page() {
    global $post;
    if (
        is_singular() &&
        $post &&
        ( has_shortcode($post->post_content, 'ebike_deals') ||
          has_shortcode($post->post_content, 'ebike_deal_finder') )
    ) {
        echo '<meta name="robots" content="noindex, follow">' . "\n";
    }
}
add_action('wp_head', 'rgb_noindex_deals_page');

function rgb_register_deal_finder_shortcode($atts) {
    ob_start();
    ?>
    <div id="rgb-deal-finder-root" class="rgb-deal-finder-wrapper">
      <style>
        .rgb-deal-finder-wrapper {
          --rgb-bg: #090d16;
          --rgb-card-bg: #131b2e;
          --rgb-border: #23314f;
          --rgb-primary: #f59e0b;
          --rgb-primary-hover: #d97706;
          --rgb-neon: #10b981;
          --rgb-blue: #38bdf8;
          --rgb-red: #ef4444;
          --rgb-text: #f8fafc;
          --rgb-muted: #94a3b8;
          font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
          color: var(--rgb-text);
          margin: 1.5rem 0;
          line-height: 1.5;
        }

        .rgb-deal-finder-wrapper * { box-sizing: border-box; }

        .rgb-header {
          text-align: center;
          padding: 1.5rem 1rem 2rem;
        }
        .rgb-badge {
          display: inline-block;
          background: rgba(245, 158, 11, 0.15);
          border: 1px solid rgba(245, 158, 11, 0.35);
          color: var(--rgb-primary);
          padding: 0.35rem 0.9rem;
          border-radius: 9999px;
          font-size: 0.78rem;
          font-weight: 800;
          margin-bottom: 0.75rem;
          text-transform: uppercase;
        }
        .rgb-header h2 {
          font-size: clamp(1.6rem, 4vw, 2.2rem);
          font-weight: 800;
          color: #0f172a;
          margin-bottom: 0.5rem;
        }
        .rgb-header p {
          color: #64748b;
          font-size: 0.95rem;
          max-width: 600px;
          margin: 0 auto;
        }

        .rgb-filter-bar {
          background: var(--rgb-card-bg);
          border: 1px solid var(--rgb-border);
          border-radius: 12px;
          padding: 1rem 1.25rem;
          margin-bottom: 2rem;
          display: flex;
          flex-direction: column;
          gap: 0.9rem;
        }

        .rgb-search-row {
          display: flex;
          gap: 0.75rem;
          flex-wrap: wrap;
        }
        .rgb-search-input {
          flex: 1;
          min-width: 220px;
          background: #090d16;
          border: 1px solid var(--rgb-border);
          color: #fff;
          padding: 0.65rem 1rem;
          border-radius: 8px;
          font-size: 0.9rem;
          outline: none;
        }
        .rgb-search-input:focus { border-color: var(--rgb-primary); }

        .rgb-dropdown {
          background: #090d16;
          border: 1px solid var(--rgb-border);
          color: #fff;
          padding: 0.65rem 0.9rem;
          border-radius: 8px;
          font-size: 0.85rem;
          outline: none;
          cursor: pointer;
        }

        .rgb-pills-row {
          display: flex;
          gap: 0.4rem;
          flex-wrap: wrap;
          align-items: center;
        }
        .rgb-pill {
          background: #090d16;
          border: 1px solid var(--rgb-border);
          color: var(--rgb-muted);
          padding: 0.35rem 0.75rem;
          border-radius: 6px;
          font-size: 0.8rem;
          font-weight: 600;
          cursor: pointer;
          transition: all 0.15s;
        }
        .rgb-pill:hover { background: #1e293b; color: #fff; }
        .rgb-pill.active { background: var(--rgb-primary); border-color: var(--rgb-primary); color: #000; font-weight: 800; }
        .rgb-pill.mega.active { background: var(--rgb-red); border-color: var(--rgb-red); color: #fff; }

        .rgb-status-meta {
          font-size: 0.85rem;
          color: #64748b;
          margin-bottom: 1rem;
          font-weight: 600;
        }

        .rgb-grid {
          display: grid;
          grid-template-columns: repeat(auto-fill, minmax(310px, 1fr));
          gap: 1.5rem;
          margin-bottom: 2.5rem;
        }

        .rgb-card {
          background: var(--rgb-card-bg);
          border: 1px solid var(--rgb-border);
          border-radius: 12px;
          overflow: hidden;
          display: flex;
          flex-direction: column;
          position: relative;
          transition: transform 0.2s, border-color 0.2s;
        }
        .rgb-card:hover { transform: translateY(-3px); border-color: var(--rgb-primary); }

        .rgb-badge-discount {
          position: absolute;
          top: 0.75rem;
          left: 0.75rem;
          background: var(--rgb-red);
          color: #fff;
          font-size: 0.72rem;
          font-weight: 800;
          padding: 0.25rem 0.55rem;
          border-radius: 4px;
          text-transform: uppercase;
          z-index: 2;
        }

        .rgb-card-img-wrap { width: 100%; height: 210px; background: #000; overflow: hidden; }
        .rgb-card-img { width: 100%; height: 100%; object-fit: cover; transition: transform 0.2s; }
        .rgb-card:hover .rgb-card-img { transform: scale(1.04); }

        .rgb-card-body { padding: 1.25rem; display: flex; flex-direction: column; flex: 1; }

        .rgb-row { display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.4rem; }
        .rgb-retailer { font-size: 0.75rem; font-weight: 800; color: var(--rgb-blue); text-transform: uppercase; }
        .rgb-score { font-size: 0.72rem; font-weight: 800; color: var(--rgb-primary); background: rgba(245,158,11,0.1); padding: 0.15rem 0.4rem; border-radius: 4px; }

        .rgb-card-title {
          font-size: 1.05rem;
          font-weight: 700;
          color: #fff;
          line-height: 1.35;
          margin-bottom: 0.75rem;
          display: -webkit-box;
          -webkit-line-clamp: 2;
          -webkit-box-orient: vertical;
          overflow: hidden;
          min-height: 2.8rem;
        }

        .rgb-specs {
          display: grid;
          grid-template-columns: 1fr 1fr;
          gap: 0.4rem;
          background: rgba(9, 13, 22, 0.6);
          border: 1px solid var(--rgb-border);
          border-radius: 6px;
          padding: 0.5rem 0.75rem;
          margin-bottom: 0.9rem;
          font-size: 0.75rem;
        }
        .rgb-spec-lbl { color: var(--rgb-muted); font-size: 0.65rem; text-transform: uppercase; }
        .rgb-spec-val { font-weight: 700; color: #f1f5f9; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }

        .rgb-price-row {
          display: flex;
          justify-content: space-between;
          align-items: baseline;
          margin-top: auto;
          padding-top: 0.75rem;
          border-top: 1px solid var(--rgb-border);
          margin-bottom: 0.9rem;
        }
        .rgb-sale-price { font-size: 1.55rem; font-weight: 800; color: var(--rgb-primary); }
        .rgb-rrp { font-size: 0.85rem; color: var(--rgb-muted); text-decoration: line-through; margin-left: 0.3rem; }
        .rgb-savings { font-size: 0.78rem; font-weight: 800; color: var(--rgb-neon); }

        .rgb-btn {
          display: block;
          width: 100%;
          text-align: center;
          background: var(--rgb-primary);
          color: #000 !important;
          font-weight: 800;
          font-size: 0.9rem;
          padding: 0.75rem 1rem;
          border-radius: 6px;
          text-decoration: none;
          transition: background 0.2s;
        }
        .rgb-btn:hover { background: var(--rgb-primary-hover); color: #000 !important; }
      </style>

      <div class="rgb-header">
        <span class="rgb-badge">⚡ Live Multi-Source E-Bike Deals</span>
        <h2>Top Electric Bike Discounts & Deals</h2>
        <p>Real-time price cuts from verified UK e-bike specialists and direct brands.</p>
      </div>

      <div class="rgb-filter-bar">
        <div class="rgb-search-row">
          <input type="text" id="rgbSearchInput" class="rgb-search-input" placeholder="🔍 Search e-bikes, brands, Bosch motors, folding..." oninput="rgbApplyFilters()">
          
          <select id="rgbSortSelect" class="rgb-dropdown" onchange="rgbApplyFilters()">
            <option value="dealScore">🏆 Best Deal Score</option>
            <option value="discount">🔥 Highest % Discount</option>
            <option value="savings">💰 Biggest Cash Savings (£)</option>
            <option value="price-asc">🏷️ Lowest Price</option>
            <option value="price-desc">💎 Highest Price</option>
          </select>
        </div>

        <div class="rgb-pills-row">
          <button class="rgb-pill active" data-cat="all" onclick="rgbFilter('all', this)">All Deals</button>
          <button class="rgb-pill mega" data-cat="mega" onclick="rgbFilter('mega', this)">🔥 Mega Deals (30%+)</button>
          <button class="rgb-pill" data-cat="budget" onclick="rgbFilter('budget', this)">⚡ Under £1,000</button>
          <button class="rgb-pill" data-cat="Mountain" onclick="rgbFilter('Mountain', this)">Mountain</button>
          <button class="rgb-pill" data-cat="Commuter" onclick="rgbFilter('Commuter', this)">Commuter</button>
          <button class="rgb-pill" data-cat="Folding" onclick="rgbFilter('Folding', this)">Folding</button>
        </div>
      </div>

      <div class="rgb-status-meta" id="rgbStatusMeta">Loading live deals feed...</div>

      <div class="rgb-grid" id="rgbDealsContainer">
        <!-- Cards inserted by script -->
      </div>
    </div>

    <script>
      (function() {
        let dealsList = [{"id":"e_bikeshop_co_uk_15351321559424","title":"Haibike Hybe CF 9 2026","brand":"Haibike Electric Bikes","retailer":"E-BikeShop UK","country":"UK","currency":"GBP","symbol":"£","category":"Mountain","dealBucket":"🔥 Mega Deal","dealScore":80,"motor_power":"250W-500W High Torque","battery":"400Wh - 750Wh Lithium-Ion","range_miles":"35 - 75 Miles","max_speed":"15.5 mph","is_uk_legal":true,"rrp":6549,"sale_price":4599,"savings_amount":1950,"discount_percentage":30,"image":"https://cdn.shopify.com/s/files/1/0609/4838/1934/files/Haibike-Hybe-CF-9-2026-Electric-Bike-Brown.jpg?v=1765810730","url":"https://www.e-bikeshop.co.uk/products/electric-bike-haibike-hybe-cf-9-2026","badge_text":"SAVE £1950 (30% OFF)"},{"id":"engwe_uk_8391383548086","title":"P275 SE Combo","brand":"engwe uk","retailer":"Engwe UK Official","country":"UK","currency":"GBP","symbol":"£","category":"Cargo & Fat Tyre","dealBucket":"🔥 Mega Deal","dealScore":70.2,"motor_power":"250W Road Legal","battery":"400Wh - 750Wh Lithium-Ion","range_miles":"35 - 75 Miles","max_speed":"15.5 mph","is_uk_legal":true,"rrp":2948,"sale_price":1548,"savings_amount":1400,"discount_percentage":47,"image":"https://cdn.shopify.com/s/files/1/0627/1385/6182/files/P275_SE_1.jpg?v=1768809378","url":"https://engwe-bikes-uk.com/products/engwe-p275-se-combo","badge_text":"SAVE £1400 (47% OFF)"},{"id":"engwe_uk_8241398710454","title":"P275 SE","brand":"engwe uk","retailer":"Engwe UK Official","country":"UK","currency":"GBP","symbol":"£","category":"Commuter","dealBucket":"🔥 Mega Deal","dealScore":46.8,"motor_power":"250W Road Legal","battery":"400Wh - 750Wh Lithium-Ion","range_miles":"35 - 75 Miles","max_speed":"15.5 mph","is_uk_legal":true,"rrp":1499,"sale_price":799,"savings_amount":700,"discount_percentage":47,"image":"https://cdn.shopify.com/s/files/1/0627/1385/6182/files/P275SE_01_7f24b64d-face-433f-9bcc-1f87be91b8c3.jpg?v=1767074661","url":"https://engwe-bikes-uk.com/products/engwe-p275-se","badge_text":"SAVE £700 (47% OFF)"},{"id":"heybike_uk_8370053808443","title":"EC 1 Commuter E-Bike","brand":"Heybike UK","retailer":"Heybike UK","country":"UK","currency":"GBP","symbol":"£","category":"Commuter","dealBucket":"🔥 Mega Deal","dealScore":37.5,"motor_power":"250W Road Legal","battery":"400Wh - 750Wh Lithium-Ion","range_miles":"35 - 75 Miles","max_speed":"15.5 mph","is_uk_legal":true,"rrp":1699,"sale_price":1099,"savings_amount":600,"discount_percentage":35,"image":"https://cdn.shopify.com/s/files/1/0730/5792/7483/files/EC1_7ef5773e-c729-4c47-a99b-39bc78075802.png?v=1751970130","url":"https://heybike.co.uk/products/ec-1","badge_text":"SAVE £600 (35% OFF)"},{"id":"cyrusher_uk_8796910616789","title":"Kommoda 3.0 , Step-through Ebike","brand":"Cyrusher United Kingdom","retailer":"Cyrusher UK","country":"UK","currency":"GBP","symbol":"£","category":"Commuter","dealBucket":"🔥 Mega Deal","dealScore":36.5,"motor_power":"250W Road Legal","battery":"400Wh - 750Wh Lithium-Ion","range_miles":"35 - 75 Miles","max_speed":"15.5 mph","is_uk_legal":true,"rrp":1799,"sale_price":1199,"savings_amount":600,"discount_percentage":33,"image":"https://cdn.shopify.com/s/files/1/0627/5861/7301/files/Cyrusher-E-Bike-Kommoda3.0-Main-Green-1.jpg?v=1774320844","url":"https://www.cyrusher.co.uk/products/kommoda-2-0","badge_text":"SAVE £600 (33% OFF)"},{"id":"cyrusher_uk_9343680282837","title":"Kommoda Pro Step-through Electric Bike","brand":"Cyrusher","retailer":"Cyrusher UK","country":"UK","currency":"GBP","symbol":"£","category":"Commuter","dealBucket":"🔥 Mega Deal","dealScore":29.7,"motor_power":"250W Road Legal","battery":"400Wh - 750Wh Lithium-Ion","range_miles":"35 - 75 Miles","max_speed":"15.5 mph","is_uk_legal":true,"rrp":1899,"sale_price":1399,"savings_amount":500,"discount_percentage":26,"image":"https://cdn.shopify.com/s/files/1/0627/5861/7301/files/Cyrusher-kommoda-pro-SUV-E-Bike-Blue-5.jpg?v=1783494579","url":"https://www.cyrusher.co.uk/products/kommoda-pro","badge_text":"SAVE £500 (26% OFF)"},{"id":"e_bikeshop_co_uk_15351339581824","title":"Haibike AllMtn 4 2026","brand":"Haibike Electric Bikes","retailer":"E-BikeShop UK","country":"UK","currency":"GBP","symbol":"£","category":"Mountain","dealBucket":"🔥 Mega Deal","dealScore":29.2,"motor_power":"250W-500W High Torque","battery":"400Wh - 750Wh Lithium-Ion","range_miles":"35 - 75 Miles","max_speed":"15.5 mph","is_uk_legal":true,"rrp":4249,"sale_price":3599,"savings_amount":650,"discount_percentage":15,"image":"https://cdn.shopify.com/s/files/1/0609/4838/1934/files/Haibike-AllMtn-4-2026-Electric-Bike-Grey.jpg?v=1765887983","url":"https://www.e-bikeshop.co.uk/products/electric-bike-haibike-allmtn-4-2026","badge_text":"SAVE £650 (15% OFF)"},{"id":"dyu_cycle_uk_8271011086500","title":"DYU C6 26 Inch City Electric Bike","brand":"DYU","retailer":"DYU UK Official","country":"UK","currency":"GBP","symbol":"£","category":"Commuter","dealBucket":"🔥 Mega Deal","dealScore":29,"motor_power":"250W Road Legal","battery":"400Wh - 750Wh Lithium-Ion","range_miles":"35 - 75 Miles","max_speed":"15.5 mph","is_uk_legal":true,"rrp":799,"sale_price":499,"savings_amount":300,"discount_percentage":38,"image":"https://cdn.shopify.com/s/files/1/0627/0579/5236/files/01_6af4da4e-08ff-4d40-b7d1-5aecd83b830a.jpg?v=1760495883","url":"https://uk.dyucycle.com/products/c6-26-inch-city-electric-bike","badge_text":"SAVE £300 (38% OFF)"},{"id":"cyrusher_uk_7986263949525","title":"Trax 2.0 All-Terrain Electric Bike","brand":"Cyrusher United Kingdom","retailer":"Cyrusher UK","country":"UK","currency":"GBP","symbol":"£","category":"Commuter","dealBucket":"🔥 Mega Deal","dealScore":25.7,"motor_power":"250W Road Legal","battery":"400Wh - 750Wh Lithium-Ion","range_miles":"35 - 75 Miles","max_speed":"15.5 mph","is_uk_legal":true,"rrp":2799,"sale_price":2299,"savings_amount":500,"discount_percentage":18,"image":"https://cdn.shopify.com/s/files/1/0627/5861/7301/files/Cyrusher-E-Bike-Trax2.0-Main-Blue-11.jpg?v=1767939260","url":"https://www.cyrusher.co.uk/products/trax-ebike","badge_text":"SAVE £500 (18% OFF)"},{"id":"cyrusher_uk_7934768644309","title":"Ranger 2.0 All-Terrain Electric Bike","brand":"Cyrusher United Kingdom","retailer":"Cyrusher UK","country":"UK","currency":"GBP","symbol":"£","category":"Commuter","dealBucket":"🔥 Mega Deal","dealScore":25.7,"motor_power":"250W Road Legal","battery":"400Wh - 750Wh Lithium-Ion","range_miles":"35 - 75 Miles","max_speed":"15.5 mph","is_uk_legal":true,"rrp":2799,"sale_price":2299,"savings_amount":500,"discount_percentage":18,"image":"https://cdn.shopify.com/s/files/1/0627/5861/7301/files/Cyrusher_Ranger_2.0_ebike_Green-11.jpg?v=1767938841","url":"https://www.cyrusher.co.uk/products/ranger-ebike","badge_text":"SAVE £500 (18% OFF)"},{"id":"cyrusher_uk_9006660419797","title":"Flex Mountain E-Bike","brand":"Cyrusher United Kingdom","retailer":"Cyrusher UK","country":"UK","currency":"GBP","symbol":"£","category":"Mountain","dealBucket":"🔥 Mega Deal","dealScore":24.3,"motor_power":"250W-500W High Torque","battery":"400Wh - 750Wh Lithium-Ion","range_miles":"35 - 75 Miles","max_speed":"15.5 mph","is_uk_legal":true,"rrp":1799,"sale_price":1399,"savings_amount":400,"discount_percentage":22,"image":"https://cdn.shopify.com/s/files/1/0627/5861/7301/files/Cyrusher-Flex-Emtb-E-Bike-Main-Blue-1.jpg?v=1784899404","url":"https://www.cyrusher.co.uk/products/flex","badge_text":"SAVE £400 (22% OFF)"},{"id":"dyu_cycle_uk_8271012069540","title":"DYU D3F 14 Inch Mini Folding Electric Bike","brand":"DYU","retailer":"DYU UK Official","country":"UK","currency":"GBP","symbol":"£","category":"Folding","dealBucket":"🔥 Mega Deal","dealScore":23.8,"motor_power":"250W Road Legal","battery":"400Wh - 750Wh Lithium-Ion","range_miles":"35 - 75 Miles","max_speed":"15.5 mph","is_uk_legal":true,"rrp":549,"sale_price":359,"savings_amount":190,"discount_percentage":35,"image":"https://cdn.shopify.com/s/files/1/0627/0579/5236/products/6_fb537967-18f1-4a7f-8dbd-ba2eef5e34dc.jpg?v=1760495806","url":"https://uk.dyucycle.com/products/dyu-small-electric-bike-d3f","badge_text":"SAVE £190 (35% OFF)"},{"id":"e_bikeshop_co_uk_15351351247232","title":"Haibike AllTrail 4 2026","brand":"Haibike Electric Bikes","retailer":"E-BikeShop UK","country":"UK","currency":"GBP","symbol":"£","category":"Mountain","dealBucket":"🔥 Mega Deal","dealScore":23.7,"motor_power":"250W-500W High Torque","battery":"400Wh - 750Wh Lithium-Ion","range_miles":"35 - 75 Miles","max_speed":"15.5 mph","is_uk_legal":true,"rrp":3499,"sale_price":2999,"savings_amount":500,"discount_percentage":14,"image":"https://cdn.shopify.com/s/files/1/0609/4838/1934/files/Haibike-AllTrail-4-2026-Electric-Bike-Blue.jpg?v=1765893898","url":"https://www.e-bikeshop.co.uk/products/electric-bike-haibike-alltrail-4-2026","badge_text":"SAVE £500 (14% OFF)"},{"id":"cyrusher_uk_9445342740693","title":"Sonder Lite Folding E-Bike","brand":"Cyrusher","retailer":"Cyrusher UK","country":"UK","currency":"GBP","symbol":"£","category":"Folding","dealBucket":"⚡ Budget Steal","dealScore":23.5,"motor_power":"250W Road Legal","battery":"400Wh - 750Wh Lithium-Ion","range_miles":"35 - 75 Miles","max_speed":"15.5 mph","is_uk_legal":true,"rrp":1099,"sale_price":799,"savings_amount":300,"discount_percentage":27,"image":"https://cdn.shopify.com/s/files/1/0627/5861/7301/files/Sonder-Lite-Blue-1.jpg?v=1783347171","url":"https://www.cyrusher.co.uk/products/sonder-lite","badge_text":"SAVE £300 (27% OFF)"},{"id":"pure_electric_14937652167032","title":"Air⁵ Ultra Suspension","brand":"Pure Electric","retailer":"Pure Electric","country":"UK","currency":"GBP","symbol":"£","category":"Commuter","dealBucket":"⚡ Budget Steal","dealScore":21.2,"motor_power":"250W Road Legal","battery":"400Wh - 750Wh Lithium-Ion","range_miles":"35 - 75 Miles","max_speed":"15.5 mph","is_uk_legal":true,"rrp":699,"sale_price":499,"savings_amount":200,"discount_percentage":29,"image":"https://cdn.shopify.com/s/files/1/0063/2714/0425/files/pure-electric-scooter-air-ultra-suspension-1238469011.jpg?v=1778803545","url":"https://www.pureelectric.com/products/air5-ultra-suspension","badge_text":"SAVE £200 (29% OFF)"},{"id":"pure_electric_14904608751992","title":"Advance+ Reboxed","brand":"Pure","retailer":"Pure Electric","country":"UK","currency":"GBP","symbol":"£","category":"Commuter","dealBucket":"⚡ Budget Steal","dealScore":20.2,"motor_power":"250W Road Legal","battery":"400Wh - 750Wh Lithium-Ion","range_miles":"35 - 75 Miles","max_speed":"15.5 mph","is_uk_legal":true,"rrp":749,"sale_price":549,"savings_amount":200,"discount_percentage":27,"image":"https://cdn.shopify.com/s/files/1/0063/2714/0425/files/pure-scooter-platinum-advance-33049019514968.jpg?v=1720747281","url":"https://www.pureelectric.com/products/advance-reboxed-1","badge_text":"SAVE £200 (27% OFF)"},{"id":"pedalgo_uk_10154801561864","title":"Amcargobikes - Ultimate Curve Electric","brand":"Amcargobikes","retailer":"PedalGo UK","country":"UK","currency":"GBP","symbol":"£","category":"Cargo & Fat Tyre","dealBucket":"🔥 Mega Deal","dealScore":19.9,"motor_power":"250W Road Legal","battery":"400Wh - 750Wh Lithium-Ion","range_miles":"35 - 75 Miles","max_speed":"15.5 mph","is_uk_legal":true,"rrp":3100,"sale_price":2699,"savings_amount":401,"discount_percentage":13,"image":"https://cdn.shopify.com/s/files/1/0911/9727/6424/files/Screenshot2025-04-05at00.01.32.jpg?v=1750519070","url":"https://pedalgo.co.uk/products/electric-cargo-bike-ultimate-curve","badge_text":"SAVE £401 (13% OFF)"},{"id":"pure_electric_14932006240632","title":"Escape Pro (Previous Gen)","brand":"Pure Electric","retailer":"Pure Electric","country":"UK","currency":"GBP","symbol":"£","category":"Commuter","dealBucket":"⚡ Budget Steal","dealScore":18.5,"motor_power":"250W Road Legal","battery":"400Wh - 750Wh Lithium-Ion","range_miles":"35 - 75 Miles","max_speed":"15.5 mph","is_uk_legal":true,"rrp":549,"sale_price":399,"savings_amount":150,"discount_percentage":27,"image":"https://cdn.shopify.com/s/files/1/0063/2714/0425/files/pure-electric-scooter-escape-pro-1238462108.jpg?v=1778800600","url":"https://www.pureelectric.com/products/escape-pro","badge_text":"SAVE £150 (27% OFF)"},{"id":"cyrusher_uk_9006660387029","title":"Roam All-Terrain Electric Bike","brand":"Cyrusher United Kingdom","retailer":"Cyrusher UK","country":"UK","currency":"GBP","symbol":"£","category":"Commuter","dealBucket":"Standard Deal","dealScore":18.5,"motor_power":"250W Road Legal","battery":"400Wh - 750Wh Lithium-Ion","range_miles":"35 - 75 Miles","max_speed":"15.5 mph","is_uk_legal":true,"rrp":1799,"sale_price":1499,"savings_amount":300,"discount_percentage":17,"image":"https://cdn.shopify.com/s/files/1/0627/5861/7301/files/Cyrusher-Roam-E-Bike-Blue-001.jpg?v=1762762175","url":"https://www.cyrusher.co.uk/products/roam","badge_text":"SAVE £300 (17% OFF)"},{"id":"cyrusher_uk_8905427812565","title":"Glider Cargo E-Bike","brand":"Cyrusher United Kingdom","retailer":"Cyrusher UK","country":"UK","currency":"GBP","symbol":"£","category":"Cargo & Fat Tyre","dealBucket":"Standard Deal","dealScore":18.5,"motor_power":"250W Road Legal","battery":"400Wh - 750Wh Lithium-Ion","range_miles":"35 - 75 Miles","max_speed":"15.5 mph","is_uk_legal":true,"rrp":1799,"sale_price":1499,"savings_amount":300,"discount_percentage":17,"image":"https://cdn.shopify.com/s/files/1/0627/5861/7301/files/GliderwithBag-3.jpg?v=1764129891","url":"https://www.cyrusher.co.uk/products/glider","badge_text":"SAVE £300 (17% OFF)"},{"id":"cyrusher_uk_8832694321365","title":"Rover, All-Terrain Ebike","brand":"Cyrusher United Kingdom","retailer":"Cyrusher UK","country":"UK","currency":"GBP","symbol":"£","category":"Commuter","dealBucket":"Standard Deal","dealScore":18.5,"motor_power":"250W Road Legal","battery":"400Wh - 750Wh Lithium-Ion","range_miles":"35 - 75 Miles","max_speed":"15.5 mph","is_uk_legal":true,"rrp":1799,"sale_price":1499,"savings_amount":300,"discount_percentage":17,"image":"https://cdn.shopify.com/s/files/1/0627/5861/7301/files/Cyrusher-Rover-E-Bike-Green-001.jpg?v=1762770884","url":"https://www.cyrusher.co.uk/products/rover","badge_text":"SAVE £300 (17% OFF)"},{"id":"pure_electric_15020411683192","title":"Pure x McLaren Black Refurbished","brand":"Pure","retailer":"Pure Electric","country":"UK","currency":"GBP","symbol":"£","category":"Commuter","dealBucket":"⚡ Budget Steal","dealScore":17.7,"motor_power":"250W Road Legal","battery":"400Wh - 750Wh Lithium-Ion","range_miles":"35 - 75 Miles","max_speed":"15.5 mph","is_uk_legal":true,"rrp":899,"sale_price":699,"savings_amount":200,"discount_percentage":22,"image":"https://cdn.shopify.com/s/files/1/0063/2714/0425/files/pure-scooter-black-pure-x-mclaren-black-1230822866.jpg?v=1775044030","url":"https://www.pureelectric.com/products/pure-x-mclaren-black-refurbished","badge_text":"SAVE £200 (22% OFF)"},{"id":"pure_electric_15020411519352","title":"Pure x McLaren Papaya Refurbished","brand":"Pure","retailer":"Pure Electric","country":"UK","currency":"GBP","symbol":"£","category":"Commuter","dealBucket":"⚡ Budget Steal","dealScore":17.7,"motor_power":"250W Road Legal","battery":"400Wh - 750Wh Lithium-Ion","range_miles":"35 - 75 Miles","max_speed":"15.5 mph","is_uk_legal":true,"rrp":899,"sale_price":699,"savings_amount":200,"discount_percentage":22,"image":"https://cdn.shopify.com/s/files/1/0063/2714/0425/files/pure-scooter-papaya-pure-x-mclaren-papaya-1230798400.jpg?v=1775035570","url":"https://www.pureelectric.com/products/pure-x-mclaren-papaya-refurbished","badge_text":"SAVE £200 (22% OFF)"},{"id":"engwe_uk_8269327138998","title":"EP-2 3.0 Boost","brand":"engwe uk","retailer":"Engwe UK Official","country":"UK","currency":"GBP","symbol":"£","category":"Commuter","dealBucket":"Standard Deal","dealScore":14.2,"motor_power":"250W Road Legal","battery":"400Wh - 750Wh Lithium-Ion","range_miles":"35 - 75 Miles","max_speed":"15.5 mph","is_uk_legal":true,"rrp":1299,"sale_price":1099,"savings_amount":200,"discount_percentage":15,"image":"https://cdn.shopify.com/s/files/1/0627/1385/6182/files/EP-23.0boost01.jpg?v=1767084299","url":"https://engwe-bikes-uk.com/products/engwe-ep-2-3-0-boost","badge_text":"SAVE £200 (15% OFF)"},{"id":"pedalgo_uk_10154645389576","title":"Amcargobikes Deluxe - Electric Cargo Bike","brand":"Amcargobikes","retailer":"PedalGo UK","country":"UK","currency":"GBP","symbol":"£","category":"Cargo & Fat Tyre","dealBucket":"Standard Deal","dealScore":11.6,"motor_power":"250W Road Legal","battery":"400Wh - 750Wh Lithium-Ion","range_miles":"35 - 75 Miles","max_speed":"15.5 mph","is_uk_legal":true,"rrp":1999,"sale_price":1800,"savings_amount":199,"discount_percentage":10,"image":"https://cdn.shopify.com/s/files/1/0911/9727/6424/files/Screenshot_2025-04-04_at_19.06.52.jpg?v=1749591584","url":"https://pedalgo.co.uk/products/amcargobikes-electric-cargo-bike-deluxe","badge_text":"SAVE £199 (10% OFF)"},{"id":"fiido_uk_9981279502637","title":"Fiido C700 City E-Bike","brand":"Fiido.uk","retailer":"Fiido UK Official","country":"UK","currency":"GBP","symbol":"£","category":"Commuter","dealBucket":"Standard Deal","dealScore":11.6,"motor_power":"250W Road Legal","battery":"400Wh - 750Wh Lithium-Ion","range_miles":"35 - 75 Miles","max_speed":"15.5 mph","is_uk_legal":true,"rrp":1727,"sale_price":1545,"savings_amount":182,"discount_percentage":11,"image":"https://cdn.shopify.com/s/files/1/0814/1232/5677/files/c700-img-1_27e456ff-8f3e-40a5-8416-3ecbe87e04a4.webp?v=1743413208","url":"https://uk.fiido.com/products/fiido-c700-city-ebike","badge_text":"SAVE £182 (11% OFF)"},{"id":"engwe_uk_8815718695094","title":"O20 Boost Combo","brand":"ENGWE UK Official","retailer":"Engwe UK Official","country":"UK","currency":"GBP","symbol":"£","category":"Cargo & Fat Tyre","dealBucket":"Standard Deal","dealScore":11.2,"motor_power":"250W Road Legal","battery":"400Wh - 750Wh Lithium-Ion","range_miles":"35 - 75 Miles","max_speed":"15.5 mph","is_uk_legal":true,"rrp":2348,"sale_price":2148,"savings_amount":200,"discount_percentage":9,"image":"https://cdn.shopify.com/s/files/1/0627/1385/6182/files/c0ad2be69190712bf2cde8f7cd4fee95.jpg?v=1782697074","url":"https://engwe-bikes-uk.com/products/engwe-o20-boost-combo","badge_text":"SAVE £200 (9% OFF)"},{"id":"tenways_14923430789492","title":"TENWAYS AGO X","brand":"TENWAYS","retailer":"Tenways Direct","country":"UK/EU","currency":"GBP","symbol":"£","category":"Commuter","dealBucket":"Standard Deal","dealScore":10.8,"motor_power":"250W Road Legal","battery":"400Wh - 750Wh Lithium-Ion","range_miles":"35 - 75 Miles","max_speed":"20 mph","is_uk_legal":false,"rrp":1744,"sale_price":1570,"savings_amount":174,"discount_percentage":10,"image":"https://cdn.shopify.com/s/files/1/0563/3926/7733/files/26-green-1_91a6bb82-d699-4ec2-9d2b-693692afc7be.webp?v=1772448584","url":"https://www.tenways.com/products/ago-x","badge_text":"SAVE £174 (10% OFF)"},{"id":"pure_electric_14955809997176","title":"Pure x McLaren Chrome Reboxed","brand":"Pure","retailer":"Pure Electric","country":"UK","currency":"GBP","symbol":"£","category":"Commuter","dealBucket":"⚡ Budget Steal","dealScore":9.8,"motor_power":"250W Road Legal","battery":"400Wh - 750Wh Lithium-Ion","range_miles":"35 - 75 Miles","max_speed":"15.5 mph","is_uk_legal":true,"rrp":799,"sale_price":699,"savings_amount":100,"discount_percentage":13,"image":"https://cdn.shopify.com/s/files/1/0063/2714/0425/files/pure-scooter-chrome-pure-x-mclaren-chrome-1182624359.jpg?v=1754076717","url":"https://www.pureelectric.com/products/pure-x-mclaren-chrome-reboxed","badge_text":"SAVE £100 (13% OFF)"},{"id":"cyrusher_uk_8992605634773","title":"Loop 2.0 City Folding E-Bike","brand":"Cyrusher United Kingdom","retailer":"Cyrusher UK","country":"UK","currency":"GBP","symbol":"£","category":"Folding","dealBucket":"⚡ Budget Steal","dealScore":9.8,"motor_power":"250W Road Legal","battery":"400Wh - 750Wh Lithium-Ion","range_miles":"35 - 75 Miles","max_speed":"15.5 mph","is_uk_legal":true,"rrp":799,"sale_price":699,"savings_amount":100,"discount_percentage":13,"image":"https://cdn.shopify.com/s/files/1/0627/5861/7301/files/Cyrusher-Loop-City-Folding-E-Bike-Main-Black-07.jpg?v=1784881157","url":"https://www.cyrusher.co.uk/products/loop","badge_text":"SAVE £100 (13% OFF)"},{"id":"pure_electric_14941317824888","title":"Pure x McLaren Flex","brand":"Pure","retailer":"Pure Electric","country":"UK","currency":"GBP","symbol":"£","category":"Commuter","dealBucket":"⚡ Budget Steal","dealScore":8.3,"motor_power":"250W Road Legal","battery":"400Wh - 750Wh Lithium-Ion","range_miles":"35 - 75 Miles","max_speed":"15.5 mph","is_uk_legal":true,"rrp":999,"sale_price":899,"savings_amount":100,"discount_percentage":10,"image":"https://cdn.shopify.com/s/files/1/0063/2714/0425/files/pure-scooter-papaya-pure-x-mclaren-flex-1238469007.jpg?v=1778803486","url":"https://www.pureelectric.com/products/pure-x-mclaren-flex","badge_text":"SAVE £100 (10% OFF)"},{"id":"engwe_uk_8800587808950","title":"ENGWE O20 Boost","brand":"ENGWE UK Official","retailer":"Engwe UK Official","country":"UK","currency":"GBP","symbol":"£","category":"Commuter","dealBucket":"Standard Deal","dealScore":7.3,"motor_power":"250W Road Legal","battery":"400Wh - 750Wh Lithium-Ion","range_miles":"35 - 75 Miles","max_speed":"15.5 mph","is_uk_legal":true,"rrp":1199,"sale_price":1099,"savings_amount":100,"discount_percentage":8,"image":"https://cdn.shopify.com/s/files/1/0627/1385/6182/files/2053.png?v=1779963383","url":"https://engwe-bikes-uk.com/products/engwe-o20-boost","badge_text":"SAVE £100 (8% OFF)"},{"id":"fiido_uk_10053672927533","title":"Fiido Nomads Touring E-bike","brand":"Fiido.uk","retailer":"Fiido UK Official","country":"UK","currency":"GBP","symbol":"£","category":"Commuter","dealBucket":"Standard Deal","dealScore":6.6,"motor_power":"250W Road Legal","battery":"400Wh - 750Wh Lithium-Ion","range_miles":"35 - 75 Miles","max_speed":"15.5 mph","is_uk_legal":true,"rrp":1363,"sale_price":1271,"savings_amount":92,"discount_percentage":7,"image":"https://cdn.shopify.com/s/files/1/0814/1232/5677/files/11-sunstone-yellow-m.webp?v=1751938354","url":"https://uk.fiido.com/products/fiido-nomads-trekking-e-bike","badge_text":"SAVE £92 (7% OFF)"},{"id":"engwe_uk_8183413309622","title":"EP-2 Boost","brand":"Engwe-bikes-UK","retailer":"Engwe UK Official","country":"UK","currency":"GBP","symbol":"£","category":"Commuter","dealBucket":"⚡ Budget Steal","dealScore":4.2,"motor_power":"250W Road Legal","battery":"400Wh - 750Wh Lithium-Ion","range_miles":"35 - 75 Miles","max_speed":"15.5 mph","is_uk_legal":true,"rrp":949,"sale_price":899,"savings_amount":50,"discount_percentage":5,"image":"https://cdn.shopify.com/s/files/1/0627/1385/6182/files/2_f7000d51-73b1-442e-9190-8c7e25f9bf48.jpg?v=1767074687","url":"https://engwe-bikes-uk.com/products/engwe-ep-2-boost","badge_text":"SAVE £50 (5% OFF)"}];
        let curCat = 'all';

        async function fetchTopDeals() {
          const feedUrls = [
            'https://cdn.jsdelivr.net/gh/usearchme12/electricbikesaffilate@main/deals.json',
            'https://raw.githubusercontent.com/usearchme12/electricbikesaffilate/main/deals.json',
            '<?php echo plugins_url('deals.json', __FILE__); ?>'
          ];

          for (const url of feedUrls) {
            try {
              const res = await fetch(url);
              if (res.ok) {
                const data = await res.json();
                if (data && data.deals && data.deals.length > 0) {
                  dealsList = data.deals;
                  rgbApplyFilters();
                  return;
                }
              }
            } catch(e) {
              console.warn('Feed fetch attempt failed:', url, e);
            }
          }
        }

        window.rgbFilter = function(cat, btn) {
          curCat = cat;
          document.querySelectorAll('.rgb-pill').forEach(b => b.classList.remove('active'));
          btn.classList.add('active');
          rgbApplyFilters();
        };

        window.rgbApplyFilters = function() {
          const search = (document.getElementById('rgbSearchInput')?.value || '').trim().toLowerCase();
          const sortMode = document.getElementById('rgbSortSelect')?.value || 'dealScore';
          const container = document.getElementById('rgbDealsContainer');

          let filtered = dealsList.filter(d => {
            if (search) {
              const title = (d.title || '').toLowerCase();
              const brand = (d.brand || '').toLowerCase();
              const ret = (d.retailer || '').toLowerCase();
              if (!title.includes(search) && !brand.includes(search) && !ret.includes(search)) return false;
            }
            if (curCat === 'mega') return d.discount_percentage >= 30 || d.savings_amount >= 400;
            if (curCat === 'budget') return d.sale_price <= 1000;
            if (curCat !== 'all' && d.category !== curCat) return false;
            return true;
          });

          filtered.sort((a, b) => {
            if (sortMode === 'dealScore') return (b.dealScore || 0) - (a.dealScore || 0);
            if (sortMode === 'discount') return b.discount_percentage - a.discount_percentage;
            if (sortMode === 'savings') return b.savings_amount - a.savings_amount;
            if (sortMode === 'price-asc') return a.sale_price - b.sale_price;
            if (sortMode === 'price-desc') return b.sale_price - a.sale_price;
            return 0;
          });

          document.getElementById('rgbStatusMeta').innerText = 'Showing ' + filtered.length + ' verified deals (Auto-updated daily)';

          container.innerHTML = filtered.map(d => {
            const sym = d.symbol || '£';
            const savings = Math.round(d.savings_amount).toLocaleString();
            return `
              <article class="rgb-card">
                <div class="rgb-badge-discount">SAVE ${sym}${savings} (${d.discount_percentage}% OFF)</div>
                <div class="rgb-card-img-wrap">
                  <img src="${d.image}" alt="${d.title}" class="rgb-card-img" loading="lazy" onerror="this.src='https://images.unsplash.com/photo-1571068316344-75bc76f77890?w=600'">
                </div>
                <div class="rgb-card-body">
                  <div class="rgb-row">
                    <span class="rgb-retailer">${d.retailer}</span>
                    <span class="rgb-score">Score: ${d.dealScore || 85}</span>
                  </div>
                  <h3 class="rgb-card-title">${d.title}</h3>
                  <div class="rgb-specs">
                    <div><span class="rgb-spec-lbl">Category</span><div class="rgb-spec-val">${d.category}</div></div>
                    <div><span class="rgb-spec-lbl">Motor</span><div class="rgb-spec-val">${d.motor_power}</div></div>
                  </div>
                  <div class="rgb-price-row">
                    <div>
                      <div class="rgb-sale-price">${sym}${d.sale_price.toLocaleString('en-GB', {minimumFractionDigits: 2})}</div>
                      ${d.rrp ? `<span class="rgb-rrp">Was ${sym}${d.rrp.toLocaleString('en-GB', {minimumFractionDigits: 2})}</span>` : ''}
                    </div>
                    <span class="rgb-savings">Save ${sym}${savings}</span>
                  </div>
                  <a href="${d.url}" target="_blank" rel="nofollow sponsored noopener" class="rgb-btn">
                    👉 View Deal at ${d.retailer} ➔
                  </a>
                </div>
              </article>
            `;
          }).join('');
        };

        rgbApplyFilters();
        fetchTopDeals();
      })();
    </script>
    <?php
    return ob_get_clean();
}

add_shortcode('ebike_deals', 'rgb_register_deal_finder_shortcode');
add_shortcode('ebike_deal_finder', 'rgb_register_deal_finder_shortcode');
