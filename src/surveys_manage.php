<?php

// Things to notice:
// This is the page where each user can MANAGE their surveys
// As a suggestion, you may wish to consider using this page to LIST the surveys they have created
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

    echo "<a href = create_survey.php>Create a survey</a>";
    echo "<br>";
    echo "<br>";
    printUserSurveys($connection);

    // a little extra text that only the admin will see:
    if ($_SESSION['username'] == "admin") {
        echo "[admin sees more!]<br>";
    }
}

function printUserSurveys($connection)
{
    // connect to database:

    // if the connection fails, we need to know, so allow this exit:
    if (! $connection) {
        die("Connection failed: " . $mysqli_connect_error);
    }

    $username = $_SESSION['username'];

    // get all surveys:
    $query = "SELECT surveyID, title, type, topic FROM surveys where username='$username'";
    $result = mysqli_query($connection, $query);

    if ($result !== null) {

        echo "<table border ='1'>";
        echo "<tr><td>surveyID</td><td>title</td><td>type</td><td>topic</td></tr>";

        while ($row = mysqli_fetch_assoc($result)) {
            echo "<tr><td>{$row['surveyID']}</td><td>{$row['title']}</td><td>{$row['type']}</td><td>{$row['topic']}</td></tr>";
        }
        echo "</table>";
    } else {
        echo "At present, there are no surveys to display<br>";
    }
}

// finish off the HTML for this page:
require_once "footer.php";

?>