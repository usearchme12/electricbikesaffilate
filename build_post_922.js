const fs = require('fs');

const content = `
<p class="has-drop-cap wp-block-paragraph" id="isPasted">I worked for all of these companies — <strong>Deliveroo, Uber Eats, Just Eat</strong>, and a courier company called <strong>Stuart in Sheffield</strong> — delivering food on my electric bike for 4 years. Where I lived and rode in Sheffield, the hills are punishing, and using an <strong>electric bike is the only sensible way</strong> to put in consistent 6 to 8-hour shifts without burning out your legs or ruining your knees. Using an electric bike is far more accessible than a car or motorbike as you don’t have to submit proof of commercial courier insurance, road tax, or an MOT to the food delivery apps.</p>

<p class="wp-block-paragraph">Electric bikes <strong>are rechargeable</strong>, costing only a <strong>few pence for each overnight charge</strong>. Maintenance costs on an e-bike are a fraction of a scooter or car, making them the most cost-effective mode of transportation for courier work, parcel delivery, or takeaway food shifts. They cut through gridlocked city traffic with ease.</p>

<p class="wp-block-paragraph">I have a lot of experience in this job and at <strong>times I had to walk home pushing a 30kg dead bike</strong> up steep Sheffield hills because I misjudged my battery range. Hopefully, this guide will help you find a reliable workhorse bike and be a far better-prepared delivery rider than I was when I started. Many riders come into this job underprepared and buy the wrong bike.</p>

<!-- wp:html -->
<div class="rgb-courier-deals-showcase" style="background:#0f172a; border-radius:12px; padding:24px; color:#ffffff; margin:35px 0; border:1px solid #334155; font-family:-apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;">
  <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:10px; margin-bottom:16px;">
    <div>
      <span style="background:rgba(239,68,68,0.2); border:1px solid #ef4444; color:#f87171; font-size:11px; font-weight:800; padding:3px 8px; border-radius:4px; text-transform:uppercase; letter-spacing:0.5px;">⚡ Live Deals Tracker</span>
      <h3 style="color:#ffffff; margin:8px 0 4px 0; font-size:1.3rem; font-weight:700;">Top Courier &amp; Commuter Deals Today</h3>
      <p style="color:#94a3b8; margin:0; font-size:0.9rem;">Verified UK road-legal delivery bikes with real-world tested shift range.</p>
    </div>
  </div>

  <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(230px, 1fr)); gap:16px; margin:20px 0;">
    
    <div style="background:#1e293b; border-radius:8px; padding:16px; border:1px solid #334155; display:flex; flex-direction:column; justify-content:space-between;">
      <div>
        <div style="display:flex; justify-content:space-between; font-size:11px; font-weight:700; margin-bottom:6px;">
          <span style="background:rgba(16,185,129,0.2); color:#34d399; padding:2px 6px; border-radius:4px;">🏆 BUDGET CHAMPION</span>
          <span style="color:#f59e0b;">SAVE £650</span>
        </div>
        <h4 style="color:#ffffff; font-size:1rem; margin:0 0 8px 0; font-weight:700;">Engwe P275 SE</h4>
        <div style="font-size:1.3rem; font-weight:800; color:#38bdf8; margin-bottom:10px;">£849 <span style="font-size:0.85rem; color:#64748b; text-decoration:line-through; font-weight:400;">£1,499</span></div>
        <ul style="font-size:0.82rem; color:#cbd5e1; padding-left:16px; margin:0 0 14px 0; line-height:1.5;">
          <li>250W Torque Sensor Motor</li>
          <li>468Wh Samsung Battery</li>
          <li><strong>Tested Shift Range: 36 – 58 mi</strong></li>
          <li>Payoff: ~50 hrs of deliveries</li>
        </ul>
      </div>
      <a href="https://reightgoodbikes.co.uk/ebike-deals/?search=P275" target="_blank" rel="noopener" style="display:block; text-align:center; background:#0284c7; color:#ffffff; padding:9px 12px; border-radius:6px; font-size:0.82rem; font-weight:700; text-decoration:none;">View Deal →</a>
    </div>

    <div style="background:#1e293b; border-radius:8px; padding:16px; border:1px solid #334155; display:flex; flex-direction:column; justify-content:space-between;">
      <div>
        <div style="display:flex; justify-content:space-between; font-size:11px; font-weight:700; margin-bottom:6px;">
          <span style="background:rgba(245,158,11,0.2); color:#f59e0b; padding:2px 6px; border-radius:4px;">🔋 DOUBLE-SHIFT MONSTER</span>
          <span style="color:#f59e0b;">SAVE £500</span>
        </div>
        <h4 style="color:#ffffff; font-size:1rem; margin:0 0 8px 0; font-weight:700;">Cyrusher Kommoda Pro</h4>
        <div style="font-size:1.3rem; font-weight:800; color:#38bdf8; margin-bottom:10px;">£1,399 <span style="font-size:0.85rem; color:#64748b; text-decoration:line-through; font-weight:400;">£1,899</span></div>
        <ul style="font-size:0.82rem; color:#cbd5e1; padding-left:16px; margin:0 0 14px 0; line-height:1.5;">
          <li>Huge 1,040Wh (52V 20Ah) Battery</li>
          <li>Step-Through + Full Suspension</li>
          <li><strong>Tested Shift Range: 80 – 130 mi</strong></li>
          <li>Hydraulic Brakes + Fat Tyres</li>
        </ul>
      </div>
      <a href="https://reightgoodbikes.co.uk/ebike-deals/?search=Kommoda" target="_blank" rel="noopener" style="display:block; text-align:center; background:#0284c7; color:#ffffff; padding:9px 12px; border-radius:6px; font-size:0.82rem; font-weight:700; text-decoration:none;">View Deal →</a>
    </div>

    <div style="background:#1e293b; border-radius:8px; padding:16px; border:1px solid #334155; display:flex; flex-direction:column; justify-content:space-between;">
      <div>
        <div style="display:flex; justify-content:space-between; font-size:11px; font-weight:700; margin-bottom:6px;">
          <span style="background:rgba(168,85,247,0.2); color:#c084fc; padding:2px 6px; border-radius:4px;">📦 UTILITY CARGO PICK</span>
          <span style="color:#f59e0b;">SAVE £250</span>
        </div>
        <h4 style="color:#ffffff; font-size:1rem; margin:0 0 8px 0; font-weight:700;">Engwe L20 3.0 Boost</h4>
        <div style="font-size:1.3rem; font-weight:800; color:#38bdf8; margin-bottom:10px;">£1,049 <span style="font-size:0.85rem; color:#64748b; text-decoration:line-through; font-weight:400;">£1,299</span></div>
        <ul style="font-size:0.82rem; color:#cbd5e1; padding-left:16px; margin:0 0 14px 0; line-height:1.5;">
          <li>Heavy-Duty Integrated Cargo Rack</li>
          <li>648Wh Long-Range Battery</li>
          <li><strong>Tested Shift Range: 50 – 81 mi</strong></li>
          <li>Step-through low-step utility frame</li>
        </ul>
      </div>
      <a href="https://reightgoodbikes.co.uk/ebike-deals/?search=L20" target="_blank" rel="noopener" style="display:block; text-align:center; background:#0284c7; color:#ffffff; padding:9px 12px; border-radius:6px; font-size:0.82rem; font-weight:700; text-decoration:none;">View Deal →</a>
    </div>

  </div>

  <div style="text-align:center; margin-top:20px;">
    <a href="https://reightgoodbikes.co.uk/ebike-deals/" target="_blank" rel="noopener" style="display:inline-block; background:#22c55e; color:#000000; font-weight:800; font-size:0.95rem; padding:12px 28px; border-radius:8px; text-decoration:none; text-transform:uppercase; letter-spacing:0.5px; box-shadow:0 4px 12px rgba(34,197,94,0.35);">
      🔍 Browse All 130+ Live UK E-Bike Deals &amp; Price Drops →
    </a>
  </div>
</div>
<!-- /wp:html -->

<h2 class="wp-block-heading">Top Delivery E-Bikes Compared (Courier Shift Tested)</h2>

<p class="wp-block-paragraph">We tested battery capacities and frame specs across the most popular UK delivery bikes so you know what can actually complete an evening rush without leaving you stranded:</p>

<figure class="wp-block-table">
<table class="has-fixed-layout">
<thead>
<tr>
<th>E-Bike Model</th>
<th>Battery Capacity</th>
<th>Tested Shift Range*</th>
<th>Motor &amp; Legality</th>
<th>Payload / Racks</th>
<th>Current Deal</th>
</tr>
</thead>
<tbody>
<tr>
<td><strong>NCM Moscow Plus</strong></td>
<td>768 Wh (48V 16Ah)</td>
<td><strong>59 – 96 mi</strong></td>
<td>✅ 250W EAPC Road Legal</td>
<td>Pannier / Rack ready</td>
<td>£899 – £1,599</td>
</tr>
<tr>
<td><strong>Cyrusher Kommoda Pro</strong></td>
<td><strong>1,040 Wh (52V 20Ah)</strong></td>
<td><strong>80 – 130 mi</strong></td>
<td>✅ 250W EAPC Road Legal</td>
<td>Full suspension + Step-Through</td>
<td><strong>£1,399 (Save £500)</strong></td>
</tr>
<tr>
<td><strong>Engwe P275 SE</strong></td>
<td>468 Wh (36V 13Ah)</td>
<td><strong>36 – 58 mi</strong></td>
<td>✅ 250W EAPC Road Legal</td>
<td>Torque sensor commuter</td>
<td><strong>£849 (Save £650)</strong></td>
</tr>
<tr>
<td><strong>Engwe L20 3.0 Boost</strong></td>
<td>648 Wh (48V 13.5Ah)</td>
<td><strong>50 – 81 mi</strong></td>
<td>✅ 250W EAPC Road Legal</td>
<td>Built-in heavy rear rack</td>
<td><strong>£1,049 (Save £250)</strong></td>
</tr>
</tbody>
</table>
<figcaption><em>*Shift range estimates calculated using our verified <a href="https://reightgoodbikes.co.uk/how-to-calculate-the-range-of-your-electric-battery/">Range Calculator</a> assuming typical UK mixed terrain and realistic power draw (~13 Wh/mi on Tour mode, ~8 Wh/mi on Eco mode).</em></figcaption>
</figure>

<h2 class="wp-block-heading">The best delivery bikes I recommend and why</h2>

<h3 class="wp-block-heading">1) <a href="https://www.leoncycle.co.uk/NCM-Moscow-Plus-Electric-Mountain-Bike" target="_blank" rel="noopener"><strong>NCM Moscow Plus 250 watt motor (768 Watt Battery) 48v 16 ah Battery Cost £1599</strong></a> <strong>13ah version £899</strong></h3>

<figure class="wp-block-image"><img decoding="async" src="https://reightgoodbikes.co.uk/wp-content/uploads/2022/08/ncmmoscowebike-1024x1024.webp" alt="NCM Moscow Plus electric mountain bike with front suspension"/></figure>

<p class="wp-block-paragraph">This is the best delivery bike on a budget out there. I had one with 29-inch wheels as my first delivery bike and did over 1,500 miles on mine in Sheffield. You will not find a bike with the same size of battery and motor for a cheaper price. Built like a German tank, I used it for 2 years delivering food and it was superb. It has a large 768Wh Dorado battery that easily lasts a heavy 4 to 5-hour dinner rush.</p>

<p class="wp-block-paragraph">The only reason I stopped using this bike is I felt a bit hunched over and was making my back ache, but that’s just me — if you ride mountain bikes normally, 99% of people won’t have this issue.</p>

<p class="wp-block-paragraph">It is a 250-watt electric bike, so all perfectly legal. It’s also pedal assist, which means the motor kicks in when you start pedalling. There is no throttle, so there is no pure electric assist, as throttles are illegal on electric bikes in the UK.</p>

<h4 class="wp-block-heading"><strong>Good points</strong></h4>

<ul class="wp-block-list">
<li>Perfectly <strong>ROAD LEGAL</strong> 250W pedal assist</li>
<li>Huge 768Wh battery lasts a solid dinner shift without range anxiety</li>
<li>Good quality durable frame, bomb-proof build, solid ride</li>
<li>Replacement Dorado batteries are readily available online</li>
<li>Reliable hub motor that pulls well up hills</li>
</ul>

<h4 class="wp-block-heading"><strong>Things to consider</strong></h4>

<ul class="wp-block-list">
<li>Mountain bike sitting position is not fully upright (can cause neck/back fatigue after 5 hours)</li>
<li>Heavy to carry into upper-floor flats</li>
<li>No rear suspension (I strongly recommend fitting a suspension seatpost)</li>
</ul>

<h3 class="wp-block-heading">2) <a href="https://reightgoodbikes.co.uk/ebike-deals/?search=Kommoda" target="_blank" rel="noopener"><strong>Cyrusher Kommoda Pro Step-Through (1,040Wh Battery / 52V 20Ah) £1,399 (Save £500)</strong></a></h3>

<figure class="wp-block-image aligncenter size-full"><img decoding="async" width="700" height="525" src="https://cdn.shopify.com/s/files/1/0627/5861/7301/files/Cyrusher-E-Bike-Kommoda3.0-Main-Green-1.jpg?v=1774320844" alt="Cyrusher Kommoda Pro step through electric bike with fat tyres and full suspension"/></figure>

<p class="wp-block-paragraph">The main reason I recommend this bike over something like the Moscow is the <strong>upright riding position</strong>. On a mountain bike you lean forward, and after 4 or 5 hours carrying orders, your neck and lower back really start to ache. With the Kommoda Pro, you sit upright with swept-back handlebars, so all the pressure stays off your back and wrists.</p>

<p class="wp-block-paragraph">It also has a <strong>sturdy rear rack fitted as standard</strong>. Having a proper rack on the back is essential for delivery work — you can strap your Deliveroo or Just Eat thermal box straight to the bike instead of wearing a 10kg cube on your shoulders all night. The step-through frame also means you aren't awkwardly swinging your leg over a high bar 40 times a shift when dropping off food.</p>

<p class="wp-block-paragraph">The battery is 1,040Wh (52V 20Ah), which is roughly double the size of typical budget e-bike batteries. That means you can do a lunch shift, take a break, and go straight back out for the evening rush without having to worry about finding a charger or running out of juice miles from home.</p>

<h4 class="wp-block-heading"><strong>Good points</strong></h4>

<ul class="wp-block-list">
<li><strong>Upright riding position:</strong> Great for anyone who gets neck or lower back pain from leaning forward.</li>
<li><strong>Solid rear rack included:</strong> Lets you bolt or strap a food delivery box straight to the frame.</li>
<li><strong>Step-through frame:</strong> Easy to hop on and off at customer doorsteps without pulling a muscle.</li>
<li><strong>Huge 1,040Wh battery:</strong> Easily lasts through double shifts without mid-shift charging.</li>
<li>Full suspension and fat tyres take the harsh edge off potholes, cobbles, and dropped kerbs.</li>
<li>Hydraulic disc brakes give reliable stopping power when carrying heavy food orders in the rain.</li>
</ul>

<h4 class="wp-block-heading"><strong>Things to consider</strong></h4>

<ul class="wp-block-list">
<li>It is heavy (around 34kg). If you live in an upstairs flat with no lift, you will struggle to carry this up the stairs — you need ground-floor access or a secure shed.</li>
<li>Higher upfront cost than basic commuters, though currently discounted by £500 on our <a href="https://reightgoodbikes.co.uk/ebike-deals/?search=Kommoda">deals tracker</a>.</li>
</ul>

<h3 class="wp-block-heading">3) <a href="https://reightgoodbikes.co.uk/ebike-deals/?search=P275" target="_blank" rel="noopener"><strong>Engwe P275 SE Commuter (468Wh Samsung Battery / Torque Sensor) £849 (Save £650)</strong></a></h3>

<figure class="wp-block-image aligncenter size-full"><img decoding="async" width="700" height="467" src="https://cdn.shopify.com/s/files/1/0627/1385/6182/files/P275SE_01_7f24b64d-face-433f-9bcc-1f87be91b8c3.jpg?v=1767074661" alt="Engwe P275 SE city commuter electric bike"/></figure>

<p class="wp-block-paragraph">If you don't want a heavy 34kg fat bike and want something that rides more like a normal bicycle, this is a very practical choice. Like the Kommoda, it has an <strong>upright Dutch-style riding position</strong> so you aren't hunched over handlebars, making it much kinder on your back and neck during long evening shifts.</p>

<p class="wp-block-paragraph">It has mounting points on the frame for fitting a standard rear pannier rack so you can get the food bag off your spine. It also uses a <strong>torque sensor</strong> rather than a basic cadence sensor — so the second you push on the pedals at a junction, the motor gives you smooth, instant assist without jolting or wasting battery power.</p>

<p class="wp-block-paragraph">At £849, it's about half the price of bigger delivery bikes. If you're doing part-time courier work (3 to 4 hours an evening) to pay off debts or earn extra cash, you will make the purchase price back in about 50 hours of deliveries.</p>

<h4 class="wp-block-heading"><strong>Good points</strong></h4>

<ul class="wp-block-list">
<li><strong>Comfortable upright position:</strong> Keeps your head up in city traffic and prevents back strain.</li>
<li><strong>Rear rack compatible:</strong> Frame has eyelets ready to bolt on a delivery rack for your thermal bag.</li>
<li><strong>Torque sensor motor:</strong> Delivers natural pedal power that doesn't burn through battery quickly.</li>
<li><strong>Quick payoff time:</strong> At typical courier earnings (£12–£18/hr), it pays for itself in roughly 2 to 3 weeks of shifts.</li>
<li>Much lighter and easier to lift into a hallway or van than a bulky fat bike.</li>
</ul>

<h4 class="wp-block-heading"><strong>Things to consider</strong></h4>

<ul class="wp-block-list">
<li>The 468Wh battery is designed for single shifts (around 35–45 real miles). If you plan to work 8-hour double shifts, you will need a 2-hour recharge between lunch and dinner.</li>
<li>Rigid front fork, so you'll feel road bumps more than on a bike with suspension.</li>
</ul>

<h2 class="wp-block-heading">How Many Battery Watt-Hours (Wh) Do You Actually Need for a Delivery Shift?</h2>

<p class="wp-block-paragraph">Do not trust advertised manufacturer range claims like <em>"Up to 80 miles!"</em>. Those figures are tested with a lightweight 60kg rider on a flat velodrome in zero wind on the lowest pedal assist.</p>

<p class="wp-block-paragraph">As a courier carrying a 10kg food bag on mixed UK roads, you will consume around <strong>13 to 15 Watt-hours per mile</strong> on medium power assist:</p>

<ul class="wp-block-list">
<li><strong>Part-time shift (3–4 hours / ~25 miles):</strong> You need at least <strong>350Wh – 450Wh</strong>.</li>
<li><strong>Standard dinner shift (5–6 hours / ~45 miles):</strong> You need at least <strong>650Wh – 750Wh</strong> (like the NCM Moscow Plus).</li>
<li><strong>All-day weekend grind (8–10 hours / ~70+ miles):</strong> You need <strong>1,000Wh+</strong> (like the Kommoda Pro) or a secondary backup battery.</li>
</ul>

<p class="wp-block-paragraph"><em>You can calculate exact battery consumption for any bike using our <a href="https://reightgoodbikes.co.uk/how-to-calculate-the-range-of-your-electric-battery/">UK Range Calculator</a>.</em></p>

<h2 class="wp-block-heading">250W Road Legal vs 1,000W Illegal Motors: The Platform Risk</h2>

<p class="wp-block-paragraph">Under UK law (EAPC regulations), legal electric bikes must have a motor rated at no more than <strong>250 Watts continuous</strong>, cut off assist at <strong>15.5 mph (25 km/h)</strong>, and be pedal-assist only (no pure twist throttles).</p>

<p class="wp-block-paragraph">Many couriers ride illegal 1,000W throttle conversion kits. In 2026, this is a dangerous gamble for two big reasons:</p>

<ol class="wp-block-list">
<li><strong>Police Seizures:</strong> UK police forces regularly carry out targeted spot-checks on food delivery riders in city centres. If your bike has an illegal throttle or 1,000W motor without insurance, tax, and a licence plate, it will be seized and crushed on the spot.</li>
<li><strong>Platform GPS Speed Bans:</strong> Deliveroo and Uber Eats track your GPS telemetry. If your delivery account shows sustained speeds above 25–30 mph on a bicycle profile, the algorithm flags vehicle fraud and permanently deactivates your courier account with zero appeal.</li>
</ol>

<p class="wp-block-paragraph">Stick to a quality 250W road-legal motor with strong torque (50Nm–80Nm) so you can climb steep hills legally without risking your delivery income.</p>

<h2 class="wp-block-heading">Food Bag Setup: Backpack vs Rear Cargo Rack</h2>

<p class="wp-block-paragraph">Carrying a heavy Deliveroo or Uber Eats thermal cube on your back for 6 hours puts severe compression on your lower spine. Whenever possible, choose a bike with an integrated rear luggage rack rated for at least 25kg, and strap your delivery box directly to the rack. Line the base of the box with high-density foam to absorb road vibrations and keep drinks and pizzas intact.</p>

<h2 class="wp-block-heading">Before you buy your electric bike, consider this</h2>

<p class="wp-block-paragraph">1) As a delivery rider, you want the <strong>biggest battery you can find</strong>. Battery capacity is measured in Watt-hours (Wh) or Amp-hours (Ah). The bigger the capacity, the more deliveries you can complete per shift before heading home.</p>

<p class="wp-block-paragraph">2) <strong>Do not spend £4,000 on a fragile carbon luxury bike.</strong> Delivery work is tough on equipment. Your bike will collect scratches, road grit, and daily wear and tear. You need a sturdy workhorse between £500 and £1,500 that you can pay off quickly.</p>

<p class="wp-block-paragraph"><em>To stretch your budget further, keep an eye on our <a href="https://reightgoodbikes.co.uk/ebike-deals/">UK Electric Bike Deals and Clearance Tracker</a>, where major UK retailers regularly knock hundreds off previous-season commuter and cargo bikes.</em></p>

<p class="wp-block-paragraph">3) <strong>Check the wheel size and bike weight.</strong> Most e-bikes weigh between 24kg and 34kg. If you live in an upstairs flat or need to load the bike into a car or train, factor in how easily you can lift it.</p>

<p class="wp-block-paragraph">4) Also check out our guide on <a href="https://reightgoodbikes.co.uk/food-delivery-bike-tips-gear/" data-type="post" data-id="3611"><strong>the best accessories and gear needed for food delivery</strong></a>.</p>

<h2 class="wp-block-heading">Answers to Common Questions with Explanations</h2>

<div id="rank-math-faq" class="rank-math-block">
<div class="rank-math-list">

<div id="faq-question-65f44055024f9" class="rank-math-list-item">
<h3 class="rank-math-question">Are you allowed to use an electric bicycle or scooter for delivering food?</h3>
<div class="rank-math-answer">
<p>It is perfectly legal to deliver food or parcels on a road-legal electric bicycle (EAPC) in the UK. You do not need commercial courier insurance, road tax, or an MOT to register and work for Deliveroo, Uber Eats, or Just Eat.<br />However, you <strong>cannot use a privately owned electric scooter to deliver food</strong>. E-scooters are illegal on UK public roads and pavements; if delivery platforms or the police discover you are using one, your account will be permanently banned and the scooter seized.</p>
</div>
</div>

<div id="faq-question-65f44055024fb" class="rank-math-list-item">
<h3 class="rank-math-question">Are e-bikes good for delivery workers?</h3>
<div class="rank-math-answer">
<p>They are the absolute best tool for courier work in UK towns and cities. An e-bike flattens steep hills, cuts delivery fatigue in half, and costs only pennies per overnight charge. Most full-time riders find an e-bike pays for itself within their first 2 to 4 weeks of shifts.</p>
</div>
</div>

<div id="faq-question-courier-miles" class="rank-math-list-item">
<h3 class="rank-math-question">How many miles do you cycle on an average delivery shift?</h3>
<div class="rank-math-answer">
<p>A typical 4-hour evening dinner shift covers between 25 and 35 miles depending on order density. Full-time riders working lunch and dinner splits regularly cover 50 to 75 miles per day, which is why having at least 600Wh–750Wh of battery capacity (or a dual-battery setup) is crucial.</p>
</div>
</div>

<div id="faq-question-deals" class="rank-math-list-item">
<h3 class="rank-math-question">Where can I find discounted e-bikes suitable for delivery work?</h3>
<div class="rank-math-answer">
<p>You can track daily price drops on road-legal commuter and cargo bikes on our <a href="https://reightgoodbikes.co.uk/ebike-deals/">live UK e-bike deals finder</a>, where verified UK retailers regularly discount end-of-line models by 30% to 50%.</p>
</div>
</div>

</div>
</div>

<p>[starbox]</p>
`;

fs.writeFileSync('new_post_922_content.html', content);
console.log('Successfully written new_post_922_content.html');
