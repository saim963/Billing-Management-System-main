<?php
session_start();

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

//If admin is logged in then only this page can be accessed else it will be redirected to admin_login
if (!isset($_SESSION['admin_loggedin']) || $_SESSION['admin_loggedin'] != true) {
    header("location: ../admin_login.php");
    exit;
}

include '../../partials/_dbconnect.php';

// Handle remark submission
if(isset($_POST['submit_remark'])) {
    $sno = $_POST['sno'];
    $remark = $_POST['remark'];
    
    $stmt = $conn->prepare("UPDATE waiting_room SET remark = ? WHERE sno = ?");
    $stmt->bind_param("si", $remark, $sno);
    
    if($stmt->execute()) {
        $success_message = "Remark added successfully!";
    } else {
        $error_message = "Failed to add remark: " . $conn->error;
    }
    $stmt->close();
}

// Handle approve action
if(isset($_POST['approve_entry'])) {
    $sno = $_POST['sno']; // Change this to sno since we're using sno in the form
    
    // First retrieve the data from waiting_room
    $stmt = $conn->prepare("SELECT employ_id, full_name, workplace, course, date_from, date_to, job, candidates, amount FROM waiting_room WHERE sno = ?");
    $stmt->bind_param("i", $sno); // Using integer parameter for sno
    $stmt->execute();
    $result = $stmt->get_result();
    
    if($row = $result->fetch_assoc()) {
        // Insert the data into remuneration table
        $insert_stmt = $conn->prepare("INSERT INTO remuneration (employ_id, full_name, workplace, course, date_from, date_to, job, candidates, amount) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $insert_stmt->bind_param("issssssii", 
            $row['employ_id'],
            $row['full_name'],
            $row['workplace'], 
            $row['course'], 
            $row['date_from'], 
            $row['date_to'], 
            $row['job'], 
            $row['candidates'], 
            $row['amount']
        );
        
        if($insert_stmt->execute()) {
            // Delete the entry from waiting_room
            $delete_stmt = $conn->prepare("DELETE FROM waiting_room WHERE sno = ?");
            $delete_stmt->bind_param("i", $sno);
            
            if($delete_stmt->execute()) {
                $success_message = "Entry approved and moved to remuneration table!";
            } else {
                $error_message = "Failed to delete from waiting room: " . $conn->error;
            }
            $delete_stmt->close();
        } else {
            $error_message = "Failed to insert into remuneration table: " . $conn->error;
        }
        $insert_stmt->close();
    } else {
        $error_message = "Entry not found!";
    }
    $stmt->close();
}

// Fetch waiting_room entries
$stmt = $conn->prepare("SELECT sno, employ_id, workplace, course, date_from, date_to, job, candidates, amount, remark FROM waiting_room");
$stmt->execute();
$result = $stmt->get_result();
$waiting_room_rows = [];
while ($row = $result->fetch_assoc()) {
    $course = $row['course'];
    $job = $row['job'];
    $sql_rate = "SELECT rate FROM courses WHERE course='$course' AND job='$job' LIMIT 1";
    $result_rate = mysqli_query($conn, $sql_rate);
    $rate = $result_rate && ($rate_row = mysqli_fetch_assoc($result_rate)) ? (int)$rate_row['rate'] : 0;

    $waiting_room_rows[] = [
        'sno' => $row['sno'],
        'employ_id' => $row['employ_id'],
        'workplace' => $row['workplace'],
        'course' => $row['course'],
        'date_from' => $row['date_from'],
        'date_to' => $row['date_to'],
        'job' => $row['job'],
        'candidates' => $row['candidates'],
        'rate' => $rate,
        'amount' => $row['amount'],
        'remark' => $row['remark']
    ];
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
    <title>Waiting Room - Admin Dashboard</title>
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
        
        .btn-remark {
            margin-right: 5px;
        }
        
        .remark-text {
            max-width: 150px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }
    </style>
</head>

<body>
    <?php require '../../partials/_nav.php'; ?>

    <div class="container mt-4">
        <?php if(isset($success_message)): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <?= $success_message ?>
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        <?php endif; ?>
        
        <?php if(isset($error_message)): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <?= $error_message ?>
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        <?php endif; ?>

        <a href="../admin_dashboard.php" class="btn btn-outline-secondary btn-back">
            <i class="fas fa-arrow-left"></i> Back to Dashboard
        </a>

        <div class="card">
            <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                <h4 class="mb-0"><i class="fas fa-hourglass-half mr-2"></i> Waiting Room Entries</h4>
                <span class="badge badge-light"><?= count($waiting_room_rows) ?> Entries</span>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <?php if (!empty($waiting_room_rows)): ?>
                        <table class="table table-hover table-striped">
                            <thead class="thead-light">
                                <tr>
                                    <th>S.No.</th>
                                    <th>Employ ID</th>
                                    <th>Workplace</th>
                                    <th>Course</th>
                                    <th>Date From</th>
                                    <th>Date To</th>
                                    <th>Job</th>
                                    <th>Candidates</th>
                                    <th>Rate (Rs)</th>
                                    <th>Amount (Rs)</th>
                                    <th>Remark</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($waiting_room_rows as $row): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($row['sno']) ?></td>
                                        <td><?= htmlspecialchars($row['employ_id']) ?></td>
                                        <td><?= htmlspecialchars($row['workplace']) ?></td>
                                        <td><?= htmlspecialchars($row['course']) ?></td>
                                        <td><?= htmlspecialchars($row['date_from']) ?></td>
                                        <td><?= htmlspecialchars($row['date_to']) ?></td>
                                        <td><?= htmlspecialchars($row['job']) ?></td>
                                        <td><?= htmlspecialchars($row['candidates']) ?></td>
                                        <td><?= htmlspecialchars($row['rate']) ?></td>
                                        <td><?= htmlspecialchars($row['amount']) ?></td>
                                        <td>
                                            <div class="remark-text">
                                                <?= !empty($row['remark']) ? htmlspecialchars($row['remark']) : '<em>No remark</em>' ?>
                                            </div>
                                            <button type="button" class="btn btn-sm btn-info btn-remark" data-toggle="modal" data-target="#remarkModal<?= $row['sno'] ?>">
                                                <i class="fas fa-comment"></i> <?= !empty($row['remark']) ? 'Edit' : 'Add' ?>
                                            </button>
                                        </td>
                                        <td>
                                            <form method="post" onsubmit="return confirm('Are you sure you want to approve this entry? It will be moved to the remuneration table.');">
                                                <input type="hidden" name="sno" value="<?= htmlspecialchars($row['sno']) ?>">
                                                <button type="submit" name="approve_entry" class="btn btn-sm btn-success">
                                                    <i class="fas fa-check"></i> Approve
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                    
                                    <!-- Remark Modal for each row -->
                                    <div class="modal fade" id="remarkModal<?= $row['sno'] ?>" tabindex="-1" role="dialog" aria-labelledby="remarkModalLabel<?= $row['sno'] ?>" aria-hidden="true">
                                        <div class="modal-dialog" role="document">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title" id="remarkModalLabel<?= $row['sno'] ?>">
                                                        <?= !empty($row['remark']) ? 'Edit Remark' : 'Add Remark' ?> - Serial No: <?= htmlspecialchars($row['sno']) ?>
                                                    </h5>
                                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                        <span aria-hidden="true">&times;</span>
                                                    </button>
                                                </div>
                                                <form method="post">
                                                    <div class="modal-body">
                                                        <input type="hidden" name="sno" value="<?= htmlspecialchars($row['sno']) ?>">
                                                        <div class="form-group">
                                                            <label for="remark<?= $row['sno'] ?>">Remark:</label>
                                                            <textarea class="form-control" id="remark<?= $row['sno'] ?>" name="remark" rows="4"><?= htmlspecialchars($row['remark'] ?? '') ?></textarea>
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                                                        <button type="submit" name="submit_remark" class="btn btn-primary">Save Remark</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php else: ?>
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle mr-2"></i> No entries in waiting room yet.
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
</body>

</html>