<?php

session_start();                                        // Start the session

error_reporting(E_ALL);                                 // Enable error reporting for debugging during development
ini_set('display_errors', 1);

require_once("functions/settings.php");                 // Include the database connection details and the HitCounter class

$conn = @mysqli_connect($host, $user, $pswd, $db);      // Database connection
$conn_msg = "";
$errors = [];                                           // Array to hold error messages
$email = $profile_name = "";                            // Variables to retain user input
$home_button = "";


if (!$conn) {
    $conn_msg = "<p>Database connection failure</p>";
} else {
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        // Retrieve and sanitize input data
        $email = trim($_POST['email']);
        $profile_name = trim($_POST['profile']);
        $password = $_POST['password'];
        $confirm_password = $_POST['c_password'];

        // Server-side validation
        $is_valid = true;

        // Check if email is valid
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $email_error = "Invalid email format.";
            $is_valid = false;
        } else {
            // Check if email already exists
            $email_check_query = "SELECT * FROM friends WHERE friend_email = ?";
            $stmt = mysqli_prepare($conn, $email_check_query);
            mysqli_stmt_bind_param($stmt, 's', $email);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);

            if (mysqli_num_rows($result) > 0) {
                $email_error = "Email already exists.";
                $is_valid = false;
            }

            mysqli_stmt_close($stmt);
        }

        // Validate profile name (only letters and not blank)
        if (empty($profile_name) || !preg_match("/^[a-zA-Z]+$/", $profile_name)) {
            $profile_error = "Profile name can only contain letters and cannot be blank.";
            $is_valid = false;
        }

        // Validate password (only letters and numbers)
        if (!preg_match("/^[a-zA-Z0-9]+$/", $password)) {
            $password_error = "Password can only contain letters and numbers.";
            $is_valid = false;
        }

        // Check if passwords match
        if ($password !== $confirm_password) {
            $confirm_password_error = "Passwords do not match.";
            $is_valid = false;
        }

        if ($is_valid) {                                                                    // If all validations pass, insert data       
            $hashed_password = hash('sha256', $password);                  // Hash the password
            $date_started = date('Y-m-d'); // Current server date
            $insert_query = "INSERT INTO friends (friend_email, password, profile_name, date_started, num_of_friends) VALUES (?, ?, ?, ?, 0)";
            $stmt = mysqli_prepare($conn, $insert_query);
            mysqli_stmt_bind_param($stmt, 'ssss', $email, $hashed_password, $profile_name, $date_started);

            if (mysqli_stmt_execute($stmt)) {
                // Registration successful, set session variables
                $_SESSION['loggedin'] = true;
                $_SESSION['profile_name'] = $profile_name;
                $_SESSION['email'] = $email;

                // Redirect to friendadd.php
                header("Location: friendadd.php");
                exit();
            } else {
                $conn_msg = "Failed to register. Please try again.";
            }

            mysqli_stmt_close($stmt);
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
    <link rel="stylesheet" href="./css/style.css" />
    <title>My Friend System | Sign up</title>
</head>

<body>
    <form action="signup.php" method="POST">
        <div class="container w-[80%] px-10 mx-auto my-10 border-solid border-2 rounded-xl border-red-100">
            <div class="flex flex-col items-center my-4">
                <h1 class="text-3xl">My friend System</h1>
                <h1>Assignment Home Page</h1>
            </div>

            <!-- Flex for Name & ID -->
            <div class="flex flex-col my-3 p-10">
                <div class="d-flex">
                    <label for="email">Email</label>
                    <input type="text" name="email" value="<?php echo htmlspecialchars($email); ?>" placeholder="kangaroo@zoo.com.au">
                    <!-- Display email error message -->
                    <?php if (!empty($email_error)) echo "<p class='text-red-500'>$email_error</p>"; ?>
                </div>
                <div class="flex-row">
                    <label for="profile">Profile Name</label>
                    <input type="text" name="profile" value="<?php echo htmlspecialchars($profile_name); ?>" placeholder="kangaroo">
                    <!-- Display profile name error message -->
                    <?php if (!empty($profile_error)) echo "<p class='text-red-500'>$profile_error</p>"; ?>
                </div>
                <div class="flex-row">
                    <label for="password">Password</label>
                    <input type="password" name="password" placeholder="**********">
                    <!-- Display password error message -->
                    <?php if (!empty($password_error)) echo "<p class='text-red-500'>$password_error</p>"; ?>
                </div>
                <div class="flex-row">
                    <label for="c_password">Confirm Password</label>
                    <input type="password" name="c_password" placeholder="**********">
                    <!-- Display confirm password error message -->
                    <?php if (!empty($confirm_password_error)) echo "<p class='text-red-500'>$confirm_password_error</p>"; ?>
                </div>
            </div>
            <div class='flex justify-around my-4'>
                <button type='submit' class='bg-blue-500 p-2 rounded-lg text-white'>Register</button>
                <button type='reset' class='bg-blue-500 p-2 rounded-lg text-white'>Clear</button>

            </div>
            <div class="flex flex-col items-center my-4">
                <?php
                echo $home_button;
                ?>
            </div>
        </div>
    </form>
</body>

</html>