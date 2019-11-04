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
                printUserData($connection);

                if (isset($_GET['deleteAccount'])) {
                    deleteAccount($connection);
                } else {

                    $editAccount = isset($_GET['editAccountDetails']);

                    if ($editAccount == true) {

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

// this function gets the select user's username from the session superglobal, asks the admin to fill in a new password for the user
// then updates the user's password via an SQL query
// this function is written by me
function changeUserDetails($connection, $fieldToChange, $fieldType, $minLength, $maxLength)
{
    if (isset($_POST['newInput'])) {

        $currentUsername = $_GET['username'];

        echo "Change user details:";
        echo "<br>";

        $todaysDate = date('Y-m-d'); // get current date: +
        $newInput = sanitise($_POST['newInput'], $connection);
        $input_val = validateInput($newInput, $fieldToChange, $minLength, $maxLength, $todaysDate);

        if ($input_val == "Generate random password") {
            $newInput = generateAlphanumericString();
            $input_val = validateInput($newInput, $fieldToChange, $minLength, $maxLength);
        }

        if ($input_val == "") {
            if ($fieldType == "password") {
                $newInput = encryptInput($newInput);
                echo "<br>";
                echo "Insert a new password if your browser hasn't automatically saved your password";
            }
            $query = "UPDATE users SET $fieldToChange='$newInput' WHERE username = '$currentUsername'";
            $result = mysqli_query($connection, $query); // +

            if ($result) {
                echo "<br>";
                echo ucfirst($fieldToChange) . " changed";
            }
        } else {
            echo "<br>";
            echo "Updating field failed: " . $input_val;
        }
    } else {
        showFieldForm($fieldToChange, $fieldType, $minLength, $maxLength);
    }
}

function showFieldForm($fieldToChange, $fieldType, $minLength, $maxLength)
{
    $currentURL = $_SERVER['REQUEST_URI'];
    $fieldToDisplay = ucfirst($fieldToChange);

    if ($fieldToDisplay == "Dob") {

        $todaysDate = date('Y-m-d'); // get current date: +

        $minDate = calcEarliestDate($todaysDate);
        $maxDate = calcLatestDate($todaysDate);

        echo <<<_END
        <form action="$currentURL" method="post">
          Please fill in the following fields:<br>
          $fieldToDisplay: <input type="$fieldType" min=$minDate max=$maxDate name="newInput">
          <br>
          <input type="submit" value="Submit">
        </form>
        _END;
    } else {
        echo <<<_END
        <form action="$currentURL" method="post">
          Please fill in the following fields:<br>
          $fieldToDisplay: <input type="$fieldType" name="newInput">
          <br>
          <input type="submit" value="Submit">
        </form>
        _END;
    }
}

// this function gets the username of the selected user from the session superglobal, then deletes the account via an SQL query
// this function is written by me:
function deleteAccount($connection)
{
    $username = $_GET["username"];

    if ($username == "admin") {
        echo "The admin account cannot be deleted";
    } else {
        echo "<br>";
        echo "Are you sure you want to delete the account " . $username . "? ";
        echo "<a href ={$_SERVER['REQUEST_URI']}&confirmDeletion=true>Yes</a>";
        echo " ";
        echo "<a href =admin.php?username=$username>Cancel</a>";

        if (isset($_GET["confirmDeletion"])) {
            $query = "DELETE FROM users WHERE username = '$username'";
            $result = mysqli_query($connection, $query); // +

            echo "<br>";
            echo "Account deleted";
        }
    }
}
?>