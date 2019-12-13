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

	initNewSurvey($connection);
}

// finish of the HTML for this page:
require_once "footer.php";

// get survey data from user:
function initNewSurvey($connection) {
	$arrayOfSurveyErrors = array();
	initEmptyArray($arrayOfSurveyErrors, 4);

	$title = "";
	$instructions = "";
	$numQuestions = null;
	$topic = "";

	if (isset($_POST['title'])) {

		// sanitise survey data:
		$title = sanitise($_POST['title'], $connection);
		$instructions = sanitise($_POST['instructions'], $connection);
		$numQuestions = sanitise($_POST['noOfQuestions'], $connection);
		$topic = sanitise($_POST['topic'], $connection);

		// insert survey into database:
		createSurvey($connection, $title, $instructions, $numQuestions, $topic, $arrayOfSurveyErrors);
	} else {
		// display create survey form:
		displayCreateSurveyForm($title, $instructions, $numQuestions, $topic, $arrayOfSurveyErrors);
	}
}

// inserts survey into database:
function createSurvey($connection, $title, $instructions, $numQuestions, $topic, $arrayOfSurveyErrors) {
	// creates array of survey data:
	createArrayOfSurveyErrors($title, $instructions, $numQuestions, $topic, $arrayOfSurveyErrors);
	$errors = implode('', $arrayOfSurveyErrors);

	// if there are no errors insert the survey into the database:
	if ($errors == "") {

		// try to insert new survey:
		$currentUser = $_SESSION['username'];
		$surveyID = md5($currentUser . $title);

		$query = "INSERT INTO surveys (surveyID, username, title, instructions, numQuestions, topic) VALUES ('$surveyID', '$currentUser',  '$title' , '$instructions','$numQuestions', '$topic')";
		$result = mysqli_query($connection, $query);

		// if no data returned, we set result to true(success)/false(failure):
		if ($result) {
			// show a successful signup message:
			echo "Survey creation was successful";
			echo "<br>";
			// show prompt to create the questions for the survey:
			echo "<a href = 'create_question.php?surveyID=$surveyID&numQuestionsInserted=0'>Click here to create questions</a><br>";
		} else {
			// show an unsuccessful message:
			echo mysqli_error($connection) . "<br>";
			displayCreateSurveyForm($title, $instructions, $numQuestions, $topic, $arrayOfSurveyErrors);
		}
	} else {
		// validation failed, show the form again with guidance:
		displayCreateSurveyForm($title, $instructions, $numQuestions, $topic, $arrayOfSurveyErrors);
	}
}

// displays create survey form:
function displayCreateSurveyForm($title, $instructions, $numQuestions, $topic, $arrayOfSurveyErrors) {

	// max number of questions length to be compatible with MYSQL smallint max value
	echo <<<_END
            <form action="create_survey.php" method="post">
              Input survey details:<br>
              Title: <input type="text" name="title" minlength="3" maxlength="64" value="$title" required> $arrayOfSurveyErrors[0]
              <br>
              Instructions: <input type="text" name="instructions" minlength="2" maxlength="500" value="$instructions" required> $arrayOfSurveyErrors[1]
              <br>
              Number of questions: <input type="text" name="noOfQuestions" minlength="1" maxlength="512" value="$numQuestions" required> $arrayOfSurveyErrors[2]
              <br>
              Survey Topic: <input type="text" name="topic" maxlength="32" value="$topic"> $arrayOfSurveyErrors[4]
              <br>
              <input type="submit" value="Submit">
            </form>
_END;
}

// creates an array of invalid survey data:
function createArrayOfSurveyErrors($title, $instructions, $numQuestions, $topic, &$arrayOfSurveyErrors) {
	$arrayOfSurveyErrors[0] = validateStringLength($title, 4, 64);
	$arrayOfSurveyErrors[1] = validateStringLength($instructions, 1, 500);
	$arrayOfSurveyErrors[2] = validateNumberOfQuestions($numQuestions, 1, 32);
	$arrayOfSurveyErrors[3] = validateStringLength($topic, 0, 32);
}

// checks integer is correct size
function validateIntSize($input, $minNo, $maxNo) {
	if ($input < $minNo) {
		// wasn't a valid length, return a help message:
		return "Input length: " . $input . ", minimum length: " . $minNo;
	} elseif ($input > $maxNo) {
		// wasn't a valid length, return a help message:
		return "Input length: " . $input . ", maximum length: " . $maxNo;
	} else {
		// data was valid, return an empty string:
		return "";
	}
}

// checks to make sure input is a number, and then validates the size of the integer
function validateNumberOfQuestions($input, $minNo, $maxNo) {
	$errors = checkOnlyNumeric($input);
	$errors = $errors . validateIntSize($input, $minNo, $maxNo);
	return $errors;
}

?>