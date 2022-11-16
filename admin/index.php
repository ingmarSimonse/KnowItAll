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
// not a admin
$alertOutput = '';
if (!isset($_SESSION['admin'])) {
    $alertOutput =
        '<script>
            alert("Je bent geen admin.");
            window.open("../", "_self").focus();
        </script>';
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
    <link rel="stylesheet" href="../styles/global.css">
    <script src="https://kit.fontawesome.com/fab5bc8fbc.js" crossorigin="anonymous"></script>
    <script src="../js/validateForm.js"></script>
    <link rel="shortcut icon" href="../images/icon.png"/>
    <title>KnowItAll</title>
    <script>
        let moderatorArray;
        window.onload = function () {
            // populate moderator list
            let moderatorHTML = document.getElementsByClassName('moderators')[0];
            let formData = new FormData();
            formData.append('getAllModerators', 'true');
            fetch('../php/operateUserDB.php', {method: "POST", body: formData})
                .then(response => response.text()).then(data => {
                let parsedResponse = JSON.parse(data);
                moderatorArray = parsedResponse;
                for (let i = 0; i < parsedResponse.length; i++) {
                    if (parsedResponse[i]['admin'] !== '1') {
                        moderatorHTML.innerHTML +=
                            '<div class="item">' +
                            `<p>${parsedResponse[i]['name']}</p>` +
                            `<p>${parsedResponse[i]['email']}</p>` +
                            `<p style="color: red; cursor: pointer" onclick="demoteUser(moderatorArray[` + i + `]['ID']);">Degraderen naar gebruiker</p>` +
                            '</div>';
                    }
                }
            });
        }

        // get user from input
        let userArray;
        function getUser(form) {
            let formResultHTML = document.getElementsByClassName('formResult')[0];
            let formData = new FormData();
            formData.append('getUser', form['userName'].value);
            fetch('../php/operateUserDB.php', {method: "POST", body: formData})
                .then(response => response.text()).then(data => {
                let parsedResponse = JSON.parse(data);
                userArray = parsedResponse;
                let itemOptions;
                if (parsedResponse[0]['admin'] === '1') {
                    itemOptions = '';
                } else if (parsedResponse[0]['moderator'] === '1') {
                    itemOptions =
                        `<p style="color: red; cursor: pointer" onclick="demoteUser(userArray[0]['ID']);">Degraderen naar gebruiker</p>`;
                } else {
                    itemOptions =
                        `<p style="color: green; cursor: pointer" onclick="promoteUser(userArray[0]['ID']);">Promoveren naar moderator</p>`;
                }
                if (parsedResponse[0]['banned'] === '1') {
                    itemOptions =
                        `<p style="color: green; cursor: pointer" onclick="unbanUser(userArray[0]['ID']);">Verbanning opheffen</p>`;
                } else {
                    itemOptions +=
                        `<p style="color: red; cursor: pointer" onclick="banUser(userArray[0]['ID']);">Verbannen</p>`;
                }
                formResultHTML.innerHTML =
                    '<div class="item">' +
                        `<p>${parsedResponse[0]['name']}</p>` +
                        `<p>${parsedResponse[0]['email']}</p>` +
                        itemOptions +
                    '</div>';
                return false;
            });
            return false;
        }

        // promote user
        function promoteUser(userID) {
            if (confirm('Weet je zeker dat je deze gebruiker wil promoten tot Moderator?')) {
                let formData = new FormData();
                formData.append('promoteUser', userID);
                fetch('../php/operateUserDB.php', {method: "POST", body: formData})
                    .then(response => response.text()).then(data => {
                    let parsedResponse = JSON.parse(data);
                    window.open('./', '_self').focus();
                });
            }
        }

        // demote user
        function demoteUser(userID) {
            if (confirm('Weet je zeker dat je deze moderator wil demoten tot gebruiker?')) {
                let formData = new FormData();
                formData.append('demoteUser', userID);
                fetch('../php/operateUserDB.php', {method: "POST", body: formData})
                    .then(response => response.text()).then(data => {
                    let parsedResponse = JSON.parse(data);
                    window.open('./', '_self').focus();
                });
            }
        }

        // ban user
        function banUser(userID) {
            if (confirm('Weet je zeker dat je deze gebruiker wil verbannen?')) {
                let formData = new FormData();
                formData.append('banUser', userID);
                fetch('../php/operateUserDB.php', {method: "POST", body: formData})
                    .then(response => response.text()).then(data => {
                    let parsedResponse = JSON.parse(data);
                    window.open('./', '_self').focus();
                });
            }
        }

        // unban user
        function unbanUser(userID) {
            if (confirm('Weet je zeker dat je de verbanning wil opheffen?')) {
                let formData = new FormData();
                formData.append('unbanUser', userID);
                fetch('../php/operateUserDB.php', {method: "POST", body: formData})
                    .then(response => response.text()).then(data => {
                    let parsedResponse = JSON.parse(data);
                    window.open('./', '_self').focus();
                });
            }
        }
    </script>
    <style>
        .content {
            margin-top: 40px;
        }
        * {
            text-align: center;
        }
        form {
            margin-bottom: 10px;
        }
        .formResult {
            margin-bottom: 20px;
        }
        .item {
            margin-top: 5px;
        }
    </style>
</head>
<body>
<?=$alertOutput?>
<a href="../">
    <img class="logo" src="../images/logo.png" alt="x">
</a>
<div class="nav">
    <?=$navOutput?>
</div>
<div class="content">
    <form action='./' method='post' onsubmit='return getUser(this);'>
        <label for='userName'>Gebruiker zoeken:<br>
            <input type='text' name='userName' value='' required maxlength='100' placeholder='Zoek een gebruiker...'><br>
        </label>
        <input type='submit' value='Zoeken'>
    </form>
    <div class="formResult"></div>
    <div class="moderators">
        <h2>Alle Moderators:</h2>
    </div>
</div>
</body>
</html>
