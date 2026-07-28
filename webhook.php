<?php
require_once __DIR__ . "/slots_list.php";
require_once __DIR__ . "/bot_helpers.php";

$raw = file_get_contents("php://input");
$update = json_decode($raw, true);

if (is_array($update) && isset($update['message'])) {
    $msg = $update['message'];
    $chat_id = isset($msg['chat']['id']) ? $msg['chat']['id'] : null;
    $username = isset($msg['from']['username']) ? $msg['from']['username'] : null;
    $text = isset($msg['text']) ? trim($msg['text']) : '';

    if ($chat_id && $username) {
        register_chat_id($username, $chat_id);

        if (strpos($text, '/start') === 0) {
            send_telegram_message(
                $chat_id,
                "✅ You're registered! Book a slot on the site with this same Telegram username, and your booking confirmation and Room ID/Password will be sent right here."
            );
        }
    } elseif ($chat_id && !$username) {
        // No public @username set on their Telegram account — we can't match
        // their booking form entry to this chat, so let them know.
        send_telegram_message(
            $chat_id,
            "⚠️ You don't have a Telegram @username set, so we can't link your bookings to this chat. Please set one in Telegram Settings, then message /start again."
        );
    }
}

http_response_code(200);
echo "OK";
?>
