<?php
// Things to notice:
// You need to add code to this script to implement the admin functions and features
// Notice that the code not only checks whether the user is logged in, but also whether they are the admin, before it displays the page content
// When an admin user is verified, you can implement all the admin tools functionality from this script, or distribute them over multiple pages - your choice
// execute the header script:
require_once "header.php";

// checks the session variable named 'loggedInSkeleton'
// take note that of the '!' (NOT operator) that precedes the 'isset' function
if (!isset($_SESSION['loggedInSkeleton'])) {
	// user isn't logged in, display a message saying they must be:
	echo "You must be logged in to view this page.<br>";
} // the user must be signed-in, show them suitable page content
else {
	// creates connection to MYSQLi DB:
	$connection = mysqli_connect($dbhost, $dbuser, $dbpass, $dbname);

	// if the connection fails, we need to know, so allow this exit:
	if (!$connection) {
		die("Connection failed: " . $mysqli_connect_error);
	}

	echo "<a href = init_database.php> Reinitialise database </a>";
	echo "<br><br>";

	// only display the page content if this is the admin account (all other users get a "you don't have permission..." message):
	if ($_SESSION['username'] == "admin") {

		echo "Click to create a new account: <br>";

		echo "<a href =admin.php?createAccount=true>Create user account</a>";
		echo "<br><br>";

		if (isset($_GET['createAccount'])) {
			initCreateAccount($connection);
		} else {
			displayListOfUsers($connection);
			if (isset($_GET['username'])) {
				$origin = "admin.php";
				$username = $_GET['username'];

				printUserData($connection, $origin, $username);

				$currentURL = $_SERVER['REQUEST_URI'];

				if ($currentURL !== "/admin.php") {
					printOptionsToEdit($origin, $username);
					enactEdit($connection);
				}
			}
		}
	} else {
		echo "You don't have permission to view this page <br>";
	}
}
// finish off the HTML for this page:
require_once "footer.php";

function initCreateAccount($connection)
{
	$arrayOfAccountCreationErrors = array();
	initEmptyArray($arrayOfAccountCreationErrors, 6);

	// default values we show in the form:
	$username = "";
	$firstname = "";
	$surname = "";
	$password = "";
	$email = "";
	$number = "";
	$dob = "";

	$todaysDate = date('Y-m-d'); // get current date: +

	if (isset($_POST['username'])) {
		// account tried to be created
		// sanitise dataa then attempt to create account:
		sanitiseUserData($connection, $username, $email, $password, $firstname, $surname, $number, $dob);
		createAccount($connection, $username, $email, $password, $firstname, $surname, $number, $dob, $todaysDate, $arrayOfAccountCreationErrors);
	} else {
		// show the sign up form
		displayCreateAccountForm($username, $email, $password, $firstname, $surname, $number, $dob, $todaysDate, $arrayOfAccountCreationErrors);
	}
}

function displayListOfUsers($connection)
{
	// queries mysql table for list of users, outputs list of users to table
	// if no errors are encountered
	$query = "SELECT username FROM users ORDER BY username ASC";
	$result = mysqli_query($connection, $query);

	echo "Or click a name from the table to view user's data:";
	echo "<br>";

	echo "<table>";
	echo "<tr><th>username</th></tr>";

	while ($row = mysqli_fetch_row($result)) {
		// if row hyperlink is clicked, set superglobal with user's name
		echo "<tr><td><a href =?username={$row[0]}>{$row[0]}</a></td></tr>"; // turns row result into hyperlink
	}
	echo "</table>";
}

?>