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

    $temp = Array();
    getSurveyQuestion($connection, $surveyID, $temp);
    $questionName = $temp[0];
    $questionID = $temp[1];
    $questionType = $temp[2];
    $answerRequired = $temp[3];
    $responseErrors = "";

    if (! empty($_POST['checkboxResponse'])) {

        $surveyResponse = implode(', ', $_POST['checkboxResponse']);

        $surveyResponse = sanitise($surveyResponse, $connection);

        insertReponse($connection, $surveyID, $questionID, $questionName, $questionType, $answerRequired, $surveyResponse, $responseErrors, $numQuestions);
    } elseif (isset($_POST['surveyResponse'])) {

        // SANITISATION (see helper.php for the function definition)
        $surveyResponse = sanitise($_POST['surveyResponse'], $connection);

        insertReponse($connection, $surveyID, $questionID, $questionName, $questionType, $answerRequired, $surveyResponse, $responseErrors, $numQuestions);
    } else {
        displaySurveyQuestion($connection, $surveyID, $questionName, $questionID, $questionType, $answerRequired, $surveyResponse, $responseErrors);
    }
}

function insertReponse($connection, $surveyID, $questionID, $questionName, $questionType, $answerRequired, $surveyResponse, $responseErrors, $numQuestions)
{
    if ($answerRequired == 1 && $surveyResponse == "") {
        $responseErrors = "Answer required!";
    } else {
        $responseErrors = "";
    }

    // input validation here, switch type to get validation

    if ($responseErrors == "") {

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

            echo mysqli_error($connection) . "<br>";
            displaySurveyQuestion($connection, $surveyID, $questionName, $questionID, $questionType, $answerRequired, $surveyResponse, $responseErrors);
        }
    } else {
        displaySurveyQuestion($connection, $surveyID, $questionName, $questionID, $questionType, $answerRequired, $surveyResponse, $responseErrors);
        echo "Response couldn't be inserted, see validation messages";
    }
}

function displaySurveyQuestion($connection, $surveyID, $questionName, $questionID, $questionType, $answerRequired, $surveyResponse, $responseErrors)
{
    echo $questionName . "<br>";

    $predefinedOptions = array();

    if ($questionType == "multOption" || $questionType == "dropdown" || $questionType == "checkboxes") {
        getPredefinedOptions($connection, $questionID, $predefinedOptions);
    }

    // to do:
    // validation
    // handling multiple answers to checkboxes

    echo "<form action='' method='post'><br>";
    echo "Response: <br>";

    switch ($questionType) {
        case ("checkboxes"):
            for ($i = 0; $i < count($predefinedOptions); $i ++) {
                echo "<input type='checkbox' name='checkboxResponse[]' value ='$predefinedOptions[$i]'>$predefinedOptions[$i]</input>";
                echo "<br>";
            }
            echo $responseErrors;
            break;
        case ("date"):
            echo "<input type='date' name='surveyResponse' value ='$surveyResponse'> $responseErrors";
            break;
        case ("dropdown"):
            echo "<select name ='surveyResponse'>";
            for ($i = 0; $i < count($predefinedOptions); $i ++) {
                echo "<option value='$predefinedOptions[$i]'>$predefinedOptions[$i]</option>";
            }
            echo "</select>";
            echo $responseErrors;
            break;
        case ("longAnswer"):
            echo "<input type='text' name='surveyResponse' minlength='1' maxlength='65533' value ='$surveyResponse'> $responseErrors";
            break;
        case ("multOption"):
            for ($i = 0; $i < count($predefinedOptions); $i ++) {
                echo "<input type='radio' name='surveyResponse' value='$predefinedOptions[$i]'>$predefinedOptions[$i]<br>";
            }
            echo $responseErrors;
            break;
        case ("number"):
            echo "<input type='' name='surveyResponse' value ='$surveyResponse'> $responseErrors";
            break;
        case ("shortAnswer"):
            echo " <input type='text' name='surveyResponse' minlength='1' maxlength='500' value ='$surveyResponse'> $responseErrors";
            break;
        case ("time"):
            echo "<input type='time' name='surveyResponse' value ='$surveyResponse'> $responseErrors";
    }

    echo "<br><br><input type='submit' value='Submit'>";
    echo "</form><br>";
}

function getSurveyQuestion($connection, $surveyID, &$temp)
{
    $questionToAnswer = $_GET['questionsAnswered'];
    $questionToAnswer ++;

    $query = "SELECT questionName, questionID, type, required FROM questions WHERE surveyID = '$surveyID' AND questionNo = '$questionToAnswer'";
    $result = mysqli_query($connection, $query);

    if ($result) {

        while ($row = mysqli_fetch_assoc($result)) {
            $temp[0] = $row['questionName'];
            $temp[1] = $row['questionID'];
            $temp[2] = $row['type'];
            $temp[3] = $row['required'];
        }
    } else {
        echo mysqli_error($connection) . "<br>";
    }
}

//
//
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

function getPredefinedOptions($connection, $questionID, &$predefinedOptions)
{
    $query = "SELECT optionName FROM questionoptions WHERE questionID = '$questionID' ORDER BY optionName ASC";
    $result = mysqli_query($connection, $query);

    if ($result) {

        while ($row = mysqli_fetch_assoc($result)) {
            $predefinedOptions[] = $row['optionName'];
        }
    } else {
        echo mysqli_error($connection) . "<br>";
    }
}

?>