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
echo "<li><a href = view_survey_results.php?surveyID=$surveyID&viewRawData=true>View raw data</a></li>";
echo "</ul>";

if (isset($_GET['viewResultsInTable'])) {
    getSurveyResults($connection, $surveyID);
}

// finish off the HTML for this page:
require_once "footer.php";

function getSurveyResults($connection, $surveyID)
{
    $arrayOfQuestions = array();
    getSurveyQuestions($connection, $surveyID, $arrayOfQuestions);

    echo "<h3>List of questions:</h3>";

    if (! empty($arrayOfQuestions)) {

        echo "<table>";
        echo "<tr>";
        echo "<th>Username</th>";
        for ($i = 0; $i < count($arrayOfQuestions); $i ++) {
            echo "<th>{$arrayOfQuestions[$i]}</th>";
        }
        echo "</tr>";

        echo "<tr>";

        for ($i = 0; $i < count($arrayOfQuestions); $i ++) {

            $query = "SELECT username, response FROM responses WHERE questionID='{$arrayOfQuestions[$i]}'";
            $result = mysqli_query($connection, $query);

            if ($result) {
                while ($row = mysqli_fetch_assoc($result)) {
                    $username = $row[0];
                    $response = $row[1];
                }
            } else {
                echo mysqli_error($connection) . "<br>";
            }

            echo "<td>$username</td><td>$response</td>";
        }
        echo "</tr>";

        echo "</table>";
    } else {
        echo "No questions found";
    }
}

function getSurveyQuestions($connection, $surveyID, &$arrayOfQuestions)
{
    $query = "SELECT questionName FROM questions WHERE surveyID = '$surveyID' ORDER BY questionNo ASC";
    $result = mysqli_query($connection, $query);

    if ($result) {
        while ($row = mysqli_fetch_assoc($result)) {
            $arrayOfQuestions[] = $row['questionName'];
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

?>