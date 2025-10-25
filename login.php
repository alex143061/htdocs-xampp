<?php
header('Content-Type: application/json');

// Database credentials
$host = "localhost";
$dbname = "litterbox_db";
$dbuser = "root";
$dbpass = "";

// Connect to MySQL
$conn = new mysqli($host, $dbuser, $dbpass, $dbname);
if ($conn->connect_error) {
    echo json_encode(['success' => false, 'message' => 'Database connection failed: ' . $conn->connect_error]);
    exit;
}

// Get and sanitize POST data
$username = trim($_POST['username'] ?? '');
$password = $_POST['password'] ?? '';
$device_code_input = trim($_POST['device_code'] ?? '');

if (!$username || !$password || !$device_code_input) {
    echo json_encode(['success' => false, 'message' => 'Missing credentials.']);
    exit;
}

$normalized_device_code = strtoupper($device_code_input);

// Prepare statement to find user by username and device_code and select email
$stmt = $conn->prepare("SELECT id, username, password, device_code, email FROM users WHERE username = ? AND UPPER(TRIM(device_code)) = ?");
$stmt->bind_param("ss", $username, $normalized_device_code);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid username or device code']);
    $stmt->close();
    $conn->close();
    exit;
}

$user = $result->fetch_assoc();
$stmt->close();

// Verify the hashed password from DB against the input password
if (!password_verify($password, $user['password'])) {
    echo json_encode(['success' => false, 'message' => 'Incorrect password']);
    $conn->close();
    exit;
}

// All good, login successful
echo json_encode([
    'success' => true,
    'message' => 'Login successful',
    'user' => [
        'id' => $user['id'],
        'username' => $user['username'],
        'device_code' => $user['device_code'],
        'email' => $user['email'],  // <-- add email here
    ]
]);

$conn->close();
?>
