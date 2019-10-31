<?php
// Things to notice:
// You need to add code to this script to implement the admin functions and features
// Notice that the code not only checks whether the user is logged in, but also whether they are the admin, before it displays the page content
// When an admin user is verified, you can implement all the admin tools functionality from this script, or distribute them over multiple pages - your choice
// execute the header script:
require_once "header.php";

$newInput = "";

// checks the session variable named 'loggedInSkeleton'
// take note that of the '!' (NOT operator) that precedes the 'isset' function
if (! isset($_SESSION['loggedInSkeleton'])) {
    // user isn't logged in, display a message saying they must be:
    echo "You must be logged in to view this page.<br>";
} // the user must be signed-in, show them suitable page content
else {
    // only display the page content if this is the admin account (all other users get a "you don't have permission..." message):
    $connection = mysqli_connect($dbhost, $dbuser, $dbpass, $dbname);
    if ($_SESSION['username'] == "admin") {

        echo "Click to create a new account: <br>";

        echo "<a href ={$_SERVER['REQUEST_URI']}?createAccount=true>Create user account</a>";
        echo "<br><br>";

        if (isset($_GET['createAccount'])) {
            createAccount($dbhost, $dbuser, $dbpass, $dbname);
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
                printUserData($dbhost, $dbuser, $dbpass, $dbname);

                if (isset($_GET['deleteAccount'])) {
                    deleteAccount($dbhost, $dbuser, $dbpass, $dbname);
                } else {

                    $editAccount = isset($_GET['editAccountDetails']);

                    if ($editAccount == true) {
                        $currentURL = $_SERVER['REQUEST_URI'];

                        $superGlobalToTrim = returnCleanVariableToChange($currentURL);
                        $superGlobalToTrim = returnCleanVariableToChange($superGlobalToTrim);
                        $superGlobalToTrim = substr($superGlobalToTrim, 6, strlen($superGlobalToTrim));
                        $trimmedSuperGlobal = substr($superGlobalToTrim, 0, strlen($superGlobalToTrim) - 5);

                        if ($trimmedSuperGlobal !== "") {
                            switch ($trimmedSuperGlobal) {
                                case $trimmedSuperGlobal == "email":
                                    $fieldType = "email";
                                    break;
                                case $trimmedSuperGlobal == "password":
                                    $fieldType = "password";
                                    break;
                                case 2:
                                    $fieldType = "text";
                                    break;
                                case $trimmedSuperGlobal == "firstname":
                                    $fieldType = "text";
                                    break;
                                case $trimmedSuperGlobal == "surname":
                                    $fieldType = "text";
                                    break;
                                case $trimmedSuperGlobal == "number":
                                    $fieldType = "text";
                                    break;
                                case $trimmedSuperGlobal == "dob":
                                    $fieldType = "date";
                                    break;
                                default:
                                    $fieldType = "";
                            } // end of switch
                            changeUserDetails($dbhost, $dbuser, $dbpass, $dbname, $trimmedSuperGlobal, $fieldType);
                        } // end of if
                    }
                }
            }
        }
    } else {
        echo "You don't have permission to view this page...<br>";
    }
}
// finish off the HTML for this page:
require_once "footer.php";

// this function gets the username of the selected user from the session superglobal, gets all their information using an SQL query, displays it in a table
// then shows the options to either change the password or delete the account
// this function is written by me:
function printUserData($dbhost, $dbuser, $dbpass, $dbname)
{
    $username = $_GET["username"];

    echo "<br>";

    $connection = mysqli_connect($dbhost, $dbuser, $dbpass, $dbname);
    $query = "SELECT * FROM users WHERE username = '$username'"; // +
    $result = mysqli_query($connection, $query); // +

    echo "User's details:";
    echo "<table border ='1'>";
    echo "<tr><td>username</td><td>firstname</td><td>surname</td><td>password</td><td>email</td><td>number</td><td>dob</td></tr>";

    while ($row = mysqli_fetch_assoc($result)) {
        echo "<tr><td>{$row['username']}</td><td>{$row['firstname']}</td><td>{$row['surname']}</td><td>{$row['password']}</td><td>{$row['email']}</td><td>{$row['number']}</td><td>{$row['dob']}</td></tr>";
    }
    echo "</table>";

    echo "<br>";
    echo "<a href =admin.php?username=$username&editAccountDetails=true&changeEmail=true>Change email</a>";
    echo " ";
    echo "<a href =admin.php?username=$username&editAccountDetails=true&changePassword=true>Change password</a>";
    echo " ";
    echo "<a href =admin.php?username=$username&editAccountDetails=true&changeFirstname=true>Change firstname</a>";
    echo " ";
    echo "<a href =admin.php?username=$username&editAccountDetails=true&changeSurname=true>Change surname</a>";
    echo " ";
    echo "<a href =admin.php?username=$username&editAccountDetails=true&changeNumber=true>Change number</a>";
    echo " ";
    echo "<a href =admin.php?username=$username&editAccountDetails=true&changedob=true>Change date of birth</a>";
    echo " ";
    echo "<a href =admin.php?username=$username&editAccountDetails=true&deleteAccount=true>Delete user account</a>";
}

function createAccount($dbhost, $dbuser, $dbpass, $dbname)
{
    $currentURL = $_SERVER['REQUEST_URI'];

    // default values we show in the form:
    $username = "";
    $firstname = ""; // +
    $surname = ""; // +
    $password = "";
    $email = "";
    $number = ""; // +
    $dob = ""; // +

    // global: +
    $todaysDate = date('Y-m-d'); // get current date: +

    // strings to hold any validation error messages:
    $username = "";
    $email = "";
    $password = "";
    $firstname = ""; // +
    $surname = ""; // +
    $number = ""; // +
    $dob = ""; // +

    echo <<<_END
    <form action="sign_up.php" method="post">
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
      Phone number: <input type="text" name="number" min="11" max="11" value="$number" required> $arrayOfErrors[5]
      <br>
      Date of birth: <input type="date" name="dob" max="$todaysDate" value="$dob" required> $arrayOfErrors[6]
      <br>
      <input type="submit" value="Submit">
    </form>
    _END;

    if (isset($_POST['username'])) {

        // connect directly to our database (notice 4th argument) we need the connection for sanitisation:
        $connection = mysqli_connect($dbhost, $dbuser, $dbpass, $dbname);

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
                $message = "Signup was successful. Please sign in<br>";
            } else {
                // show the form:
                $show_signup_form = true;
                // show an unsuccessful signup message:
                $message = "Sign up failed, please try again<br>";
            }
        } else {
            // validation failed, show the form again with guidance:
            $show_signup_form = true;
            // show an unsuccessful signin message:
            $message = "Sign up failed, please check the errors shown above and try again<br>";
        }
        // we're finished with the database, close the connection:
        mysqli_close($connection);
    }
}

// this function gets the select user's username from the session superglobal, asks the admin to fill in a new password for the user
// then updates the user's password via an SQL query
// this function is written by me
function changeUserDetails($dbhost, $dbuser, $dbpass, $dbname, $fieldToChange, $fieldType)
{
    $username = $_GET["username"];

    if ($username == "admin") {
        echo "<br>";
        echo "The admin's " . $fieldToChange . " cannot be changed";
    } else {
        echo "<br>";

        // $password_val

        $currentURL = $_SERVER['REQUEST_URI'];

        $fieldTypeToDisplay = ucfirst($fieldToChange);

        echo <<<_END
        <form action="$currentURL" method="post">
          Please fill in the following fields:<br>
          $fieldTypeToDisplay: <input type="$fieldType" name="newInput" minlength="12" maxlength="32">
          <br>
          <input type="submit" value="Submit">
        </form>
        _END;

        if (isset($_POST['newPassword'])) {
            $connection = mysqli_connect($dbhost, $dbuser, $dbpass, $dbname);

            $newInput = sanitise($_POST['newInput'], $connection);
            $newPassword_val = validatePassword($newInput, 12, 31);

            if ($newPassword_val == "") {
                $newInput = encryptInput($newInput);
                $query = "UPDATE users SET password='$newInput' WHERE username = '$username'";
                $result = mysqli_query($connection, $query); // +
            }
            if ($result) {
                echo "Password changed";
            } else {
                echo "Password failed to change";
            }
        } // end of isset
    } // end of admin if
}

// this function gets the username of the selected user from the session superglobal, then deletes the account via an SQL query
// this function is written by me:
function deleteAccount($dbhost, $dbuser, $dbpass, $dbname)
{
    $username = $_GET["username"];

    if ($username == "admin") {
        echo "The admin account cannot be deleted";
    } else {
        echo "<br>";
        echo "are you sure you want to delete " . $username . "? ";
        echo "<a href ={$_SERVER['REQUEST_URI']}&confirmDeletion=true>Yes</a>";
        echo " ";
        echo "<a href =admin.php?username=$username>Cancel</a>";

        $shouldDeleteAccount = ""; // required to fix undefined index error

        $shouldDeleteAccount = $_GET["confirmDeletion"];

        if ($shouldDeleteAccount == "true") {
            $connection = mysqli_connect($dbhost, $dbuser, $dbpass, $dbname);
            $query = "DELETE FROM users WHERE username = '$username'";
            $result = mysqli_query($connection, $query); // +

            echo "Account deleted";
        }
    }
}
?>