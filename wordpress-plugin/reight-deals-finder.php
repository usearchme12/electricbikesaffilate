<?php
/**
 * Plugin Name: Reight Good Bikes - E-Bike Deals Finder
 * Plugin URI: https://reightgoodbikes.co.uk/
 * Description: Embeds an interactive, multi-source UK Electric Bike Deals & Clearance Offers page via shortcode [ebike_deals]. Automatically syncs with the live cloud aggregator. Zero iframe layout, 100% mobile-optimized.
 * Version: 2.4.2
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


function rgb_register_deal_finder_shortcode($atts) {
    ob_start();
    ?>
    <div id="rgb-deal-finder-root" class="rgb-deal-finder-wrapper">
      <style>
        /* Eliminate ALL theme white gaps on the deals page */
        .page .container.content-wrapper,
        .single .container.content-wrapper,
        .content-wrapper {
          margin-top: 15px !important;
          padding-top: 0 !important;
        }
        .entry-header {
          margin-bottom: 10px !important;
        }
        .entry-content > p {
          margin-bottom: 12px !important;
        }
        .entry-content > p:empty,
        .entry-content > p.wp-block-paragraph:empty {
          display: none !important;
          margin: 0 !important;
          padding: 0 !important;
          height: 0 !important;
        }

        /* Seamless dark wrapper - ELIMINATES all white background and gaps inside deals */
        #rgb-deal-finder-root.rgb-deal-finder-wrapper,
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
          background: #090d16 !important;
          color: var(--rgb-text) !important;
          border-radius: 16px !important;
          padding: 1.75rem 1.25rem 2.5rem !important;
          box-shadow: 0 12px 40px rgba(0, 0, 0, 0.45) !important;
          margin: 0.75rem 0 2.5rem 0 !important;
          line-height: 1.4;
          width: 100%;
          box-sizing: border-box !important;
        }

        #rgb-deal-finder-root,
        #rgb-deal-finder-root * {
          box-sizing: border-box !important;
          font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Oxygen, Ubuntu, Cantarell, "Helvetica Neue", sans-serif !important;
        }

        .rgb-header {
          text-align: center;
          padding: 0.25rem 0.5rem 1rem;
        }
        .rgb-badge {
          display: inline-block;
          background: rgba(245, 158, 11, 0.15);
          border: 1px solid rgba(245, 158, 11, 0.35);
          color: var(--rgb-primary) !important;
          padding: 0.3rem 0.85rem;
          border-radius: 9999px;
          font-size: 0.75rem;
          font-weight: 800;
          margin-bottom: 0.5rem;
          text-transform: uppercase;
        }
        #rgb-deal-finder-root .rgb-header h2 {
          font-size: clamp(1.4rem, 3.5vw, 1.9rem) !important;
          font-weight: 800 !important;
          color: #ffffff !important;
          margin: 0 0 0.35rem 0 !important;
          line-height: 1.25 !important;
        }
        #rgb-deal-finder-root .rgb-header p {
          color: #94a3b8 !important;
          font-size: 0.92rem !important;
          max-width: 580px;
          margin: 0 auto !important;
          line-height: 1.4 !important;
        }

        /* Newsletter / Omnisend Card - Compact & Clean */
        #rgb-deal-finder-root .rgb-newsletter-card {
          background: linear-gradient(145deg, #131b2e 0%, #090d16 100%) !important;
          border: 1px solid var(--rgb-border) !important;
          border-top: 3px solid var(--rgb-primary) !important;
          border-radius: 12px;
          padding: 1.25rem 1.25rem;
          margin-bottom: 1.5rem;
          text-align: center;
          box-shadow: 0 10px 25px rgba(0, 0, 0, 0.35);
        }
        .rgb-newsletter-badge {
          display: inline-block;
          background: rgba(245, 158, 11, 0.15) !important;
          color: var(--rgb-primary) !important;
          font-size: 0.72rem;
          font-weight: 800;
          padding: 0.25rem 0.7rem;
          border-radius: 99px;
          text-transform: uppercase;
          margin-bottom: 0.5rem;
          letter-spacing: 0.04em;
        }
        #rgb-deal-finder-root .rgb-newsletter-card h3 {
          color: #ffffff !important;
          font-size: clamp(1.15rem, 2.5vw, 1.4rem) !important;
          font-weight: 800 !important;
          margin-top: 0 !important;
          margin-bottom: 0.35rem !important;
        }
        #rgb-deal-finder-root .rgb-newsletter-sub {
          color: var(--rgb-muted) !important;
          font-size: 0.88rem !important;
          max-width: 540px;
          margin: 0 auto 1rem !important;
          line-height: 1.4 !important;
        }
        #rgb-deal-finder-root .rgb-newsletter-trust {
          color: #64748b !important;
          font-size: 0.72rem !important;
          margin-top: 0.75rem !important;
          font-weight: 600;
        }

        /* Compact, Single-Row Responsive Filter Bar */
        #rgb-deal-finder-root .rgb-filter-bar {
          background: #131b2e !important;
          border: 1px solid var(--rgb-border) !important;
          border-radius: 12px;
          padding: 0.85rem 1.1rem !important;
          margin-bottom: 1.75rem !important;
          display: flex !important;
          flex-direction: column !important;
          gap: 0.75rem !important;
          box-shadow: 0 4px 20px rgba(0, 0, 0, 0.25) !important;
        }

        #rgb-deal-finder-root .rgb-search-row {
          display: flex !important;
          flex-direction: row !important;
          flex-wrap: nowrap !important;
          gap: 0.65rem !important;
          align-items: center !important;
          width: 100% !important;
        }
        @media (max-width: 820px) {
          #rgb-deal-finder-root .rgb-search-row {
            flex-wrap: wrap !important;
          }
        }

        #rgb-deal-finder-root input.rgb-search-input,
        #rgb-deal-finder-root .rgb-search-input {
          flex: 1 1 220px !important;
          min-width: 200px !important;
          height: 42px !important;
          min-height: 42px !important;
          max-height: 42px !important;
          background: #090d16 !important;
          border: 1px solid var(--rgb-border) !important;
          color: #ffffff !important;
          padding: 0.5rem 1rem !important;
          border-radius: 8px !important;
          font-size: 0.88rem !important;
          line-height: 1.2 !important;
          outline: none !important;
          margin: 0 !important;
        }
        #rgb-deal-finder-root .rgb-search-input:focus {
          border-color: var(--rgb-primary) !important;
          box-shadow: 0 0 0 2px rgba(245, 158, 11, 0.2) !important;
        }

        #rgb-deal-finder-root select.rgb-dropdown,
        #rgb-deal-finder-root .rgb-dropdown {
          flex: 0 1 auto !important;
          width: auto !important;
          min-width: 180px !important;
          max-width: 260px !important;
          height: 42px !important;
          min-height: 42px !important;
          max-height: 42px !important;
          background: #090d16 !important;
          border: 1px solid var(--rgb-border) !important;
          color: #ffffff !important;
          padding: 0.5rem 2rem 0.5rem 0.85rem !important;
          border-radius: 8px !important;
          font-size: 0.85rem !important;
          line-height: 1.2 !important;
          outline: none !important;
          cursor: pointer !important;
          margin: 0 !important;
          -webkit-appearance: none !important;
          -moz-appearance: none !important;
          appearance: none !important;
          background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 24 24' stroke='%2394a3b8'%3E%3Cpath stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M19 9l-7 7-7-7'%3E%3C/path%3E%3C/svg%3E") !important;
          background-repeat: no-repeat !important;
          background-position: right 0.75rem center !important;
          background-size: 1rem !important;
        }
        #rgb-deal-finder-root select.rgb-dropdown:focus {
          border-color: var(--rgb-primary) !important;
          box-shadow: 0 0 0 2px rgba(245, 158, 11, 0.2) !important;
        }

        #rgb-deal-finder-root .rgb-pills-row {
          display: flex !important;
          gap: 0.4rem !important;
          flex-wrap: wrap !important;
          align-items: center !important;
          width: 100% !important;
          margin: 0 !important;
          padding: 0 !important;
        }
        #rgb-deal-finder-root .rgb-pill {
          height: 32px !important;
          min-height: 32px !important;
          line-height: 1.2 !important;
          background: #090d16 !important;
          border: 1px solid var(--rgb-border) !important;
          color: var(--rgb-muted) !important;
          padding: 0.35rem 0.75rem !important;
          border-radius: 6px !important;
          font-size: 0.8rem !important;
          font-weight: 600 !important;
          cursor: pointer !important;
          margin: 0 !important;
          display: inline-flex !important;
          align-items: center !important;
          transition: all 0.15s ease !important;
        }
        #rgb-deal-finder-root .rgb-pill:hover {
          background: #1e293b !important;
          color: #ffffff !important;
          border-color: #334155 !important;
        }
        #rgb-deal-finder-root .rgb-pill.active {
          background: var(--rgb-primary) !important;
          border-color: var(--rgb-primary) !important;
          color: #000000 !important;
          font-weight: 800 !important;
        }
        #rgb-deal-finder-root .rgb-pill.new.active,
        #rgb-deal-finder-root .rgb-pill.legal.active {
          background: #10b981 !important;
          border-color: #10b981 !important;
          color: #000000 !important;
          font-weight: 800 !important;
        }

        .rgb-status-meta {
          font-size: 0.85rem;
          color: #64748b;
          margin-bottom: 1rem;
          font-weight: 600;
        }

                
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

        #rgb-deal-finder-root .rgb-grid,
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
        }

        .rgb-card-body { padding: 1.25rem; display: flex; flex-direction: column; flex: 1; }

        .rgb-row { display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.4rem; }
        .rgb-retailer { font-size: 0.75rem; font-weight: 800; color: var(--rgb-blue) !important; text-transform: uppercase; }
        .rgb-score { font-size: 0.72rem; font-weight: 800; color: var(--rgb-primary) !important; background: rgba(245,158,11,0.1) !important; padding: 0.15rem 0.4rem; border-radius: 4px; }

        /* CRITICAL CONTRAST FIX: Explicit white title with high specificity and !important */
        #rgb-deal-finder-root .rgb-card-title,
        .rgb-deal-finder-wrapper .rgb-card-title,
        .rgb-deal-finder-wrapper h3.rgb-card-title,
        .rgb-card h3.rgb-card-title,
        .rgb-deal-finder-wrapper h3 {
          font-size: 1.05rem !important;
          font-weight: 700 !important;
          color: #ffffff !important;
          line-height: 1.35 !important;
          margin-top: 0 !important;
          margin-bottom: 0.75rem !important;
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
          background: rgba(9, 13, 22, 0.6) !important;
          border: 1px solid var(--rgb-border) !important;
          border-radius: 6px;
          padding: 0.5rem 0.75rem;
          margin-bottom: 0.9rem;
          font-size: 0.75rem;
        }
        .rgb-spec-lbl { color: var(--rgb-muted) !important; font-size: 0.65rem; text-transform: uppercase; }
        .rgb-spec-val { font-weight: 700; color: #f1f5f9 !important; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }

        .rgb-price-row {
          display: flex;
          justify-content: space-between;
          align-items: baseline;
          margin-top: auto;
          padding-top: 0.75rem;
          border-top: 1px solid var(--rgb-border) !important;
          margin-bottom: 0.9rem;
        }
        .rgb-sale-price { font-size: 1.55rem; font-weight: 800; color: var(--rgb-primary) !important; }
        .rgb-rrp { font-size: 0.85rem; color: var(--rgb-muted) !important; text-decoration: line-through; margin-left: 0.3rem; }
        .rgb-savings { font-size: 0.78rem; font-weight: 800; color: var(--rgb-neon) !important; }

        .rgb-btn {
          display: block;
          width: 100%;
          text-align: center;
          background: var(--rgb-primary) !important;
          color: #000000 !important;
          font-weight: 800;
          font-size: 0.9rem;
          padding: 0.75rem 1rem;
          border-radius: 6px;
          text-decoration: none;
          transition: background 0.2s;
        }
        .rgb-btn:hover { background: var(--rgb-primary-hover) !important; color: #000000 !important; }
      </style>

      <div class="rgb-header">
        <span class="rgb-badge" id="rgbHeaderBadge">⚡ Live Multi-Source Deals • Updated Today</span>
        <h2>Top Electric Bike Discounts & Deals</h2>
        <p>Real-time price cuts from verified UK e-bike specialists and direct brands.</p>
      </div>

      <!-- Omnisend Weekly Deals Subscription Banner -->
      <div class="rgb-newsletter-card" id="rgbNewsletterCard">
        <div class="rgb-newsletter-badge">⚡ WEEKLY PRICE DROP DIGEST</div>
        <h3>⚡ Reight Good E-Bike Deals — Delivered Weekly</h3>
        <p class="rgb-newsletter-sub">We scan UK retailers daily for secret price cuts and clearance stock. Join our weekly digest and grab top e-bike bargains before they sell out.</p>

        <!-- Omnisend Form Embed -->
        <div id="omnisend-embedded-v2-6a8986b8c8c1603e9077a360"></div>

        <p class="rgb-newsletter-trust">🔒 100% Free • Sent Weekly • Unsubscribe Anytime</p>
      </div>

      <div class="rgb-filter-bar">
        <div class="rgb-search-row">
          <input type="text" id="rgbSearchInput" class="rgb-search-input" placeholder="🔍 Search e-bikes, brands, Bosch motors, folding..." oninput="rgbApplyFilters()">
          
          <select id="rgbSortSelect" class="rgb-dropdown" onchange="rgbApplyFilters()">
            <option value="valueScore">🏆 Best Value Score</option>
            <option value="savings">💰 Biggest Cash Savings (£)</option>
            <option value="newest">✨ Newest Drops First</option>
            <option value="discount">🔥 Highest % Discount</option>
            <option value="price-asc">🏷️ Lowest Price</option>
            <option value="price-desc">💎 Highest Price</option>
          </select>
          <select id="rgbPriceSelect" class="rgb-dropdown" onchange="rgbApplyFilters()">
            <option value="all">🏷️ All Price Ranges</option>
            <option value="under1000">⚡ Under £1,000</option>
            <option value="1000-2500">🚲 £1,000 – £2,500</option>
            <option value="2500-5000">💎 £2,500 – £5,000</option>
            <option value="over5000">👑 Over £5,000</option>
          </select>
        </div>

        <div class="rgb-pills-row">
          <button class="rgb-pill active" data-cat="all" onclick="rgbFilter('all', this)">All Deals</button>
          <button class="rgb-pill legal" data-cat="legal" onclick="rgbFilter('legal', this)">✅ Road Legal Only</button>
          <button class="rgb-pill new" data-cat="new" onclick="rgbFilter('new', this)">✨ Just Added</button>
          <button class="rgb-pill mega" data-cat="mega" onclick="rgbFilter('mega', this)">🔥 Mega Deals (30%+)</button>
          <button class="rgb-pill" data-cat="budget" onclick="rgbFilter('budget', this)">⚡ Under £1,000</button>
          <button class="rgb-pill" data-cat="Fat Tyre" onclick="rgbFilter('Fat Tyre', this)">🛞 Fat Tyre</button>
          <button class="rgb-pill" data-cat="Mountain" onclick="rgbFilter('Mountain', this)">Mountain</button>
          <button class="rgb-pill" data-cat="Commuter" onclick="rgbFilter('Commuter', this)">Commuter</button>
          <button class="rgb-pill" data-cat="Folding" onclick="rgbFilter('Folding', this)">Folding</button>
        </div>
      </div>

      <div class="rgb-status-meta" id="rgbStatusMeta">Loading live deals feed...</div>

      <div id="rgbDealsContainer">
        <!-- Cards inserted by script -->
      </div>
    </div>

    <script data-no-optimize="1" data-no-defer="1">
      (function() {
        let dealsList = [{"id":"engwe_uk_8241398710454","title":"P275 SE (Sky Blue)","brand":"engwe uk","retailer":"Engwe UK Official","country":"UK","currency":"GBP","symbol":"£","category":"Commuter","dealBucket":"🔥 Mega Deal","dealScore":90.3,"valueScore":90.3,"motor_power":"250W Road Legal","battery":"468Wh Lithium-Ion","range_miles":"See retailer listing","max_speed":"15.5 mph (EAPC)","is_uk_legal":true,"rrp":1499,"sale_price":849,"price_from":true,"savings_amount":650,"discount_percentage":43,"image":"https://cdn.shopify.com/s/files/1/0627/1385/6182/files/P275SE_01_7f24b64d-face-433f-9bcc-1f87be91b8c3.jpg?v=1767074661","url":"https://www.awin1.com/cread.php?awinmid=65774&awinaffid=3040709&clickref=dealspage&ued=https%3A%2F%2Fengwe-bikes-uk.com%2Fproducts%2Fengwe-p275-se%3Fvariant%3D44044580389046","badge_text":"SAVE £650 (43% OFF)","first_seen":"2026-09-09","is_new":true,"is_cached":false,"last_verified":"2026-09-10T20:25:06.240Z","lowest_price_30d":849,"is_lowest_price_30d":true,"price_drop_amount":0,"last_checked":"2026-09-10T20:25:13.587Z"},{"id":"dyu_cycle_uk_15244701237616","title":"M20 All-Terrain Long-Range Electric Bike (Brown)","brand":"DYU UK","retailer":"DYU UK Official","country":"UK","currency":"GBP","symbol":"£","category":"Fat Tyre","dealBucket":"🔥 Mega Deal","dealScore":89,"valueScore":89,"motor_power":"Specification not confirmed","battery":"Specification not confirmed","range_miles":"See retailer listing","max_speed":"Check retailer listing","is_uk_legal":false,"rrp":1399,"sale_price":799,"price_from":false,"savings_amount":600,"discount_percentage":43,"image":"https://cdn.shopify.com/s/files/1/0627/0579/5236/files/M20_c00579a9-e920-46f5-a6e8-eda0ad2a8e9d.jpg?v=1787813197","url":"https://uk.dyucycle.com/products/m20-all-terrain-long-range-electric-bike?variant=54224939516272","badge_text":"SAVE £600 (43% OFF)","first_seen":"2026-09-09","is_new":true,"is_cached":false,"last_verified":"2026-09-10T20:25:07.674Z","lowest_price_30d":799,"is_lowest_price_30d":true,"price_drop_amount":0,"last_checked":"2026-09-10T20:25:13.587Z"},{"id":"fiido_uk_9812103627053","title":"Fiido C11 Pro: Lightweight, 104km Long Range, IP54 Waterproof (Standard)","brand":"Fiido UK","retailer":"Fiido UK Official","country":"UK","currency":"GBP","symbol":"£","category":"Commuter","dealBucket":"🔥 Mega Deal","dealScore":86,"valueScore":86,"motor_power":"Specification not confirmed","battery":"Specification not confirmed","range_miles":"See retailer listing","max_speed":"Check retailer listing","is_uk_legal":false,"rrp":1635,"sale_price":999,"price_from":false,"savings_amount":636,"discount_percentage":39,"image":"https://cdn.shopify.com/s/files/1/0814/1232/5677/files/1-c11-pro_ffb8eb92-7116-4f18-bb8c-42b9848dd0b2.webp?v=1786689927","url":"https://uk.fiido.com/products/fiido-c11-pro-city-e-bike?variant=51957821767981","badge_text":"SAVE £636 (39% OFF)","first_seen":"2026-09-10","is_new":true,"is_cached":false,"last_verified":"2026-09-10T20:25:10.528Z","lowest_price_30d":999,"is_lowest_price_30d":true,"price_drop_amount":0,"last_checked":"2026-09-10T20:25:13.587Z"},{"id":"fiido_uk_8646545178925","title":"Fiido C21 E-Gravel & City E-Bike (Grey-L (5'7\" - 6'5\") (170 cm - 195 cm) / Standard)","brand":"fiido","retailer":"Fiido UK Official","country":"UK","currency":"GBP","symbol":"£","category":"Commuter","dealBucket":"🔥 Mega Deal","dealScore":86,"valueScore":86,"motor_power":"Specification not confirmed","battery":"Specification not confirmed","range_miles":"See retailer listing","max_speed":"Check retailer listing","is_uk_legal":false,"rrp":1635,"sale_price":999,"price_from":true,"savings_amount":636,"discount_percentage":39,"image":"https://cdn.shopify.com/s/files/1/0814/1232/5677/files/M.webp?v=1758698312","url":"https://uk.fiido.com/products/fiido-c21-lightweight-step-over-urban-gravel-ebikes?variant=46489900745005","badge_text":"SAVE £636 (39% OFF)","first_seen":"2026-09-10","is_new":true,"is_cached":false,"last_verified":"2026-09-10T20:25:10.778Z","lowest_price_30d":999,"is_lowest_price_30d":true,"price_drop_amount":0,"last_checked":"2026-09-10T20:25:13.587Z"},{"id":"cyrusher_uk_8970135240917","title":"Rumble 2.0 Fat Tire E-Bike (Blue)","brand":"Cyrusher United Kingdom","retailer":"Cyrusher UK","country":"UK","currency":"GBP","symbol":"£","category":"Fat Tyre","dealBucket":"🔥 Mega Deal","dealScore":79.3,"valueScore":79.3,"motor_power":"Specification not confirmed","battery":"Specification not confirmed","range_miles":"See retailer listing","max_speed":"Check retailer listing","is_uk_legal":false,"rrp":1399,"sale_price":899,"price_from":true,"savings_amount":500,"discount_percentage":36,"image":"https://cdn.shopify.com/s/files/1/0627/5861/7301/files/CyrusherRumble2.0E-Bike-Blue-1.jpg?v=1775906946","url":"https://www.cyrusher.co.uk/products/rumble-2-0?variant=51487534645461","badge_text":"SAVE £500 (36% OFF)","first_seen":"2026-09-10","is_new":true,"is_cached":false,"last_verified":"2026-09-10T20:25:12.569Z","lowest_price_30d":899,"is_lowest_price_30d":true,"price_drop_amount":0,"last_checked":"2026-09-10T20:25:13.587Z"},{"id":"e_bikeshop_co_uk_15205986697600","title":"Orbea Diem 20 Electric Bike (S 44cm / Ivory)","brand":"Orbea Electric Bikes","retailer":"E-BikeShop UK","country":"UK","currency":"GBP","symbol":"£","category":"Commuter","dealBucket":"🔥 Mega Deal","dealScore":79,"valueScore":79,"motor_power":"Specification not confirmed","battery":"630Wh Lithium-Ion","range_miles":"30 - 140 Miles","max_speed":"Check retailer listing","is_uk_legal":false,"rrp":4299,"sale_price":2399,"price_from":true,"savings_amount":1900,"discount_percentage":44,"image":"https://cdn.shopify.com/s/files/1/0609/4838/1934/files/Orbea-Diem-20-2025-White-Electric-Bike.jpg?v=1766064958","url":"https://www.e-bikeshop.co.uk/products/electric-bike-orbea-diem-20-2025?variant=56106341990784","badge_text":"SAVE £1900 (44% OFF)","first_seen":"2026-09-10","is_new":true,"is_cached":false,"last_verified":"2026-09-10T20:25:03.694Z","lowest_price_30d":2399,"is_lowest_price_30d":true,"price_drop_amount":0,"last_checked":"2026-09-10T20:25:13.587Z"},{"id":"engwe_uk_8391383548086","title":"P275 SE Combo (Grey / Grey)","brand":"engwe uk","retailer":"Engwe UK Official","country":"UK","currency":"GBP","symbol":"£","category":"Cargo","dealBucket":"🔥 Mega Deal","dealScore":79,"valueScore":79,"motor_power":"250W Road Legal","battery":"468Wh Lithium-Ion","range_miles":"See retailer listing","max_speed":"15.5 mph (EAPC)","is_uk_legal":true,"rrp":2948,"sale_price":1648,"price_from":true,"savings_amount":1300,"discount_percentage":44,"image":"https://cdn.shopify.com/s/files/1/0627/1385/6182/files/P275_SE_1.jpg?v=1768809378","url":"https://www.awin1.com/cread.php?awinmid=65774&awinaffid=3040709&clickref=dealspage&ued=https%3A%2F%2Fengwe-bikes-uk.com%2Fproducts%2Fengwe-p275-se-combo%3Fvariant%3D44510305747126","badge_text":"SAVE £1300 (44% OFF)","first_seen":"2026-09-09","is_new":true,"is_cached":false,"last_verified":"2026-09-10T20:25:06.240Z","lowest_price_30d":1648,"is_lowest_price_30d":true,"price_drop_amount":0,"last_checked":"2026-09-10T20:25:13.587Z"},{"id":"heybike_uk_8205448446267","title":"EC 1-ST Commuter E-Bike (Teal Blue / Standard( for 155 - 185 cm))","brand":"Heybike UK","retailer":"Heybike UK","country":"UK","currency":"GBP","symbol":"£","category":"Commuter","dealBucket":"🔥 Mega Deal","dealScore":76,"valueScore":76,"motor_power":"Specification not confirmed","battery":"Specification not confirmed","range_miles":"See retailer listing","max_speed":"Check retailer listing","is_uk_legal":false,"rrp":1699,"sale_price":1099,"price_from":false,"savings_amount":600,"discount_percentage":35,"image":"https://cdn.shopify.com/s/files/1/0730/5792/7483/files/EC1-ST_d7a529d0-c272-4b25-872f-b144e16befe4.png?v=1750042205","url":"https://heybike.co.uk/products/ec-1-st-1?variant=47142595199291","badge_text":"SAVE £600 (35% OFF)","first_seen":"2026-09-10","is_new":true,"is_cached":false,"last_verified":"2026-09-10T20:25:07.421Z","lowest_price_30d":1099,"is_lowest_price_30d":true,"price_drop_amount":0,"last_checked":"2026-09-10T20:25:13.587Z"},{"id":"heybike_uk_8370053808443","title":"EC 1 Commuter E-Bike (Buttery White / L - for 165 - 190 cm)","brand":"Heybike UK","retailer":"Heybike UK","country":"UK","currency":"GBP","symbol":"£","category":"Commuter","dealBucket":"🔥 Mega Deal","dealScore":76,"valueScore":76,"motor_power":"Specification not confirmed","battery":"Specification not confirmed","range_miles":"See retailer listing","max_speed":"Check retailer listing","is_uk_legal":false,"rrp":1699,"sale_price":1099,"price_from":false,"savings_amount":600,"discount_percentage":35,"image":"https://cdn.shopify.com/s/files/1/0730/5792/7483/files/EC1_7ef5773e-c729-4c47-a99b-39bc78075802.png?v=1751970130","url":"https://heybike.co.uk/products/ec-1?variant=45212938535227","badge_text":"SAVE £600 (35% OFF)","first_seen":"2026-09-09","is_new":true,"is_cached":false,"last_verified":"2026-09-10T20:25:07.421Z","lowest_price_30d":1099,"is_lowest_price_30d":true,"price_drop_amount":0,"last_checked":"2026-09-10T20:25:13.587Z"},{"id":"dyu_cycle_uk_8271011086500","title":"DYU C6 26 Inch City Electric Bike (Black)","brand":"DYU","retailer":"DYU UK Official","country":"UK","currency":"GBP","symbol":"£","category":"Commuter","dealBucket":"🔥 Mega Deal","dealScore":76,"valueScore":76,"motor_power":"250W Road Legal","battery":"450Wh (36V 12.5Ah)","range_miles":"See retailer listing","max_speed":"15.5 mph (EAPC)","is_uk_legal":true,"rrp":799,"sale_price":499,"price_from":false,"savings_amount":300,"discount_percentage":38,"image":"https://cdn.shopify.com/s/files/1/0627/0579/5236/files/01_6af4da4e-08ff-4d40-b7d1-5aecd83b830a.jpg?v=1760495883","url":"https://uk.dyucycle.com/products/c6-26-inch-city-electric-bike?variant=44675867082916","badge_text":"SAVE £300 (38% OFF)","first_seen":"2026-09-09","is_new":true,"is_cached":false,"last_verified":"2026-09-10T20:25:07.674Z","lowest_price_30d":499,"is_lowest_price_30d":true,"price_drop_amount":0,"last_checked":"2026-09-10T20:25:13.587Z"},{"id":"e_bikeshop_co_uk_8142661452014","title":"Orbea Vibe H30 EQ Mid Uni (S 46cm / Ivory)","brand":"Orbea Electric Bikes","retailer":"E-BikeShop UK","country":"UK","currency":"GBP","symbol":"£","category":"Commuter","dealBucket":"🔥 Mega Deal","dealScore":75,"valueScore":75,"motor_power":"250W Mid-Drive (EAPC)","battery":"250Wh Lithium-Ion","range_miles":"30 - 80 Miles","max_speed":"15.5 mph (EAPC)","is_uk_legal":true,"rrp":3099,"sale_price":1849,"price_from":false,"savings_amount":1250,"discount_percentage":40,"image":"https://cdn.shopify.com/s/files/1/0609/4838/1934/files/Orbea-Vibe-H30-EQ-Mid-2024-White-Electric-Bike.jpg?v=1701524169","url":"https://www.e-bikeshop.co.uk/products/electric-bike-orbea-vibe-h30-eq-mid-2024-uni?variant=44384013910254","badge_text":"SAVE £1250 (40% OFF)","first_seen":"2026-09-10","is_new":true,"is_cached":false,"last_verified":"2026-09-10T20:25:05.606Z","lowest_price_30d":1849,"is_lowest_price_30d":true,"price_drop_amount":0,"last_checked":"2026-09-10T20:25:13.587Z"},{"id":"cyrusher_uk_9237707325653","title":"Zenith Fat Tire E-Bike (Titanium)","brand":"Cyrusher United Kingdom","retailer":"Cyrusher UK","country":"UK","currency":"GBP","symbol":"£","category":"Fat Tyre","dealBucket":"🔥 Mega Deal","dealScore":74,"valueScore":74,"motor_power":"Specification not confirmed","battery":"Specification not confirmed","range_miles":"See retailer listing","max_speed":"Check retailer listing","is_uk_legal":false,"rrp":1799,"sale_price":1199,"price_from":true,"savings_amount":600,"discount_percentage":33,"image":"https://cdn.shopify.com/s/files/1/0627/5861/7301/files/Cyrusher-Zenith-Fat-Tire-E-Bike-Main-Green-1.jpg?v=1776408962","url":"https://www.cyrusher.co.uk/products/zenith?variant=52977025450197","badge_text":"SAVE £600 (33% OFF)","first_seen":"2026-09-10","is_new":true,"is_cached":false,"last_verified":"2026-09-10T20:25:12.569Z","lowest_price_30d":1199,"is_lowest_price_30d":true,"price_drop_amount":0,"last_checked":"2026-09-10T20:25:13.587Z"},{"id":"cyrusher_uk_8972121047253","title":"Aura Fat Tire E-Bike (Blue)","brand":"Cyrusher United Kingdom","retailer":"Cyrusher UK","country":"UK","currency":"GBP","symbol":"£","category":"Fat Tyre","dealBucket":"🔥 Mega Deal","dealScore":74,"valueScore":74,"motor_power":"250W Road Legal","battery":"780Wh Lithium-Ion","range_miles":"See retailer listing","max_speed":"15.5 mph (EAPC)","is_uk_legal":true,"rrp":1799,"sale_price":1199,"price_from":true,"savings_amount":600,"discount_percentage":33,"image":"https://cdn.shopify.com/s/files/1/0627/5861/7301/files/Cyrusher-Aura-Fat-Tire-E-Bike-Blue-00.jpg?v=1767966846","url":"https://www.cyrusher.co.uk/products/aura?variant=51493030592725","badge_text":"SAVE £600 (33% OFF)","first_seen":"2026-09-10","is_new":true,"is_cached":false,"last_verified":"2026-09-10T20:25:12.569Z","lowest_price_30d":1199,"is_lowest_price_30d":true,"price_drop_amount":0,"last_checked":"2026-09-10T20:25:13.587Z"},{"id":"cyrusher_uk_8796910616789","title":"Kommoda 3.0 , Step-through Ebike (Green)","brand":"Cyrusher United Kingdom","retailer":"Cyrusher UK","country":"UK","currency":"GBP","symbol":"£","category":"Fat Tyre","dealBucket":"🔥 Mega Deal","dealScore":74,"valueScore":74,"motor_power":"Specification not confirmed","battery":"Specification not confirmed","range_miles":"See retailer listing","max_speed":"Check retailer listing","is_uk_legal":false,"rrp":1799,"sale_price":1199,"price_from":true,"savings_amount":600,"discount_percentage":33,"image":"https://cdn.shopify.com/s/files/1/0627/5861/7301/files/Cyrusher-E-Bike-Kommoda3.0-Main-Green-1.jpg?v=1774320844","url":"https://www.cyrusher.co.uk/products/kommoda-2-0?variant=50445615399125","badge_text":"SAVE £600 (33% OFF)","first_seen":"2026-09-09","is_new":true,"is_cached":false,"last_verified":"2026-09-10T20:25:12.569Z","lowest_price_30d":1199,"is_lowest_price_30d":true,"price_drop_amount":0,"last_checked":"2026-09-10T20:25:13.587Z"},{"id":"fiido_uk_9391047573805","title":"Fiido Air Ultra-Light Carbon Fiber E-Bike (M | 5'5\" (165cm) - 5'9\" (175cm))","brand":"Fiido.uk","retailer":"Fiido UK Official","country":"UK","currency":"GBP","symbol":"£","category":"Commuter","dealBucket":"🔥 Mega Deal","dealScore":71,"valueScore":71,"motor_power":"Specification not confirmed","battery":"Specification not confirmed","range_miles":"See retailer listing","max_speed":"Check retailer listing","is_uk_legal":false,"rrp":2545,"sale_price":1636,"price_from":true,"savings_amount":909,"discount_percentage":36,"image":"https://cdn.shopify.com/s/files/1/0814/1232/5677/files/c31-img-1.webp?v=1758853049","url":"https://uk.fiido.com/products/fiido-air-carbon-fiber-electric-bike?variant=48919061201197","badge_text":"SAVE £909 (36% OFF)","first_seen":"2026-09-10","is_new":true,"is_cached":false,"last_verified":"2026-09-10T20:25:10.528Z","lowest_price_30d":1636,"is_lowest_price_30d":true,"price_drop_amount":0,"last_checked":"2026-09-10T20:25:13.587Z"},{"id":"dyu_cycle_uk_8271012069540","title":"DYU D3F 14 Inch Mini Folding Electric Bike (Black)","brand":"DYU","retailer":"DYU UK Official","country":"UK","currency":"GBP","symbol":"£","category":"Folding","dealBucket":"🔥 Mega Deal","dealScore":70.1,"valueScore":70.1,"motor_power":"250W Road Legal","battery":"360Wh (36V 10Ah)","range_miles":"See retailer listing","max_speed":"15.5 mph (EAPC)","is_uk_legal":true,"rrp":549,"sale_price":359,"price_from":false,"savings_amount":190,"discount_percentage":35,"image":"https://cdn.shopify.com/s/files/1/0627/0579/5236/products/6_fb537967-18f1-4a7f-8dbd-ba2eef5e34dc.jpg?v=1760495806","url":"https://uk.dyucycle.com/products/dyu-small-electric-bike-d3f?variant=44675870490788","badge_text":"SAVE £190 (35% OFF)","first_seen":"2026-09-09","is_new":true,"is_cached":false,"last_verified":"2026-09-10T20:25:07.674Z","lowest_price_30d":359,"is_lowest_price_30d":true,"price_drop_amount":0,"last_checked":"2026-09-10T20:25:13.587Z"},{"id":"e_bikeshop_co_uk_8142670495982","title":"Orbea Diem 30 Electric Bike (S 44cm / Grey)","brand":"Orbea Electric Bikes","retailer":"E-BikeShop UK","country":"UK","currency":"GBP","symbol":"£","category":"Commuter","dealBucket":"🔥 Mega Deal","dealScore":69,"valueScore":69,"motor_power":"Specification not confirmed","battery":"540Wh Lithium-Ion","range_miles":"30 - 130 Miles","max_speed":"Check retailer listing","is_uk_legal":false,"rrp":3499,"sale_price":2299,"price_from":true,"savings_amount":1200,"discount_percentage":34,"image":"https://cdn.shopify.com/s/files/1/0609/4838/1934/files/Orbea-Diem-30-2025-Green-Electric-Bike.jpg?v=1728126408","url":"https://www.e-bikeshop.co.uk/products/electric-bike-orbea-diem-30-2025?variant=44384059261166","badge_text":"SAVE £1200 (34% OFF)","first_seen":"2026-09-10","is_new":true,"is_cached":false,"last_verified":"2026-09-10T20:25:04.760Z","lowest_price_30d":2299,"is_lowest_price_30d":true,"price_drop_amount":0,"last_checked":"2026-09-10T20:25:13.587Z"},{"id":"e_bikeshop_co_uk_8169071608046","title":"Scott Voltage eRide 920 (M 42cm)","brand":"Scott Electric Bikes","retailer":"E-BikeShop UK","country":"UK","currency":"GBP","symbol":"£","category":"Commuter","dealBucket":"🔥 Mega Deal","dealScore":69,"valueScore":69,"motor_power":"Specification not confirmed","battery":"360Wh Lithium-Ion","range_miles":"30 - 100 Miles","max_speed":"Check retailer listing","is_uk_legal":false,"rrp":6099,"sale_price":3399,"price_from":true,"savings_amount":2700,"discount_percentage":44,"image":"https://cdn.shopify.com/s/files/1/0609/4838/1934/files/Scott-Voltage-eRide-920-2025-Electric-Bike.jpg?v=1735041322","url":"https://www.e-bikeshop.co.uk/products/electric-bike-scott-voltage-eride-920-2025?variant=44438689939694","badge_text":"SAVE £2700 (44% OFF)","first_seen":"2026-09-10","is_new":true,"is_cached":false,"last_verified":"2026-09-10T20:25:04.760Z","lowest_price_30d":3399,"is_lowest_price_30d":true,"price_drop_amount":0,"last_checked":"2026-09-10T20:25:13.587Z"},{"id":"pedalgo_uk_10304863633672","title":"Aitour Heal Mini - Folding Electric Trike (Blue)","brand":"Aitour","retailer":"PedalGo UK","country":"UK","currency":"GBP","symbol":"£","category":"Folding","dealBucket":"🔥 Mega Deal","dealScore":68,"valueScore":68,"motor_power":"250W Road Legal","battery":"468Wh (36V 13Ah)","range_miles":"See retailer listing","max_speed":"15.5 mph (EAPC)","is_uk_legal":true,"rrp":2999,"sale_price":2000,"price_from":false,"savings_amount":999,"discount_percentage":33,"image":"https://cdn.shopify.com/s/files/1/0911/9727/6424/files/Untitled_design_14.jpg?v=1749385493","url":"https://www.awin1.com/cread.php?awinmid=114770&awinaffid=3040709&clickref=dealspage&ued=https%3A%2F%2Fpedalgo.co.uk%2Fproducts%2Faitour-heal-mini-folding-electric-trike%3Fvariant%3D51656256848136","badge_text":"SAVE £999 (33% OFF)","first_seen":"2026-09-10","is_new":true,"is_cached":false,"last_verified":"2026-09-10T20:25:09.390Z","lowest_price_30d":2000,"is_lowest_price_30d":true,"price_drop_amount":0,"last_checked":"2026-09-10T20:25:13.587Z"},{"id":"fiido_uk_8646528860461","title":"Fiido M1 Pro Fat Tire Electric Bike (Standard)","brand":"fiido","retailer":"Fiido UK Official","country":"UK","currency":"GBP","symbol":"£","category":"Fat Tyre","dealBucket":"Standard Deal","dealScore":66.7,"valueScore":66.7,"motor_power":"Specification not confirmed","battery":"Specification not confirmed","range_miles":"See retailer listing","max_speed":"Check retailer listing","is_uk_legal":false,"rrp":1363,"sale_price":999,"price_from":true,"savings_amount":364,"discount_percentage":27,"image":"https://cdn.shopify.com/s/files/1/0814/1232/5677/files/7-m1-pro_17d47553-11cc-472f-83af-63d7eae68b35.webp?v=1775184005","url":"https://uk.fiido.com/products/fiido-m1-pro-fat-tire-electric-bike?variant=51957837758765","badge_text":"SAVE £364 (27% OFF)","first_seen":"2026-09-10","is_new":true,"is_cached":false,"last_verified":"2026-09-10T20:25:10.528Z","lowest_price_30d":999,"is_lowest_price_30d":true,"price_drop_amount":0,"last_checked":"2026-09-10T20:25:13.587Z"},{"id":"e_bikeshop_co_uk_8640443187438","title":"Orbea Gain M30 105 Electric Road Bike (XL 54cm / Wine Red)","brand":"Orbea Electric Bikes","retailer":"E-BikeShop UK","country":"UK","currency":"GBP","symbol":"£","category":"Commuter","dealBucket":"🔥 Mega Deal","dealScore":65,"valueScore":65,"motor_power":"250W Mid-Drive (EAPC)","battery":"353Wh Lithium-Ion","range_miles":"30 - 120 Miles","max_speed":"15.5 mph (EAPC)","is_uk_legal":true,"rrp":5299,"sale_price":3199,"price_from":false,"savings_amount":2100,"discount_percentage":40,"image":"https://cdn.shopify.com/s/files/1/0609/4838/1934/files/Orbea-Gain-M30-2025-Electric-Road-Bike-Red-Wine-Carbon-View.jpg?v=1724256107","url":"https://www.e-bikeshop.co.uk/products/electric-bike-orbea-gain-m30-2024?variant=45688151015662","badge_text":"SAVE £2100 (40% OFF)","first_seen":"2026-09-10","is_new":true,"is_cached":false,"last_verified":"2026-09-10T20:25:04.760Z","lowest_price_30d":3199,"is_lowest_price_30d":true,"price_drop_amount":0,"last_checked":"2026-09-10T20:25:13.587Z"},{"id":"e_bikeshop_co_uk_8169063448814","title":"Scott Voltage eRide 900 Tuned (S 40cm)","brand":"Scott Electric Bikes","retailer":"E-BikeShop UK","country":"UK","currency":"GBP","symbol":"£","category":"Commuter","dealBucket":"🔥 Mega Deal","dealScore":65,"valueScore":65,"motor_power":"Specification not confirmed","battery":"360Wh Lithium-Ion","range_miles":"30 - 100 Miles","max_speed":"Check retailer listing","is_uk_legal":false,"rrp":10099,"sale_price":5599,"price_from":true,"savings_amount":4500,"discount_percentage":45,"image":"https://cdn.shopify.com/s/files/1/0609/4838/1934/files/Scott-Voltage-eRide-900-Tuned-2025-Electric-Bike.jpg?v=1735040058","url":"https://www.e-bikeshop.co.uk/products/electric-bike-scott-voltage-eride-900-tuned-2025?variant=44438676209902","badge_text":"SAVE £4500 (45% OFF)","first_seen":"2026-09-10","is_new":true,"is_cached":false,"last_verified":"2026-09-10T20:25:04.760Z","lowest_price_30d":5599,"is_lowest_price_30d":true,"price_drop_amount":0,"last_checked":"2026-09-10T20:25:13.587Z"},{"id":"cyrusher_uk_9445342740693","title":"(Preorder)Sonder Lite Folding E-Bike (Blue)","brand":"Cyrusher","retailer":"Cyrusher UK","country":"UK","currency":"GBP","symbol":"£","category":"Folding","dealBucket":"⚡ Budget Steal","dealScore":65,"valueScore":65,"motor_power":"250W Road Legal","battery":"360Wh (36V 10Ah)","range_miles":"See retailer listing","max_speed":"15.5 mph (EAPC)","is_uk_legal":true,"rrp":1099,"sale_price":799,"price_from":true,"savings_amount":300,"discount_percentage":27,"image":"https://cdn.shopify.com/s/files/1/0627/5861/7301/files/Sonder-Lite-Blue-1.jpg?v=1783347171","url":"https://www.cyrusher.co.uk/products/sonder-lite?variant=54156723028181","badge_text":"SAVE £300 (27% OFF)","first_seen":"2026-09-09","is_new":true,"is_cached":false,"last_verified":"2026-09-10T20:25:12.568Z","lowest_price_30d":799,"is_lowest_price_30d":true,"price_drop_amount":0,"last_checked":"2026-09-10T20:25:13.587Z"},{"id":"cyrusher_uk_9343680282837","title":"Kommoda Pro Step-through Electric Bike (Yellow)","brand":"Cyrusher","retailer":"Cyrusher UK","country":"UK","currency":"GBP","symbol":"£","category":"Fat Tyre","dealBucket":"🔥 Mega Deal","dealScore":64.3,"valueScore":64.3,"motor_power":"250W Road Legal","battery":"1040Wh (52V 20Ah)","range_miles":"See retailer listing","max_speed":"15.5 mph (EAPC)","is_uk_legal":true,"rrp":1899,"sale_price":1399,"price_from":true,"savings_amount":500,"discount_percentage":26,"image":"https://cdn.shopify.com/s/files/1/0627/5861/7301/files/Cyrusher-kommoda-pro-SUV-E-Bike-Blue-5.jpg?v=1783494579","url":"https://www.cyrusher.co.uk/products/kommoda-pro?variant=53724753461461","badge_text":"SAVE £500 (26% OFF)","first_seen":"2026-09-10","is_new":true,"is_cached":false,"last_verified":"2026-09-10T20:25:12.569Z","lowest_price_30d":1399,"is_lowest_price_30d":true,"price_drop_amount":0,"last_checked":"2026-09-10T20:25:13.587Z"},{"id":"e_bikeshop_co_uk_15701268726144","title":"Orbea Denna H50 2026 Cues Electric Gravel Bike (L 50cm / Blue Stone)","brand":"Orbea Electric Bikes","retailer":"E-BikeShop UK","country":"UK","currency":"GBP","symbol":"£","category":"Commuter","dealBucket":"🔥 Mega Deal","dealScore":64,"valueScore":64,"motor_power":"Specification not confirmed","battery":"420Wh Lithium-Ion","range_miles":"30 - 120 Miles","max_speed":"Check retailer listing","is_uk_legal":false,"rrp":3499,"sale_price":2499,"price_from":true,"savings_amount":1000,"discount_percentage":29,"image":"https://cdn.shopify.com/s/files/1/0609/4838/1934/files/Orbea-Denna-H50-2026-Electric-Gravel-Bike-Blue-Stone.jpg?v=1777986746","url":"https://www.e-bikeshop.co.uk/products/electric-bike-orbea-denna-h50-2026-cues-electric-gravel-bike?variant=57885031334272","badge_text":"SAVE £1000 (29% OFF)","first_seen":"2026-09-10","is_new":true,"is_cached":false,"last_verified":"2026-09-10T20:25:02.469Z","lowest_price_30d":2499,"is_lowest_price_30d":true,"price_drop_amount":0,"last_checked":"2026-09-10T20:25:13.586Z"},{"id":"leisure_lakes_merida_eone_sixty_7000","title":"Merida eOne-Sixty 7000 Electric Bike (Gold/Silver)","brand":"Merida","retailer":"Leisure Lakes Bikes","country":"UK","currency":"GBP","symbol":"£","category":"Mountain","dealBucket":"🔥 Mega Deal","dealScore":63,"valueScore":63,"motor_power":"250W Mid-Drive Shimano EP8","battery":"630Wh Shimano Lithium-Ion","range_miles":"50 - 85 Miles","max_speed":"15.5 mph (EAPC)","is_uk_legal":true,"rrp":6400,"sale_price":3999,"savings_amount":2401,"discount_percentage":38,"image":"https://www.leisurelakesbikes.com/images/merida-eonesixty-7000-electric-bike-2024-goldsilver.jpg","url":"https://www.awin1.com/cread.php?awinmid=6914&awinaffid=3040709&clickref=dealspage&ued=https%3A%2F%2Fwww.leisurelakesbikes.com%2Fbikes%2Felectric-bikes%2Fmerida-eone-sixty-7000-electric-bike-goldsilver__411417","badge_text":"SAVE £2401 (38% OFF)","first_seen":"2026-09-09","is_new":true,"last_verified":"2026-09-10T20:25:13.585Z","lowest_price_30d":3999,"is_lowest_price_30d":true,"price_drop_amount":0,"last_checked":"2026-09-10T20:25:13.587Z","is_cached":false},{"id":"e_bikeshop_co_uk_8169191538926","title":"Scott Sub Cross eRide 20 EQ Crossbar (S 44cm)","brand":"Scott Electric Bikes","retailer":"E-BikeShop UK","country":"UK","currency":"GBP","symbol":"£","category":"Commuter","dealBucket":"🔥 Mega Deal","dealScore":61,"valueScore":61,"motor_power":"250W Mid-Drive (EAPC)","battery":"500Wh Lithium-Ion","range_miles":"30 - 120 Miles","max_speed":"15.5 mph (EAPC)","is_uk_legal":true,"rrp":3249,"sale_price":2399,"price_from":false,"savings_amount":850,"discount_percentage":26,"image":"https://cdn.shopify.com/s/files/1/0609/4838/1934/files/Scott-Sub-Cross-eRide-20-EQ-2024-Crossbar-Electric-Bike.jpg?v=1699547986","url":"https://www.e-bikeshop.co.uk/products/electric-bike-scott-sub-cross-eride-20-eq-2024-crossbar?variant=44438868918510","badge_text":"SAVE £850 (26% OFF)","first_seen":"2026-09-10","is_new":true,"is_cached":false,"last_verified":"2026-09-10T20:25:05.606Z","lowest_price_30d":2399,"is_lowest_price_30d":true,"price_drop_amount":0,"last_checked":"2026-09-10T20:25:13.587Z"},{"id":"e_bikeshop_co_uk_8132012540142","title":"Moustache J ALL (All Terrain) (S / Black)","brand":"Moustache Electric Bikes","retailer":"E-BikeShop UK","country":"UK","currency":"GBP","symbol":"£","category":"Fat Tyre","dealBucket":"🔥 Mega Deal","dealScore":61,"valueScore":61,"motor_power":"250W Mid-Drive (EAPC)","battery":"625Wh Lithium-Ion","range_miles":"30 - 140 Miles","max_speed":"15.5 mph (EAPC)","is_uk_legal":true,"rrp":4999,"sale_price":3199,"price_from":false,"savings_amount":1800,"discount_percentage":36,"image":"https://cdn.shopify.com/s/files/1/0609/4838/1934/files/Moustache-J-ALL-2024-Electric-Bike-Black.jpg?v=1696594677","url":"https://www.e-bikeshop.co.uk/products/electric-bike-moustache-j-all-2024?variant=44352021364974","badge_text":"SAVE £1800 (36% OFF)","first_seen":"2026-09-10","is_new":true,"is_cached":false,"last_verified":"2026-09-10T20:25:05.606Z","lowest_price_30d":3199,"is_lowest_price_30d":true,"price_drop_amount":0,"last_checked":"2026-09-10T20:25:13.587Z"},{"id":"e_bikeshop_co_uk_8137729704174","title":"Orbea Wild M11 AXS Carbon (S 40cm / Silver)","brand":"Orbea Electric Bikes","retailer":"E-BikeShop UK","country":"UK","currency":"GBP","symbol":"£","category":"Mountain","dealBucket":"🔥 Mega Deal","dealScore":60,"valueScore":60,"motor_power":"250W Mid-Drive (EAPC)","battery":"750Wh Lithium-Ion","range_miles":"30 - 160 Miles","max_speed":"15.5 mph (EAPC)","is_uk_legal":true,"rrp":8899,"sale_price":5299,"price_from":true,"savings_amount":3600,"discount_percentage":40,"image":"https://cdn.shopify.com/s/files/1/0609/4838/1934/files/Orbea-Wild-M11-AXS-2024-Electric-Bike-Halo-Silver.jpg?v=1701806301","url":"https://www.e-bikeshop.co.uk/products/electric-bike-orbea-wild-m11-axs-2024?variant=44370658689262","badge_text":"SAVE £3600 (40% OFF)","first_seen":"2026-09-10","is_new":true,"is_cached":false,"last_verified":"2026-09-10T20:25:05.606Z","lowest_price_30d":5299,"is_lowest_price_30d":true,"price_drop_amount":0,"last_checked":"2026-09-10T20:25:13.587Z"},{"id":"e_bikeshop_co_uk_8140451643630","title":"Orbea Rise M-Team Carbon (XL 50cm / Chameleon)","brand":"Orbea Electric Bikes","retailer":"E-BikeShop UK","country":"UK","currency":"GBP","symbol":"£","category":"Commuter","dealBucket":"🔥 Mega Deal","dealScore":60,"valueScore":60,"motor_power":"Specification not confirmed","battery":"360Wh Lithium-Ion","range_miles":"30 - 100 Miles","max_speed":"Check retailer listing","is_uk_legal":false,"rrp":9299,"sale_price":5599,"price_from":false,"savings_amount":3700,"discount_percentage":40,"image":"https://cdn.shopify.com/s/files/1/0609/4838/1934/files/Orbea-Rise-M-TEAM-Grey-2024-Electric-Bike.jpg?v=1698312053","url":"https://www.e-bikeshop.co.uk/products/electric-bike-orbea-rise-m-team-2024?variant=44378154533102","badge_text":"SAVE £3700 (40% OFF)","first_seen":"2026-09-10","is_new":true,"is_cached":false,"last_verified":"2026-09-10T20:25:05.606Z","lowest_price_30d":5599,"is_lowest_price_30d":true,"price_drop_amount":0,"last_checked":"2026-09-10T20:25:13.587Z"},{"id":"e_bikeshop_co_uk_8140434374894","title":"Orbea Rise M-LTD Carbon (M 42cm / Grey)","brand":"Orbea Electric Bikes","retailer":"E-BikeShop UK","country":"UK","currency":"GBP","symbol":"£","category":"Commuter","dealBucket":"🔥 Mega Deal","dealScore":60,"valueScore":60,"motor_power":"Specification not confirmed","battery":"360Wh Lithium-Ion","range_miles":"30 - 100 Miles","max_speed":"Check retailer listing","is_uk_legal":false,"rrp":9999,"sale_price":5999,"price_from":false,"savings_amount":4000,"discount_percentage":40,"image":"https://cdn.shopify.com/s/files/1/0609/4838/1934/files/Orbea-Rise-M-LTD-Green-2024-Electric-Bike.jpg?v=1698249650","url":"https://www.e-bikeshop.co.uk/products/electric-bike-orbea-rise-m-ltd-2024?variant=44378134806766","badge_text":"SAVE £4000 (40% OFF)","first_seen":"2026-09-10","is_new":true,"is_cached":false,"last_verified":"2026-09-10T20:25:05.606Z","lowest_price_30d":5999,"is_lowest_price_30d":true,"price_drop_amount":0,"last_checked":"2026-09-10T20:25:13.587Z"},{"id":"e_bikeshop_co_uk_8132004741358","title":"Moustache J ON (On Road) (S)","brand":"Moustache Electric Bikes","retailer":"E-BikeShop UK","country":"UK","currency":"GBP","symbol":"£","category":"Commuter","dealBucket":"🔥 Mega Deal","dealScore":60,"valueScore":60,"motor_power":"250W Mid-Drive (EAPC)","battery":"625Wh Lithium-Ion","range_miles":"30 - 140 Miles","max_speed":"15.5 mph (EAPC)","is_uk_legal":true,"rrp":5699,"sale_price":3699,"price_from":true,"savings_amount":2000,"discount_percentage":35,"image":"https://cdn.shopify.com/s/files/1/0609/4838/1934/files/Moustache-J-ON-2024-Electric-Bike-Black.jpg?v=1696591006","url":"https://www.e-bikeshop.co.uk/products/electric-bike-moustache-j-on-2024?variant=44351979749614","badge_text":"SAVE £2000 (35% OFF)","first_seen":"2026-09-10","is_new":true,"is_cached":false,"last_verified":"2026-09-10T20:25:05.606Z","lowest_price_30d":3699,"is_lowest_price_30d":true,"price_drop_amount":0,"last_checked":"2026-09-10T20:25:13.587Z"},{"id":"cyrusher_uk_9006660419797","title":"Flex Mountain E-Bike (Blue)","brand":"Cyrusher United Kingdom","retailer":"Cyrusher UK","country":"UK","currency":"GBP","symbol":"£","category":"Mountain","dealBucket":"🔥 Mega Deal","dealScore":57.7,"valueScore":57.7,"motor_power":"250W Mid-Drive","battery":"720Wh (48V 15Ah)","range_miles":"See retailer listing","max_speed":"15.5 mph (EAPC)","is_uk_legal":true,"rrp":1799,"sale_price":1399,"price_from":true,"savings_amount":400,"discount_percentage":22,"image":"https://cdn.shopify.com/s/files/1/0627/5861/7301/files/Cyrusher-Flex-Emtb-E-Bike-Main-Blue-1.jpg?v=1784899404","url":"https://www.cyrusher.co.uk/products/flex?variant=51648992936149","badge_text":"SAVE £400 (22% OFF)","first_seen":"2026-09-09","is_new":true,"is_cached":false,"last_verified":"2026-09-10T20:25:12.569Z","lowest_price_30d":1399,"is_lowest_price_30d":true,"price_drop_amount":0,"last_checked":"2026-09-10T20:25:13.587Z"},{"id":"e_bikeshop_co_uk_8641040187630","title":"Orbea Wild ST H20 Alloy (M 42cm / Diamond Black)","brand":"Orbea Electric Bikes","retailer":"E-BikeShop UK","country":"UK","currency":"GBP","symbol":"£","category":"Mountain","dealBucket":"🔥 Mega Deal","dealScore":56,"valueScore":56,"motor_power":"250W Mid-Drive (EAPC)","battery":"750Wh Lithium-Ion","range_miles":"30 - 160 Miles","max_speed":"15.5 mph (EAPC)","is_uk_legal":true,"rrp":5799,"sale_price":3999,"price_from":false,"savings_amount":1800,"discount_percentage":31,"image":"https://cdn.shopify.com/s/files/1/0609/4838/1934/files/Orbea-Wild-ST-H20-2025-Electric-Bike-Halo-Silver-Gloss.jpg?v=1727526487","url":"https://www.e-bikeshop.co.uk/products/electric-bike-orbea-wild-st-h20-2025?variant=45691477655790","badge_text":"SAVE £1800 (31% OFF)","first_seen":"2026-09-10","is_new":true,"is_cached":false,"last_verified":"2026-09-10T20:25:04.760Z","lowest_price_30d":3999,"is_lowest_price_30d":true,"price_drop_amount":0,"last_checked":"2026-09-10T20:25:13.587Z"},{"id":"e_bikeshop_co_uk_8428162941166","title":"Orbea Rise LT M-Team (630Wh) (M 41cm / Cosmic Carbon)","brand":"Orbea Electric Bikes","retailer":"E-BikeShop UK","country":"UK","currency":"GBP","symbol":"£","category":"Commuter","dealBucket":"🔥 Mega Deal","dealScore":55,"valueScore":55,"motor_power":"Specification not confirmed","battery":"630Wh Lithium-Ion","range_miles":"30 - 140 Miles","max_speed":"Check retailer listing","is_uk_legal":false,"rrp":10179,"sale_price":6599,"price_from":true,"savings_amount":3580,"discount_percentage":35,"image":"https://cdn.shopify.com/s/files/1/0609/4838/1934/files/Orbea-Rise-LT-M-Team-630W-Blue-2025-Electric-Bike.jpg?v=1714213676","url":"https://www.e-bikeshop.co.uk/products/electric-bike-orbea-rise-lt-m-team-2025-630wh?variant=45156968530158","badge_text":"SAVE £3580 (35% OFF)","first_seen":"2026-09-10","is_new":true,"is_cached":false,"last_verified":"2026-09-10T20:25:04.760Z","lowest_price_30d":6599,"is_lowest_price_30d":true,"price_drop_amount":0,"last_checked":"2026-09-10T20:25:13.587Z"},{"id":"e_bikeshop_co_uk_15274220552576","title":"Orbea Rise LT H20 2026 (630Wh) (M 41cm / Bumblebee Yellow)","brand":"Orbea Electric Bikes","retailer":"E-BikeShop UK","country":"UK","currency":"GBP","symbol":"£","category":"Commuter","dealBucket":"🔥 Mega Deal","dealScore":54,"valueScore":54,"motor_power":"Specification not confirmed","battery":"630Wh Lithium-Ion","range_miles":"30 - 140 Miles","max_speed":"Check retailer listing","is_uk_legal":false,"rrp":4799,"sale_price":3399,"price_from":true,"savings_amount":1400,"discount_percentage":29,"image":"https://cdn.shopify.com/s/files/1/0609/4838/1934/files/Orbea-Rise-LT-H20-630Wh-2026-Electric-Bike-Diamond-Black.jpg?v=1758122658","url":"https://www.e-bikeshop.co.uk/products/electric-bike-orbea-rise-lt-h20-2026?variant=56372099907968","badge_text":"SAVE £1400 (29% OFF)","first_seen":"2026-09-10","is_new":true,"is_cached":false,"last_verified":"2026-09-10T20:25:03.692Z","lowest_price_30d":3399,"is_lowest_price_30d":true,"price_drop_amount":0,"last_checked":"2026-09-10T20:25:13.586Z"},{"id":"e_bikeshop_co_uk_8278238429422","title":"Cube AMS Hybrid ONE44 C:68X SLX 400X 29 (XL 47cm)","brand":"Cube Electric Bikes","retailer":"E-BikeShop UK","country":"UK","currency":"GBP","symbol":"£","category":"Mountain","dealBucket":"🔥 Mega Deal","dealScore":54,"valueScore":54,"motor_power":"250W Mid-Drive (EAPC)","battery":"400Wh Lithium-Ion","range_miles":"30 - 120 Miles","max_speed":"15.5 mph (EAPC)","is_uk_legal":true,"rrp":5499,"sale_price":3899,"price_from":false,"savings_amount":1600,"discount_percentage":29,"image":"https://cdn.shopify.com/s/files/1/0609/4838/1934/files/Cube-AMS-Hybrid-ONE44-C68X-SLX-2025-Electric-Bike.jpg?v=1725626473","url":"https://www.e-bikeshop.co.uk/products/electric-bike-cube-ams-hybrid-one44-slx-2025?variant=44767224561902","badge_text":"SAVE £1600 (29% OFF)","first_seen":"2026-09-10","is_new":true,"is_cached":false,"last_verified":"2026-09-10T20:25:04.760Z","lowest_price_30d":3899,"is_lowest_price_30d":true,"price_drop_amount":0,"last_checked":"2026-09-10T20:25:13.587Z"},{"id":"e_bikeshop_co_uk_15274346316160","title":"Orbea Rise LT M20 2026 (630Wh) (S 40cm / Cosmic Carbon)","brand":"Orbea Electric Bikes","retailer":"E-BikeShop UK","country":"UK","currency":"GBP","symbol":"£","category":"Fat Tyre","dealBucket":"🔥 Mega Deal","dealScore":52,"valueScore":52,"motor_power":"Specification not confirmed","battery":"630Wh Lithium-Ion","range_miles":"30 - 140 Miles","max_speed":"Check retailer listing","is_uk_legal":false,"rrp":6699,"sale_price":4549,"price_from":true,"savings_amount":2150,"discount_percentage":32,"image":"https://cdn.shopify.com/s/files/1/0609/4838/1934/files/Orbea-Rise-LT-M20-630Wh-2026-Electric-Bike-Desert-Rose.jpg?v=1758192628","url":"https://www.e-bikeshop.co.uk/products/electric-bike-orbea-rise-lt-m20-2026?variant=56372659650944","badge_text":"SAVE £2150 (32% OFF)","first_seen":"2026-09-10","is_new":true,"is_cached":false,"last_verified":"2026-09-10T20:25:03.691Z","lowest_price_30d":4549,"is_lowest_price_30d":true,"price_drop_amount":0,"last_checked":"2026-09-10T20:25:13.586Z"},{"id":"e_bikeshop_co_uk_14989695353216","title":"Haibike Hybe CF 11 (S 40cm)","brand":"Haibike Electric Bikes","retailer":"E-BikeShop UK","country":"UK","currency":"GBP","symbol":"£","category":"Mountain","dealBucket":"🔥 Mega Deal","dealScore":52,"valueScore":52,"motor_power":"250W Mid-Drive (EAPC)","battery":"800Wh Lithium-Ion","range_miles":"30 - 170 Miles","max_speed":"15.5 mph (EAPC)","is_uk_legal":true,"rrp":8399,"sale_price":5699,"price_from":false,"savings_amount":2700,"discount_percentage":32,"image":"https://cdn.shopify.com/s/files/1/0609/4838/1934/files/Haibike-Hybe-CF-11-2025-Electric-Bike.jpg?v=1739879759","url":"https://www.e-bikeshop.co.uk/products/electric-bike-haibike-hybe-cf-11-2025?variant=55292122005888","badge_text":"SAVE £2700 (32% OFF)","first_seen":"2026-09-10","is_new":true,"is_cached":false,"last_verified":"2026-09-10T20:25:03.694Z","lowest_price_30d":5699,"is_lowest_price_30d":true,"price_drop_amount":0,"last_checked":"2026-09-10T20:25:13.587Z"},{"id":"e_bikeshop_co_uk_8428171329774","title":"Orbea Rise LT M10 (630Wh) (L 43cm / Desert Rose)","brand":"Orbea Electric Bikes","retailer":"E-BikeShop UK","country":"UK","currency":"GBP","symbol":"£","category":"Commuter","dealBucket":"🔥 Mega Deal","dealScore":52,"valueScore":52,"motor_power":"Specification not confirmed","battery":"630Wh Lithium-Ion","range_miles":"30 - 140 Miles","max_speed":"Check retailer listing","is_uk_legal":false,"rrp":7779,"sale_price":5299,"price_from":true,"savings_amount":2480,"discount_percentage":32,"image":"https://cdn.shopify.com/s/files/1/0609/4838/1934/files/Orbea-Rise-LT-M10-630W-Rose-2025-Electric-Bike.jpg?v=1764073942","url":"https://www.e-bikeshop.co.uk/products/electric-bike-orbea-rise-lt-m10-2025-630wh?variant=45156999954670","badge_text":"SAVE £2480 (32% OFF)","first_seen":"2026-09-10","is_new":true,"is_cached":false,"last_verified":"2026-09-10T20:25:04.760Z","lowest_price_30d":5299,"is_lowest_price_30d":true,"price_drop_amount":0,"last_checked":"2026-09-10T20:25:13.587Z"},{"id":"e_bikeshop_co_uk_8428174934254","title":"Orbea Rise SL M10 (630Wh) (XL 46cm / Desert Rose)","brand":"Orbea Electric Bikes","retailer":"E-BikeShop UK","country":"UK","currency":"GBP","symbol":"£","category":"Commuter","dealBucket":"🔥 Mega Deal","dealScore":52,"valueScore":52,"motor_power":"Specification not confirmed","battery":"630Wh Lithium-Ion","range_miles":"30 - 140 Miles","max_speed":"Check retailer listing","is_uk_legal":false,"rrp":7379,"sale_price":4999,"price_from":false,"savings_amount":2380,"discount_percentage":32,"image":"https://cdn.shopify.com/s/files/1/0609/4838/1934/files/Orbea-Rise-SL-M10-630W-Blue-2025-Electric-Bike.jpg?v=1714133298","url":"https://www.e-bikeshop.co.uk/products/electric-bike-orbea-rise-sl-m10-2025-630wh?variant=45157024039150","badge_text":"SAVE £2380 (32% OFF)","first_seen":"2026-09-10","is_new":true,"is_cached":false,"last_verified":"2026-09-10T20:25:04.760Z","lowest_price_30d":4999,"is_lowest_price_30d":true,"price_drop_amount":0,"last_checked":"2026-09-10T20:25:13.587Z"},{"id":"e_bikeshop_co_uk_15273308094848","title":"Orbea Wild ST H30 2026 Alloy (S 40cm / Diamond Black)","brand":"Orbea Electric Bikes","retailer":"E-BikeShop UK","country":"UK","currency":"GBP","symbol":"£","category":"Mountain","dealBucket":"🔥 Mega Deal","dealScore":51,"valueScore":51,"motor_power":"250W Mid-Drive (EAPC)","battery":"600Wh Lithium-Ion","range_miles":"30 - 140 Miles","max_speed":"15.5 mph (EAPC)","is_uk_legal":true,"rrp":4699,"sale_price":3499,"price_from":true,"savings_amount":1200,"discount_percentage":26,"image":"https://cdn.shopify.com/s/files/1/0609/4838/1934/files/Orbea-Wild-ST-H30-2026-Electric-Bike-Diamond-Black-Blue-Stone-Matt.jpg?v=1755264085","url":"https://www.e-bikeshop.co.uk/products/electric-bike-orbea-wild-st-h30-2026?variant=56369213538688","badge_text":"SAVE £1200 (26% OFF)","first_seen":"2026-09-10","is_new":true,"is_cached":false,"last_verified":"2026-09-10T20:25:03.691Z","lowest_price_30d":3499,"is_lowest_price_30d":true,"price_drop_amount":0,"last_checked":"2026-09-10T20:25:13.586Z"},{"id":"e_bikeshop_co_uk_15273253011840","title":"Orbea Wild M-Team 2026 Carbon (S 40cm / Seaweed Carbon)","brand":"Orbea Electric Bikes","retailer":"E-BikeShop UK","country":"UK","currency":"GBP","symbol":"£","category":"Mountain","dealBucket":"🔥 Mega Deal","dealScore":51,"valueScore":51,"motor_power":"250W Mid-Drive (EAPC)","battery":"750Wh Lithium-Ion","range_miles":"30 - 160 Miles","max_speed":"15.5 mph (EAPC)","is_uk_legal":true,"rrp":8999,"sale_price":6249,"price_from":true,"savings_amount":2750,"discount_percentage":31,"image":"https://cdn.shopify.com/s/files/1/0609/4838/1934/files/Orbea-Wild-M-Team-2026-Electric-Bike-Seaweed-Carbon-View.jpg?v=1755086721","url":"https://www.e-bikeshop.co.uk/products/electric-bike-orbea-wild-m-team-2026?variant=56369150460288","badge_text":"SAVE £2750 (31% OFF)","first_seen":"2026-09-10","is_new":true,"is_cached":false,"last_verified":"2026-09-10T20:25:03.692Z","lowest_price_30d":6249,"is_lowest_price_30d":true,"price_drop_amount":0,"last_checked":"2026-09-10T20:25:13.586Z"},{"id":"e_bikeshop_co_uk_15158718398848","title":"Orbea Denna H30 2026 GRX Electric Gravel Bike (M 47cm / Ivory)","brand":"Orbea Electric Bikes","retailer":"E-BikeShop UK","country":"UK","currency":"GBP","symbol":"£","category":"Commuter","dealBucket":"🔥 Mega Deal","dealScore":51,"valueScore":51,"motor_power":"Specification not confirmed","battery":"420Wh Lithium-Ion","range_miles":"30 - 120 Miles","max_speed":"Check retailer listing","is_uk_legal":false,"rrp":3899,"sale_price":2899,"price_from":true,"savings_amount":1000,"discount_percentage":26,"image":"https://cdn.shopify.com/s/files/1/0609/4838/1934/files/Orbea-Denna-H30-2026-Electric-Gravel-Bike-Ivory-White.jpg?v=1750856707","url":"https://www.e-bikeshop.co.uk/products/electric-bike-orbea-denna-h30-2025?variant=55948570526080","badge_text":"SAVE £1000 (26% OFF)","first_seen":"2026-09-10","is_new":true,"is_cached":false,"last_verified":"2026-09-10T20:25:03.694Z","lowest_price_30d":2899,"is_lowest_price_30d":true,"price_drop_amount":0,"last_checked":"2026-09-10T20:25:13.587Z"},{"id":"e_bikeshop_co_uk_14962267423104","title":"Orbea Gain M40 Cues Electric Road Bike (M 49cm / Halo Silver)","brand":"Orbea Electric Bikes","retailer":"E-BikeShop UK","country":"UK","currency":"GBP","symbol":"£","category":"Commuter","dealBucket":"🔥 Mega Deal","dealScore":51,"valueScore":51,"motor_power":"250W Mid-Drive (EAPC)","battery":"350Wh Lithium-Ion","range_miles":"30 - 120 Miles","max_speed":"15.5 mph (EAPC)","is_uk_legal":true,"rrp":4999,"sale_price":3699,"price_from":true,"savings_amount":1300,"discount_percentage":26,"image":"https://cdn.shopify.com/s/files/1/0609/4838/1934/files/Orbea-Gain-M40-2025-Electric-Road-Bike-Purple_e44c6022-67a0-43bb-8146-0d0d4af813f2.jpg?v=1744895375","url":"https://www.e-bikeshop.co.uk/products/electric-bike-orbea-gain-m40-2025?variant=54868848476544","badge_text":"SAVE £1300 (26% OFF)","first_seen":"2026-09-10","is_new":true,"is_cached":false,"last_verified":"2026-09-10T20:25:03.694Z","lowest_price_30d":3699,"is_lowest_price_30d":true,"price_drop_amount":0,"last_checked":"2026-09-10T20:25:13.587Z"},{"id":"e_bikeshop_co_uk_14962260509056","title":"Orbea Gain M30 105 Electric Road Bike (L 52cm / Halo Silver)","brand":"Orbea Electric Bikes","retailer":"E-BikeShop UK","country":"UK","currency":"GBP","symbol":"£","category":"Commuter","dealBucket":"🔥 Mega Deal","dealScore":51,"valueScore":51,"motor_power":"250W Mid-Drive (EAPC)","battery":"350Wh Lithium-Ion","range_miles":"30 - 120 Miles","max_speed":"15.5 mph (EAPC)","is_uk_legal":true,"rrp":5299,"sale_price":3899,"price_from":true,"savings_amount":1400,"discount_percentage":26,"image":"https://cdn.shopify.com/s/files/1/0609/4838/1934/files/Orbea-Gain-M30-2025-Electric-Road-Bike-Silver.jpg?v=1741341074","url":"https://www.e-bikeshop.co.uk/products/electric-bike-orbea-gain-m30-2025?variant=54868835369344","badge_text":"SAVE £1400 (26% OFF)","first_seen":"2026-09-10","is_new":true,"is_cached":false,"last_verified":"2026-09-10T20:25:03.694Z","lowest_price_30d":3899,"is_lowest_price_30d":true,"price_drop_amount":0,"last_checked":"2026-09-10T20:25:13.587Z"},{"id":"engwe_uk_8164313268406","title":"L20 3.0 Boost (Black)","brand":"Engwe-bikes-UK","retailer":"Engwe UK Official","country":"UK","currency":"GBP","symbol":"£","category":"Fat Tyre","dealBucket":"Standard Deal","dealScore":50.7,"valueScore":50.7,"motor_power":"250W Road Legal","battery":"648Wh Lithium-Ion","range_miles":"See retailer listing","max_speed":"15.5 mph (EAPC)","is_uk_legal":true,"rrp":1299,"sale_price":1049,"price_from":true,"savings_amount":250,"discount_percentage":19,"image":"https://cdn.shopify.com/s/files/1/0627/1385/6182/files/L20_3.0_Boost.png?v=1787719907","url":"https://www.awin1.com/cread.php?awinmid=65774&awinaffid=3040709&clickref=dealspage&ued=https%3A%2F%2Fengwe-bikes-uk.com%2Fproducts%2Fengwe-l20-3-0-boost%3Fvariant%3D43837038624950","badge_text":"SAVE £250 (19% OFF)","first_seen":"2026-09-09","is_new":true,"is_cached":false,"last_verified":"2026-09-10T20:25:06.240Z","lowest_price_30d":1049,"is_lowest_price_30d":true,"price_drop_amount":0,"last_checked":"2026-09-10T20:25:13.587Z"},{"id":"fiido_uk_8646532071725","title":"Fiido D3 Pro Mini Electric Bike (Black / Standard)","brand":"fiido","retailer":"Fiido UK Official","country":"UK","currency":"GBP","symbol":"£","category":"Commuter","dealBucket":"⚡ Budget Steal","dealScore":50.6,"valueScore":50.6,"motor_power":"Specification not confirmed","battery":"Specification not confirmed","range_miles":"See retailer listing","max_speed":"Check retailer listing","is_uk_legal":false,"rrp":545,"sale_price":449,"price_from":true,"savings_amount":96,"discount_percentage":18,"image":"https://cdn.shopify.com/s/files/1/0814/1232/5677/files/1-d3pro_d4ee5a8b-c192-4d63-95c6-65f74348777d.webp?v=1775813790","url":"https://uk.fiido.com/products/fiido-d3-pro-mini-electric-bike?variant=46489883181357","badge_text":"SAVE £96 (18% OFF)","first_seen":"2026-09-10","is_new":true,"is_cached":false,"last_verified":"2026-09-10T20:25:10.779Z","lowest_price_30d":449,"is_lowest_price_30d":true,"price_drop_amount":0,"last_checked":"2026-09-10T20:25:13.587Z"},{"id":"e_bikeshop_co_uk_15351325852032","title":"Haibike AllMtn CF 9 2026 (M 43cm / Black)","brand":"Haibike Electric Bikes","retailer":"E-BikeShop UK","country":"UK","currency":"GBP","symbol":"£","category":"Mountain","dealBucket":"🔥 Mega Deal","dealScore":50,"valueScore":50,"motor_power":"250W Mid-Drive (EAPC)","battery":"800Wh Lithium-Ion","range_miles":"30 - 170 Miles","max_speed":"15.5 mph (EAPC)","is_uk_legal":true,"rrp":6249,"sale_price":4399,"price_from":true,"savings_amount":1850,"discount_percentage":30,"image":"https://cdn.shopify.com/s/files/1/0609/4838/1934/files/Haibike-AllMtn-CF-9-2026-Electric-Bike-Black.jpg?v=1765812100","url":"https://www.e-bikeshop.co.uk/products/electric-bike-haibike-allmtn-cf-9-2026?variant=56606905729408","badge_text":"SAVE £1850 (30% OFF)","first_seen":"2026-09-10","is_new":true,"is_cached":false,"last_verified":"2026-09-10T20:25:02.471Z","lowest_price_30d":4399,"is_lowest_price_30d":true,"price_drop_amount":0,"last_checked":"2026-09-10T20:25:13.586Z"},{"id":"e_bikeshop_co_uk_15351321559424","title":"Haibike Hybe CF 9 2026 (S 40cm)","brand":"Haibike Electric Bikes","retailer":"E-BikeShop UK","country":"UK","currency":"GBP","symbol":"£","category":"Mountain","dealBucket":"🔥 Mega Deal","dealScore":50,"valueScore":50,"motor_power":"250W Mid-Drive (EAPC)","battery":"800Wh Lithium-Ion","range_miles":"30 - 170 Miles","max_speed":"15.5 mph (EAPC)","is_uk_legal":true,"rrp":6549,"sale_price":4599,"price_from":true,"savings_amount":1950,"discount_percentage":30,"image":"https://cdn.shopify.com/s/files/1/0609/4838/1934/files/Haibike-Hybe-CF-9-2026-Electric-Bike-Brown.jpg?v=1765810730","url":"https://www.e-bikeshop.co.uk/products/electric-bike-haibike-hybe-cf-9-2026?variant=56606876205440","badge_text":"SAVE £1950 (30% OFF)","first_seen":"2026-09-09","is_new":true,"is_cached":false,"last_verified":"2026-09-10T20:25:02.471Z","lowest_price_30d":4599,"is_lowest_price_30d":true,"price_drop_amount":0,"last_checked":"2026-09-10T20:25:13.586Z"},{"id":"e_bikeshop_co_uk_15274417947008","title":"Orbea Rise SL M10 2026 (630Wh) (S 40cm / Cosmic Carbon)","brand":"Orbea Electric Bikes","retailer":"E-BikeShop UK","country":"UK","currency":"GBP","symbol":"£","category":"Commuter","dealBucket":"🔥 Mega Deal","dealScore":50,"valueScore":50,"motor_power":"Specification not confirmed","battery":"630Wh Lithium-Ion","range_miles":"30 - 140 Miles","max_speed":"Check retailer listing","is_uk_legal":false,"rrp":7699,"sale_price":5399,"price_from":true,"savings_amount":2300,"discount_percentage":30,"image":"https://cdn.shopify.com/s/files/1/0609/4838/1934/files/Orbea-Rise-SL-M10-630Wh-2026-Electric-Bike-Cosmic-Carbon.jpg?v=1758201498","url":"https://www.e-bikeshop.co.uk/products/electric-bike-orbea-rise-sl-m10-2026?variant=56372996047232","badge_text":"SAVE £2300 (30% OFF)","first_seen":"2026-09-10","is_new":true,"is_cached":false,"last_verified":"2026-09-10T20:25:03.691Z","lowest_price_30d":5399,"is_lowest_price_30d":true,"price_drop_amount":0,"last_checked":"2026-09-10T20:25:13.586Z"},{"id":"e_bikeshop_co_uk_15274277765504","title":"Orbea Rise LT M10 2026 (630Wh) (S 40cm / Desert Rose)","brand":"Orbea Electric Bikes","retailer":"E-BikeShop UK","country":"UK","currency":"GBP","symbol":"£","category":"Commuter","dealBucket":"🔥 Mega Deal","dealScore":50,"valueScore":50,"motor_power":"Specification not confirmed","battery":"630Wh Lithium-Ion","range_miles":"30 - 140 Miles","max_speed":"Check retailer listing","is_uk_legal":false,"rrp":8199,"sale_price":5749,"price_from":true,"savings_amount":2450,"discount_percentage":30,"image":"https://cdn.shopify.com/s/files/1/0609/4838/1934/files/Orbea-Rise-LT-M10-630Wh-2026-Electric-Bike-Cosmic-Carbon.jpg?v=1758191526","url":"https://www.e-bikeshop.co.uk/products/electric-bike-orbea-rise-lt-m10-2026?variant=56372433092992","badge_text":"SAVE £2450 (30% OFF)","first_seen":"2026-09-10","is_new":true,"is_cached":false,"last_verified":"2026-09-10T20:25:03.691Z","lowest_price_30d":5749,"is_lowest_price_30d":true,"price_drop_amount":0,"last_checked":"2026-09-10T20:25:13.586Z"},{"id":"e_bikeshop_co_uk_15273307439488","title":"Orbea Wild ST H20 2026 Alloy (S 40cm / Halo Silver)","brand":"Orbea Electric Bikes","retailer":"E-BikeShop UK","country":"UK","currency":"GBP","symbol":"£","category":"Mountain","dealBucket":"🔥 Mega Deal","dealScore":50,"valueScore":50,"motor_power":"250W Mid-Drive (EAPC)","battery":"750Wh Lithium-Ion","range_miles":"30 - 160 Miles","max_speed":"15.5 mph (EAPC)","is_uk_legal":true,"rrp":5299,"sale_price":3949,"price_from":true,"savings_amount":1350,"discount_percentage":25,"image":"https://cdn.shopify.com/s/files/1/0609/4838/1934/files/Orbea-Wild-ST-H20-2026-Electric-Bike-Halo-Silver-Gloss.jpg?v=1755262761","url":"https://www.e-bikeshop.co.uk/products/electric-bike-orbea-wild-st-h20-2026?variant=56369211343232","badge_text":"SAVE £1350 (25% OFF)","first_seen":"2026-09-10","is_new":true,"is_cached":false,"last_verified":"2026-09-10T20:25:03.692Z","lowest_price_30d":3949,"is_lowest_price_30d":true,"price_drop_amount":0,"last_checked":"2026-09-10T20:25:13.586Z"},{"id":"cyrusher_uk_9006660387029","title":"Roam All-Terrain Electric Bike (Blue)","brand":"Cyrusher United Kingdom","retailer":"Cyrusher UK","country":"UK","currency":"GBP","symbol":"£","category":"Fat Tyre","dealBucket":"Standard Deal","dealScore":50,"valueScore":50,"motor_power":"250W Road Legal","battery":"811Wh (52V 15.6Ah)","range_miles":"See retailer listing","max_speed":"15.5 mph (EAPC)","is_uk_legal":true,"rrp":1799,"sale_price":1499,"price_from":true,"savings_amount":300,"discount_percentage":17,"image":"https://cdn.shopify.com/s/files/1/0627/5861/7301/files/Cyrusher-Roam-E-Bike-Blue-001.jpg?v=1762762175","url":"https://www.cyrusher.co.uk/products/roam?variant=51648992837845","badge_text":"SAVE £300 (17% OFF)","first_seen":"2026-09-09","is_new":true,"is_cached":false,"last_verified":"2026-09-10T20:25:12.569Z","lowest_price_30d":1499,"is_lowest_price_30d":true,"price_drop_amount":0,"last_checked":"2026-09-10T20:25:13.587Z"},{"id":"cyrusher_uk_8905427812565","title":"Glider Cargo E-Bike (Blue)","brand":"Cyrusher United Kingdom","retailer":"Cyrusher UK","country":"UK","currency":"GBP","symbol":"£","category":"Cargo","dealBucket":"Standard Deal","dealScore":50,"valueScore":50,"motor_power":"Specification not confirmed","battery":"2080Wh (52V 40Ah)","range_miles":"See retailer listing","max_speed":"Check retailer listing","is_uk_legal":false,"rrp":1799,"sale_price":1499,"price_from":true,"savings_amount":300,"discount_percentage":17,"image":"https://cdn.shopify.com/s/files/1/0627/5861/7301/files/GliderwithBag-3.jpg?v=1764129891","url":"https://www.cyrusher.co.uk/products/glider?variant=51166880235733","badge_text":"SAVE £300 (17% OFF)","first_seen":"2026-09-09","is_new":true,"is_cached":false,"last_verified":"2026-09-10T20:25:12.569Z","lowest_price_30d":1499,"is_lowest_price_30d":true,"price_drop_amount":0,"last_checked":"2026-09-10T20:25:13.587Z"},{"id":"cyrusher_uk_8832694321365","title":"Rover, All-Terrain Ebike (Blue)","brand":"Cyrusher United Kingdom","retailer":"Cyrusher UK","country":"UK","currency":"GBP","symbol":"£","category":"Fat Tyre","dealBucket":"Standard Deal","dealScore":50,"valueScore":50,"motor_power":"250W Road Legal","battery":"811Wh (52V 15.6Ah)","range_miles":"See retailer listing","max_speed":"15.5 mph (EAPC)","is_uk_legal":true,"rrp":1799,"sale_price":1499,"price_from":true,"savings_amount":300,"discount_percentage":17,"image":"https://cdn.shopify.com/s/files/1/0627/5861/7301/files/Cyrusher-Rover-E-Bike-Green-001.jpg?v=1762770884","url":"https://www.cyrusher.co.uk/products/rover?variant=50837799043285","badge_text":"SAVE £300 (17% OFF)","first_seen":"2026-09-09","is_new":true,"is_cached":false,"last_verified":"2026-09-10T20:25:12.569Z","lowest_price_30d":1499,"is_lowest_price_30d":true,"price_drop_amount":0,"last_checked":"2026-09-10T20:25:13.587Z"},{"id":"e_bikeshop_co_uk_8278235676910","title":"Cube AMS Hybrid ONE44 C:68X SLT 400X 29 (XL 47cm)","brand":"Cube Electric Bikes","retailer":"E-BikeShop UK","country":"UK","currency":"GBP","symbol":"£","category":"Mountain","dealBucket":"🔥 Mega Deal","dealScore":49,"valueScore":49,"motor_power":"250W Mid-Drive (EAPC)","battery":"400Wh Lithium-Ion","range_miles":"30 - 120 Miles","max_speed":"15.5 mph (EAPC)","is_uk_legal":true,"rrp":7499,"sale_price":5299,"price_from":false,"savings_amount":2200,"discount_percentage":29,"image":"https://cdn.shopify.com/s/files/1/0609/4838/1934/files/Cube-AMS-Hybrid-ONE44-C68X-SLT-2025-Electric-Bike.jpg?v=1725623630","url":"https://www.e-bikeshop.co.uk/products/electric-bike-cube-ams-hybrid-one44-slt-2025?variant=44767211847918","badge_text":"SAVE £2200 (29% OFF)","first_seen":"2026-09-10","is_new":true,"is_cached":false,"last_verified":"2026-09-10T20:25:04.760Z","lowest_price_30d":5299,"is_lowest_price_30d":true,"price_drop_amount":0,"last_checked":"2026-09-10T20:25:13.587Z"},{"id":"e_bikeshop_co_uk_8278230860014","title":"Cube AMS Hybrid ONE44 C:68X SUPER TM 400X 29 (L 42cm)","brand":"Cube Electric Bikes","retailer":"E-BikeShop UK","country":"UK","currency":"GBP","symbol":"£","category":"Mountain","dealBucket":"🔥 Mega Deal","dealScore":49,"valueScore":49,"motor_power":"250W Mid-Drive (EAPC)","battery":"400Wh Lithium-Ion","range_miles":"30 - 120 Miles","max_speed":"15.5 mph (EAPC)","is_uk_legal":true,"rrp":7999,"sale_price":5699,"price_from":false,"savings_amount":2300,"discount_percentage":29,"image":"https://cdn.shopify.com/s/files/1/0609/4838/1934/files/Cube-AMS-Hybrid-ONE44-C68X-SUPERTM-2025-Electric-Bike.jpg?v=1725623174","url":"https://www.e-bikeshop.co.uk/products/electric-bike-cube-ams-hybrid-one44-super-tm-2025?variant=44767192416494","badge_text":"SAVE £2300 (29% OFF)","first_seen":"2026-09-10","is_new":true,"is_cached":false,"last_verified":"2026-09-10T20:25:04.760Z","lowest_price_30d":5699,"is_lowest_price_30d":true,"price_drop_amount":0,"last_checked":"2026-09-10T20:25:13.587Z"},{"id":"engwe_uk_8169600778422","title":"L20 3.0 Boost Combo (Black / Black)","brand":"Engwe-bikes-UK","retailer":"Engwe UK Official","country":"UK","currency":"GBP","symbol":"£","category":"Fat Tyre","dealBucket":"🔥 Mega Deal","dealScore":48.3,"valueScore":48.3,"motor_power":"250W Road Legal","battery":"648Wh Lithium-Ion","range_miles":"See retailer listing","max_speed":"15.5 mph (EAPC)","is_uk_legal":true,"rrp":2548,"sale_price":2048,"price_from":true,"savings_amount":500,"discount_percentage":20,"image":"https://cdn.shopify.com/s/files/1/0627/1385/6182/files/1_11af9723-c33a-4926-8e83-d3a198a2091a.jpg?v=1764668047","url":"https://www.awin1.com/cread.php?awinmid=65774&awinaffid=3040709&clickref=dealspage&ued=https%3A%2F%2Fengwe-bikes-uk.com%2Fproducts%2Fl20-3-0-boost-combo%3Fvariant%3D43863663640758","badge_text":"SAVE £500 (20% OFF)","first_seen":"2026-09-09","is_new":true,"is_cached":false,"last_verified":"2026-09-10T20:25:06.240Z","lowest_price_30d":2048,"is_lowest_price_30d":true,"price_drop_amount":0,"last_checked":"2026-09-10T20:25:13.587Z"},{"id":"e_bikeshop_co_uk_15274196468096","title":"Orbea Rise LT H10 2026 (630Wh) (S 40cm / Escape Green)","brand":"Orbea Electric Bikes","retailer":"E-BikeShop UK","country":"UK","currency":"GBP","symbol":"£","category":"Commuter","dealBucket":"🔥 Mega Deal","dealScore":48,"valueScore":48,"motor_power":"Specification not confirmed","battery":"630Wh Lithium-Ion","range_miles":"30 - 140 Miles","max_speed":"Check retailer listing","is_uk_legal":false,"rrp":5699,"sale_price":4099,"price_from":true,"savings_amount":1600,"discount_percentage":28,"image":"https://cdn.shopify.com/s/files/1/0609/4838/1934/files/Orbea-Rise-LT-H10-630Wh-2026-Electric-Bike-Escape-Green.jpg?v=1758121471","url":"https://www.e-bikeshop.co.uk/products/electric-bike-orbea-rise-lt-h10-2026?variant=56372019429760","badge_text":"SAVE £1600 (28% OFF)","first_seen":"2026-09-10","is_new":true,"is_cached":false,"last_verified":"2026-09-10T20:25:03.692Z","lowest_price_30d":4099,"is_lowest_price_30d":true,"price_drop_amount":0,"last_checked":"2026-09-10T20:25:13.586Z"},{"id":"e_bikeshop_co_uk_8640524845294","title":"Orbea Rise LT M20 (630Wh) (XL 46cm / Cosmic Carbon)","brand":"Orbea Electric Bikes","retailer":"E-BikeShop UK","country":"UK","currency":"GBP","symbol":"£","category":"Fat Tyre","dealBucket":"🔥 Mega Deal","dealScore":48,"valueScore":48,"motor_power":"Specification not confirmed","battery":"630Wh Lithium-Ion","range_miles":"30 - 140 Miles","max_speed":"Check retailer listing","is_uk_legal":false,"rrp":6499,"sale_price":4699,"price_from":true,"savings_amount":1800,"discount_percentage":28,"image":"https://cdn.shopify.com/s/files/1/0609/4838/1934/files/Orbea-Rise-LT-M20-630W-2025-Electric-Bike-Cosmic-Carbon.jpg?v=1724337858","url":"https://www.e-bikeshop.co.uk/products/electric-bike-orbea-rise-lt-m20-2025-630wh?variant=45688550850798","badge_text":"SAVE £1800 (28% OFF)","first_seen":"2026-09-10","is_new":true,"is_cached":false,"last_verified":"2026-09-10T20:25:04.760Z","lowest_price_30d":4699,"is_lowest_price_30d":true,"price_drop_amount":0,"last_checked":"2026-09-10T20:25:13.587Z"},{"id":"leisure_lakes_mondraker_level_r","title":"Mondraker Level R Electric Bike 2026 (Chili Red/Super Black)","brand":"Mondraker","retailer":"Leisure Lakes Bikes","country":"UK","currency":"GBP","symbol":"£","category":"Mountain","dealBucket":"🔥 Mega Deal","dealScore":48,"valueScore":48,"motor_power":"250W Mid-Drive Bosch CX","battery":"750Wh Bosch PowerTube","range_miles":"45 - 80 Miles","max_speed":"15.5 mph (EAPC)","is_uk_legal":true,"rrp":5999,"sale_price":4299,"savings_amount":1700,"discount_percentage":28,"image":"https://www.leisurelakesbikes.com/images/products/m/mo/mondraker-level-r-electric-bike-2026.jpg","url":"https://www.awin1.com/cread.php?awinmid=6914&awinaffid=3040709&clickref=dealspage&ued=https%3A%2F%2Fwww.leisurelakesbikes.com%2Fbikes%2Felectric-bikes%2Fmondraker-level-r-electric-bike-2026-chili-redsuper-black__433857","badge_text":"SAVE £1700 (28% OFF)","first_seen":"2026-09-09","is_new":true,"last_verified":"2026-09-10T20:25:13.585Z","lowest_price_30d":4299,"is_lowest_price_30d":true,"price_drop_amount":0,"last_checked":"2026-09-10T20:25:13.587Z","is_cached":false},{"id":"e_bikeshop_co_uk_15351314284928","title":"Haibike Hybe CF 11 2026 (M 43cm)","brand":"Haibike Electric Bikes","retailer":"E-BikeShop UK","country":"UK","currency":"GBP","symbol":"£","category":"Mountain","dealBucket":"🔥 Mega Deal","dealScore":47,"valueScore":47,"motor_power":"250W Mid-Drive (EAPC)","battery":"800Wh Lithium-Ion","range_miles":"30 - 170 Miles","max_speed":"15.5 mph (EAPC)","is_uk_legal":true,"rrp":9549,"sale_price":6999,"price_from":true,"savings_amount":2550,"discount_percentage":27,"image":"https://cdn.shopify.com/s/files/1/0609/4838/1934/files/Haibike-Hybe-CF-11-2026-Electric-Bike-White.jpg?v=1765808619","url":"https://www.e-bikeshop.co.uk/products/electric-bike-haibike-hybe-cf-11-2026?variant=56606827315584","badge_text":"SAVE £2550 (27% OFF)","first_seen":"2026-09-10","is_new":true,"is_cached":false,"last_verified":"2026-09-10T20:25:02.471Z","lowest_price_30d":6999,"is_lowest_price_30d":true,"price_drop_amount":0,"last_checked":"2026-09-10T20:25:13.586Z"},{"id":"e_bikeshop_co_uk_15273266151808","title":"Orbea Wild M10 2026 Carbon (S 40cm / Seaweed Carbon)","brand":"Orbea Electric Bikes","retailer":"E-BikeShop UK","country":"UK","currency":"GBP","symbol":"£","category":"Mountain","dealBucket":"🔥 Mega Deal","dealScore":47,"valueScore":47,"motor_power":"250W Mid-Drive (EAPC)","battery":"750Wh Lithium-Ion","range_miles":"30 - 160 Miles","max_speed":"15.5 mph (EAPC)","is_uk_legal":true,"rrp":7399,"sale_price":5399,"price_from":true,"savings_amount":2000,"discount_percentage":27,"image":"https://cdn.shopify.com/s/files/1/0609/4838/1934/files/Orbea-Wild-M10-2026-Electric-Bike-Diamond-Carbon-View.jpg?v=1755092605","url":"https://www.e-bikeshop.co.uk/products/electric-bike-orbea-wild-m10-2026?variant=56369165730176","badge_text":"SAVE £2000 (27% OFF)","first_seen":"2026-09-10","is_new":true,"is_cached":false,"last_verified":"2026-09-10T20:25:03.692Z","lowest_price_30d":5399,"is_lowest_price_30d":true,"price_drop_amount":0,"last_checked":"2026-09-10T20:25:13.586Z"},{"id":"cyrusher_uk_7986263949525","title":"Trax 2.0 All-Terrain Electric Bike (Orange)","brand":"Cyrusher United Kingdom","retailer":"Cyrusher UK","country":"UK","currency":"GBP","symbol":"£","category":"Fat Tyre","dealBucket":"🔥 Mega Deal","dealScore":46.3,"valueScore":46.3,"motor_power":"Specification not confirmed","battery":"Specification not confirmed","range_miles":"See retailer listing","max_speed":"Check retailer listing","is_uk_legal":false,"rrp":2799,"sale_price":2299,"price_from":true,"savings_amount":500,"discount_percentage":18,"image":"https://cdn.shopify.com/s/files/1/0627/5861/7301/files/Cyrusher-E-Bike-Trax2.0-Main-Blue-11.jpg?v=1767939260","url":"https://www.cyrusher.co.uk/products/trax-ebike?variant=44009380020437","badge_text":"SAVE £500 (18% OFF)","first_seen":"2026-09-09","is_new":true,"is_cached":false,"last_verified":"2026-09-10T20:25:12.569Z","lowest_price_30d":2299,"is_lowest_price_30d":true,"price_drop_amount":0,"last_checked":"2026-09-10T20:25:13.587Z"},{"id":"cyrusher_uk_7934768644309","title":"Ranger 2.0 All-Terrain Electric Bike (Green)","brand":"Cyrusher United Kingdom","retailer":"Cyrusher UK","country":"UK","currency":"GBP","symbol":"£","category":"Fat Tyre","dealBucket":"🔥 Mega Deal","dealScore":46.3,"valueScore":46.3,"motor_power":"250W Road Legal","battery":"1040Wh (52V 20Ah)","range_miles":"See retailer listing","max_speed":"15.5 mph (EAPC)","is_uk_legal":true,"rrp":2799,"sale_price":2299,"price_from":true,"savings_amount":500,"discount_percentage":18,"image":"https://cdn.shopify.com/s/files/1/0627/5861/7301/files/Cyrusher_Ranger_2.0_ebike_Green-11.jpg?v=1767938841","url":"https://www.cyrusher.co.uk/products/ranger-ebike?variant=43851464540373","badge_text":"SAVE £500 (18% OFF)","first_seen":"2026-09-09","is_new":true,"is_cached":false,"last_verified":"2026-09-10T20:25:12.569Z","lowest_price_30d":2299,"is_lowest_price_30d":true,"price_drop_amount":0,"last_checked":"2026-09-10T20:25:13.587Z"},{"id":"e_bikeshop_co_uk_15273284239744","title":"Orbea Wild M20 2026 Carbon (S 40cm / Caramel Carbon)","brand":"Orbea Electric Bikes","retailer":"E-BikeShop UK","country":"UK","currency":"GBP","symbol":"£","category":"Fat Tyre","dealBucket":"🔥 Mega Deal","dealScore":46,"valueScore":46,"motor_power":"250W Mid-Drive (EAPC)","battery":"750Wh Lithium-Ion","range_miles":"30 - 140 Miles","max_speed":"15.5 mph (EAPC)","is_uk_legal":true,"rrp":6299,"sale_price":4649,"price_from":true,"savings_amount":1650,"discount_percentage":26,"image":"https://cdn.shopify.com/s/files/1/0609/4838/1934/files/Orbea-Wild-M20-2026-Electric-Bike-Caramel-Carbon-View.jpg?v=1755177185","url":"https://www.e-bikeshop.co.uk/products/electric-bike-orbea-wild-m20-2026?variant=56369200988544","badge_text":"SAVE £1650 (26% OFF)","first_seen":"2026-09-10","is_new":true,"is_cached":false,"last_verified":"2026-09-10T20:25:03.692Z","lowest_price_30d":4649,"is_lowest_price_30d":true,"price_drop_amount":0,"last_checked":"2026-09-10T20:25:13.586Z"},{"id":"e_bikeshop_co_uk_14779054162304","title":"Scott Patron eRide 900 (S 41cm)","brand":"Scott Electric Bikes","retailer":"E-BikeShop UK","country":"UK","currency":"GBP","symbol":"£","category":"Commuter","dealBucket":"🔥 Mega Deal","dealScore":46,"valueScore":46,"motor_power":"250W Mid-Drive (EAPC)","battery":"800Wh Lithium-Ion","range_miles":"30 - 170 Miles","max_speed":"15.5 mph (EAPC)","is_uk_legal":true,"rrp":8099,"sale_price":5999,"price_from":true,"savings_amount":2100,"discount_percentage":26,"image":"https://cdn.shopify.com/s/files/1/0609/4838/1934/files/Scott-Patron-eRide-900-2025-Carbon-Black-Electric-Bike.jpg?v=1733310811","url":"https://www.e-bikeshop.co.uk/products/electric-bike-scott-patron-eride-900-2025?variant=52555091640704","badge_text":"SAVE £2100 (26% OFF)","first_seen":"2026-09-10","is_new":true,"is_cached":false,"last_verified":"2026-09-10T20:25:04.760Z","lowest_price_30d":5999,"is_lowest_price_30d":true,"price_drop_amount":0,"last_checked":"2026-09-10T20:25:13.587Z"},{"id":"cyrusher_uk_8992605634773","title":"Loop 2.0 City Folding E-Bike (Black)","brand":"Cyrusher United Kingdom","retailer":"Cyrusher UK","country":"UK","currency":"GBP","symbol":"£","category":"Folding","dealBucket":"⚡ Budget Steal","dealScore":45.7,"valueScore":45.7,"motor_power":"250W Road Legal","battery":"Specification not confirmed","range_miles":"See retailer listing","max_speed":"15.5 mph (EAPC)","is_uk_legal":true,"rrp":799,"sale_price":699,"price_from":true,"savings_amount":100,"discount_percentage":13,"image":"https://cdn.shopify.com/s/files/1/0627/5861/7301/files/Cyrusher-Loop-City-Folding-E-Bike-Main-Black-07.jpg?v=1784881157","url":"https://www.cyrusher.co.uk/products/loop?variant=51590266290389","badge_text":"SAVE £100 (13% OFF)","first_seen":"2026-09-09","is_new":true,"is_cached":false,"last_verified":"2026-09-10T20:25:12.568Z","lowest_price_30d":699,"is_lowest_price_30d":true,"price_drop_amount":0,"last_checked":"2026-09-10T20:25:13.587Z"},{"id":"engwe_uk_8269327138998","title":"EP-2 3.0 Boost (Forest Green)","brand":"engwe uk","retailer":"Engwe UK Official","country":"UK","currency":"GBP","symbol":"£","category":"Fat Tyre","dealBucket":"Standard Deal","dealScore":45.3,"valueScore":45.3,"motor_power":"Specification not confirmed","battery":"648Wh Lithium-Ion","range_miles":"See retailer listing","max_speed":"Check retailer listing","is_uk_legal":false,"rrp":1299,"sale_price":1099,"price_from":true,"savings_amount":200,"discount_percentage":15,"image":"https://cdn.shopify.com/s/files/1/0627/1385/6182/files/EP-23.0boost01.jpg?v=1767084299","url":"https://www.awin1.com/cread.php?awinmid=65774&awinaffid=3040709&clickref=dealspage&ued=https%3A%2F%2Fengwe-bikes-uk.com%2Fproducts%2Fengwe-ep-2-3-0-boost%3Fvariant%3D44182278930614","badge_text":"SAVE £200 (15% OFF)","first_seen":"2026-09-09","is_new":true,"is_cached":false,"last_verified":"2026-09-10T20:25:06.240Z","lowest_price_30d":1099,"is_lowest_price_30d":true,"price_drop_amount":0,"last_checked":"2026-09-10T20:25:13.587Z"},{"id":"fiido_uk_8646547767597","title":"Fiido X Folding Electric Bike With Torque Sensor","brand":"fiido","retailer":"Fiido UK Official","country":"UK","currency":"GBP","symbol":"£","category":"Folding","dealBucket":"Standard Deal","dealScore":45.3,"valueScore":45.3,"motor_power":"Specification not confirmed","battery":"Specification not confirmed","range_miles":"See retailer listing","max_speed":"Check retailer listing","is_uk_legal":false,"rrp":1635,"sale_price":1399,"price_from":false,"savings_amount":236,"discount_percentage":14,"image":"https://cdn.shopify.com/s/files/1/0814/1232/5677/files/1-x.jpg?v=1787629926","url":"https://uk.fiido.com/products/fiido-x-folding-electric-bike?variant=46489909330221","badge_text":"SAVE £236 (14% OFF)","first_seen":"2026-09-10","is_new":true,"is_cached":false,"last_verified":"2026-09-10T20:25:10.779Z","lowest_price_30d":1399,"is_lowest_price_30d":true,"price_drop_amount":0,"last_checked":"2026-09-10T20:25:13.587Z"},{"id":"e_bikeshop_co_uk_14962264473984","title":"Orbea Gain M30i 105 Di2 Electric Road Bike (L 52cm / Halo Silver)","brand":"Orbea Electric Bikes","retailer":"E-BikeShop UK","country":"UK","currency":"GBP","symbol":"£","category":"Commuter","dealBucket":"🔥 Mega Deal","dealScore":45,"valueScore":45,"motor_power":"250W Mid-Drive (EAPC)","battery":"350Wh Lithium-Ion","range_miles":"30 - 120 Miles","max_speed":"15.5 mph (EAPC)","is_uk_legal":true,"rrp":5999,"sale_price":4499,"price_from":true,"savings_amount":1500,"discount_percentage":25,"image":"https://cdn.shopify.com/s/files/1/0609/4838/1934/files/Orbea-Gain-M30i-2025-Electric-Road-Bike-Silver.jpg?v=1737719082","url":"https://www.e-bikeshop.co.uk/products/electric-bike-orbea-gain-m30i-2025?variant=54868842578304","badge_text":"SAVE £1500 (25% OFF)","first_seen":"2026-09-10","is_new":true,"is_cached":false,"last_verified":"2026-09-10T20:25:03.694Z","lowest_price_30d":4499,"is_lowest_price_30d":true,"price_drop_amount":0,"last_checked":"2026-09-10T20:25:13.587Z"},{"id":"e_bikeshop_co_uk_8428177096942","title":"Orbea Rise SL M-LTD (630Wh) (M 41cm / Tanzanite Carbon)","brand":"Orbea Electric Bikes","retailer":"E-BikeShop UK","country":"UK","currency":"GBP","symbol":"£","category":"Commuter","dealBucket":"🔥 Mega Deal","dealScore":45,"valueScore":45,"motor_power":"Specification not confirmed","battery":"630Wh Lithium-Ion","range_miles":"30 - 140 Miles","max_speed":"Check retailer listing","is_uk_legal":false,"rrp":11179,"sale_price":8399,"price_from":true,"savings_amount":2780,"discount_percentage":25,"image":"https://cdn.shopify.com/s/files/1/0609/4838/1934/files/Orbea-Rise-SL-M-LTD-630W-Tanzanite-Carbon-View-2025-Electric-Bike_5e30dcea-7c97-4cda-b46e-571704bd9fb1.jpg?v=1715939606","url":"https://www.e-bikeshop.co.uk/products/electric-bike-orbea-rise-sl-m-ltd-2025-630wh?variant=45157035737326","badge_text":"SAVE £2780 (25% OFF)","first_seen":"2026-09-10","is_new":true,"is_cached":false,"last_verified":"2026-09-10T20:25:04.760Z","lowest_price_30d":8399,"is_lowest_price_30d":true,"price_drop_amount":0,"last_checked":"2026-09-10T20:25:13.587Z"},{"id":"e_bikeshop_co_uk_15351333126528","title":"Haibike AllMtn 6 2026 (S 40cm / Ocean Grey)","brand":"Haibike Electric Bikes","retailer":"E-BikeShop UK","country":"UK","currency":"GBP","symbol":"£","category":"Mountain","dealBucket":"🔥 Mega Deal","dealScore":44,"valueScore":44,"motor_power":"250W Mid-Drive (EAPC)","battery":"800Wh Lithium-Ion","range_miles":"30 - 170 Miles","max_speed":"15.5 mph (EAPC)","is_uk_legal":true,"rrp":4699,"sale_price":3799,"price_from":true,"savings_amount":900,"discount_percentage":19,"image":"https://cdn.shopify.com/s/files/1/0609/4838/1934/files/Haibike-AllMtn-6-2026-Electric-Bike-Ocean-Grey.jpg?v=1765813205","url":"https://www.e-bikeshop.co.uk/products/electric-bike-haibike-allmtn-6-2026?variant=56606927487360","badge_text":"SAVE £900 (19% OFF)","first_seen":"2026-09-09","is_new":true,"is_cached":false,"last_verified":"2026-09-10T20:25:02.471Z","lowest_price_30d":3799,"is_lowest_price_30d":true,"price_drop_amount":0,"last_checked":"2026-09-10T20:25:13.586Z"},{"id":"engwe_uk_8164311990454","title":"L20 3.0 Pro (Champagne)","brand":"Engwe-bikes-UK","retailer":"Engwe UK Official","country":"UK","currency":"GBP","symbol":"£","category":"Fat Tyre","dealBucket":"Standard Deal","dealScore":43.3,"valueScore":43.3,"motor_power":"250W Road Legal","battery":"720Wh Lithium-Ion","range_miles":"See retailer listing","max_speed":"15.5 mph (EAPC)","is_uk_legal":true,"rrp":1599,"sale_price":1399,"price_from":true,"savings_amount":200,"discount_percentage":13,"image":"https://cdn.shopify.com/s/files/1/0627/1385/6182/files/L20_3.0_Pro_0fe86aa6-653a-4afc-ae18-82d6a46a0710.png?v=1787720998","url":"https://www.awin1.com/cread.php?awinmid=65774&awinaffid=3040709&clickref=dealspage&ued=https%3A%2F%2Fengwe-bikes-uk.com%2Fproducts%2Fengwe-l20-3-0-pro%3Fvariant%3D43837036396726","badge_text":"SAVE £200 (13% OFF)","first_seen":"2026-09-09","is_new":true,"is_cached":false,"last_verified":"2026-09-10T20:25:06.240Z","lowest_price_30d":1399,"is_lowest_price_30d":true,"price_drop_amount":0,"last_checked":"2026-09-10T20:25:13.587Z"},{"id":"e_bikeshop_co_uk_15329443742080","title":"Scott Sub Tour 40 2026 Low (S 44cm)","brand":"Scott Electric Bikes","retailer":"E-BikeShop UK","country":"UK","currency":"GBP","symbol":"£","category":"Commuter","dealBucket":"🔥 Mega Deal","dealScore":43,"valueScore":43,"motor_power":"250W Mid-Drive (EAPC)","battery":"540Wh Lithium-Ion","range_miles":"30 - 130 Miles","max_speed":"15.5 mph (EAPC)","is_uk_legal":true,"rrp":2799,"sale_price":2349,"price_from":true,"savings_amount":450,"discount_percentage":16,"image":"https://cdn.shopify.com/s/files/1/0609/4838/1934/files/Scott-Sub-Tour-40-Low-2026-Electric-Bike.jpg?v=1758550757","url":"https://www.e-bikeshop.co.uk/products/electric-bike-scott-sub-tour-40-2026-low?variant=56532047987072","badge_text":"SAVE £450 (16% OFF)","first_seen":"2026-09-10","is_new":true,"is_cached":false,"last_verified":"2026-09-10T20:25:03.691Z","lowest_price_30d":2349,"is_lowest_price_30d":true,"price_drop_amount":0,"last_checked":"2026-09-10T20:25:13.586Z"},{"id":"e_bikeshop_co_uk_15329404125568","title":"Scott Sub Tour 40 2026 Crossbar (S 40cm)","brand":"Scott Electric Bikes","retailer":"E-BikeShop UK","country":"UK","currency":"GBP","symbol":"£","category":"Commuter","dealBucket":"🔥 Mega Deal","dealScore":43,"valueScore":43,"motor_power":"250W Mid-Drive (EAPC)","battery":"540Wh Lithium-Ion","range_miles":"30 - 130 Miles","max_speed":"15.5 mph (EAPC)","is_uk_legal":true,"rrp":2799,"sale_price":2349,"price_from":true,"savings_amount":450,"discount_percentage":16,"image":"https://cdn.shopify.com/s/files/1/0609/4838/1934/files/Scott-Sub-Tour-40-Crossbar-2026-Electric-Bike.jpg?v=1758024455","url":"https://www.e-bikeshop.co.uk/products/electric-bike-scott-sub-tour-40-2026-crossbar?variant=56531975242112","badge_text":"SAVE £450 (16% OFF)","first_seen":"2026-09-10","is_new":true,"is_cached":false,"last_verified":"2026-09-10T20:25:03.691Z","lowest_price_30d":2349,"is_lowest_price_30d":true,"price_drop_amount":0,"last_checked":"2026-09-10T20:25:13.586Z"},{"id":"e_bikeshop_co_uk_15274240082304","title":"Orbea Rise LT M-Team 2026 (630Wh) (S 40cm / Tanzanite Carbon)","brand":"Orbea Electric Bikes","retailer":"E-BikeShop UK","country":"UK","currency":"GBP","symbol":"£","category":"Commuter","dealBucket":"🔥 Mega Deal","dealScore":43,"valueScore":43,"motor_power":"Specification not confirmed","battery":"630Wh Lithium-Ion","range_miles":"30 - 140 Miles","max_speed":"Check retailer listing","is_uk_legal":false,"rrp":10231,"sale_price":7899,"price_from":true,"savings_amount":2332,"discount_percentage":23,"image":"https://cdn.shopify.com/s/files/1/0609/4838/1934/files/Orbea-Rise-LT-M-Team-630Wh-2026-Electric-Bike-Desert-Rose.jpg?v=1758212443","url":"https://www.e-bikeshop.co.uk/products/electric-bike-orbea-rise-lt-m-team-2026?variant=56372148601216","badge_text":"SAVE £2332 (23% OFF)","first_seen":"2026-09-10","is_new":true,"is_cached":false,"last_verified":"2026-09-10T20:25:03.691Z","lowest_price_30d":7899,"is_lowest_price_30d":true,"price_drop_amount":0,"last_checked":"2026-09-10T20:25:13.586Z"},{"id":"e_bikeshop_co_uk_15363969188224","title":"Haibike AllTrack 6 2026 (S 40cm)","brand":"Haibike Electric Bikes","retailer":"E-BikeShop UK","country":"UK","currency":"GBP","symbol":"£","category":"Mountain","dealBucket":"🔥 Mega Deal","dealScore":42,"valueScore":42,"motor_power":"250W Mid-Drive (EAPC)","battery":"800Wh Lithium-Ion","range_miles":"30 - 170 Miles","max_speed":"15.5 mph (EAPC)","is_uk_legal":true,"rrp":2949,"sale_price":2499,"price_from":false,"savings_amount":450,"discount_percentage":15,"image":"https://cdn.shopify.com/s/files/1/0609/4838/1934/files/Haibike-AllTrack-6-2026-Electric-Bike.jpg?v=1765897424","url":"https://www.e-bikeshop.co.uk/products/electric-bike-haibike-alltrack-6-2026?variant=56649505472896","badge_text":"SAVE £450 (15% OFF)","first_seen":"2026-09-10","is_new":true,"is_cached":false,"last_verified":"2026-09-10T20:25:02.472Z","lowest_price_30d":2499,"is_lowest_price_30d":true,"price_drop_amount":0,"last_checked":"2026-09-10T20:25:13.586Z"},{"id":"fiido_uk_8646533611821","title":"Fiido D11 Folding E-bike","brand":"fiido","retailer":"Fiido UK Official","country":"UK","currency":"GBP","symbol":"£","category":"Folding","dealBucket":"Standard Deal","dealScore":41.4,"valueScore":41.4,"motor_power":"Specification not confirmed","battery":"Specification not confirmed","range_miles":"See retailer listing","max_speed":"Check retailer listing","is_uk_legal":false,"rrp":999,"sale_price":910,"price_from":false,"savings_amount":89,"discount_percentage":9,"image":"https://cdn.shopify.com/s/files/1/0814/1232/5677/files/1-d11-grey_ea5209aa-e081-4fca-9a6b-485b132f4bea.webp?v=1747311454","url":"https://uk.fiido.com/products/fiido-d11-folding-electric-bike-for-commuter?variant=50703883305261","badge_text":"SAVE £89 (9% OFF)","first_seen":"2026-09-10","is_new":true,"is_cached":false,"last_verified":"2026-09-10T20:25:10.779Z","lowest_price_30d":910,"is_lowest_price_30d":true,"price_drop_amount":0,"last_checked":"2026-09-10T20:25:13.587Z"},{"id":"fiido_uk_9292643336493","title":"Fiido T2 Electric Cargo Bike: 200 kg Payload, 136.26 km Long Range (Forest green / Standard)","brand":"Fiido.uk","retailer":"Fiido UK Official","country":"UK","currency":"GBP","symbol":"£","category":"Cargo","dealBucket":"Standard Deal","dealScore":40.8,"valueScore":40.8,"motor_power":"Specification not confirmed","battery":"Specification not confirmed","range_miles":"See retailer listing","max_speed":"Check retailer listing","is_uk_legal":false,"rrp":1635,"sale_price":1454,"price_from":true,"savings_amount":181,"discount_percentage":11,"image":"https://cdn.shopify.com/s/files/1/0814/1232/5677/files/t2-main-1-green.jpg?v=1758698023","url":"https://uk.fiido.com/products/fiido-t2-longtail-cargo-ebike-for-versatile-all-terrain?variant=48607121572141","badge_text":"SAVE £181 (11% OFF)","first_seen":"2026-09-10","is_new":true,"is_cached":false,"last_verified":"2026-09-10T20:25:10.528Z","lowest_price_30d":1454,"is_lowest_price_30d":true,"price_drop_amount":0,"last_checked":"2026-09-10T20:25:13.587Z"},{"id":"e_bikeshop_co_uk_15363973841280","title":"Haibike AllTrack 4 2026 (S 43cm)","brand":"Haibike Electric Bikes","retailer":"E-BikeShop UK","country":"UK","currency":"GBP","symbol":"£","category":"Mountain","dealBucket":"🔥 Mega Deal","dealScore":40.7,"valueScore":40.7,"motor_power":"250W Mid-Drive (EAPC)","battery":"600Wh Lithium-Ion","range_miles":"30 - 140 Miles","max_speed":"15.5 mph (EAPC)","is_uk_legal":true,"rrp":2599,"sale_price":2199,"price_from":true,"savings_amount":400,"discount_percentage":15,"image":"https://cdn.shopify.com/s/files/1/0609/4838/1934/files/Haibike-AllTrack-4-2026-Electric-Bike.jpg?v=1765899542","url":"https://www.e-bikeshop.co.uk/products/electric-bike-haibike-alltrack-4-2026?variant=56649521660288","badge_text":"SAVE £400 (15% OFF)","first_seen":"2026-09-10","is_new":true,"is_cached":false,"last_verified":"2026-09-10T20:25:02.470Z","lowest_price_30d":2199,"is_lowest_price_30d":true,"price_drop_amount":0,"last_checked":"2026-09-10T20:25:13.586Z"},{"id":"e_bikeshop_co_uk_15364002873728","title":"Haibike Trekking 3 2026 Low (L 52cm)","brand":"Haibike Electric Bikes","retailer":"E-BikeShop UK","country":"UK","currency":"GBP","symbol":"£","category":"Mountain","dealBucket":"🔥 Mega Deal","dealScore":40.7,"valueScore":40.7,"motor_power":"250W Mid-Drive (EAPC)","battery":"500Wh Lithium-Ion","range_miles":"30 - 120 Miles","max_speed":"15.5 mph (EAPC)","is_uk_legal":true,"rrp":2599,"sale_price":2199,"price_from":false,"savings_amount":400,"discount_percentage":15,"image":"https://cdn.shopify.com/s/files/1/0609/4838/1934/files/Haibike-Trekking-3-Low-2026-Electric-Bike.jpg?v=1765971825","url":"https://www.e-bikeshop.co.uk/products/electric-bike-haibike-trekking-3-2026-low?variant=56649615573376","badge_text":"SAVE £400 (15% OFF)","first_seen":"2026-09-10","is_new":true,"is_cached":false,"last_verified":"2026-09-10T20:25:02.471Z","lowest_price_30d":2199,"is_lowest_price_30d":true,"price_drop_amount":0,"last_checked":"2026-09-10T20:25:13.586Z"},{"id":"e_bikeshop_co_uk_15364000973184","title":"Haibike Trekking 3 2026 Crossbar (M 52cm)","brand":"Haibike Electric Bikes","retailer":"E-BikeShop UK","country":"UK","currency":"GBP","symbol":"£","category":"Mountain","dealBucket":"🔥 Mega Deal","dealScore":40.7,"valueScore":40.7,"motor_power":"250W Mid-Drive (EAPC)","battery":"500Wh Lithium-Ion","range_miles":"30 - 120 Miles","max_speed":"15.5 mph (EAPC)","is_uk_legal":true,"rrp":2599,"sale_price":2199,"price_from":false,"savings_amount":400,"discount_percentage":15,"image":"https://cdn.shopify.com/s/files/1/0609/4838/1934/files/Haibike-Trekking-3-Crossbar-2026-Electric-Bike.jpg?v=1765901357","url":"https://www.e-bikeshop.co.uk/products/electric-bike-haibike-trekking-3-2026-crossbar?variant=56649612099968","badge_text":"SAVE £400 (15% OFF)","first_seen":"2026-09-10","is_new":true,"is_cached":false,"last_verified":"2026-09-10T20:25:02.472Z","lowest_price_30d":2199,"is_lowest_price_30d":true,"price_drop_amount":0,"last_checked":"2026-09-10T20:25:13.586Z"},{"id":"e_bikeshop_co_uk_15273325592960","title":"Orbea Denna M30 2026 GRX Electric Gravel Bike (S 44cm / Caramel Carbon)","brand":"Orbea Electric Bikes","retailer":"E-BikeShop UK","country":"UK","currency":"GBP","symbol":"£","category":"Commuter","dealBucket":"🔥 Mega Deal","dealScore":40,"valueScore":40,"motor_power":"Specification not confirmed","battery":"420Wh Lithium-Ion","range_miles":"30 - 120 Miles","max_speed":"Check retailer listing","is_uk_legal":false,"rrp":5199,"sale_price":4149,"price_from":true,"savings_amount":1050,"discount_percentage":20,"image":"https://cdn.shopify.com/s/files/1/0609/4838/1934/files/Orbea-Denna-M30-2026-Electric-Gravel-Bike-Caramel-Carbon-View.jpg?v=1755604986","url":"https://www.e-bikeshop.co.uk/products/electric-bike-orbea-denna-m30-2026?variant=56379563180416","badge_text":"SAVE £1050 (20% OFF)","first_seen":"2026-09-10","is_new":true,"is_cached":false,"last_verified":"2026-09-10T20:25:03.691Z","lowest_price_30d":4149,"is_lowest_price_30d":true,"price_drop_amount":0,"last_checked":"2026-09-10T20:25:13.586Z"},{"id":"e_bikeshop_co_uk_8673291698414","title":"Orbea Wild M-Team Carbon (S 40cm / Caramel Carbon)","brand":"Orbea Electric Bikes","retailer":"E-BikeShop UK","country":"UK","currency":"GBP","symbol":"£","category":"Mountain","dealBucket":"🔥 Mega Deal","dealScore":40,"valueScore":40,"motor_power":"250W Mid-Drive (EAPC)","battery":"750Wh Lithium-Ion","range_miles":"30 - 160 Miles","max_speed":"15.5 mph (EAPC)","is_uk_legal":true,"rrp":8499,"sale_price":6799,"price_from":false,"savings_amount":1700,"discount_percentage":20,"image":"https://cdn.shopify.com/s/files/1/0609/4838/1934/files/Orbea-Wild-M-Team-2025-Electric-Bike-Diamond-Carbon-View.jpg?v=1727874400","url":"https://www.e-bikeshop.co.uk/products/electric-bike-orbea-wild-m-team-2025-carbon?variant=45841591927022","badge_text":"SAVE £1700 (20% OFF)","first_seen":"2026-09-10","is_new":true,"is_cached":false,"last_verified":"2026-09-10T20:25:04.760Z","lowest_price_30d":6799,"is_lowest_price_30d":true,"price_drop_amount":0,"last_checked":"2026-09-10T20:25:13.587Z"},{"id":"e_bikeshop_co_uk_15213392429440","title":"Orbea Muga 30 2026 (FS) (S 40cm / Ivory)","brand":"Orbea Electric Bikes","retailer":"E-BikeShop UK","country":"UK","currency":"GBP","symbol":"£","category":"Mountain","dealBucket":"🔥 Mega Deal","dealScore":39.7,"valueScore":39.7,"motor_power":"250W Mid-Drive (EAPC)","battery":"600Wh Lithium-Ion","range_miles":"30 - 130 Miles","max_speed":"15.5 mph (EAPC)","is_uk_legal":true,"rrp":4399,"sale_price":3699,"price_from":true,"savings_amount":700,"discount_percentage":16,"image":"https://cdn.shopify.com/s/files/1/0609/4838/1934/files/Orbea-Muga-30-2026-Ivory-Electric-Bike.jpg?v=1765816967","url":"https://www.e-bikeshop.co.uk/products/electric-bike-orbea-muga-30-2026?variant=56133420679552","badge_text":"SAVE £700 (16% OFF)","first_seen":"2026-09-10","is_new":true,"is_cached":false,"last_verified":"2026-09-10T20:25:03.694Z","lowest_price_30d":3699,"is_lowest_price_30d":true,"price_drop_amount":0,"last_checked":"2026-09-10T20:25:13.587Z"},{"id":"e_bikeshop_co_uk_15453855973760","title":"Orbea Kemen ADV 20 2026 (S 42cm / Mars Red)","brand":"Orbea Electric Bikes","retailer":"E-BikeShop UK","country":"UK","currency":"GBP","symbol":"£","category":"Commuter","dealBucket":"🔥 Mega Deal","dealScore":38,"valueScore":38,"motor_power":"Specification not confirmed","battery":"630Wh Lithium-Ion","range_miles":"30 - 140 Miles","max_speed":"Check retailer listing","is_uk_legal":false,"rrp":3599,"sale_price":2999,"price_from":true,"savings_amount":600,"discount_percentage":17,"image":"https://cdn.shopify.com/s/files/1/0609/4838/1934/files/Orbea-Kemen-ADV-20-2026-Diamond-Black-Electric-Bike.jpg?v=1764244734","url":"https://www.e-bikeshop.co.uk/products/electric-bike-orbea-kemen-adv-30-2026?variant=56947676250496","badge_text":"SAVE £600 (17% OFF)","first_seen":"2026-09-10","is_new":true,"is_cached":false,"last_verified":"2026-09-10T20:25:02.472Z","lowest_price_30d":2999,"is_lowest_price_30d":true,"price_drop_amount":0,"last_checked":"2026-09-10T20:25:13.586Z"},{"id":"e_bikeshop_co_uk_15351339581824","title":"Haibike AllMtn 4 2026 (S 40cm)","brand":"Haibike Electric Bikes","retailer":"E-BikeShop UK","country":"UK","currency":"GBP","symbol":"£","category":"Mountain","dealBucket":"🔥 Mega Deal","dealScore":37.3,"valueScore":37.3,"motor_power":"250W Mid-Drive (EAPC)","battery":"800Wh Lithium-Ion","range_miles":"30 - 170 Miles","max_speed":"15.5 mph (EAPC)","is_uk_legal":true,"rrp":4249,"sale_price":3599,"price_from":true,"savings_amount":650,"discount_percentage":15,"image":"https://cdn.shopify.com/s/files/1/0609/4838/1934/files/Haibike-AllMtn-4-2026-Electric-Bike-Grey.jpg?v=1765887983","url":"https://www.e-bikeshop.co.uk/products/electric-bike-haibike-allmtn-4-2026?variant=56606955405696","badge_text":"SAVE £650 (15% OFF)","first_seen":"2026-09-09","is_new":true,"is_cached":false,"last_verified":"2026-09-10T20:25:02.470Z","lowest_price_30d":3599,"is_lowest_price_30d":true,"price_drop_amount":0,"last_checked":"2026-09-10T20:25:13.586Z"},{"id":"e_bikeshop_co_uk_15274492166528","title":"Orbea Urrun 20 Hardtail 2026 (S 40cm / Tanzanite Blue)","brand":"Orbea Electric Bikes","retailer":"E-BikeShop UK","country":"UK","currency":"GBP","symbol":"£","category":"Commuter","dealBucket":"🔥 Mega Deal","dealScore":37,"valueScore":37,"motor_power":"Specification not confirmed","battery":"630Wh Lithium-Ion","range_miles":"30 - 140 Miles","max_speed":"Check retailer listing","is_uk_legal":false,"rrp":3699,"sale_price":3099,"price_from":true,"savings_amount":600,"discount_percentage":16,"image":"https://cdn.shopify.com/s/files/1/0609/4838/1934/files/Orbea-Urrun-20-2026-Electric-Bike-Magnetic-Bronze.jpg?v=1755596366","url":"https://www.e-bikeshop.co.uk/products/electric-bike-orbea-urrun-20-2026?variant=56373387854208","badge_text":"SAVE £600 (16% OFF)","first_seen":"2026-09-10","is_new":true,"is_cached":false,"last_verified":"2026-09-10T20:25:03.692Z","lowest_price_30d":3099,"is_lowest_price_30d":true,"price_drop_amount":0,"last_checked":"2026-09-10T20:25:13.586Z"},{"id":"engwe_uk_8183413309622","title":"EP-2 Boost (Grey)","brand":"Engwe-bikes-UK","retailer":"Engwe UK Official","country":"UK","currency":"GBP","symbol":"£","category":"Fat Tyre","dealBucket":"⚡ Budget Steal","dealScore":36.3,"valueScore":36.3,"motor_power":"250W Road Legal","battery":"624Wh Lithium-Ion","range_miles":"See retailer listing","max_speed":"15.5 mph (EAPC)","is_uk_legal":true,"rrp":949,"sale_price":899,"price_from":true,"savings_amount":50,"discount_percentage":5,"image":"https://cdn.shopify.com/s/files/1/0627/1385/6182/files/2_f7000d51-73b1-442e-9190-8c7e25f9bf48.jpg?v=1767074687","url":"https://www.awin1.com/cread.php?awinmid=65774&awinaffid=3040709&clickref=dealspage&ued=https%3A%2F%2Fengwe-bikes-uk.com%2Fproducts%2Fengwe-ep-2-boost%3Fvariant%3D43912260845750","badge_text":"SAVE £50 (5% OFF)","first_seen":"2026-09-09","is_new":true,"is_cached":false,"last_verified":"2026-09-10T20:25:06.240Z","lowest_price_30d":899,"is_lowest_price_30d":true,"price_drop_amount":0,"last_checked":"2026-09-10T20:25:13.587Z"},{"id":"e_bikeshop_co_uk_15351344955776","title":"Haibike AllTrail 6 2026 (XL 50cm)","brand":"Haibike Electric Bikes","retailer":"E-BikeShop UK","country":"UK","currency":"GBP","symbol":"£","category":"Mountain","dealBucket":"🔥 Mega Deal","dealScore":36,"valueScore":36,"motor_power":"250W Mid-Drive (EAPC)","battery":"800Wh Lithium-Ion","range_miles":"30 - 170 Miles","max_speed":"15.5 mph (EAPC)","is_uk_legal":true,"rrp":3899,"sale_price":3299,"price_from":false,"savings_amount":600,"discount_percentage":15,"image":"https://cdn.shopify.com/s/files/1/0609/4838/1934/files/Haibike-AllTrail-6-2026-Electric-Bike-Brown.jpg?v=1765892427","url":"https://www.e-bikeshop.co.uk/products/electric-bike-haibike-alltrail-6-2026?variant=56606987714944","badge_text":"SAVE £600 (15% OFF)","first_seen":"2026-09-10","is_new":true,"is_cached":false,"last_verified":"2026-09-10T20:25:02.471Z","lowest_price_30d":3299,"is_lowest_price_30d":true,"price_drop_amount":0,"last_checked":"2026-09-10T20:25:13.586Z"},{"id":"e_bikeshop_co_uk_15273346105728","title":"Orbea Gain M30 2026 105 Electric Road Bike (XS 43cm / Halo Silver)","brand":"Orbea Electric Bikes","retailer":"E-BikeShop UK","country":"UK","currency":"GBP","symbol":"£","category":"Commuter","dealBucket":"🔥 Mega Deal","dealScore":36,"valueScore":36,"motor_power":"250W Mid-Drive (EAPC)","battery":"350Wh Lithium-Ion","range_miles":"30 - 120 Miles","max_speed":"15.5 mph (EAPC)","is_uk_legal":true,"rrp":5299,"sale_price":4449,"price_from":false,"savings_amount":850,"discount_percentage":16,"image":"https://cdn.shopify.com/s/files/1/0609/4838/1934/files/Orbea-Gain-M30-2026-Electric-Road-Bike-Fantasy-Purple.jpg?v=1755610474","url":"https://www.e-bikeshop.co.uk/products/electric-bike-orbea-gain-m30-2026?variant=56369290084736","badge_text":"SAVE £850 (16% OFF)","first_seen":"2026-09-10","is_new":true,"is_cached":false,"last_verified":"2026-09-10T20:25:03.691Z","lowest_price_30d":4449,"is_lowest_price_30d":true,"price_drop_amount":0,"last_checked":"2026-09-10T20:25:13.586Z"},{"id":"e_bikeshop_co_uk_15265409433984","title":"Cube Touring Hybrid One 600 2026 Easy (XS 46cm / Coal)","brand":"Cube Electric Bikes","retailer":"E-BikeShop UK","country":"UK","currency":"GBP","symbol":"£","category":"Commuter","dealBucket":"Standard Deal","dealScore":36,"valueScore":36,"motor_power":"250W Mid-Drive (EAPC)","battery":"600Wh Lithium-Ion","range_miles":"30 - 140 Miles","max_speed":"15.5 mph (EAPC)","is_uk_legal":true,"rrp":2399,"sale_price":2099,"price_from":true,"savings_amount":300,"discount_percentage":13,"image":"https://cdn.shopify.com/s/files/1/0609/4838/1934/files/Cube-Touring-Hybrid-One-600-2026-Easy-Coal-Electric-Bike.jpg?v=1756983679","url":"https://www.e-bikeshop.co.uk/products/electric-bike-cube-touring-hybrid-one-600-2026-easy?variant=56350972051840","badge_text":"SAVE £300 (13% OFF)","first_seen":"2026-09-10","is_new":true,"is_cached":false,"last_verified":"2026-09-10T20:25:03.693Z","lowest_price_30d":2099,"is_lowest_price_30d":true,"price_drop_amount":0,"last_checked":"2026-09-10T20:25:13.586Z"},{"id":"e_bikeshop_co_uk_15265372537216","title":"Cube Stereo Hybrid ONE77 HPC TM 800 2026 (S 37cm)","brand":"Cube Electric Bikes","retailer":"E-BikeShop UK","country":"UK","currency":"GBP","symbol":"£","category":"Commuter","dealBucket":"🔥 Mega Deal","dealScore":36,"valueScore":36,"motor_power":"250W Mid-Drive (EAPC)","battery":"800Wh Lithium-Ion","range_miles":"30 - 170 Miles","max_speed":"15.5 mph (EAPC)","is_uk_legal":true,"rrp":5499,"sale_price":4599,"price_from":false,"savings_amount":900,"discount_percentage":16,"image":"https://cdn.shopify.com/s/files/1/0609/4838/1934/files/Cube-Stereo-Hybrid-ONE77-HPC-TM-800-2026-Electric-Bike.jpg?v=1756984308","url":"https://www.e-bikeshop.co.uk/products/electric-bike-cube-stereo-hybrid-one77-hpc-tm-800-2026?variant=56350913593728","badge_text":"SAVE £900 (16% OFF)","first_seen":"2026-09-10","is_new":true,"is_cached":false,"last_verified":"2026-09-10T20:25:03.693Z","lowest_price_30d":4599,"is_lowest_price_30d":true,"price_drop_amount":0,"last_checked":"2026-09-10T20:25:13.586Z"},{"id":"e_bikeshop_co_uk_8670962221294","title":"Haibike Trekking 7 2026 Crossbar (S 40cm / Soft Grey)","brand":"Haibike Electric Bikes","retailer":"E-BikeShop UK","country":"UK","currency":"GBP","symbol":"£","category":"Mountain","dealBucket":"🔥 Mega Deal","dealScore":36,"valueScore":36,"motor_power":"250W Mid-Drive (EAPC)","battery":"800Wh Lithium-Ion","range_miles":"30 - 170 Miles","max_speed":"15.5 mph (EAPC)","is_uk_legal":true,"rrp":3899,"sale_price":3299,"price_from":true,"savings_amount":600,"discount_percentage":15,"image":"https://cdn.shopify.com/s/files/1/0609/4838/1934/files/Haibike-Trekking-7-2026-Crossbar-Electric-Bike-Soft-Grey.jpg?v=1762773918","url":"https://www.e-bikeshop.co.uk/products/electric-bike-haibike-trekking-7-2026-crossbar?variant=45827944808686","badge_text":"SAVE £600 (15% OFF)","first_seen":"2026-09-10","is_new":true,"is_cached":false,"last_verified":"2026-09-10T20:25:04.760Z","lowest_price_30d":3299,"is_lowest_price_30d":true,"price_drop_amount":0,"last_checked":"2026-09-10T20:25:13.587Z"},{"id":"e_bikeshop_co_uk_8670959993070","title":"Haibike Trekking 7 2026 Low (M 45cm / Soft Grey)","brand":"Haibike Electric Bikes","retailer":"E-BikeShop UK","country":"UK","currency":"GBP","symbol":"£","category":"Mountain","dealBucket":"🔥 Mega Deal","dealScore":36,"valueScore":36,"motor_power":"250W Mid-Drive (EAPC)","battery":"800Wh Lithium-Ion","range_miles":"30 - 170 Miles","max_speed":"15.5 mph (EAPC)","is_uk_legal":true,"rrp":3899,"sale_price":3299,"price_from":true,"savings_amount":600,"discount_percentage":15,"image":"https://cdn.shopify.com/s/files/1/0609/4838/1934/files/Haibike-Trekking-7-2026-Lowstep-Electric-Bike-Soft-Grey.jpg?v=1762774173","url":"https://www.e-bikeshop.co.uk/products/electric-bike-haibike-trekking-7-2026-low?variant=45827938189550","badge_text":"SAVE £600 (15% OFF)","first_seen":"2026-09-10","is_new":true,"is_cached":false,"last_verified":"2026-09-10T20:25:04.760Z","lowest_price_30d":3299,"is_lowest_price_30d":true,"price_drop_amount":0,"last_checked":"2026-09-10T20:25:13.587Z"},{"id":"engwe_uk_8800587808950","title":"ENGWE O20 Boost (Soft Purple)","brand":"ENGWE UK Official","retailer":"Engwe UK Official","country":"UK","currency":"GBP","symbol":"£","category":"Fat Tyre","dealBucket":"Standard Deal","dealScore":35.7,"valueScore":35.7,"motor_power":"Specification not confirmed","battery":"720Wh Lithium-Ion","range_miles":"See retailer listing","max_speed":"Check retailer listing","is_uk_legal":false,"rrp":1199,"sale_price":1099,"price_from":true,"savings_amount":100,"discount_percentage":8,"image":"https://cdn.shopify.com/s/files/1/0627/1385/6182/files/2053.png?v=1779963383","url":"https://www.awin1.com/cread.php?awinmid=65774&awinaffid=3040709&clickref=dealspage&ued=https%3A%2F%2Fengwe-bikes-uk.com%2Fproducts%2Fengwe-o20-boost%3Fvariant%3D45146393477302","badge_text":"SAVE £100 (8% OFF)","first_seen":"2026-09-09","is_new":true,"is_cached":false,"last_verified":"2026-09-10T20:25:06.240Z","lowest_price_30d":1099,"is_lowest_price_30d":true,"price_drop_amount":0,"last_checked":"2026-09-10T20:25:13.587Z"},{"id":"tenways_14735051161972","title":"TENWAYS CGO600 (CGO 600 New Edition / L / Midnight Black)","brand":"TENWAYS","retailer":"Tenways Direct","country":"UK/EU","currency":"GBP","symbol":"£","category":"Commuter","dealBucket":"Standard Deal","dealScore":35.7,"valueScore":35.7,"motor_power":"Specification not confirmed","battery":"Specification not confirmed","range_miles":"See retailer listing","max_speed":"Check retailer listing","is_uk_legal":false,"rrp":1299,"sale_price":1199,"price_from":true,"savings_amount":100,"discount_percentage":8,"image":"https://cdn.shopify.com/s/files/1/0563/3926/7733/files/Greygreen-1.webp?v=1758781276","url":"https://www.tenways.com/products/cgo600?variant=52328132444532","badge_text":"SAVE £100 (8% OFF)","first_seen":"2026-09-10","is_new":true,"is_cached":false,"last_verified":"2026-09-10T20:25:13.584Z","lowest_price_30d":1199,"is_lowest_price_30d":true,"price_drop_amount":0,"last_checked":"2026-09-10T20:25:13.587Z"},{"id":"e_bikeshop_co_uk_15262642372992","title":"Cube Reaction Hybrid ONE 600 2026 (S 37cm / Royal Green)","brand":"Cube Electric Bikes","retailer":"E-BikeShop UK","country":"UK","currency":"GBP","symbol":"£","category":"Commuter","dealBucket":"Standard Deal","dealScore":35,"valueScore":35,"motor_power":"250W Mid-Drive (EAPC)","battery":"600Wh Lithium-Ion","range_miles":"30 - 140 Miles","max_speed":"15.5 mph (EAPC)","is_uk_legal":true,"rrp":2499,"sale_price":2199,"price_from":false,"savings_amount":300,"discount_percentage":12,"image":"https://cdn.shopify.com/s/files/1/0609/4838/1934/files/Cube-Reaction-Hybrid-ONE-600-2026-Electric-Bike-Royal-Green.jpg?v=1756975111","url":"https://www.e-bikeshop.co.uk/products/electric-bike-cube-reaction-hybrid-one-600-2026?variant=56338038358400","badge_text":"SAVE £300 (12% OFF)","first_seen":"2026-09-10","is_new":true,"is_cached":false,"last_verified":"2026-09-10T20:25:03.693Z","lowest_price_30d":2199,"is_lowest_price_30d":true,"price_drop_amount":0,"last_checked":"2026-09-10T20:25:13.586Z"},{"id":"fiido_uk_10053672927533","title":"Fiido Nomads Touring E-bike (M 170cm(5'7'' ) - 190cm (6'3'') / Standard (79km))","brand":"Fiido.uk","retailer":"Fiido UK Official","country":"UK","currency":"GBP","symbol":"£","category":"Commuter","dealBucket":"Standard Deal","dealScore":34.5,"valueScore":34.5,"motor_power":"Specification not confirmed","battery":"Specification not confirmed","range_miles":"See retailer listing","max_speed":"Check retailer listing","is_uk_legal":false,"rrp":1363,"sale_price":1271,"price_from":true,"savings_amount":92,"discount_percentage":7,"image":"https://cdn.shopify.com/s/files/1/0814/1232/5677/files/11-sunstone-yellow-m.webp?v=1751938354","url":"https://uk.fiido.com/products/fiido-nomads-trekking-e-bike?variant=50921343516973","badge_text":"SAVE £92 (7% OFF)","first_seen":"2026-09-09","is_new":true,"is_cached":false,"last_verified":"2026-09-10T20:25:10.006Z","lowest_price_30d":1271,"is_lowest_price_30d":true,"price_drop_amount":0,"last_checked":"2026-09-10T20:25:13.587Z"},{"id":"e_bikeshop_co_uk_15351290003840","title":"Raleigh Novus 2026 Crossbar (Derailleur) (S 40cm)","brand":"Raleigh Electric Bikes","retailer":"E-BikeShop UK","country":"UK","currency":"GBP","symbol":"£","category":"Commuter","dealBucket":"Standard Deal","dealScore":34,"valueScore":34,"motor_power":"250W Mid-Drive (EAPC)","battery":"600Wh Lithium-Ion","range_miles":"30 - 140 Miles","max_speed":"15.5 mph (EAPC)","is_uk_legal":true,"rrp":2799,"sale_price":2499,"price_from":true,"savings_amount":300,"discount_percentage":11,"image":"https://cdn.shopify.com/s/files/1/0609/4838/1934/files/Raleigh-Novus-2026-Derailleur-Crossbar-Electric-Bike.jpg?v=1759315469","url":"https://www.e-bikeshop.co.uk/products/electric-bike-raleigh-novus-2026-crossbar-derailleur?variant=56606787305856","badge_text":"SAVE £300 (11% OFF)","first_seen":"2026-09-10","is_new":true,"is_cached":false,"last_verified":"2026-09-10T20:25:03.691Z","lowest_price_30d":2499,"is_lowest_price_30d":true,"price_drop_amount":0,"last_checked":"2026-09-10T20:25:13.586Z"},{"id":"e_bikeshop_co_uk_15262659051904","title":"Cube Reaction Hybrid ONE 800 2026 (S 37cm / Sleek Grey)","brand":"Cube Electric Bikes","retailer":"E-BikeShop UK","country":"UK","currency":"GBP","symbol":"£","category":"Commuter","dealBucket":"Standard Deal","dealScore":34,"valueScore":34,"motor_power":"250W Mid-Drive (EAPC)","battery":"800Wh Lithium-Ion","range_miles":"30 - 170 Miles","max_speed":"15.5 mph (EAPC)","is_uk_legal":true,"rrp":2699,"sale_price":2399,"price_from":false,"savings_amount":300,"discount_percentage":11,"image":"https://cdn.shopify.com/s/files/1/0609/4838/1934/files/Cube-Reaction-Hybrid-ONE-800-2026-Electric-Bike-Royal-Green.jpg?v=1756975959","url":"https://www.e-bikeshop.co.uk/products/electric-bike-cube-reaction-hybrid-one-800-2026?variant=56338111693184","badge_text":"SAVE £300 (11% OFF)","first_seen":"2026-09-10","is_new":true,"is_cached":false,"last_verified":"2026-09-10T20:25:03.693Z","lowest_price_30d":2399,"is_lowest_price_30d":true,"price_drop_amount":0,"last_checked":"2026-09-10T20:25:13.586Z"},{"id":"tenways_7919692218517","title":"TENWAYS AGO T (Pearl White)","brand":"TENWAYS","retailer":"Tenways Direct","country":"UK/EU","currency":"GBP","symbol":"£","category":"Commuter","dealBucket":"Standard Deal","dealScore":34,"valueScore":34,"motor_power":"Specification not confirmed","battery":"504Wh Lithium-Ion","range_miles":"See retailer listing","max_speed":"Check retailer listing","is_uk_legal":false,"rrp":2699,"sale_price":2399,"price_from":false,"savings_amount":300,"discount_percentage":11,"image":"https://cdn.shopify.com/s/files/1/0563/3926/7733/files/green_cc228123-abb2-4ae0-81f3-c8bd5166eec2.webp?v=1756713072","url":"https://www.tenways.com/products/ago-t?variant=43484494332053","badge_text":"SAVE £300 (11% OFF)","first_seen":"2026-09-10","is_new":true,"is_cached":false,"last_verified":"2026-09-10T20:25:13.585Z","lowest_price_30d":2399,"is_lowest_price_30d":true,"price_drop_amount":0,"last_checked":"2026-09-10T20:25:13.587Z"},{"id":"e_bikeshop_co_uk_15351351247232","title":"Haibike AllTrail 4 2026 (L 47cm)","brand":"Haibike Electric Bikes","retailer":"E-BikeShop UK","country":"UK","currency":"GBP","symbol":"£","category":"Mountain","dealBucket":"🔥 Mega Deal","dealScore":32.3,"valueScore":32.3,"motor_power":"250W Mid-Drive (EAPC)","battery":"600Wh Lithium-Ion","range_miles":"30 - 140 Miles","max_speed":"15.5 mph (EAPC)","is_uk_legal":true,"rrp":3499,"sale_price":2999,"price_from":false,"savings_amount":500,"discount_percentage":14,"image":"https://cdn.shopify.com/s/files/1/0609/4838/1934/files/Haibike-AllTrail-4-2026-Electric-Bike-Blue.jpg?v=1765893898","url":"https://www.e-bikeshop.co.uk/products/electric-bike-haibike-alltrail-4-2026?variant=56607029068160","badge_text":"SAVE £500 (14% OFF)","first_seen":"2026-09-10","is_new":true,"is_cached":false,"last_verified":"2026-09-10T20:25:02.470Z","lowest_price_30d":2999,"is_lowest_price_30d":true,"price_drop_amount":0,"last_checked":"2026-09-10T20:25:13.586Z"},{"id":"e_bikeshop_co_uk_8670953701614","title":"Haibike Trekking 6.5 2026 Low (S 40cm / Star Dust)","brand":"Haibike Electric Bikes","retailer":"E-BikeShop UK","country":"UK","currency":"GBP","symbol":"£","category":"Mountain","dealBucket":"🔥 Mega Deal","dealScore":32.3,"valueScore":32.3,"motor_power":"250W Mid-Drive (EAPC)","battery":"600Wh Lithium-Ion","range_miles":"30 - 170 Miles","max_speed":"15.5 mph (EAPC)","is_uk_legal":true,"rrp":3499,"sale_price":2999,"price_from":true,"savings_amount":500,"discount_percentage":14,"image":"https://cdn.shopify.com/s/files/1/0609/4838/1934/files/Haibike-Trekking-6.5-2026-Lowstep-Electric-Bike-Toffee.jpg?v=1762772239","url":"https://www.e-bikeshop.co.uk/products/electric-bike-haibike-trekking-6-5-2026-low?variant=56832999424384","badge_text":"SAVE £500 (14% OFF)","first_seen":"2026-09-10","is_new":true,"is_cached":false,"last_verified":"2026-09-10T20:25:04.760Z","lowest_price_30d":2999,"is_lowest_price_30d":true,"price_drop_amount":0,"last_checked":"2026-09-10T20:25:13.587Z"},{"id":"e_bikeshop_co_uk_8670948983022","title":"Haibike Trekking 6.5 2026 Crossbar (S 40cm / Toffee)","brand":"Haibike Electric Bikes","retailer":"E-BikeShop UK","country":"UK","currency":"GBP","symbol":"£","category":"Mountain","dealBucket":"🔥 Mega Deal","dealScore":32.3,"valueScore":32.3,"motor_power":"250W Mid-Drive (EAPC)","battery":"600Wh Lithium-Ion","range_miles":"30 - 140 Miles","max_speed":"15.5 mph (EAPC)","is_uk_legal":true,"rrp":3499,"sale_price":2999,"price_from":true,"savings_amount":500,"discount_percentage":14,"image":"https://cdn.shopify.com/s/files/1/0609/4838/1934/files/Haibike-Trekking-6.5-2026-Crossbar-Electric-Bike-Toffee.jpg?v=1762772009","url":"https://www.e-bikeshop.co.uk/products/electric-bike-haibike-trekking-6-5-2026-crossbar?variant=45827895754990","badge_text":"SAVE £500 (14% OFF)","first_seen":"2026-09-10","is_new":true,"is_cached":false,"last_verified":"2026-09-10T20:25:04.760Z","lowest_price_30d":2999,"is_lowest_price_30d":true,"price_drop_amount":0,"last_checked":"2026-09-10T20:25:13.587Z"},{"id":"e_bikeshop_co_uk_15274380263808","title":"Orbea Rise SL M-LTD 2026 (630Wh) (S 40cm / Tanzanite Carbon)","brand":"Orbea Electric Bikes","retailer":"E-BikeShop UK","country":"UK","currency":"GBP","symbol":"£","category":"Commuter","dealBucket":"🔥 Mega Deal","dealScore":32,"valueScore":32,"motor_power":"Specification not confirmed","battery":"630Wh Lithium-Ion","range_miles":"30 - 140 Miles","max_speed":"Check retailer listing","is_uk_legal":false,"rrp":10199,"sale_price":8999,"price_from":true,"savings_amount":1200,"discount_percentage":12,"image":"https://cdn.shopify.com/s/files/1/0609/4838/1934/files/Orbea-Rise-SL-M-Ltd-630Wh-2026-Electric-Bike-Desert-Rose.jpg?v=1758200509","url":"https://www.e-bikeshop.co.uk/products/electric-bike-orbea-rise-sl-m-ltd-2026?variant=56372827619712","badge_text":"SAVE £1200 (12% OFF)","first_seen":"2026-09-10","is_new":true,"is_cached":false,"last_verified":"2026-09-10T20:25:03.691Z","lowest_price_30d":8999,"is_lowest_price_30d":true,"price_drop_amount":0,"last_checked":"2026-09-10T20:25:13.586Z"},{"id":"e_bikeshop_co_uk_15351291412864","title":"Raleigh Captus 2026 Low (M 51cm)","brand":"Raleigh Electric Bikes","retailer":"E-BikeShop UK","country":"UK","currency":"GBP","symbol":"£","category":"Commuter","dealBucket":"Standard Deal","dealScore":31.3,"valueScore":31.3,"motor_power":"250W Mid-Drive (EAPC)","battery":"400Wh Lithium-Ion","range_miles":"30 - 120 Miles","max_speed":"15.5 mph (EAPC)","is_uk_legal":true,"rrp":1899,"sale_price":1699,"price_from":false,"savings_amount":200,"discount_percentage":11,"image":"https://cdn.shopify.com/s/files/1/0609/4838/1934/files/Raleigh-Captus-2026-Low-Electric-Bike.jpg?v=1759837151","url":"https://www.e-bikeshop.co.uk/products/electric-bike-raleigh-captus-2026-low?variant=56606791401856","badge_text":"SAVE £200 (11% OFF)","first_seen":"2026-09-10","is_new":true,"is_cached":false,"last_verified":"2026-09-10T20:25:03.691Z","lowest_price_30d":1699,"is_lowest_price_30d":true,"price_drop_amount":0,"last_checked":"2026-09-10T20:25:13.586Z"},{"id":"e_bikeshop_co_uk_15265359036800","title":"Cube Stereo Hybrid ONE44 HPC Race 800 2026 (S 37cm / Black)","brand":"Cube Electric Bikes","retailer":"E-BikeShop UK","country":"UK","currency":"GBP","symbol":"£","category":"Commuter","dealBucket":"🔥 Mega Deal","dealScore":31.3,"valueScore":31.3,"motor_power":"250W Mid-Drive (EAPC)","battery":"800Wh Lithium-Ion","range_miles":"30 - 170 Miles","max_speed":"15.5 mph (EAPC)","is_uk_legal":true,"rrp":3999,"sale_price":3499,"price_from":true,"savings_amount":500,"discount_percentage":13,"image":"https://cdn.shopify.com/s/files/1/0609/4838/1934/files/Cube-Stereo-Hybrid-ONE44-HPC-Race-800-2026-Black-Electric-Bike.jpg?v=1756987431","url":"https://www.e-bikeshop.co.uk/products/electric-bike-cube-stereo-hybrid-one44-hpc-race-800-2026?variant=56350873289088","badge_text":"SAVE £500 (13% OFF)","first_seen":"2026-09-10","is_new":true,"is_cached":false,"last_verified":"2026-09-10T20:25:03.693Z","lowest_price_30d":3499,"is_lowest_price_30d":true,"price_drop_amount":0,"last_checked":"2026-09-10T20:25:13.586Z"},{"id":"e_bikeshop_co_uk_15265394164096","title":"Cube AMS Hybrid 177 C:62 AT 600X 2026 (S 37cm)","brand":"Cube Electric Bikes","retailer":"E-BikeShop UK","country":"UK","currency":"GBP","symbol":"£","category":"Mountain","dealBucket":"🔥 Mega Deal","dealScore":31,"valueScore":31,"motor_power":"250W Mid-Drive (EAPC)","battery":"600Wh Lithium-Ion","range_miles":"30 - 140 Miles","max_speed":"15.5 mph (EAPC)","is_uk_legal":true,"rrp":7499,"sale_price":6699,"price_from":true,"savings_amount":800,"discount_percentage":11,"image":"https://cdn.shopify.com/s/files/1/0609/4838/1934/files/Cube-Stereo-Hybrid-177-C62-AT-600X-2026-Electric-Bike.jpg?v=1756990325","url":"https://www.e-bikeshop.co.uk/products/electric-bike-cube-ams-hybrid-177-c-62-at-600x-2026?variant=56350949114240","badge_text":"SAVE £800 (11% OFF)","first_seen":"2026-09-10","is_new":true,"is_cached":false,"last_verified":"2026-09-10T20:25:03.692Z","lowest_price_30d":6699,"is_lowest_price_30d":true,"price_drop_amount":0,"last_checked":"2026-09-10T20:25:13.586Z"},{"id":"e_bikeshop_co_uk_8670930403566","title":"Haibike AllTrack 6.5 2026 (S 40cm)","brand":"Haibike Electric Bikes","retailer":"E-BikeShop UK","country":"UK","currency":"GBP","symbol":"£","category":"Mountain","dealBucket":"🔥 Mega Deal","dealScore":31,"valueScore":31,"motor_power":"250W Mid-Drive (EAPC)","battery":"600Wh Lithium-Ion","range_miles":"30 - 130 Miles","max_speed":"15.5 mph (EAPC)","is_uk_legal":true,"rrp":3149,"sale_price":2699,"price_from":true,"savings_amount":450,"discount_percentage":14,"image":"https://cdn.shopify.com/s/files/1/0609/4838/1934/files/Haibike-AllTrack-6.5-2025-Electric-Bike.jpg?v=1727437387","url":"https://www.e-bikeshop.co.uk/products/electric-bike-haibike-alltrack-6-5-2026?variant=45827851682030","badge_text":"SAVE £450 (14% OFF)","first_seen":"2026-09-10","is_new":true,"is_cached":false,"last_verified":"2026-09-10T20:25:04.760Z","lowest_price_30d":2699,"is_lowest_price_30d":true,"price_drop_amount":0,"last_checked":"2026-09-10T20:25:13.587Z"},{"id":"fiido_uk_9981279502637","title":"Fiido C700 City E-Bike","brand":"Fiido.uk","retailer":"Fiido UK Official","country":"UK","currency":"GBP","symbol":"£","category":"Commuter","dealBucket":"Standard Deal","dealScore":30.9,"valueScore":30.9,"motor_power":"Specification not confirmed","battery":"Specification not confirmed","range_miles":"See retailer listing","max_speed":"Check retailer listing","is_uk_legal":false,"rrp":1727,"sale_price":1545,"price_from":false,"savings_amount":182,"discount_percentage":11,"image":"https://cdn.shopify.com/s/files/1/0814/1232/5677/files/c700-img-1_27e456ff-8f3e-40a5-8416-3ecbe87e04a4.webp?v=1743413208","url":"https://uk.fiido.com/products/fiido-c700-city-ebike?variant=50607052063021","badge_text":"SAVE £182 (11% OFF)","first_seen":"2026-09-10","is_new":true,"is_cached":false,"last_verified":"2026-09-10T20:25:10.528Z","lowest_price_30d":1545,"is_lowest_price_30d":true,"price_drop_amount":0,"last_checked":"2026-09-10T20:25:13.587Z"},{"id":"e_bikeshop_co_uk_15265362837888","title":"Cube Stereo Hybrid ONE77 HPC AT 800 2026 (S 37cm)","brand":"Cube Electric Bikes","retailer":"E-BikeShop UK","country":"UK","currency":"GBP","symbol":"£","category":"Commuter","dealBucket":"🔥 Mega Deal","dealScore":29.7,"valueScore":29.7,"motor_power":"250W Mid-Drive (EAPC)","battery":"800Wh Lithium-Ion","range_miles":"30 - 170 Miles","max_speed":"15.5 mph (EAPC)","is_uk_legal":true,"rrp":6499,"sale_price":5799,"price_from":true,"savings_amount":700,"discount_percentage":11,"image":"https://cdn.shopify.com/s/files/1/0609/4838/1934/files/Cube-Stereo-Hybrid-ONE77-HPC-AT-800-2026-Electric-Bike.jpg?v=1756985521","url":"https://www.e-bikeshop.co.uk/products/electric-bike-cube-stereo-hybrid-one77-hpc-at-800-2026?variant=56350891934080","badge_text":"SAVE £700 (11% OFF)","first_seen":"2026-09-10","is_new":true,"is_cached":false,"last_verified":"2026-09-10T20:25:03.693Z","lowest_price_30d":5799,"is_lowest_price_30d":true,"price_drop_amount":0,"last_checked":"2026-09-10T20:25:13.586Z"},{"id":"e_bikeshop_co_uk_15265357070720","title":"Cube Stereo Hybrid ONE44 HPC AT 800 2026 (S 37cm)","brand":"Cube Electric Bikes","retailer":"E-BikeShop UK","country":"UK","currency":"GBP","symbol":"£","category":"Commuter","dealBucket":"🔥 Mega Deal","dealScore":29.7,"valueScore":29.7,"motor_power":"250W Mid-Drive (EAPC)","battery":"800Wh Lithium-Ion","range_miles":"30 - 170 Miles","max_speed":"15.5 mph (EAPC)","is_uk_legal":true,"rrp":6499,"sale_price":5799,"price_from":true,"savings_amount":700,"discount_percentage":11,"image":"https://cdn.shopify.com/s/files/1/0609/4838/1934/files/Cube-Stereo-Hybrid-ONE44-HPC-AT-800-2026-Electric-Bike.jpg?v=1756986378","url":"https://www.e-bikeshop.co.uk/products/electric-bike-cube-stereo-hybrid-one44-hpc-at-800-2026?variant=56350867751296","badge_text":"SAVE £700 (11% OFF)","first_seen":"2026-09-10","is_new":true,"is_cached":false,"last_verified":"2026-09-10T20:25:03.693Z","lowest_price_30d":5799,"is_lowest_price_30d":true,"price_drop_amount":0,"last_checked":"2026-09-10T20:25:13.586Z"},{"id":"e_bikeshop_co_uk_15265360544128","title":"Cube Stereo Hybrid ONE44 HPC SLX 800 2026 (M 40cm / Slab Grey)","brand":"Cube Electric Bikes","retailer":"E-BikeShop UK","country":"UK","currency":"GBP","symbol":"£","category":"Commuter","dealBucket":"🔥 Mega Deal","dealScore":29.3,"valueScore":29.3,"motor_power":"250W Mid-Drive (EAPC)","battery":"800Wh Lithium-Ion","range_miles":"30 - 170 Miles","max_speed":"15.5 mph (EAPC)","is_uk_legal":true,"rrp":4499,"sale_price":3999,"price_from":false,"savings_amount":500,"discount_percentage":11,"image":"https://cdn.shopify.com/s/files/1/0609/4838/1934/files/Cube-Stereo-Hybrid-ONE44-HPC-SLX-800-2026-Slab-Grey-Electric-Bike.jpg?v=1756989294","url":"https://www.e-bikeshop.co.uk/products/electric-bike-cube-stereo-hybrid-one44-hpc-slx-800-2026?variant=56350882234752","badge_text":"SAVE £500 (11% OFF)","first_seen":"2026-09-10","is_new":true,"is_cached":false,"last_verified":"2026-09-10T20:25:03.692Z","lowest_price_30d":3999,"is_lowest_price_30d":true,"price_drop_amount":0,"last_checked":"2026-09-10T20:25:13.586Z"},{"id":"engwe_uk_8815718695094","title":"O20 Boost Combo (Soft Purple / Soft Purple)","brand":"ENGWE UK Official","retailer":"Engwe UK Official","country":"UK","currency":"GBP","symbol":"£","category":"Fat Tyre","dealBucket":"Standard Deal","dealScore":29.3,"valueScore":29.3,"motor_power":"Specification not confirmed","battery":"720Wh Lithium-Ion","range_miles":"See retailer listing","max_speed":"Check retailer listing","is_uk_legal":false,"rrp":2348,"sale_price":2148,"price_from":true,"savings_amount":200,"discount_percentage":9,"image":"https://cdn.shopify.com/s/files/1/0627/1385/6182/files/c0ad2be69190712bf2cde8f7cd4fee95.jpg?v=1782697074","url":"https://www.awin1.com/cread.php?awinmid=65774&awinaffid=3040709&clickref=dealspage&ued=https%3A%2F%2Fengwe-bikes-uk.com%2Fproducts%2Fengwe-o20-boost-combo%3Fvariant%3D45208262901942","badge_text":"SAVE £200 (9% OFF)","first_seen":"2026-09-09","is_new":true,"is_cached":false,"last_verified":"2026-09-10T20:25:06.240Z","lowest_price_30d":2148,"is_lowest_price_30d":true,"price_drop_amount":0,"last_checked":"2026-09-10T20:25:13.587Z"},{"id":"engwe_uk_8169596223670","title":"L20 3.0 Pro Combo (Champagne / Champagne)","brand":"Engwe-bikes-UK","retailer":"Engwe UK Official","country":"UK","currency":"GBP","symbol":"£","category":"Fat Tyre","dealBucket":"🔥 Mega Deal","dealScore":28.7,"valueScore":28.7,"motor_power":"250W Road Legal","battery":"720Wh Lithium-Ion","range_miles":"See retailer listing","max_speed":"15.5 mph (EAPC)","is_uk_legal":true,"rrp":3148,"sale_price":2748,"price_from":true,"savings_amount":400,"discount_percentage":13,"image":"https://cdn.shopify.com/s/files/1/0627/1385/6182/files/2_62ac8446-2ef9-4920-865c-204a7c655e2f.jpg?v=1764668058","url":"https://www.awin1.com/cread.php?awinmid=65774&awinaffid=3040709&clickref=dealspage&ued=https%3A%2F%2Fengwe-bikes-uk.com%2Fproducts%2Fl20-3-0-pro-combo%3Fvariant%3D43863640342710","badge_text":"SAVE £400 (13% OFF)","first_seen":"2026-09-09","is_new":true,"is_cached":false,"last_verified":"2026-09-10T20:25:06.240Z","lowest_price_30d":2748,"is_lowest_price_30d":true,"price_drop_amount":0,"last_checked":"2026-09-10T20:25:13.587Z"},{"id":"pedalgo_uk_10154801561864","title":"Amcargobikes - Ultimate Curve Electric (80% Assembled)","brand":"Amcargobikes","retailer":"PedalGo UK","country":"UK","currency":"GBP","symbol":"£","category":"Cargo","dealBucket":"🔥 Mega Deal","dealScore":28.7,"valueScore":28.7,"motor_power":"250W Road Legal","battery":"504Wh Lithium-Ion","range_miles":"See retailer listing","max_speed":"15.5 mph (EAPC)","is_uk_legal":true,"rrp":3100,"sale_price":2699,"price_from":true,"savings_amount":401,"discount_percentage":13,"image":"https://cdn.shopify.com/s/files/1/0911/9727/6424/files/Screenshot2025-04-05at00.01.32.jpg?v=1750519070","url":"https://www.awin1.com/cread.php?awinmid=114770&awinaffid=3040709&clickref=dealspage&ued=https%3A%2F%2Fpedalgo.co.uk%2Fproducts%2Felectric-cargo-bike-ultimate-curve%3Fvariant%3D51795342459144","badge_text":"SAVE £401 (13% OFF)","first_seen":"2026-09-09","is_new":true,"is_cached":false,"last_verified":"2026-09-10T20:25:09.390Z","lowest_price_30d":2699,"is_lowest_price_30d":true,"price_drop_amount":0,"last_checked":"2026-09-10T20:25:13.587Z"},{"id":"e_bikeshop_co_uk_15262688575872","title":"Cube Reaction Hybrid Race 800 2026 (S 37cm / Polar)","brand":"Cube Electric Bikes","retailer":"E-BikeShop UK","country":"UK","currency":"GBP","symbol":"£","category":"Commuter","dealBucket":"🔥 Mega Deal","dealScore":27.7,"valueScore":27.7,"motor_power":"250W Mid-Drive (EAPC)","battery":"800Wh Lithium-Ion","range_miles":"30 - 170 Miles","max_speed":"15.5 mph (EAPC)","is_uk_legal":true,"rrp":3299,"sale_price":2899,"price_from":true,"savings_amount":400,"discount_percentage":12,"image":"https://cdn.shopify.com/s/files/1/0609/4838/1934/files/Cube-Reaction-Hybrid-Race-800-2026-Electric-Bike-Lizard.jpg?v=1756978708","url":"https://www.e-bikeshop.co.uk/products/electric-bike-cube-reaction-hybrid-race-800-2026?variant=56338231198080","badge_text":"SAVE £400 (12% OFF)","first_seen":"2026-09-10","is_new":true,"is_cached":false,"last_verified":"2026-09-10T20:25:03.693Z","lowest_price_30d":2899,"is_lowest_price_30d":true,"price_drop_amount":0,"last_checked":"2026-09-10T20:25:13.586Z"},{"id":"e_bikeshop_co_uk_15265378500992","title":"Cube AMS Hybrid 177 C:62 TM 600X 2026 (S 37cm)","brand":"Cube Electric Bikes","retailer":"E-BikeShop UK","country":"UK","currency":"GBP","symbol":"£","category":"Mountain","dealBucket":"🔥 Mega Deal","dealScore":27,"valueScore":27,"motor_power":"250W Mid-Drive (EAPC)","battery":"600Wh Lithium-Ion","range_miles":"30 - 140 Miles","max_speed":"15.5 mph (EAPC)","is_uk_legal":true,"rrp":5499,"sale_price":4899,"price_from":true,"savings_amount":600,"discount_percentage":11,"image":"https://cdn.shopify.com/s/files/1/0609/4838/1934/files/Cube-Stereo-Hybrid-177-C62-TM-600X-2026-Electric-Bike.jpg?v=1756989943","url":"https://www.e-bikeshop.co.uk/products/electric-bike-cube-ams-hybrid-177-c-62-tm-600x-2026?variant=56350919754112","badge_text":"SAVE £600 (11% OFF)","first_seen":"2026-09-10","is_new":true,"is_cached":false,"last_verified":"2026-09-10T20:25:03.692Z","lowest_price_30d":4899,"is_lowest_price_30d":true,"price_drop_amount":0,"last_checked":"2026-09-10T20:25:13.586Z"},{"id":"e_bikeshop_co_uk_15265361953152","title":"Cube Stereo Hybrid ONE44 HPC TM 800 2026 (S 37cm)","brand":"Cube Electric Bikes","retailer":"E-BikeShop UK","country":"UK","currency":"GBP","symbol":"£","category":"Commuter","dealBucket":"🔥 Mega Deal","dealScore":27,"valueScore":27,"motor_power":"250W Mid-Drive (EAPC)","battery":"800Wh Lithium-Ion","range_miles":"30 - 170 Miles","max_speed":"15.5 mph (EAPC)","is_uk_legal":true,"rrp":5499,"sale_price":4899,"price_from":true,"savings_amount":600,"discount_percentage":11,"image":"https://cdn.shopify.com/s/files/1/0609/4838/1934/files/Cube-Stereo-Hybrid-ONE44-HPC-TM-800-2026-Electric-Bike.jpg?v=1756989581","url":"https://www.e-bikeshop.co.uk/products/electric-bike-cube-stereo-hybrid-one44-hpc-tm-800-2026?variant=56350885872000","badge_text":"SAVE £600 (11% OFF)","first_seen":"2026-09-10","is_new":true,"is_cached":false,"last_verified":"2026-09-10T20:25:03.692Z","lowest_price_30d":4899,"is_lowest_price_30d":true,"price_drop_amount":0,"last_checked":"2026-09-10T20:25:13.586Z"},{"id":"e_bikeshop_co_uk_15251699401088","title":"Cube Kathmandu Hybrid EXC 800 2026 Uni (XS 46cm)","brand":"Cube Electric Bikes","retailer":"E-BikeShop UK","country":"UK","currency":"GBP","symbol":"£","category":"Commuter","dealBucket":"🔥 Mega Deal","dealScore":26.7,"valueScore":26.7,"motor_power":"250W Mid-Drive (EAPC)","battery":"800Wh Lithium-Ion","range_miles":"30 - 170 Miles","max_speed":"15.5 mph (EAPC)","is_uk_legal":true,"rrp":3699,"sale_price":3299,"price_from":false,"savings_amount":400,"discount_percentage":11,"image":"https://cdn.shopify.com/s/files/1/0609/4838/1934/files/Cube-Kathmandu-Hybrid-EXC-800-2026-Uni-Electric-Bike.jpg?v=1756902631","url":"https://www.e-bikeshop.co.uk/products/electric-bike-cube-kathmandu-hybrid-exc-800-2026-uni?variant=56287133237632","badge_text":"SAVE £400 (11% OFF)","first_seen":"2026-09-10","is_new":true,"is_cached":false,"last_verified":"2026-09-10T20:25:03.693Z","lowest_price_30d":3299,"is_lowest_price_30d":true,"price_drop_amount":0,"last_checked":"2026-09-10T20:25:13.587Z"},{"id":"e_bikeshop_co_uk_15251693142400","title":"Cube Kathmandu Hybrid EXC 800 2026 Easy (XS 46cm)","brand":"Cube Electric Bikes","retailer":"E-BikeShop UK","country":"UK","currency":"GBP","symbol":"£","category":"Commuter","dealBucket":"🔥 Mega Deal","dealScore":26.7,"valueScore":26.7,"motor_power":"250W Mid-Drive (EAPC)","battery":"800Wh Lithium-Ion","range_miles":"30 - 170 Miles","max_speed":"15.5 mph (EAPC)","is_uk_legal":true,"rrp":3699,"sale_price":3299,"price_from":false,"savings_amount":400,"discount_percentage":11,"image":"https://cdn.shopify.com/s/files/1/0609/4838/1934/files/Cube-Kathmandu-Hybrid-EXC-800-2026-Easy-Electric-Bike.jpg?v=1756901977","url":"https://www.e-bikeshop.co.uk/products/electric-bike-cube-kathmandu-hybrid-exc-800-2026-easy?variant=56287124554112","badge_text":"SAVE £400 (11% OFF)","first_seen":"2026-09-10","is_new":true,"is_cached":false,"last_verified":"2026-09-10T20:25:03.694Z","lowest_price_30d":3299,"is_lowest_price_30d":true,"price_drop_amount":0,"last_checked":"2026-09-10T20:25:13.587Z"},{"id":"e_bikeshop_co_uk_15251665092992","title":"Cube Kathmandu Hybrid C:62 Pro 400X 2026 Crossbar (S 50cm)","brand":"Cube Electric Bikes","retailer":"E-BikeShop UK","country":"UK","currency":"GBP","symbol":"£","category":"Commuter","dealBucket":"🔥 Mega Deal","dealScore":26.7,"valueScore":26.7,"motor_power":"250W Mid-Drive (EAPC)","battery":"400Wh Lithium-Ion","range_miles":"30 - 120 Miles","max_speed":"15.5 mph (EAPC)","is_uk_legal":true,"rrp":3499,"sale_price":3099,"price_from":true,"savings_amount":400,"discount_percentage":11,"image":"https://cdn.shopify.com/s/files/1/0609/4838/1934/files/Cube-Kathmandu-Hybrid-C-62-Pro-400X-Crossbar-2026-Electric-Bike.jpg?v=1756893734","url":"https://www.e-bikeshop.co.uk/products/electric-bike-cube-kathmandu-hybrid-c-62-pro-400x-2026-crossbar?variant=56287078646144","badge_text":"SAVE £400 (11% OFF)","first_seen":"2026-09-10","is_new":true,"is_cached":false,"last_verified":"2026-09-10T20:25:03.694Z","lowest_price_30d":3099,"is_lowest_price_30d":true,"price_drop_amount":0,"last_checked":"2026-09-10T20:25:13.587Z"},{"id":"e_bikeshop_co_uk_15251731317120","title":"Cube Kathmandu Hybrid One 800 2026 Uni (XS 46cm / Metallic Grey)","brand":"Cube Electric Bikes","retailer":"E-BikeShop UK","country":"UK","currency":"GBP","symbol":"£","category":"Commuter","dealBucket":"💎 Premium Drop","dealScore":26.3,"valueScore":26.3,"motor_power":"250W Mid-Drive (EAPC)","battery":"800Wh Lithium-Ion","range_miles":"30 - 170 Miles","max_speed":"15.5 mph (EAPC)","is_uk_legal":true,"rrp":2999,"sale_price":2649,"price_from":true,"savings_amount":350,"discount_percentage":12,"image":"https://cdn.shopify.com/s/files/1/0609/4838/1934/files/Cube-Kathmandu-Hybrid-One-800-2026-Uni-Electric-Bike-Metallic-Grey.jpg?v=1756907330","url":"https://www.e-bikeshop.co.uk/products/electric-bike-cube-kathmandu-hybrid-one-800-2026-uni?variant=56287206637952","badge_text":"SAVE £350 (12% OFF)","first_seen":"2026-09-10","is_new":true,"is_cached":false,"last_verified":"2026-09-10T20:25:03.693Z","lowest_price_30d":2649,"is_lowest_price_30d":true,"price_drop_amount":0,"last_checked":"2026-09-10T20:25:13.586Z"},{"id":"e_bikeshop_co_uk_15251712704896","title":"Cube Kathmandu Hybrid One 800 2026 Crossbar (S 50cm / Metallic Grey)","brand":"Cube Electric Bikes","retailer":"E-BikeShop UK","country":"UK","currency":"GBP","symbol":"£","category":"Commuter","dealBucket":"💎 Premium Drop","dealScore":26.3,"valueScore":26.3,"motor_power":"250W Mid-Drive (EAPC)","battery":"800Wh Lithium-Ion","range_miles":"30 - 170 Miles","max_speed":"15.5 mph (EAPC)","is_uk_legal":true,"rrp":2999,"sale_price":2649,"price_from":true,"savings_amount":350,"discount_percentage":12,"image":"https://cdn.shopify.com/s/files/1/0609/4838/1934/files/Cube-Kathmandu-Hybrid-One-800-2026-Crossbar-Electric-Bike-Metallic-Grey.jpg?v=1756903412","url":"https://www.e-bikeshop.co.uk/products/electric-bike-cube-kathmandu-hybrid-one-800-2026-crossbar?variant=56287153226112","badge_text":"SAVE £350 (12% OFF)","first_seen":"2026-09-10","is_new":true,"is_cached":false,"last_verified":"2026-09-10T20:25:03.693Z","lowest_price_30d":2649,"is_lowest_price_30d":true,"price_drop_amount":0,"last_checked":"2026-09-10T20:25:13.586Z"},{"id":"e_bikeshop_co_uk_15262718034304","title":"Cube Reaction Hybrid SLX 800 2026 (S 37cm)","brand":"Cube Electric Bikes","retailer":"E-BikeShop UK","country":"UK","currency":"GBP","symbol":"£","category":"Commuter","dealBucket":"💎 Premium Drop","dealScore":24.3,"valueScore":24.3,"motor_power":"250W Mid-Drive (EAPC)","battery":"800Wh Lithium-Ion","range_miles":"30 - 170 Miles","max_speed":"15.5 mph (EAPC)","is_uk_legal":true,"rrp":3499,"sale_price":3149,"price_from":false,"savings_amount":350,"discount_percentage":10,"image":"https://cdn.shopify.com/s/files/1/0609/4838/1934/files/Cube-Reaction-Hybrid-SLX-800-2026-Electric-Bike.jpg?v=1756979401","url":"https://www.e-bikeshop.co.uk/products/electric-bike-cube-reaction-hybrid-slx-800-2026?variant=56338364039552","badge_text":"SAVE £350 (10% OFF)","first_seen":"2026-09-10","is_new":true,"is_cached":false,"last_verified":"2026-09-10T20:25:03.693Z","lowest_price_30d":3149,"is_lowest_price_30d":true,"price_drop_amount":0,"last_checked":"2026-09-10T20:25:13.586Z"},{"id":"tenways_8195597664405","title":"TENWAYS CGO600 Pro | Plus (CGO600 Pro New Edition (Belt Drive) / M / Willow Mist)","brand":"TENWAYS","retailer":"Tenways Direct","country":"UK/EU","currency":"GBP","symbol":"£","category":"Commuter","dealBucket":"Standard Deal","dealScore":23.7,"valueScore":23.7,"motor_power":"Specification not confirmed","battery":"Specification not confirmed","range_miles":"See retailer listing","max_speed":"Check retailer listing","is_uk_legal":false,"rrp":1799,"sale_price":1699,"price_from":true,"savings_amount":100,"discount_percentage":6,"image":"https://cdn.shopify.com/s/files/1/0563/3926/7733/files/willow_mist-1.webp?v=1775096126","url":"https://www.tenways.com/products/cgo600-pro?variant=54229821096308","badge_text":"SAVE £100 (6% OFF)","first_seen":"2026-09-10","is_new":true,"is_cached":false,"last_verified":"2026-09-10T20:25:13.584Z","lowest_price_30d":1699,"is_lowest_price_30d":true,"price_drop_amount":0,"last_checked":"2026-09-10T20:25:13.587Z"},{"id":"e_bikeshop_co_uk_15351285612928","title":"Raleigh Novus 2026 Low (Hub) (M 45cm)","brand":"Raleigh Electric Bikes","retailer":"E-BikeShop UK","country":"UK","currency":"GBP","symbol":"£","category":"Commuter","dealBucket":"💎 Premium Drop","dealScore":23,"valueScore":23,"motor_power":"250W Mid-Drive (EAPC)","battery":"600Wh Lithium-Ion","range_miles":"30 - 140 Miles","max_speed":"15.5 mph (EAPC)","is_uk_legal":true,"rrp":2899,"sale_price":2599,"price_from":false,"savings_amount":300,"discount_percentage":10,"image":"https://cdn.shopify.com/s/files/1/0609/4838/1934/files/Raleigh-Novus-2026-Hub-Low-Electric-Bike.jpg?v=1759315595","url":"https://www.e-bikeshop.co.uk/products/electric-bike-raleigh-novus-2026-low-hub?variant=56606778917248","badge_text":"SAVE £300 (10% OFF)","first_seen":"2026-09-10","is_new":true,"is_cached":false,"last_verified":"2026-09-10T20:25:03.691Z","lowest_price_30d":2599,"is_lowest_price_30d":true,"price_drop_amount":0,"last_checked":"2026-09-10T20:25:13.586Z"},{"id":"e_bikeshop_co_uk_15351288004992","title":"Raleigh Novus 2026 Crossbar (Hub) (M 45cm)","brand":"Raleigh Electric Bikes","retailer":"E-BikeShop UK","country":"UK","currency":"GBP","symbol":"£","category":"Commuter","dealBucket":"💎 Premium Drop","dealScore":23,"valueScore":23,"motor_power":"250W Mid-Drive (EAPC)","battery":"600Wh Lithium-Ion","range_miles":"30 - 140 Miles","max_speed":"15.5 mph (EAPC)","is_uk_legal":true,"rrp":2899,"sale_price":2599,"price_from":false,"savings_amount":300,"discount_percentage":10,"image":"https://cdn.shopify.com/s/files/1/0609/4838/1934/files/Raleigh-Novus-2026-Hub-Crossbar-Electric-Bike.jpg?v=1759315518","url":"https://www.e-bikeshop.co.uk/products/electric-bike-raleigh-novus-2026-crossbar-hub?variant=56606781669760","badge_text":"SAVE £300 (10% OFF)","first_seen":"2026-09-10","is_new":true,"is_cached":false,"last_verified":"2026-09-10T20:25:03.691Z","lowest_price_30d":2599,"is_lowest_price_30d":true,"price_drop_amount":0,"last_checked":"2026-09-10T20:25:13.586Z"},{"id":"e_bikeshop_co_uk_15262672650624","title":"Cube Reaction Hybrid Pro 800 2026 (S 37cm / Desert Stone)","brand":"Cube Electric Bikes","retailer":"E-BikeShop UK","country":"UK","currency":"GBP","symbol":"£","category":"Commuter","dealBucket":"💎 Premium Drop","dealScore":23,"valueScore":23,"motor_power":"250W Mid-Drive (EAPC)","battery":"800Wh Lithium-Ion","range_miles":"30 - 170 Miles","max_speed":"15.5 mph (EAPC)","is_uk_legal":true,"rrp":2899,"sale_price":2599,"price_from":false,"savings_amount":300,"discount_percentage":10,"image":"https://cdn.shopify.com/s/files/1/0609/4838/1934/files/Cube-Reaction-Hybrid-Pro-800-2026-Electric-Bike-Black.jpg?v=1756977516","url":"https://www.e-bikeshop.co.uk/products/electric-bike-cube-reaction-hybrid-pro-800-2026?variant=56338176770432","badge_text":"SAVE £300 (10% OFF)","first_seen":"2026-09-10","is_new":true,"is_cached":false,"last_verified":"2026-09-10T20:25:03.693Z","lowest_price_30d":2599,"is_lowest_price_30d":true,"price_drop_amount":0,"last_checked":"2026-09-10T20:25:13.586Z"},{"id":"tenways_14968231526772","title":"TENWAYS AGO AIR (Velvet Red / AGO AIR (Chain Drive))","brand":"TENWAYS","retailer":"Tenways Direct","country":"UK/EU","currency":"GBP","symbol":"£","category":"Commuter","dealBucket":"Standard Deal","dealScore":22.7,"valueScore":22.7,"motor_power":"Specification not confirmed","battery":"560Wh Lithium-Ion","range_miles":"See retailer listing","max_speed":"Check retailer listing","is_uk_legal":false,"rrp":2199,"sale_price":2099,"price_from":true,"savings_amount":100,"discount_percentage":5,"image":"https://cdn.shopify.com/s/files/1/0563/3926/7733/files/Air_Belt-Air_Belt_-_Velvet_Red_1.png?v=1774431627","url":"https://www.tenways.com/products/ago-air?variant=54052315234676","badge_text":"SAVE £100 (5% OFF)","first_seen":"2026-09-09","is_new":true,"is_cached":false,"last_verified":"2026-09-10T20:25:13.584Z","lowest_price_30d":2099,"is_lowest_price_30d":true,"price_drop_amount":0,"last_checked":"2026-09-10T20:25:13.587Z"},{"id":"tenways_8195598188693","title":"TENWAYS CGO800S | Plus (CGO800S (Belt Drive) / Sky Blue)","brand":"TENWAYS","retailer":"Tenways Direct","country":"UK/EU","currency":"GBP","symbol":"£","category":"Commuter","dealBucket":"Standard Deal","dealScore":22.7,"valueScore":22.7,"motor_power":"Specification not confirmed","battery":"Specification not confirmed","range_miles":"See retailer listing","max_speed":"Check retailer listing","is_uk_legal":false,"rrp":1899,"sale_price":1799,"price_from":true,"savings_amount":100,"discount_percentage":5,"image":"https://cdn.shopify.com/s/files/1/0563/3926/7733/files/1_ceb4832d-d55d-4521-a1ae-de18135198dc.webp?v=1788246543","url":"https://www.tenways.com/products/cgo800s?variant=44247685136533","badge_text":"SAVE £100 (5% OFF)","first_seen":"2026-09-10","is_new":true,"is_cached":false,"last_verified":"2026-09-10T20:25:13.584Z","lowest_price_30d":1799,"is_lowest_price_30d":true,"price_drop_amount":0,"last_checked":"2026-09-10T20:25:13.587Z"},{"id":"e_bikeshop_co_uk_15251744522624","title":"Cube Kathmandu Hybrid Pro 800 2026 Easy (S 50cm / Black)","brand":"Cube Electric Bikes","retailer":"E-BikeShop UK","country":"UK","currency":"GBP","symbol":"£","category":"Commuter","dealBucket":"💎 Premium Drop","dealScore":22,"valueScore":22,"motor_power":"250W Mid-Drive (EAPC)","battery":"800Wh Lithium-Ion","range_miles":"30 - 170 Miles","max_speed":"15.5 mph (EAPC)","is_uk_legal":true,"rrp":3299,"sale_price":2999,"price_from":true,"savings_amount":300,"discount_percentage":9,"image":"https://cdn.shopify.com/s/files/1/0609/4838/1934/files/Cube-Kathmandu-Hybrid-Pro-800-2026-Easy-Electric-Bike-Black.jpg?v=1756909605","url":"https://www.e-bikeshop.co.uk/products/electric-bike-cube-kathmandu-hybrid-pro-800-2026-easy?variant=56287245074816","badge_text":"SAVE £300 (9% OFF)","first_seen":"2026-09-10","is_new":true,"is_cached":false,"last_verified":"2026-09-10T20:25:03.693Z","lowest_price_30d":2999,"is_lowest_price_30d":true,"price_drop_amount":0,"last_checked":"2026-09-10T20:25:13.586Z"},{"id":"e_bikeshop_co_uk_15251739410816","title":"Cube Kathmandu Hybrid Pro 800 2026 Uni (S 50cm / Black)","brand":"Cube Electric Bikes","retailer":"E-BikeShop UK","country":"UK","currency":"GBP","symbol":"£","category":"Commuter","dealBucket":"💎 Premium Drop","dealScore":22,"valueScore":22,"motor_power":"250W Mid-Drive (EAPC)","battery":"800Wh Lithium-Ion","range_miles":"30 - 170 Miles","max_speed":"15.5 mph (EAPC)","is_uk_legal":true,"rrp":3299,"sale_price":2999,"price_from":true,"savings_amount":300,"discount_percentage":9,"image":"https://cdn.shopify.com/s/files/1/0609/4838/1934/files/Cube-Kathmandu-Hybrid-Pro-800-2026-Uni-Electric-Bike-Black.jpg?v=1756910405","url":"https://www.e-bikeshop.co.uk/products/electric-bike-cube-kathmandu-hybrid-pro-800-2026-uni?variant=56287235211648","badge_text":"SAVE £300 (9% OFF)","first_seen":"2026-09-10","is_new":true,"is_cached":false,"last_verified":"2026-09-10T20:25:03.693Z","lowest_price_30d":2999,"is_lowest_price_30d":true,"price_drop_amount":0,"last_checked":"2026-09-10T20:25:13.586Z"},{"id":"e_bikeshop_co_uk_15453830316416","title":"Orbea Muga 20 2026 (FS) (M 41cm / Blue Stone)","brand":"Orbea Electric Bikes","retailer":"E-BikeShop UK","country":"UK","currency":"GBP","symbol":"£","category":"Mountain","dealBucket":"🔥 Mega Deal","dealScore":18.7,"valueScore":18.7,"motor_power":"250W Mid-Drive (EAPC)","battery":"750Wh Lithium-Ion","range_miles":"30 - 140 Miles","max_speed":"15.5 mph (EAPC)","is_uk_legal":true,"rrp":5299,"sale_price":4899,"price_from":true,"savings_amount":400,"discount_percentage":8,"image":"https://cdn.shopify.com/s/files/1/0609/4838/1934/files/Orbea-Muga-20-2026-Metallic-Rust-Electric-Bike.jpg?v=1764247991","url":"https://www.e-bikeshop.co.uk/products/electric-bike-orbea-muga-20-2026?variant=56947615629696","badge_text":"SAVE £400 (8% OFF)","first_seen":"2026-09-10","is_new":true,"is_cached":false,"last_verified":"2026-09-10T20:25:02.472Z","lowest_price_30d":4899,"is_lowest_price_30d":true,"price_drop_amount":0,"last_checked":"2026-09-10T20:25:13.586Z"},{"id":"e_bikeshop_co_uk_15273358164352","title":"Orbea Gain M30i 2026 105 Di2 Electric Road Bike (M 49cm / Fantasy Purple)","brand":"Orbea Electric Bikes","retailer":"E-BikeShop UK","country":"UK","currency":"GBP","symbol":"£","category":"Commuter","dealBucket":"🔥 Mega Deal","dealScore":17.7,"valueScore":17.7,"motor_power":"250W Mid-Drive (EAPC)","battery":"350Wh Lithium-Ion","range_miles":"30 - 120 Miles","max_speed":"15.5 mph (EAPC)","is_uk_legal":true,"rrp":5999,"sale_price":5599,"price_from":true,"savings_amount":400,"discount_percentage":7,"image":"https://cdn.shopify.com/s/files/1/0609/4838/1934/files/Orbea-Gain-M30i-2026-Electric-Road-Bike-Halo-Silver.jpg?v=1755613270","url":"https://www.e-bikeshop.co.uk/products/electric-bike-orbea-gain-m30i-2026?variant=56369305289088","badge_text":"SAVE £400 (7% OFF)","first_seen":"2026-09-10","is_new":true,"is_cached":false,"last_verified":"2026-09-10T20:25:03.691Z","lowest_price_30d":5599,"is_lowest_price_30d":true,"price_drop_amount":0,"last_checked":"2026-09-10T20:25:13.586Z"},{"id":"e_bikeshop_co_uk_15265363788160","title":"Cube Stereo Hybrid ONE77 HPC Race 800 2026 (S 37cm / Iron Grey)","brand":"Cube Electric Bikes","retailer":"E-BikeShop UK","country":"UK","currency":"GBP","symbol":"£","category":"Commuter","dealBucket":"💎 Premium Drop","dealScore":17.7,"valueScore":17.7,"motor_power":"250W Mid-Drive (EAPC)","battery":"800Wh Lithium-Ion","range_miles":"30 - 170 Miles","max_speed":"15.5 mph (EAPC)","is_uk_legal":true,"rrp":3999,"sale_price":3749,"price_from":true,"savings_amount":250,"discount_percentage":6,"image":"https://cdn.shopify.com/s/files/1/0609/4838/1934/files/Cube-Stereo-Hybrid-ONE77-HPC-RACE-800-2026-Iron-Grey-Electric-Bike.jpg?v=1756985178","url":"https://www.e-bikeshop.co.uk/products/electric-bike-cube-stereo-hybrid-one77-hpc-race-800-2026?variant=56350892851584","badge_text":"SAVE £250 (6% OFF)","first_seen":"2026-09-10","is_new":true,"is_cached":false,"last_verified":"2026-09-10T20:25:03.693Z","lowest_price_30d":3749,"is_lowest_price_30d":true,"price_drop_amount":0,"last_checked":"2026-09-10T20:25:13.586Z"},{"id":"e_bikeshop_co_uk_15265429913984","title":"Cube Touring Hybrid Pro 800 2026 Uni (XS 46cm / Pearl Grey)","brand":"Cube Electric Bikes","retailer":"E-BikeShop UK","country":"UK","currency":"GBP","symbol":"£","category":"Commuter","dealBucket":"💎 Premium Drop","dealScore":17.3,"valueScore":17.3,"motor_power":"250W Mid-Drive (EAPC)","battery":"800Wh Lithium-Ion","range_miles":"30 - 170 Miles","max_speed":"15.5 mph (EAPC)","is_uk_legal":true,"rrp":2799,"sale_price":2599,"price_from":true,"savings_amount":200,"discount_percentage":7,"image":"https://cdn.shopify.com/s/files/1/0609/4838/1934/files/Cube-Touring-Hybrid-Pro-800-2026-Uni-Pearl-Grey-Electric-Bike.jpg?v=1756980611","url":"https://www.e-bikeshop.co.uk/products/electric-bike-cube-touring-hybrid-pro-800-2026-uni?variant=56351001936256","badge_text":"SAVE £200 (7% OFF)","first_seen":"2026-09-10","is_new":true,"is_cached":false,"last_verified":"2026-09-10T20:25:03.693Z","lowest_price_30d":2599,"is_lowest_price_30d":true,"price_drop_amount":0,"last_checked":"2026-09-10T20:25:13.586Z"},{"id":"e_bikeshop_co_uk_15265414840704","title":"Cube Touring Hybrid Pro 800 2026 Crossbar (S 50cm / Golden Lime)","brand":"Cube Electric Bikes","retailer":"E-BikeShop UK","country":"UK","currency":"GBP","symbol":"£","category":"Commuter","dealBucket":"💎 Premium Drop","dealScore":17.3,"valueScore":17.3,"motor_power":"250W Mid-Drive (EAPC)","battery":"800Wh Lithium-Ion","range_miles":"30 - 170 Miles","max_speed":"15.5 mph (EAPC)","is_uk_legal":true,"rrp":2799,"sale_price":2599,"price_from":false,"savings_amount":200,"discount_percentage":7,"image":"https://cdn.shopify.com/s/files/1/0609/4838/1934/files/Cube-Touring-Hybrid-Pro-800-2026-Crossbar-Pearl-Grey-Electric-Bike.jpg?v=1756982708","url":"https://www.e-bikeshop.co.uk/products/electric-bike-cube-touring-hybrid-pro-800-2026-crossbar?variant=56350979490176","badge_text":"SAVE £200 (7% OFF)","first_seen":"2026-09-10","is_new":true,"is_cached":false,"last_verified":"2026-09-10T20:25:03.693Z","lowest_price_30d":2599,"is_lowest_price_30d":true,"price_drop_amount":0,"last_checked":"2026-09-10T20:25:13.586Z"},{"id":"e_bikeshop_co_uk_15273444376960","title":"Orbea Gain M40 2026 Cues Electric Road Bike (S 46cm / Fantasy Purple)","brand":"Orbea Electric Bikes","retailer":"E-BikeShop UK","country":"UK","currency":"GBP","symbol":"£","category":"Commuter","dealBucket":"💎 Premium Drop","dealScore":14,"valueScore":14,"motor_power":"250W Mid-Drive (EAPC)","battery":"350Wh Lithium-Ion","range_miles":"30 - 120 Miles","max_speed":"15.5 mph (EAPC)","is_uk_legal":true,"rrp":4999,"sale_price":4699,"price_from":true,"savings_amount":300,"discount_percentage":6,"image":"https://cdn.shopify.com/s/files/1/0609/4838/1934/files/Orbea-Gain-M40-2026-Electric-Road-Bike-Fantasy-Purple.jpg?v=1755614812","url":"https://www.e-bikeshop.co.uk/products/electric-bike-orbea-gain-m40-2026?variant=56369669931392","badge_text":"SAVE £300 (6% OFF)","first_seen":"2026-09-10","is_new":true,"is_cached":false,"last_verified":"2026-09-10T20:25:03.691Z","lowest_price_30d":4699,"is_lowest_price_30d":true,"price_drop_amount":0,"last_checked":"2026-09-10T20:25:13.586Z"}];
        let curCat = 'all';
        let lastUpdatedStr = "2026-09-10 20:25:13 (Auto-updated daily)";

        function formatLastUpdated(dateStr) {
          if (!dateStr) return lastUpdatedStr;
          try {
            const d = new Date(dateStr.replace(' ', 'T') + 'Z');
            if (isNaN(d.getTime())) return dateStr;
            return d.toLocaleDateString('en-GB', { day: 'numeric', month: 'short', year: 'numeric' }) + ' at ' + d.toLocaleTimeString('en-GB', { hour: '2-digit', minute: '2-digit' }) + ' BST';
          } catch(e) {
            return dateStr;
          }
        }

                        async function fetchTopDeals() {
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
        }

        window.rgbFilter = function(cat, btn) {
          curCat = cat;
          document.querySelectorAll('.rgb-pill').forEach(b => b.classList.remove('active'));
          btn.classList.add('active');
          rgbApplyFilters();
        };

                                                                                                                function rgbGetPriceBand(price) {
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
          const safeTitle = (d.title || '').replace(/'/g, "\\'");
          return `
            <article class="rgb-card">
              <div class="rgb-badge-discount">SAVE ${sym}${savings} (${d.discount_percentage}% OFF)</div>
              ${d.is_lowest_price_30d ? '<div class="rgb-badge-low30">🔥 30-Day Low</div>' : (d.is_new ? '<div class="rgb-badge-new">✨ Just Added</div>' : '')}
              ${d.price_drop_amount > 0 ? `<div class="rgb-badge-drop">📉 Dropped ${sym}${d.price_drop_amount}</div>` : ''}
              <div class="rgb-card-img-wrap">
                <img src="${d.image}" alt="${d.title}" class="rgb-card-img skip-lazy" data-no-lazy="1" width="360" height="220" loading="lazy" decoding="async" onerror="this.src='https://images.unsplash.com/photo-1571068316344-75bc76f77890?w=600'">
              </div>
              <div class="rgb-card-body">
                <div class="rgb-row">
                  <span class="rgb-retailer">${d.retailer}</span>
                  <span class="rgb-score">Score: ${score}</span>
                </div>
                <h3 class="rgb-card-title">${d.title}</h3>
                <div class="rgb-specs">
                  <div><span class="rgb-spec-lbl">Category</span><div class="rgb-spec-val">${d.category}</div></div>
                  <div><span class="rgb-spec-lbl">Motor</span><div class="rgb-spec-val" title="${d.motor_power}">${d.motor_power === 'Specification not confirmed' ? '<span style="color:#94a3b8;">Not confirmed</span>' : d.motor_power}</div></div>
                  <div><span class="rgb-spec-lbl">Battery</span><div class="rgb-spec-val" title="${d.battery}">${d.battery === 'Specification not confirmed' ? '<span style="color:#94a3b8;">Not confirmed</span>' : d.battery}</div></div>
                  <div><span class="rgb-spec-lbl">UK Status</span><div class="rgb-spec-val">${d.is_uk_legal ? '<span style="color:#10b981;">✅ Road Legal</span>' : (d.motor_power === 'Specification not confirmed' ? '<span style="color:#f59e0b;">⚠️ Check Retailer</span>' : '<span style="color:#ef4444;">⚠️ Off-Road</span>')}</div></div>
                </div>
                <div class="rgb-price-row">
                  <div>
                    <div class="rgb-sale-price">${sym}${d.sale_price.toLocaleString('en-GB', {minimumFractionDigits: 2})}</div>
                    ${d.rrp ? `<span class="rgb-rrp">Was ${sym}${d.rrp.toLocaleString('en-GB', {minimumFractionDigits: 2})}</span>` : ''}
                  </div>
                  <span class="rgb-savings">Save ${sym}${savings}</span>
                </div>
                <a href="${d.url}" target="_blank" rel="sponsored nofollow noopener" class="rgb-btn" onclick="rgbTrackDealClick('${d.id}', '${safeTitle}', '${d.retailer}', ${d.sale_price}, ${score})">
                  👉 View Deal at ${d.retailer} ➔
                </a>
              </div>
            </article>
          `;
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
              html += `
                <section class="rgb-section">
                  <div class="rgb-section-header">
                    <div class="rgb-section-title-wrap">
                      <h3 class="rgb-section-title">⚡ Best Value Under £1,500</h3>
                      <span class="rgb-section-badge rgb-badge-budget">Budget Champions</span>
                    </div>
                    <p class="rgb-section-sub">Highest-scoring road legal & commuter e-bikes for everyday UK riders. Ranked by genuine value, not luxury price tags.</p>
                  </div>
                  <div class="rgb-grid">
                    ${featuredUnder1500.map(rgbRenderDealCard).join('')}
                  </div>
                </section>
              `;
            }

            if (featuredMid.length > 0) {
              html += `
                <section class="rgb-section">
                  <div class="rgb-section-header">
                    <div class="rgb-section-title-wrap">
                      <h3 class="rgb-section-title">🚲 Strong Mid-Range Deals (£1,500 – £3,000)</h3>
                      <span class="rgb-section-badge rgb-badge-mid">Performance Value</span>
                    </div>
                    <p class="rgb-section-sub">Upgraded motors, torque sensors, and larger range batteries offering serious long-term value.</p>
                  </div>
                  <div class="rgb-grid">
                    ${featuredMid.map(rgbRenderDealCard).join('')}
                  </div>
                </section>
              `;
            }

            if (featuredPremium.length > 0) {
              html += `
                <section class="rgb-section">
                  <div class="rgb-section-header">
                    <div class="rgb-section-title-wrap">
                      <h3 class="rgb-section-title">💎 Premium Clearance Deals (£3,000+)</h3>
                      <span class="rgb-section-badge rgb-badge-premium">Biggest Cash Savings</span>
                    </div>
                    <p class="rgb-section-sub">Massive clearance cuts on high-end carbon e-MTBs and premium European builds.</p>
                  </div>
                  <div class="rgb-grid">
                    ${featuredPremium.map(rgbRenderDealCard).join('')}
                  </div>
                </section>
              `;
            }

            if (remainingDeals.length > 0) {
              html += `
                <section class="rgb-section">
                  <div class="rgb-section-header">
                    <div class="rgb-section-title-wrap">
                      <h3 class="rgb-section-title">📋 Complete E-Bike Deal Directory</h3>
                      <span class="rgb-section-badge rgb-badge-all">${remainingDeals.length} More Deals</span>
                    </div>
                    <p class="rgb-section-sub">Browse every remaining verified price cut currently tracked across UK retailers, ordered by overall value score.</p>
                  </div>
                  <div class="rgb-grid">
                    ${remainingDeals.map(rgbRenderDealCard).join('')}
                  </div>
                </section>
              `;
            }

            container.innerHTML = html;
          } else {
            if (filtered.length === 0) {
              container.innerHTML = '<div style="text-align: center; padding: 3rem 1rem; color: #94a3b8; font-size: 1.1rem; grid-column: 1 / -1;">No e-bikes found matching your criteria. Try loosening your search filters!</div>';
            } else {
              container.innerHTML = '<div class="rgb-grid">' + filtered.map(rgbRenderDealCard).join('') + '</div>';
            }
          }
        };

        rgbApplyFilters();
        fetchTopDeals();

        // BFCache (Back-Forward Cache) safeguard: ensures layout and cards restore properly
        window.addEventListener('pageshow', function(e) {
          const container = document.getElementById('rgbDealsContainer');
          if (container && (!container.children || container.children.length === 0)) {
            rgbApplyFilters();
          }
        });
      })();
    </script>
    <?php
    return ob_get_clean();
}

add_shortcode('ebike_deals', 'rgb_register_deal_finder_shortcode');
add_shortcode('ebike_deal_finder', 'rgb_register_deal_finder_shortcode');
