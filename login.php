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

<body class="bg-zinc-700">

    <div class="flex items-center justify-center text-white font-thin ">

        <!-- Parent container -->
        <div class="flex flex-col mx-20 my-4">

            <!-- Card / Form -->
            <div class="flex flex-col bg-zinc-800 rounded-2xl px-10 p-6 my-4">

                <!-- Form head -->
                <div class="flex flex-col items-center my-4">
                    <h1 class="text-3xl font-medium">My Friend System</h1>
                    <h1 class="text-2xl">Log in Page</h1>
                </div>

                <!-- Form body -->
                <form action="login.php" method="POST">
                    <div class="flex flex-col my-3 space-y-6">

                        <!-- Flex for Email & Password -->
                        <div class="d-flex">
                            <label for="email" class="text-slate-400">Email</label>
                            <input type="text" name="email" value="<?php echo htmlspecialchars($email); ?>" placeholder="kangaroo@zoo.com.au"
                                class="w-full p-6 border border-gray-300 rounded-md placeholder:font-light text-slate-700">
                            <!-- Display email error message -->
                            <?php if (!empty($errors['email'])) echo "<p class='text-red-500 mt-2'>{$errors['email']}</p>"; ?>
                        </div>

                        <div class="flex-row">
                            <label for="password" class="text-slate-400">Password</label>
                            <input type="password" name="password" placeholder="**********"
                                class="w-full text-slate-700 p-6 border border-gray-300 rounded-md placeholder:font-light">
                            <!-- Display password error message -->
                            <?php if (!empty($errors['password'])) echo "<p class='text-red-500 mt-2'>{$errors['password']}</p>"; ?>
                        </div>

                        <!-- Buttons -->
                        <div class="flex flex-col item-center justify-between space-y-6 md:flex-row md:space-x-4 md:space-y-0 w-full">
                            <!-- Login -->
                            <button type='submit' class='w-full flex md:flex-grow justify-center items-center p-4 space-x-4 font-bold text-zinc-800 rounded-md shadow-lg px-9 bg-lime-500 hover:bg-opacity-80 hover:shadow-md hover:shadow-lime-800 transition hover:-translate-y-0.5 duration-150'>Log in</button>

                            <!-- Home -->
                            <a href="index.php"><button type="button" class="w-full flex md:flex-grow justify-center items-center p-4 space-x-4 rounded-md shadow-lg px-9 outline-lime-500 hover:bg-opacity-80 hover:shadow-md hover:shadow-lime-800 border transition hover:-translate-y-0.5 duration-150">Home</button></a>

                        </div>


                        <!-- Display connection error message, if any -->
                        <?php if (!empty($conn_msg)) echo "<p class='text-red-500 text-center'>$conn_msg</p>"; ?>
                    </div>
                </form>
            </div>
        </div>
    </div>


</body>

</html>