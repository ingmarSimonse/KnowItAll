<?php
session_start();
// set Up nav
$navArray = array(
    array("Home", "../"),
    array("Weetjes", "./"),
);
if (isset($_SESSION["loggedIn"])) {
    array_push($navArray, array("Profiel", "../profile"));
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
// set selected date
if (!isset($_SESSION["selectedDay"])) {
    $_SESSION["selectedDay"] = intval(date('d'));
    $_SESSION["selectedMonth"] = intval(date('n'));
}
// days in month array
$monthArray = array(31, 29, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31);
// next or previous day or month
if (isset($_GET["nextDay"])) {
    if ($_SESSION["selectedDay"] + 1 > $monthArray[$_SESSION["selectedMonth"] - 1]) {
        $_SESSION["selectedDay"] = 1;
    } else {
        $_SESSION["selectedDay"]++;
    }
} elseif (isset($_GET["previousDay"])) {
    if ($_SESSION["selectedDay"] - 1 < 1) {
        $_SESSION["selectedDay"] = $monthArray[$_SESSION["selectedMonth"] - 1];
    } else {
        $_SESSION["selectedDay"]--;
    }
} elseif (isset($_GET["nextMonth"])) {
    if ($_SESSION["selectedMonth"] + 1 > 12) {
        $_SESSION["selectedMonth"] = 1;
    } else {
        $_SESSION["selectedMonth"]++;
    }
    if ($_SESSION["selectedDay"] > $monthArray[$_SESSION["selectedMonth"] - 1]) {
        $_SESSION["selectedDay"] = $monthArray[$_SESSION["selectedMonth"] - 1];
    }
} elseif (isset($_GET["previousMonth"])) {
    if ($_SESSION["selectedMonth"] - 1 < 1) {
        $_SESSION["selectedMonth"] = 12;
    } else {
        $_SESSION["selectedMonth"]--;
    }
    if ($_SESSION["selectedDay"] > $monthArray[$_SESSION["selectedMonth"] - 1]) {
        $_SESSION["selectedDay"] = $monthArray[$_SESSION["selectedMonth"] - 1];
    }
}
// date output
$dayOutput = "";
$monthOutput = "";
if ($_SESSION["selectedDay"] < 10) {
    $dayOutput = "0" . $_SESSION["selectedDay"];
} else {
    $dayOutput = $_SESSION["selectedDay"];
}
if ($_SESSION["selectedMonth"] < 10) {
    $monthOutput = "0" . $_SESSION["selectedMonth"];
} else {
    $monthOutput = $_SESSION["selectedMonth"];
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
    <script>
        let triviaArray;
        let page = 0;
        let triviaHTML = document.getElementsByClassName('allTrivia');
        window.onload = function () {
            let selectedDay = document.getElementById('day').innerHTML.trim();
            let selectedMonth = document.getElementById('month').innerHTML.trim();
            // Get trivia from day
            let formData = new FormData();
            formData.append('allTriviaDay', 'true');
            formData.append('value', `${selectedDay}-${selectedMonth}`);
            fetch('../php/operateTriviaDB.php', {method: "POST", body: formData})
                .then(response => response.text()).then(data => {
                let parsedResponse = JSON.parse(data);
                triviaArray = [...parsedResponse];
                populateAllTrivia();
            });
        }

        // Next Page
        function nextPage() {
            let lastPage = Math.ceil(triviaArray.length / 4) - 1;
            if (page + 1 > lastPage) {
                page = 0;
                populateAllTrivia();
            } else {
                page++;
                populateAllTrivia();
            }
        }

        // Previous Page
        function previousPage() {
            let lastPage = Math.ceil(triviaArray.length / 4) - 1;
            if (page - 1 < 0) {
                page = lastPage;
                populateAllTrivia();
            } else {
                page--;
                populateAllTrivia();
            }
        }

        // populate current page
        function populateAllTrivia() {
            const currentTrivia = page * 4;
            // check if array index is empty
            if (triviaArray[currentTrivia] !== undefined) {
                triviaHTML[0].innerHTML = '';
                for (let i = currentTrivia; i < (currentTrivia + 4) && i < triviaArray.length; i++) {
                    // Read more substring
                    let triviaString = triviaArray[i]['triviaText'];
                    let triviaString1 = triviaArray[i]['triviaText'].substr(0, 150);
                    let triviaString2 = triviaArray[i]['triviaText'].substr(150);
                    if (triviaString2.length > 20) {
                        triviaString = `${triviaString1}<span id="dots">... Lees Meer</span><span id="more">${triviaString2}</span>`;
                    }
                    // output
                    triviaHTML[0].innerHTML +=
                        '<div class="trivia" onclick="readMore(this)">' +
                            '<div class="left">' +
                                `<h2>${triviaArray[i]['title']}</h2>` +
                                `<h3>${triviaArray[i]['day']}</h3>` +
                                `<p class="trivia_post"><b>${triviaArray[i]["year"]}</b> ${triviaString}</p>` +
                                `<p class="trivia_user">Ingestuurd door ${triviaArray[i][10]}</p>` +
                            '</div>' +
                            '<div class="right">' +
                                `<img src="../images/trivia_images/${triviaArray[i]['imagePath']}" alt="x">` +
                            '</div>' +
                        '</div>';
                }
            } else {
                triviaHTML[0].innerHTML = 'Deze dag heeft geen weetjes';
            }
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
<a href="../">
    <img class="logo" src="../images/logo.png" alt="x">
</a>
<div class="nav">
    <?=$navOutput?>
</div>
<div class="content">
    <div class="selectDate">
        <a href="./?nextDay=true">
            <div>
                <i class="fa-solid fa-angle-up"></i>
            </div>
        </a>
        <div></div>
        <a href="./?nextMonth=true">
            <div>
                <i class="fa-solid fa-angle-up"></i>
            </div>
        </a>
        <div id="day">
            <?=$dayOutput?>
        </div>
        <div>
            -
        </div>
        <div id="month">
            <?=$monthOutput?>
        </div>
        <a href="./?previousDay=true">
            <div>
                <i class="fa-solid fa-angle-down"></i>
            </div>
        </a>
        <div></div>
        <a href="./?previousMonth=true">
            <div>
                <i class="fa-solid fa-angle-down"></i>
            </div>
        </a>
    </div>
    <div class="pager pager_left" onclick="previousPage();">
        <i class="fa-solid fa-angle-left"></i>
    </div>
    <div class="pager pager_right" onclick="nextPage();">
        <i class="fa-solid fa-angle-right"></i>
    </div>
    <div class="allTrivia"></div>
</div>
</body>
</html>
