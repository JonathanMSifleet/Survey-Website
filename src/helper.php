<?php

// Things to notice:
// This script holds the sanitisation function that we pass all our user data to
// This script holds the validation functions that double-check our user data is valid
// You can add new PHP functions to validate different kinds of user data (e.g., emails, dates) by following the same convention:
// if the data is valid return an empty string, if the data is invalid return a help message
// You are encouraged to create/add your own PHP functions here to make frequently used code easier to handle

// function to sanitise (clean) user data:
function sanitise($str, $connection)
{
    if (get_magic_quotes_gpc()) {
        // just in case server is running an old version of PHP with "magic quotes" running:
        $str = stripslashes($str);
    }

    // escape any dangerous characters, e.g. quotes:
    $str = mysqli_real_escape_string($connection, $str);
    // ensure any html code is safe by converting reserved characters to entities:
    $str = htmlentities($str);
    // return the cleaned string:
    return $str;
}

// if the input is contains only non-numbers and is the correct length then return an empty string, if the data is invalid return a help message
// this entire function is made by me:
function validateString($field, $minlength, $maxlength) // master function +
{
    $errors = "";
    $errors = $errors . validateStringLength($field, $minlength, $maxlength);
    $errors = $errors . checkIsNonNumeric($field);
    return $errors;
}

// this function checks if an inputted email address is valid, and then returns an error message if it isn't
// this function is made by me:
function validateEmail($field, $minLength, $maxLength)
{
    $errors = "";
    $errors = $errors . validateStringLength($field, $minLength, $maxLength);
    $errors = $errors . checkIsEmail($field);
    return $errors;
}

// if the data is valid return an empty string, if the data is invalid return a help message
function validateStringLength($field, $minlength, $maxlength) // + edit function name
{
    if (strlen($field) < $minlength) {
        // wasn't a valid length, return a help message:
        return "Minimum length: " . $minlength;
    } elseif (strlen($field) > $maxlength) {
        // wasn't a valid length, return a help message:
        return "Password length: " . strlen($field) . " Maximum length: " . $maxlength;
    } else {
    // data was valid, return an empty string:
    return "";
    }
}

// this function checks if an inputted password is equal to 0 chracters, then validates the string length and returns any error messages
// this function is made by me:
function validatePassword($field, $minlength, $maxlength)
{
    $isZero = checkIfLengthZero($field);

    if ($isZero) {
        return 0;     
    } else {
        return validateStringLength($field, $minlength, $maxlength);
    }
}

// this function checks if an input is 0 chracters long and returns a message, if the input is larger than 0 characters
// send a different message
// this function is made by me:
function checkIfLengthZero($field)
{
    if (strlen($field) == 0) {
        return true;
    } else {
        return false;
    }
}

// if the input contains the @ symbol then return an empty string, if the data is invalid return a help message
// this function is made by me:
function checkIsEmail($field)
{
    if (strpos($field, '@') == false) {
        return "Email must contain an '@'";
    } else {
        return "";
    }
}

// if the input is contains only numbers then return an empty string, if the data is invalid return a help message
// this entire function is made by me:
function checkIsNonNumeric($field)
{
    $charArray = str_split($field);

    for ($i = 0; $i < count($charArray); $i ++) {
        if (is_numeric($charArray[$i]) == true) {
            return "Must not contain any numbers";
        } else {
            return "";
        }
    }
}

// if the input is 11 digits long return an empty string, if the data is invalid return a help message
// function is completely written by me:
function validatePhoneNumber($field)
{

    // could add functionality that only allows specific number prefixs
    // e.g. 01, 07, 08, etc... but https://en.wikipedia.org/wiki/Telephone_numbers_in_the_United_Kingdom
    // A number can start with every combination of 0X. e.g. 01, 02, 03, 04 etc...
    if (is_numeric($field)) { // check if phone number is numerical
        if (strlen($field) == "11") { // check if phone number is correct length
            return "";
        } else {
            return "Phone number must be 11 digits long, yours was " . strlen($field) . " digits long";
        }
    } else {
        return "Phone number must not contain any characters";
    }
}

// if the input date is less than 13 years ago or more than 120, return an empty string, if the data is invalid return a help message
// this function is written by me:
function validateDate($field, $todaysDate)
{
    $inputYear = substr($field, 0, 4);
    $inputYear = (int) $inputYear;

    $todaysYear = substr($todaysDate, 0, 4);
    $todaysYear = (int) $todaysYear;

    // $error = ("Todays date - 120 years = " . ($todaysDate-120) . " Input year: " . $inputYear);
    // return $error;

    if ($todaysYear - $inputYear > 120) {
        return "You must be less than 120 years old";
    } else if ($todaysYear - $inputYear < 13) {
        return "GDPR requires that data cannot be stored about people younger than 13";
    } else {
        return "";
    }
}

// this function encrypts a user input
// this function is written by me:
function encryptInput($input)
{
    return password_hash($input, PASSWORD_BCRYPT); // leave third parameter empty to generate random salt every time +
}

// this function generates 32 random alphanumeric characters, converts them to ascii, combines the combination of characters, then returns the combination
// this function is written by me:
function generatePassword()
{
    $charArray = createArrayOfUsableCharacters();
    $lengthOfCharArray = count($charArray) - 1;

    $tempPassword[] = "";

    for ($i = 0; $i <= 31; $i ++) {
        $randNumber = rand(0, $lengthOfCharArray);
        $tempPassword[$i] = chr($charArray[$randNumber]);
    }

    $finalPassword = "";

    for ($i = 0; $i <= 31; $i ++) {
        $finalPassword = $finalPassword . $tempPassword[$i];
    }

    return $finalPassword;
}

// this function creates an array of all alphanumeric characters
// this function is written by me:
function createArrayOfUsableCharacters()
{
    $charArray[] = "";

    $j = 0;

    for ($i = 48; $i <= 57; $i ++) {
        $charArray[$j] = $i;
        $j ++;
    }

    for ($i = 65; $i <= 90; $i ++) {
        $charArray[$j] = $i;
        $j ++;
    }

    for ($i = 97; $i <= 122; $i ++) {
        $charArray[$j] = $i;
        $j ++;
    }

    return $charArray;
}
/
function createArrayOfValidatedInputs($username, $password, $email, $firstname, $surname, $number, $DOB, $todaysDate) {
    $username_val = validateStringLength($username, 1, 20); // +
    $email_val = validateStringLength($email, 1, 64); // this line will validate the email as a string, but maybe you can do a better job...
    $firstname_val = validateString($firstname, 2, 16); // see line below +
    $surname_val = validateString($surname, 2, 20); // shortest last name I've ever seen was a girl called "Ng" +
    $number_val = validatePhoneNumber($number); // +
    $DOB_val = validateDate($DOB, $todaysDate); // +    
    
    return array($username_val, $email_val, $firstname_val, $surname_val, $number_val, $DOB_val);
    
}

function sanitiseInputs(&$username, &$firstname, &$surname, &$password, &$email, &$passowrd, &$number, &$DOB, $todaysDate, $connection)
{
    // adding '&' in front of a variable calls variable by reference so function can modify value +
    
    // take copies of the credentials the user submitted, and sanitise (clean) them:
    $username = sanitise($_POST['username'], $connection);
    $firstname = sanitise($_POST['firstname'], $connection); // +
    $surname = sanitise($_POST['surname'], $connection); // +
    $password = sanitise($_POST['password'], $connection);
    $email = sanitise($_POST['email'], $connection);
    $number = sanitise($_POST['number'], $connection); // +
    $DOB = sanitise($_POST['DOB'], $connection); // +
}

?>