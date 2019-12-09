<?php

// execute the header script:
require_once "header.php";

// checks the session variable named 'loggedInSkeleton'
// take note that of the '!' (NOT operator) that precedes the 'isset' function
if (!isset($_SESSION['loggedInSkeleton'])) {
	// user isn't logged in, display a message saying they must be:
	echo "You must be logged in to view this page.<br>";
} else {
	$connection = mysqli_connect($dbhost, $dbuser, $dbpass, $dbname);

	$origin = "account.php";
	$username = $_SESSION["username"];

	printUserData($connection, $origin, $username);
	printOptionsToEdit($origin, $username);

	$currentURL = $_SERVER['REQUEST_URI'];

	if ($currentURL !== "/account.php") {
		enactEdit($connection);
	}

	mysqli_close($connection);
}

// finish of the HTML for this page:
require_once "footer.php";
