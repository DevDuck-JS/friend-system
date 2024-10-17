<?php
session_start(); // Start the session

error_reporting(E_ALL); // Enable error reporting for debugging during development
ini_set('display_errors', 1);

require_once("settings.php"); // Include the database connection details

$conn = @mysqli_connect($host, $user, $pswd, $db); // Database connection

if (!$conn) {
    echo "Database connection failure";
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_SESSION['email'])) {
    // Retrieve the logged-in user's email and the friend's ID from the form submission
    $email = $_SESSION['email'];
    $friend_id_to_add = $_POST['friend_id'];

    // Get the current user's friend_id
    $query = "SELECT friend_id FROM friends WHERE friend_email = ?";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, 's', $email);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if ($row = mysqli_fetch_assoc($result)) {
        $current_user_id = $row['friend_id'];

        // Insert two new records into the "myfriends" table to establish the mutual friendship
        $insert_query = "
            INSERT INTO myfriends (friend_id1, friend_id2) VALUES (?, ?), (?, ?)
        ";
        $stmt = mysqli_prepare($conn, $insert_query);
        mysqli_stmt_bind_param($stmt, 'iiii', $current_user_id, $friend_id_to_add, $friend_id_to_add, $current_user_id);

        if (mysqli_stmt_execute($stmt)) {
            // Successfully added the friend in both directions, redirect back to friendadd.php
            header("Location: ../friendadd.php");
            exit();
        } else {
            echo "Failed to add friend.";
        }
        mysqli_stmt_close($stmt);
    } else {
        echo "User not found.";
    }
} else {
    echo "Invalid request.";
}

mysqli_close($conn); // Close the connection
