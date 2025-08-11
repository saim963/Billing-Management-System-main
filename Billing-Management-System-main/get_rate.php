<?php
session_start();

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("HTTP/1.1 403 Forbidden");
    echo json_encode(['success' => false, 'message' => 'Not logged in']);
    exit;
}

include 'partials/_dbconnect.php';

$course = trim($_POST['course'] ?? '');
$job = trim($_POST['job'] ?? '');

try {
    $stmt = $conn->prepare("SELECT rate FROM courses WHERE course = ? AND job = ? LIMIT 1");
    $stmt->bind_param("ss", $course, $job);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {
        echo json_encode(['success' => true, 'rate' => (int)$row['rate']]);
    } else {
        echo json_encode(['success' => false, 'message' => 'No rate found', 'rate' => 0]);
    }
} catch (Exception $e) {
    error_log("Error fetching rate: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Database error', 'rate' => 0]);
}

$stmt->close();
$conn->close();
?>