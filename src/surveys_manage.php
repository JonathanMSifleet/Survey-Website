<?php

// execute the header script:
require_once "header.php";

// checks the session variable named 'loggedInSkeleton'
// take note that of the '!' (NOT operator) that precedes the 'isset' function
if (!isset($_SESSION['loggedInSkeleton'])) {
    // user isn't logged in, display a message saying they must be:
    echo "You must be logged in to view this page.<br>";
} // the user must be signed-in, show them suitable page content
else {

    $connection = mysqli_connect($dbhost, $dbuser, $dbpass, $dbname);

    // if the connection fails, we need to know, so allow this exit:
    if (!$connection) {
        die("Connection failed: " . $mysqli_connect_error);
    }

    echo "<a href = create_survey.php>Create a survey</a>";
    echo "<br><br>";
    echo "<h3>Surveys:</h3>";

    printSurveys($connection);

    if (isset($_GET['deleteSurvey'])) {
        echo "<br>";
        echo "Are you sure you want to delete the survey " . $_GET['surveyID'] . "? ";
        echo "<br>";
        echo "<a href ={$_SERVER['REQUEST_URI']}&confirmDeletion=true>Yes</a>";
        echo " ";
        echo "<a href =surveys_manage.php>Cancel</a>";
        echo "<br>";

        if (isset($_GET["confirmDeletion"])) {

            $surveyID = $_GET['surveyID'];

            $query = "DELETE FROM surveys WHERE surveyID = '$surveyID'";
            $result = mysqli_query($connection, $query);

            echo "<br>";

            if ($result) {
                echo "Survey deleted<br>";
            } else {
                echo mysqli_error($connection);
            }
        }
    }
}

//
//
function printSurveys($connection)
{
    $username = $_SESSION['username'];

    if ($username == "admin") {
        $userIsAdmin = true;
    } else {
        $userIsAdmin = false;
    }

    if ($userIsAdmin) {
        $query = "SELECT surveyID, username, title, topic FROM surveys ORDER BY username ASC";
    } else {
        $query = "SELECT surveyID, title, topic FROM surveys where username='$username' ORDER BY username ASC";
    }

    $result = mysqli_query($connection, $query);

    echo "<table>";

    if ($userIsAdmin) {
        echo "<tr><th>Survey ID</th><th>Username</th><th>Title</th><th>Topic</th><th>Survey link</th><th>View results</th><th>Delete survey</th></tr>";
        while ($row = mysqli_fetch_assoc($result)) {
            echo "<tr><td>{$row['surveyID']}</td><td>{$row['username']}</td><td>{$row['title']}</td><td>{$row['topic']}</td><td><a href = answer_survey.php?surveyID={$row['surveyID']}&questionsAnswered=0>Survey link</a></td><td><a href = view_survey_results.php?surveyID={$row['surveyID']}>View Results</a><td><a href = ?deleteSurvey=true&surveyID={$row['surveyID']}> Delete</a></td></tr>";
        }
    } else {
        echo "<tr><th>Survey ID</th><th>Title</th><th>Topic</th><th>Survey link</th><th>View results</th><th>Delete survey</th></tr>";
        while ($row = mysqli_fetch_assoc($result)) {
            echo "<tr><td>{$row['surveyID']}</td><td>{$row['title']}</td><td>{$row['topic']}</td><td><a href = answer_survey.php?surveyID={$row['surveyID']}&questionsAnswered=0> Survey link</a></td><td><a href = view_survey_results.php?surveyID={$row['surveyID']}>View Results</a></td><td><a href = ?deleteSurvey=true&surveyID={$row['surveyID']}> Delete</a></td></tr>";
        }
    }
    echo "</table>";

}

// finish off the HTML for this page:
require_once "footer.php";

?>