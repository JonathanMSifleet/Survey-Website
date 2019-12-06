<?php

// execute the header script:
require_once "header.php";

// default values we show in the form:
$username = "";
$password = "";
// strings to hold any validation error messages:
$username_val = "";
$password_val = "";

// should we show the signin form:
$show_signin_form = false;

// checks the session variable named 'loggedInSkeleton'
if (isset($_SESSION['loggedInSkeleton'])) {
	// user is already logged in, just display a message:
	echo "You are already logged in, please log out first.<br>";
} elseif (isset($_POST['username'])) {

	// user has just tried to log in:
	// connect directly to our database (notice 4th argument) we need the connection for sanitisation:
	$connection = mysqli_connect($dbhost, $dbuser, $dbpass, $dbname);

	// if the connection fails, we need to know, so allow this exit:
	if (!$connection) {
		die("Connection failed: " . $mysqli_connect_error);
	}

	// take copies of the credentials the user submitted and sanitise (clean) them:
	$username = sanitise($_POST['username'], $connection);
	$password = sanitise($_POST['password'], $connection);

	// now validate the data (both strings must be between 1 and 16 characters long):
	// (reasons: we don't want empty credentials, and we used VARCHAR(16) in the database table)
	$username_val = validateStringLength($username, 3, 20);
	$password_val = validateStringLength($password, 6, 32);

	// concatenate all the validation results together ($errors will only be empty if ALL the data is valid):
	$errors = $username_val . $password_val;

	// check that all the validation tests passed before going to the database:
	if ($errors == "") {

		// currently only barryg, mandyb, or timmy can sign in... each with ANY password
		// you need to replace this code with code that checks the username and password against the relevant database table...

		$query = "SELECT * FROM users WHERE username='$username'";
		$result = mysqli_query($connection, $query);

		// if there was a match then set the session variables and display a success message:
		if (mysqli_num_rows($result) > 0) {
			while ($row = mysqli_fetch_array($result)) {
				if (password_verify($password, $row['password'])) {
					// set a session variable to record that this user has successfully logged in:
					$_SESSION['loggedInSkeleton'] = true;
					// and copy their username into the session data for use by our other scripts:
					$_SESSION['username'] = $username;

					// show a successful signin message:
					echo "Hi, $username, you have successfully logged in, please <a href='about.php'>click here</a><br>";
					// setcookie(session_name(), '', time()-2592000, '/'); // maybe add https +
				} else {
					$show_signin_form = true;
					// show an unsuccessful signin message:
					echo "Username not found or password is wrong<br>";
					echo "<a href = sign_in.php>Click here to try again</a><br>";
				}
			} // end of while
		} else {
			echo "Username not found or password is wrong<br>";
			echo "<a href = sign_in.php>Click here to try again</a><br>";
		}
	} else {
		// validation failed, show the form again with guidance:
		$show_signin_form = true;
		// show an unsuccessful signin message:
		echo "Sign in failed, please check the errors shown above and try again<br>";
	}

	// we're finished with the database, close the connection:
	mysqli_close($connection);
} else {

	// user has arrived at the page for the first time, just show them the form:
	// show signin form:
	$show_signin_form = true;
}

if ($show_signin_form) {
	// show the form that allows users to log in
	// Note we use an HTTP POST request to avoid their password appearing in the URL:
	echo <<<_END
        <form action="sign_in.php" method="post">
          Please enter your username and password:<br>
          Username: <input type="text" name="username" minlength="3" maxlength="20" value="$username" required> $username_val
          <br>
          Password: <input type="password" name="password" minlength="6" maxlength="32" value="$password" required> $password_val
          <br>
          <input type="submit" value="Submit">
        </form>
_END;
}

// finish off the HTML for this page:
require_once "footer.php";
?>
