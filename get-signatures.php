<?php
/**
 * get-signatures.php
 * Returns signatures.json as JSON for signatures.html to render.
 */

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');

$signaturesFile = 'data/signatures.json';

if (!file_exists($signaturesFile)) {
    echo json_encode([]);
    exit;
}

$raw = file_get_contents($signaturesFile);
if (!$raw) {
    echo json_encode([]);
    exit;
}

$signatures = json_decode($raw, true) ?? [];

// Sort newest first
usort($signatures, function($a, $b) {
    return strcmp($b['timestamp'], $a['timestamp']);
});

// Strip email and IP before sending to browser
$public = array_map(function($s) {
    return [
        'fullname'  => $s['fullname'],
        'country'   => $s['country'],
        'reason'    => $s['reason'] ?? '',
        'timestamp' => $s['timestamp'],
        'date'      => $s['date'] ?? '',
    ];
}, $signatures);

echo json_encode($public);
