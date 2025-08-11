<?php
$server = "db";
$username = "admin";
$password = "password";
$database = "ignou_bill_db";

$conn = mysqli_connect($server, $username, $password, $database);
if (!$conn){
//     echo "success";
// }
// else{
    die("Error". mysqli_connect_error());
}
?>