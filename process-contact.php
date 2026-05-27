<?php
/**
 * process-contact.php
 * Handles both contact.html and press.html form submissions.
 */

header('Content-Type: application/json; charset=utf-8');

$recipientEmail = 'wisdomsbrother@gmail.com';
$senderEmail    = 'noreply@cognitiveageinitiative.com';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed.']);
    exit;
}

$name         = isset($_POST['name'])         ? trim(strip_tags($_POST['name']))         : '';
$email        = isset($_POST['email'])        ? trim(strip_tags($_POST['email']))        : '';
$subject      = isset($_POST['subject'])      ? trim(strip_tags($_POST['subject']))      : '';
$message      = isset($_POST['message'])      ? trim(strip_tags($_POST['message']))      : '';
$organization = isset($_POST['organization']) ? trim(strip_tags($_POST['organization'])) : '';

if (empty($name) || empty($email) || empty($message)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Please fill in all required fields.']);
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Please enter a valid email address.']);
    exit;
}

$emailSubject = $subject ? 'New Inquiry: ' . $subject : 'New Contact Form Submission';
$emailBody    = "New inquiry from the website:\n\n"
    . "Name:    {$name}\n"
    . "Email:   {$email}\n"
    . ($subject      ? "Subject: {$subject}\n"      : '')
    . ($organization ? "Org:     {$organization}\n" : '')
    . "\nMessage:\n" . str_repeat('-', 50) . "\n{$message}\n" . str_repeat('-', 50) . "\n\n"
    . "Submitted: " . date('Y-m-d H:i:s') . "\n"
    . "IP:        " . ($_SERVER['REMOTE_ADDR'] ?? '') . "\n";

$headers  = "From: {$senderEmail}\r\nReply-To: {$email}\r\nContent-Type: text/plain; charset=UTF-8\r\n";
$mailSent = mail($recipientEmail, $emailSubject, $emailBody, $headers);

if (!$mailSent) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Failed to send message. Please try again later.']);
    exit;
}

// Confirmation to sender
$confirmBody = "Dear {$name},\n\n"
    . "Thank you for contacting the Cognitive Age Initiative.\n"
    . "We have received your inquiry and will respond as soon as possible.\n\n"
    . "The Cognitive Age Initiative\n";

$confirmHeaders = "From: {$senderEmail}\r\nContent-Type: text/plain; charset=UTF-8\r\n";
mail($email, 'We received your message — The Cognitive Age Initiative', $confirmBody, $confirmHeaders);

echo json_encode(['success' => true, 'message' => 'Message sent successfully.']);
