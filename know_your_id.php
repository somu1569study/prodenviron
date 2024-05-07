<?php
session_start();

// Check if the user is logged in
if (isset($_SESSION['username']) && isset($_SESSION['id'])) {
    // Get the id value from the session
    $id = $_SESSION['id'];
    // Display the id value
    echo "Welcome, " . $_SESSION['username'] . "! Your user ID is: $id";
} else {
    // If the user is not logged in, redirect to the login page
    header("Location: login.php");
    exit();
}
?>
