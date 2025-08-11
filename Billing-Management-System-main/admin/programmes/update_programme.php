<?php
session_start();
include '../../partials/_dbconnect.php';

if ($_POST) {
    $original_course_code = $_POST['original_course_code'];
    $programme_title = $_POST['programme_title'];
    $course_code = $_POST['course_code'];
    $course_title = $_POST['course_title'];
    $incharge = $_POST['incharge'];

    $stmt = $conn->prepare("UPDATE programme_table SET programme_title = ?, course_code = ?, course_title = ?, incharge = ? WHERE course_code = ?");
    $stmt->bind_param("sssss", $programme_title, $course_code, $course_title, $incharge, $original_course_code);
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to update programme']);
    }
}
?>