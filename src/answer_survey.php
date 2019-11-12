<?php
// execute the header script:
require_once "header.php";

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

    if (determineValidSurvey($connection) == false) {
        echo "Invalid survey ID";
    } else {

        $surveyID = $_GET['surveyID'];
        displaySurveyQuestion($connection, $surveyID);
    }
}

// finish of the HTML for this page:
require_once "footer.php";

function displaySurveyQuestion($connection, $surveyID)
{
    $query = "SELECT questionName FROM questions WHERE surveyID = '$surveyID' ORDER BY questionNo ASC";
    $result = mysqli_query($connection, $query);

    while ($row = mysqli_fetch_assoc($result)) {
        echo $row['questionName'];
        echo "<br>";
    }
}

function determineValidSurvey($connection)
{
    $surveyID = $_GET['surveyID'];

    $query = "SELECT * FROM surveys WHERE surveyID='$surveyID'";
    $result = mysqli_query($connection, $query);

    if (mysqli_num_rows($result) == 0) {
        return false;
    } else {
        return true;
    }
}

?>