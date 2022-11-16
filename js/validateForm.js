// Validate register form
function validateRegisterForm(form) {
    // Validate email
    const validateEmail = /^(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3})|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
    if (form['email'].value === '') {
        alert('Vul een E-mail in.');
        return false;
    } else if (!validateEmail.test(form['email'].value.toLowerCase())) {
        alert('Vul een geldig E-mail adres in.');
        return false;
    }

    // Validate userName
    // no username given
    if (form['userName'].value === '') {
        alert('Vul een Gebruikersnaam in.');
        return false;
    }
    // username too long
    if (form['userName'].value.length > 50) {
        alert('Vul een kortere Gebruikersnaam in.');
        return false;
    }

    // Validate password
    // Validate lowercase letters
    const lowerCaseLetters = /[a-z]/g;
    if(!form['password'].value.match(lowerCaseLetters)) {
        alert('Het wachtwoord moet minimaal een kleine letter bevatten.');
        return false;
    }
    // Validate capital letters
    const upperCaseLetters = /[A-Z]/g;
    if(!form['password'].value.match(upperCaseLetters)) {
        alert('Het wachtwoord moet minimaal een hoofdletter bevatten.');
        return false;
    }
    // Validate numbers
    const numbers = /[0-9]/g;
    if(!form['password'].value.match(numbers)) {
        alert('Het wachtwoord moet minimaal een nummer bevatten');
        return false;
    }
    // Validate length
    if(form['password'].value.length < 8) {
        alert('Het wachtwoord moet minimaal acht characters bevatten. ' + form['password'].value.length + '/8');
        return false;
    }

    // Check if email or userName exists and hash password
    let formData = new FormData();
    formData.append('checkEmail', form['email'].value);
    formData.append('checkUserName', form['userName'].value);
    formData.append('hashPassword', form['password'].value);
    fetch('../php/operateUserDB.php', {method: "POST", body: formData})
        .then(response => response.text()).then(data => {
        let parsedResponse = JSON.parse(data);
        if (parsedResponse[0]) {
            alert('E-mail is al geregistreerd.');
            return false;
        } else if (parsedResponse[1]) {
            alert('Gebruikersnaam bestaat al.');
            return false;
        } else {
            window.open('./?login=true', '_self').focus();
            return false;
        }
    });
    return false;
}

// Validate Login Form
function validateLoginForm(form) {
    // Check if email and password are correct
    let formData = new FormData();
    formData.append('checkEmail', form['email'].value);
    formData.append('checkPasswordCorrect', form['password'].value);
    fetch('../php/operateUserDB.php', {method: "POST", body: formData})
        .then(response => response.text()).then(data => {
        let parsedResponse = JSON.parse(data);
        if (parsedResponse[0] && parsedResponse[1]) {
            window.open('../', '_self').focus();
        } else {
            alert('email en of wachtwoord zijn incorrect');
        }
        return false;
    });
    return false;
}

// Validate password form
function validatePasswordForm(form) {
    // Check if new password is valid
    // Validate lowercase letters
    const lowerCaseLetters = /[a-z]/g;
    if(!form['newPassword'].value.match(lowerCaseLetters)) {
        alert('Het wachtwoord moet minimaal een kleine letter bevatten.');
        return false;
    }
    // Validate capital letters
    const upperCaseLetters = /[A-Z]/g;
    if(!form['newPassword'].value.match(upperCaseLetters)) {
        alert('Het wachtwoord moet minimaal een hoofdletter bevatten.');
        return false;
    }
    // Validate numbers
    const numbers = /[0-9]/g;
    if(!form['newPassword'].value.match(numbers)) {
        alert('Het wachtwoord moet minimaal een nummer bevatten');
        return false;
    }
    // Validate length
    if(form['newPassword'].value.length < 8) {
        alert('Het wachtwoord moet minimaal acht characters bevatten. ' + form['newPassword'].value.length + '/8');
        return false;
    }
    // Check if password is correct
    let formData = new FormData();
    formData.append('checkPasswordCorrectID', form['password'].value);
    formData.append('changePassword', form['newPassword'].value);
    fetch('../php/operateUserDB.php', {method: "POST", body: formData})
        .then(response => response.text()).then(data => {
        let parsedResponse = JSON.parse(data);
        if (parsedResponse[0]) {
            window.open('../', '_self').focus();
        } else {
            alert("Verkeerd wachtwoord");
        }
        return false;
    });
    return false;
}

// validate change username form
function validateUserForm(form) {
    // Validate userName
    // no username given
    if (form['userName'].value === "") {
        alert('Vul een Gebruikersnaam in.');
        return false;
    }
    // username too long
    if (form['userName'].value.length > 50) {
        alert('Vul een kortere Gebruikersnaam in.');
        return false;
    }
    // Check if password is correct
    let formData = new FormData();
    formData.append('checkPasswordCorrectID', form['password'].value);
    formData.append('changeUsername', form['userName'].value);
    fetch('../php/operateUserDB.php', {method: "POST", body: formData})
        .then(response => response.text()).then(data => {
        let parsedResponse = JSON.parse(data);
        if (parsedResponse[0]) {
            window.open('../', '_self').focus();
        } else {
            alert("Verkeerd wachtwoord");
        }
        return false;
    });
    return false;
}

// log out
function logOut() {
    if (confirm('Weet je zeker dat je uit wil loggen?')) {
        // Log out in session
        let formData = new FormData();
        formData.append('logOut', 'true');
        fetch('../php/operateUserDB.php', {method: "POST", body: formData})
            .then(response => response.text()).then(data => {
            let parsedResponse = JSON.parse(data);
            window.open('../', '_self').focus();
        });
    } else {
        window.open('./', '_self').focus();
    }
}

// Validate Trivia Form
function validateTriviaForm(form) {
    // Validate date
    let dateArray = form['date'].value.split('-');
    for (let i = 0; i < dateArray.length; i++) {
        dateArray[i] = dateArray[i];
    }
    dateArray = dateArray.reverse();
    let day = dateArray[0] + '-' + dateArray[1];
    let year = dateArray[2];

    // Validate title
    // No title given
    if (form['title'].value === "") {
        alert('Vul een titel in.');
        return false;
    }
    // Title too long
    if (form['title'].value.length > 30) {
        alert('Vul een kortere Titel in.');
        return false;
    }

    // Validate Trivia
    // Trivia too short
    if (form['trivia'].value.length < 30) {
        alert('Maak je weetje langer.');
        return false;
    }
    // Trivia too long
    if (form['trivia'].value.length > 500) {
        alert('Maak je weetje korter.');
        return false;
    }

    // Validate Image
    // Check if image is a image
    let imgCheck = form['image'].value.split('.');
    imgCheck = imgCheck.reverse();
    imgCheck = imgCheck[0];
    if (imgCheck !== 'jpeg' && imgCheck !== 'jpg' && imgCheck !== 'png' && imgCheck !== 'bmp' && imgCheck !== 'gif') {
        alert('Upload een afbeeldinig.');
        return false;
    }
    // Image too big
    let fileSize = (form['image'].files[0].size / 1024 / 1024).toFixed(2);
    if (fileSize > 5) {
        alert('De afbeelding is te groot (mb)');
        return false;
    }

    // Insert
    let image = form['image'].files[0];
    let formData = new FormData();
    formData.append('day', day);
    formData.append('year', year);
    formData.append('title', form['title'].value);
    formData.append('trivia', form['trivia'].value);
    formData.append('image', image);
    fetch('../php/operateTriviaDB.php', {method: "POST", body: formData})
        .then(response => response.text()).then(data => {
            let parsedResponse = JSON.parse(data);
            if (parsedResponse[0] === false) {
                alert('Je bent verbannen van het insturen van weetjes.')
            } else if (parsedResponse[1]) {
                window.open('../', '_self').focus();
            } else {
                alert('er is iets fout gegaan.');
            }
    });

    return false;
}