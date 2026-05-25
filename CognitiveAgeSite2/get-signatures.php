<?php
// Set JSON header
header('Content-Type: application/json; charset=utf-8');

$signaturesFile = 'data/signatures.json';

if (file_exists($signaturesFile)) {
    $fileContent = file_get_contents($signaturesFile);
    if ($fileContent) {
        $signatures = json_decode($fileContent, true) ?? [];
        // Sort by timestamp descending (newest first)
        usort($signatures, function($a, $b) {
            return strtotime($b['timestamp']) - strtotime($a['timestamp']);
        });
        echo json_encode($signatures);
    } else {
        echo json_encode([]);
    }
} else {
    echo json_encode([]);
}
?>
