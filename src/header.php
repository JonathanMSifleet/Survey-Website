<?php

// database connection details:
require_once "credentials.php";

// loads shared functions:
require_once "helper.php";

// start/restart the session:
// this allows use to make use of session variables
session_start();

// loads style sheet and other header information:
echo <<<_END
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <title>Jonathan's Survey Website</title>
        <link href="https://fonts.googleapis.com/css?family=Roboto" rel="stylesheet">
        <link rel="stylesheet" href="mystyle.css">
        <!--Load the AJAX API:-->
        <script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
    </head>
    <h1>2CWK50: A Survey Website</h1>
    <nav>
    <ul>
_END;

// checks the session variable named 'loggedInSkeleton'
if (isset($_SESSION['loggedInSkeleton'])) {
	// THIS PERSON IS LOGGED IN
	// show the logged in menu options:
	echo <<<_END
                    <li><a href='about.php'>About</a></li>
                    <li><a href='surveys_manage.php'>My Surveys</a></li>
                    <li><a href='view_responses.php'>My responses</a></li>
                    <li><a href='competitors.php'>Design and Analysis</a></li>
                    <li><a href='account.php'>Account</a></li>
                    <li><a href='sign_out.php'>Sign Out ({$_SESSION['username']})</a></li>
_END;

	// add an extra menu option if this was the admin:
	// this allows us to display the admin tools to them only
	if ($_SESSION['username'] == "admin") {
		echo "<li><a href='admin.php'>Admin Tools</a></li>";
	}
} else {
	// THIS PERSON IS NOT LOGGED IN
	// show the logged out menu options:
	echo <<<_END
            <li><a href='about.php'>About</a></li>
            <li><a href='competitors.php'>Design and Analysis</a></li>
            <li><a href='sign_up.php'>Sign Up</a></li>
            <li><a href='sign_in.php'>Sign In</a></li>
_END;
}
echo <<<_END
</ul>
</nav>
<br><br><br><br>
<body>
_END;
?>