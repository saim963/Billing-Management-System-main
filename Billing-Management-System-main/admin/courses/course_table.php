<?php
session_start();

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// If admin is not logged in, redirect to admin_login
if (!isset($_SESSION['admin_loggedin']) || $_SESSION['admin_loggedin'] != true) {
    header("location: ../admin_login.php");
    exit;
}

include '../../partials/_dbconnect.php';

// Fetch courses data
$stmt = $conn->prepare("SELECT id, course, job, rate FROM courses");
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
        .container {
            max-width: 100%;
            width: 100%;
            margin: 0 auto;
        }
        .card {
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            margin-bottom: 30px;
        }

        .btn-back {
            margin-bottom: 15px;
        }

        .table-responsive {
            max-height: 600px;
            overflow-y: auto;
        }

        .toast {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 9999;
        }
    </style>
</head>

<body>
    <?php require '../../partials/_nav.php'; ?>

    <!-- Success/Error Toast -->
    <div class="toast" role="alert" aria-live="assertive" aria-atomic="true" data-delay="3000">
        <div class="toast-header">
            <strong class="mr-auto" id="toast-title">Notification</strong>
            <button type="button" class="ml-2 mb-1 close" data-dismiss="toast" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
        <div class="toast-body" id="toast-message"></div>
    </div>

    <div class="container mt-4">
        <a href="../admin_dashboard.php" class="btn btn-outline-secondary btn-back">
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
                            <tbody id="courses-table-body">
                                <?php foreach ($courses_rows as $row): ?>
                                    <tr data-id="<?= $row['id'] ?>">
                                        <td><?= htmlspecialchars($row['course']) ?></td>
                                        <td><?= htmlspecialchars($row['job']) ?></td>
                                        <td><?= htmlspecialchars($row['rate']) ?></td>
                                        <td>
                                            <button class="btn btn-sm btn-outline-primary edit-btn"
                                                data-id="<?= $row['id'] ?>"
                                                data-course="<?= htmlspecialchars($row['course']) ?>"
                                                data-job="<?= htmlspecialchars($row['job']) ?>"
                                                data-rate="<?= htmlspecialchars($row['rate']) ?>">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button class="btn btn-sm btn-outline-danger delete-btn" data-id="<?= $row['id'] ?>">
                                                <i class="fas fa-trash"></i>
                                            </button>
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
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form id="addCourseForm">
                        <div class="form-group">
                            <label>Course Name</label>
                            <input type="text" class="form-control" id="add-course-name" required>
                        </div>
                        <div class="form-group">
                            <label>Job Role</label>
                            <input type="text" class="form-control" id="add-job-role" required>
                        </div>
                        <div class="form-group">
                            <label>Rate (Rs)</label>
                            <input type="number" class="form-control" id="add-rate" required>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-success" id="save-course-btn">Save Course</button>
                </div>
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
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form id="editCourseForm">
                        <input type="hidden" id="edit-course-id">
                        <div class="form-group">
                            <label>Course Name</label>
                            <input type="text" class="form-control" id="edit-course-name" required>
                        </div>
                        <div class="form-group">
                            <label>Job Role</label>
                            <input type="text" class="form-control" id="edit-job-role" required>
                        </div>
                        <div class="form-group">
                            <label>Rate (Rs)</label>
                            <input type="number" class="form-control" id="edit-rate" required>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-primary" id="update-course-btn">Update Course</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div class="modal fade" id="deleteCourseModal" tabindex="-1" role="dialog" aria-labelledby="deleteCourseModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title" id="deleteCourseModalLabel">Confirm Delete</h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to delete this course? This action cannot be undone.</p>
                    <input type="hidden" id="delete-course-id">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-danger" id="confirm-delete-btn">Delete</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS and dependencies -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.0/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/js/bootstrap.min.js"></script>

    <script>
        $(document).ready(function() {
            // Show toast message
            function showToast(message, isSuccess = true) {
                $('#toast-title').text(isSuccess ? 'Success' : 'Error');
                $('#toast-message').text(message);
                $('.toast').toast('show');
                $('.toast').removeClass('bg-success bg-danger');
                $('.toast').addClass(isSuccess ? 'bg-success text-white' : 'bg-danger text-white');
            }

            // Open edit modal with course data
            $(document).on('click', '.edit-btn', function() {
                const id = $(this).data('id');
                const course = $(this).data('course');
                const job = $(this).data('job');
                const rate = $(this).data('rate');

                $('#edit-course-id').val(id);
                $('#edit-course-name').val(course);
                $('#edit-job-role').val(job);
                $('#edit-rate').val(rate);

                $('#editCourseModal').modal('show');
            });

            // Update course
            $('#update-course-btn').click(function() {
                const courseId = $('#edit-course-id').val();
                const courseName = $('#edit-course-name').val();
                const jobRole = $('#edit-job-role').val();
                const rate = $('#edit-rate').val();

                if (!courseName || !jobRole || !rate) {
                    showToast('All fields are required', false);
                    return;
                }

                $.ajax({
                    url: 'update_course.php',
                    type: 'POST',
                    data: {
                        id: courseId,
                        course: courseName,
                        job: jobRole,
                        rate: rate
                    },
                    success: function(response) {
                        const result = JSON.parse(response);
                        if (result.success) {
                            // Update the table row
                            const row = $(`tr[data-id="${courseId}"]`);
                            row.find('td:eq(0)').text(courseName);
                            row.find('td:eq(1)').text(jobRole);
                            row.find('td:eq(2)').text(rate);

                            // Update the edit button data attributes
                            row.find('.edit-btn').data('course', courseName);
                            row.find('.edit-btn').data('job', jobRole);
                            row.find('.edit-btn').data('rate', rate);

                            showToast('Course updated successfully');
                            $('#editCourseModal').modal('hide');
                        } else {
                            showToast(result.message || 'Failed to update course', false);
                        }
                    },
                    error: function() {
                        showToast('An error occurred while updating the course', false);
                    }
                });
            });

            // Open delete confirmation modal
            $(document).on('click', '.delete-btn', function() {
                const courseId = $(this).data('id');
                $('#delete-course-id').val(courseId);
                $('#deleteCourseModal').modal('show');
            });

            // Delete course
            $('#confirm-delete-btn').click(function() {
                const courseId = $('#delete-course-id').val();

                $.ajax({
                    url: 'delete_course.php',
                    type: 'POST',
                    data: {
                        id: courseId
                    },
                    success: function(response) {
                        const result = JSON.parse(response);
                        if (result.success) {
                            // Remove the row from the table
                            $(`tr[data-id="${courseId}"]`).fadeOut('slow', function() {
                                $(this).remove();

                                // Update course count in badge
                                const courseCount = $('#courses-table-body tr').length;
                                $('.badge').text(courseCount + ' Courses');

                                // Show empty message if no courses left
                                if (courseCount === 0) {
                                    $('.table-responsive').html(`
                                        <div class="alert alert-info">
                                            <i class="fas fa-info-circle mr-2"></i> No courses available.
                                        </div>
                                    `);
                                }
                            });

                            showToast('Course deleted successfully');
                            $('#deleteCourseModal').modal('hide');
                        } else {
                            showToast(result.message || 'Failed to delete course', false);
                        }
                    },
                    error: function() {
                        showToast('An error occurred while deleting the course', false);
                    }
                });
            });

            // Add new course functionality (already in your code, but updated to work with the UI)
            $('#save-course-btn').click(function() {
                const courseName = $('#add-course-name').val();
                const jobRole = $('#add-job-role').val();
                const rate = $('#add-rate').val();

                if (!courseName || !jobRole || !rate) {
                    showToast('All fields are required', false);
                    return;
                }

                $.ajax({
                    url: 'add_course.php',
                    type: 'POST',
                    data: {
                        course: courseName,
                        job: jobRole,
                        rate: rate
                    },
                    success: function(response) {
                        const result = JSON.parse(response);
                        if (result.success) {
                            // Reload the page to show the new course
                            location.reload();
                        } else {
                            showToast(result.message || 'Failed to add course', false);
                        }
                    },
                    error: function() {
                        showToast('An error occurred while adding the course', false);
                    }
                });
            });
        });
    </script>
</body>

</html>