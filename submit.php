<?php
require_once __DIR__ . "/slots_list.php";
require_once __DIR__ . "/bot_helpers.php";

// Telegram Bot setup — this chat_id is YOUR own admin chat, for the booking log.
// The booker's own DM is sent separately below via their registered chat_id.
$token = "8183046818:AAFERd6yNEt86ohzCNzCCcAK_P00dmApI1Q";
$chat_id = "6011657948";

// Get user input
$username = trim($_POST['telegram_username']);
$time_slot = trim($_POST['time_slot']);

$valid_slots = get_slot_list();
$booked = load_booked_slots();

function render_message($title, $color, $heading, $body) {
    echo "
    <!DOCTYPE html>
    <html lang='en'>
    <head>
        <meta charset='UTF-8'>
        <meta name='viewport' content='width=device-width, initial-scale=1.0'>
        <title>$title</title>
        <style>
            body { font-family: Arial, sans-serif; background: #f5f6fa; display: flex;
                   justify-content: center; align-items: center; height: 100vh; margin: 0; }
            .message-box { background: #fff; padding: 30px; border-radius: 12px;
                            box-shadow: 0 4px 12px rgba(0,0,0,0.1); text-align: center; width: 350px; }
            h2 { color: $color; font-size: 20px; }
        </style>
    </head>
    <body>
        <div class='message-box'>
            <h2>$heading</h2>
            <p>$body</p>
        </div>
    </body>
    </html>";
    exit;
}

// Validate the slot exists in our canonical list
if (!in_array($time_slot, $valid_slots, true)) {
    render_message("Invalid Slot", "#e11d48", "⚠️ Invalid Slot", "Please choose a valid slot from the list.");
}

// Slot already taken by someone (server-side lock, source of truth)
if (isset($booked[$time_slot])) {
    render_message("Slot Unavailable", "#e11d48", "🚫 Slot Already Booked",
        "Sorry, <b>$time_slot</b> was just booked by someone else. Please pick another slot.");
}

// Lock the slot
$booked[$time_slot] = $username;
save_booked_slots($booked);

// Save a timestamp record for this username (kept for reference / future cooldown use)
$orders = load_orders();
$orders[$username] = time();
save_orders($orders);

// Notify admin's own chat (booking log)
$message = "🎮 *New BGMI Room Booking*\n\n👤 Telegram Username: @$username\n⏰ Slot: $time_slot";
file_get_contents("https://api.telegram.org/bot$token/sendMessage?chat_id=$chat_id&text=" . urlencode($message) . "&parse_mode=Markdown");

// DM the booker directly, if we know their chat_id (they've messaged the bot before)
$booker_chat_id = get_chat_id_for_username($username);
if ($booker_chat_id) {
    send_telegram_message(
        $booker_chat_id,
        "✅ *Slot Booked*\n\nYour slot *$time_slot* is confirmed. We'll DM the Room ID and Password here closer to match time."
    );
    render_message("Booking Confirmed", "#16a34a", "✅ Room Booked",
        "Your slot <b>$time_slot</b> is confirmed — check your Telegram DMs!");
} else {
    $bot_username = telegram_bot_username();
    render_message("Booking Confirmed", "#16a34a", "✅ Room Booked",
        "Your slot <b>$time_slot</b> is confirmed. To receive the Room ID/Password, message our bot "
        . "<a href='https://t.me/$bot_username' target='_blank'>@$bot_username</a> and tap Start.");
}
?>
