<?php

// Things to notice:
// The main job of this script is to execute a SELECT statement to find the user's profile information (then display it)

// execute the header script:
require_once "header.php";

// checks the session variable named 'loggedInSkeleton'
// take note that of the '!' (NOT operator) that precedes the 'isset' function
if (! isset($_SESSION['loggedInSkeleton'])) {
    // user isn't logged in, display a message saying they must be:
    echo "You must be logged in to view this page.<br>";
} // the user must be signed-in, show them suitable page content
else {

    // now read their account data from the table...
    // connect directly to our database (notice 4th argument - database name):
    $connection = mysqli_connect($dbhost, $dbuser, $dbpass, $dbname);

    printUserData($connection, "account.php", $_SESSION["username"]);

    // we're finished with the database, close the connection:
    mysqli_close($connection);
}

// finish off the HTML for this page:
require_once "footer.php";
?>