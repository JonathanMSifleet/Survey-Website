<?php

// Things to notice:
// The main job of this script is to execute an INSERT statement to add the submitted username, password and email address
// However, the assignment specification tells you that you need more fields than this for each user.
// So you will need to amend this script to include them. Don't forget to update your database (create_data.php) in tandem so they match
// This script does client-side validation using "password","text" inputs and "required","maxlength" attributes (but we can't rely on it happening!)
// we sanitise the user's credentials - see helper.php (included via header.php) for the sanitisation function
// we validate the user's credentials - see helper.php (included via header.php) for the validation functions
// the validation functions all follow the same rule: return an empty string if the data is valid...
// ... otherwise return a help message saying what is wrong with the data.
// if validation of any field fails then we display the help messages (see previous) when re-displaying the form

// execute the header script:
require_once "header.php";

// default values we show in the form:
$username = "";
$email = "";
$password = "";
$firstname = ""; // +
$surname = ""; // +
$number = ""; // +
$DOB = ""; // +

$arrayOfErrors;

// global: +
$todaysDate = date('Y-m-d'); // get current date: +

// should we show the signup form?:
$show_signup_form = false;
// message to output to user:
$message = "";

// checks the session variable named 'loggedInSkeleton'
if (isset($_SESSION['loggedInSkeleton'])) {
    // user is already logged in, just display a message:
    echo "You are already logged in, please log out if you wish to create a new account<br>";
} elseif (isset($_POST['username'])) {
    // user just tried to sign up:

    // connect directly to our database (notice 4th argument) we need the connection for sanitisation:
    $connection = mysqli_connect($dbhost, $dbuser, $dbpass, $dbname);

    // if the connection fails, we need to know, so allow this exit:
    if (! $connection) {
        die("Connection failed: " . $mysqli_connect_error);
    }

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
} else {

    // just a normal visit to the page, show the signup form:

    $show_signup_form = true;
}

if ($show_signup_form) {

    // show the form that allows users to sign up
    // Note we use an HTTP POST request to avoid their password appearing in the URL:

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
      Phone number: <input type="text" name="number" minlength="11" maxlength="11" value="$number" required> $arrayOfErrors[5]
      <br>
      Date of birth: <input type="date" name="dob" max="$todaysDate" value="$dob" required> $arrayOfErrors[6]
      <br>
      <input type="submit" value="Submit">
    </form>	
    _END;
}

// display our message to the user:
echo $message;

// finish off the HTML for this page:
require_once "footer.php";

?>