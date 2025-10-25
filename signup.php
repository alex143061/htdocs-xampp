<?php
header('Content-Type: application/json');

// Database credentials
$host = "localhost";
$dbname = "litterbox_db";
$dbuser = "root";
$dbpass = "";

// Connect to MySQL
$conn = new mysqli($host, $dbuser, $dbpass, $dbname);

// Check connection
if ($conn->connect_error) {
    echo json_encode(['success' => false, 'message' => 'Database connection failed: ' . $conn->connect_error]);
    exit;
}

// Get and sanitize POST data
$username = trim($_POST['username'] ?? '');
$email = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';
$device_code_input = trim($_POST['device_code'] ?? '');

// Validate required fields
if (!$username || !$email || !$password || !$device_code_input) {
    echo json_encode(['success' => false, 'message' => 'Missing required fields']);
    exit;
}

// Normalize device code (e.g., uppercase)
$normalized_device_code = strtoupper($device_code_input);

// Validate device code (must exist and not be used)
$checkStmt = $conn->prepare("SELECT id FROM device_codes WHERE UPPER(TRIM(device_code)) = ? AND is_used = 0");
$checkStmt->bind_param("s", $normalized_device_code);
$checkStmt->execute();
$result = $checkStmt->get_result();

if ($result->num_rows === 0) {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid or already used machine code',
        'code_you_entered' => $device_code_input
    ]);
    $checkStmt->close();
    exit;
}

// Hash the password
$hashed_password = password_hash($password, PASSWORD_DEFAULT);

// Insert user
$insertStmt = $conn->prepare("INSERT INTO users (username, email, password, device_code) VALUES (?, ?, ?, ?)");
if (!$insertStmt) {
    echo json_encode(['success' => false, 'message' => 'Prepare failed: ' . $conn->error]);
    exit;
}
$insertStmt->bind_param("ssss", $username, $email, $hashed_password, $normalized_device_code);

if ($insertStmt->execute()) {
    // Mark the device code as used
    $updateStmt = $conn->prepare("UPDATE device_codes SET is_used = 1 WHERE UPPER(TRIM(device_code)) = ?");
    $updateStmt->bind_param("s", $normalized_device_code);
    $updateStmt->execute();
    $updateStmt->close();

    echo json_encode(['success' => true, 'message' => 'Signup successful!']);
} else {
    echo json_encode(['success' => false, 'message' => 'Signup failed: ' . $insertStmt->error]);
}

$insertStmt->close();
$checkStmt->close();
$conn->close();
?>
