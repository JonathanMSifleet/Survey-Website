<?php
require_once "header.php";

$connection = mysqli_connect($dbhost, $dbuser, $dbpass, $dbname);

// if the connection fails, we need to know, so allow this exit:
if (!$connection) {
    die("Connection failed: " . $mysqli_connect_error);
}

$surveyID = $_GET['surveyID'];

$arrayOfQuestionNames = array();
$arrayOfQuestionIDs = array();
getSurveyQuestions($connection, $surveyID, $arrayOfQuestionNames, $arrayOfQuestionIDs);

echo "<h3>" . getSurveyName($connection, $surveyID) . "</h3>";

echo "<br>How would you like to do?<br>";

echo "<ul>";
echo "<li><a href = view_survey_results.php?surveyID=$surveyID&viewResultsInTable=true>View raw results</a></li>";
echo "<li><a href = view_survey_results.php?surveyID=$surveyID&showListOfQuestionsToDelete=true>Delete a question</a></li>";
echo "</ul>";

if (isset($_GET['viewResultsInTable'])) {

    // get num of responses

    $arrayOfRespondents = array();
    getSurveyRespondents($connection, $surveyID, $arrayOfRespondents);

    if (count($arrayOfRespondents) != 0) {
        displaySurveyResults($connection, $surveyID, $arrayOfQuestionNames, $arrayOfQuestionIDs, $arrayOfRespondents);
    } else {
        echo "No survey responses<br>";
    }
}

if (isset($_GET['showListOfQuestionsToDelete'])) {
    displayQuestionsToDelete($connection, $surveyID, $arrayOfQuestionNames, $arrayOfQuestionIDs);
}

// finish off the HTML for this page:
require_once "footer.php";

function displayQuestionsToDelete($connection, $surveyID, $arrayOfQuestionNames, $arrayOfQuestionIDs)
{
    $numQuestions = count($arrayOfQuestionNames);

    if ($numQuestions == 0) {
        echo "</ul>";
        echo "There are no questions to delete<br>";
    } else {
        echo "Pick a question to delete:<br>";

        echo "<ul>";

        for ($i = 0; $i < $numQuestions; $i++) {
            echo "<li><a href = view_survey_results.php?surveyID=$surveyID&showListOfQuestionsToDelete=true&deleteQuestion=$arrayOfQuestionIDs[$i]>$arrayOfQuestionNames[$i]</a></li>";
        }
        echo "</ul>";

        if (isset($_GET['deleteQuestion'])) {

            echo "<br> Are you sure?<br><br>";
            echo "<a href = {$_SERVER['REQUEST_URI']}&confirmDeletion=true> Yes</a>";
            echo " ";
            echo "<a href = view_survey_results.php?surveyID=$surveyID&showListOfQuestionsToDelete=true>Cancel</a><br>";

            if (isset($_GET['confirmDeletion'])) {
                initDeleteQuestion($connection, $surveyID, $numQuestions);
            }
        }
    }
}

function initDeleteQuestion($connection, $surveyID, $numQuestions)
{
    deleteQuestion($connection);
    updateTableNumQuestions($connection, $surveyID, $numQuestions);
    //updateAllQuestionNums($connection); // finish this function

}

function updateTableNumQuestions($connection, $surveyID, $numQuestions)
{

    $numQuestions--;

    $query = "UPDATE surveys SET numQuestions = '$numQuestions' WHERE surveyID='$surveyID'";
    $result = mysqli_query($connection, $query);

    if ($result) {
        echo "Question deleted successfully<br>";
    } else {
        echo mysqli_error($connection);
    }

}

function deleteQuestion($connection)
{
    $questionID = $_GET['deleteQuestion'];

    $query = "DELETE FROM questions WHERE questionID = '$questionID'";
    $result = mysqli_query($connection, $query);

    if (!$result) {
        echo mysqli_error($connection);
    }
}

function displaySurveyResults($connection, $surveyID, $arrayOfQuestionNames, $arrayOfQuestionIDs, $arrayOfRespondents)
{

    $numResponses = count($arrayOfRespondents);
    $tableName = "response_CSV_" . $surveyID;
    $_SESSION['tableName'] = $tableName;
    $_SESSION['questionNames'] = $arrayOfQuestionNames;

    echo "<h3>Results:</h3>";

    echo "Number of results: " . $numResponses . "<br>";

    echo "<a href = exportResultsToCSV.php?surveyID=$surveyID>Export results to CSV</a>";

    if (!empty($arrayOfQuestionNames)) {
        getTableOfResults($connection, $surveyID, $tableName, $arrayOfQuestionNames, $arrayOfQuestionIDs, $arrayOfRespondents, $numResponses);
        displayTableOfResults($connection, $tableName, $arrayOfQuestionNames, $surveyID);
    } else {
        echo "<br><br>No Responses found<br>";
    }

    if (isset($_GET['username'])) {
        $query = "DELETE r.* FROM responses r INNER JOIN questions q ON r.questionID = q.questionID WHERE q.surveyID = '$surveyID' AND r.username = '{$_GET['username']}'";
        $result = mysqli_query($connection, $query);

        if ($result) {
            echo "Success";
        } else {
            echo mysqli_error($connection);
        }
    }
}

function getTableOfResults($connection, $surveyID, $tableName, $arrayOfQuestionNames, $arrayOfQuestionIDs, $arrayOfRespondents, $numResponses)
{
    dropTable($connection, $tableName);
    createTable($connection, $surveyID, $arrayOfQuestionNames, $tableName);
    populateTable($connection, $tableName, $arrayOfQuestionIDs, $arrayOfRespondents, $numResponses);
}

//
//
function getSurveyRespondents($connection, $surveyID, &$arrayOfRespondents)
{
    $query = "SELECT DISTINCT username FROM responses INNER JOIN questions ON responses.questionID = questions.questionID WHERE surveyID= '$surveyID'";
    $result = mysqli_query($connection, $query);

    if ($result) {
        while ($row = mysqli_fetch_assoc($result)) {
            $arrayOfRespondents[] = $row['username'];
        }
    } else {
        echo mysqli_error($connection) . "<br>";
    }
}

function getSurveyName($connection, $surveyID)
{
    $query = "SELECT title FROM surveys WHERE surveyID = '$surveyID'";
    $result = mysqli_query($connection, $query);

    if ($result) {
        $row = mysqli_fetch_row($result);
        return $row[0];
    } else {
        echo mysqli_error($connection) . "<br>";
    }
}


//
//
function getSurveyQuestions($connection, $surveyID, &$arrayOfQuestions, &$arrayOfQuestionIDs)
{
    $query = "SELECT questionName, questionID FROM questions WHERE surveyID = '$surveyID' ORDER BY questionNo ASC";
    $result = mysqli_query($connection, $query);

    if ($result) {
        while ($row = mysqli_fetch_assoc($result)) {
            $arrayOfQuestions[] = $row['questionName'];
            $arrayOfQuestionIDs[] = $row['questionID'];
        }
    } else {
        echo mysqli_error($connection) . "<br>";
    }
}

//
//
function createTable($connection, $surveyID, $arrayOfQuestionNames, $tableName)
{

    // make our table:
    $query = "CREATE TABLE $tableName (Username VARCHAR(20),  PRIMARY KEY(username))";
    $result = mysqli_query($connection, $query);

    if ($result) {
        for ($i = 0; $i < count($arrayOfQuestionNames); $i++) {

            $questionName = $arrayOfQuestionNames[$i];

            $query = "ALTER IGNORE TABLE $tableName ADD `$questionName` VARCHAR(128)";
            $result2 = mysqli_query($connection, $query);

            if (!$result2) {
                echo("Error: " . mysqli_error($connection));
            }
        }
    } else {
        echo("Error: " . mysqli_error($connection));
    }
}

function populateTable($connection, $tableName, $arrayOfQuestionIDs, $arrayOfRespondents, $numResponses)
{
    $dataToInsert = array();

    for ($i = 0; $i < $numResponses; $i++) {
        $username = $arrayOfRespondents[$i];
        $dataToInsert[] = $username;

        for ($j = 0; $j < count($arrayOfQuestionIDs); $j++) {

            $query = "SELECT response FROM responses WHERE questionID = '{$arrayOfQuestionIDs[$j]}' AND username = '$username'";
            $result = mysqli_query($connection, $query);

            if ($result) {
                $row = mysqli_fetch_assoc($result);
                $dataToInsert[] = $row['response'];
            } else {
                echo mysqli_error($connection) . "<br>";
            }
        }
        insertResponseIntoTable($connection, $tableName, $dataToInsert);
        $dataToInsert = array();
    }
}

function insertResponseIntoTable($connection, $tableName, $dataToInsert)
{
    $values = implode("','", $dataToInsert);
    $values = "'" . $values . "'";

    $query = "INSERT INTO $tableName VALUES ($values)";
    $result = mysqli_query($connection, $query);

    if (!$result) {
        echo mysqli_error($connection);
    }
}

function displayTableOfResults($connection, $tableName, $arrayOfQuestionNames, $surveyID)
{
    echo "<br>";

    $query = "SELECT * FROM  $tableName ORDER BY username ASC";
    $result = mysqli_query($connection, $query);
    $numColumns = mysqli_num_fields($result);

    echo "<br><table>";

    displayHeaders($connection, $tableName, $arrayOfQuestionNames);
    displayRows($result, $surveyID);

    echo "</table>";
}

function displayHeaders($connection, $tableName, $arrayOfQuestionNames)
{

    echo "<tr>";
    echo "<th>Username</th>";
    for ($i = 0; $i < count($arrayOfQuestionNames); $i++) {
        echo "<th>{$arrayOfQuestionNames[$i]}</th>";
    }

    if ($_SESSION['username'] == "admin") {
        echo "<th>Delete response</th>";
    }

    echo "</tr>";

}

function displayRows($result, $surveyID)
{
    while ($row = mysqli_fetch_assoc($result)) {
        echo "<tr>";

        // iterate through associative array:
        foreach ($row as $i => $value) {
            echo "<td>$value</td>";
        }

        if ($_SESSION['username'] == "admin") {
            echo "<td><a href = view_survey_results.php?surveyID=$surveyID&viewResultsInTable=true&username={$row['Username']}>Delete</a></td>";
        }

        echo "</tr>";
    }
}

?>