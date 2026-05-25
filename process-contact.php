<?php
// Email configuration
$recipientEmail = 'wisdomsbrother@gmail.com';
$senderEmail = 'noreply@cognitiveagecinitiative.com';

// Check if form was submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize and validate input
    $name = isset($_POST['name']) ? trim($_POST['name']) : '';
    $email = isset($_POST['email']) ? trim($_POST['email']) : '';
    $subject = isset($_POST['subject']) ? trim($_POST['subject']) : '';
    $message = isset($_POST['message']) ? trim($_POST['message']) : '';
    $organization = isset($_POST['organization']) ? trim($_POST['organization']) : '';
    
    // Basic validation
    if (empty($name) || empty($email) || empty($message)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Please fill in all required fields.']);
        exit;
    }
    
    // Validate email format
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Please enter a valid email address.']);
        exit;
    }
    
    // Prepare email content
    $emailSubject = $subject ? 'New Inquiry: ' . $subject : 'New Contact Form Submission';
    
    $emailBody = "You have received a new inquiry from the contact form:\n\n";
    $emailBody .= "Name: " . $name . "\n";
    $emailBody .= "Email: " . $email . "\n";
    
    if (!empty($subject)) {
        $emailBody .= "Subject: " . $subject . "\n";
    }
    
    if (!empty($organization)) {
        $emailBody .= "Organization: " . $organization . "\n";
    }
    
    $emailBody .= "\nMessage:\n";
    $emailBody .= str_repeat("-", 50) . "\n";
    $emailBody .= $message . "\n";
    $emailBody .= str_repeat("-", 50) . "\n\n";
    $emailBody .= "Submitted at: " . date('Y-m-d H:i:s') . "\n";
    $emailBody .= "IP Address: " . $_SERVER['REMOTE_ADDR'] . "\n";
    
    // Set headers
    $headers = "From: " . $senderEmail . "\r\n";
    $headers .= "Reply-To: " . $email . "\r\n";
    $headers .= "Content-Type: text/plain; charset=UTF-8\r\n";
    
    // Send email
    $mailSent = mail($recipientEmail, $emailSubject, $emailBody, $headers);
    
    if ($mailSent) {
        // Also send confirmation to user
        $confirmSubject = "We received your message - The Cognitive Age Initiative";
        $confirmBody = "Dear " . $name . ",\n\n";
        $confirmBody .= "Thank you for contacting the Cognitive Age Initiative.\n\n";
        $confirmBody .= "We have received your inquiry and will review it shortly.\n";
        $confirmBody .= "A member of our team will respond to you as soon as possible.\n\n";
        $confirmBody .= "Best regards,\n";
        $confirmBody .= "The Cognitive Age Initiative Team\n";
        
        $confirmHeaders = "From: " . $senderEmail . "\r\n";
        $confirmHeaders .= "Content-Type: text/plain; charset=UTF-8\r\n";
        
        mail($email, $confirmSubject, $confirmBody, $confirmHeaders);
        
        http_response_code(200);
        echo json_encode(['success' => true, 'message' => 'Message sent successfully.']);
    } else {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Failed to send message. Please try again later.']);
    }
} else {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed.']);
}
?>