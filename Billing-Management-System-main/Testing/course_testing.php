<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include '../partials/_dbconnect.php';

// Handle Add Course
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_course'])) {
    $course = $_POST['course'] ?? '';
    $job = $_POST['job'] ?? '';
    $rate = $_POST['rate'] ?? 0;

    $stmt = $conn->prepare("INSERT INTO courses (course, job, rate) VALUES (?, ?, ?)");
    $stmt->bind_param("ssi", $course, $job, $rate);
    if ($stmt->execute()) {
        header("location: courses.php"); // Refresh page
        exit;
    } else {
        $showError = "Failed to add course: " . $stmt->error;
    }
    $stmt->close();
}

// Handle Edit Course
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['edit_course'])) {
    $course_id = $_POST['course_id'] ?? '';
    $course = $_POST['course'] ?? '';
    $job = $_POST['job'] ?? '';
    $rate = $_POST['rate'] ?? 0;

    $stmt = $conn->prepare("UPDATE courses SET course = ?, job = ?, rate = ? WHERE course = ? AND job = ?");
    $stmt->bind_param("ssiss", $course, $job, $rate, $course_id, $job_id); // Using original course and job as identifiers
    if ($stmt->execute()) {
        header("location: courses.php");
        exit;
    } else {
        $showError = "Failed to update course: " . $stmt->error;
    }
    $stmt->close();
}

// Handle Delete Course
if (isset($_GET['delete_course']) && isset($_GET['delete_job'])) {
    $course = $_GET['delete_course'];
    $job = $_GET['delete_job'];

    $stmt = $conn->prepare("DELETE FROM courses WHERE course = ? AND job = ?");
    $stmt->bind_param("ss", $course, $job);
    if ($stmt->execute()) {
        header("location: courses.php");
        exit;
    } else {
        $showError = "Failed to delete course: " . $stmt->error;
    }
    $stmt->close();
}

// Fetch courses data
$stmt = $conn->prepare("SELECT course, job, rate FROM courses");
$stmt->execute();
$result = $stmt->get_result();
$courses_rows = [];
while ($row = $result->fetch_assoc()) {
    $courses_rows[] = $row;
}

$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <title>Courses - Admin Dashboard</title>
    <style>
        .card {
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
            margin-bottom: 30px;
        }
        .btn-back {
            margin-bottom: 15px;
        }
        .table-responsive {
            max-height: 600px;
            overflow-y: auto;
        }
    </style>
</head>
<body>
    <?php require '../partials/_nav.php'; ?>
    <div class="container mt-4">
        <a href="admin_panel.php" class="btn btn-outline-secondary btn-back">
            <i class="fas fa-arrow-left"></i> Back to Dashboard
        </a>
        
        <div class="card">
            <div class="card-header bg-success text-white d-flex justify-content-between align-items-center">
                <h4 class="mb-0"><i class="fas fa-graduation-cap mr-2"></i> Available Courses</h4>
                <span class="badge badge-light"><?= count($courses_rows) ?> Courses</span>
            </div>
            <div class="card-body">
                <div class="d-flex justify-content-end mb-3">
                    <button class="btn btn-success" data-toggle="modal" data-target="#addCourseModal">
                        <i class="fas fa-plus"></i> Add New Course
                    </button>
                </div>
                
                <div class="table-responsive">
                    <?php if (!empty($courses_rows)): ?>
                        <table class="table table-hover table-striped">
                            <thead class="thead-light">
                                <tr>
                                    <th>Course Name</th>
                                    <th>Job Role</th>
                                    <th>Rate (Rs)</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($courses_rows as $row): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($row['course']) ?></td>
                                        <td><?= htmlspecialchars($row['job']) ?></td>
                                        <td><?= htmlspecialchars($row['rate']) ?></td>
                                        <td>
                                            <button class="btn btn-sm btn-outline-primary edit-btn" 
                                                    data-toggle="modal" 
                                                    data-target="#editCourseModal"
                                                    data-course="<?= htmlspecialchars($row['course']) ?>"
                                                    data-job="<?= htmlspecialchars($row['job']) ?>"
                                                    data-rate="<?= htmlspecialchars($row['rate']) ?>">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <a href="?delete_course=<?= urlencode($row['course']) ?>&delete_job=<?= urlencode($row['job']) ?>" 
                                               class="btn btn-sm btn-outline-danger" 
                                               onclick="return confirm('Are you sure you want to delete this course?');">
                                                <i class="fas fa-trash"></i>
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php else: ?>
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle mr-2"></i> No courses available.
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Add Course Modal -->
    <div class="modal fade" id="addCourseModal" tabindex="-1" role="dialog" aria-labelledby="addCourseModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title" id="addCourseModalLabel">Add New Course</h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">×</span>
                    </button>
                </div>
                <form method="post" action="courses.php">
                    <div class="modal-body">
                        <input type="hidden" name="add_course" value="1">
                        <div class="form-group">
                            <label>Course Name</label>
                            <input type="text" class="form-control" name="course" required>
                        </div>
                        <div class="form-group">
                            <label>Job Role</label>
                            <input type="text" class="form-control" name="job" required>
                        </div>
                        <div class="form-group">
                            <label>Rate (Rs)</label>
                            <input type="number" class="form-control" name="rate" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-success">Save Course</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit Course Modal -->
    <div class="modal fade" id="editCourseModal" tabindex="-1" role="dialog" aria-labelledby="editCourseModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="editCourseModalLabel">Edit Course</h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">×</span>
                    </button>
                </div>
                <form method="post" action="courses.php">
                    <div class="modal-body">
                        <input type="hidden" name="edit_course" value="1">
                        <input type="hidden" name="course_id" id="edit_course_id">
                        <input type="hidden" name="job_id" id="edit_job_id">
                        <div class="form-group">
                            <label>Course Name</label>
                            <input type="text" class="form-control" name="course" id="edit_course" required>
                        </div>
                        <div class="form-group">
                            <label>Job Role</label>
                            <input type="text" class="form-control" name="job" id="edit_job" required>
                        </div>
                        <div class="form-group">
                            <label>Rate (Rs)</label>
                            <input type="number" class="form-control" name="rate" id="edit_rate" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Update Course</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        $(document).ready(function() {
            // Populate Edit Modal with data
            $('.edit-btn').click(function() {
                var course = $(this).data('course');
                var job = $(this).data('job');
                var rate = $(this).data('rate');

                $('#edit_course_id').val(course);
                $('#edit_course').val(course);
                $('#edit_job_id').val(job);
                $('#edit_job').val(job);
                $('#edit_rate').val(rate);
            });
        });
    </script>
</body>
</html>