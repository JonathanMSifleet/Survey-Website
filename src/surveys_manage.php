<?php
// execute the header script:
require_once "header.php";
// checks the session variable named 'loggedInSkeleton'
// take note that of the '!' (NOT operator) that precedes the 'isset' function
if (!isset($_SESSION['loggedInSkeleton'])) {
	// user isn't logged in, display a message saying they must be:
	echo "You must be logged in to view this page.<br>";
} // the user must be signed-in, show them suitable page content
else {
	$connection = mysqli_connect($dbhost, $dbuser, $dbpass, $dbname);
	// if the connection fails, we need to know, so allow this exit:
	if (!$connection) {
		die("Connection failed: " . mysqli_connect_error());
	}

	// display create question prompt:
	echo "<a href = create_survey.php>Create a survey</a>";
	echo "<br><br>";
	echo "<h3>Surveys:</h3>";

	$username = $_SESSION['username'];

	// determine if user is admin:
	if ($username == "admin") {
		$userIsAdmin = true;
	} else {
		$userIsAdmin = false;
	}

	// if user is admin, display different results:
	if ($userIsAdmin) {
		$query = "SELECT surveyID, username, title, topic FROM surveys ORDER BY username ASC";
	} else {
		$query = "SELECT surveyID, title, topic FROM surveys where username='$username' ORDER BY username ASC";
	}

	$result = mysqli_query($connection, $query);

	// if surveys exist into database, display them:
	if (mysqli_num_rows($result) != 0) {
		printSurveys($connection, $result, $userIsAdmin);
	} else {
		// otherwise display no surveys found message:
		echo "No surveys found<br>";
	}

	// display survey deletion confirmation message:
	if (isset($_GET['deleteSurvey'])) {

		echo "<br> Are you sure you want to delete the survey " . $_GET['surveyID'] . "?<br>";
		echo "<a href ={$_SERVER['REQUEST_URI']}&confirmDeletion=true>Yes</a>";
		echo " ";
		echo "<a href =surveys_manage.php>Cancel</a><br>";

		// if confirmation is confirmed, delete survey from database:
		if (isset($_GET["confirmDeletion"])) {
			$surveyID = $_GET['surveyID'];
			$query = "DELETE FROM surveys WHERE surveyID = '$surveyID'";
			$result = mysqli_query($connection, $query);
			echo "<br>";
			if ($result) {
				echo "Survey deleted<br>";
			} else {
				echo mysqli_error($connection);
			}
		}
	}
	// finish off the HTML for this page:
	require_once "footer.php";
}

// prints list of user surveys
function printSurveys($connection, $result, $userIsAdmin)
{
	// if user is admin print all surveys from database:
	echo "<table>";
	if ($userIsAdmin) {
		echo "<tr><th>Survey ID</th><th>Username</th><th>Title</th><th>Topic</th><th>Survey link</th><th>View results</th><th>Delete survey</th></tr>";
		while ($row = mysqli_fetch_assoc($result)) {
			echo "<tr><td>{$row['surveyID']}</td><td>{$row['username']}</td><td>{$row['title']}</td><td>{$row['topic']}</td><td><a href = answer_survey.php?surveyID={$row['surveyID']}&questionsAnswered=0>Survey link</a></td><td><a href = view_survey_results.php?surveyID={$row['surveyID']}>View Results</a><td><a href = ?deleteSurvey=true&surveyID={$row['surveyID']}> Delete</a></td></tr>";
		}
	} else {
		// if user is not the admin, only display the user's surveys:
		echo "<tr><th>Survey ID</th><th>Title</th><th>Topic</th><th>Survey link</th><th>View results</th><th>Delete survey</th></tr>";
		while ($row = mysqli_fetch_assoc($result)) {
			echo "<tr><td>{$row['surveyID']}</td><td>{$row['title']}</td><td>{$row['topic']}</td><td><a href = answer_survey.php?surveyID={$row['surveyID']}&questionsAnswered=0> Survey link</a></td><td><a href = view_survey_results.php?surveyID={$row['surveyID']}>View Results</a></td><td><a href = ?deleteSurvey=true&surveyID={$row['surveyID']}> Delete</a></td></tr>";
		}
	}
	echo "</table>";
}

?>