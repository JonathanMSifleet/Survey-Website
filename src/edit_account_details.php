<?php

// Things to notice:
// This script will let a logged-in user VIEW their account details and allow them to UPDATE those details
// The main job of this script is to execute an INSERT or UPDATE statement to create or update a user's account information...
// ... but only once the data the user supplied has been validated on the client-side, and then sanitised ("cleaned") and validated again on the server-side
// It's your job to add these steps into the code
// Both sign_up.php and sign_in.php do client-side validation, followed by sanitisation and validation again on the server-side -- you may find it helpful to look at how they work
// HTML5 can validate all the account data for you on the client-side
// The PHP functions in helper.php will allow you to sanitise the data on the server-side and validate *some* of the fields...
// There are fields you will want to add to allow the user to update them...
// ... you'll also need to add some new PHP functions of your own to validate email addresses, telephone numbers and dates

// execute the header script:
require_once "header.php";

// checks the session variable named 'loggedInSkeleton'
// take note that of the '!' (NOT operator) that precedes the 'isset' function
if (! isset($_SESSION['loggedInSkeleton'])) {
    // user isn't logged in, display a message saying they must be:
    echo "You must be logged in to view this page.<br>";
} else {
    $username = $_SESSION["username"];

    $connection = mysqli_connect($dbhost, $dbuser, $dbpass, $dbname);

    printUserData($connection, "edit_account_details.php", $username);

    if (isset($_GET['editAccountDetails'])) {

        $superGlobalName = getSuperGlobalName($_SERVER['REQUEST_URI']);

        $minLength = null;
        $maxLength = null;
        $fieldType = determineFieldType($superGlobalName, $minLength, $maxLength);

        echo "<br>";

        if ($superGlobalName !== "") {
            changeUserDetails($connection, $superGlobalName, $fieldType, $minLength, $maxLength);
        } // end of if
    }

    mysqli_close($connection);
}

// finish of the HTML for this page:
require_once "footer.php";
?>