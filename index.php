<?php

echo 'Current PHP version: ' . phpversion();


// Enable error reporting for debugging during development
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Include the database connection details and the HitCounter class
require_once("functions/settings.php");

$conn = @mysqli_connect($host, $user, $pswd, $db);

$conn_msg = "friends";
$table_msg = "";
$friends_msg = "";
$myfriends_msg = "";
$friends_pop_msg = "";
$myfriends_pop_msg = "";

if (!$conn) {
  $conn_msg = "<p>Database connection failure</p>";
} else {
  $conn_msg = "<p>Database connection successful</p>";

  $sql_create_friends = "CREATE TABLE IF NOT EXISTS friends (
    friend_id INTEGER NOT NULL AUTO_INCREMENT PRIMARY KEY,
    friend_email VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(20) NOT NULL,
    profile_name VARCHAR(30) NOT NULL,
    date_started DATE NOT NULL,
    num_of_friends INTEGER UNSIGNED
  );
  ";

  $sql_create_myfriends = "CREATE TABLE IF NOT EXISTS myfriends (
    friend_id1 INTEGER NOT NULL,
    friend_id2 INTEGER NOT NULL,
    CONSTRAINT fk_friend1 FOREIGN KEY (friend_id1) REFERENCES friends(friend_id),
    CONSTRAINT fk_friend2 FOREIGN KEY (friend_id2) REFERENCES friends(friend_id),
    CONSTRAINT chk_different_ids CHECK (friend_id1 <> friend_id2)
  );";

  $sql_pop_friends = "
  INSERT INTO friends (friend_email, password, profile_name, date_started, num_of_friends) VALUES
    ('john.doe@gmail.com', 'password123', 'John Doe', '2023-01-15', 5),
    ('jane.smith@yahoo.com', 'mypassword', 'Jane Smith', '2022-11-20', 3),
    ('alice.jones@outlook.com', 'alice123', 'Alice Jones', '2023-02-10', 8),
    ('bob.brown@gmail.com', 'securepass', 'Bob Brown', '2021-05-25', 10),
    ('charlie.white@hotmail.com', 'charlie007', 'Charlie White', '2022-08-14', 7),
    ('david.green@aol.com', 'davidpass', 'David Green', '2023-03-12', 2),
    ('emily.black@gmail.com', 'emily321', 'Emily Black', '2022-12-05', 6),
    ('frank.thomas@yahoo.com', 'frankpwd', 'Frank Thomas', '2023-04-18', 4),
    ('george.wilson@outlook.com', 'georgepwd', 'George Wilson', '2022-09-30', 9),
    ('hannah.martin@gmail.com', 'hannahpwd', 'Hannah Martin', '2023-06-07', 1);
  ";

  $sql_pop_myfriends = "
  INSERT INTO myfriends (friend_id1, friend_id2) VALUES
    (1, 2),
    (1, 3),
    (2, 4),
    (2, 5),
    (3, 6),
    (3, 7),
    (4, 8),
    (4, 9),
    (5, 10),
    (5, 1),
    (6, 2),
    (6, 8),
    (7, 3),
    (7, 9),
    (8, 4),
    (8, 10),
    (9, 1),
    (9, 6),
    (10, 7),
    (10, 5);
  ";

  // Create tables

  $queryResult_friends = @mysqli_query($conn, $sql_create_friends);

  if ($queryResult_friends) {
    $friends_msg = "Successfully created the <strong>friends</strong> table.";
  } else {
    $friends_msg = "Error code " . mysqli_errno($conn) . ": " . mysqli_error($conn);
  }

  $queryResult_myfriends = @mysqli_query($conn, $sql_create_myfriends);

  if ($queryResult_myfriends) {
    $myfriends_msg = "Successfully created the <strong>my friends</strong> table.";
  } else {
    $myfriends_msg = "Error code " . mysqli_errno($conn) . ": " . mysqli_error($conn);
  }

  if ($queryResult_friends && $queryResult_myfriends) {
    $table_msg = "Table successfully created and populated.";
  }



  // Populate tables
  // Check if the 'friends' table is already populated
  $check_friends = "SELECT COUNT(*) AS count FROM friends";
  $result_friends = @mysqli_query($conn, $check_friends);
  $row_friends = mysqli_fetch_assoc($result_friends);
  $friends_count = $row_friends['count'];

  // Populate the 'friends' table only if it's not already populated
  if ($friends_count == 0) {
    $queryResult_pop_friends = @mysqli_query($conn, $sql_pop_friends);
    if ($queryResult_pop_friends) {
      $friends_pop_msg = "Successfully populated the <strong>friends</strong> table.";
    } else {
      $friends_pop_msg = "Error code " . mysqli_errno($conn) . ": " . mysqli_error($conn);
    }
  } else {
    $friends_pop_msg = "<strong>friends</strong> table is already populated.";
  }

  // Check if the 'myfriends' table is already populated
  $check_myfriends = "SELECT COUNT(*) AS count FROM myfriends";
  $result_myfriends = @mysqli_query($conn, $check_myfriends);
  $row_myfriends = mysqli_fetch_assoc($result_myfriends);
  $myfriends_count = $row_myfriends['count'];

  // Populate the 'myfriends' table only if it's not already populated
  if ($myfriends_count == 0) {
    $queryResult_pop_myfriends = @mysqli_query($conn, $sql_pop_myfriends);
    if ($queryResult_pop_myfriends) {
      $myfriends_pop_msg = "Successfully populated the <strong>myfriends</strong> table.";
    } else {
      $myfriends_pop_msg = "Error code " . mysqli_errno($conn) . ": " . mysqli_error($conn);
    }
  } else {
    $myfriends_pop_msg = "<strong>myfriends</strong> table is already populated.";
  }
}

?>


<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <link rel="stylesheet" href="./css/style.css" />
  <title>My Friend System | Home</title>
</head>

<body>
  <div class="container px-10">
    <div class="my-10 mx-10 px-10 border-solid border-2 rounded-xl border-red-100">
      <div class="flex flex-col items-center my-4">
        <h1 class="text-3xl">My friend System</h1>
        <h1>Assigment Home Page</h1>
      </div>

      <!-- Flex for Name & ID -->
      <div class="flex flex-row justify-between my-3">
        <div class="flex-col">
          <p>Name: Jinjuta Suksuwan</p>
          <p>Email: 103818112@student.swin.edu.au</p>
        </div>
        <div class="flex-col">
          <p>Student ID: 103818112</p>

        </div>
      </div>

      <div class="my-4">
        <p>
          I declare that this assignment is my individual work. I have not worked
          collaboratively nor have I copied from any other student's work or from
          any other source.
        </p>
      </div>

      <!-- Echo from PHP -->
      <!-- Create tables -->

      <p><?php echo $friends_msg; ?></p>
      <p><?php echo $myfriends_msg; ?></p>
      <p><?php echo $table_msg; ?></p>
      <!-- Populate tables -->
      <p><?php echo $friends_pop_msg; ?></p>
      <p><?php echo $myfriends_pop_msg; ?></p>

      <div class="flex flex-row justify-between my-4">

        <a href="signup.php">

          <button class="bg-blue-500 p-2 rounded-lg text-white">Sign up</button>
        </a>
        <a href="#">
          <button class="bg-red-500 p-2 rounded-lg text-white">Log in</button>
        </a>
        <a href="#">
          <button class="bg-orange-500 p-2 rounded-lg text-white">About</button>
        </a>
      </div>
    </div>
  </div>

  <p>
  <pre><?php echo $conn_msg; ?></pre>
  </p>
</body>

</html>