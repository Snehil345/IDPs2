<?php require_once __DIR__ . "/slots_list.php";
$slots = get_slot_list();
$booked = load_booked_slots();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>BGMI Room Booking</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Rajdhani:wght@500;600;700&family=JetBrains+Mono:wght@400;500;700&display=swap" rel="stylesheet">
<style>
  :root{
    --bg:#0a0d10;
    --panel:#12161a;
    --panel-2:#171c21;
    --line:#262e34;
    --yellow:#f2b807;
    --yellow-dim:#8a6c07;
    --danger:#e8432f;
    --text:#e7ecef;
    --text-dim:#8a9299;
  }

  *{box-sizing:border-box;}

  body{
    margin:0;
    min-height:100vh;
    display:flex;
    justify-content:center;
    align-items:center;
    padding:24px;
    font-family:'Rajdhani',sans-serif;
    color:var(--text);
    background:
      radial-gradient(circle at 20% 15%, rgba(242,184,7,0.06), transparent 40%),
      radial-gradient(circle at 85% 90%, rgba(232,67,47,0.05), transparent 45%),
      repeating-linear-gradient(0deg, rgba(255,255,255,0.015) 0px, rgba(255,255,255,0.015) 1px, transparent 1px, transparent 3px),
      var(--bg);
  }

  .wrap{
    position:relative;
    width:380px;
    max-width:100%;
  }

  /* reticle corner brackets — the signature element */
  .wrap::before, .wrap::after,
  .corner-tl, .corner-br{
    content:"";
    position:absolute;
    width:22px;
    height:22px;
    z-index:2;
    pointer-events:none;
  }
  .wrap::before{
    top:-9px; left:-9px;
    border-top:3px solid var(--yellow);
    border-left:3px solid var(--yellow);
  }
  .wrap::after{
    top:-9px; right:-9px;
    border-top:3px solid var(--yellow);
    border-right:3px solid var(--yellow);
  }
  .corner-tl{
    bottom:-9px; left:-9px;
    border-bottom:3px solid var(--yellow);
    border-left:3px solid var(--yellow);
  }
  .corner-br{
    bottom:-9px; right:-9px;
    border-bottom:3px solid var(--yellow);
    border-right:3px solid var(--yellow);
  }

  .panel{
    background:linear-gradient(180deg, var(--panel) 0%, var(--panel-2) 100%);
    border:1px solid var(--line);
    padding:0 0 26px;
    position:relative;
    overflow:hidden;
    box-shadow:0 20px 50px rgba(0,0,0,0.5);
  }

  .top-bar{
    display:flex;
    align-items:center;
    justify-content:space-between;
    padding:12px 22px;
    background:repeating-linear-gradient(135deg, var(--yellow) 0 10px, #d9a300 10px 20px);
    color:#0a0d10;
  }
  .top-bar span{
    font-family:'JetBrains Mono',monospace;
    font-size:11px;
    font-weight:700;
    letter-spacing:1.5px;
  }
  .live-dot{
    width:8px;height:8px;border-radius:50%;
    background:#0a0d10;
    display:inline-block;
    margin-right:6px;
    animation:pulse 1.4s infinite;
  }
  @keyframes pulse{
    0%,100%{opacity:1;}
    50%{opacity:0.25;}
  }

  .head{
    padding:22px 26px 4px;
  }
  .eyebrow{
    font-family:'JetBrains Mono',monospace;
    font-size:11px;
    letter-spacing:3px;
    color:var(--yellow);
    margin:0 0 6px;
  }
  h2{
    margin:0;
    font-size:26px;
    font-weight:700;
    letter-spacing:1px;
    text-transform:uppercase;
    line-height:1.1;
  }
  .sub{
    color:var(--text-dim);
    font-size:13px;
    margin:8px 0 0;
    font-weight:500;
  }

  form{
    padding:14px 26px 0;
  }

  label{
    font-family:'JetBrains Mono',monospace;
    font-size:11px;
    letter-spacing:1.5px;
    text-transform:uppercase;
    color:var(--text-dim);
    display:flex;
    align-items:center;
    gap:8px;
    margin:18px 0 8px;
  }
  label::before{
    content:"";
    width:6px;height:6px;
    background:var(--yellow);
    display:inline-block;
    transform:rotate(45deg);
  }

  input, select{
    width:100%;
    padding:13px 14px;
    background:#0d1114;
    border:1px solid var(--line);
    color:var(--text);
    font-family:'Rajdhani',sans-serif;
    font-size:15px;
    font-weight:600;
    border-radius:4px;
    outline:none;
    appearance:none;
    transition:border-color .2s, box-shadow .2s;
  }
  select{
    background-image:linear-gradient(45deg, transparent 50%, var(--yellow) 50%), linear-gradient(135deg, var(--yellow) 50%, transparent 50%);
    background-position: calc(100% - 20px) center, calc(100% - 14px) center;
    background-size: 6px 6px, 6px 6px;
    background-repeat:no-repeat;
  }
  input::placeholder{
    color:#4c5560;
    font-weight:500;
  }
  input:focus, select:focus{
    border-color:var(--yellow);
    box-shadow:0 0 0 3px rgba(242,184,7,0.15);
  }
  option:disabled{
    color:var(--danger);
  }

  button{
    width:100%;
    padding:15px;
    margin-top:24px;
    border:none;
    border-radius:4px;
    background:var(--yellow);
    color:#0a0d10;
    font-family:'Rajdhani',sans-serif;
    font-size:17px;
    font-weight:700;
    letter-spacing:2px;
    text-transform:uppercase;
    cursor:pointer;
    position:relative;
    clip-path:polygon(0 0, 100% 0, 100% 70%, 96% 100%, 0 100%);
    transition:background .2s, transform .1s;
  }
  button:hover{
    background:#ffcc2e;
  }
  button:active{
    transform:translateY(1px);
  }

  .notice{
    margin:14px 26px 0;
    padding:12px 14px;
    border-radius:4px;
    text-align:center;
    font-family:'JetBrains Mono',monospace;
    font-size:13px;
    font-weight:700;
    letter-spacing:1px;
  }
  .notice-white{
    background:rgba(231,236,239,0.08);
    border:1px solid #3a4450;
    color:var(--text);
  }
  .notice-yellow{
    background:rgba(242,184,7,0.12);
    border:1px solid var(--yellow);
    color:var(--yellow);
    box-shadow:0 0 12px rgba(242,184,7,0.15);
  }
  .notice-yellow span{
    font-size:14.5px;
    text-decoration:underline;
  }
  .notice:last-child{
    margin-bottom:22px;
  }
</style>
</head>
<body>
  <div class="wrap">
    <div class="corner-tl"></div>
    <div class="corner-br"></div>
    <div class="panel">
      <div class="top-bar">
        <span>&#9679; MATCH LOBBY</span>
        <span><span class="live-dot"></span>SLOTS OPEN</span>
      </div>
      <div class="head">
        <p class="eyebrow">CUSTOM ROOM // BGMI</p>
        <h2>Book Your Squad's Slot</h2>
        <p class="sub">Reserve a room timing before it fills up.</p>
      </div>

      <form action="submit.php" method="POST">
        <label for="telegram_username">Telegram Username</label>
        <input type="text" id="telegram_username" name="telegram_username" placeholder="@username" required>

        <label for="time_slot">Room Timing</label>
        <select id="time_slot" name="time_slot" required>
          <option value="" disabled selected>Choose a slot</option>
          <?php foreach ($slots as $slot):
              $isBooked = isset($booked[$slot]); ?>
          <option value="<?= htmlspecialchars($slot) ?>" <?= $isBooked ? "disabled" : "" ?>>
            <?= htmlspecialchars($slot) ?><?= $isBooked ? " — BOOKED" : "" ?>
          </option>
          <?php endforeach; ?>
        </select>

        <button type="submit">Lock In Slot</button>
      </form>
      <p class="notice notice-white">ONE ENTRY PER SQUAD · CONFIRMATION SENT ON TELEGRAM</p>
      <p class="notice notice-yellow">FOR ANY QUERY, DM <span>@WINGODO5</span></p>
      
    </div>
  </div>
</body>
</html>