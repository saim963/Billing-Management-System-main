<?php
session_start();
include '../../partials/_dbconnect.php';

if ($_POST) {
    $programme_title = $_POST['programme_title'];
    $course_code = $_POST['course_code'];
    $course_title = $_POST['course_title'];
    $incharge = $_POST['incharge'];

    $stmt = $conn->prepare("INSERT INTO programme_table (programme_title, course_code, course_title, incharge) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssss", $programme_title, $course_code, $course_title, $incharge);
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to add programme']);
    }
}
?>