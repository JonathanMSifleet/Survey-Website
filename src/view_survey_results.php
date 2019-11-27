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

    // Improve messages if survey contains no questions
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

    if (mysqli_num_rows($result) != 0) {
        printSurveys($connection, $result, $userIsAdmin, $username);
    } else {
        echo "No surveys found<br>";
    }

    // finish off the HTML for this page:
    require_once "footer.php";
}

?>