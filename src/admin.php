<?php
// Things to notice:
// You need to add code to this script to implement the admin functions and features
// Notice that the code not only checks whether the user is logged in, but also whether they are the admin, before it displays the page content
// When an admin user is verified, you can implement all the admin tools functionality from this script, or distribute them over multiple pages - your choice
// execute the header script:
require_once "header.php";

$newInput = null; // +
$shouldDeleteAccount = null; // +

$arrayOfErrors;
initEmptyArray($arrayOfErrors, 6);

// checks the session variable named 'loggedInSkeleton'
// take note that of the '!' (NOT operator) that precedes the 'isset' function
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

    if ($_SESSION['username'] == "admin") {

        echo "Click to create a new account: <br>";

        echo "<a href ={$_SERVER['REQUEST_URI']}?createAccount=true>Create user account</a>";
        echo "<br><br>";

        if (isset($_GET['createAccount'])) {
            createAccount($connection);
        } else {

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
                printUserData($connection, "admin.php", $_GET['username']);

                if (isset($_GET['deleteAccount'])) {
                    deleteAccount($connection, $_GET["username"]);
                } else {

                    if (isset($_GET['editAccountDetails'])) {

                        $superGlobalName = getSuperGlobalName($_SERVER['REQUEST_URI']);

                        $minLength = null;
                        $maxLength = null;
                        $fieldType = determineFieldType($superGlobalName, $minLength, $maxLength);

                        echo "<br>";

                        if ($superGlobalName !== "") {
                            changeUserDetails($connection, $superGlobalName, $fieldType, $minLength, $maxLength);
                        } // end of if
                    }
                }
            }
        }
    } else {
        echo "You don't have permission to view this page <br>";
    }
    mysqli_close($connection);
}
// finish off the HTML for this page:
require_once "footer.php";

function createAccount($connection)
{
    $todaysDate = date('Y-m-d'); // get current date: +

    // default values we show in the form:
    $username = "";
    $firstname = ""; // +
    $surname = ""; // +
    $password = "";
    $email = "";
    $number = ""; // +
    $dob = ""; // +

    // strings to hold any validation error messages:
    $username = "";
    $email = "";
    $password = "";
    $firstname = ""; // +
    $surname = ""; // +
    $number = ""; // +
    $dob = ""; // +

    if (isset($_POST['username'])) {

        // connect directly to our database (notice 4th argument) we need the connection for sanitisation:
        // SANITISATION (see helper.php for the function definition)
        // cannot be put into function as _POST requires superglobals

        $username = sanitise($_POST['username'], $connection);
        $email = sanitise($_POST['email'], $connection);
        $password = sanitise($_POST['password'], $connection);
        $firstname = sanitise($_POST['firstname'], $connection); // +
        $surname = sanitise($_POST['surname'], $connection); // +
        $number = sanitise($_POST['number'], $connection); // +
        $dob = sanitise($_POST['dob'], $connection); // +

        // this was created by me:
        if (checkIfLengthZero($password)) {
            $password = generateAlphanumericString();
        }
        // /////////

        // this was created by me:
        createArrayOfErrors($username, $email, $password, $firstname, $surname, $number, $dob, $todaysDate, $arrayOfErrors); // +
        $numberOfErrors = count($arrayOfErrors); // +

        // concatenate all the validation results together ($errors will only be empty if ALL the data is valid): +
        $errors = concatValidationMessages($username, $email, $password, $firstname, $surname, $number, $dob, $todaysDate, $arrayOfErrors);
        // /////////

        // check that all the validation tests passed before going to the database:
        if ($errors == "") {

            $password = encryptInput($password);

            // try to insert the new details:
            $query = "INSERT INTO users (username, firstname, surname, password, email, number, dob) VALUES ('$username','$firstname','$surname','$password','$email','$number', '$dob')";
            $result = mysqli_query($connection, $query);

            // no data returned, we just test for true(success)/false(failure):
            if ($result) {
                // show a successful signup message:
                echo "Signup was successful. Please sign in<br>";
            } else {

                echo "Sign up failed, please try again<br>";
            }
        }
        // we're finished with the database, close the connection:
    } else {

        error_reporting(0);
        $currentURL = $_SERVER['REQUEST_URI'];

        echo <<<_END
        <form action="$currentURL" method="post">
          Please fill in the following fields:<br>
          Username: <input type="text" name="username" minlength="3" maxlength="16" value="$username" required> $arrayOfErrors[0]
          <br>
          Email: <input type="email" name="email" minlength="3" maxlength="64" value="$email" required> $arrayOfErrors[1]
          <br>
          Password: <input type="password" name="password" maxlength="32" value="$password"> Leave blank for an auto-generated password $arrayOfErrors[2]
          <br>
          First name: <input type="text" name="firstname" minlength="2" maxlength="16" value="$firstname" required> $arrayOfErrors[3]
          <br>
          Surname: <input type="text" name="surname" minlength="2" maxlength="24" value="$surname" required> $arrayOfErrors[4]
          <br>
          Phone number: <input type="text" name="number" min=length"11" maxlength="11" value="$number" required> $arrayOfErrors[5]
          <br>
          Date of birth: <input type="date" name="dob" max="$todaysDate" value="$dob" required> $arrayOfErrors[6]
          <br>
          <input type="submit" value="Submit">
        </form>
        _END;
        error_reporting(1);
    }
}
?>