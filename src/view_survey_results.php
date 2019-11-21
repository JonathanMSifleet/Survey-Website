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
echo "<li><a href = view_survey_results.php?surveyID=$surveyID&viewResultsInTable=true>View raw results</a></li>";
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
    $arrayOfRespondents = array();
    getSurveyQuestions($connection, $surveyID, $arrayOfQuestions, $arrayOfQuestionIDs);
    getSurveyRespondents($connection, $surveyID, $arrayOfRespondents);

    echo "<h3>Results:</h3>";

    $numResponses = getNumResponses($connection, $surveyID);
    echo "Number of results: " . $numResponses . "<br>";

    echo "<a href = exportResultsToCSV.php?surveyID=$surveyID>Export results to CSV</a>";

    if (! empty($arrayOfQuestions)) {
        displayTableOfResults($connection, $arrayOfQuestions, $arrayOfQuestionIDs, $arrayOfRespondents, $numResponses);
    } else {
        echo "No Responses found";
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

function displaySurveyResponse($connection, $arrayOfQuestionIDs, $username)
{
    echo "<tr>";

    echo "<td>$username</td>";

    for ($i = 0; $i < count($arrayOfQuestionIDs); $i ++) {

        $query = "SELECT response FROM responses WHERE questionID = '{$arrayOfQuestionIDs[$i]}' AND username = '$username'";
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
    echo "<th>Username</th>";
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

function getSurveyRespondents($connection, $surveyID, &$arrayOfRespondents)
{
    $query = "SELECT DISTINCT username FROM responses"; // $responseID'";
    $result = mysqli_query($connection, $query);

    if ($result) {
        while ($row = mysqli_fetch_assoc($result)) {
            $arrayOfRespondents[] = $row['username'];
        }
    } else {
        echo mysqli_error($connection) . "<br>";
    }
}

function displayTableOfResults($connection, $arrayOfQuestions, $arrayOfQuestionIDs, $arrayOfRespondents, $numResponses)
{
    echo "<table>";

    displayTableHeaders($arrayOfQuestions);

    for ($i = 0; $i < $numResponses; $i ++) {
        $username = $arrayOfRespondents[$i];
        displaySurveyResponse($connection, $arrayOfQuestionIDs, $username);
    }

    echo "</table>";
}

?>