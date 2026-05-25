<?php
// Email configuration
$recipientEmail = 'awakenedwatchman@gmail.com';
$senderEmail = 'noreply@cognitiveagecinitiative.com';
$signaturesFile = 'data/signatures.json';

// Create data directory if it doesn't exist
if (!is_dir('data')) {
    mkdir('data', 0755, true);
}

// Check if form was submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize and validate input
    $fullname = isset($_POST['fullname']) ? trim($_POST['fullname']) : '';
    $email = isset($_POST['email']) ? trim($_POST['email']) : '';
    $country = isset($_POST['country']) ? trim($_POST['country']) : '';
    $reason = isset($_POST['reason']) ? trim($_POST['reason']) : '';
    
    // Basic validation
    if (empty($fullname) || empty($email) || empty($country)) {
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
    
    // Create signature record
    $signatureRecord = [
        'id' => uniqid(),
        'fullname' => $fullname,
        'email' => $email,
        'country' => $country,
        'reason' => $reason,
        'timestamp' => date('Y-m-d H:i:s'),
        'date' => date('Y-m-d'),
        'ip' => $_SERVER['REMOTE_ADDR']
    ];
    
    // Load existing signatures
    $signatures = [];
    if (file_exists($signaturesFile)) {
        $fileContent = file_get_contents($signaturesFile);
        if ($fileContent) {
            $signatures = json_decode($fileContent, true) ?? [];
        }
    }
    
    // Add new signature
    $signatures[] = $signatureRecord;
    
    // Save signatures to file
    $saveSuccess = file_put_contents($signaturesFile, json_encode($signatures, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    
    if ($saveSuccess !== false) {
        // Send email notification
        $emailSubject = 'New Signature: ' . $fullname . ' from ' . $country;
        
        $emailBody = "A new signature has been recorded:\n\n";
        $emailBody .= "Name: " . $fullname . "\n";
        $emailBody .= "Email: " . $email . "\n";
        $emailBody .= "Country: " . $country . "\n";
        
        if (!empty($reason)) {
            $emailBody .= "Reason: " . $reason . "\n";
        }
        
        $emailBody .= "\nRecorded at: " . $signatureRecord['timestamp'] . "\n";
        $emailBody .= "IP Address: " . $_SERVER['REMOTE_ADDR'] . "\n";
        $emailBody .= "Signature ID: " . $signatureRecord['id'] . "\n";
        $emailBody .= "\nTotal signatures recorded: " . count($signatures) . "\n";
        
        // Set headers
        $headers = "From: " . $senderEmail . "\r\n";
        $headers .= "Reply-To: " . $email . "\r\n";
        $headers .= "Content-Type: text/plain; charset=UTF-8\r\n";
        
        // Send email
        $mailSent = mail($recipientEmail, $emailSubject, $emailBody, $headers);
        
        // Send confirmation to signatory
        $confirmSubject = "Your Signature Recorded - The Cognitive Age Initiative";
        $confirmBody = "Dear " . $fullname . ",\n\n";
        $confirmBody .= "Thank you for signing the World AI Constitution and Global Cognition Accord.\n\n";
        $confirmBody .= "Your signature has been recorded and added to our global archive.\n";
        $confirmBody .= "You are now part of a growing movement of individuals standing up for human dignity in the age of intelligence.\n\n";
        $confirmBody .= "Signature Details:\n";
        $confirmBody .= "- Signature ID: " . $signatureRecord['id'] . "\n";
        $confirmBody .= "- Recorded: " . $signatureRecord['timestamp'] . "\n";
        $confirmBody .= "- Country: " . $country . "\n\n";
        $confirmBody .= "Visit our signatures page to see the global record: https://cognitiveagecinitiative.com/signatures.html\n\n";
        $confirmBody .= "Together, we are building a future where humanity remains at the center.\n\n";
        $confirmBody .= "Best regards,\n";
        $confirmBody .= "The Cognitive Age Initiative Team\n";
        
        $confirmHeaders = "From: " . $senderEmail . "\r\n";
        $confirmHeaders .= "Content-Type: text/plain; charset=UTF-8\r\n";
        
        mail($email, $confirmSubject, $confirmBody, $confirmHeaders);
        
        http_response_code(200);
        echo json_encode(['success' => true, 'message' => 'Signature recorded successfully.', 'id' => $signatureRecord['id']]);
    } else {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Failed to record signature. Please try again later.']);
    }
} else {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed.']);
}
?>
