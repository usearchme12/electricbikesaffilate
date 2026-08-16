<?php
/**
 * Plugin Name: Reight Good Bikes - Top 10 E-Bike Deals
 * Plugin URI: https://reightgoodbikes.co.uk/
 * Description: Embeds an interactive, 100% native Top 10 UK Electric Bike Deals & Clearance Offers page via shortcode [ebike_deal_finder]. Links directly to external offers on E-BikeShop UK and Amazon. Zero iframes.
 * Version: 1.2.0
 * Author: Reight Good Bikes
 * Text Domain: reight-deals
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

function rgb_register_deal_finder_shortcode() {
    ob_start();
    ?>
    <div id="rgb-deal-finder-root" class="rgb-deal-finder-wrapper">
      <style>
        .rgb-deal-finder-wrapper {
          --rgb-card-bg: #131b2e;
          --rgb-border: #23314f;
          --rgb-primary: #f59e0b;
          --rgb-primary-hover: #d97706;
          --rgb-neon: #10b981;
          --rgb-blue: #38bdf8;
          --rgb-red: #ef4444;
          --rgb-text: #f8fafc;
          --rgb-muted: #94a3b8;
          font-family: 'Inter', system-ui, sans-serif;
          color: var(--rgb-text);
          margin: 2rem 0;
        }

        .rgb-deal-finder-wrapper * { box-sizing: border-box; }

        .rgb-hero {
          text-align: center;
          padding: 2rem 1rem;
        }
        .rgb-hero-badge {
          display: inline-block;
          background: rgba(245, 158, 11, 0.15);
          border: 1px solid rgba(245, 158, 11, 0.3);
          color: var(--rgb-primary);
          padding: 0.35rem 0.9rem;
          border-radius: 9999px;
          font-size: 0.8rem;
          font-weight: 700;
          margin-bottom: 0.75rem;
          text-transform: uppercase;
        }
        .rgb-hero h2 {
          font-size: clamp(1.8rem, 4vw, 2.4rem);
          font-weight: 800;
          color: #fff;
          margin-bottom: 0.5rem;
        }
        .rgb-hero p {
          color: var(--rgb-muted);
          font-size: 1rem;
          max-width: 600px;
          margin: 0 auto 1.5rem;
        }

        .rgb-filter-bar {
          background: var(--rgb-card-bg);
          border: 1px solid var(--rgb-border);
          border-radius: 12px;
          padding: 1rem 1.25rem;
          margin-bottom: 2rem;
          display: flex;
          justify-content: space-between;
          align-items: center;
          gap: 1rem;
          flex-wrap: wrap;
        }
        .rgb-pills { display: flex; gap: 0.4rem; flex-wrap: wrap; }
        .rgb-pill {
          background: #090d16;
          border: 1px solid var(--rgb-border);
          color: var(--rgb-muted);
          padding: 0.4rem 0.8rem;
          border-radius: 6px;
          font-size: 0.8rem;
          font-weight: 600;
          cursor: pointer;
          transition: all 0.2s;
        }
        .rgb-pill:hover { background: #1e293b; color: #fff; }
        .rgb-pill.active { background: var(--rgb-primary); border-color: var(--rgb-primary); color: #000; font-weight: 800; }

        .rgb-sort {
          background: #090d16;
          border: 1px solid var(--rgb-border);
          color: #fff;
          padding: 0.5rem 0.9rem;
          border-radius: 6px;
          font-size: 0.8rem;
          outline: none;
          cursor: pointer;
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
          font-size: 0.7rem;
          font-weight: 800;
          padding: 0.25rem 0.55rem;
          border-radius: 4px;
          text-transform: uppercase;
          z-index: 2;
        }

        .rgb-card-img { width: 100%; height: 210px; object-fit: cover; background: #000; }
        .rgb-card-body { padding: 1.25rem; display: flex; flex-direction: column; flex: 1; }

        .rgb-row { display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.4rem; }
        .rgb-retailer { font-size: 0.75rem; font-weight: 700; color: var(--rgb-blue); text-transform: uppercase; }
        .rgb-legal { font-size: 0.75rem; font-weight: 700; color: var(--rgb-neon); }

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
        .rgb-rrp { font-size: 0.85rem; color: var(--rgb-muted); text-decoration: line-through; }
        .rgb-savings { font-size: 0.75rem; font-weight: 700; color: var(--rgb-neon); }

        .rgb-btn {
          display: block;
          width: 100%;
          text-align: center;
          background: var(--rgb-primary);
          color: #000;
          font-weight: 800;
          font-size: 0.9rem;
          padding: 0.75rem 1rem;
          border-radius: 6px;
          text-decoration: none;
          transition: background 0.2s;
        }
        .rgb-btn:hover { background: var(--rgb-primary-hover); color: #000; }
      </style>

      <div class="rgb-hero">
        <span class="rgb-hero-badge">🔥 Hand-Curated Clearance</span>
        <h2>Top 10 UK Electric Bike Deals & Discounts</h2>
        <p>Verified high-discount clearance offers from E-BikeShop.co.uk and Amazon UK.</p>
      </div>

      <div class="rgb-filter-bar">
        <div class="rgb-pills">
          <button class="rgb-pill active" onclick="rgbFilter('all', this)">All Top 10</button>
          <button class="rgb-pill" onclick="rgbFilter('Mountain', this)">Mountain</button>
          <button class="rgb-pill" onclick="rgbFilter('Commuter', this)">Commuter</button>
          <button class="rgb-pill" onclick="rgbFilter('Folding', this)">Folding</button>
        </div>

        <select id="rgbSortSelect" class="rgb-sort" onchange="rgbRender()">
          <option value="discount">🔥 Highest % Discount</option>
          <option value="savings">💰 Biggest £ Savings</option>
          <option value="price-asc">🏷️ Lowest Price</option>
        </select>
      </div>

      <div class="rgb-grid" id="rgbDealsContainer">
        <!-- Cards inserted by script -->
      </div>
    </div>

    <script>
      (function() {
        let dealsList = [];
        let curCat = 'all';

        async function fetchTopDeals() {
          try {
            const res = await fetch('<?php echo plugins_url('deals.json', __FILE__); ?>?v=' + Date.now());
            const data = await res.json();
            dealsList = data.deals || [];
            rgbRender();
          } catch(e) {
            console.error('Error loading deals:', e);
          }
        }

        window.rgbFilter = function(cat, btn) {
          curCat = cat;
          document.querySelectorAll('.rgb-pill').forEach(b => b.classList.remove('active'));
          btn.classList.add('active');
          rgbRender();
        };

        window.rgbRender = function() {
          const sortMode = document.getElementById('rgbSortSelect').value;
          const container = document.getElementById('rgbDealsContainer');

          let filtered = dealsList.filter(d => curCat === 'all' || d.category === curCat);

          filtered.sort((a, b) => {
            if (sortMode === 'discount') return b.discount_percentage - a.discount_percentage;
            if (sortMode === 'savings') return b.savings_amount - a.savings_amount;
            if (sortMode === 'price-asc') return a.sale_price - b.sale_price;
            return 0;
          });

          container.innerHTML = filtered.map(d => `
            <article class="rgb-card">
              <div class="rgb-badge-discount">SAVE £${Math.round(d.savings_amount)} (${d.discount_percentage}% OFF)</div>
              <img src="${d.image}" alt="${d.title}" class="rgb-card-img" loading="lazy">
              <div class="rgb-card-body">
                <div class="rgb-row">
                  <span class="rgb-retailer">${d.retailer}</span>
                  <span class="rgb-legal">✅ 100% UK Legal</span>
                </div>
                <h3 class="rgb-card-title">${d.title}</h3>
                <div class="rgb-specs">
                  <div><span class="rgb-spec-lbl">Motor</span><div class="rgb-spec-val">${d.motor_power}</div></div>
                  <div><span class="rgb-spec-lbl">Battery</span><div class="rgb-spec-val">${d.battery}</div></div>
                </div>
                <div class="rgb-price-row">
                  <div>
                    <div class="rgb-sale-price">£${d.sale_price.toLocaleString('en-GB', {minimumFractionDigits: 2})}</div>
                    <span class="rgb-rrp">Was £${d.rrp.toLocaleString('en-GB', {minimumFractionDigits: 2})}</span>
                  </div>
                  <span class="rgb-savings">Save £${Math.round(d.savings_amount)}</span>
                </div>
                <a href="${d.url}" target="_blank" rel="nofollow noopener" class="rgb-btn">
                  👉 View Deal at ${d.retailer} ➔
                </a>
              </div>
            </article>
          `).join('');
        };

        fetchTopDeals();
      })();
    </script>
    <?php
    return ob_get_clean();
}

add_shortcode('ebike_deal_finder', 'rgb_register_deal_finder_shortcode');
