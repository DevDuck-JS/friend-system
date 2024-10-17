<?php

session_start();                                        // Start the session

error_reporting(E_ALL);                                 // Enable error reporting for debugging during development
ini_set('display_errors', 1);

require_once("functions/settings.php");                 // Include the database connection details

$conn = @mysqli_connect($host, $user, $pswd, $db);      // Database connection
$conn_msg = "";
$errors = [];                                           // Array to hold error messages
$email = "";                                            // Variable to retain user input
$home_button = "";

if (!$conn) {
    $conn_msg = "<p>Database connection failure</p>";
} else {
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $email = trim($_POST['email']);                  // Retrieve and sanitize input data
        $password = $_POST['password'];

        $is_valid = true;                                // Server-side validation
        if (empty($password)) {                                     // Check if password is empty
            $errors['password'] = "Password is required.";
            $is_valid = false;
        }
        if (empty($email)) {                            // Check if email is empty
            $errors['email'] = "Email is required.";
            $is_valid = false;
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {           // Check if email is valid and exists in the database
            $errors['email'] = "Invalid email format.";
            $is_valid = false;
        } else {                                                    // Check if email exists in the database
            $email_check_query = "SELECT * FROM friends WHERE friend_email = ?";
            $stmt = mysqli_prepare($conn, $email_check_query);
            mysqli_stmt_bind_param($stmt, 's', $email);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);

            if (mysqli_num_rows($result) > 0) {

                $user = mysqli_fetch_assoc($result);                // Email exists, now check if the password matches

                if ($user['password'] !== $password) {
                    $errors['password'] = "Incorrect password.";
                    $is_valid = false;
                }
            } else {
                $errors['email'] = "Email does not exist.";
                $is_valid = false;
            }

            mysqli_stmt_close($stmt);
        }

        if ($is_valid) {
            $_SESSION['loggedin'] = true;                           // Set session variables for successful login
            $_SESSION['profile_name'] = $user['profile_name'];
            $_SESSION['email'] = $user['friend_email'];

            header("Location: friendlist.php");                     // Redirect to friendlist.php
            exit();
        }

        if (!$is_valid) {
            $home_button = "<a href='index.php' class='bg-red-500 p-2 rounded-lg text-white'>Home</a>";
        }
    }
    mysqli_close($conn);
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="./style.css" />
    <title>My Friend System | Log in</title>
</head>

<body>
    <form action="login.php" method="POST">
        <div class="container w-[50%] px-10 mx-auto my-10 border-solid border-2 rounded-xl border-red-100">
            <div class="flex flex-col items-center my-4">
                <h1 class="text-3xl">My Friend System</h1>
                <h1>Log in Page</h1>
            </div>

            <!-- Flex for Email & Password -->
            <div class="flex flex-col my-3 p-10">
                <div class="d-flex">
                    <label for="email">Email</label>
                    <input type="text" name="email" value="<?php echo htmlspecialchars($email); ?>" placeholder="kangaroo@zoo.com.au">
                    <!-- Display email error message -->
                    <?php if (!empty($errors['email'])) echo "<p class='text-red-500'>{$errors['email']}</p>"; ?>
                </div>

                <div class="flex-row">
                    <label for="password">Password</label>
                    <input type="password" name="password" placeholder="**********">
                    <!-- Display password error message -->
                    <?php if (!empty($errors['password'])) echo "<p class='text-red-500'>{$errors['password']}</p>"; ?>
                </div>

            </div>
            <div class='flex justify-around my-4'>
                <button type='submit' class='bg-blue-500 p-2 rounded-lg text-white'>Log in</button>

                <a href="index.php">
                    <button type="button" class='bg-green-500 p-2 rounded-lg text-white'>Home</button>
                </a>
            </div>
            <!-- Display connection error message, if any -->
            <?php if (!empty($conn_msg)) echo "<p class='text-red-500 text-center'>$conn_msg</p>"; ?>
        </div>
    </form>
</body>

</html>