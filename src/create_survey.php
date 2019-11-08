<?php

// execute the header script:
require_once "header.php";

$arrayOfSurveyErrors = array();
initEmptyArray($arrayOfSurveyErrors, 2);

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

    $title = "";
    $instructions = "";
    $noOfQuestions = null;

    $maxInstructionLength = (2 ** 16) - 2; // max varchar length = 2^16, deduct 2 just to be sure

    if (isset($_POST['title'])) {

        // connect directly to our database (notice 4th argument) we need the connection for sanitisation:
        // SANITISATION (see helper.php for the function definition)
        // cannot be put into function as _POST requires superglobals

        $title = sanitise($_POST['title'], $connection);
        $instructions = sanitise($_POST['instructions'], $connection);
        $noOfQuestions = sanitise($_POST['noOfQuestions'], $connection);

        createArrayOfSurveyErrors($title, $instructions, $noOfQuestions, $maxInstructionLength, $arrayOfSurveyErrors);
        $errors = concatValidationMessages($arrayOfSurveyErrors);
        
        if ($errors == "") {
            
        } else {
            displayCreateSurveyForm($title, $instructions, $noOfQuestions, $maxInstructionLength, $arrayOfSurveyErrors);
        }
        
    } else {
        displayCreateSurveyForm($title, $instructions, $noOfQuestions, $maxInstructionLength, $arrayOfSurveyErrors);
    }
}

// finish of the HTML for this page:
require_once "footer.php";

function createSurvey($connection) {
    
}

function displayCreateSurveyForm($title, $instructions, $noOfQuestions, $maxInstructionLength, $arrayOfSurveyErrors)
{
    echo "Input survey details:";

    // error reporting turned off and re-enabled to hide undefined array of errors variable
    // error_reporting(0);
    echo <<<_END
    <form action="create_survey.php" method="post">
      Please fill in the following fields:<br>
      Title: <input type="text" name="title" minlength="3" maxlength="64" value="$title" required> $arrayOfSurveyErrors[0]
      <br>
      Instructions: <input type="text" name="instructions" minlength="2" maxlength="$maxInstructionLength" value="$instructions" required> $arrayOfSurveyErrors[1]
      <br>
      Number of questions: <input type="text" name="noOfQuestions" minlength="1" maxlength="32" value="$noOfQuestions"> $arrayOfSurveyErrors[2]
      <br>
      <input type="submit" value="Submit">
    </form>
    _END;
    // error_reporting(1);
}

?>

