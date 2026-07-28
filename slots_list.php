<?php
// Canonical list of bookable slots. Edit this array to change available timings.
function get_slot_list() {
    return [
        "1:00 PM - IDP",
        "1:10 PM - IDP",
        "1:30 PM - IDP",
        "2:00 PM - IDP",
        "2:10 PM - IDP",
        "3:00 PM - IDP",
        "3:10 PM - IDP",
        "3:30 PM - IDP",
        "4:00 PM - IDP",
        "4:10 PM - IDP",
        "4:30 PM - IDP",
        "5:00 PM - IDP",
        "5:10 PM - IDP",
        "6:00 PM - IDP",
        "6:10 PM - IDP",
        "7:00 PM - IDP",
        "7:10 PM - IDP",
        "8:00 PM - IDP",
        "8:10 PM - IDP",
        "8:30 PM - IDP",
        "9:10 PM - IDP",
        "9:30 PM - IDP",
        "10:00 PM - IDP",
        "10:10 PM - IDP",
        "10:30 PM - IDP",
        "11:00 PM - IDP",
        "11:10 PM - IDP",
        "11:30 PM - IDP",
        "12:00 AM - IDP",
    ];
}

// --- Upstash Redis REST storage ---
// Prefer setting these as Environment Variables in Render's dashboard
// (Settings -> Environment) instead of leaving them here in code.
function upstash_base_url() {
    return getenv('UPSTASH_REDIS_REST_URL') ?: "https://optimal-liger-150954.upstash.io";
}
function upstash_token() {
    return getenv('UPSTASH_REDIS_REST_TOKEN') ?: "gQAAAAAAAk2qAAIgcDIxZGY3MmJkNzFhNjU0YWFlYjBlOWI2ZmE0MWU2ZmJhOA";
}

function upstash_call($method, $path, $body = null) {
    $url = rtrim(upstash_base_url(), "/") . $path;
    $headers = "Authorization: Bearer " . upstash_token() . "\r\n";
    if ($body !== null) $headers .= "Content-Type: text/plain\r\n";

    $options = [
        "http" => [
            "method"        => $method,
            "header"        => $headers,
            "content"       => $body,
            "ignore_errors" => true,
            "timeout"       => 8,
        ]
    ];
    $context = stream_context_create($options);
    $result = @file_get_contents($url, false, $context);
    return $result === false ? null : $result;
}

function upstash_get_value($key) {
    $res = upstash_call("GET", "/get/" . rawurlencode($key));
    if ($res === null) return null;
    $data = json_decode($res, true);
    return isset($data['result']) ? $data['result'] : null;
}

function upstash_set_value($key, $value) {
    upstash_call("POST", "/set/" . rawurlencode($key), $value);
}

// slots -> { "slot label": "telegram_username", ... } for BOOKED slots only.
function load_booked_slots() {
    $raw = upstash_get_value("bgmi_booked_slots");
    if (!$raw) return [];
    $data = json_decode($raw, true);
    return is_array($data) ? $data : [];
}
function save_booked_slots($data) {
    upstash_set_value("bgmi_booked_slots", json_encode($data));
}

// per-username booking timestamps (kept for reference / future cooldown use)
function load_orders() {
    $raw = upstash_get_value("bgmi_orders");
    if (!$raw) return [];
    $data = json_decode($raw, true);
    return is_array($data) ? $data : [];
}
function save_orders($data) {
    upstash_set_value("bgmi_orders", json_encode($data));
}
?>
