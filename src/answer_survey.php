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
		die("Connection failed: " . $mysqli_connect_error);
	}

	$surveyID = $_GET['surveyID'];
	$username = $username = $_SESSION['username'];

	// if survey doesn't exist in database then show message that it is invalid
	if (determineValidSurvey($connection, $surveyID) == false) {
		echo "Invalid survey ID";
	} else {

		// if not then determine if the user has already answered the survey only if it is the first question
		if ($_GET['questionsAnswered'] == 0) {
			if (hasUserRespondedToSurvey($connection, $surveyID, $username)) {

				// if they have already answered the survey ask them if they'd like to update their response
				echo "You have already answered the survey<br>";
				echo "<a href = {$_SERVER['REQUEST_URI']}&displaySurvey=true>Click here to update your response</a><br>";

				// if they want to update their response, delete all their old data
				// then begin to show survey:
				if (isset($_GET['displaySurvey'])) {
					deleteUserResponse($connection, $surveyID);
					getSurveyData($connection, $surveyID);
				}
			} else {
				// show survey:
				getSurveyData($connection, $surveyID);
			}
		} else {
			// show survey
			getSurveyData($connection, $surveyID);
		}
	}
}

// finish of the HTML for this page:
require_once "footer.php";

// fetches survey data from database
function getSurveyData($connection, $surveyID)
{
	$surveyID = $_GET['surveyID'];
	$surveyInformation = array();

	// retrieves survey information from table:
	getSurveyInformation($connection, $surveyID, $surveyInformation);
	$title = $surveyInformation[0];
	$topic = $surveyInformation[1];
	$instructions = $surveyInformation[2];
	$numQuestions = $surveyInformation[3];

	// displays useful information regarding survey:
	echo <<<_END
            <h2>$title</h2><br>
            <h3>Topic: $topic</h3>
            Instructions:<br>
            $instructions
_END;

	// displays survey:
	displaySurvey($connection, $surveyID, $numQuestions);
}

// determines if user has already responded to survey
function hasUserRespondedToSurvey($connection, $surveyID, $username)
{
	// if the user has responded to the survey, return the value true, as they have answered the survey already
	$query = "SELECT DISTINCT username FROM responses INNER JOIN questions USING(questionID) WHERE surveyID = '$surveyID' AND username = '$username'";
	$result = mysqli_query($connection, $query);
	if ($result) {
		if (mysqli_num_rows($result) != 0) {
			return true;
		} else {
			return false;
		}
	} else {
		echo mysqli_error($connection);
	}
}

// displays survey
function displaySurvey($connection, $surveyID, $numQuestions)
{
	$surveyResponse = "";

	$questionData = Array();
	getQuestionData($connection, $surveyID, $questionData);
	$questionName = $questionData[0];
	$questionID = $questionData[1];
	$questionType = $questionData[2];
	$answerRequired = $questionData[3];
	$responseErrors = "";

	if (!empty($_POST['checkboxResponse'])) {

		// append list of responses if question is checkbox question:
		$surveyResponse = implode(',', $_POST['checkboxResponse']);
		$surveyResponse = sanitise($surveyResponse, $connection);

		// insert response into table:
		insertResponse($connection, $surveyID, $questionID, $questionName, $questionType, $answerRequired, $surveyResponse, $responseErrors, $numQuestions);
	} elseif (isset($_POST['surveyResponse'])) {

		// insert response into table:
		$surveyResponse = sanitise($_POST['surveyResponse'], $connection);
		insertResponse($connection, $surveyID, $questionID, $questionName, $questionType, $answerRequired, $surveyResponse, $responseErrors, $numQuestions);
	} else {
		// if the question hasn't been answered display the question:
		displaySurveyQuestion($connection, $surveyID, $questionName, $questionID, $questionType, $answerRequired, $surveyResponse, $responseErrors);
	}
}

// attempts to insert user response into database
function insertResponse($connection, $surveyID, $questionID, $questionName, $questionType, $answerRequired, $surveyResponse, $responseErrors, $numQuestions)
{
	if ($answerRequired == 1 && $surveyResponse == "") {
		$responseErrors = "Answer required!";
	} else {
		$responseErrors = "";
	}

	// create list of errors
	$responseErrors = $responseErrors . createResponseErrors($questionType, $surveyResponse);

	// if there are no errors, attempt to insert response into table:
	if ($responseErrors == "") {

		$currentUser = $_SESSION['username'];

		if ($questionType == "checkboxes") {
			$arrayOfCheckboxAnswers = explode(",", $surveyResponse);

			for ($i = 0; $i < count($arrayOfCheckboxAnswers); $i++) {
				$responseID = md5($surveyID . $questionID . $currentUser . $arrayOfCheckboxAnswers[$i]);

				$query = "INSERT INTO responses (questionID, username, responseID, response) VALUES ('$questionID', '$currentUser', '$responseID', '{$arrayOfCheckboxAnswers[$i]}')";
				$result = mysqli_query($connection, $query);
			}
		} else {
			$responseID = md5($surveyID . $questionID . $currentUser . $surveyResponse);

			$query = "INSERT INTO responses (questionID, username, responseID, response) VALUES ('$questionID', '$currentUser', '$responseID', '$surveyResponse')";
			$result = mysqli_query($connection, $query);
		}

		if ($result) {
			echo "<br><br>Response was successful<br>";

			$questionsAnswered = $_GET['questionsAnswered'];
			$questionsAnswered++;

			// if the survey still has unanswered questions, move onto the next question:
			if ($questionsAnswered < $numQuestions) {
				$nextQuestionURL = "answer_survey.php?surveyID=$surveyID&questionsAnswered=$questionsAnswered";
				echo "<a href = $nextQuestionURL>Click here to answer the next question</a><br>";
			} else {
				// otherwise, display message that survey has been completed
				echo "<br>Survey completed!<br><br>";
				echo "<a href = about.php> Click here to return to the main page</a><br>";
			}
		} else {
			// else display an error:
			displaySurveyQuestion($connection, $surveyID, $questionName, $questionID, $questionType, $answerRequired, $surveyResponse, $responseErrors);
			echo "Error: " . mysqli_error($connection) . "<br>";
		}
	} else {
		displaySurveyQuestion($connection, $surveyID, $questionName, $questionID, $questionType, $answerRequired, $surveyResponse, $responseErrors);
		echo "Response couldn't be inserted, see validation messages<br>";
	}
}

// displays the current question and response form
function displaySurveyQuestion($connection, $surveyID, $questionName, $questionID, $questionType, $answerRequired, $surveyResponse, $responseErrors)
{
	echo "<br><h4>" . ($_GET['questionsAnswered'] + 1) . ": $questionName</h4>";

	$predefinedOptions = array();

	// if the question type has predefined options, fetch them from database:
	if ($questionType == "multOption" || $questionType == "dropdown" || $questionType == "checkboxes") {
		getPredefinedOptions($connection, $questionID, $predefinedOptions);
	}

	echo "Response:<br>";

	echo "<form action='' method='post'><br>";

	// display correct form depending on question type:
	switch ($questionType) {
		case ("checkboxes"):
			for ($i = 0; $i < count($predefinedOptions); $i++) {
				echo "<input type='checkbox' name='checkboxResponse[]' value ='$predefinedOptions[$i]'>$predefinedOptions[$i]</input>";
				echo "<br>";
			}
			echo $responseErrors;
			break;
		case ("date"):
			echo "<input type='date' name='surveyResponse' value ='$surveyResponse'> $responseErrors";
			echo "<br>";
			break;
		case ("dropdown"):
			echo "<select name ='surveyResponse'>";
			for ($i = 0; $i < count($predefinedOptions); $i++) {
				echo "<option value='$predefinedOptions[$i]'>$predefinedOptions[$i]</option>";
			}
			echo "</select>";
			echo "<br>";
			echo $responseErrors;
			break;
		case ("longAnswer"):
			echo "<input type='text' name='surveyResponse' minlength='1' maxlength='65533' value ='$surveyResponse'> $responseErrors";
			echo "<br>";
			break;
		case ("multOption"):
			for ($i = 0; $i < count($predefinedOptions); $i++) {
				echo "<input type='radio' name='surveyResponse' value='$predefinedOptions[$i]'>$predefinedOptions[$i]<br>";
			}
			echo $responseErrors;
			break;
		case ("number"):
			echo "<input type='text' name='surveyResponse' minlength ='1' value ='$surveyResponse'> $responseErrors";
			echo "<br>";
			break;
		case ("shortAnswer"):
			echo " <input type='text' name='surveyResponse' minlength='1' maxlength='500' value ='$surveyResponse'> $responseErrors";
			echo "<br>";
			break;
		case ("time"):
			echo "<input type='time' name='surveyResponse' value ='$surveyResponse'> $responseErrors";
			echo "<br>";
	}

	echo "<br><input type='submit' value='Submit'>";
	echo "</form>";
}

// fetches question data from database
function getQuestionData($connection, $surveyID, &$questionData)
{
	$questionToAnswer = $_GET['questionsAnswered'];

	$query = "SELECT questionName, questionID, type, required FROM questions WHERE surveyID = '$surveyID' AND questionNo = '$questionToAnswer'";
	$result = mysqli_query($connection, $query);

	if ($result) {

		while ($row = mysqli_fetch_row($result)) {
			$questionData[0] = $row[0];
			$questionData[1] = $row[1];
			$questionData[2] = $row[2];
			$questionData[3] = $row[3];
		}
	} else {
		echo mysqli_error($connection) . "<br>";
	}
}

// determine if surveyID exists in database
function determineValidSurvey($connection, $surveyID)
{
	$query = "SELECT * FROM surveys WHERE surveyID='$surveyID'";
	$result = mysqli_query($connection, $query);

	if (mysqli_num_rows($result) == 0) {
		return false;
	} else {
		return true;
	}
}

// fetches a questions predefined options if it has any
function getPredefinedOptions($connection, $questionID, &$predefinedOptions)
{
	$query = "SELECT optionName FROM question_options WHERE questionID = '$questionID' ORDER BY optionNo ASC";
	$result = mysqli_query($connection, $query);

	if ($result) {

		while ($row = mysqli_fetch_row($result)) {
			$predefinedOptions[] = $row[0];
		}
	} else {
		echo mysqli_error($connection) . "<br>";
	}
}

// fetches survey information from database
function getSurveyInformation($connection, $surveyID, &$surveyInformation)
{
	$query = "SELECT title, topic, instructions, numQuestions FROM surveys WHERE surveyID = '$surveyID'";
	$result = mysqli_query($connection, $query);

	if ($result) {

		while ($row = mysqli_fetch_row($result)) {
			$surveyInformation[0] = $row[0];
			$surveyInformation[1] = $row[1];
			$surveyInformation[2] = $row[2];
			$surveyInformation[3] = $row[3];
		}
	} else {
		echo mysqli_error($connection) . "<br>";
	}
}

// creates errors to response if user input is invalid:
function createResponseErrors($questionType, $surveyResponse)
{

	// validate input based upon question type:
	switch ($questionType) {
		case ("longAnswer"):
			return validateStringLength($surveyResponse, 0, 65535);
			break;
		case ("number"):
			return checkOnlyNumeric($surveyResponse);
			break;
		case ("shortAnswer"):
			return validateStringLength($surveyResponse, 0, 500);
			break;
		default:
			return "";
	}
}

?>