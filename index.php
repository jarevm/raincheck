<?php require 'config.php';
head('Forecast & Reports');
$areas = $db->query("SELECT * FROM areas"); ?>
<h1>Weather in <?= e(PLACE) ?></h1>
<div id="adv" class="adv NO">
  <h2>Loading advisory...</h2>
</div>
<div class="panel wide">
  <div class="now" id="now">Loading forecast...</div>
  <h3>Next hours</h3>
  <div class="strip" id="hourly"></div>
  <h3>7-day forecast</h3>
  <div class="strip" id="daily"></div>
</div>
<h2>Community rain &amp; flood reports</h2>
<p><small>Reports are from fellow students and staff. Confirm what you also see. This is not an official suspension notice; follow school and PAGASA announcements.</small></p>
<div class="filters"><select id="area">
    <option value="">All areas</option><?php while ($a = $areas->fetch_assoc()): ?><option value="<?= $a['id'] ?>"><?= e($a['name']) ?></option><?php endwhile; ?>
  </select>
  <select id="rain">
    <option value="">Any rain</option>
    <option>LIGHT</option>
    <option>MODERATE</option>
    <option>HEAVY</option>
  </select>
  <select id="flood">
    <option value="">Any flood level</option>
    <option value="YES">Flooded (any)</option>
    <option>ANKLE</option>
    <option>KNEE</option>
    <option>WAIST</option>
  </select>
  <input type="date" id="date" title="Reported on/after"><a class="btn" href="report.php">+ Report</a>
</div>
<div class="grid" id="results"></div>
<script>
  const LAT = <?= LAT ?>,
    LON = <?= LON ?>;
  const wmo = c => c <= 1 ? ['Clear', '☀️'] : c <= 3 ? ['Cloudy', '⛅'] : c <= 48 ? ['Fog', '🌫️'] : c <= 67 ? ['Rain', '🌧️'] : c <= 82 ? ['Showers', '🌦️'] : ['Thunderstorm', '⛈️'];
  const hr = t => {
    const h = +t.slice(11, 13);
    return (h % 12 || 12) + (h < 12 ? ' AM' : ' PM')
  };
  async function weather() {
    const u = `https://api.open-meteo.com/v1/forecast?latitude=${LAT}&longitude=${LON}&current=temperature_2m,weather_code&hourly=precipitation_probability,precipitation,weather_code&daily=weather_code,temperature_2m_max,temperature_2m_min,precipitation_probability_max&timezone=Asia%2FManila&forecast_days=7`;
    try {
      const d = await (await fetch(u)).json(),
        c = d.current,
        h = d.hourly;
      const s = h.time.findIndex(t => t >= c.time.slice(0, 13) + ':00'),
        n = 6;
      const probs = h.precipitation_probability.slice(s, s + n),
        mm = h.precipitation.slice(s, s + n);
      const maxP = Math.max(...probs),
        total = mm.reduce((a, b) => a + b, 0),
        peak = Math.max(...mm);
      const lvl = (maxP >= 60 || total >= 2 || peak >= 2.5) ? 'YES' : maxP >= 30 ? 'MAYBE' : 'NO';
      const txt = {
        YES: 'Yes, bring an umbrella!',
        MAYBE: 'Maybe. Pack a compact umbrella.',
        NO: 'No umbrella needed.'
      } [lvl];
      document.getElementById('adv').className = 'adv ' + lvl;
      document.getElementById('adv').innerHTML = `<h2>☂️ Should I bring an umbrella? ${txt}</h2>Next ${n} hours: up to ${maxP}% chance of rain, about ${total.toFixed(1)} mm expected.` + (peak >= 7.5 ? '<br><b>⚠️ Heavy rain expected. Avoid flood-prone roads and check official class announcements.</b>' : '');
      document.getElementById('now').innerHTML = `<b>${Math.round(c.temperature_2m)}°C</b><span style="font-size:2rem">${wmo(c.weather_code)[1]}</span><span>${wmo(c.weather_code)[0]}</span>`;
      document.getElementById('hourly').innerHTML = probs.map((p, i) => `<div class="fc"><small>${hr(h.time[s+i])}</small><div>${wmo(h.weather_code[s+i])[1]}</div><b>${p}%</b><small>${mm[i]} mm</small></div>`).join('');
      const D = d.daily;
      document.getElementById('daily').innerHTML = D.time.map((t, i) => `<div class="fc"><small>${new Date(t+'T00:00').toLocaleDateString([], {weekday:'short',day:'numeric'})}</small><div>${wmo(D.weather_code[i])[1]}</div><b>${Math.round(D.temperature_2m_max[i])}°/${Math.round(D.temperature_2m_min[i])}°</b><small>${D.precipitation_probability_max[i]}% rain</small></div>`).join('');
    } catch (e) {
      document.getElementById('adv').innerHTML = '<h2>Forecast unavailable</h2>Check your internet connection and try again.';
      document.getElementById('now').textContent = '';
    }
  }
  const ids = ['area', 'rain', 'flood', 'date'];
  let timer;
  async function load() {
    const p = new URLSearchParams();
    ids.forEach(i => p.set(i, document.getElementById(i).value));
    document.getElementById('results').innerHTML = await (await fetch('api_reports.php?' + p)).text();
  }
  async function confirmReport(id) {
    const r = await fetch('confirm.php', {
      method: 'POST',
      body: new URLSearchParams({
        id
      })
    });
    if (r.status === 401) {
      location = 'login.php';
      return
    }
    alert((await r.json()).msg);
    load();
  }
  ids.forEach(i => document.getElementById(i).addEventListener('input', () => {
    clearTimeout(timer);
    timer = setTimeout(load, 250)
  }));
  weather();
  load();
</script>
<?php foot(); ?>