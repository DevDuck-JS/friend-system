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
    // Retrieve the logged-in user's email
    $email = $_SESSION['email'];
    $friend_name = $_POST['friend_name'];

    // Get the current user's friend_id
    $query = "SELECT friend_id FROM friends WHERE friend_email = ?";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, 's', $email);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if ($row = mysqli_fetch_assoc($result)) {
        $friend_id = $row['friend_id'];

        // Find the friend_id of the friend to be removed
        $friend_query = "SELECT friend_id FROM friends WHERE profile_name = ?";
        $stmt = mysqli_prepare($conn, $friend_query);
        mysqli_stmt_bind_param($stmt, 's', $friend_name);
        mysqli_stmt_execute($stmt);
        $friend_result = mysqli_stmt_get_result($stmt);

        if ($friend_row = mysqli_fetch_assoc($friend_result)) {
            $friend_to_remove_id = $friend_row['friend_id'];

            // Delete the friendship from the "myfriends" table
            $delete_query = "DELETE FROM myfriends WHERE (friend_id1 = ? AND friend_id2 = ?) OR (friend_id1 = ? AND friend_id2 = ?)";
            $stmt = mysqli_prepare($conn, $delete_query);
            mysqli_stmt_bind_param($stmt, 'iiii', $friend_id, $friend_to_remove_id, $friend_to_remove_id, $friend_id);

            if (mysqli_stmt_execute($stmt)) {
                // Successfully removed the friend, redirect back to the friend list
                header("Location: ../friendlist.php");
                exit();
            } else {
                echo "Failed to remove friend.";
            }
            mysqli_stmt_close($stmt);
        } else {
            echo "Friend not found.";
        }
    } else {
        echo "User not found.";
    }
} else {
    echo "Invalid request.";
}

mysqli_close($conn); // Close the connection
