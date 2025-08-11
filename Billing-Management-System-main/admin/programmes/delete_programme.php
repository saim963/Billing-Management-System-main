<?php
session_start();
include '../../partials/_dbconnect.php';

if ($_POST) {
    $course_code = $_POST['course_code'];

    $stmt = $conn->prepare("DELETE FROM programme_table WHERE course_code = ?");
    $stmt->bind_param("s", $course_code);
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to delete programme']);
    }
}
?>