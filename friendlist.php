<?php
session_start();                                        // Start the session

error_reporting(E_ALL);                                 // Enable error reporting for debugging during development
ini_set('display_errors', 1);

require_once("functions/settings.php");                 // Include the database connection details

$conn = @mysqli_connect($host, $user, $pswd, $db);      // Database connection

$friend_count = 0;
$profile_name = isset($_SESSION['profile_name']) ? $_SESSION['profile_name'] : '';
$friends_list = [];                                     // Array to store friends
if (!$conn) {
    $conn_msg = "<p>Database connection failure</p>";
} else {
    // query using this user's friend_id attribute in "friends" table as friend_id1 attribute in "myfriends" table to find friend_id2 attribute's values associated with this friend_id1's values.
    // Fetch the logged-in user's friend_id from the "friends" table using their email
    $email = $_SESSION['email'];
    $query = "SELECT friend_id FROM friends WHERE friend_email = ?";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, 's', $email);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if ($row = mysqli_fetch_assoc($result)) {
        $friend_id = $row['friend_id'];

        // Now query the "myfriends" table to find friends associated with this friend_id
        $friends_query = "
        SELECT f.profile_name
        FROM myfriends mf
        JOIN friends f ON mf.friend_id2 = f.friend_id
        WHERE mf.friend_id1 = ?";

        $stmt = mysqli_prepare($conn, $friends_query);
        mysqli_stmt_bind_param($stmt, 'i', $friend_id);
        mysqli_stmt_execute($stmt);
        $friends_result = mysqli_stmt_get_result($stmt);

        // Fetch friends and update the friend count
        while ($friend_row = mysqli_fetch_assoc($friends_result)) {
            $friends_list[] = $friend_row['profile_name'];
        }
        $friends_count = count($friends_list); // Update the total number of friends

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
        <h1 class="text-3xl">My friend System</h1>
        <h1><?php echo $profile_name; ?>'s Friend List Page</h1>
        <h1>Total number of friends is <?php echo $friend_count ?>.</h1>
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
                            <td><?php echo htmlspecialchars($friend_name); ?></td>
                            <td>
                                <form action="unfriend.php" method="POST">
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

        <div class="flex justify-around my-4">
            <a href="friendadd.php"><button class="bg-blue-500 p-2 rounded-lg text-white">Add Friends</button></a>
            <a href="logout.php"><button class="bg-blue-500 p-2 rounded-lg text-white">Log out</button></a>
        </div>
    </div>
</body>

</html>