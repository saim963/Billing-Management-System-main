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

// Fetch users data
$stmt = $conn->prepare("SELECT * FROM remuneration");
$stmt->execute();
$result = $stmt->get_result();
$users_rows = [];
while ($row = $result->fetch_assoc()) {
    $users_rows[] = $row;
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
    <title>Remuneration - Admin Dashboard</title>
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

        .user-status {
            width: 10px;
            height: 10px;
            border-radius: 50%;
            display: inline-block;
            margin-right: 5px;
        }

        .status-active {
            background-color: #28a745;
        }

        .status-inactive {
            background-color: #dc3545;
        }
    </style>
</head>

<body>
    <?php require '../../partials/_nav.php'; ?>

    <div class="container mt-4">
        <a href="../admin_dashboard.php" class="btn btn-outline-secondary btn-back">
            <i class="fas fa-arrow-left"></i> Back to Dashboard
        </a>

        <div class="card">
            <div class="card-header bg-info text-white d-flex justify-content-between align-items-center">
                <h4 class="mb-0"><i class="fas fa-users mr-2"></i>Remuneration List</h4>
                <span class="badge badge-light"><?= count($users_rows) ?> Remuneration</span>
            </div>
            <div class="card-body">
                <div class="input-group mb-3">
                    <div class="input-group-prepend">
                        <span class="input-group-text"><i class="fas fa-search"></i></span>
                    </div>
                    <input type="text" class="form-control" id="userSearchInput" placeholder="Search users...">
                </div>

                <div class="table-responsive">
                    <?php if (!empty($users_rows)): ?>
                        <table class="table table-hover table-striped" id="usersTable">
                            <thead class="thead-light">
                                <tr>
                                    <th>Serial No.</th>
                                    <th>Evaluator</th>
                                    <th>Programme</th>
                                    <th>Course</th>
                                    <th>Address</th>
                                    <th>PAN</th> 
                                    <th>Courses</th> 
                                    <th>Assignments</th> 
                                    <th>Total Assignments</th> 
                                    <th>Rate</th> 
                                    <th>Amount</th> 
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($users_rows as $row): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($row['sno']) ?></td>
                                        <td><?= htmlspecialchars($row['evaluator']) ?></td>
                                        <td><?= htmlspecialchars($row['programme']) ?></td>
                                        <td><?= htmlspecialchars($row['course']) ?></td>
                                        <td><?= htmlspecialchars($row['address']) ?></td>
                                        <td><?= htmlspecialchars($row['pan']) ?></td>
                                        <td><?= htmlspecialchars($row['courses_no']) ?></td>
                                        <td><?= htmlspecialchars($row['assignments_no']) ?></td>
                                        <td><?= htmlspecialchars($row['total_ass']) ?></td>
                                        <td><?= htmlspecialchars($row['rate']) ?></td>
                                        <td><?= htmlspecialchars($row['amount']) ?></td>
                                        <!-- <td>
                                            <span class="user-status status-active"></span>
                                            Active
                                        </td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                <button class="btn btn-sm btn-outline-info" title="View Details">
                                                    <i class="fas fa-eye"></i>
                                                </button>
                                                <button class="btn btn-sm btn-outline-warning" title="Reset Password">
                                                    <i class="fas fa-key"></i>
                                                </button>
                                                <button class="btn btn-sm btn-outline-danger" title="Delete User">
                                                    <i class="fas fa-user-times"></i>
                                                </button>
                                            </div>
                                        </td> -->
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php else: ?>
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle mr-2"></i> No users registered.
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS and dependencies -->
    <script src="https://code.jquery.com/jquery-3.4.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.0/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/js/bootstrap.min.js"></script>

    <script>
        // Simple search functionality
        $(document).ready(function() {
            $("#userSearchInput").on("keyup", function() {
                var value = $(this).val().toLowerCase();
                $("#usersTable tbody tr").filter(function() {
                    $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1)
                });
            });
        });
    </script>
</body>

</html>