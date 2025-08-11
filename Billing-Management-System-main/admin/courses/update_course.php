<?php
session_start();

// If admin is not logged in, return error
if (!isset($_SESSION['admin_loggedin']) || $_SESSION['admin_loggedin'] != true) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit;
}

include '../../partials/_dbconnect.php';

// Check if all required fields are provided
if (!isset($_POST['id']) || !isset($_POST['course']) || !isset($_POST['job']) || !isset($_POST['rate'])) {
    echo json_encode(['success' => false, 'message' => 'All fields are required']);
    exit;
}

$id = $_POST['id'];
$course = $_POST['course'];
$job = $_POST['job'];
$rate = $_POST['rate'];

// Validate inputs
if (empty($course) || empty($job) || empty($rate) || !is_numeric($rate)) {
    echo json_encode(['success' => false, 'message' => 'Invalid input data']);
    exit;
}

// Update course in database
try {
    $stmt = $conn->prepare("UPDATE courses SET course = ?, job = ?, rate = ? WHERE id = ?");
    $stmt->bind_param("ssii", $course, $job, $rate, $id);
    $result = $stmt->execute();
    
    if ($result) {
        echo json_encode(['success' => true, 'message' => 'Course updated successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to update course: ' . $conn->error]);
    }
    
    $stmt->close();
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}

$conn->close();
?>