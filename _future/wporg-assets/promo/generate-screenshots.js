/**
 * Generate MasjidOS WordPress.org promo screenshot HTML (1280×800).
 * Mosque name: Madani Masjid
 */
const fs = require( 'node:fs' );
const path = require( 'node:path' );

const dir = __dirname;
const mosque = 'Madani Masjid';

const baseCss = `
  * { box-sizing: border-box; margin: 0; padding: 0; }
  html, body {
    width: 1280px; height: 800px; overflow: hidden;
    font-family: "Segoe UI", "Helvetica Neue", Arial, sans-serif;
    background: #f4f1ea; color: #111827;
  }
  .frame {
    width: 1280px; height: 800px; position: relative;
    background:
      radial-gradient(ellipse 50% 40% at 85% 15%, rgba(201,168,76,0.12), transparent 60%),
      radial-gradient(ellipse 40% 50% at 10% 90%, rgba(26,107,90,0.08), transparent 55%),
      #f4f1ea;
  }
  .frame::before {
    content: ""; position: absolute; inset: 0;
    background-image: radial-gradient(rgba(17,24,39,0.07) 1px, transparent 1px);
    background-size: 18px 18px; pointer-events: none;
  }
  .inner {
    position: relative; z-index: 1;
    display: grid; grid-template-columns: 420px 1fr; gap: 36px;
    height: 100%; padding: 56px 52px 56px 60px; align-items: center;
  }
  .brand { display: flex; align-items: center; gap: 12px; margin-bottom: 32px; }
  .mark {
    width: 40px; height: 40px; border-radius: 10px; background: #134e42;
    display: grid; place-items: center;
  }
  .mark svg { width: 22px; height: 22px; }
  .brand-name { font-size: 28px; font-weight: 800; letter-spacing: -0.03em; color: #134e42; }
  .brand-name span { color: #c9a84c; }
  .eyebrow {
    display: flex; align-items: center; gap: 12px; margin-bottom: 16px;
    color: #c9a84c; font-size: 13px; font-weight: 700;
    letter-spacing: 0.12em; text-transform: uppercase;
  }
  .eyebrow i { display: block; width: 28px; height: 2px; background: #c9a84c; }
  h1 {
    font-size: 52px; line-height: 1.05; font-weight: 800;
    letter-spacing: -0.035em; color: #134e42; margin-bottom: 16px;
  }
  h1 em { font-style: normal; color: #c9a84c; }
  .sub { font-size: 17px; line-height: 1.5; color: #667085; max-width: 34ch; font-weight: 500; }
  .card {
    background: #fff; border-radius: 18px;
    box-shadow: 0 24px 60px rgba(15, 61, 52, 0.14);
    border: 1px solid rgba(26,107,90,0.08);
    overflow: hidden; min-height: 500px;
  }
`;

function shell( num, label, titleHtml, sub, cardHtml, extraCss = '' ) {
	return `<!DOCTYPE html>
<html lang="en"><head><meta charset="utf-8" />
<title>MasjidOS Screenshot ${ num }</title>
<style>${ baseCss }${ extraCss }</style></head>
<body><div class="frame"><div class="inner">
  <div class="copy">
    <div class="brand">
      <div class="mark"><svg viewBox="0 0 24 24" fill="none" stroke="#c9a84c" stroke-width="1.8"><path d="M4 20h16M6 20V10l6-5 6 5v10M10 20v-5h4v5"/></svg></div>
      <div class="brand-name">Masjid<span>OS</span></div>
    </div>
    <div class="eyebrow"><i></i>${ String( num ).padStart( 2, '0' ) } · ${ label }</div>
    <h1>${ titleHtml }</h1>
    <p class="sub">${ sub }</p>
  </div>
  ${ cardHtml }
</div></div></body></html>`;
}

const shots = [
	{
		file: 'screenshot-01-welcome.html',
		num: 1,
		label: 'WELCOME',
		title: 'Ready in<br><em>Minutes</em>',
		sub: 'Set prayer once, preview live times, then put widgets on your mosque website — no coding needed.',
		css: `
  .card-top { display:flex; justify-content:space-between; align-items:center; padding:18px 22px;
    background: linear-gradient(135deg,#0f3d34,#1a6b5a 55%,#145a4c); color:#fff; }
  .card-top strong { font-size:18px; } .card-top small { opacity:.8; font-size:12px; font-weight:600; }
  .countdown { display:flex; justify-content:space-between; align-items:center; margin:18px 22px 12px;
    padding:16px 18px; border-radius:12px;
    background: linear-gradient(135deg,rgba(26,107,90,.08),rgba(201,168,76,.1));
    border:1px solid rgba(26,107,90,.12); }
  .countdown small { display:block; font-size:11px; font-weight:700; letter-spacing:.06em; text-transform:uppercase; color:#667085; }
  .countdown b { display:block; margin-top:4px; font-size:18px; }
  .timer { font-size:28px; font-weight:800; color:#1a6b5a; }
  .rows { padding:0 22px 8px; }
  .head, .row { display:grid; grid-template-columns:1.2fr 1fr 1fr; gap:8px; padding:11px 12px; font-size:14px; font-weight:600; }
  .head { padding:4px 12px 10px; font-size:11px; letter-spacing:.05em; text-transform:uppercase; color:#98a2b3; }
  .head span:last-child, .row span:last-child { text-align:right; }
  .row { border-radius:10px; color:#344054; }
  .row.now { background:rgba(26,107,90,.12); color:#134e42; }
  .chips { display:flex; gap:8px; padding:14px 22px 22px; }
  .chip { display:inline-flex; align-items:center; gap:7px; padding:8px 12px; border-radius:999px;
    background:rgba(26,107,90,.08); border:1px solid rgba(26,107,90,.2); font-size:12px; font-weight:700; color:#134e42; }
  .chip i { width:8px; height:8px; border-radius:50%; background:#1a6b5a; }
`,
		card: `<div class="card">
  <div class="card-top"><div><strong>${ mosque }</strong><div><small>Dhaka, Bangladesh · 17 Jul 2026</small></div></div><small>Live preview</small></div>
  <div class="countdown"><div><small>Next prayer</small><b>Maghrib</b></div><div class="timer">01:24:18</div></div>
  <div class="rows">
    <div class="head"><span>Prayer</span><span>Iqamah</span><span>Azan</span></div>
    <div class="row"><span>Fajr</span><span>4:45 AM</span><span>4:18 AM</span></div>
    <div class="row"><span>Dhuhr</span><span>12:20 PM</span><span>12:01 PM</span></div>
    <div class="row now"><span>Asr · Now</span><span>4:35 PM</span><span>4:12 PM</span></div>
    <div class="row"><span>Maghrib</span><span>6:53 PM</span><span>6:48 PM</span></div>
    <div class="row"><span>Isha</span><span>8:30 PM</span><span>8:15 PM</span></div>
  </div>
  <div class="chips"><span class="chip"><i></i>Timezone</span><span class="chip"><i></i>Location</span><span class="chip"><i></i>Prayer times</span></div>
</div>`
	},
	{
		file: 'screenshot-02-tv.html',
		num: 2,
		label: 'TV DISPLAY',
		title: 'Mosque<br><em>TV Board</em>',
		sub: 'Fullscreen lobby screen with live countdown, Iqamah times, Hijri date, and rotating notices.',
		css: `
  .card { background:#0b1a17; color:#f7faf9; padding:22px; display:grid; grid-template-rows:auto 1fr auto; gap:16px;
    border:1px solid rgba(201,168,76,.18); box-shadow:0 24px 60px rgba(15,61,52,.22); }
  .tv-head { display:flex; justify-content:space-between; }
  .tv-head h2 { font-size:22px; } .tv-head p { margin-top:4px; font-size:13px; color:rgba(247,250,249,.65); }
  .hijri { text-align:right; font-size:13px; color:#c9a84c; font-weight:700; }
  .tv-main { display:grid; grid-template-columns:1.1fr .9fr; gap:16px; }
  .hero { border-radius:14px; padding:22px; min-height:260px; display:flex; flex-direction:column; justify-content:center;
    background:linear-gradient(160deg,rgba(26,107,90,.55),rgba(15,61,52,.9)); border:1px solid rgba(201,168,76,.2); }
  .hero small { font-size:12px; letter-spacing:.08em; text-transform:uppercase; color:rgba(247,250,249,.7); font-weight:700; }
  .hero strong { display:block; margin-top:8px; font-size:36px; font-weight:800; }
  .hero .big { margin-top:18px; font-size:52px; font-weight:800; color:#c9a84c; }
  .list { display:flex; flex-direction:column; gap:8px; }
  .item { display:flex; justify-content:space-between; padding:12px 14px; border-radius:10px;
    background:rgba(255,255,255,.04); border:1px solid rgba(255,255,255,.06); font-size:14px; font-weight:600; }
  .item.active { background:rgba(201,168,76,.14); border-color:rgba(201,168,76,.35); color:#f0d78c; }
  .ticker { display:flex; align-items:center; gap:12px; padding:12px 14px; border-radius:10px;
    background:rgba(26,107,90,.45); border:1px solid rgba(201,168,76,.22); font-size:13px; }
  .ticker b { padding:4px 10px; border-radius:999px; background:#c9a84c; color:#134e42; font-size:11px; font-weight:800; }
`,
		card: `<div class="card">
  <div class="tv-head"><div><h2>${ mosque }</h2><p>Dhaka · Asia/Dhaka</p></div><div class="hijri">22 Muharram 1448<br>Friday</div></div>
  <div class="tv-main">
    <div class="hero"><small>Next prayer</small><strong>Maghrib</strong><div class="big">01:24:18</div></div>
    <div class="list">
      <div class="item"><span>Fajr</span><span>4:18</span></div>
      <div class="item"><span>Dhuhr</span><span>12:01</span></div>
      <div class="item active"><span>Asr</span><span>4:12</span></div>
      <div class="item"><span>Maghrib</span><span>6:48</span></div>
      <div class="item"><span>Isha</span><span>8:15</span></div>
    </div>
  </div>
  <div class="ticker"><b>NOTICE</b><span>Jumuah today — second session at 2:00 PM. Please arrive early.</span></div>
</div>`
	},
	{
		file: 'screenshot-03-dashboard.html',
		num: 3,
		label: 'DASHBOARD',
		title: 'Mosque<br><em>Overview</em>',
		sub: 'See next prayer, Hijri date, notices, and quick actions from one dedicated MasjidOS dashboard.',
		css: `
  .card { padding:0; display:grid; grid-template-columns: 200px 1fr; min-height:520px; }
  .side { background:#111923; color:#fff; padding:20px 16px; }
  .side .logo { font-weight:800; font-size:16px; margin-bottom:18px; }
  .side .logo span { color:#c9a84c; }
  .nav { display:flex; flex-direction:column; gap:6px; font-size:13px; }
  .nav div { padding:9px 12px; border-radius:8px; color:rgba(255,255,255,.7); font-weight:600; }
  .nav .on { background:#1a6b5a; color:#fff; }
  .main { padding:22px; background:#f6f7f9; }
  .hi { font-size:22px; font-weight:800; color:#134e42; }
  .hi small { display:block; margin-top:4px; font-size:13px; color:#667085; font-weight:600; }
  .box { margin-top:16px; padding:16px; border-radius:12px; background:#fff; border:1px solid #e4e7ec; }
  .box .lbl { font-size:11px; font-weight:700; letter-spacing:.06em; text-transform:uppercase; color:#667085; }
  .box .val { margin-top:6px; font-size:28px; font-weight:800; color:#1a6b5a; }
  .grid { display:grid; grid-template-columns:1fr 1fr; gap:10px; margin-top:12px; }
  .stat { padding:14px; border-radius:10px; background:#fff; border:1px solid #e4e7ec; }
  .stat b { display:block; font-size:20px; color:#134e42; }
  .stat span { font-size:12px; color:#667085; font-weight:600; }
`,
		card: `<div class="card">
  <div class="side"><div class="logo">Masjid<span>OS</span></div>
    <div class="nav"><div class="on">Dashboard</div><div>Features</div><div>Minbar</div><div>Settings</div><div>Docs</div></div>
  </div>
  <div class="main">
    <div class="hi">Assalamu Alaikum<div><small>${ mosque } · 22 Muharram 1448</small></div></div>
    <div class="box"><div class="lbl">Next prayer · Maghrib</div><div class="val">01:24:18</div></div>
    <div class="grid">
      <div class="stat"><b>5</b><span>Active notices</span></div>
      <div class="stat"><b>3</b><span>Upcoming events</span></div>
      <div class="stat"><b>Asia/Dhaka</b><span>Timezone ready</span></div>
      <div class="stat"><b>Karachi</b><span>Calculation method</span></div>
    </div>
  </div>
</div>`
	},
	{
		file: 'screenshot-04-features.html',
		num: 4,
		label: 'FEATURES',
		title: 'Copy<br><em>Shortcodes</em>',
		sub: 'Browse widgets, tweak options, preview, and copy a ready shortcode — no memorizing attributes.',
		css: `
  .card { padding:22px; background:#f6f7f9; }
  .title { font-size:20px; font-weight:800; color:#134e42; margin-bottom:14px; }
  .feats { display:grid; grid-template-columns:1fr 1fr; gap:12px; }
  .feat { background:#fff; border:1px solid #e4e7ec; border-radius:12px; padding:14px; }
  .feat .t { font-size:14px; font-weight:800; color:#134e42; }
  .feat .d { margin-top:4px; font-size:12px; color:#667085; line-height:1.4; }
  .feat code { display:block; margin-top:10px; padding:8px 10px; border-radius:8px; background:rgba(26,107,90,.08);
    color:#1a6b5a; font-size:12px; font-weight:700; }
  .feat .btn { margin-top:8px; display:inline-block; padding:6px 10px; border-radius:8px; background:#1a6b5a; color:#fff; font-size:11px; font-weight:700; }
`,
		card: `<div class="card"><div class="title">Features · ${ mosque }</div>
  <div class="feats">
    <div class="feat"><div class="t">Prayer Times</div><div class="d">Today’s times with countdown and Qibla.</div><code>[masjidos_prayer_times]</code><span class="btn">Copy</span></div>
    <div class="feat"><div class="t">Monthly Timetable</div><div class="d">Full month with print support.</div><code>[masjidos_monthly_prayer_times]</code><span class="btn">Copy</span></div>
    <div class="feat"><div class="t">Jumuah</div><div class="d">Sessions, khatib, topic, and notice.</div><code>[masjidos_jumuah]</code><span class="btn">Copy</span></div>
    <div class="feat"><div class="t">Islamic Articles</div><div class="d">Publish and list mosque articles.</div><code>[masjidos_articles]</code><span class="btn">Copy</span></div>
  </div>
</div>`
	},
	{
		file: 'screenshot-05-docs.html',
		num: 5,
		label: 'DOCS',
		title: 'Help for<br><em>Everyone</em>',
		sub: 'Plain-language Docs with First 5 minutes, checklist, generators, and shortcode reference.',
		css: `
  .card { padding:22px; }
  .tabs { display:flex; gap:8px; flex-wrap:wrap; margin-bottom:16px; }
  .tabs span { padding:7px 12px; border-radius:999px; background:#f2f4f7; font-size:12px; font-weight:700; color:#667085; }
  .tabs .on { background:#1a6b5a; color:#fff; }
  .sec { font-size:16px; font-weight:800; color:#134e42; margin-bottom:10px; }
  .steps { display:grid; grid-template-columns:1fr 1fr; gap:10px; }
  .step { padding:14px; border-radius:12px; border:1px solid #e4e7ec; background:#fafbfc; }
  .step b { display:block; color:#134e42; font-size:13px; margin-bottom:4px; }
  .step span { font-size:12px; color:#667085; line-height:1.4; }
`,
		card: `<div class="card">
  <div class="tabs"><span class="on">Overview</span><span>Generators</span><span>Prayer</span><span>Minbar</span><span>Articles</span><span>Reference</span></div>
  <div class="sec">First 5 minutes · ${ mosque }</div>
  <div class="steps">
    <div class="step"><b>1. Admin language</b><span>Pick English, Bangla, or Arabic from the top bar.</span></div>
    <div class="step"><b>2. Prayer Settings</b><span>Set timezone, coordinates, and method — then save.</span></div>
    <div class="step"><b>3. Features preview</b><span>Copy a shortcode and paste it on any page.</span></div>
    <div class="step"><b>4. Optional extras</b><span>Add Jumuah, notices, articles, or TV display.</span></div>
  </div>
</div>`
	},
	{
		file: 'screenshot-06-prayer.html',
		num: 6,
		label: 'PRAYER TIMES',
		title: 'Public<br><em>Widget</em>',
		sub: 'Beautiful prayer times for your homepage — classic or compact, English, Bangla, or Arabic labels.',
		css: `
  .card { padding:0; overflow:hidden; }
  .ph { padding:18px 22px; background:linear-gradient(135deg,#134e42,#1a6b5a); color:#fff; }
  .ph strong { font-size:20px; } .ph small { display:block; margin-top:4px; opacity:.8; font-size:12px; }
  .meta { display:flex; gap:16px; padding:12px 22px; background:rgba(26,107,90,.06); font-size:12px; font-weight:700; color:#134e42; }
  .table { padding:8px 16px 18px; }
  .r { display:grid; grid-template-columns:1.4fr 1fr 1fr; padding:12px 10px; border-bottom:1px solid #eef0f3; font-size:14px; font-weight:600; }
  .r.cur { background:rgba(26,107,90,.1); border-radius:8px; border:none; color:#134e42; }
  .r span:nth-child(2), .r span:nth-child(3) { text-align:right; }
  .foot { padding:0 22px 18px; font-size:12px; color:#667085; }
`,
		card: `<div class="card">
  <div class="ph"><strong>Prayer Times</strong><small>${ mosque } · Dhaka</small></div>
  <div class="meta"><span>Qibla 292°</span><span>Hanafi Asr</span><span>Karachi method</span></div>
  <div class="table">
    <div class="r"><span>Fajr</span><span>Iqamah 4:45</span><span>4:18 AM</span></div>
    <div class="r"><span>Dhuhr</span><span>Iqamah 12:20</span><span>12:01 PM</span></div>
    <div class="r cur"><span>Asr · Now</span><span>Iqamah 4:35</span><span>4:12 PM</span></div>
    <div class="r"><span>Maghrib</span><span>Iqamah 6:53</span><span>6:48 PM</span></div>
    <div class="r"><span>Isha</span><span>Iqamah 8:30</span><span>8:15 PM</span></div>
  </div>
  <div class="foot">Next: Maghrib in 01:24:18</div>
</div>`
	},
	{
		file: 'screenshot-07-jumuah.html',
		num: 7,
		label: 'JUMUAH',
		title: 'Friday<br><em>Ready</em>',
		sub: 'Publish Jumuah sessions, khatib profile, topic, and this week’s Minbar schedule for your congregation.',
		css: `
  .card { padding:22px; }
  .badge { display:inline-block; padding:4px 10px; border-radius:999px; background:rgba(201,168,76,.2); color:#8a6d1d; font-size:11px; font-weight:800; margin-bottom:10px; }
  .jtitle { font-size:22px; font-weight:800; color:#134e42; }
  .jsub { margin-top:4px; color:#667085; font-size:13px; font-weight:600; }
  .grid { display:grid; grid-template-columns:120px 1fr; gap:16px; margin-top:18px; align-items:center; }
  .photo { width:120px; height:120px; border-radius:14px; background:linear-gradient(145deg,#1a6b5a,#134e42); display:grid; place-items:center; color:#c9a84c; font-size:36px; font-weight:800; }
  .info b { display:block; font-size:16px; color:#111827; }
  .info span { display:block; margin-top:6px; font-size:13px; color:#667085; line-height:1.45; }
  .sessions { display:grid; grid-template-columns:1fr 1fr; gap:10px; margin-top:18px; }
  .s { padding:14px; border-radius:12px; background:rgba(26,107,90,.07); border:1px solid rgba(26,107,90,.14); }
  .s small { font-size:11px; font-weight:700; color:#667085; text-transform:uppercase; }
  .s strong { display:block; margin-top:4px; font-size:18px; color:#134e42; }
`,
		card: `<div class="card">
  <div class="badge">FRIDAY · LANGUAGE BANGLA</div>
  <div class="jtitle">Jumuah Prayer</div>
  <div class="jsub">${ mosque }</div>
  <div class="grid">
    <div class="photo">IM</div>
    <div class="info"><b>Imam Madani</b><span>Topic: Patience in daily life<br>Arrive 15 minutes early · sisters section open</span></div>
  </div>
  <div class="sessions">
    <div class="s"><small>First Jumuah</small><strong>1:15 PM</strong></div>
    <div class="s"><small>Second Jumuah</small><strong>2:00 PM</strong></div>
  </div>
</div>`
	},
	{
		file: 'screenshot-08-articles.html',
		num: 8,
		label: 'ARTICLES',
		title: 'Islamic<br><em>Articles</em>',
		sub: 'Publish articles with category, language, author, source, and takeaway — then list them on any page.',
		css: `
  .card { padding:22px; background:#f6f7f9; }
  .ht { font-size:18px; font-weight:800; color:#134e42; margin-bottom:14px; }
  .arts { display:flex; flex-direction:column; gap:12px; }
  .art { display:grid; grid-template-columns:96px 1fr; gap:14px; background:#fff; border:1px solid #e4e7ec; border-radius:12px; padding:12px; }
  .thumb { border-radius:10px; background:linear-gradient(145deg,#1a6b5a,#0f3d34); }
  .art .cat { font-size:11px; font-weight:800; color:#c9a84c; letter-spacing:.04em; text-transform:uppercase; }
  .art .tt { margin-top:4px; font-size:15px; font-weight:800; color:#111827; }
  .art .meta { margin-top:6px; font-size:12px; color:#667085; }
  .art .btn { margin-top:8px; display:inline-block; padding:6px 12px; border-radius:8px; background:#1a6b5a; color:#fff; font-size:12px; font-weight:700; }
`,
		card: `<div class="card"><div class="ht">Islamic Articles · ${ mosque }</div>
  <div class="arts">
    <div class="art"><div class="thumb"></div><div><div class="cat">Fiqh · Bangla</div><div class="tt">সালাতের খুশু কীভাবে বাড়াবেন</div><div class="meta">Author: Imam Madani · 4 min read</div><span class="btn">Read</span></div></div>
    <div class="art"><div class="thumb" style="background:linear-gradient(145deg,#c9a84c,#8a6d1d)"></div><div><div class="cat">Akhlaq · English</div><div class="tt">Good character in the marketplace</div><div class="meta">Source: Riyadh as-Salihin · 3 min read</div><span class="btn">Read</span></div></div>
    <div class="art"><div class="thumb" style="background:linear-gradient(145deg,#134e42,#1a6b5a)"></div><div><div class="cat">Seerah · Bangla</div><div class="tt">মক্কা বিজয়ের শিক্ষা</div><div class="meta">Key takeaway included · 5 min read</div><span class="btn">Read</span></div></div>
  </div>
</div>`
	},
	{
		file: 'screenshot-09-duas.html',
		num: 9,
		label: 'DUAS & LEARNING',
		title: 'Duas,<br><em>Quran, Hadith</em>',
		sub: 'Share authentic duas, daily Quran verse, Hadith, and 99 Names — with Bangla and English meanings.',
		css: `
  .card { padding:22px; }
  .ht { font-size:18px; font-weight:800; color:#134e42; margin-bottom:12px; }
  .dua { padding:16px; border-radius:12px; border:1px solid #e4e7ec; background:#fafbfc; margin-bottom:12px; }
  .ar { font-size:22px; text-align:right; color:#134e42; font-weight:700; line-height:1.6; direction:rtl; }
  .bn { margin-top:8px; font-size:13px; color:#344054; line-height:1.45; }
  .src { margin-top:8px; font-size:11px; font-weight:700; color:#c9a84c; }
  .row2 { display:grid; grid-template-columns:1fr 1fr; gap:10px; }
  .mini { padding:14px; border-radius:12px; background:rgba(26,107,90,.07); border:1px solid rgba(26,107,90,.14); }
  .mini b { display:block; font-size:13px; color:#134e42; }
  .mini span { display:block; margin-top:4px; font-size:12px; color:#667085; }
`,
		card: `<div class="card"><div class="ht">Duas & Azkar · ${ mosque }</div>
  <div class="dua">
    <div class="ar">بِسْمِ اللَّهِ الرَّحْمَٰنِ الرَّحِيمِ</div>
    <div class="bn">পরম করুণাময় অসীম দয়ালু আল্লাহর নামে। Morning remembrance · counter ready.</div>
    <div class="src">Source · Quran 1:1</div>
  </div>
  <div class="row2">
    <div class="mini"><b>Quran Verse</b><span>Daily ayah with Bangla & English</span></div>
    <div class="mini"><b>Hadith of Day</b><span>Authentic collections, shareable</span></div>
  </div>
</div>`
	},
	{
		file: 'screenshot-10-notices.html',
		num: 10,
		label: 'NOTICES & EVENTS',
		title: 'Keep the<br><em>Community</em>',
		sub: 'Schedule notices, popup alerts, community events, and iCal downloads for your congregation.',
		css: `
  .card { padding:22px; background:#f6f7f9; }
  .ht { font-size:18px; font-weight:800; color:#134e42; margin-bottom:12px; }
  .notice { display:flex; gap:12px; align-items:flex-start; padding:14px; border-radius:12px; background:#fff; border:1px solid #e4e7ec; margin-bottom:10px; }
  .tag { padding:4px 8px; border-radius:6px; background:#1a6b5a; color:#fff; font-size:10px; font-weight:800; }
  .tag.u { background:#b42318; }
  .notice b { display:block; font-size:14px; color:#111827; }
  .notice span { display:block; margin-top:3px; font-size:12px; color:#667085; }
  .ev { margin-top:14px; padding:14px; border-radius:12px; background:#fff; border:1px solid #e4e7ec; }
  .ev .d { font-size:11px; font-weight:800; color:#c9a84c; text-transform:uppercase; }
  .ev .t { margin-top:4px; font-size:15px; font-weight:800; color:#134e42; }
  .ev .m { margin-top:4px; font-size:12px; color:#667085; }
`,
		card: `<div class="card"><div class="ht">Notices & Events · ${ mosque }</div>
  <div class="notice"><span class="tag">GENERAL</span><div><b>Parking update this Friday</b><span>Use the south gate · starts 17 Jul</span></div></div>
  <div class="notice"><span class="tag u">URGENT</span><div><b>Water outage after Maghrib</b><span>Ends tonight · temporary bottles available</span></div></div>
  <div class="ev"><div class="d">Event · 3 days left</div><div class="t">Youth Halaqa · Seerah night</div><div class="m">Saturday 8:00 PM · Main hall · Add to calendar</div></div>
</div>`
	}
];

for ( const shot of shots ) {
	const html = shell( shot.num, shot.label, shot.title, shot.sub, shot.card, shot.css );
	fs.writeFileSync( path.join( dir, shot.file ), html, 'utf8' );
	console.log( 'Wrote', shot.file );
}
console.log( 'Done', shots.length );
