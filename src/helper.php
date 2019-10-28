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

// this entire function is made by me:
function validateString($field, $minlength, $maxlength) // master function +
{
    $errors = "";
    $errors = $errors . validateStringLength($field, $minlength, $maxlength);
    $errors = $errors . checkIsNonNumeric($field);
}

// if the data is valid return an empty string, if the data is invalid return a help message
function validateStringLength($field, $minlength, $maxlength) // + edit function name
{
    if (strlen($field) < $minlength) {
        // wasn't a valid length, return a help message:
        return "Minimum length: " . $minlength;
    } elseif (strlen($field) > $maxlength) {
        // wasn't a valid length, return a help message:
        return "Maximum length: " . $maxlength;
    }

    // data was valid, return an empty string:
    return "";
}

// this entire function is made by me:
function checkIsNonNumeric($field)
{
    $charArray = str_split($field);

    for ($i = 0; $i < count($charArray); $i ++) {
        if (is_numeric($charArray[$i]) == false) {
            return "Must not contain any numbers";
        } else {
            return "";
        }
    }
}

// if the input is 11 digits long return an empty string, if the data is invalid return a help message
function validatePhoneNumber($field) // +
{
    if (is_numeric($field)) {
        if (strlen($field) == "11") {
            return "";
        } else {
            return "Phone number must be 11 digits long, yours was " . strlen($field) . " digits long";
        }
    } else {
        return "Phone number must not contain any characters";
    }
}
// all other validation functions should follow the same rule:
// if the data is valid return an empty string, if the data is invalid return a help message
// ...

?>