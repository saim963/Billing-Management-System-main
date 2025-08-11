<?php
session_start();

// If admin is not logged in, return error
if (!isset($_SESSION['admin_loggedin']) || $_SESSION['admin_loggedin'] != true) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit;
}

include '../../partials/_dbconnect.php';

// Check if all required fields are provided
if (!isset($_POST['course']) || !isset($_POST['job']) || !isset($_POST['rate'])) {
    echo json_encode(['success' => false, 'message' => 'All fields are required']);
    exit;
}

$course = $_POST['course'];
$job = $_POST['job'];
$rate = $_POST['rate'];

// Validate inputs
if (empty($course) || empty($job) || empty($rate) || !is_numeric($rate)) {
    echo json_encode(['success' => false, 'message' => 'Invalid input data']);
    exit;
}

// Check if course already exists
$stmt = $conn->prepare("SELECT id FROM courses WHERE course = ? AND job = ? ");
$stmt->bind_param("ss", $course, $job);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    echo json_encode(['success' => false, 'message' => 'Course and Job already exists']);
    $stmt->close();
    $conn->close();
    exit;
}

// Insert new course into database
try {
    $stmt = $conn->prepare("INSERT INTO courses (course, job, rate) VALUES (?, ?, ?)");
    $stmt->bind_param("ssi", $course, $job, $rate);
    $result = $stmt->execute();
    
    if ($result) {
        // Get the ID of the newly inserted course
        $newId = $conn->insert_id;
        echo json_encode([
            'success' => true, 
            'message' => 'Course added successfully',
            'courseId' => $newId
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to add course: ' . $conn->error]);
    }
    
    $stmt->close();
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}

$conn->close();
?>