<?php
// session_start();
// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

header('Content-Type: application/json');

// if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] != true || !isset($_SESSION['employ_id'])) {
//     echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
//     exit;
// }

// if ($_SERVER["REQUEST_METHOD"] != "POST" || !isset($_POST['edit_waiting_room'])) {
//     echo json_encode(['success' => false, 'message' => 'Invalid request']);
//     exit;
// }

include '../partials/_dbconnect.php';

$employ_id = 96;
// $employ_id = $_SESSION['employ_id'];
$original_workplace = $_POST['original_workplace'] ?? '';
$original_course = $_POST['original_course'] ?? '';
$original_job = $_POST['original_job'] ?? '';
$workplace = $_POST['workplace'] ?? '';
$course = $_POST['course'] ?? '';
$date_from = $_POST['date_from'] ?? '';
$date_to = $_POST['date_to'] ?? '';
$job = $_POST['job'] ?? '';
$candidates = $_POST['candidates'] ?? 0;
$amount = $_POST['amount'] ?? 0;

// Validate inputs
if (empty($workplace) || empty($course) || empty($date_from) || empty($date_to) || empty($job) || $candidates < 0 || $amount < 0) {
    echo json_encode(['success' => false, 'message' => 'All fields are required and must be valid']);
    exit;
}

// Validate dates
if (strtotime($date_to) < strtotime($date_from)) {
    echo json_encode(['success' => false, 'message' => 'End date must be after start date']);
    exit;
}

$stmt = $conn->prepare("UPDATE waiting_room SET workplace = ?, course = ?, date_from = ?, date_to = ?, job = ?, candidates = ?, amount = ? WHERE employ_id = ? AND workplace = ? AND course = ? AND job = ?");
$stmt->bind_param("sssssiissss", $workplace, $course, $date_from, $date_to, $job, $candidates, $amount, $employ_id, $original_workplace, $original_course, $original_job);
if ($stmt->execute()) {
    if ($stmt->affected_rows > 0) {
        echo json_encode(['success' => true, 'message' => 'Entry updated successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => 'No matching entry found']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to update entry: ' . $stmt->error]);
}
$stmt->close();
$conn->close();
?>