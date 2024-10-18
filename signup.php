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
            $date_started = date('Y-m-d'); // Current server date
            $insert_query = "INSERT INTO friends (friend_email, password, profile_name, date_started, num_of_friends) VALUES (?, ?, ?, ?, 0)";
            $stmt = mysqli_prepare($conn, $insert_query);
            mysqli_stmt_bind_param($stmt, 'ssss', $email, $password, $profile_name, $date_started);

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
            $home_button = "<a href='index.php' class='w-full flex justify-center items-center p-4 space-x-2 rounded-md shadow-lg px-9 text-lime-500  hover:bg-opacity-80 hover:shadow-md hover:shadow-lime-800  transition hover:-translate-y-0.5 duration-150'><span>Return home</span>
            
            <svg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 24 24' stroke-width='1' stroke='currentColor' class='size-6'>
            <path stroke-linecap='round' stroke-linejoin='round' d='m2.25 12 8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25' />
            </svg>


            </a>";
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
    <title>My Friend System | Sign up</title>
</head>

<body class="bg-zinc-700">
    <div class="flex items-center justify-center text-white font-thin ">

        <!-- Parent container -->
        <div class="flex flex-col mx-20 my-4">

            <!-- Card / Form -->
            <div class="flex flex-col bg-zinc-800 rounded-2xl px-10 p-6 my-4">

                <!-- Form head -->
                <div class="flex flex-col items-center my-4 ">
                    <h1 class="text-3xl font-medium">My friend System</h1>
                    <h1 class="text-2xl">Registration Page</h1>
                </div>
                <!-- Form body -->
                <form action="signup.php" method="POST">
                    <div class="flex flex-col my-3 space-y-6">

                        <!-- Email -->
                        <div class="d-flex">
                            <label for="email" class="text-slate-400">Email</label>
                            <input type="text" name="email" class="w-full p-6 border border-gray-300 rounded-md placeholder:font-light"

                                value="<?php echo htmlspecialchars($email); ?>" placeholder="kangaroo@zoo.com.au">

                            <!-- Display email error message -->
                            <?php if (!empty($email_error)) echo "<p class='text-red-500 mt-2'>$email_error</p>"; ?>

                        </div>

                        <!-- Profile Name -->
                        <div class="flex-row">
                            <label for="profile" class="text-slate-400">Profile Name</label>
                            <input type="text" name="profile"

                                class="w-full p-6 border border-gray-400 rounded-md placeholder:font-light"

                                value="<?php echo htmlspecialchars($profile_name); ?>" placeholder="kangaroo">
                            <!-- Display profile name error message -->
                            <?php if (!empty($profile_error)) echo "<p class='text-red-500 mt-2'>$profile_error</p>"; ?>
                        </div>

                        <!-- Password -->
                        <div class="flex-row">
                            <label for="password" class="text-slate-400">Password</label>
                            <input type="password" name="password" placeholder="**********"

                                class="w-full p-6 border border-gray-400 rounded-md placeholder:font-light">
                            <!-- Display password error message -->
                            <?php if (!empty($password_error)) echo "<p class='text-red-500 mt-2'>$password_error</p>"; ?>
                        </div>

                        <!-- Confirm password -->

                        <div class="flex-row">
                            <label for="c_password" class="text-slate-400">Confirm Password</label>
                            <input type="password" name="c_password" placeholder="**********"

                                class="w-full p-6 border border-gray-300 rounded-md placeholder:font-light">
                            <!-- Display confirm password error message -->
                            <?php if (!empty($confirm_password_error)) echo "<p class='text-red-500 mt-2'>$confirm_password_error</p>"; ?>
                        </div>
                        <div class="flex flex-col item-center justify-between space-y-6 md:flex-row md:space-x-4 md:space-y-0 w-full">
                            <button type='submit' class='w-full flex md:flex-grow  justify-center items-center p-4 space-x-4 font-bold text-zinc-800 rounded-md shadow-lg px-9 bg-lime-500 hover:bg-opacity-80 hover:shadow-md hover:shadow-lime-800 transition hover:-translate-y-0.5 duration-150'>Register</button>
                            <button type='reset' class="w-full flex md:flex-grow  justify-center items-center p-4 space-x-4  rounded-md shadow-lg px-9 outline-lime-500 hover:bg-opacity-80 hover:shadow-md hover:shadow-lime-800 border transition hover:-translate-y-0.5 duration-150">Clear</button>

                        </div>
                    </div>
                </form>
            </div>
            <!-- Home button -->
            <div class="items-center my-4">
                <?php
                echo $home_button;
                ?>
            </div>
        </div>
    </div>
</body>

</html>