<?php /* focus.php - Drop-in page */ ?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Focus Mode | QuickList</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">

  <!-- Fonts & Icons -->
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800&family=Space+Grotesk:wght@600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

  <!-- Bootstrap 5 -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

  <style>
    :root{
      --primary-grad: linear-gradient(135deg, #6366f1 0%, #22c55e 100%);
      --work-grad: linear-gradient(135deg, #4f46e5 0%, #22c55e 100%);
      --break-grad: linear-gradient(135deg, #f59e0b 0%, #f43f5e 100%);
      --glass-bg: rgba(255,255,255,0.7);
      --glass-border: rgba(0,0,0,0.06);
      --shadow: rgba(31,38,135,0.22);
      --text: #0f172a;
    }
    html,body{height:100%}
    body{
      font-family: Inter, system-ui, -apple-system, Segoe UI, Roboto, Helvetica, Arial, "Apple Color Emoji","Segoe UI Emoji";
      background: linear-gradient(135deg, #eef2ff 0%, #e0f2fe 100%);
      background-attachment: fixed;
      color: var(--text);
      overflow-x: hidden;
    }
    body::before{
      content:""; position:fixed; inset:0; z-index:-1; pointer-events:none;
      background-image:
        radial-gradient(circle at 15% 85%, rgba(99,102,241,.22) 0%, transparent 50%),
        radial-gradient(circle at 85% 20%, rgba(34,197,94,.2) 0%, transparent 50%),
        radial-gradient(circle at 45% 45%, rgba(245,158,11,.18) 0%, transparent 45%);
    }
    .shell{max-width: 980px; margin: 36px auto; padding: 0 16px;}
    .cardx{
      background: var(--glass-bg); backdrop-filter: blur(14px); -webkit-backdrop-filter: blur(14px);
      border: 1px solid var(--glass-border); border-radius: 24px; box-shadow: 0 10px 34px var(--shadow), inset 0 1px 0 rgba(255,255,255,.35);
      padding: 1.5rem; position: relative; overflow: hidden;
    }
    .cardx::before{content:""; position:absolute; left:0; right:0; top:0; height:4px; background: var(--primary-grad);}
    .title-row h1{
      font-family: "Space Grotesk", Inter, sans-serif; font-weight: 800; font-size: clamp(1.8rem, 3.2vw, 2.4rem);
      background: linear-gradient(135deg,#111827,#1f2937); -webkit-background-clip:text; -webkit-text-fill-color: transparent; background-clip:text; margin:0;
    }
    .badge-mode{ background:#fef3c7; color:#92400e; border-radius: 10px; font-weight:800; letter-spacing:.3px; }

    .ring-wrap{ display:flex; justify-content:center; margin: 12px 0 8px; }
    .ring{
      width: 230px; height: 230px; border-radius: 50%;
      background: conic-gradient(#22c55e var(--p, 100%), rgba(99,102,241,.18) 0%);
      display:flex; align-items:center; justify-content:center; position:relative; transition: background .4s ease;
    }
    .ring::after{
      content:""; position:absolute; inset:12px; background: rgba(255,255,255,.75); border-radius: 50%;
      backdrop-filter: blur(6px); -webkit-backdrop-filter: blur(6px); border:1px solid rgba(0,0,0,.06);
    }
    .ring-inner{ position:relative; z-index:2; text-align:center; }
    .timer{
      font-family: "Space Grotesk", Inter, sans-serif; font-weight: 800; letter-spacing: 2px;
      font-size: clamp(3rem, 8vw, 5rem);
      background: var(--work-grad); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text;
      margin: 4px 0;
    }
    .session-type{ text-align:center; font-weight:800; color:#4f46e5 }

    .controls .btn{
      border:none; border-radius: 14px; padding: .85rem 1.1rem; font-weight:800; letter-spacing:.3px;
      box-shadow: 0 10px 22px rgba(79,70,229,.18); transition: transform .2s ease, box-shadow .2s ease;
    }
    .controls .btn:active{ transform: scale(.98); }
    .btn-work{ background: var(--work-grad); color:#fff; }
    .btn-break{ background: var(--break-grad); color:#fff; }
    .btn-ghost{ background: rgba(255,255,255,.75); border:1px solid rgba(0,0,0,.06); color:#111827; }

    .panel{ background: rgba(255,255,255,.78); border:1px solid rgba(0,0,0,.06); border-radius: 16px; padding: 1rem; }
    .input-group .btn{ border-radius: 10px; }

    .stat{ text-align:center; padding:.75rem; border-radius: 12px; background: rgba(255,255,255,.78); border:1px solid rgba(0,0,0,.06); }
    .stat .num{ font-family:"Space Grotesk", Inter, sans-serif; font-weight:800; font-size:1.6rem; background: var(--primary-grad); -webkit-background-clip:text; -webkit-text-fill-color:transparent; background-clip:text; }

    @media (max-width:768px){ .ring{ width: 190px; height:190px; } .shell{ margin-top: 20px; } }
  </style>
</head>
<body>
<?php include '../navbar.php'; ?>

<div class="shell">
  <div class="cardx">
    <div class="d-flex justify-content-between align-items-center title-row mb-2">
      <h1><i class="fa-solid fa-bullseye me-2 text-success"></i>Focus Mode</h1>
      <span id="modeBadge" class="badge badge-mode">Pomodoro</span>
    </div>

    <!-- Timer + Ring -->
    <div class="ring-wrap">
      <div id="ring" class="ring" style="--p: 100%;">
        <div class="ring-inner">
          <div id="timer" class="timer">25:00</div>
          <div id="sessionType" class="session-type">Work</div>
        </div>
      </div>
    </div>

    <!-- Controls -->
    <div class="controls d-flex flex-wrap gap-2 justify-content-center my-3">
      <button type="button" class="btn btn-work" id="btnStart"><i class="fas fa-play me-1"></i>Start</button>
      <button type="button" class="btn btn-break" id="btnPause"><i class="fas fa-pause me-1"></i>Pause</button>
      <button type="button" class="btn btn-ghost" id="btnReset"><i class="fas fa-rotate-left me-1"></i>Reset</button>
      <button type="button" class="btn btn-ghost" id="btnSkip"><i class="fas fa-forward me-1"></i>Skip</button>
      <button type="button" class="btn btn-ghost" id="btnNotify"><i class="fas fa-bell me-1"></i>Enable Notifications</button>
    </div>

    <!-- Duration Settings -->
    <div class="row g-3">
      <div class="col-md-4">
        <div class="panel">
          <label class="form-label fw-bold" for="focusMin">Focus duration (min)</label>
          <div class="input-group">
            <input type="number" min="1" max="120" step="1" class="form-control" id="focusMin" value="25" inputmode="numeric" aria-label="Focus minutes">
            <button type="button" class="btn btn-ghost" data-preset="25">25</button>
            <button type="button" class="btn btn-ghost" data-preset="50">50</button>
          </div>
          <small class="text-muted d-block mt-1">Choose 1–120 min</small>
        </div>
      </div>
      <div class="col-md-4">
        <div class="panel">
          <label class="form-label fw-bold" for="shortBreakMin">Short break (min)</label>
          <div class="input-group">
            <input type="number" min="1" max="60" step="1" class="form-control" id="shortBreakMin" value="5" inputmode="numeric" aria-label="Short break minutes">
            <button type="button" class="btn btn-ghost" data-preset="5">5</button>
            <button type="button" class="btn btn-ghost" data-preset="10">10</button>
          </div>
          <small class="text-muted d-block mt-1">Choose 1–60 min</small>
        </div>
      </div>
      <div class="col-md-4">
        <div class="panel">
          <label class="form-label fw-bold" for="longBreakMin">Long break (min)</label>
          <div class="input-group">
            <input type="number" min="5" max="60" step="1" class="form-control" id="longBreakMin" value="15" inputmode="numeric" aria-label="Long break minutes">
            <button type="button" class="btn btn-ghost" data-preset="15">15</button>
            <button type="button" class="btn btn-ghost" data-preset="20">20</button>
          </div>
          <small class="text-muted d-block mt-1">After every 4 rounds</small>
        </div>
      </div>
    </div>

    <!-- Stats -->
    <div class="row g-3 mt-2">
      <div class="col-6 col-md-3"><div class="stat"><div class="text-muted fw-semibold">Rounds</div><div id="statRounds" class="num">0</div></div></div>
      <div class="col-6 col-md-3"><div class="stat"><div class="text-muted fw-semibold">Focus min</div><div id="statFocusMin" class="num">0</div></div></div>
      <div class="col-6 col-md-3"><div class="stat"><div class="text-muted fw-semibold">Break min</div><div id="statBreakMin" class="num">0</div></div></div>
      <div class="col-6 col-md-3"><div class="stat"><div class="text-muted fw-semibold">Cycles</div><div id="statCycles" class="num">0</div></div></div>
    </div>

    <!-- Quote -->
    <div class="text-center mt-3">
      <div class="fst-italic text-primary-emphasis">
        <i class="fas fa-quote-left me-1"></i>
        <span id="quoteText">The secret of getting ahead is getting started.</span>
        <i class="fas fa-quote-right ms-1"></i>
      </div>
    </div>
  </div>
</div>

<!-- Sounds -->
<audio id="beepWork" preload="auto">
  <source src="https://assets.mixkit.co/active_storage/sfx/2850/2850-preview.mp3" type="audio/mpeg">
</audio>
<audio id="beepBreak" preload="auto">
  <source src="https://assets.mixkit.co/active_storage/sfx/1435/1435-preview.mp3" type="audio/mpeg">
</audio>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
/* -------------- State -------------- */
let interval = null;
let mode = 'work'; // 'work' | 'short' | 'long'
let workSec = 25*60, shortSec = 5*60, longSec = 15*60;
let remaining = workSec, total = workSec;
let rounds = 0;
let stats = { roundsToday: 0, focusMin: 0, breakMin: 0, cycles: 0 };
const QUOTES = [
  "The secret of getting ahead is getting started.",
  "Focus on being productive instead of busy.",
  "Small progress is still progress.",
  "Your future is created by what you do today.",
  "Discipline is choosing what you want most."
];

/* -------------- Elements -------------- */
const elTimer = document.getElementById('timer');
const elRing = document.getElementById('ring');
const elSessionType = document.getElementById('sessionType');
const elModeBadge = document.getElementById('modeBadge');

const elBtnStart = document.getElementById('btnStart');
const elBtnPause = document.getElementById('btnPause');
const elBtnReset = document.getElementById('btnReset');
const elBtnSkip  = document.getElementById('btnSkip');
const elBtnNotify = document.getElementById('btnNotify');

const inpFocus = document.getElementById('focusMin');
const inpShort = document.getElementById('shortBreakMin');
const inpLong  = document.getElementById('longBreakMin');

const statRounds = document.getElementById('statRounds');
const statFocusMin = document.getElementById('statFocusMin');
const statBreakMin = document.getElementById('statBreakMin');
const statCycles = document.getElementById('statCycles');

const quoteText = document.getElementById('quoteText');
const beepWork = document.getElementById('beepWork');
const beepBreak = document.getElementById('beepBreak');

/* -------------- Helpers -------------- */
function clampInt(v, min, max){
  let n = parseInt(v || 0, 10);
  if (isNaN(n)) n = min;
  return Math.max(min, Math.min(max, n));
}
function fmt(sec){
  const m = Math.floor(sec/60);
  const s = sec % 60;
  return `${m.toString().padStart(2,'0')}:${s.toString().padStart(2,'0')}`;
}
function setRing(percent, isBreak=false){
  elRing.style.setProperty('--p', `${percent}%`);
  elRing.style.background = `conic-gradient(${isBreak? '#f59e0b':'#22c55e'} ${percent}%, rgba(99,102,241,.18) 0%)`;
}
function updateUI(){
  elTimer.textContent = fmt(remaining);
  const percent = Math.max(0, Math.round((remaining/total)*100));
  setRing(percent, mode!=='work');
  elSessionType.textContent = (mode==='work'?'Work':(mode==='short'?'Short Break':'Long Break'));
  elModeBadge.textContent = (mode==='work'?'Pomodoro':'Break');
  statRounds.textContent = stats.roundsToday;
  statFocusMin.textContent = stats.focusMin;
  statBreakMin.textContent = stats.breakMin;
  statCycles.textContent = stats.cycles;
}
function rotateQuote(){
  const next = QUOTES[Math.floor(Math.random()*QUOTES.length)];
  quoteText.textContent = next;
}

/* -------------- Durations (user-defined) -------------- */
function saveDurations(){
  localStorage.setItem('focusDurations', JSON.stringify({
    focus: clampInt(inpFocus.value, 1, 120),
    short: clampInt(inpShort.value, 1, 60),
    long:  clampInt(inpLong.value,  5, 60),
  }));
}
function loadDurations(){
  const raw = localStorage.getItem('focusDurations');
  if (!raw) return;
  try {
    const d = JSON.parse(raw);
    if (d.focus) inpFocus.value = d.focus;
    if (d.short) inpShort.value = d.short;
    if (d.long)  inpLong.value  = d.long;
  } catch(e){}
}
function applyUserDurations(){
  const focusMin = clampInt(inpFocus.value, 1, 120);
  const shortMin = clampInt(inpShort.value, 1, 60);
  const longMin  = clampInt(inpLong.value,  5, 60);
  workSec  = focusMin * 60;
  shortSec = shortMin * 60;
  longSec  = longMin  * 60;
  if (!interval) {
    if (mode==='work'){ total = workSec; remaining = workSec; }
    if (mode==='short'){ total = shortSec; remaining = shortSec; }
    if (mode==='long'){ total = longSec; remaining = longSec; }
    updateUI();
  }
  saveDurations();
}
[inpFocus, inpShort, inpLong].forEach(el=>{
  el.addEventListener('change', applyUserDurations);
});
document.querySelectorAll('.input-group .btn[data-preset]').forEach(btn=>{
  btn.addEventListener('click', ()=>{
    const group = btn.closest('.input-group');
    const input = group.querySelector('input[type="number"]');
    input.value = btn.dataset.preset;
    applyUserDurations();
  });
});

/* -------------- Mode switching -------------- */
function switchMode(next){
  mode = next;
  if (mode==='work'){ total = workSec; }
  else if (mode==='short'){ total = shortSec; }
  else { total = longSec; }
  remaining = total;
  rotateQuote();
  updateUI();
}
function onSessionEnd(){
  try { (mode==='work' ? beepBreak : beepWork).play(); } catch(e){}
  notify(`${mode==='work'?'Work Complete':'Break Over'}`, `Starting ${mode==='work'?'Break':'Work'} next.`);
  if (mode==='work'){
    rounds += 1;
    stats.roundsToday += 1;
    stats.focusMin += Math.round(workSec/60);
    if (rounds % 4 === 0){
      stats.cycles += 1;
      switchMode('long');
    } else {
      switchMode('short');
    }
  } else {
    stats.breakMin += Math.round((mode==='short'? shortSec : longSec)/60);
    switchMode('work');
  }
  startTimer(); // auto-continue
}

/* -------------- Timer -------------- */
function tick(){
  if (remaining > 0){
    remaining -= 1;
    updateUI();
  } else {
    pauseTimer();
    onSessionEnd();
  }
}
function startTimer(){
  applyUserDurations(); // ensure latest values
  if (interval) return;
  interval = setInterval(tick, 1000);
  elBtnStart.disabled = true;
  elBtnPause.disabled = false;
}
function pauseTimer(){
  if (interval){ clearInterval(interval); interval = null; }
  elBtnStart.disabled = false;
  elBtnPause.disabled = true;
}
function resetTimer(){
  pauseTimer();
  if (mode==='work') remaining = total = workSec;
  else if (mode==='short') remaining = total = shortSec;
  else remaining = total = longSec;
  updateUI();
}
function skipSession(){
  pauseTimer();
  onSessionEnd();
}

/* -------------- Notifications (MDN pattern) -------------- */
function notify(title, body){
  if (!("Notification" in window)) return;
  if (Notification.permission === "granted"){
    new Notification(title, { body });
  }
}
elBtnNotify.addEventListener('click', async ()=>{
  if (!("Notification" in window)) { alert('Notifications not supported in this browser.'); return; }
  if (Notification.permission === "granted"){ notify("Notifications Enabled", "You will get alerts when sessions end."); }
  else {
    try {
      const perm = await Notification.requestPermission();
      if (perm === 'granted') notify("Notifications Enabled", "You will get alerts when sessions end.");
    } catch(e){}
  }
}); // MDN Notifications guidance [7][13][4]

/* -------------- Pause on tab hide (MDN) -------------- */
document.addEventListener('visibilitychange', ()=>{
  if (document.hidden && interval){
    pauseTimer();
  }
}); // Page Visibility API [1][8]

/* -------------- Keyboard shortcuts -------------- */
// Space: start/pause, R: reset, N: skip
document.addEventListener('keydown', (e)=>{
  if (e.target.matches('input, textarea')) return;
  if (e.code === 'Space'){ e.preventDefault(); if (interval) pauseTimer(); else startTimer(); }
  if (e.key.toLowerCase() === 'r'){ resetTimer(); }
  if (e.key.toLowerCase() === 'n'){ skipSession(); }
});

/* -------------- Attach listeners & init -------------- */
elBtnStart.addEventListener('click', startTimer);
elBtnPause.addEventListener('click', pauseTimer);
elBtnReset.addEventListener('click', resetTimer);
elBtnSkip .addEventListener('click', skipSession);

loadDurations();
applyUserDurations();
updateUI();
</script>
</body>
</html>
