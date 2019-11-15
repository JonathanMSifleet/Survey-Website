<?php

// Things to notice:
// Listing the available surveys for each user will probably involve accessing the contents of another TABLE in your database
// Give users options such as to CREATE a new survey, EDIT a survey, ANALYSE a survey, or DELETE a survey, might be a nice idea
// You will probably want to make some additional PHP scripts that let your users CREATE and EDIT surveys and the questions they contain
// REMEMBER: Your admin will want a slightly different view of this page so they can MANAGE all of the users' surveys

// execute the header script:
require_once "header.php";

// checks the session variable named 'loggedInSkeleton'
// take note that of the '!' (NOT operator) that precedes the 'isset' function
if (! isset($_SESSION['loggedInSkeleton'])) {
    // user isn't logged in, display a message saying they must be:
    echo "You must be logged in to view this page.<br>";
} // the user must be signed-in, show them suitable page content
else {

    $connection = mysqli_connect($dbhost, $dbuser, $dbpass, $dbname);

    // if the connection fails, we need to know, so allow this exit:
    if (! $connection) {
        die("Connection failed: " . $mysqli_connect_error);
    }

    echo "<a href = create_survey.php>Create a survey</a>";
    echo "<br>";
    echo "<br>";

    $username = $_SESSION['username'];

    if ($username == "admin") {
        echo "User surveys:";
        $userIsAdmin = true;
    } else {
        echo "Your surveys";
        $userIsAdmin = false;
    }

    printSurveys($connection, $username, $userIsAdmin);

    if (isset($_GET['deleteSurvey'])) {
        echo "<br>";
        echo "Are you sure you want to delete the survey " . $_GET['surveyID'] . "? ";
        echo "<br>";
        echo "<a href ={$_SERVER['REQUEST_URI']}&confirmDeletion=true>Yes</a>";
        echo " ";
        echo "<a href =surveys_manage.php>Cancel</a>";

        if (isset($_GET["confirmDeletion"])) {

            $surveyID = $_GET['surveyID'];

            $query = "DELETE FROM surveys WHERE surveyID = '$surveyID'";
            echo "<br>";

            if (! mysqli_query($connection, $query)) {
                echo mysqli_error($connection);
            } else {
                echo "Survey deleted";
            }
        }
    }
}

//
//
function printSurveys($connection, $username, $userIsAdmin)
{
    if ($userIsAdmin) {
        $query = "SELECT surveyID, username, title, type, topic FROM surveys ORDER BY username ASC";
    } else {
        $query = "SELECT surveyID, title, type, topic FROM surveys where username='$username' ORDER BY username ASC";
    }

    $result = mysqli_query($connection, $query);

    if ($result !== null) {

        echo "<table border ='1'>";

        if ($userIsAdmin) {
            echo "<tr><td>surveyID</td><td>username</td><td>title</td><td>type</td><td>topic</td><td>Survey Link</td><td>Delete Survey</td></tr>";
            while ($row = mysqli_fetch_assoc($result)) {
                echo "<tr><td>{$row['surveyID']}</td><td>{$row['username']}</td><td>{$row['title']}</td><td>{$row['type']}</td><td>{$row['topic']}</td><td><a href = http://localhost/answer_survey.php?surveyID={$row['surveyID']}&questionsAnswered=0> Survey link</a></td><td><a href = ?deleteSurvey=true&surveyID={$row['surveyID']}> Delete</a></td></tr>";
            }
        } else {
            echo "<tr><td>surveyID</td><td>title</td><td>type</td><td>topic</td><td>Delete Survey</td></tr>";
            while ($row = mysqli_fetch_assoc($result)) {
                echo "<tr><td>{$row['surveyID']}</td><td>{$row['title']}</td><td>{$row['type']}</td><td>{$row['topic']}</td><td><a href = ?deleteSurvey=true&surveyID={$row['surveyID']}> Delete</a></td></tr>";
            }
        }
        echo "</table>";
    } else {
        echo "At present, there are no surveys to display<br>";
    }
}

// finish off the HTML for this page:
require_once "footer.php";

?>