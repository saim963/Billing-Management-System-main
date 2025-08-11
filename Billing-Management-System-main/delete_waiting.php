<?php
session_start();
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: login.php");
    exit;
}

include 'partials/_dbconnect.php';

$employ_id = (int)$_SESSION['employ_id'];
$workplace = $_POST['workplace'] ?? '';
$course = $_POST['course'] ?? '';
$date_from = $_POST['date_from'] ?? '';

try {
    $stmt = $conn->prepare("DELETE FROM waiting_room WHERE employ_id = ? AND workplace = ? AND course = ? AND date_from = ?");
    $stmt->bind_param("isss", $employ_id, $workplace, $course, $date_from);
    $stmt->execute();

    echo json_encode(['success' => true, 'message' => 'Row deleted successfully']);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

$stmt->close();
$conn->close();
?>