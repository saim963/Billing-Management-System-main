<?php

$showAlert = false;
$showError = false;
if($_SERVER["REQUEST_METHOD"] == "POST"){
    include 'partials/_dbconnect.php';
    $username =$_POST["username"];
    $password = $_POST["password"];
    $cpassword = $_POST["cpassword"];

    //adding personal details
    // $full_name = trim($_POST["full_name"]);
    // $designation = trim($_POST["designation"]);
    // $status = $_POST["status"];
    // $department = trim($_POST["department"]);
    // $contact_number = $_POST["contact_number"];
    // $address = trim($_POST["address"]);

    //adding payment details
    // $acc_number = trim($_POST["acc_number"]);
    // $ifsc_code = $_POST["ifsc_code"];
    // $bank_name = trim($_POST["bank_name"]);

    // $exists=false;

    // Check whether this username exists
    $existSql = "SELECT * FROM `authentication` WHERE username = '$username'";
    $result = mysqli_query($conn, $existSql);
    $numExistRows = mysqli_num_rows($result);
    if($numExistRows > 0){
        // $exists = true;
        $showError = "Account Already Exists";
    }
    else{
        // $exists = false; 
        if (is_numeric($username)) {
            $showError = "Employ ID must be a string value.";
        }
        // elseif($username <= 0 || $username > 65535){
        //     $showError = "Try a valid one!!"; 
        // } elseif (strlen($username) > 5 ) {
        //     $showError = "Enter a valid username.";
        // } 
        elseif ($password != $cpassword) {
            $showError = "Passwords do not match.";
        } else {
            // Proceed with insertion if all validations pass
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $sql1 = "INSERT INTO `authentication` (`username`, `password`) VALUES ('$username', '$hash')";
            $result1 = mysqli_query($conn, $sql1);
            // if ($result1) {
                //Contact number checking
                // if (!preg_match('/^[0-9]{10}$/', $contact_number)) {
                //     $showError = "Enter a valid 10-digit contact number!";
                // }
                //checking account number datatype
                // if (!is_numeric($acc_number)){
                //     $showError = "Account number must be a numeric value.";
                // }
                //insert into personal details
                // $sql2 = "INSERT INTO `personal_details`(`username`,`full_name`, `designation`, `status`,   `department`, `contact_number`, `address`,`acc_number`,`ifsc_code`,`bank_name`) VALUES ('$username','$full_name','$designation', '$status','$department', '$contact_number', '$address', '$acc_number', '$ifsc_code', '$bank_name')";
                // $result2 = mysqli_query($conn,$sql2);
                // if($result2){
                //     $showAlert = true;
                // }else{
                //     $showError = "Check personal details again!!" . mysqli_error($conn);
                // }
            // } else {
            //     $showError = "Something went wrong. Please try again.";
            // }
        }
    }
}  
?>

<!doctype html>
<html lang="en">
  <head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/css/bootstrap.min.css" integrity="sha384-Vkoo8x4CGsO3+Hhxv8T/Q5PaXtkKtu6ug5TOeNV6gBiFeWPGFN9MuhOf23Q9Ifjh" crossorigin="anonymous">

    <title>SignUp</title>
  </head>
  <body>
    <?php require 'partials/_nav.php' ?>
    <?php
    if($showAlert){
    echo ' <div class="alert alert-success alert-dismissible fade show" role="alert">
        <strong>Success!</strong> Your account is now created and you can login
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    </div> ';
    }
    if($showError){
    echo ' <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <strong>Error!</strong> '. $showError.'
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    </div> ';
    }
    ?>

    <div class="container my-4">
        <h1 class="text-center">Signup to IGNOU Billing System</h1>
        <form action="/signup.php" method="post">
            <div class="form-group row">
                <label for="username" class="col-sm-3 col-form-label">Username</label>
                <div class="col-sm-9">
                    <input type="text" class="form-control" id="username" name="username" required>
                </div>
            </div>
            <div class="form-group row">
                <label for="password" class="col-sm-3 col-form-label">Password</label>
                <div class="col-sm-9">
                    <input type="password" maxlength="23" class="form-control" id="password" name="password" required>
                </div>
            </div>
            <div class="form-group row">
                <label for="cpassword" class="col-sm-3 col-form-label">Confirm Password</label>
                <div class="col-sm-9">
                    <input type="password" class="form-control" id="cpassword" name="cpassword" required>
                    <small class="form-text text-muted">Make sure to type the same password</small>
                </div>
            </div>


            <!-- <div style="width: 100%; height: 1px; border-bottom: 1px solid black; margin-bottom:10px;"></div>
            <div style="font-size: 1.5em; font-weight: bold;">Personal Details</div>

            <div class="form-group row">
                <label for="full_name" class="col-sm-3 col-form-label">Full Name <small>(as per PAN card)</small></label>
                <div class="col-sm-9">
                    <input type="text" maxlength="100" class="form-control" id="full_name" name="full_name" required>
                </div>
            </div>

            <div class="form-group row">
                <label for="contact_number" class="col-sm-3 col-form-label">Contact Number</label>
                <div class="col-sm-9">
                    <input type="tel" maxlength="10" pattern="[0-9]{10}" class="form-control" id="contact_number" name="contact_number" required>
                </div>
            </div> -->

            <!-- <div class="form-group row">
                <label for="designation" class="col-sm-3 col-form-label">Designation</label>
                <div class="col-sm-3">
                    <input type="text" maxlength="50" class="form-control" id="designation" name="designation" required>
                </div>

                <label for="status" class="col-sm-2 col-form-label">Status</label>
                <div class="col-sm-4">
                    <select name="status" id="status" class="form-control" required>
                        <option value="">Select Status</option>
                        <option value="school_teacher">School Teacher</option>
                        <option value="guest_faculty">Guest Faculty</option>
                        <option value="contractual">Contractual</option>
                        <option value="phd">Phd</option>
                        <option value="permanent">Permanent</option>
                    </select>
                </div>
            </div> -->

            <!-- <div class="form-group row">
                <label for="department" class="col-sm-3 col-form-label">Department</label>
                <div class="col-sm-9">
                    <input type="text" maxlength="50" class="form-control" id="department" name="department" required>
                </div>
            </div>

            <div class="form-group row">
                <label for="address" class="col-sm-3 col-form-label">Department Address</label>
                <div class="col-sm-9">
                    <input type="text" maxlength="100" class="form-control" id="address" name="address" required>
                </div>
            </div> -->

            <!-- <div style="width: 100%; height: 1px; border-bottom: 1px solid black; margin-bottom:10px;"></div>
            <div style="font-size: 1.5em; font-weight: bold;">Payment Details</div>

            <div class="form-group row">
                <label for="acc_number" class="col-sm-3 col-form-label">Account Number</label>
                <div class="col-sm-9">
                    <input type="text" maxlength="16" class="form-control" id="acc_number" name="acc_number" required>
                </div>
            </div>

            <div class="form-group row">
                <label for="ifsc_code" class="col-sm-3 col-form-label">IFSC Code</label>
                <div class="col-sm-9">
                    <input type="text" maxlength="11" class="form-control" id="ifsc_code" name="ifsc_code" required>
                </div>
            </div>

            <div class="form-group row">
                <label for="bank_name" class="col-sm-3 col-form-label">Bank Name</label>
                <div class="col-sm-9">
                    <input type="text" maxlength="50" class="form-control" id="bank_name" name="bank_name" required>
                </div>
            </div> -->


            <div class="form-group row">
                <div class="col-sm-9 offset-sm-3">
                    <button type="submit" class="btn btn-primary">Submit</button>
                </div>
            </div>

        </form>
    </div>


    <!-- Optional JavaScript -->
    <!-- jQuery first, then Popper.js, then Bootstrap JS -->
    <script src="https://code.jquery.com/jquery-3.4.1.slim.min.js" integrity="sha384-J6qa4849blE2+poT4WnyKhv5vZF5SrPo0iEjwBvKU7imGFAV0wwj1yYfoRSJoZ+n" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.0/dist/umd/popper.min.js" integrity="sha384-Q6E9RHvbIyZFJoft+2mJbHaEWldlvI9IOYy5n3zV9zzTtmI3UksdQRVvoxMfooAo" crossorigin="anonymous"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/js/bootstrap.min.js" integrity="sha384-wfSDF2E50Y2D1uUdj0O3uMBJnjuUD4Ih7YwaYd1iqfktj0Uod8GCExl3Og8ifwB6" crossorigin="anonymous"></script>
  </body>
</html>