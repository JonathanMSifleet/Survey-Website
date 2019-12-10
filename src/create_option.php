<?php

// execute the header script:
require_once "header.php";

if (!isset($_SESSION['loggedInSkeleton'])) {
    // user isn't logged in, display a message saying they must be:
    echo "You must be logged in to view this page.<br>";
} // the user must be signed-in, show them suitable page content
else {

    $connection = mysqli_connect($dbhost, $dbuser, $dbpass, $dbname);

    $questionID = $_GET['questionID'];
    $numOptions = getNumOptions($connection);
    getOptions($connection, $numOptions, $questionID);
}

// finish of the HTML for this page:
require_once "footer.php";

// gets users inputted options
function getOptions($connection, $numOptions, $questionID)
{
    // creates required arrays
    $arrayOfOptions = Array();
    initEmptyArray($arrayOfOptions, $numOptions);
    $arrayOfOptionErrors = Array();
    initEmptyArray($arrayOfOptionErrors, $numOptions);

    // if the user has entered some options
    if (isset($_POST['options'])) {

        $arrayOfOptions = $_POST['options'];

        // sanitise inputs
        for ($i = 0; $i < count($arrayOfOptions); $i++) {
            $arrayOfOptions[$i] = sanitise($arrayOfOptions[$i], $connection);
        }

        // insert options into database
        insertOptions($connection, $arrayOfOptions, $numOptions, $arrayOfOptionErrors);
    } else {
        // if any issues re-display the option form:
        displayOptionForm($numOptions, $arrayOfOptionErrors);
    }
}

// attempts to insert options into database
function insertOptions($connection, $arrayOfOptions, $numOptions, $arrayOfOptionErrors)
{
    // gets list of errors if user data is invalid
    createArrayOfOptionErrors($arrayOfOptions, $arrayOfOptionErrors);
    $errors = implode('', $arrayOfOptionErrors);

    // if no errors attempt to insert options into database:
    if ($errors == "") {

        // get question ID
        $questionID = $_GET['questionID'];

        for ($i = 0; $i < count($arrayOfOptions); $i++) {
            $query = "INSERT INTO question_options (questionID, optionName, optionNo) VALUES ('$questionID', '$arrayOfOptions[$i]', '$i')";
            $result = mysqli_query($connection, $query);

            if (!$result) {
                // else display an error
                displayOptionForm($numOptions, $arrayOfOptionErrors);
                echo "Error: " . mysqli_error($connection) . "<br>";
            }
        }

        if ($result) {
            echo "Options inserted successfully <br>";
            $surveyID = $_GET['surveyID'];
            $numQuestionsInserted = $_GET['numQuestionsInserted'];
            $numQuestions = $_GET['numQuestions'];

            // if options inserted successfully, show the next question
            displayCreateQuestionPrompt($surveyID, $numQuestionsInserted, $numQuestions);
        } else {
            // show invalid options message
            displayOptionForm($numOptions, $arrayOfOptionErrors);
            echo "Option created failed, see validation errors <br>";
        }
    }
}

// displays the form for creating an option:
function displayOptionForm($numOptions, $arrayOfOptionErrors)
{
    echo "<form action='' method='post'>";

    $optionNum = 0;

    // display form:
    for ($i = 0; $i < $numOptions; $i++) {
        $optionNum++;
        echo "Option $optionNum: <input type='text' name='options[]' minlength='1' maxlength='32' required> $arrayOfOptionErrors[$i] <br><br>";
    }

    echo "<input type='submit' value='Submit'>";
    echo "</form>";
}

// get the number of options a question has from the database:
function getNumOptions($connection)
{
    $questionID = $_GET['questionID'];

    $query = "SELECT numOptions FROM questions WHERE questionID = '$questionID'";
    $result = mysqli_query($connection, $query);

    // if no data returned, we set result to true(success)/false(failure):
    if ($result) {
        $row = mysqli_fetch_row($result);

        return $row[0];
    } else {
        // show an unsuccessful signup message:
        echo mysqli_error($connection) . "<br>";
    }
}

// creates an array of option errors:
function createArrayOfOptionErrors($arrayOfOptions, &$arrayOfOptionErrors)
{
    for ($i = 0; $i < count($arrayOfOptions); $i++) {
        $arrayOfOptionErrors[$i] = validateStringLength($arrayOfOptions[$i], 1, 64);
    }
}

?>
