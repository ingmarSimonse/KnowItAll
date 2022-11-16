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

// Get user by username
if (isset($_POST["getUser"])) {
    $username = $_POST["getUser"];
    $username = $conn -> real_escape_string($username);
    $username = htmlspecialchars($username);
    $result = $conn->query("SELECT `ID`, `name`, `email`, `admin`, `moderator`, `banned` FROM `user` WHERE `name` = '$username'");
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_array()) {
            array_push($returnArr, $row);
        }
    }
}

// Get all moderators
if (isset($_POST["getAllModerators"])) {
    $result = $conn->query("SELECT `ID`, `name`, `email`, `admin` FROM `user` WHERE `moderator` = '1'");
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_array()) {
            array_push($returnArr, $row);
        }
    }
}

// Promote User
if (isset($_POST['promoteUser'])) {
    $id = intval($_POST["promoteUser"]);
    $id = $conn -> real_escape_string($id);
    $id = htmlspecialchars($id);
    $result = $conn->query("UPDATE `user` SET `moderator` = 1 WHERE `ID` = $id");
}

// Demote User
if (isset($_POST['demoteUser'])) {
    $id = intval($_POST["demoteUser"]);
    $id = $conn -> real_escape_string($id);
    $id = htmlspecialchars($id);
    $result = $conn->query("UPDATE `user` SET `moderator` = -1 WHERE `ID` = $id");
}

// Ban User
if (isset($_POST['banUser'])) {
    $id = intval($_POST["banUser"]);
    $id = $conn -> real_escape_string($id);
    $id = htmlspecialchars($id);
    $result = $conn->query("UPDATE `user` SET `moderator` = -1, `banned` = 1 WHERE `ID` = $id");
}

// Unban User
if (isset($_POST['unbanUser'])) {
    $id = intval($_POST["unbanUser"]);
    $id = $conn -> real_escape_string($id);
    $id = htmlspecialchars($id);
    $result = $conn->query("UPDATE `user` SET `banned` = -1 WHERE `ID` = $id");
}

// Check if email exists
if (isset($_POST["checkEmail"])) {
    $checkEmail = strval($_POST["checkEmail"]);
    $checkEmail = $conn -> real_escape_string($checkEmail);
    $checkEmail = htmlspecialchars($checkEmail);
    $result = $conn->query("SELECT `email` FROM `user` WHERE EXISTS (SELECT `email` FROM `user` WHERE `email` = '$checkEmail')");
    if (empty($result->num_rows)) {
        array_push($returnArr, false);
    } else {
        array_push($returnArr, true);
    }
}

// Check if name exists
if (isset($_POST["checkUserName"])) {
    $checkName = strval($_POST["checkUserName"]);
    $checkName = $conn -> real_escape_string($checkName);
    $checkName = htmlspecialchars($checkName);
    $result = $conn->query("SELECT `name` FROM `user` WHERE EXISTS (SELECT `name` FROM `user` WHERE `name` = '$checkName')");
    if (empty($result->num_rows)) {
        array_push($returnArr, false);
    } else {
        array_push($returnArr, true);
    }
}

// Hash password
if (isset($_POST["hashPassword"])) {
    if (!$returnArr[0] && !$returnArr[1]) {
        $hashedPass = password_hash($_POST["hashPassword"], PASSWORD_DEFAULT);
        // push to database
        $name = strval($_POST["checkUserName"]);
        $email = strval($_POST["checkEmail"]);
        $name = $conn -> real_escape_string($name);
        $name = htmlspecialchars($name);
        $email = $conn -> real_escape_string($email);
        $email = htmlspecialchars($email);
        $conn->query("INSERT INTO `user` (`ID`, `name`, `email`, `password`, `banned`, `admin`, `moderator`)
                            VALUES (NULL, '$name', '$email', '$hashedPass', -1, -1, -1)");
    }
}

// Check if password is correct by email
if (isset($_POST["checkPasswordCorrect"])) {
    if ($returnArr[0]) {
        $passwordArray = array();
        $email = strval($_POST["checkEmail"]);
        $email = $conn -> real_escape_string($email);
        $email = htmlspecialchars($email);
        $result = $conn->query("SELECT `password` FROM `user` WHERE `email` = '$email'");
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_array()) {
                array_push($passwordArray, $row);
            }
        }
        // password verify
        if (password_verify(strval($_POST["checkPasswordCorrect"]), $passwordArray[0]["password"])) {
            array_push($returnArr, true);
            // save user in session
            $result = $conn->query("SELECT `ID`, `admin`, `moderator` FROM `user` WHERE `email` = '$email'");
            if ($result->num_rows > 0) {
                while ($row = $result->fetch_array()) {
                    array_push($returnArr, $row);
                    $_SESSION["loggedIn"] = $row[0];
                    if ($row[1] === '1') {
                        $_SESSION["admin"] = $row[1];
                    }
                    if ($row[2] === '1') {
                        $_SESSION["moderator"] = $row[2];
                    }
                }
            }
        } else {
            array_push($returnArr, false);
        }

    } else {
        array_push($returnArr, false);
    }
}

// Check if Password is correct by ID
if (isset($_POST["checkPasswordCorrectID"]) && isset($_SESSION["loggedIn"])) {
    $passwordArray = array();
    $id = intval($_SESSION["loggedIn"]);
    $id = $conn -> real_escape_string($id);
    $id = htmlspecialchars($id);
    $result = $conn->query("SELECT `password` FROM `user` WHERE `ID` = '$id'");
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_array()) {
            array_push($passwordArray, $row);
        }
    }
    // verify password
    if (password_verify(strval($_POST["checkPasswordCorrectID"]), $passwordArray[0]["password"])) {
        array_push($returnArr, true);
    } else {
        array_push($returnArr, false);
    }
}

// Change password (uses checkPasswordCorrectID)
if (isset($_POST["changePassword"])) {
    if ($returnArr[0]) {
        $hashedPass = password_hash($_POST["changePassword"], PASSWORD_DEFAULT);
        $id = intval($_SESSION["loggedIn"]);
        $id = $conn -> real_escape_string($id);
        $id = htmlspecialchars($id);
        $result = $conn->query("UPDATE `user` SET `password` = '$hashedPass' WHERE `ID` = '$id'");
    }
}

// Change username (uses checkPasswordCorrectID)
if (isset($_POST["changeUsername"])) {
    if ($returnArr[0]) {
        $newUserName = strval($_POST["changeUsername"]);
        $newUserName = $conn -> real_escape_string($newUserName);
        $newUserName = htmlspecialchars($newUserName);
        $id = intval($_SESSION["loggedIn"]);
        $id = $conn -> real_escape_string($id);
        $id = htmlspecialchars($id);
        $result = $conn->query("UPDATE `user` SET `name` = '$newUserName' WHERE `ID` = '$id'");
    }
}

// Log Out
if (isset($_POST["logOut"])) {
    unset($_SESSION["loggedIn"]);
    unset($_SESSION["admin"]);
    unset($_SESSION["moderator"]);
}

// Return The Result Array
echo json_encode($returnArr);
