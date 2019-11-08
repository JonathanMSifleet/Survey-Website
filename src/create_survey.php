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

    $title = "";
    $instructions = "";
    $noOfQuestions = null;

    if (isset($_POST['username'])) {} else {

        echo "Input survey details:";

        $maxInstructionLength = (2 ** 16) - 1; // max varchar length = 2^16, deduct 1 just to be sure

        // error reporting turned off and re-enabled to hide undefined array of errors variable
        //error_reporting(0);
        echo <<<_END
        <form action="create_survey.php" method="post">
          Please fill in the following fields:<br>
          Title: <input type="text" name="Title" minlength="3" maxlength="64" value="$title" required> $arrayOfErrors[0]
          <br>
          Instructions: <input type="text" name="Instructions" minlength="2" maxlength="$maxInstructionLength" value="$instructions" required> $arrayOfErrors[1]
          <br>
          Number of questions: <input type="text" name="noOfQuestion" minlength="1" maxlength="32" value="$noOfQuestions"> $arrayOfErrors[2]
          <br>         
          <input type="submit" value="Submit">
        </form>
        _END;
        //error_reporting(1);
    }
}

// finish of the HTML for this page:
require_once "footer.php";

?>

