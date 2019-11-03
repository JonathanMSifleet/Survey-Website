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

// default values we show in the form:
$email = "";

// strings to hold any validation error messages:
$email_val = "";

// should we show the set profile form?:
$show_account_form = false;

// message to output to user:
$message = "";

// checks the session variable named 'loggedInSkeleton'
// take note that of the '!' (NOT operator) that precedes the 'isset' function
if (! isset($_SESSION['loggedInSkeleton'])) {
    // user isn't logged in, display a message saying they must be:
    echo "You must be logged in to view this page.<br>";
} 
elseif (isset($_POST['email'])) {
    $username = $_GET["username"];

    echo "<br>";

    $query = "SELECT * FROM users WHERE username = '$username'"; // +
    $result = mysqli_query($connection, $query); // +

    echo "User's details:";
    echo "<table border ='1'>";
    echo "<tr><td>username</td><td>firstname</td><td>surname</td><td>password</td><td>email</td><td>number</td><td>dob</td></tr>";

    while ($row = mysqli_fetch_assoc($result)) {
        echo "<tr><td>{$row['username']}</td><td>{$row['firstname']}</td><td>{$row['surname']}</td><td>{$row['password']}</td><td>{$row['email']}</td><td>{$row['number']}</td><td>{$row['dob']}</td></tr>";
    }
    echo "</table>";
}

// display our message to the user:
echo $message;

// finish of the HTML for this page:
require_once "footer.php";
?>