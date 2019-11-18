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

    $surveyID = $_GET['surveyID'];

    $numQuestions = getNoOfSurveyQuestions($connection, $surveyID); // by removing survey's number of questions, user cannot edit it from url bar

    initNewQuestions($connection, $surveyID, $numQuestions);

    // finish of the HTML for this page:
    require_once "footer.php";
}

//
//
function initNewQuestions($connection, $surveyID, $numQuestions)
{
    $arrayOfQuestionErrors = array();
    initEmptyArray($arrayOfQuestionErrors, 1);

    $questionName = "";
    $type = "";
    $required = null;
    $numOptions = null;

    if (isset($_POST['questionName'])) {

        // SANITISATION (see helper.php for the function definition)
        $questionName = sanitise($_POST['questionName'], $connection);
        $type = sanitise($_POST['type'], $connection);
        $numOptions = sanitise($_POST['numOptions'], $connection);

        if (isset($_POST['required'])) {
            $required = 1;
        } else {
            $required = 0;
        }

        if (! ($type == "multOption" || $type == "dropdown" || $type == "checkboxes")) {
            $numOptions = 1;
        }

        insertQuestion($connection, $surveyID, $questionName, $type, $numOptions, $required, $numQuestions, $arrayOfQuestionErrors);
    } else {
        displayCreateQuestionForm($questionName, $type, $numOptions, $required, $arrayOfQuestionErrors);
    }
}

//
//
function insertQuestion($connection, $surveyID, $questionName, $type, $numOptions, $required, $numQuestions, $arrayOfQuestionErrors)
{
    createArrayOfQuestionErrors($questionName, $type, $numOptions, $arrayOfQuestionErrors);
    $errors = concatValidationMessages($arrayOfQuestionErrors);

    if ($errors == "") {

        $questionID = md5($surveyID . $questionName);

        // try to insert new question:
        $query = "INSERT INTO questions (questionID, surveyID, questionName, type, numOptions, required) VALUES ('$questionID', '$surveyID', '$questionName', '$type', '$numOptions', '$required')";
        $result = mysqli_query($connection, $query);

        // if no data returned, we set result to true(success)/false(failure):
        if ($result) {

            echo "Question created succesfully<br>";

            $numQuestionsInserted = $_GET['numQuestionsInserted'];
            $numQuestionsInserted ++;

            $query = "UPDATE questions SET questionNo = '$numQuestionsInserted' WHERE questionID = '$questionID'";
            $result = mysqli_query($connection, $query);

            if ($type == "multOption" || $type == "dropdown" || $type == "checkboxes") {

                echo "<br>Now to create some options: <br>";
                echo "<a href = create_option.php?questionID=$questionID&surveyID=$surveyID&numQuestionsInserted=$numQuestionsInserted&numQuestions=$numQuestions> Click here to create options </a>";
            } else {

                displayCreateQuestionPrompt($surveyID, $numQuestionsInserted, $numQuestions);
            }
        } else {
            echo mysqli_error($connection) . "<br>";

            displayCreateQuestionForm($questionName, $type, $numOptions, $required, $arrayOfQuestionErrors);
        }
    } else {
        // validation failed, show the form again with guidance:
        displayCreateQuestionForm($questionName, $type, $numOptions, $required, $arrayOfQuestionErrors);
        // show an unsuccessful signup message:
        echo "Question creation failed, see validation errors<br>";
    }
}

//
//
function displayCreateQuestionForm($questionName, $type, $numOptions, $required, $arrayOfQuestionErrors)
{
    $questionNo = $_GET['numQuestionsInserted'];
    $questionNo ++;

    echo <<<_END
    <form action="" method="post">
      Question $questionNo: <input type="text" name="questionName" minlength="3" maxlength="64" value="$questionName" required> $arrayOfQuestionErrors[0]
      <br>
      Type of question:
      <select name="type">
        <option value ="checkboxes">Checkboxes</option>
        <option value ="date">Date</option>
        <option value ="dropdown">Dropdown</option>
        <option value ="longAnswer">Long answer</option>
        <option value ="multOption">Multiple options</option>
        <option value ="Number">Number</option>
        <option value ="shortAnswer">Short answer</option>      
        <option value ="time">Time</option>
      </select>  
      <br>
      Number of pre-defined options: <input type="text" name="numOptions" minlength="1" maxlength="32" value="$numOptions" required> $arrayOfQuestionErrors[0] only applies to dropdown or checkboxes
      <br>
      Required: <input type="checkbox" name="required" value="1">
      <br>
      <input type="submit" value="Submit">
    </form>
    _END;

    echo "<br>";
}

//
//
function createArrayOfQuestionErrors($questionName, $type, $numOptions, &$arrayOfQuestionErrors)
{
    $arrayOfQuestionErrors[0] = validateStringLength($questionName, 4, 64);
    $arrayOfQuestionErrors[1] = validateStringLength($numOptions, 1, 32);
}

?>