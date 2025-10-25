<?php
$weight_file = __DIR__ . "/latest_weight.txt"; // ✅ Correct constant

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['weight'])) {
    $weight = $_POST['weight'];
    file_put_contents($weight_file, $weight);
    echo "Weight received: " . $weight . " kg";
} else {
    http_response_code(400);
    echo "Invalid request. Please send weight using POST.";
}
?>
