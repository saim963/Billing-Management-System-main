<?php
session_start();

error_reporting(E_ALL);
ini_set('display_errors', 1);
// Security: Regenerate session ID and set secure cookies
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: login.php");
    exit;
}

// Set secure session cookie parameters before regenerating ID
// session_set_cookie_params([
//     'secure' => isset($_SERVER['HTTPS']),
//     'httponly' => true,
//     'samesite' => 'Strict'
// ]);

session_regenerate_id(true);

// CSRF Protection
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Initialize variables
$success_message = '';
$error_message = '';


// Handle form submissions
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // CSRF Protection
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        $error_message = "Invalid CSRF token.";
    } else {
        try {
            include 'partials/_dbconnect.php';

            if (!$conn) {
                throw new Exception("Database connection failed");
            }

            // Handle assignment evaluation update
            if (isset($_POST['update_assignment'])) {
                $sno = filter_input(INPUT_POST, 'sno', FILTER_VALIDATE_INT);
                $evaluation_date = trim($_POST['evaluation_date'] ?? '');
                $status = trim($_POST['status'] ?? '');

                if (empty($status)) {
                    $error_message = "Status is required.";
                } elseif (!in_array($status, ['evaluated', 'returned'])) {
                    $error_message = "Invalid status selected.";
                } else {
                    $stmt_verify = $conn->prepare("SELECT sno FROM assignment_table WHERE sno = ? status = 'submitted'");
                    $stmt_verify->bind_param("i", $sno);
                    $stmt_verify->execute();
                    $result_verify = $stmt_verify->get_result();

                    if ($result_verify->num_rows === 0) {
                        $error_message = "Assignment not found or not authorized to evaluate.";
                    } else {
                        $stmt_update = $conn->prepare("UPDATE assignment_table SET evaluation_date = ?, status = ? WHERE sno = ?");
                        $stmt_update->bind_param("ssi", $evaluation_date, $status, $sno);

                        if ($stmt_update->execute() && $stmt_update->affected_rows > 0) {
                            $success_message = "Assignment evaluation updated successfully.";
                        } else {
                            $error_message = "Error updating assignment.";
                        }
                        $stmt_update->close();
                    }
                    $stmt_verify->close();
                }
            }

            // Handle new assignment submission
            elseif (isset($_POST['add_assignment'])) {
                $programme_title = trim($_POST['programme_title'] ?? '');
                $course_code = trim($_POST['course_code'] ?? '');
                $course_title = trim($_POST['course_title'] ?? '');
                $student_name = trim($_POST['student_name'] ?? '');
                $enrolment_number = trim($_POST['enrolment_number'] ?? '');
                $upload_portal = trim($_POST['upload_portal'] ?? '');
                $evaluator = trim($_POST['evaluator'] ?? '');
                $submitted_on = trim($_POST['submitted_on'] ?? '');
                $incharge = trim($_POST['incharge'] ?? '');
                $evaluation_date = trim($_POST['evaluation_date'] ?? '');
                $status = 'submitted';

                if (empty($programme_title) || empty($course_code) || empty($course_title) || empty($student_name) || empty($enrolment_number) || empty($evaluator) || empty($submitted_on) || empty($incharge)) {
                    $error_message = "All fields are required for assignment submission.";
                } else {
                    $stmt_add = $conn->prepare("INSERT INTO assignment_table (programme_title, course_code, course_title, enrolment_number, student_name, evaluator, submitted_on, incharge, evaluation_date, upload_portal, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                    $stmt_add->bind_param("sssssssssss", $programme_title, $course_code, $course_title, $enrolment_number, $student_name, $evaluator, $submitted_on, $incharge, $evaluation_date, $upload_portal, $status);

                    if ($stmt_add->execute()) {
                        $success_message = "Assignment submitted successfully.";
                    } else {
                        $error_message = "Error submitting assignment: " . $stmt_add->error;
                    }
                    $stmt_add->close();
                }
            }

            // Handle new programme addition
            elseif (isset($_POST['add_programme'])) {
                $programme_title = trim($_POST['programme_title'] ?? '');
                $course_code = trim($_POST['course_code'] ?? '');
                $course_title = trim($_POST['course_title'] ?? '');
                $incharge = trim($_POST['incharge'] ?? '');

                if (empty($programme_title) || empty($course_code) || empty($course_title) || empty($incharge)) {
                    $error_message = "All fields are required for programme addition.";
                } else {
                    $stmt_prog = $conn->prepare("INSERT INTO programme_table (programme_title, course_code, course_title, incharge) VALUES (?, ?, ?, ?)");
                    $stmt_prog->bind_param("ssss", $programme_title, $course_code, $course_title, $incharge);

                    if ($stmt_prog->execute()) {
                        $success_message = "Programme added successfully.";
                    } else {
                        $error_message = "Error adding programme: " . $stmt_prog->error;
                    }
                    $stmt_prog->close();
                }
            }

            // Handle new student addition
            elseif (isset($_POST['add_student'])) {
                $student_name = trim($_POST['student_name'] ?? '');
                $enrolment_no = trim($_POST['enrolment_no'] ?? '');
                $programme = trim($_POST['programme'] ?? '');

                if (empty($student_name) || empty($enrolment_no) || empty($programme)) {
                    $error_message = "All fields are required for student addition.";
                } else {
                    $stmt_student = $conn->prepare("INSERT INTO students (name, enrolment_no, programme) VALUES (?, ?, ?)");
                    $stmt_student->bind_param("sss", $student_name, $enrolment_no, $programme);

                    if ($stmt_student->execute()) {
                        $success_message = "Student added successfully.";
                    } else {
                        $error_message = "Error adding student: " . $stmt_student->error;
                    }
                    $stmt_student->close();
                }
            }

            // Handle new evaluator addition
            elseif (isset($_POST['add_evaluator'])) {
                $evaluator_name = trim($_POST['evaluator_name'] ?? '');
                $code_no = trim($_POST['code_no'] ?? '');
                $pan = trim($_POST['pan'] ?? '');
                $address = trim($_POST['address'] ?? '');
                $amount = trim($_POST['amount'] ?? '');

                if (empty($evaluator_name) || empty($code_no) || empty($pan) || empty($address) || empty($amount)) {
                    $error_message = "All fields are required for evaluator addition.";
                } else {
                    $stmt_evaluator = $conn->prepare("INSERT INTO evaluators (name, address, code, pan, amount) VALUES (?, ?, ?, ?, ?)");
                    $stmt_evaluator->bind_param("ssssi", $evaluator_name, $code_no, $pan, $address, $amount);

                    if ($stmt_evaluator->execute()) {
                        $success_message = "Evaluator added successfully.";
                    } else {
                        $error_message = "Error adding evaluator: " . $stmt_evaluator->error;
                    }
                    $stmt_evaluator->close();
                }
            }
        } catch (Exception $e) {
            error_log("Form submission error: " . $e->getMessage());
            $error_message = "An error occurred while processing your request.";
        } finally {
            if (isset($conn) && $conn) {
                $conn->close();
            }
        }
    }
}

// Fetch data for display and form options
$pending_assignments = [];
$evaluated_assignments = [];
$programmes = [];
$incharges = [];

try {
    include 'partials/_dbconnect.php';

    if (!$conn) {
        throw new Exception("Database connection failed");
    }

    // Fixed: Fetch pending assignments without evaluator filter
    $stmt_pending = $conn->prepare("SELECT * FROM assignment_table 
                                    WHERE status = 'submitted' 
                                    ORDER BY submitted_on ASC");

    if ($stmt_pending) {
        $stmt_pending->execute();
        $result_pending = $stmt_pending->get_result();

        while ($row = $result_pending->fetch_assoc()) {
            $pending_assignments[] = $row;
        }
        $stmt_pending->close();
    }

    // Fixed: Fetch evaluated assignments without evaluator filter
    $stmt_evaluated = $conn->prepare("SELECT programme_title, course_code, course_title, student_name, enrolment_number, submitted_on, evaluation_date, status
                                      FROM assignment_table 
                                      WHERE status IN ('evaluated', 'returned') 
                                      ORDER BY evaluation_date DESC");

    if ($stmt_evaluated) {
        $stmt_evaluated->execute();
        $result_evaluated = $stmt_evaluated->get_result();

        while ($row = $result_evaluated->fetch_assoc()) {
            $evaluated_assignments[] = $row;
        }
        $stmt_evaluated->close();
    }

    // Fetch programmes for dropdown
    $stmt_programmes = $conn->prepare("SELECT programme_title, course_code, course_title, incharge FROM programme_table ORDER BY programme_title");
    if ($stmt_programmes) {
        $stmt_programmes->execute();
        $result_programmes = $stmt_programmes->get_result();

        while ($row = $result_programmes->fetch_assoc()) {
            $programmes[] = $row;
            if (!in_array($row['incharge'], $incharges) && !empty($row['incharge'])) {
                $incharges[] = $row['incharge'];
            }
        }
        $stmt_programmes->close();
    }
} catch (Exception $e) {
    error_log("Database query error: " . $e->getMessage());
    $error_message = "Error loading data.";
} finally {
    if (isset($conn) && $conn) {
        $conn->close();
    }
}
?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="Assignment Management System">
    <meta name="author" content="Your Organization">

    <!-- Security Headers -->
    <meta http-equiv="X-Content-Type-Options" content="nosniff">
    <meta http-equiv="X-Frame-Options" content="DENY">
    <meta http-equiv="X-XSS-Protection" content="1; mode=block">

    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/css/bootstrap.min.css"
        integrity="sha384-Vkoo8x4CGsO3+Hhxv8T/Q5PaXtkKtu6ug5TOeNV6gBiFeWPGFN9MuhOf23Q9Ifjh" crossorigin="anonymous">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css"
        integrity="sha512-iBBXm8fW90+nuLcSKlbmrPcLa0OT92xO1BIsZ+ywDWZCvqsWgccV3gFoRBv0z+8dLJgyAHIhR35VZc2oM/gI1w==" crossorigin="anonymous">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="css/style.css">

    <title>Assignment Management System<?php if (isset($_SESSION['username'])): ?> - User: <?= htmlspecialchars($_SESSION['username'], ENT_QUOTES, 'UTF-8') ?><?php endif; ?></title>
</head>

<body>
    <?php require 'partials/_nav.php' ?>

    <div class=" container-fluid">
        <!-- Success/Error Messages -->
        <?php if (!empty($success_message)): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <?= htmlspecialchars($success_message, ENT_QUOTES, 'UTF-8') ?>
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">×</span>
                </button>
            </div>
        <?php endif; ?>

        <?php if (!empty($error_message)): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <?= htmlspecialchars($error_message, ENT_QUOTES, 'UTF-8') ?>
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">×</span>
                </button>
            </div>
        <?php endif; ?>

        <!-- Welcome Section -->
        <div class="alert alert-info" role="alert">
            <h4 class="alert-heading">Assignment Management System</h4>
            <p class="mb-0">Welcome<?php if (isset($_SESSION['username'])): ?>, User: <?= htmlspecialchars($_SESSION['username'], ENT_QUOTES, 'UTF-8') ?><?php endif; ?>. Manage assignments and add new records below.</p>
        </div>

        <!-- Add Forms Section -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0"><i class="fas fa-plus-circle"></i> Add New Records</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6 col-lg-4 mb-3">
                                <button class="btn btn-outline-primary btn-block" data-toggle="modal" data-target="#addAssignmentModal">
                                    <i class="fas fa-file-alt"></i> Add Assignment
                                </button>
                            </div>
                            <div class="col-md-6 col-lg-4 mb-3">
                                <button class="btn btn-outline-success btn-block" data-toggle="modal" data-target="#addProgrammeModal">
                                    <i class="fas fa-graduation-cap"></i> Add Programme
                                </button>
                            </div>
                            <div class="col-md-6 col-lg-4 mb-3">
                                <button class="btn btn-outline-warning btn-block" data-toggle="modal" data-target="#addStudentModal">
                                    <i class="fas fa-user-graduate"></i> Add Student
                                </button>
                            </div>
                            <div class="col-md-6 col-lg-4 mb-3">
                                <button class="btn btn-outline-warning btn-block" data-toggle="modal" data-target="#addEvaluatorModal">
                                    <i class="fas fa-user-graduate"></i> Add Evaluator
                                </button>
                            </div>

                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Pending Assignments Section -->
        <div class="card mb-4">
            <div class="card-header bg-warning text-dark">
                <h5 class="mb-0">
                    <i class="fas fa-clock"></i> Assignments Pending Evaluation
                    <span class="badge badge-dark"><?= count($pending_assignments) ?></span>
                </h5>
            </div>
            <div class="card-body">
                <?php if (!empty($pending_assignments)): ?>
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover">
                            <thead class="thead-light">
                                <tr>
                                    <th>Sno.</th>
                                    <th>Programme</th>
                                    <th>Course Title</th>
                                    <th>Course Code</th>
                                    <th>Student Name</th>
                                    <th>Enrolment Number</th>
                                    <th>Evaluator</th>
                                    <th>Incharge</th>
                                    <th>Submitted On</th>
                                    <th>Upload Portal</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($pending_assignments as $assignment): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($assignment['sno'], ENT_QUOTES, 'UTF-8') ?></td>
                                        <td><?= htmlspecialchars($assignment['programme_title'], ENT_QUOTES, 'UTF-8') ?></td>
                                        <td><?= htmlspecialchars($assignment['course_title'], ENT_QUOTES, 'UTF-8') ?></td>
                                        <td><?= htmlspecialchars($assignment['course_code'], ENT_QUOTES, 'UTF-8') ?></td>
                                        <td><?= htmlspecialchars($assignment['student_name'], ENT_QUOTES, 'UTF-8') ?></td>
                                        <td><?= htmlspecialchars($assignment['enrolment_number'], ENT_QUOTES, 'UTF-8') ?></td>
                                        <td><?= htmlspecialchars($assignment['evaluator'], ENT_QUOTES, 'UTF-8') ?></td>
                                        <td><?= htmlspecialchars($assignment['incharge'], ENT_QUOTES, 'UTF-8') ?></td>
                                        <td><?= htmlspecialchars($assignment['submitted_on'], ENT_QUOTES, 'UTF-8') ?></td>
                                        <td><?= htmlspecialchars($assignment['upload_portal'], ENT_QUOTES, 'UTF-8') ?></td>
                                        <td>
                                            <button class="btn btn-success btn-sm evaluate-btn"
                                                data-toggle="modal"
                                                data-target="#evaluateAssignmentModal"
                                                data-sno="<?= htmlspecialchars($assignment['sno'], ENT_QUOTES, 'UTF-8') ?>">
                                                <i class="fas fa-check"></i> Evaluate
                                            </button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="text-center text-muted py-4">
                        <i class="fas fa-inbox fa-3x mb-3"></i>
                        <p class="mb-0">No assignments pending evaluation.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Evaluated Assignments Section -->
        <div class="card mb-4">
            <div class="card-header bg-success text-white">
                <h5 class="mb-0">
                    <i class="fas fa-check-circle"></i> Evaluated Assignments
                    <span class="badge badge-light text-dark"><?= count($evaluated_assignments) ?></span>
                </h5>
            </div>
            <div class="card-body">
                <?php if (!empty($evaluated_assignments)): ?>
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover">
                            <thead class="thead-light">
                                <tr>
                                    <th>Programme</th>
                                    <th>Course</th>
                                    <th>Student Name</th>
                                    <th>Enrolment Number</th>
                                    <th>Submitted On</th>
                                    <th>Evaluation Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($evaluated_assignments as $assignment): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($assignment['programme_title'], ENT_QUOTES, 'UTF-8') ?></td>
                                        <td><?= htmlspecialchars($assignment['course_title'], ENT_QUOTES, 'UTF-8') ?></td>
                                        <td><?= htmlspecialchars($assignment['student_name'], ENT_QUOTES, 'UTF-8') ?></td>
                                        <td><?= htmlspecialchars($assignment['enrolment_number'], ENT_QUOTES, 'UTF-8') ?></td>
                                        <td><?= htmlspecialchars($assignment['submitted_on'], ENT_QUOTES, 'UTF-8') ?></td>
                                        <td><?= htmlspecialchars($assignment['evaluation_date'], ENT_QUOTES, 'UTF-8') ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="text-center text-muted py-4">
                        <i class="fas fa-list-alt fa-3x mb-3"></i>
                        <p class="mb-0">No evaluated assignments.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Add Assignment Modal -->
    <div class="modal fade" id="addAssignmentModal" tabindex="-1" role="dialog" aria-labelledby="addAssignmentModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addAssignmentModalLabel">
                        <i class="fas fa-file-alt"></i> Add Assignment
                    </h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">×</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form method="post" id="addAssignmentForm">
                        <input type="hidden" name="add_assignment" value="1">
                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8') ?>">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="programme_title">Programme Title <span class="text-danger">*</span></label>
                                    <input type="text" name="programme_title" class="form-control" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="course_code">Course Code <span class="text-danger">*</span></label>
                                    <input type="text" name="course_code" class="form-control" required>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="course_title">Course Title <span class="text-danger">*</span></label>
                                    <input type="text" name="course_title" class="form-control" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="student_name">Student Name <span class="text-danger">*</span></label>
                                    <input type="text" name="student_name" class="form-control" required>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="enrolment_number">Enrolment Number <span class="text-danger">*</span></label>
                                    <input type="text" name="enrolment_number" class="form-control" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="evaluator">Evaluator <span class="text-danger">*</span></label>
                                    <input type="text" name="evaluator" class="form-control" required>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="submitted_on">Submission Date <span class="text-danger">*</span></label>
                                    <input type="text" name="submitted_on" class="form-control" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="incharge">incharge <span class="text-danger">*</span></label>
                                    <input type="text" name="incharge" class="form-control" required>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="evaluation_date">Evaluation Date</label>
                                    <input type="text" name="evaluation_date" class="form-control">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="upload_portal">Upload Portal</label>
                                    <input type="text" name="upload_portal" class="form-control">
                                </div>
                            </div>
                        </div>
                        <div class="form-group text-right">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">
                                <i class="fas fa-times"></i> Cancel
                            </button>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Add Assignment
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Add Programme Modal -->
    <div class="modal fade" id="addProgrammeModal" tabindex="-1" role="dialog" aria-labelledby="addProgrammeModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addProgrammeModalLabel">
                        <i class="fas fa-graduation-cap"></i> Add Programme
                    </h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">×</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form method="post" id="addProgrammeForm">
                        <input type="hidden" name="add_programme" value="1">
                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8') ?>">
                        <div class="form-group">
                            <label for="programme_title">Programme Title <span class="text-danger">*</span></label>
                            <input type="text" name="programme_title" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label for="course_code">Course Code <span class="text-danger">*</span></label>
                            <input type="text" name="course_code" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label for="course_title">Course Title <span class="text-danger">*</span></label>
                            <input type="text" name="course_title" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label for="incharge">Incharge <span class="text-danger">*</span></label>
                            <input type="text" name="incharge" class="form-control" required>
                        </div>
                        <div class="form-group text-right">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">
                                <i class="fas fa-times"></i> Cancel
                            </button>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Add Programme
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Add Student Modal -->
    <div class="modal fade" id="addStudentModal" tabindex="-1" role="dialog" aria-labelledby="addStudentModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addStudentModalLabel">
                        <i class="fas fa-user-graduate"></i> Add Student
                    </h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">×</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form method="post" id="addStudentForm">
                        <input type="hidden" name="add_student" value="1">
                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8') ?>">
                        <div class="form-group">
                            <label for="student_name">Student Name <span class="text-danger">*</span></label>
                            <input type="text" name="student_name" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label for="enrolment_no">Enrolment Number <span class="text-danger">*</span></label>
                            <input type="text" name="enrolment_no" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label for="programme">Programme <span class="text-danger">*</span></label>
                            <input type="text" name="programme" class="form-control" required>
                        </div>
                        <div class="form-group text-right">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">
                                <i class="fas fa-times"></i> Cancel
                            </button>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Add Student
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Add Evaluator Modal -->
    <div class="modal fade" id="addEvaluatorModal" tabindex="-1" role="dialog" aria-labelledby="addEvaluatorModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addEvaluatorModalLabel">
                        <i class="fas fa-user-graduate"></i> Add Evaluator
                    </h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">×</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form method="post" id="addEvaluatorForm">
                        <input type="hidden" name="add_evaluator" value="1">
                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8') ?>">
                        <div class="form-group">
                            <label for="evaluator_name">Evaluator Name <span class="text-danger">*</span></label>
                            <input type="text" name="evaluator_name" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label for="code_no">Code No. <span class="text-danger">*</span></label>
                            <input type="text" name="code_no" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label for="pan">PAN no. <span class="text-danger">*</span></label>
                            <input type="text" name="pan" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label for="address">Address <span class="text-danger">*</span></label>
                            <input type="text" name="address" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label for="amount">Amount <span class="text-danger">*</span></label>
                            <input type="number" name="amount" class="form-control" required>
                        </div>
                        <div class="form-group text-right">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">
                                <i class="fas fa-times"></i> Cancel
                            </button>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Add Evaluator
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Evaluate Assignment Modal -->
    <div class="modal fade" id="evaluateAssignmentModal" tabindex="-1" role="dialog" aria-labelledby="evaluateAssignmentModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="evaluateAssignmentModalLabel">
                        <i class="fas fa-check-circle"></i> Evaluate Assignment
                    </h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">×</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form method="post" id="evaluateAssignmentForm">
                        <input type="hidden" name="update_assignment" value="1">
                        <input type="hidden" name="sno" id="evaluate_sno">
                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8') ?>">
                        <div class="form-group">
                            <label for="evaluation_date">Evaluation Date <span class="text-danger">*</span></label>
                            <input type="date" name="evaluation_date" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label for="status">Status <span class="text-danger">*</span></label>
                            <select name="status" class="form-control" required>
                                <option value="">Select Status...</option>
                                <option value="evaluated">Evaluated</option>
                                <option value="returned">Returned</option>
                            </select>
                        </div>
                        <div class="form-group text-right">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">
                                <i class="fas fa-times"></i> Cancel
                            </button>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Save
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS and dependencies -->
    <script src="https://code.jquery.com/jquery-3.4.1.min.js"
        integrity="sha256-CSXorXvZcTkaix6Yvo6HppcZGetbYMGWSFlBw8HfCJo="
        crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.0/dist/umd/popper.min.js"
        integrity="sha384-Q6E9RHvbIyZFJoft+2mJbHaEWldlvI9IOYy5n3zV9zzTtmI3UksdQRVvoxMfooAo"
        crossorigin="anonymous"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/js/bootstrap.min.js"
        integrity="sha384-wfSDF2E50Y2D1uUdj0O3uMBJnjuUD4Ih7YwaYd1iqfktj0Uod8GCExl3Og8ifwB6"
        crossorigin="anonymous"></script>

    <!-- Custom JavaScript -->
    <script>
        $(document).on('click', '.evaluate-btn', function() {
            var sno = $(this).data('sno');
            $('#evaluate_sno').val(sno);
        });
    </script>

</body>

</html>