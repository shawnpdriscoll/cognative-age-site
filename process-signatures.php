<?php
/**
 * process-signature.php
 * Handles signature form submissions from sign.html
 * Saves to data/signatures.json and sends email notifications.
 */

header('Content-Type: application/json; charset=utf-8');

$recipientEmail = 'awakenedwatchman@gmail.com';
$senderEmail    = 'noreply@cognitiveageinitiative.com';
$signaturesFile = 'data/signatures.json';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed.']);
    exit;
}

$fullname = isset($_POST['fullname']) ? trim(strip_tags($_POST['fullname'])) : '';
$email    = isset($_POST['email'])    ? trim(strip_tags($_POST['email']))    : '';
$country  = isset($_POST['country'])  ? trim(strip_tags($_POST['country']))  : '';
$reason   = isset($_POST['reason'])   ? trim(strip_tags($_POST['reason']))   : '';

if (empty($fullname) || empty($email) || empty($country)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Please fill in all required fields.']);
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Please enter a valid email address.']);
    exit;
}

// Create data directory if needed
if (!is_dir('data')) {
    mkdir('data', 0775, true);
}

$record = [
    'id'        => uniqid('sig_', true),
    'fullname'  => $fullname,
    'email'     => $email,
    'country'   => $country,
    'reason'    => $reason,
    'timestamp' => date('Y-m-d H:i:s'),
    'date'      => date('Y-m-d'),
    'ip'        => $_SERVER['REMOTE_ADDR'] ?? ''
];

// Load + append + save
$signatures = [];
if (file_exists($signaturesFile)) {
    $raw = file_get_contents($signaturesFile);
    if ($raw) $signatures = json_decode($raw, true) ?? [];
}
$signatures[] = $record;
$saved = file_put_contents($signaturesFile, json_encode($signatures, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

if ($saved === false) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Failed to save signature. Please try again.']);
    exit;
}

// Notification email to admin
$adminSubject = 'New Signature: ' . $fullname . ' from ' . $country;
$adminBody    = "New signature received:\n\n"
    . "Name:    {$fullname}\n"
    . "Email:   {$email}\n"
    . "Country: {$country}\n"
    . ($reason ? "Reason:  {$reason}\n" : '')
    . "\nRecorded: {$record['timestamp']}\n"
    . "ID:       {$record['id']}\n"
    . "Total:    " . count($signatures) . " signatures\n";

$adminHeaders  = "From: {$senderEmail}\r\nReply-To: {$email}\r\nContent-Type: text/plain; charset=UTF-8\r\n";
mail($recipientEmail, $adminSubject, $adminBody, $adminHeaders);

// Confirmation email to signatory
$confirmSubject = 'Your Signature — The Cognitive Age Initiative';
$confirmBody    = "Dear {$fullname},\n\n"
    . "Thank you for signing the World AI Constitution and Global Cognition Accord.\n"
    . "Your signature has been recorded in the global archive.\n\n"
    . "Signature ID: {$record['id']}\n"
    . "Recorded:     {$record['timestamp']}\n"
    . "Country:      {$country}\n\n"
    . "View the global record: https://cognitiveageinitiative.com/signatures.html\n\n"
    . "Together, we are building a future where humanity remains at the center.\n\n"
    . "The Cognitive Age Initiative\n";

$confirmHeaders = "From: {$senderEmail}\r\nContent-Type: text/plain; charset=UTF-8\r\n";
mail($email, $confirmSubject, $confirmBody, $confirmHeaders);

echo json_encode(['success' => true, 'message' => 'Signature recorded.', 'id' => $record['id']]);
