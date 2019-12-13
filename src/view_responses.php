<?php

// execute the header script:
require_once "header.php";

if (!isset($_SESSION['loggedInSkeleton'])) {
	// user isn't logged in, display a message saying they must be:
	echo "You must be logged in to view this page.<br>";
} // the user must be signed-in, show them suitable page content
else {

	// only display the page content if this is the admin account (all other users get a "you don't have permission..." message):
	$connection = mysqli_connect($dbhost, $dbuser, $dbpass, $dbname);

	// if the connection fails, we need to know, so allow this exit:
	if (!$connection) {
		die("Connection failed: " . mysqli_connect_error());
	}

	echo "<h3>My responses:</h3>";

	// display response table:
	displayResponseTable($connection);

	if(isset($_GET['response'])) {
		deleteUserResponse($connection, $_GET['response']);
	}

}

// finish of the HTML for this page:
require_once "footer.php";

// displays table of responses:
function displayResponseTable($connection)
{

	// gets a list of all surveys the user has responded to:
	$query = "SELECT DISTINCT s.title, s.username, s.surveyID FROM surveys s INNER JOIN questions q USING(surveyID) INNER JOIN responses r USING(questionID) WHERE r.username = '{$_SESSION['username']}' ORDER BY s.title ASC";
	$result = mysqli_query($connection, $query);

	if ($result) {
		// displays table:
		echo "<table>";
		echo "<tr><th>Survey name</th><th>Survey author</th><th>My responses</th><th>Delete response</th></tr>";

		while ($row = mysqli_fetch_row($result)) {
			echo "<tr><td>{$row[0]}</td><td>{$row[1]}</td><td><a href = answer_survey.php?surveyID={$row[2]}&questionsAnswered=0>Edit response</a></td><td><a href = view_responses.php?response={$row[2]}>Delete response</a></td></tr>";
		}
		echo "</table>";
	} else {
		echo mysqli_error($connection);
	}
}

?>