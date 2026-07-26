<?php
session_start();
require_once __DIR__ . "/slots_list.php";

$admin_password = "snehil";

// --- Login handling ---
if (isset($_POST['login_password'])) {
    if ($_POST['login_password'] === $admin_password) {
        $_SESSION['bgmi_admin'] = true;
    } else {
        $login_error = "Wrong password.";
    }
}
if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: admin.php");
    exit;
}

$is_logged_in = !empty($_SESSION['bgmi_admin']);

// --- Actions (only when logged in) ---
if ($is_logged_in && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $booked = load_booked_slots();

    if (isset($_POST['toggle_slot'])) {
        $slot = $_POST['toggle_slot'];
        if (isset($booked[$slot])) {
            unset($booked[$slot]); // unbook
        } else {
            $booked[$slot] = "admin"; // manually marked booked by admin
        }
        save_booked_slots($booked);
    }

    if (isset($_POST['reset_all'])) {
        save_booked_slots([]); // clear all slot bookings
        save_orders([]); // clear cooldowns too
    }

    header("Location: admin.php");
    exit;
}

$slots = get_slot_list();
$booked = load_booked_slots();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>BGMI Room Admin</title>
<style>
  body { font-family: Arial, sans-serif; background: #f5f6fa; margin: 0; padding: 30px; }
  .wrap { max-width: 500px; margin: 0 auto; }
  h2 { text-align: center; color: #333; }
  .login-box, .panel { background: #fff; padding: 25px; border-radius: 12px; box-shadow: 0 4px 12px rgba(0,0,0,0.1); }
  input { width: 100%; padding: 12px; margin: 10px 0; border: 1px solid #ddd; border-radius: 8px; box-sizing: border-box; font-size: 14px; }
  .error { color: #e11d48; font-size: 13px; text-align: center; }
  button { border: none; border-radius: 8px; padding: 10px 14px; font-size: 14px; cursor: pointer; }
  .login-btn { width: 100%; background: #111827; color: #fff; padding: 12px; font-size: 16px; }
  .top-bar { display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px; }
  .reset-btn { background: #e11d48; color: #fff; }
  .logout-link { font-size: 13px; color: #555; text-decoration: none; }
  table { width: 100%; border-collapse: collapse; margin-top: 10px; }
  th, td { text-align: left; padding: 8px 6px; border-bottom: 1px solid #eee; font-size: 13px; }
  .status-free { color: #16a34a; font-weight: bold; }
  .status-booked { color: #e11d48; font-weight: bold; }
  .mark-btn { background: #e11d48; color: #fff; }
  .unmark-btn { background: #16a34a; color: #fff; }
</style>
</head>
<body>
<div class="wrap">
<?php if (!$is_logged_in): ?>
  <h2>Admin Login</h2>
  <div class="login-box">
    <?php if (!empty($login_error)): ?><p class="error"><?= htmlspecialchars($login_error) ?></p><?php endif; ?>
    <form method="POST">
      <input type="password" name="login_password" placeholder="Admin password" required autofocus>
      <button type="submit" class="login-btn">Login</button>
    </form>
  </div>
<?php else: ?>
  <div class="top-bar">
    <h2 style="margin:0;">BGMI Room Slots</h2>
    <a class="logout-link" href="?logout=1">Logout</a>
  </div>
  <div class="panel">
    <form method="POST" onsubmit="return confirm('Reset ALL slots and cooldowns? This cannot be undone.');">
      <button type="submit" name="reset_all" value="1" class="reset-btn" style="width:100%;">🔄 Reset All Slots (Daily)</button>
    </form>
    <table>
      <tr><th>Slot</th><th>Status</th><th></th></tr>
      <?php foreach ($slots as $slot):
          $isBooked = isset($booked[$slot]);
          $who = $isBooked ? $booked[$slot] : "";
      ?>
      <tr>
        <td><?= htmlspecialchars($slot) ?></td>
        <td>
          <?php if ($isBooked): ?>
            <span class="status-booked">Booked<?= $who && $who !== "admin" ? " (@" . htmlspecialchars($who) . ")" : "" ?></span>
          <?php else: ?>
            <span class="status-free">Free</span>
          <?php endif; ?>
        </td>
        <td>
          <form method="POST" style="margin:0;">
            <input type="hidden" name="toggle_slot" value="<?= htmlspecialchars($slot) ?>">
            <button type="submit" class="<?= $isBooked ? 'unmark-btn' : 'mark-btn' ?>">
              <?= $isBooked ? "Unbook" : "Mark Booked" ?>
            </button>
          </form>
        </td>
      </tr>
      <?php endforeach; ?>
    </table>
  </div>
<?php endif; ?>
</div>
</body>
</html>
