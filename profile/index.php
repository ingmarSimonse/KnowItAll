<?php
session_start();
// set Up nav
$navArray = array(
    array("Home", "../"),
    array("Weetjes", "../trivia"),
);
if (isset($_SESSION["loggedIn"])) {
    array_push($navArray, array("Profiel", "./"));
} else {
    array_push($navArray, array("Registreer", "../register"));
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
// not logged in
$alertOutput = '';
if (!isset($_SESSION['loggedIn'])) {
    $alertOutput =
        '<script>
            alert("Je bent niet ingelogd.");
            window.open("../", "_self").focus();
        </script>';
}
// set up options
$profileOutput = "";
if (isset($_GET["changePassword"])) {
    $profileOutput = "
    <form action='./' method='post' onsubmit='return validatePasswordForm(this);'>
        <label for='password'>Wachtwoord:<br>
            <input type='password' name='password' value='' required maxlength='100'><br>
        </label>
        <label for='newPassword'>Nieuw Wachtwoord:<br>
            <input type='password' name='newPassword' value='' required maxlength='100'><br>
        </label>
        <input type='submit' value='verander wachtwoord'>
    </form>";
} elseif (isset($_GET["changeUsername"])) {
    $profileOutput = "
    <form action='./' method='post' onsubmit='return validateUserForm(this);'>
        <label for='userName'>Nieuwe Gebruikersnaam:<br>
            <input type='text' name='userName' value='' required maxlength='100' placeholder='Vul hier je nieuwe gebruikersnaam in..'><br>
        </label>
        <label for='password'>Wachtwoord:<br>
            <input type='password' name='password' value='' required maxlength='100'><br>
        </label>
        <input type='submit' value='verander gebruikersnaam'>
    </form>";
} elseif (isset($_GET["logOut"])) {
    $profileOutput = "
    <form action='./' method='post' onsubmit='return logOut();'>
        <input type='submit' value='uitloggen'>
    </form>";
} elseif (isset($_GET["addTrivia"])) {
    $profileOutput = "
        <form action='./' method='post' onsubmit='return validateTriviaForm(this);'>
            <label for='date'>Datum: <br>
                <input type='date' name='date' id='date' required><br>
            </label>
            <label for='title'>Titel:<br>
                <input type='text' name='title' value='Typ hier de titel...' required maxlength='50'><br>
            </label>
            <label for='trivia'>Weetje:<br>
                <textarea name='trivia' id='trivia_id' cols='50' rows='10' required maxlength='500'>Typ hier het weeetje...</textarea>
<!--
                <input type='text' name='trivia' value='Typ hier het weetje...' required maxlength='500'><br>
-->
            </label>
            <label for='image'>Afbeelding:<br>
                <input type='file' name='image' required><br>
            </label>
            <input type='submit' value='verstuur weetje'>
        </form>";
} elseif (isset($_GET["changeTrivia"])) {
    $profileOutput = "
        <form action='./' method='post' onsubmit='return validateTriviaForm(this);'>
            <label for='date'>Datum: <br>
                <input type='date' name='date' id='date' value='" . $_GET["date"] . "' required><br>
            </label>
            <label for='title'>Titel:<br>
                <input type='text' name='title' value='" . $_GET["title"] . "' required maxlength='50'><br>
            </label>
            <label for='trivia'>Weetje:<br>
                <textarea name='trivia' id='trivia_id' cols='50' rows='10' required maxlength='500'>" . $_GET["triviaText"] . "</textarea>
            </label>
            <label for='image'>Afbeelding:<br>
                <input type='file' name='image' required><br>
            </label>
            <input type='submit' value='verstuur weetje'>
            <p>Feedback: " . $_GET["feedback"] . "</p>
        </form>";
} elseif (isset($_GET["allTrivia"])) {
    $profileOutput = "
     <div class='triviaHTML'></div>
    <script>getProfileTrivia();</script>";
} else {
    $profileOutput = "
    <div class='options'>
        <a href='./?changePassword=true'><p>verander wachtwoord</p></a>
        <a href='./?changeUsername=true'><p>verander gebruikersnaam</p></a>
        <a href='./?logOut=true'><p>uitloggen</p></a>
        <a href='./?addTrivia=true'><p>weetje insturen</p></a>
        <a href='./?allTrivia=true'><p>mijn weetjes</p></a>
    </div>";
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
    <script src="https://kit.fontawesome.com/fab5bc8fbc.js" crossorigin="anonymous"></script>
    <link rel="shortcut icon" href="../images/icon.png"/>
    <link rel="stylesheet" href="./styles.css">
    <link rel="stylesheet" href="../styles/global.css">
    <link rel="stylesheet" href="./responsive.css">
    <script src="../js/validateForm.js"></script>
    <title>KnowItAll</title>
    <script>
        let triviaArray;
        let page = 0;
        function getProfileTrivia() {
            let formData = new FormData();
            formData.append('allTriviaUser', 'true');
            fetch('../php/operateTriviaDB.php', {method: "POST", body: formData})
                .then(response => response.text()).then(data => {
                let parsedResponse = JSON.parse(data);
                triviaArray = [...parsedResponse];
                populateProfileTrivia();
            });
        }

        function populateProfileTrivia() {
            const currentTrivia = page * 2;
            let triviaHTML = document.getElementsByClassName('triviaHTML');
            // check if array index is empty
            if (triviaArray[currentTrivia] !== undefined) {
                triviaHTML[0].innerHTML =
                    '<div class="pager pager_left" onclick="previousPage();">' +
                        '<i class="fa-solid fa-angle-left"></i>' +
                    '</div>' +
                    '<div class="pager pager_right" onclick="nextPage();">' +
                        '<i class="fa-solid fa-angle-right"></i>' +
                    '</div>';
                for (let i = currentTrivia; i < (currentTrivia + 2) && i < triviaArray.length; i++) {
                    // Read more substring
                    let triviaString = triviaArray[i]['triviaText'];
                    let triviaString1 = triviaArray[i]['triviaText'].substr(0, 150);
                    let triviaString2 = triviaArray[i]['triviaText'].substr(150);
                    if (triviaString2.length > 20) {
                        triviaString = `${triviaString1}<span id="dots"></span><span id="more">${triviaString2}</span>`;
                    }
                    // approved
                    let approved = '';
                    if (triviaArray[i]['approved'] === '-1') {
                        approved = '<p class="not_approved">niet geaccepteerd</p>';
                    } else if (triviaArray[i]['approved'] === '0') {
                        approved = '<p class="pending">in behandeling</p>';
                    } else {
                        approved = '<p class="approved">geaccepteerd</p>';
                    }
                    // output
                    triviaHTML[0].innerHTML +=
                        '<div class="trivia" onclick="editTrivia(triviaArray[' + i + ']);">' +
                            `<h2>${triviaArray[i]['title']}</h2>` +
                            `<h3>${triviaArray[i]['day']}</h3>` +
                            `<p class="trivia_post"><b>${triviaArray[i]['year']}</b> ${triviaString}</p>` +
                            `${approved}` +
                            `<p class="trivia_feedback">${triviaArray[i]['feedback']}</p>` +
                        '</div>';
                }
            } else {
                triviaHTML[0].innerHTML = 'Je hebt nog geen weetjes ingestuurd';
            }
        }

        // Next Page
        function nextPage() {
            let lastPage = Math.ceil(triviaArray.length / 2) - 1;
            if (page + 1 > lastPage) {
                page = 0;
                populateProfileTrivia();
            } else {
                page++;
                populateProfileTrivia();
            }
        }

        // Previous Page
        function previousPage() {
            let lastPage = Math.ceil(triviaArray.length / 2) - 1;
            if (page - 1 < 0) {
                page = lastPage;
                populateProfileTrivia();
            } else {
                page--;
                populateProfileTrivia();
            }
        }

        // edit trivia
        function editTrivia(e) {
            let date = e['day'].split('-');
            date.reverse();
            date.unshift(e['year']);
            date = date.join('-');
            window.open('./?changeTrivia=true&date=' + date + '&feedback=' + e['feedback'] +
                '&title=' + e['title'] + '&triviaText=' + e['triviaText'], '_self').focus();
        }
    </script>
</head>
<body>
<?=$alertOutput?>
<a href="../" style="z-index: 20;">
    <img class="logo" src="../images/logo.png" alt="x">
</a>
<div class="nav">
    <?=$navOutput?>
</div>
<div class="content">
    <?=$profileOutput?>
</div>
</body>
</html>
