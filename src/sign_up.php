<?php

// Things to notice:
// The main job of this script is to execute an INSERT statement to add the submitted username, password and email address
// However, the assignment specification tells you that you need more fields than this for each user.
// So you will need to amend this script to include them. Don't forget to update your database (create_data.php) in tandem so they match
// This script does client-side validation using "password","text" inputs and "required","maxlength" attributes (but we can't rely on it happening!)
// we sanitise the user's credentials - see helper.php (included via header.php) for the sanitisation function
// we validate the user's credentials - see helper.php (included via header.php) for the validation functions
// the validation functions all follow the same rule: return an empty string if the data is valid...
// ... otherwise return a help message saying what is wrong with the data.
// if validation of any field fails then we display the help messages (see previous) when re-displaying the form

// execute the header script:
require_once "header.php";

$arrayOfAccountCreationErrors = array();
initEmptyArray($arrayOfAccountCreationErrors, 6);

// global: +
$todaysDate = date('Y-m-d'); // get current date: +

// message to output to user:

// checks the session variable named 'loggedInSkeleton'
if (isset($_SESSION['loggedInSkeleton'])) {
    // user is already logged in, just display a message:
    echo "You are already logged in, please log out if you wish to create a new account<br>";
} else {

    $arrayOfAccountErrors = array();
    initEmptyArray($arrayOfAccountErrors, 6);

    // default values we show in the form:
    $username = "";
    $email = "";
    $password = "";
    $firstname = ""; // +
    $surname = ""; // +
    $number = ""; // +
    $dob = ""; // +

    if (isset($_POST['username'])) {
        // user just tried to sign up:

        // connect directly to our database (notice 4th argument) we need the connection for sanitisation:
        $connection = mysqli_connect($dbhost, $dbuser, $dbpass, $dbname);

        // if the connection fails, we need to know, so allow this exit:
        if (! $connection) {
            die("Connection failed: " . $mysqli_connect_error);
        }

        // SANITISATION (see helper.php for the function definition)
        // cannot be put into function as _POST requires superglobals
        $username = sanitise($_POST['username'], $connection);
        $email = sanitise($_POST['email'], $connection);
        $password = sanitise($_POST['password'], $connection);
        $firstname = sanitise($_POST['firstname'], $connection); // +
        $surname = sanitise($_POST['surname'], $connection); // +
        $number = sanitise($_POST['number'], $connection); // +
        $dob = sanitise($_POST['dob'], $connection); // +

        createAccount($connection, $username, $email, $password, $firstname, $surname, $number, $dob, $todaysDate, $arrayOfAccountCreationErrors);
    } else {

        // just a normal visit to the page, show the signup form:
        displayCreateAccountForm($username, $email, $password, $firstname, $surname, $number, $dob, $todaysDate, $arrayOfAccountErrors);
    }
}

// finish off the HTML for this page:
require_once "footer.php";

?>