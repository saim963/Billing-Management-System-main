<?php
session_start();

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

//If admin is logged in then pnly this page can be accessed else it will be redirected to admin_login
if (!isset($_SESSION['admin_loggedin']) || $_SESSION['admin_loggedin'] != true) {
    header("location: admin_login.php");
    exit;
}
include '../partials/_dbconnect.php';

// New ones
$evaluators_count = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM evaluators"))['count'];
$students_count = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM students"))['count'];
$remuneration_count = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM remuneration"))['count'];
$programmes_count = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM programme_table"))['count'];
$assignments_count = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM assignment_table"))['count'];

// Get counts for dashboard summary
// $waiting_count = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM waiting_room"))['count'];
// $courses_count = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM courses"))['count'];
// $users_count = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM personal_details"))['count'];
// $remuneration_count = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM remuneration"))['count'];

$conn->close();
?>

<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/css/bootstrap.min.css" integrity="sha384-Vkoo8x4CGsO3+Hhxv8T/Q5PaXtkKtu6ug5TOeNV6gBiFeWPGFN9MuhOf23Q9Ifjh" crossorigin="anonymous">
    <script src="https://code.jquery.com/jquery-3.4.1.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/js/bootstrap.min.js"></script>
    <title>Admin Dashboard</title>

    <style>
        .dashboard-card {
            transition: transform 0.3s, box-shadow 0.3s;
            cursor: pointer;
            margin-bottom: 20px;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        .dashboard-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.2);
        }

        .card-icon {
            font-size: 3rem;
            opacity: 0.8;
        }

        .dashboard-card .card-body {
            padding: 1.5rem;
        }

        .dashboard-header {
            padding: 15px 0;
            margin-bottom: 20px;
            border-bottom: 1px solid #eee;
        }

        .count-number {
            font-size: 2.5rem;
            font-weight: bold;
        }
    </style>
</head>

<body>
    <?php require '../partials/_nav.php' ?>
    <div class="container mt-4">
        <div class="dashboard-header">
            <h2>Admin Dashboard</h2>
            <p class="text-muted">Manage your system data</p>
        </div>

        <div class="row">
            <!-- Student Card -->
            <div class="col-md-4">
                <div class="card dashboard-card bg-primary text-white" onclick="window.location='students/student_table.php'">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h4 class="card-title">Students</h4>
                                <div class="count-number"><?= $students_count ?></div>
                                <p class="card-text">Active Students</p>
                            </div>
                            <div class="card-icon">
                                <i class="fas fa-hourglass-half"></i>
                            </div>
                        </div>
                        <button class="btn btn-light btn-sm mt-3">View Details</button>
                    </div>
                </div>
            </div>

            <!-- Programmes Card -->
            <div class="col-md-4">
                <div class="card dashboard-card bg-success text-white" onclick="window.location='courses/course_table.php'">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h4 class="card-title">Programmes</h4>
                                <div class="count-number"><?= $programmes_count ?></div>
                                <p class="card-text">Available Programmes</p>
                            </div>
                            <div class="card-icon">
                                <i class="fas fa-graduation-cap"></i>
                            </div>
                        </div>
                        <button class="btn btn-light btn-sm mt-3">View Details</button>
                    </div>
                </div>
            </div>

            <!-- Evaluators Card -->
            <div class="col-md-4">
                <div class="card dashboard-card bg-info text-white" onclick="window.location='users/user_table.php'">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h4 class="card-title">Evaluators</h4>
                                <div class="count-number"><?= $evaluators_count ?></div>
                                <p class="card-text">Registered Evaluators</p>
                            </div>
                            <div class="card-icon">
                                <i class="fas fa-users"></i>
                            </div>
                        </div>
                        <button class="btn btn-light btn-sm mt-3">View Details</button>
                    </div>
                </div>
            </div>

            <!-- Remuneration Table Card -->
            <div class="col-md-4">
                <div class="card dashboard-card bg-secondary text-white" onclick="window.location='remuneration/remuneration_review.php'">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h4 class="card-title">Remuneration</h4>
                                <div class="count-number"><?= $remuneration_count ?></div>
                                <p class="card-text">Verified for Remuneration</p>
                            </div>
                            <div class="card-icon">
                                <i class="fas fa-hourglass-half"></i>
                            </div>
                        </div>
                        <button class="btn btn-light btn-sm mt-3">View Details</button>
                    </div>
                </div>
            </div>

            <!-- Assignments Card -->
            <div class="col-md-4">
                <div class="card dashboard-card bg-warning text-white" onclick="window.location='assignments/assignment_table.php'">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h4 class="card-title">Assignments</h4>
                                <div class="count-number"><?= $assignments_count ?></div>
                                <p class="card-text">Available Assignments</p>
                            </div>
                            <div class="card-icon">
                                <i class="fas fa-tasks"></i>
                            </div>
                        </div>
                        <button class="btn btn-light btn-sm mt-3">View Details</button>
                    </div>
                </div>
            </div>

        </div>
    </div>
</body>

</html>
