<?php
require_once "header.php";

$connection = mysqli_connect($dbhost, $dbuser, $dbpass, $dbname);

// if the connection fails, we need to know, so allow this exit:
if (! $connection) {
    die("Connection failed: " . $mysqli_connect_error);
}

$surveyID = $_GET['surveyID'];

echo "<h3>" . getSurveyName($connection, $surveyID) . "</h3>";

echo "<br>How would you like to view results?<br>";

echo "<ul>";
echo "<li><a href = view_survey_results.php?surveyID=$surveyID&viewResultsInTable=true>View results in a table</a></li>";
// echo "<li><a href = view_survey_results.php?surveyID=$surveyID&viewRawData=true>View raw data</a></li>";
echo "</ul>";

if (isset($_GET['viewResultsInTable'])) {
    getSurveyResults($connection, $surveyID);
}

// finish off the HTML for this page:
require_once "footer.php";

function getSurveyResults($connection, $surveyID)
{
    $arrayOfQuestions = array();
    $arrayOfQuestionIDs = array();
    getSurveyQuestions($connection, $surveyID, $arrayOfQuestions, $arrayOfQuestionIDs);

    echo "<h3>Results:</h3>";

    $numResponses = getNumResponses($connection, $surveyID);

    if (! empty($arrayOfQuestions)) {

        echo "<table>";

        displayTableHeaders($arrayOfQuestions);

        for ($i = 0; $i < $numResponses; $i ++) {
            displaySurveyResponse($connection, $arrayOfQuestionIDs);
        }

        echo "</table>";
    } else {
        echo "No questions found";
    }
}

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

function displaySurveyResponse($connection, $arrayOfQuestionIDs)
{
    echo "<tr>";

    for ($i = 0; $i < count($arrayOfQuestionIDs); $i ++) {

        $query = "SELECT response FROM responses WHERE questionID = '{$arrayOfQuestionIDs[$i]}'";
        $result = mysqli_query($connection, $query);

        if ($result) {
            $row = mysqli_fetch_assoc($result);
            echo "<td>{$row['response']}</td>";
        } else {
            echo mysqli_error($connection) . "<br>";
        }
    }

    echo "</tr>";
}

function displayTableHeaders($arrayOfQuestions)
{
    echo "<tr>";
    for ($i = 0; $i < count($arrayOfQuestions); $i ++) {
        echo "<th>{$arrayOfQuestions[$i]}</th>";
    }
    echo "</tr>";
}

function getNumResponses($connection, $surveyID)
{
    $query = "SELECT DISTINCT username FROM responses"; // $responseID'";
    $result = mysqli_query($connection, $query);

    if ($result) {
        return mysqli_num_rows($result);
    } else {
        echo mysqli_error($connection) . "<br>";
    }
}

?>