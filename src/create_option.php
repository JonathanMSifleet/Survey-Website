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

    $numOptions = getNumOptions($connection);
    $numOptionsInserted = 0;

    createOption($connection, $numOptions, $numOptionsInserted);

    // finish of the HTML for this page:
    require_once "footer.php";
}

function createOption($connection, $numOptions, &$numOptionsInserted)
{
    
    if(isset($_POST['option'])) {
        $_POST['option'] = NULL;
    }
    
    $option = getOption($connection, $numOptionsInserted);

    if (isset($option)) {
        insertOption($connection, $option, $numOptions, $numOptionsInserted);
    }
}

function insertOption($connection, $option, $numOptions, &$numOptionsInserted)
{

    // get question ID
    $questionID = $_GET['questionID'];

    $query = "INSERT INTO questionoptions (questionID, optionName) VALUES ('$questionID', '$option')";
    $result = mysqli_query($connection, $query);

    if ($result) {
        echo "Options inserted successfully";
        $numOptionsInserted ++;

        if ($numOptionsInserted < $numOptions) {
            createOption($connection, $numOptions, $numOptionsInserted); // recursion
        }
    } else {
        // show an unsuccessful signup message:
        echo "Query failed, please try again<br>";
    }
}

function getOption($connection)
{
    if (isset($_POST['option'])) {
        return sanitise($_POST['option'], $connection);
    } else {
        echo <<<_END
        <form action="" method="post">
          Option: <input type="text" name="option" minlength="1" maxlength="32" required>
          <br>
          <input type="submit" value="Submit">
        </form>
        _END;

        echo "<br>";
    }
}

function getNumOptions($connection)
{
    $questionID = $_GET['questionID'];

    $query = "SELECT numOptions FROM questions WHERE questionID = '$questionID'";
    $result = mysqli_query($connection, $query);

    // if no data returned, we set result to true(success)/false(failure):
    if ($result) {

        $row = (mysqli_fetch_row($result));

        return $row[0];
    } else {
        // show an unsuccessful signup message:
        echo "Query failed, please try again<br>";
    }
}

?>