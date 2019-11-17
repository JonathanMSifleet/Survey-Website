<?php

// execute the header script:
require_once "header.php";

if (! isset($_SESSION['loggedInSkeleton'])) {
    // user isn't logged in, display a message saying they must be:
    echo "You must be logged in to view this page.<br>";
} // the user must be signed-in, show them suitable page content
else {

    $connection = mysqli_connect($dbhost, $dbuser, $dbpass, $dbname);

    $questionID = $_GET['questionID'];
    $numOptions = getNumOptions($connection);
    getOptions($connection, $numOptions, $questionID);
}

function getOptions($connection, $numOptions, $questionID)
{
    $arrayOfOptions = Array();
    initEmptyArray($arrayOfOptions, $numOptions);

    $arrayOfOptions = $_POST['options'];

    if (isset($_POST['options'])) {

        for ($i = 0; $i < count($arrayOfOptions); $i ++) {
            $arrayOfOptions[$i] = sanitise($arrayOfOptions[$i], $connection);
        }

        insertOptions($connection, $arrayOfOptions, $numOptions);
    } else {
        displayOptionForm($numOptions);
    }
}

//
//
function insertOptions($connection, $arrayOfOptions, $numOptions)
{

    // get question ID
    $questionID = $_GET['questionID'];

    for ($i = 0; $i < count($arrayOfOptions); $i ++) {
        $query = "INSERT INTO questionoptions (questionID, optionName) VALUES ('$questionID', '$arrayOfOptions[$i]')";
        $result = mysqli_query($connection, $query);
    }

    if ($result) {
        echo "Options inserted successfully";
        // go to to next question
    } else {
        // show an unsuccessful signup message:
        echo "Query failed, please try again<br>";
    }
}

//
//
function displayOptionForm($numOptions)
{
    echo "<form action='' method='post'>";

    $optionNum = 0;

    for ($i = 0; $i < $numOptions; $i ++) {
        $optionNum ++;
        echo "Option $optionNum: <input type='text' name='options[]' minlength='1' maxlength='32' required> <br><br>";
    }

    echo "<input type='submit' value='Submit'>";
    echo "</form>";
}

//
//
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
        echo "Query failed, please try again<br>";
    }
}

?>