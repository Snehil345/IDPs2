<?php
session_start();
require_once __DIR__ . "/slots_list.php";
require_once __DIR__ . "/bot_helpers.php";

if (empty($_SESSION['bgmi_admin'])) {
    header("Location: admin.php");
    exit;
}

$slots = get_slot_list();
$result = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $room_id = trim($_POST['room_id'] ?? '');
    $room_password = trim($_POST['room_password'] ?? '');
    $match_time = trim($_POST['match_time'] ?? '');

    if ($room_id !== '' && $room_password !== '' && in_array($match_time, $slots, true)) {
        $booked = load_booked_slots();
        $sent = [];
        $failed = [];

        foreach ($booked as $slot => $bookedUsername) {
            if ($slot !== $match_time) continue; // only this match time
            if ($bookedUsername === "admin") continue; // manually-marked slot, no real user to DM

            $chat_id = get_chat_id_for_username($bookedUsername);
            if ($chat_id) {
                $text = "🎮 *Room Details*\n\n⏰ Match: *$match_time*\n🆔 Room ID: `$room_id`\n🔑 Password: `$room_password`\n\nGood luck, see you in the lobby!";
                send_telegram_message($chat_id, $text);
                $sent[] = $bookedUsername;
            } else {
                $failed[] = $bookedUsername;
            }
        }

        $result = ['sent' => $sent, 'failed' => $failed, 'slot' => $match_time];
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Room Distribution</title>
<style>
  body { font-family: Arial, sans-serif; background: #f5f6fa; margin: 0; padding: 30px; }
  .wrap { max-width: 500px; margin: 0 auto; }
  h2 { text-align: center; color: #333; }
  .panel { background: #fff; padding: 25px; border-radius: 12px; box-shadow: 0 4px 12px rgba(0,0,0,0.1); margin-bottom: 20px; }
  label { font-size: 13px; color: #555; display: block; margin-top: 10px; }
  input, select { width: 100%; padding: 12px; margin: 6px 0 10px; border: 1px solid #ddd; border-radius: 8px; box-sizing: border-box; font-size: 14px; }
  button { width: 100%; padding: 12px; border: none; border-radius: 8px; background: #111827; color: #fff; font-size: 16px; cursor: pointer; margin-top: 10px; }
  .top-bar { display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px; }
  .back-link { font-size: 13px; color: #555; text-decoration: none; }
  .result { font-size: 13px; }
  .sent { color: #16a34a; font-weight: bold; }
  .failed { color: #e11d48; font-weight: bold; }
  ul { padding-left: 18px; margin: 6px 0; }
</style>
</head>
<body>
<div class="wrap">
  <div class="top-bar">
    <h2 style="margin:0;">Send Room Details</h2>
    <a class="back-link" href="admin.php">← Back</a>
  </div>

  <div class="panel">
    <form method="POST">
      <label for="match_time">Match Time (Slot)</label>
      <select id="match_time" name="match_time" required>
        <option value="" disabled selected>Select the slot</option>
        <?php foreach ($slots as $slot): ?>
          <option value="<?= htmlspecialchars($slot) ?>"><?= htmlspecialchars($slot) ?></option>
        <?php endforeach; ?>
      </select>

      <label for="room_id">Room ID</label>
      <input type="text" id="room_id" name="room_id" placeholder="e.g. 123456" required>

      <label for="room_password">Room Password</label>
      <input type="text" id="room_password" name="room_password" placeholder="e.g. abcd12" required>

      <button type="submit">Send to Booked Users</button>
    </form>
  </div>

  <?php if ($result): ?>
  <div class="panel result">
    <p>Slot: <b><?= htmlspecialchars($result['slot']) ?></b></p>
    <p class="sent">✅ Sent to <?= count($result['sent']) ?> user(s)</p>
    <?php if ($result['sent']): ?>
      <ul><?php foreach ($result['sent'] as $u): ?><li>@<?= htmlspecialchars($u) ?></li><?php endforeach; ?></ul>
    <?php endif; ?>

    <?php if ($result['failed']): ?>
      <p class="failed">⚠️ Could not reach <?= count($result['failed']) ?> user(s) — they haven't messaged the bot yet:</p>
      <ul><?php foreach ($result['failed'] as $u): ?><li>@<?= htmlspecialchars($u) ?></li><?php endforeach; ?></ul>
    <?php endif; ?>

    <?php if (!$result['sent'] && !$result['failed']): ?>
      <p>No bookings found for this slot.</p>
    <?php endif; ?>
  </div>
  <?php endif; ?>
</div>
</body>
</html>
