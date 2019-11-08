<?php
// Things to notice:
// You need to add code to this script to implement the admin functions and features
// Notice that the code not only checks whether the user is logged in, but also whether they are the admin, before it displays the page content
// When an admin user is verified, you can implement all the admin tools functionality from this script, or distribute them over multiple pages - your choice
// execute the header script:
require_once "header.php";

// checks the session variable named 'loggedInSkeleton'
// take note that of the '!' (NOT operator) that precedes the 'isset' function
if (! isset($_SESSION['loggedInSkeleton'])) {
    // user isn't logged in, display a message saying they must be:
    echo "You must be logged in to view this page.<br>";
} // the user must be signed-in, show them suitable page content
else {
    // creates connection to MYSQLi DB:
    $connection = mysqli_connect($dbhost, $dbuser, $dbpass, $dbname);

    // if the connection fails, we need to know, so allow this exit:
    if (! $connection) {
        die("Connection failed: " . $mysqli_connect_error);
    }

    // only display the page content if this is the admin account (all other users get a "you don't have permission..." message):
    if ($_SESSION['username'] == "admin") {

        echo "Click to create a new account: <br>";

        echo "<a href =admin.php?createAccount=true>Create user account</a>";
        echo "<br><br>";

        if (isset($_GET['createAccount'])) {
            initCreateAccount($connection);
        } else {
            displayListOfUsers($connection);
        }
    } else {
        echo "You don't have permission to view this page <br>";
    }
}
// finish off the HTML for this page:
require_once "footer.php";

function initCreateAccount($connection)
{
    $arrayOfAccountCreationErrors = array();
    initEmptyArray($arrayOfAccountCreationErrors, 6);

    $todaysDate = date('Y-m-d'); // get current date: +

    // default values we show in the form:
    $username = "";
    $firstname = ""; // +
    $surname = ""; // +
    $password = "";
    $email = "";
    $number = ""; // +
    $dob = ""; // +

    if (isset($_POST['username'])) {

        // SANITISATION (see helper.php for the function definition)
        // cannot be put into function as _POST requires superglobals
        $username = sanitise($_POST['username'], $connection);
        $email = sanitise($_POST['email'], $connection);
        $password = sanitise($_POST['password'], $connection);
        $firstname = sanitise($_POST['firstname'], $connection); // +
        $surname = sanitise($_POST['surname'], $connection); // +
        $number = sanitise($_POST['number'], $connection); // +
        $dob = sanitise($_POST['dob'], $connection); // +

        createAccount($connection, $username, $email, $password, $firstname, $surname, $number, $dob, $todaysDate, $arrayOfAccountCreationErrors);
    } else {
        // show the sign up form
        displayCreateAccountForm($username, $email, $password, $firstname, $surname, $number, $dob, $todaysDate, $arrayOfAccountCreationErrors);
    }
}

function displayListOfUsers($connection)
{
    // queries mysql table, outputs results to table
    // this is written by me:
    $query = "SELECT username FROM users"; // +
    $result = mysqli_query($connection, $query); // +

    echo "Or click a name from the table to view user's data:";
    echo "<br>";

    echo "<table border ='1'>";
    echo "<tr><td>username</td></tr>";

    while ($row = mysqli_fetch_assoc($result)) {
        // if row hyperlink is clicked, set superglobal with user's name
        echo "<tr><td><a href =?username={$row['username']}>{$row['username']}</a></td></tr>"; // turns row result into hyperlink
    }
    echo "</table>";

    // print user's data
    if (isset($_GET['username'])) {
        displayDetailsAndEditOptions($connection, "admin.php", $_GET['username']);
    }
}

?>