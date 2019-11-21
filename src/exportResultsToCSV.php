<?php
require_once "header.php";

$connection = mysqli_connect($dbhost, $dbuser, $dbpass, $dbname);

// if the connection fails, we need to know, so allow this exit:
if (! $connection) {
    die("Connection failed: " . $mysqli_connect_error);
}

$surveyID = $_GET['surveyID'];

$arrayOfQuestionNames = array();
$arrayOfQuestionIDs = array();
getSurveyQuestions($connection, $surveyID, $arrayOfQuestionNames, $arrayOfQuestionIDs);

createTable($connection, $surveyID, $arrayOfQuestionNames);

function createTable($connection, $surveyID, $arrayOfQuestionNames)
{
    $tableName = "response_CSV_" . $surveyID;

    // make our table:
    $query = "CREATE TABLE $tableName (username VARCHAR(20),  PRIMARY KEY(username))";
    $result = mysqli_query($connection, $query);

    if ($result) {
        for ($i = 0; $i < count($arrayOfQuestionNames); $i ++) {

            $questionName = $arrayOfQuestionNames[$i];

            $query = "ALTER IGNORE TABLE $tableName ADD `$questionName` VARCHAR(128)";
            $result2 = mysqli_query($connection, $query);

            if (! $result2) {
                echo ("Error: " . mysqli_error($connection));
            }
        }
    } else {
        echo ("Error: " . mysqli_error($connection));
    }
}

echo "<br>";
require_once "footer.php";

?>