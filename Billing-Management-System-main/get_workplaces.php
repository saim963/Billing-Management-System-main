<?php
session_start();
include 'partials/_dbconnect.php';

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    die(json_encode(['success' => false, 'message' => 'Not logged in']));
}

$employ_id = (int)$_SESSION['employ_id'];

// Fetch distinct workplaces from waiting_room for this employ_id
$stmt_workplace = $conn->prepare("SELECT DISTINCT workplace FROM waiting_room WHERE employ_id = ?");
$stmt_workplace->bind_param("i", $employ_id);
$stmt_workplace->execute();
$result_workplace = $stmt_workplace->get_result();
$workplaces = [];
while ($row = $result_workplace->fetch_assoc()) {
    $workplaces[] = htmlspecialchars($row['workplace']);
}
$stmt_workplace->close();
$conn->close();

echo json_encode(['success' => true, 'workplaces' => $workplaces]);
