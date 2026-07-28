<?php
// Shared Telegram bot helpers.
// Prefer setting these as Environment Variables in Render's dashboard
// (Settings -> Environment) instead of leaving them here in code.
function telegram_token() {
    return getenv('TELEGRAM_BOT_TOKEN') ?: "8844088119:AAGNjKa2jL7wogRZAqM3UiAkiuavN8jgUOI";
}
function telegram_bot_username() {
    return getenv('TELEGRAM_BOT_USERNAME') ?: "IDPs1bot";
}

// Send a DM to a specific chat_id (NOT a username — Telegram bots can only
// message a chat_id, learned once that user has messaged the bot at least once).
function send_telegram_message($chat_id, $text) {
    $token = telegram_token();
    $url = "https://api.telegram.org/bot$token/sendMessage"
         . "?chat_id=" . urlencode($chat_id)
         . "&text=" . urlencode($text)
         . "&parse_mode=Markdown";
    return @file_get_contents($url);
}

// --- username -> chat_id map, stored in Upstash (same store as bookings) ---
// Populated by webhook.php whenever a user messages the bot.
function normalize_username($username) {
    return strtolower(ltrim(trim($username), '@'));
}

function load_chat_id_map() {
    $raw = upstash_get_value("bgmi_chatid_map");
    if (!$raw) return [];
    $data = json_decode($raw, true);
    return is_array($data) ? $data : [];
}

function save_chat_id_map($map) {
    upstash_set_value("bgmi_chatid_map", json_encode($map));
}

function register_chat_id($username, $chat_id) {
    $map = load_chat_id_map();
    $map[normalize_username($username)] = $chat_id;
    save_chat_id_map($map);
}

function get_chat_id_for_username($username) {
    $map = load_chat_id_map();
    $key = normalize_username($username);
    return isset($map[$key]) ? $map[$key] : null;
}
?>
