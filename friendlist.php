<?php
session_start(); // Start the session

error_reporting(E_ALL); // Enable error reporting for debugging during development
ini_set('display_errors', 1);

require_once("functions/settings.php"); // Include the database connection details

$conn = @mysqli_connect($host, $user, $pswd, $db); // Database connection

$friends_count = 0;
$profile_name = isset($_SESSION['profile_name']) ? $_SESSION['profile_name'] : '';
$friends_list = []; // Array to store friends

// Pagination settings
$results_per_page = 10;
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
    <link rel="stylesheet" href="./css/style.css" />
    <title>My Friend System | Friend List</title>
</head>

<body>
    <div class="flex flex-col items-center my-4 border-2 border-slate-500 rounded-lg w-[80%] mx-auto">
        <div class="my-4">
            <h1 class="text-3xl">My Friend System</h1>
            <h1><strong><?php echo htmlspecialchars($profile_name); ?></strong>'s Friend List Page</h1>
            <h1>Total number of friends is <strong><?php echo $total_rows; ?></strong>.</h1>
        </div>
        <div>
            <table class="border-solid border-2 border-slate-500">
                <thead>
                    <tr>
                        <th>Friend</th>
                        <th>Option</th>
                    </tr>
                </thead>
                <tbody class="border-solid border-2 border-slate-500">
                    <?php if ($friends_count > 0): ?>
                        <?php foreach ($friends_list as $friend_name): ?>
                            <tr>
                                <td class="px-2"><?php echo htmlspecialchars($friend_name); ?></td>
                                <td class="px-2">
                                    <form action="functions/unfriend.php" method="POST">
                                        <input type="hidden" name="friend_name" value="<?php echo htmlspecialchars($friend_name); ?>">
                                        <button type="submit" class="bg-red-500 p-2 rounded-lg text-white">Unfriend</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="2">No friends found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Pagination Controls -->

        <div class="flex justify-around my-4">
            <div>
                <?php if ($current_page > 1): ?>
                    <a href="?page=<?php echo $current_page - 1; ?>" class="bg-gray-500 p-2 rounded-lg">
                        < Previous</a>
                        <?php endif; ?>

                        <span class="p-2">Page <?php echo $current_page; ?> of <?php echo $total_pages; ?></span>

                        <?php if ($current_page < $total_pages): ?>
                            <a href="?page=<?php echo $current_page + 1; ?>" class="bg-gray-500 p-2 rounded-lg">Next ></a>
                        <?php endif; ?>
            </div>
        </div>


        <div class="flex justify-around my-4">
            <a href="friendadd.php"><button class="bg-blue-500 p-2 rounded-lg text-white">Add Friends</button></a>
            <a href="logout.php"><button class="bg-blue-500 p-2 rounded-lg text-white">Log out</button></a>
        </div>
    </div>
</body>

</html>