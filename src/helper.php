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

//
//
function validateInput($input, $inputType, $minLength, $maxLength)
{  
    switch ($inputType) {
        case $inputType == "email":
            return validateStringLength($input, $minLength, $maxLength);
        case $inputType == "password":
            return validateStringLength($input, $minLength, $maxLength);
        case $inputType == "firstname":
            return validateName($input, $minLength, $maxLength);
        case $inputType == "surname":
            return validateName($input, $minLength, $maxLength);
        case $inputType == "number":
            return validateNumber($input);
        case $inputType == "dob":
            return validateDate($input); // "email";
        default:
            return "";
    }
}

// if the input is contains only non-numbers and is the correct length then return an empty string, if the data is invalid return a help message
// this entire function is made by me:
function validateName($field, $minlength, $maxlength) // master function +
{
    $errors = "";
    $errors = $errors . checkIsNonNumeric($field);
    $errors = $errors . validateStringLength($field, $minlength, $maxlength);

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
    $lengthOfCharArray = count($charArray);

    for ($i = 0; $i < $lengthOfCharArray; $i ++) {
        if (is_numeric($charArray[$i]) == true) {
            return "Must not contain any numbers ";
        }
    }
    return "";
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
function validateDate($field)
{
    
    $todaysDate = date('Y-m-d');
        
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
function generateAlphanumericString()
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

// this function validates all user inputs, and adds each validation message to an array of errors
// this was created by me:
function createArrayOfErrors($username, $email, $password, $firstname, $surname, $number, $DOB, $todaysDate, &$arrayOfErrors)
{
    $username_val = validateStringLength($username, 1, 20); // +
    $email_val = validateStringLength($email, 1, 64);
    $password_val = validateStringLength($password, 12, 32);
    $firstname_val = validateName($firstname, 2, 16); // see line below +
    $surname_val = validateName($surname, 2, 20); // shortest last name I've ever seen was a girl called "Ng" +
    $number_val = validatePhoneNumber($number); // +
    $DOB_val = validateDate($DOB); // +

    $arrayOfErrors[0] = $username_val;
    $arrayOfErrors[1] = $email_val;
    $arrayOfErrors[2] = $password_val;
    $arrayOfErrors[3] = $firstname_val;
    $arrayOfErrors[4] = $surname_val;
    $arrayOfErrors[5] = $number_val;
    $arrayOfErrors[6] = $DOB_val;
}

// this function concatenates each valuae in the array of errors to create one large error, then returns this value
// this was created by me:
function concatValidationMessages($username, $email, $password, $firstname, $surname, $number, $DOB, $todaysDate, $arrayOfErrors)
{
    createArrayOfErrors($username, $email, $password, $firstname, $surname, $number, $DOB, $todaysDate, $arrayOfErrors); // +
    $numberOfErrors = count($arrayOfErrors); // +

    $errors = "";
    for ($i = 0; $i < $numberOfErrors; $i ++) {
        $errors = $errors . $arrayOfErrors[$i];
    }

    return $errors;
}

//
//
function getSuperGlobalName($inputURL)
{
    $tempString = $inputURL;
    
    while (containsAmpersand($tempString)) {
        $tempString = removeAmpersand($tempString);
    }
    
    $tempString = substr($tempString, 0, strlen($tempString) - 5); // removes '=true' from end of string
    return substr($tempString, 6, strlen($tempString)); // removes 'change' from beginning of string
}

function containsAmpersand($inputString)
{
    $arrayOfChars = str_split($inputString);
    $inputLength = count($arrayOfChars);
    
    for ($i = 0; $i < $inputLength; $i ++) {
        if ($arrayOfChars[$i] == '&') {
            return true;
        }
    }
    return false;
}

//
//
function removeAmpersand($inputString)
{
    $stringLength = strlen($inputString);
    $locationOfAmpersand = getAmpersandLocation($inputString);
    return substr($inputString, $locationOfAmpersand + 1, $stringLength); // trim variable
}

//
//
function getAmpersandLocation($inputString)
{
    $arrayOfChars = str_split($inputString);
    $inputLength = count($arrayOfChars);
    
    for ($i = 0; $i <= $inputLength; $i ++) {
        if ($arrayOfChars[$i] == '&') {
            return $i;
        }
    }
    return 127;
}

//
//
function determineFieldType($trimmedSuperGlobal, &$minLength, &$maxLength)
{
    switch ($trimmedSuperGlobal) {
        case $trimmedSuperGlobal == "email":
            $minLength = 3;
            $maxLength = 64;
            return "email";
        case $trimmedSuperGlobal == "password":
            $maxLength = 32;
            return "password";
        case $trimmedSuperGlobal == "firstname":
            $minLength = 2;
            $maxLength = 16;
            return "text";
        case $trimmedSuperGlobal == "surname":
            $minLength = 3;
            $maxLength = 24;
            return "text";
        case $trimmedSuperGlobal == "number":
            $minLength = 11;
            $maxLength = 11;
            return "text";
        case $trimmedSuperGlobal == "dob":
            return "date";
        default:
            return "";
    }
}

?>