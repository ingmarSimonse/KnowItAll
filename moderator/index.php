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
// not a moderator
$alertOutput = '';
if (!isset($_SESSION['moderator'])) {
    $alertOutput =
        '<script>
            alert("Je bent geen moderator.");
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
        let page = 0;
        let triviaArray = [];
        window.onload = function () {
            // get recent trivia
            let formData = new FormData();
            formData.append('getRecentTrivia', 'true');
            fetch('../php/operateTriviaDB.php', {method: "POST", body: formData})
                .then(response => response.text()).then(data => {
                triviaArray = JSON.parse(data);
                populateTrivia();
            });
        }
        // Populate Trivia
        function populateTrivia() {
            let triviaHTML = document.getElementsByClassName('trivia')[0];
            let currentTrivia = page * 5;
            triviaHTML.innerHTML = '';
            for (let i = currentTrivia; i < (currentTrivia + 5) && i < triviaArray.length; i++) {
                // approved
                let approved = '';
                if (triviaArray[i]['approved'] === '-1') {
                    approved = 
                        '<p style="color: red;">niet geaccepteerd</p>';
                } else if (triviaArray[i]['approved'] === '0') {
                    approved =
                        '<p style="color: blue;">in behandeling</p>' +
                        '<form action="./" method="post" onsubmit="return sendFeedback(this, triviaArray[' + i + '][' + "'ID'" + ']);">' +
                            '<textarea name="feedbackText" id="" cols="30" rows="10"></textarea>' +
                            '<select name="accept" id="accept">' +
                                '<option value="true">Accepteren</option>' +
                                '<option value="false">Niet accepteren</option>' +
                            '</select>' +
                            '<input type="submit" value="Verstuur feedback">' +
                        '</form>';
                } else {
                    approved = '<p style="color: green;">geaccepteerd</p>';
                }
                // output
                triviaHTML.innerHTML +=
                    '<div class="item">' +
                        '<div>' +
                            `<h2>${triviaArray[i]['title']}</h2>` +
                            `<p>${triviaArray[i]['day']}</p>` +
                            `<p>${triviaArray[i]['year']}</p>` +
                            `<p>${triviaArray[i]['triviaText']}</p>` +
                            `<p>Ingestuurd door: ${triviaArray[i][10]}</p>` +
                            approved +
                            `<p>Feedback: ${triviaArray[i]['feedback']}</p>` +
                    '</div>' +
                        `<img src="../images/trivia_images/${triviaArray[i]['imagePath']}" alt="x">` +
                    '</div>';
            }
        }
        // Next Page
        function nextPage() {
            let lastPage = Math.ceil(triviaArray.length / 5) - 1;
            if (page + 1 > lastPage) {
                page = 0;
                populateTrivia();
            } else {
                page++;
                populateTrivia();
            }
        }
        // Previous Page
        function previousPage() {
            let lastPage = Math.ceil(triviaArray.length / 5) - 1;
            if (page - 1 < 0) {
                page = lastPage;
                populateTrivia();
            } else {
                page--;
                populateTrivia();
            }
        }

        // Send Feedback
        function sendFeedback(form, triviaID) {
            let formData = new FormData();
            formData.append('sendFeedback', 'true');
            formData.append('feedbackText', form['feedbackText'].value);
            formData.append('accept', form['accept'].value);
            formData.append('triviaID', triviaID);
            fetch('../php/operateTriviaDB.php', {method: "POST", body: formData})
                .then(response => response.text()).then(data => {
                let parsedResponse = JSON.parse(data);
                window.open('./', '_self').focus();
                return false;
            });
            return false;
        }
    </script>
    <style>
        .content {
            margin: 40px 40px 0 40px;
            padding: 0 0 50px 0;
        }
        .pager_container {
            display: flex;
        }
        .pager {
            width: 50%;
            display: flex;
            justify-content: center;
            align-items: center;
            font-size: 1.7rem;
        }
        .pager:hover {
            opacity: 0.6;
            cursor: pointer;
        }
        .item {
            display: flex;
            justify-content: space-between;
            margin-top: 20px;
        }
        .item img {
            width: 25%;
            object-fit: contain;
            margin-left: 5px;
        }
        @media only screen and (max-width: 650px) {
            .item {
                flex-direction: column;
            }
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
    <div class="pager_container">
        <div class="pager pager_left" onclick="previousPage();">
            <i class="fa-solid fa-angle-left"></i>
        </div>
        <div class="pager pager_right" onclick="nextPage();">
            <i class="fa-solid fa-angle-right"></i>
        </div>
    </div>
    <div class="trivia">
    </div>
</div>
</body>
</html>
