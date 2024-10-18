<?php



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
    $table_msg = "Tables have been successfully created and populated.";
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
  <link rel="stylesheet" href="./style.css" />
  <title>My Friend System | Home</title>
</head>

<body class="bg-zinc-700">
  <div class="">
    <!-- Background / Parent container -->
    <div class="flex items-center justify-center h-screen text-white">
      <div class="flex flex-col mx-20">
        <div class="bg-zinc-800 p-2 rounded-2xl">
          <!-- Card -->
          <div class="flex flex-col md:flex-row rounded-l-lg">
            <!-- Image -->
            <img
              src="images/image.jpg"
              alt=""
              class="object-fit rounded-t-xl h-80 md:h-96 md:rounded-l-xl md:rounded-r-none transform hover:scale-105 hover:rounded-t-xl md:hover:scale-105 md:hover:rounded-l-xl md:hover:rounded-r-none" />


            <!-- Content -->
            <div class="p-2 md:-12 font-thin">
              <div class="items-center mt-2">
                <h1 class="text-3xl font-medium text-center md:text-left px-4">My friend System</h1>
                <h1 class="text-2xl text-center md:text-left px-4">Assigment Home Page</h1>
              </div>

              <!-- Name & ID -->
              <div class="flex flex-col items-start mt-5 space-y-4 md:space-x-3 md:flex-row md:space-y-0 md:justify-between">
                <div class="px-4">
                  <span class="font-light">Name: </span>
                  <span>Jinjuta Suksuwan</span>
                </div>

                <div class="px-4 text-end">
                  <span class="font-light">Student ID: </span>
                  <span>103818112</span>
                </div>
              </div>

              <div class="p-2 px-4">
                <span class="font-light">Email: </span>
                <span>103818112@student.swin.edu.au</span>
              </div>

              <div class="px-4 my-4 max-w-96 text-sm leading-5 tracking-wide text-center md:text-left">
                <p>
                  I declare that this assignment is my individual work. I have not worked collaboratively nor have I copied from any other student's work or from any other source.
                </p>
              </div>

              <!-- Buttons -->
              <div class="flex flex-col item-center justify-between space-y-6 md:flex-row md:space-y-0 px-4 w-full">


                <!-- Sign up -->
                <a href="signup.php" class="md:w-full md:flex-grow  md:mr-4">
                  <button
                    class="w-full flex justify-center items-center p-4 space-x-4 font-bold text-zinc-800 rounded-md shadow-lg px-9 bg-lime-500 hover:bg-opacity-80 hover:shadow-md hover:shadow-lime-800 transition hover:-translate-y-0.5 duration-150">
                    <span>Sign up</span>
                    <svg
                      xmlns="http://www.w3.org/2000/svg"
                      viewBox="0 0 16 16"
                      fill="currentColor"
                      class="size-4">
                      <path
                        fill-rule="evenodd"
                        d="M15 8A7 7 0 1 0 1 8a7 7 0 0 0 14 0ZM4.75 7.25a.75.75 0 0 0 0 1.5h4.69L8.22 9.97a.75.75 0 1 0 1.06 1.06l2.5-2.5a.75.75 0 0 0 0-1.06l-2.5-2.5a.75.75 0 0 0-1.06 1.06l1.22 1.22H4.75Z"
                        clip-rule="evenodd" />
                    </svg>
                  </button>
                </a>


                <a href="login.php" class="md:w-full md:flex-grow md:mr-4">
                  <!-- Log in -->
                  <button class="w-full flex justify-center items-center p-4 space-x-4  rounded-md shadow-lg px-9 outline-lime-500 hover:bg-opacity-80 hover:shadow-md hover:shadow-lime-800 border transition hover:-translate-y-0.5 duration-150">Log in</button>
                </a>
              </div>
              <a href=" about.php">
                <button class="text-lime-500 font-thin text-sm px-4 pt-2 w-full md:w-auto flex justify-end items-center space-x-2"><span>About project</span>
                  <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m11.25 11.25.041-.02a.75.75 0 0 1 1.063.852l-.708 2.836a.75.75 0 0 0 1.063.853l.041-.021M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9-3.75h.008v.008H12V8.25Z" />
                  </svg>
                </button>
              </a>
            </div>



          </div>


        </div>
        <div class="text-lime-500 my-4 text-xs font-mono">
          <!-- Echo from PHP -->
          <!-- Create tables -->
          <p><?php echo $conn_msg; ?></p>
          <br />
          <p><?php echo $friends_msg; ?></p>
          <p><?php echo $myfriends_msg; ?></p>
          <p><?php echo $friends_pop_msg; ?></p>
          <p><?php echo $myfriends_pop_msg; ?></p>
          <br />
          <p><?php echo $table_msg; ?></p>

          <br />
          <br />

          <p><?php
              echo 'Current PHP version: ' . phpversion(); ?></p>
        </div>
      </div>
    </div>
  </div>


  </div>
</body>

</html>