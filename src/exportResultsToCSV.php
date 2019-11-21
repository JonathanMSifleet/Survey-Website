<?php

require_once "credentials.php";

require_once "helper.php";

$connection = mysqli_connect($dbhost, $dbuser, $dbpass, $dbname);

// if the connection fails, we need to know, so allow this exit:
if (!$connection) {
    die("Connection failed: " . $mysqli_connect_error);
}

$surveyID = $_GET['surveyID'];

$arrayOfQuestionNames = array();
$arrayOfQuestionIDs = array();
$arrayOfRespondents = array();
getSurveyQuestions($connection, $surveyID, $arrayOfQuestionNames, $arrayOfQuestionIDs);
getSurveyRespondents($connection, $surveyID, $arrayOfRespondents);

$numResponses = getNumResponses($connection, $surveyID);
$tableName = "response_CSV_" . $surveyID;

dropTable($connection, $tableName);
createTable($connection, $surveyID, $arrayOfQuestionNames, $tableName);
populateTable($connection, $tableName, $arrayOfQuestionIDs, $arrayOfRespondents, $numResponses);
exportTableToCSV($connection, $tableName, $arrayOfQuestionNames);
dropTable($connection, $tableName);

function exportTableToCSV($connection, $tableName, $arrayOfQuestionNames)
{
    // output headers so that the file is downloaded rather than displayed
    header('Content-type: text/csv');
    header('Content-Disposition: attachment; filename="demo.csv"');

    // do not cache the file
    header('Pragma: no-cache');
    header('Expires: 0');

    // create a file pointer connected to the output stream
    $file = fopen('php://output', 'w');

    // column headers:

    $arrayOfColumnNames = $arrayOfQuestionNames;
    array_unshift($arrayOfColumnNames, "Username");

    fputcsv($file, $arrayOfColumnNames);

    //query the database
    $query = "SELECT * FROM $tableName ORDER BY username ASC";

    if ($rows = mysqli_query($connection, $query)) {
        // loop over the rows, outputting them
        while ($row = mysqli_fetch_assoc($rows)) {
            fputcsv($file, $row);
        }
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

function dropTable($connection, $tableName)
{
    $sql = "DROP TABLE IF EXISTS $tableName";
    if (!mysqli_query($connection, $sql)) {
        echo "Error checking for user table: " . mysqli_error($connection);
    }
}

?>