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

    // echoVariable($surveyID);

    // for ($i = 0; $i < $numQuestions; $i ++) {
    initNewQuestion($connection, $_GET['surveyID']);

    // }}

    // finish of the HTML for this page:
    require_once "footer.php";
}

//
//
function initNewQuestion($connection, $surveyID)
{
    $arrayOfQuestionErrors = array();
    initEmptyArray($arrayOfQuestionErrors, 1);

    $questionName = "";
    $type = "";
    $required = 0;

    if (isset($_POST['questionName'])) {

        // SANITISATION (see helper.php for the function definition)
        $questionName = sanitise($_POST['questionName'], $connection);
        $type = sanitise($_POST['type'], $connection);
        $required = sanitise($_POST['required'], $connection);

        createQuestion($connection, $surveyID, $questionName, $type, $required, $arrayOfQuestionErrors);
    } else {
        displayCreateQuestionForm($questionName, $type, $required, $arrayOfQuestionErrors);
    }
}

//
//
function createQuestion($connection, $surveyID, $questionName, $type, $required, $arrayOfQuestionErrors)
{
    createArrayOfQuestionErrors($questionName, $type, $arrayOfQuestionErrors);
    $errors = concatValidationMessages($arrayOfQuestionErrors);

    if ($errors == "") {

        // try to insert new question:
        $query = "INSERT INTO questions (surveyID, questionName, type, isMandatory) VALUES ('$surveyID', '$questionName', '$type', '$required')";
        $result = mysqli_query($connection, $query);

        // if no data returned, we set result to true(success)/false(failure):
        if ($result) {
            // show a successful signup message:
            echo "Question creation was successful";
            echo "<br>";
        } else {
            // validation failed, show the form again with guidance:
            displayCreateQuestionForm($questionName, $type, $required, $arrayOfQuestionErrors);
            // show an unsuccessful signup message:
            echo "Survey creation failed, please try again<br>";
        }
    } else {
        // validation failed, show the form again with guidance:
        displayCreateQuestionForm($questionName, $type, $required, $arrayOfQuestionErrors);
    }
}

//
//
function displayCreateQuestionForm($questionName, $type, $required, $arrayOfQuestionErrors)
{
    echo <<<_END
    <form action="" method="post">
      Please fill in the following fields:<br>
      Question: <input type="text" name="questionName" minlength="3" maxlength="64" value="$questionName" required> $arrayOfQuestionErrors[0]
      <br>
      Type: <input type="text" name="type" minlength="2" maxlength="32" value="$type" required> $arrayOfQuestionErrors[1]
      <br>
      Required: <input type="checkbox" name="required" value="$required">
      <br>
      <input type="submit" value="Submit">
    </form>
    _END;

    echo "<br>";
}

//
//
function createArrayOfQuestionErrors($questionName, $type, &$arrayOfQuestionErrors)
{
    $arrayOfQuestionErrors[0] = validateStringLength($questionName, 4, 64);
    $arrayOfQuestionErrors[1] = validateStringLength($type, 1, 32);
}

?>