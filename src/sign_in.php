<?php

// Things to notice:
// The main job of this script is to execute a SELECT statement to look for the submitted username and password
// If they are found then we set $_SESSION["loggedIn"]=true;
// All other scripts check for this value before loading

// execute the header script:
require_once "header.php";

// should we show the signin form:
$show_signin_form = false;
// message to output to user:
$message = "";

// variables to make the form more functional
// values entered by user (if problems) occur
$username = "";
$password = "";
// error messages to display about each field
$username_errors = "";
$password_errors = "";
// to be used for combination of all server-side errors
$errors = "";

if (isset($_SESSION['loggedIn'])) {
    // user is already logged in, just display a message:
    echo "You are already logged in, please log out first.<br>";
} elseif (isset($_POST['username'])) {
    // user has just tried to log in, check form data against database:
    
    // take copies of the credentials the user submitted:
    $username = $_POST['username'];
    $password = $_POST['password'];
    
    // connect directly to our database (notice 4th argument):
    $connection = mysqli_connect($dbhost, $dbuser, $dbpass, $dbname);
    
    // if the connection fails, we need to know, so allow this exit:
    if (! $connection) {
        die("Connection failed: " . $mysqli_connect_error);
    }
    
    // ///////////////////////////////////////
    // ////// SERVER-SIDE VALIDATION /////////
    // ///////////////////////////////////////
    // First, sanitise the user input (functions in helper.php)
    $username = sanitise($username, $connection);
    $password = sanitise($password, $connection);
    // Next, validate the user input (functions in helper.php)
    $username_errors = validateString($username, 1, 16);
    $password_errors = validateString($password, 1, 16);
    // concatenate the errors from both validation calls
    $errors = $username_errors . $password_errors;
    
    if ($errors == "") {
        
        // check for a row in our members table with a matching username and password:
        $query = "SELECT * FROM members WHERE username='$username' AND password='$password'";
        
        // this query can return data ($result is an identifier):
        $result = mysqli_query($connection, $query);
        
        // how many rows came back? (can only be 1 or 0 because usernames are the primary key in our table):
        $n = mysqli_num_rows($result);
        
        // if there was a match then set the session variables and display a success message:
        if ($n > 0) {
            // set a session variable to record that this user has successfully logged in:
            $_SESSION['loggedIn'] = true;
            // and copy their username into the session data for use by our other scripts:
            $_SESSION['username'] = $username;
            
            // show a successful signin message:
            $message = "Hi, $username, you have successfully logged in, please <a href='show_favourite.php'>click here</a><br>";
        } else {
            // no matching credentials found so redisplay the signin` form with a failure message:
            $show_signin_form = true;
            // show an unsuccessful signin message:
            $message = "Sign in failed, please try again<br>";
        }
        
        // we're finished with the database, close the connection:
        mysqli_close($connection);
    }
    else {
        echo "<b>Sign-up Failed";
        echo "<br><br></b>";
        $show_signin_form = true;
    }
} else {
    // user has arrived at the page for the first time, just ask them to log in:
    
    // show signin form:
    $show_signin_form = true;
}

if ($show_signin_form) {
    // show the form that allows users to log in
    // Note we use an HTTP POST request to avoid their password appearing in the URL:
    echo <<<_END
    <form action="sign_in.php" method="post">
      Sign in to see your favourite film:<br>
      Username: <input type="text" minlength="1" maxlength="16" name="username" value="$username" required>
      <br>
      Password: <input type="password" minlength="1" maxlength="16" name="password" value="$password" required>
      <br>
      <input type="submit" value="Sign-In">
    </form>
    _END;
}

/*
 *
 * /// VERSION WITHOUT CLIENT VALIDATION - FOR TESTING SERVER-SIDE VALIDATION EASILY:
 *
 * if ($show_signin_form)
 * {
 * // show the form that allows users to log in
 * // Note we use an HTTP POST request to avoid their password appearing in the URL:
 * echo <<<_END
 * <form action="sign_in.php" method="post">
 * Sign in to see your favourite film:<br>
 * Username: <input type="text" name="username" value="$username"> <b><i>$username_errors</b></i>
 * <br>
 * Password: <input type="password" name="password" value="$password"> <b><i>$password_errors</b></i>
 * <br>
 * <input type="submit" value="Sign-In">
 * </form>
 * _END;
 * }
 */
 
 echo $message;
 
 // finish off the HTML for this page:
 require_once "footer.php";
 ?>