<?php
session_start();                                        // Start the session
if (!isset($_SESSION['profile_name'])) {                // Check if the user is logged in by verifying if the session variable 'profile_name' exists
    header("Location: index.php");                      // Redirect to home page if not logged in
    exit();                                             // Ensure no further code is executed
}


error_reporting(E_ALL);                                 // Enable error reporting for debugging during development
ini_set('display_errors', 1);

require_once("functions/settings.php");                 // Include the database connection details

$conn = @mysqli_connect($host, $user, $pswd, $db);      // Database connection

$friends_count = 0;
$profile_name = $_SESSION['profile_name'];              // Get profile name from session
$friends_list = [];                                     // Array to store friends

$results_per_page = 10;                                 // Pagination settings

$current_page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($current_page - 1) * $results_per_page;

if (!$conn) {
    $conn_msg = "<p>Database connection failure</p>";
} else {
    // Fetch the logged-in user's friend_id from the "friends" table using their email
    $email = $_SESSION['email'];
    $query = "SELECT friend_id FROM friends WHERE friend_email = ?";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, 's', $email);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if ($row = mysqli_fetch_assoc($result)) {
        $current_user_id = $row['friend_id'];

        // Query to count total number of friends for pagination
        $count_query = "
        SELECT COUNT(*) as total
        FROM myfriends mf
        WHERE mf.friend_id1 = ?";
        $stmt = mysqli_prepare($conn, $count_query);
        mysqli_stmt_bind_param($stmt, 'i', $current_user_id);
        mysqli_stmt_execute($stmt);
        $count_result = mysqli_stmt_get_result($stmt);
        $total_rows = mysqli_fetch_assoc($count_result)['total'];
        $total_pages = ceil($total_rows / $results_per_page);

        // Query to find friends associated with this friend_id, with pagination
        $friends_query = "
        SELECT f.profile_name
        FROM myfriends mf
        JOIN friends f ON mf.friend_id2 = f.friend_id
        WHERE mf.friend_id1 = ?
        ORDER BY f.profile_name
        LIMIT ? OFFSET ?";

        $stmt = mysqli_prepare($conn, $friends_query);
        mysqli_stmt_bind_param($stmt, 'iii', $current_user_id, $results_per_page, $offset);
        mysqli_stmt_execute($stmt);
        $friends_result = mysqli_stmt_get_result($stmt);

        // Fetch friends and update the friend count
        while ($friend_row = mysqli_fetch_assoc($friends_result)) {
            $friends_list[] = $friend_row['profile_name'];
            $friends_count++;
        }

        mysqli_stmt_close($stmt);
    }
    mysqli_close($conn); // Close the connection
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="./style.css" />
    <title>My Friend System | Friend List</title>
</head>

<body class="bg-zinc-700">
    <div class="flex items-center justify-center text-white font-thin ">

        <!-- Parent container -->
        <div class="flex flex-col mx-20 my-4">

            <!-- Card  -->
            <div class="flex flex-col bg-zinc-800 rounded-2xl px-20 p-10 my-4">

                <!-- Friend head -->
                <div class="flex flex-col items-center my-4 ">
                    <h1 class="text-3xl font-medium">My Friend System</h1>
                    <h1 class="mt-2"><strong><?php echo htmlspecialchars($profile_name); ?></strong>'s Friend List Page</h1>

                    <h1 class="text-sm">Total number of friends is <strong><?php echo $total_rows; ?></strong>.</h1>
                </div>

                <!-- Friend list -->
                <div class="flex flex-row justify-between items-center border-b-2 border-slate-500 p-2">
                    <div class="font-bold">
                        Your Friends
                    </div>
                    <div class="font-bold">
                        Option
                    </div>
                </div>

                <?php if ($friends_count > 0): ?>
                    <?php foreach ($friends_list as $friend_name): ?>
                        <div class="flex flex-row justify-between items-center border-b border-slate-500 p-2">
                            <div class="">
                                <?php echo htmlspecialchars($friend_name); ?>
                            </div>
                            <div>
                                <form action="functions/unfriend.php" method="POST">
                                    <input type="hidden" name="friend_name" value="<?php echo htmlspecialchars($friend_name); ?>">
                                    <button type="submit" class="bg-red-500 p-2 rounded-lg text-white">Unfriend</button>
                                </form>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="p-4">
                        No friends found.
                    </div>
                <?php endif; ?>




                <!-- Pagination Controls -->
                <div class="flex justify-around my-4 text-sm">
                    <div>
                        <?php if ($current_page > 1): ?>
                            <a href="?page=<?php echo $current_page - 1; ?>" class="text-lime-500 p-2">
                                < Previous</a>
                                <?php endif; ?>

                                <span class="p-2">Page <?php echo $current_page; ?> of <?php echo $total_pages; ?></span>

                                <?php if ($current_page < $total_pages): ?>
                                    <a href="?page=<?php echo $current_page + 1; ?>" class="text-lime-500 p-2 text-sm rounded-lg">Next ></a>
                                <?php endif; ?>
                    </div>
                </div>

                <!-- Buttons -->
                <div class="flex flex-col item-center justify-between space-y-6 md:flex-row md:space-x-4 md:space-y-0 w-full">
                    <!-- Register -->
                    <a href="friendadd.php">
                        <button type='button' class='w-full flex md:flex-grow  justify-center items-center p-4 space-x-4 font-bold text-zinc-800 rounded-md shadow-lg px-9 bg-lime-500 hover:bg-opacity-80 hover:shadow-md hover:shadow-lime-800 transition hover:-translate-y-0.5 duration-150'>Add new friends</button>
                    </a>

                    <!-- Clear -->
                    <a href="logout.php">
                        <button type='button' class="w-full flex md:flex-grow  justify-center items-center p-4 space-x-4  rounded-md shadow-lg px-9 outline-lime-500 hover:bg-opacity-80 hover:shadow-md hover:shadow-lime-800 border transition hover:-translate-y-0.5 duration-150">Log out

                        </button>
                    </a>

                </div>

            </div>
        </div>
    </div>
</body>

</html>