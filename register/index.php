<?php
session_start();
// set Up nav
$navArray = array(
    array("Home", "../"),
    array("Weetjes", "../trivia"),
);
if (isset($_SESSION["loggedIn"])) {
    array_push($navArray, array("Profiel", "../profile"));
} else {
    array_push($navArray, array("Registreer", "./"));
}
if (isset($_SESSION["admin"])) {
    array_push($navArray, array("Admin", "../admin"));
}
if (isset($_SESSION["moderator"]) || isset($_SESSION["admin"])) {
    array_push($navArray, array("Moderator", "../moderator"));
}
$navOutput = "";
for ($i = 0; $i < count($navArray); $i++) {
    $navOutput .= "<a href='" . $navArray[$i][1] . "'><div>" . $navArray[$i][0] . "</div></a>";
}
//Log in or register
$formOutput = "";
if (isset($_GET["login"])) {
    $formOutput =
    "<a href='./?register=true'>
        <p>Registreer</p>
    </a>
    <form action='./' method='post' onsubmit='return validateLoginForm(this);'>
        <label for='email'>E-mail:<br>
            <input type='email' name='email' value='' required maxlength='100' placeholder='Vul hier je E-mail in..'><br>
        </label>
        <label for='password'>Wachtwoord:<br>
            <input type='password' name='password' value='' required maxlength='100'><br>
        </label>
        <input type='submit' value='inloggen'>
    </form>";
/*    <label for='stayLoggedIn'>Blijf ingelogd:<br>
            <input type='checkbox' name='stayLoggedIn' value=''><br>
      </label>*/
} elseif(isset($_GET["register"])) {
    $formOutput =
    "<a href='./?login=true'>
        <p>Log in</p>
    </a>
    <form action='./' method='post' onsubmit='return validateRegisterForm(this);'>
        <label for='userName'>Gebruikersnaam:<br>
            <input type='text' name='userName' value='' required maxlength='100' placeholder='Vul hier je gebruikersnaam in..'><br>
        </label>
        <label for='email'>E-mail:<br>
            <input type='email' name='email' value='' required maxlength='100' placeholder='Vul hier je E-mail in...'><br>
        </label>
        <label for='password'>Wachtwoord:<br>
            <input type='password' name='password' value='' required maxlength='100'><br>
        </label>
        <input type='submit' value='Registreren'>
    </form>";
} else {
    $formOutput =
    "<a href='./?login=true'>
        <p>Log in</p>
    </a>
    <form action='./' method='post' onsubmit='return validateRegisterForm(this);'>
        <label for='userName'>Gebruikersnaam:<br>
            <input type='text' name='userName' value='' required maxlength='100' placeholder='Vul hier je gebruikersnaam in..'><br>
        </label>
        <label for='email'>E-mail:<br>
            <input type='email' name='email' value='' required maxlength='100' placeholder='Vul hier je E-mail in..'><br>
        </label>
        <label for='password'>Wachtwoord:<br>
            <input type='password' name='password' value='' required maxlength='100'><br>
        </label>
        <input type='submit' value='Registreren'>
    </form>";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta http-equiv="content-type" content="text/html;" charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Rubik&display=swap" rel="stylesheet">
    <link rel="shortcut icon" href="../images/icon.png"/>
    <link rel="stylesheet" href="../styles/global.css">
    <link rel="stylesheet" href="./styles.css">
    <script src="https://kit.fontawesome.com/fab5bc8fbc.js" crossorigin="anonymous"></script>
    <script src="../js/validateForm.js"></script>
    <title>KnowItAll</title>
</head>
<body>
<a href="../">
    <img class="logo" src="../images/logo.png" alt="x">
</a>
<div class="nav">
    <?=$navOutput?>
</div>
<div class="content">
    <?=$formOutput?>
</div>
</body>
</html>
