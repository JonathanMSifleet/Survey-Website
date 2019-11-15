<?php
// execute the header script:
require_once "header.php";

if (! isset($_SESSION['loggedInSkeleton'])) {
    // user isn't logged in, display a message saying they must be:
    echo "You must be logged in to view this page.<br>";
} // the user must be signed-in, show them suitable page content
else {

    // only display the page content if this is the admin account (all other users get a "you don't have permission..." message):
    $connection = mysqli_connect($dbhost, $dbuser, $dbpass, $dbname);

    // if the connection fails, we need to know, so allow this exit:
    if (! $connection) {
        die("Connection failed: " . $mysqli_connect_error);
    }

    if (determineValidSurvey($connection) == false) {
        echo "Invalid survey ID";
    } else {

        $surveyID = $_GET['surveyID'];
        $numQuestions = getNoOfSurveyQuestions($connection, $surveyID);
        displaySurvey($connection, $surveyID, $numQuestions);
    }
}

// finish of the HTML for this page:
require_once "footer.php";

function displaySurvey($connection, $surveyID, $numQuestions)
{
    $surveyResponse = "";
    $responseVal = "";

    $temp = Array();
    getSurveyQuestion($connection, $surveyID, $temp);
    $questionName = $temp[0];
    $questionID = $temp[1];

    if (isset($_POST['surveyResponse'])) {
        $surveyResponse = sanitise($_POST['surveyResponse'], $connection);
        insertReponse($connection, $surveyID, $questionID, $questionName, $surveyResponse, $responseVal, $numQuestions);
    } else {
        displaySurveyQuestion($connection, $surveyID, $questionName, $questionID, $surveyResponse);
    }
}

function insertReponse($connection, $surveyID, $questionID, $questionName, $surveyResponse, $responseVal, $numQuestions)
{

    // input validation here: $responseVal =
    $currentUser = $_SESSION['username'];
    $responseID = md5($currentUser . $surveyResponse);

    $query = "INSERT INTO responses (questionID, username, responseID, response) VALUES ('$questionID', '$currentUser', '$responseID', '$surveyResponse')";
    $result = mysqli_query($connection, $query);

    if ($result) {
        echo "Response was successful <br>";

        $questionsAnswered = $_GET['questionsAnswered'];
        $questionsAnswered ++;

        if ($questionsAnswered < $numQuestions) {
            $nextQuestionURL = "answer_survey.php?surveyID=$surveyID&questionsAnswered=$questionsAnswered";
            echo "<a href = $nextQuestionURL> Click here to answer the next question </a>";
        } else {
            echo "<br>";
            echo "Survey completed!";
            echo "<br>";
            echo "<a href = about.php> Click here to return to the main page </a>";
        }
    } else {
        echo "Error";
        echo "<br>" . $questionID . "<br>" . $currentUser . "<br>" . $responseID . "<br>" . $surveyResponse;
    }
}

function displaySurveyQuestion($connection, $surveyID, &$questionName, &$questionID, $surveyresponse)
{
    echo <<<_END
    $questionName
    <form action="" method="post">
      Response: <input type="text" name="surveyResponse" minlength="3" maxlength="64" value ="$surveyresponse" required>
      <br>
      <input type="submit" value="Submit">
    </form>
    _END;
}

function getSurveyQuestion($connection, $surveyID, &$temp)
{
    $questionToAnswer = $_GET['questionsAnswered'];
    $questionToAnswer ++;

    $query = "SELECT questionName, questionID FROM questions WHERE surveyID = '$surveyID' AND questionNo = '$questionToAnswer'";
    $result = mysqli_query($connection, $query);

    if ($result) {

        while ($row = mysqli_fetch_assoc($result)) {
            $temp[0] = $row['questionName'];
            $temp[1] = $row['questionID'];
        }
    } else {
        echo "Error";
    }
}

function determineValidSurvey($connection)
{
    $surveyID = $_GET['surveyID'];

    $query = "SELECT * FROM surveys WHERE surveyID='$surveyID'";
    $result = mysqli_query($connection, $query);

    if (mysqli_num_rows($result) == 0) {
        return false;
    } else {
        return true;
    }
}

?>