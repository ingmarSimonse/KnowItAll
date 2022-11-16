<?php
session_start();
// set Up nav
$navArray = array(
    array("Home", "./"),
    array("Weetjes", "./trivia"),
);
if (isset($_SESSION["loggedIn"])) {
    array_push($navArray, array("Profiel", "./profile"));
} else {
    array_push($navArray, array("Registreer", "./register"));
}
if (isset($_SESSION["admin"])) {
    array_push($navArray, array("Admin", "./admin"));
}
if (isset($_SESSION["moderator"]) || isset($_SESSION["admin"])) {
    array_push($navArray, array("Moderator", "./moderator"));
}
$navOutput = "";
for ($i = 0; $i < count($navArray); $i++) {
    $navOutput .= "<a href='" . $navArray[$i][1] . "'><div>" . $navArray[$i][0] . "</div></a>";
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
    <link rel="shortcut icon" href="./images/icon.png"/>
    <link rel="stylesheet" href="./styles/global.css">
    <link rel="stylesheet" href="./styles/responsive.css">
    <link rel="stylesheet" href="./styles/styles.css">
    <script>
        window.onload = function () {
            let triviaHTML = document.getElementsByClassName('trivia')[0];
            // Get random trivia from day
            let date = new Date();
            let day = date.getDate().toString();
            let month = date.getMonth() + 1;
            month = "0" + month;
            let formData = new FormData();
            formData.append('random', 'true');
            formData.append('value', `${day}-${month}`);
            fetch('./php/operateTriviaDB.php', {method: "POST", body: formData})
                .then(response => response.text()).then(data => {
                let parsedResponse = JSON.parse(data);
                // Read more substring
                let triviaString = parsedResponse['triviaText'];
                let triviaString1 = parsedResponse['triviaText'].substr(0, 200);
                let triviaString2 = parsedResponse['triviaText'].substr(200);
                if (triviaString2.length > 20) {
                    triviaString = `${triviaString1}<span id="dots">... Lees Meer</span><span id="more">${triviaString2}</span>`;
                }
                triviaHTML.innerHTML =
                    '<div class="left">' +
                    `<h2>${parsedResponse["title"]}</h2>` +
                    `<h3>${parsedResponse["day"]}</h3>` +
                    `<p class="trivia_post"><b>${parsedResponse["year"]}</b> ${triviaString}</p>` +
                    `<p class="trivia_user">Ingestuurd door ${parsedResponse[10]}</p>` +
                    '</div>' +
                    '<div class="right">' +
                    `<img src="./images/trivia_images/${parsedResponse["imagePath"]}" alt="x">` +
                    '</div>';
            });
        }

        // Read more function
        function readMore(e) {
            let dots = e.querySelector('#dots');
            let moreHTML = e.querySelector('#more');

            if (dots.style.display === "none") {
                dots.style.display = "inline";
                moreHTML.style.display = "none";
            } else {
                dots.style.display = "none";
                moreHTML.style.display = "inline";
            }
        }
    </script>
    <title>KnowItAll</title>
</head>
<body>
<a href="./">
    <img class="logo" src="./images/logo.png" alt="x">
</a>
<div class="nav">
    <?=$navOutput?>
</div>
<div class="content">
    <h1>Sport Weetje van de dag</h1>
    <div class="trivia" onclick="readMore(this)"></div>
</div>
</body>
</html>
