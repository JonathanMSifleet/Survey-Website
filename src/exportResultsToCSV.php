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
$arrayOfRespondents = array();
getSurveyQuestions($connection, $surveyID, $arrayOfQuestionNames, $arrayOfQuestionIDs);
getSurveyRespondents($connection, $surveyID, $arrayOfRespondents);

$numResponses = getNumResponses($connection, $surveyID);

$tableName = "response_CSV_" . $surveyID;

$sql = "DROP TABLE IF EXISTS $tableName";
// no data returned, we just test for true(success)/false(failure):
if (!mysqli_query($connection, $sql)) {
    echo "Error checking for user table: " . mysqli_error($connection);
}

createTable($connection, $surveyID, $arrayOfQuestionNames, $tableName);

$dataToInsert = array();

//for ($i = 0; $i < $numResponses; $i++) {
$username = $arrayOfRespondents[0];
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
//}

$values = implode("','", $dataToInsert);
$values = "'".$values."'";

echo "<br> $values <br>";

$query = "INSERT INTO $tableName VALUES ($values)";
$result = mysqli_query($connection, $query);

if ($result) {
    echo "Success";
} else {
    echo mysqli_error($connection);
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

echo "<br>";
require_once "footer.php";

?>