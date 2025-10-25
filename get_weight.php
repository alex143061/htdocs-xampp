<?php
$weight_file = __DIR__ . "/latest_weight.txt";

if (file_exists($weight_file)) {
    echo file_get_contents($weight_file);
} else {
    echo "0.0";
}
