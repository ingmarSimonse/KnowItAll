<?php
session_start();

// Create connection
$conn = new mysqli($servername, $username, $password, $database);

// Connection Check
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Return Array
$returnArr = array();

// Get Recent 100 items
if (isset($_POST["getRecentTrivia"])) {
    $result = $conn->query("SELECT * FROM `trivia` ORDER BY `id` DESC LIMIT 100");
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_array()) {
            // get userName
            $userID = intval($row["userID"]);
            $userID = $conn -> real_escape_string($userID);
            $userID = htmlspecialchars($userID);
            $userResult = $conn->query("SELECT `name` FROM `user` WHERE `id` = $userID");
            array_push($row, $userResult->fetch_array()[0]);
            // push row
            array_push($returnArr, $row);
        }
    }
}

// Update feedback
if (isset($_POST['sendFeedback'])) {
    $feedbackText = $_POST['feedbackText'];
    $feedbackText = $conn -> real_escape_string($feedbackText);
    $feedbackText = htmlspecialchars($feedbackText);
    $triviaID = $_POST['triviaID'];
    $triviaID = $conn -> real_escape_string($triviaID);
    $triviaID = htmlspecialchars($triviaID);
    if ($_POST['accept'] === 'true') {
        $accept = 1;
    } else {
        $accept = -1;
    }
    // Update
    $conn->query("UPDATE `trivia` SET `feedback` = '$feedbackText', `approved` = $accept WHERE `ID` = $triviaID");
}

// All trivia from selected day
if (isset($_POST["random"]) || isset($_POST["allTriviaDay"])) {
    $value_REQUEST = $_POST["value"];
    $value_REQUEST = strval($value_REQUEST);
    $value_REQUEST = $conn -> real_escape_string($value_REQUEST);
    $value_REQUEST = htmlspecialchars($value_REQUEST);
    $result = $conn->query("SELECT * FROM `trivia` WHERE `day` = '$value_REQUEST' AND `approved` = 1 ORDER BY `year` ASC");
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_array()) {
            // get userName
            $userID = $row["userID"];
            $userResult = $conn->query("SELECT `name` FROM `user` WHERE `id` = $userID");
            array_push($row, $userResult->fetch_array()[0]);
            // push row
            array_push($returnArr, $row);
        }
        // Random Trivia of the Day
        if (isset($_POST["random"])) {
            $returnArr = $returnArr[rand(0, (count($returnArr) - 1))];
        }
    } elseif (isset($_POST["random"])) {
        // No trivia from that day, pick random trivia from other day
        $result = $conn->query("SELECT * FROM `trivia` WHERE `approved` = 1 ORDER BY rand() LIMIT 1");
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_array()) {
                // get userName
                $userID = $row["userID"];
                $userResult = $conn->query("SELECT `name` FROM `user` WHERE `id` = $userID");
                array_push($row, $userResult->fetch_array()[0]);
                // push row
                array_push($returnArr, $row);
                $returnArr[0][1] .= " (geen weetje vandaag)";
                $returnArr[0]["day"] .= " (geen weetje vandaag)";
                $returnArr = $returnArr[0];
            }
        }
    }
}

// All trivia from selected user
if (isset($_POST["allTriviaUser"])) {
    $userID = $_SESSION["loggedIn"];
    $userID = $conn -> real_escape_string($userID);
    $userID = htmlspecialchars($userID);
    $result = $conn->query("SELECT * FROM `trivia` WHERE `userID` = $userID ORDER BY `id` DESC");
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_array()) {
            // get userName
            $userResult = $conn->query("SELECT `name` FROM `user` WHERE `id` = $userID");
            array_push($row, $userResult->fetch_array()[0]);
            // push row
            array_push($returnArr, $row);
        }
    }
}

// Insert new trivia
if (isset($_FILES["image"])) {
    // userID
    $userID = $_SESSION["loggedIn"];
    $userID = $conn -> real_escape_string($userID);
    $userID = htmlspecialchars($userID);
    $userID = intval($userID);
    // day
    $day = $_POST["day"];
    $day = $conn -> real_escape_string($day);
    $day = htmlspecialchars($day);
    // title
    $title = $_POST["title"];
    $title = $conn -> real_escape_string($title);
    $title = htmlspecialchars($title);
    // triviaText
    $triviaText = $_POST["trivia"];
    $triviaText = $conn -> real_escape_string($triviaText);
    $triviaText = htmlspecialchars($triviaText);
    // year
    $year = $_POST["year"];
    $year = $conn -> real_escape_string($year);
    $year = htmlspecialchars($year);
    $year = intval($year);
    // image
    $target_dir = "../images/trivia_images/";
    $extension = explode(".", $_FILES["image"]["name"]);
    $extension = array_reverse($extension);
    $extension = $extension[0];
    $file = uniqid() . "." . $extension;
    $target_file = $target_dir . $file;
    // Check if user is banned
    $checkUserBanned = $conn->query("SELECT `name` FROM `user` WHERE EXISTS 
        (SELECT `name` FROM `user` WHERE `ID` = $userID AND `banned` = -1)");
    if (empty($checkUserBanned->num_rows)) {
        array_push($returnArr, false);
    } else {
        array_push($returnArr, true);
        // Check if image already exists
        if (file_exists($target_file)) {
            array_push($returnArr, false);
        } else {
            // save image
            if (move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {
                array_push($returnArr, true);
                // insert
                $conn->query("INSERT INTO `trivia`
                        (`ID`, `day`, `title`, `triviaText`, `year`, `imagePath`, `feedback`, `anonymous`, `approved`, `userID`)
                        VALUES (NULL, '$day', '$title', '$triviaText', $year, '$file', '', -1, 0, $userID)");
            } else {
                array_push($returnArr, false);
            }
        }
    }
}

/*var_dump($returnArr);*/
// Return The Array
echo json_encode($returnArr);
