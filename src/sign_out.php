<?php

// execute the header script:
require_once "header.php";

// checks the session variable named 'loggedInSkeleton'
// take note that of the '!' (NOT operator) that precedes the 'isset' function
if (!isset($_SESSION['loggedInSkeleton'])) {
	// user isn't logged in, display a message saying they must be:
	echo "You must be logged in to view this page.<br>";
} // the user must be signed-in, proceed with ending the session
// and clearing session cookies and any others you may have added
else {
	signOut();
	echo "You have been logged out, please <a href='sign_in.php'>click here</a><br>";
}

// finish of the HTML for this page:
require_once "footer.php";

?>