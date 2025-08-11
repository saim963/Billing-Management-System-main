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

// Fetch programmes data
$stmt = $conn->prepare("SELECT * FROM programme_table");
$stmt->execute();
$result = $stmt->get_result();
$programmes_rows = [];
while ($row = $result->fetch_assoc()) {
    $programmes_rows[] = $row;
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
    <title>Programmes - Admin Dashboard</title>
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
                <h4 class="mb-0"><i class="fas fa-graduation-cap mr-2"></i> Available Programmes</h4>
                <span class="badge badge-light"><?php echo count($programmes_rows); ?> Programmes</span>
            </div>
            <div class="card-body">
                <div class="d-flex justify-content-end mb-3">
                    <button class="btn btn-success" data-toggle="modal" data-target="#addCourseModal">
                        <i class="fas fa-plus"></i> Add New Programme
                    </button>
                </div>

                <div class="table-responsive">
                    <?php if (!empty($programmes_rows)): ?>
                        <table class="table table-hover table-striped">
                            <thead class="thead-light">
                                <tr>
                                    <th>Programme Title</th>
                                    <th>Course Code</th>
                                    <th>Course Title</th>
                                    <th>Incharge</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody id="courses-table-body">
                                <?php foreach ($programmes_rows as $index => $row): ?>
                                    <tr data-index="<?php echo $index; ?>" data-course_code="<?php echo htmlspecialchars($row['course_code']); ?>">
                                        <td><?php echo htmlspecialchars($row['programme_title']); ?></td>
                                        <td><?php echo htmlspecialchars($row['course_code']); ?></td>
                                        <td><?php echo htmlspecialchars($row['course_title']); ?></td>
                                        <td><?php echo htmlspecialchars($row['incharge']); ?></td>
                                        <td>
                                            <button class="btn btn-sm btn-outline-primary edit-btn"
                                                data-index="<?php echo $index; ?>"
                                                data-programme="<?php echo htmlspecialchars($row['programme_title']); ?>"
                                                data-course_code="<?php echo htmlspecialchars($row['course_code']); ?>"
                                                data-course_title="<?php echo htmlspecialchars($row['course_title']); ?>"
                                                data-incharge="<?php echo htmlspecialchars($row['incharge']); ?>">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button class="btn btn-sm btn-outline-danger delete-btn" 
                                                data-course_code="<?php echo htmlspecialchars($row['course_code']); ?>"
                                                data-programme="<?php echo htmlspecialchars($row['programme_title']); ?>">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php else: ?>
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle mr-2"></i> No programmes available.
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Add Programme Modal -->
    <div class="modal fade" id="addCourseModal" tabindex="-1" role="dialog" aria-labelledby="addCourseModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title" id="addCourseModalLabel">Add New Programme</h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form id="addCourseForm">
                        <div class="form-group">
                            <label>Programme Title</label>
                            <input type="text" class="form-control" id="add-programme-title" required>
                        </div>
                        <div class="form-group">
                            <label>Course Code</label>
                            <input type="text" class="form-control" id="add-course-code" required>
                        </div>
                        <div class="form-group">
                            <label>Course Title</label>
                            <input type="text" class="form-control" id="add-course-title" required>
                        </div>
                        <div class="form-group">
                            <label>Incharge</label>
                            <input type="text" class="form-control" id="add-incharge" required>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-success" id="save-course-btn">Save Programme</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Programme Modal -->
    <div class="modal fade" id="editCourseModal" tabindex="-1" role="dialog" aria-labelledby="editCourseModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="editCourseModalLabel">Edit Programme</h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form id="editCourseForm">
                        <input type="hidden" id="edit-course-original-code">
                        <div class="form-group">
                            <label>Programme Title</label>
                            <input type="text" class="form-control" id="edit-programme-title" required>
                        </div>
                        <div class="form-group">
                            <label>Course Code</label>
                            <input type="text" class="form-control" id="edit-course-code" required>
                        </div>
                        <div class="form-group">
                            <label>Course Title</label>
                            <input type="text" class="form-control" id="edit-course-title" required>
                        </div>
                        <div class="form-group">
                            <label>Incharge</label>
                            <input type="text" class="form-control" id="edit-incharge" required>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-primary" id="update-course-btn">Update Programme</button>
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
                    <p>Are you sure you want to delete this programme? This action cannot be undone.</p>
                    <p><strong>Programme:</strong> <span id="delete-programme-name"></span></p>
                    <input type="hidden" id="delete-course-code">
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

            // Open edit modal with programme data
            $(document).on('click', '.edit-btn', function() {
                const programme = $(this).data('programme');
                const courseCode = $(this).data('course_code');
                const courseTitle = $(this).data('course_title');
                const incharge = $(this).data('incharge');

                $('#edit-course-original-code').val(courseCode);
                $('#edit-programme-title').val(programme);
                $('#edit-course-code').val(courseCode);
                $('#edit-course-title').val(courseTitle);
                $('#edit-incharge').val(incharge);

                $('#editCourseModal').modal('show');
            });

            // Update programme
            $('#update-course-btn').click(function() {
                const originalCourseCode = $('#edit-course-original-code').val();
                const programmeTitle = $('#edit-programme-title').val();
                const courseCode = $('#edit-course-code').val();
                const courseTitle = $('#edit-course-title').val();
                const incharge = $('#edit-incharge').val();

                if (!programmeTitle || !courseCode || !courseTitle || !incharge) {
                    showToast('All fields are required', false);
                    return;
                }

                $.ajax({
                    url: 'update_programme.php',
                    type: 'POST',
                    data: {
                        original_course_code: originalCourseCode,
                        programme_title: programmeTitle,
                        course_code: courseCode,
                        course_title: courseTitle,
                        incharge: incharge
                    },
                    success: function(response) {
                        const result = JSON.parse(response);
                        if (result.success) {
                            // Update the table row
                            const row = $(`tr[data-course_code="${originalCourseCode}"]`);
                            row.find('td:eq(0)').text(programmeTitle);
                            row.find('td:eq(1)').text(courseCode);
                            row.find('td:eq(2)').text(courseTitle);
                            row.find('td:eq(3)').text(incharge);

                            // Update data attributes
                            row.attr('data-course_code', courseCode);
                            row.find('.edit-btn').data('programme', programmeTitle);
                            row.find('.edit-btn').data('course_code', courseCode);
                            row.find('.edit-btn').data('course_title', courseTitle);
                            row.find('.edit-btn').data('incharge', incharge);

                            row.find('.delete-btn').data('course_code', courseCode);
                            row.find('.delete-btn').data('programme', programmeTitle);

                            showToast('Programme updated successfully');
                            $('#editCourseModal').modal('hide');
                        } else {
                            showToast(result.message || 'Failed to update programme', false);
                        }
                    },
                    error: function() {
                        showToast('An error occurred while updating the programme', false);
                    }
                });
            });

            // Open delete confirmation modal
            $(document).on('click', '.delete-btn', function() {
                const courseCode = $(this).data('course_code');
                const programmeName = $(this).data('programme');
                $('#delete-course-code').val(courseCode);
                $('#delete-programme-name').text(programmeName);
                $('#deleteCourseModal').modal('show');
            });

            // Delete programme
            $('#confirm-delete-btn').click(function() {
                const courseCode = $('#delete-course-code').val();

                $.ajax({
                    url: 'delete_programme.php',
                    type: 'POST',
                    data: {
                        course_code: courseCode
                    },
                    success: function(response) {
                        const result = JSON.parse(response);
                        if (result.success) {
                            // Remove the row from the table
                            $(`tr[data-course_code="${courseCode}"]`).fadeOut('slow', function() {
                                $(this).remove();

                                // Update programme count in badge
                                const courseCount = $('#courses-table-body tr').length;
                                $('.badge').text(courseCount + ' Programmes');

                                // Show empty message if no programmes left
                                if (courseCount === 0) {
                                    $('.table-responsive').html(`
                                        <div class="alert alert-info">
                                            <i class="fas fa-info-circle mr-2"></i> No programmes available.
                                        </div>
                                    `);
                                }
                            });

                            showToast('Programme deleted successfully');
                            $('#deleteCourseModal').modal('hide');
                        } else {
                            showToast(result.message || 'Failed to delete programme', false);
                        }
                    },
                    error: function() {
                        showToast('An error occurred while deleting the programme', false);
                    }
                });
            });

            // Add new programme functionality
            $('#save-course-btn').click(function() {
                const programmeTitle = $('#add-programme-title').val();
                const courseCode = $('#add-course-code').val();
                const courseTitle = $('#add-course-title').val();
                const incharge = $('#add-incharge').val();

                if (!programmeTitle || !courseCode || !courseTitle || !incharge) {
                    showToast('All fields are required', false);
                    return;
                }

                $.ajax({
                    url: 'add_programme.php',
                    type: 'POST',
                    data: {
                        programme_title: programmeTitle,
                        course_code: courseCode,
                        course_title: courseTitle,
                        incharge: incharge
                    },
                    success: function(response) {
                        const result = JSON.parse(response);
                        if (result.success) {
                            // Reload the page to show the new programme
                            location.reload();
                        } else {
                            showToast(result.message || 'Failed to add programme', false);
                        }
                    },
                    error: function() {
                        showToast('An error occurred while adding the programme', false);
                    }
                });
            });
        });
    </script>
</body>

</html>