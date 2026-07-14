<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    die('Not logged in');
}

echo "=== FORM SUBMISSION DEBUG ===\n";
echo "REQUEST_METHOD: " . $_SERVER['REQUEST_METHOD'] . "\n";
echo "POST data:\n";
print_r($_POST);

if (isset($_POST['skp_feedback'])) {
    echo "\nSKP Feedback data:\n";
    foreach ($_POST['skp_feedback'] as $index => $feedback) {
        echo "Index $index: '$feedback'\n";
    }
}

if (isset($_POST['perilaku_feedback'])) {
    echo "\nPerilaku Feedback data:\n";
    foreach ($_POST['perilaku_feedback'] as $key => $feedback) {
        echo "Key $key: '$feedback'\n";
    }
}
?>
