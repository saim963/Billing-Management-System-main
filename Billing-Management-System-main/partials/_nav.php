<?php

// session_start();

$isUser = isset($_SESSION['loggedin']) && $_SESSION['loggedin']===true;
$isAdmin = isset($_SESSION['admin_loggedin']) && $_SESSION['admin_loggedin'] === true;

if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] == true) {
  $loggedin = true;
} else {
  $loggedin = false;
}

echo '<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
  <a class="navbar-brand" href="/index.php">IGNOU Billing Management System</a>
  <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
    <span class="navbar-toggler-icon"></span>
  </button>

  <div class="collapse navbar-collapse" id="navbarSupportedContent">
    <ul class="navbar-nav mr-auto">
      <li class="nav-item active">
        <a class="nav-link" href="/welcome.php">Home <span class="sr-only">(current)</span></a>
      </li>';

if ($isUser) {
  echo '<li class="nav-item">
        <a class="nav-link" href="/logout.php">Logout</a>
      </li>';
}
elseif ($isAdmin) {
  echo '<li class="nav-item active">
        <a class="nav-link" href="/admin/admin_dashboard.php">Admin Dashboard</a>
      </li>';
}else{
  // No one is logged in
  echo '<li class="nav-item">
          <a class="nav-link" href="/login.php">Login</a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="/signup.php">Signup</a>
        </li>';
}

echo '</ul>';


if (!$isUser && !$isAdmin) {
  // Only show Admin button if no one is logged in
  echo '<a href="/admin/admin_login.php" class="btn btn-outline-success my-2 my-sm-0">Admin</a>';
} elseif ($isAdmin) {
  echo '<a href="/admin/logout.php" class="btn btn-outline-danger my-2 my-sm-0">Logout</a>';
}

echo '</div></nav>';
?>