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
function validateInput($input, $fieldToChange, $minLength, $maxLength, $todaysDate)
{
    switch ($fieldToChange) {
        case $fieldToChange == "email":
            return validateStringLength($input, 1, 64);
        case $fieldToChange == "password":
            return validatePassword($input, 12, 32);
        case $fieldToChange == "firstname":
            return validateName($input, 2, 16);
        case $fieldToChange == "surname":
            return validateName($input, 2, 20);
        case $fieldToChange == "number":
            return validatePhoneNumber($input);
        case $fieldToChange == "dob":
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
    // echo "String length: " . strlen($field);
    if (strlen($field) < $minlength) {
        // wasn't a valid length, return a help message:
        return "Input length: " . strlen($field) . ", minimum length: " . $minlength;
    } elseif (strlen($field) > $maxlength) {
        // wasn't a valid length, return a help message:
        return "Input length: " . strlen($field) . ", maximum length: " . $maxlength;
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

//
//
function validatePassword($field, $minLength, $maxLength)
{
    if (strlen($field) == 0) {
        return "Generate random password";
    } else {
        return validateStringLength($field, $minLength, $maxLength);
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
    $arrayOfErrors[0] = validateStringLength($username, 1, 20); // +
    $arrayOfErrors[1] = validateStringLength($email, 1, 64);
    $arrayOfErrors[2] = validatePassword($password, 12, 32);
    $arrayOfErrors[3] = validateName($firstname, 2, 16); // see line below +
    $arrayOfErrors[4] = validateName($surname, 2, 20); // shortest last name I've ever seen was a girl called "Ng" +
    $arrayOfErrors[5] = validatePhoneNumber($number); // +
    $arrayOfErrors[6] = validateDate($DOB); // +
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
    $tempString = substr($tempString, 6, strlen($tempString)); // removes 'change' from beginning of string
    return strtolower($tempString);
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
function determineFieldType($superGlobalName, &$minLength, &$maxLength)
{
    switch ($superGlobalName) {
        case $superGlobalName == "email":
            $minLength = 3;
            $maxLength = 64;
            return "email";
        case $superGlobalName == "password":
            $maxLength = 32;
            return "password";
        case $superGlobalName == "firstname":
            $minLength = 2;
            $maxLength = 16;
            return "text";
        case $superGlobalName == "surname":
            $minLength = 3;
            $maxLength = 24;
            return "text";
        case $superGlobalName == "number":
            $minLength = 11;
            $maxLength = 11;
            return "text";
        case $superGlobalName == "dob":
            return "date";
        default:
            return "";
    }
}

function determineMinMaxVals($field, &$minLength, &$maxLength, $todaysDate)
{
    switch ($field) {
        case $field == "email":
            $minLength = 3;
            $maxLength = 64;
            return "email";
        case $field == "password":
            $maxLength = 32;
            return "password";
        case $field == "firstname":
            $minLength = 2;
            $maxLength = 16;
            return "text";
        case $field == "surname":
            $minLength = 3;
            $maxLength = 24;
            return "text";
        case $field == "number":
            $minLength = 11;
            $maxLength = 11;
            return "text";
        case $field == "dob":
            $minLength = calcEarliestDate($todaysDate);
            $maxLength = calcLatestDate($todaysDate);
            return "date";
        default:
            return "";
    }
}

//
//
function calcEarliestDate($todaysDate)
{
    $minDate = $todaysDate;
    $minDate = substr($todaysDate, 0, 4);
    $minDate = (int) $minDate;
    $minYear = $minDate - 120;
    $minDate = substr($todaysDate, 4, strlen($todaysDate));
    $minDate = $minYear . $minDate;
    return $minDate;
}

//
//
function calcLatestDate($todaysDate)
{
    $maxDate = $todaysDate;
    $maxDate = substr($todaysDate, 0, 4);
    $maxDate = (int) $maxDate;
    $maxYear = $maxDate - 13;
    $maxDate = substr($todaysDate, 4, strlen($todaysDate));
    $maxDate = $maxYear . $maxDate;
    return $maxDate;
}

//
//
function initEmptyArray(&$array, $size)
{
    for ($i = 0; $i <= $size; $i ++) {
        $array[$i] = "";
    }
}

// this function gets the username of the selected user from the session superglobal, gets all their information using an SQL query, displays it in a table
// then shows the options to either change the password or delete the account
// this function is written by me:
function printUserData($connection, $origin, $username)
{
    echo "<br>";

    $query = "SELECT * FROM users WHERE username = '$username'"; // +
    $result = mysqli_query($connection, $query); // +

    echo "User's details:";
    echo "<table border ='1'>";
    echo "<tr><td>username</td><td>email</td><td>password</td><td>firstname</td><td>surname</td><td>number</td><td>dob</td></tr>";

    while ($row = mysqli_fetch_assoc($result)) {
        echo "<tr><td>{$row['username']}</td><td>{$row['email']}</td><td>{$row['password']}</td><td>{$row['firstname']}</td><td>{$row['surname']}</td><td>{$row['number']}</td><td>{$row['dob']}</td></tr>";
    }
    echo "</table>";

    $currentURL = $_SERVER['REQUEST_URI'];
    $currentURL = substr($currentURL, 1, strlen($currentURL));

    echo "<br>";
    echo "<a href =$origin?username=$username&editAccountDetails=true&changeEmail=true>Change email</a>";
    echo " ";
    echo "<a href =$origin?username=$username&editAccountDetails=true&changePassword=true>Change password</a>";
    echo " ";
    echo "<a href =$origin?username=$username&editAccountDetails=true&changeFirstname=true>Change firstname</a>";
    echo " ";
    echo "<a href =$origin?username=$username&editAccountDetails=true&changeSurname=true>Change surname</a>";
    echo " ";
    echo "<a href =$origin?username=$username&editAccountDetails=true&changeNumber=true>Change number</a>";
    echo " ";
    echo "<a href =$origin?username=$username&editAccountDetails=true&changedob=true>Change date of birth</a>";
    echo " ";
    echo "<a href =$origin?username=$username&editAccountDetails=true&deleteAccount=true>Delete user account</a>";
}

// this function gets the select user's username from the session superglobal, asks the admin to fill in a new password for the user
// then updates the user's password via an SQL query
// this function is written by me
function changeUserDetails($connection, $fieldToChange, $fieldType, $minLength, $maxLength)
{
    if (isset($_POST['newInput'])) {

        $currentUsername = $_GET['username'];

        echo "Change user details:";
        echo "<br>";

        $todaysDate = date('Y-m-d'); // get current date: +
        $newInput = sanitise($_POST['newInput'], $connection);
        $input_val = validateInput($newInput, $fieldToChange, $minLength, $maxLength, $todaysDate);

        if ($input_val == "Generate random password") {
            $newInput = generateAlphanumericString();
            $input_val = validateInput($newInput, $fieldToChange, $minLength, $maxLength);
        }

        if ($input_val == "") {
            if ($fieldType == "password") {
                $newInput = encryptInput($newInput);
                echo "<br>";
                echo "Insert a new password if your browser hasn't automatically saved your password";
            }
            $query = "UPDATE users SET $fieldToChange='$newInput' WHERE username = '$currentUsername'";
            $result = mysqli_query($connection, $query); // +

            if ($result) {
                echo "<br>";
                echo ucfirst($fieldToChange) . " changed";
            }
        } else {
            echo "<br>";
            echo "Updating field failed: " . $input_val;
        }
    } else {
        showFieldForm($fieldToChange, $fieldType, $minLength, $maxLength);
    }
}

//
//
function showFieldForm($fieldToChange, $fieldType, $minLength, $maxLength)
{
    $currentURL = $_SERVER['REQUEST_URI'];
    $fieldToDisplay = ucfirst($fieldToChange);

    if ($fieldToDisplay == "Dob") {

        $todaysDate = date('Y-m-d'); // get current date: +

        $minDate = calcEarliestDate($todaysDate);
        $maxDate = calcLatestDate($todaysDate);

        echo <<<_END
        <form action="$currentURL" method="post">
          Please fill in the following fields:<br>
          $fieldToDisplay: <input type="$fieldType" min=$minDate max=$maxDate name="newInput">
          <br>
          <input type="submit" value="Submit">
        </form>
        _END;
    } else {
        echo <<<_END
        <form action="$currentURL" method="post">
          Please fill in the following fields:<br>
          $fieldToDisplay: <input type="$fieldType" name="newInput">
          <br>
          <input type="submit" value="Submit">
        </form>
        _END;
    }
}

// this function gets the username of the selected user from the session superglobal, then deletes the account via an SQL query
// this function is written by me:
function deleteAccount($connection, $username)
{
    if ($username == "admin") {
        echo "The admin account cannot be deleted";
    } else {
        echo "<br>";
        echo "Are you sure you want to delete the account " . $username . "? ";
        echo "<a href ={$_SERVER['REQUEST_URI']}&confirmDeletion=true>Yes</a>";
        echo " ";
        echo "<a href =admin.php?username=$username>Cancel</a>";

        if (isset($_GET["confirmDeletion"])) {
            $query = "DELETE FROM users WHERE username = '$username'";
            $result = mysqli_query($connection, $query); // +

            echo "<br>";
            echo "Account deleted";
        }
    }
}

//
//
function echoVariable($variableToEcho)
{
    echo "<br>";
    echo "Variable value: " . $variableToEcho;
    echo "<br>";
}

?>