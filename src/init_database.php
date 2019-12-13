<?php
require_once "header.php";
require_once "credentials.php";

$connection = mysqli_connect($dbhost, $dbuser, $dbpass);

if (isset($_SESSION['username'])) {
	if ($_SESSION['username'] == "admin") {
		$userIsAdmin = true;
	}
} else {
	$userIsAdmin = false;
}

$query = "SELECT * FROM users";
$result = mysqli_query($connection, $query);

if ($result) {
	$dbExists = true;
} else {
	echo mysqli_error($connection) . "<br>";
	$dbExists = false;
}

if ($dbExists == false || ($dbExists == true && $userIsAdmin == true)) {
	// connect to the host:
	$connection = mysqli_connect($dbhost, $dbuser, $dbpass);

	// exit the script with a useful message if there was an error:
	if (!$connection) {
		die("Connection failed: " . mysqli_connect_error());
	}

	// begin timer:
	$time_pre = microtime(true);

	// if database exists, drop it:
	dropDatabase($connection, $dbname);

	// create database and tables:
	createDatabase($connection, $dbname);
	createUserTable($connection);
	createSurveyTable($connection);
	createQuestionTable($connection);
	createQuestionOptionsTable($connection);
	createResponseTable($connection);

	// insert default users into database;
	insertDefaultUsers($connection);
	// insert default survey into database:
	createDefaultSurvey($connection);

	// stop timer:
	$time_post = microtime(true);
	// calculate difference between stop and start time:
	$timeTaken = calculateTimeTaken($time_pre, $time_post);
	// display time taken to initiate database:
	echo "<br>Time taken: " . $timeTaken . " seconds<br>";

	// we're finished, close the connection:
	mysqli_close($connection);
} else {
	echo "The database must not exist, or you must be the admin to re-initialise the database!";
}

// finish off the HTML for this page:
require_once "footer.php";

// calculates time taken between survey being initiated and it being finished:
function calculateTimeTaken($time_pre, $time_post) {
	$timeTaken = $time_post - $time_pre;
	$timeTaken = $timeTaken * 1000;
	$timeTaken = floor($timeTaken);
	$timeTaken = $timeTaken / 1000;
	return $timeTaken;
}

// if database exists, drop it:
function dropDatabase($connection, $dbname) {
	// build a statement to create a new database:
	$sql = "DROP DATABASE IF EXISTS " . $dbname;
	// no data returned, we just test for true(success)/false(failure):
	if (mysqli_query($connection, $sql)) {
		echo "Database dropped successfully<br>";
	} else {
		die("Error dropping database: " . mysqli_error($connection));
	}
}

// create database:
function createDatabase($connection, $dbname) {
	// build a statement to create a new database:
	$sql = "CREATE DATABASE IF NOT EXISTS " . $dbname;
	// no data returned, we just test for true(success)/false(failure):
	if (mysqli_query($connection, $sql)) {
		echo "Database created successfully <br>";
	} else {
		die("Error creating database: " . mysqli_error($connection));
	}
	// connect to our database:
	mysqli_select_db($connection, $dbname);
}

// create user table:
function createUserTable($connection) {
	// if there's an old version of our table, then drop it:
	$sql = "DROP TABLE IF EXISTS users";
	// no data returned, we just test for true(success)/false(failure):
	if (mysqli_query($connection, $sql)) {
		echo "Dropped existing table: users<br>";
	} else {
		die("Error checking for user table: " . mysqli_error($connection));
	}
	// make our table:
	$sql = "CREATE TABLE users (username VARCHAR(20), firstname VARCHAR(16), surname VARCHAR(20), password VARCHAR(60), email VARCHAR(64), number VARCHAR(11), dob DATE, PRIMARY KEY(username))"; // phone number is a varchar rather than using tel, because tel relies on american formatting, and there is no html tag for an integer
	// no data returned, we just test for true(success)/false(failure):
	if (mysqli_query($connection, $sql)) {
		echo "Table created successfully: users<br>";
	} else {
		die("Error creating table: " . mysqli_error($connection));
	}
}

// create survey table:
function createSurveyTable($connection) {
	// if there's an old version of our table, then drop it:
	$sql = "DROP TABLE IF EXISTS surveys";
	// no data returned, we just test for true(success)/false(failure):
	if (mysqli_query($connection, $sql)) {
		echo "Dropped existing table: surveys<br>";
	} else {
		die("Error checking for survey table: " . mysqli_error($connection));
	}
	// make our table:
	$sql = "CREATE TABLE surveys (surveyID VARCHAR(32), username VARCHAR(20), title VARCHAR(64), instructions MEDIUMTEXT, numQuestions SMALLINT, topic VARCHAR(12), FOREIGN KEY (username) REFERENCES users(username) ON DELETE CASCADE, PRIMARY KEY (surveyID))";
	// no data returned, we just test for true(success)/false(failure):
	if (mysqli_query($connection, $sql)) {
		echo "Table created successfully: surveys<br>";
	} else {
		die("Error creating table: " . mysqli_error($connection));
	}
}

// create question table:
function createQuestionTable($connection) {
	// if there's an old version of our table, then drop it:
	$sql = "DROP TABLE IF EXISTS questions";
	// no data returned, we just test for true(success)/false(failure):
	if (mysqli_query($connection, $sql)) {
		echo "Dropped existing table: questions<br>";
	} else {
		die("Error checking for existing table: " . mysqli_error($connection));
	}
	// make our table:
	$sql = "CREATE TABLE questions (questionID VARCHAR(32), surveyID VARCHAR(32), questionNo TINYINT, questionName VARCHAR(128), type VARCHAR(12), numOptions SMALLINT, required BOOLEAN, FOREIGN KEY (surveyID) REFERENCES surveys(surveyID) ON DELETE CASCADE, PRIMARY KEY (questionID))";
	// no data returned, we just test for true(success)/false(failure):
	if (mysqli_query($connection, $sql)) {
		echo "Table created successfully: questions<br>";
	} else {
		die("Error creating table: " . mysqli_error($connection));
	}
}

// create question options table:
function createQuestionOptionsTable($connection) {
	// if there's an old version of our table, then drop it:
	$sql = "DROP TABLE IF EXISTS question_options";
	// no data returned, we just test for true(success)/false(failure):
	if (mysqli_query($connection, $sql)) {
		echo "Dropped existing table: question_options<br>";
	} else {
		die("Error checking for existing table: " . mysqli_error($connection));
	}
	// make our table:
	$sql = "CREATE TABLE question_options (questionID VARCHAR(32), optionName VARCHAR(32), optionNo TINYINT, FOREIGN KEY (questionID) REFERENCES questions(questionID) ON DELETE CASCADE, PRIMARY KEY (questionID, optionName))";
	// no data returned, we just test for true(success)/false(failure):
	if (mysqli_query($connection, $sql)) {
		echo "Table created successfully: questions<br>";
	} else {
		die("Error creating table: " . mysqli_error($connection));
	}
}

// create response table:
function createResponseTable($connection) {
	// if there's an old version of our table, then drop it:
	$sql = "DROP TABLE IF EXISTS responses";
	// no data returned, we just test for true(success)/false(failure):
	if (mysqli_query($connection, $sql)) {
		echo "Dropped existing table: responses<br>";
	} else {
		die("Error checking for existing table: " . mysqli_error($connection));
	}
	// make our table:
	$sql = "CREATE TABLE responses (questionID VARCHAR(32), username VARCHAR(20), responseID VARCHAR(32), response MEDIUMTEXT, FOREIGN KEY (username) REFERENCES users(username) ON DELETE CASCADE, FOREIGN KEY (questionID) REFERENCES questions(questionID) ON DELETE CASCADE, PRIMARY KEY (responseID))";
	// no data returned, we just test for true(success)/false(failure):
	if (mysqli_query($connection, $sql)) {
		echo "Table created successfully: responses<br>";
	} else {
		die("Error creating table: " . mysqli_error($connection));
	}
}

// insert default users into table:
function insertDefaultUsers($connection) {
	// put some data in our table:
	// create an array variable for each field in the DB that we want to populate
	$usernames[] = 'admin';
	$emails[] = 'jonathanmsifleet@gmail.com';
	$firstnames[] = '';
	$surnames[] = '';
	$numbers[] = '';
	$dobs[] = '';
	$usernames[] = 'barrym';
	$emails[] = 'barry@m-domain.com';
	$firstnames[] = 'Barry';
	$surnames[] = 'madeup';
	$numbers[] = '07123456789';
	$dobs[] = '1990-04-13';
	$usernames[] = 'mandyb';
	$emails[] = 'webmaster@mandy-g.co.uk';
	$firstnames[] = 'Mandy';
	$surnames[] = 'Basic';
	$numbers[] = '07123456710';
	$dobs[] = '1991-04-28';
	$usernames[] = 'timmy';
	$emails[] = 'timmy@lassie.com';
	$firstnames[] = 'Timmy';
	$surnames[] = 'Turner';
	$numbers[] = '07123456711';
	$dobs[] = '1992-07-17';
	$usernames[] = 'briang';
	$emails[] = 'brian@quahog.gov';
	$firstnames[] = 'Brian';
	$surnames[] = 'Lifeof';
	$numbers[] = '07123456712';
	$dobs[] = '1993-02-09';
	$usernames[] = 'abc';
	$passwords[] = 'N!N4dhS-mUS&_2Jm';
	$emails[] = 'a@alphabet.test.com';
	$firstnames[] = 'Alphabet';
	$surnames[] = 'Test';
	$numbers[] = '07123456713';
	$dobs[] = '1994-10-12';
	$usernames[] = 'bcde';
	$emails[] = 'b@alphabet.test.com';
	$firstnames[] = 'Brandon';
	$surnames[] = 'Stark';
	$numbers[] = '07123456714';
	$dobs[] = '1995-03-08';
	$usernames[] = 'cdefg';
	$emails[] = 'c@alphabet.test.com';
	$firstnames[] = 'Chris';
	$surnames[] = 'Topher';
	$numbers[] = '07123456715';
	$dobs[] = '1996-05-16';
	$usernames[] = 'defg';
	$passwords[] = '&2M!qTq4MkhjDGZr';
	$emails[] = 'd@alphabet.test.com';
	$firstnames[] = 'Dee';
	$surnames[] = 'Sweet';
	$numbers[] = '07123456716';
	$dobs[] = '1997-11-19';

	// loop through the arrays above and add rows to the table:
	for ($i = 0; $i < count($usernames); $i++) {
		// this is made by me:
		if ($i == 0) {
			$passwords[$i] = 'secret'; // manually overrides admin password
		} else {
			$passwords[$i] = generateAlphanumericString();
		}
		// ///////
		$passwords[$i] = encryptInput($passwords[$i]); // encrypt password before entering DB
		// create the SQL query to be executed
		$sql = "INSERT INTO users (username, firstname, surname, password, email, number, dob) VALUES ('$usernames[$i]','$firstnames[$i]','$surnames[$i]','$passwords[$i]','$emails[$i]','$numbers[$i]', '$dobs[$i]')";
		// run the above query '$sql' on our DB
		// no data returned, we just test for true(success)/false(failure):
		if (mysqli_query($connection, $sql)) {
			echo "row inserted<br>";
		} else {
			die("Error inserting row: " . mysqli_error($connection));
		}
	}
}

// create default survey
function createDefaultSurvey($connection) {
	$surveyID = "";

	insertDefaultSurvey($connection, $surveyID);

	$arrayOfQuestionIDs = array();

	insertDefaultQuestions($connection, $surveyID, $arrayOfQuestionIDs);
	insertDefaultOptions($connection, $arrayOfQuestionIDs);
	insertDefaultResponses($connection, $arrayOfQuestionIDs);
}

// insert default survey into database:
function insertDefaultSurvey($connection, &$surveyID) {
	$title = "Website Feedback";
	$surveyID = md5("admin" . $title);
	$instructions = "This is the default survey. The survey contains five questions and five question types. This survey acts as feedback for the site";

	$query = "INSERT INTO surveys(surveyID, username, title, instructions, numQuestions, topic) VALUES ('$surveyID', 'admin', '$title','$instructions','5','feedback')";
	$result = mysqli_query($connection, $query);

	if ($result) {
		echo "Default survey inserted successfully<br>";
	} else {
		// show an unsuccessful signup message:
		echo mysqli_error($connection) . "<br>";
	}
}

// insert default survey questions into database:
function insertDefaultQuestions($connection, $surveyID, &$arrayOfQuestionIDs) {
	$arrayOfQuestions = array(
		"What is todays date?",
		"How satisfied are you with the website?",
		"What could be improved with the website?",
		"How likely are you to recommend the website?",
		"What have you used this website for?"
	);

	$arrayOfQuestionTypes = array(
		"date",
		"dropdown",
		"longAnswer",
		"multOption",
		"shortAnswer"
	);

	$arrayOfNumOptions = array(
		"1",
		"5",
		"1",
		"5",
		"1"
	);

	for ($i = 0; $i < 5; $i++) {
		$questionID = md5($surveyID . $arrayOfQuestions[$i]);
		$query = "INSERT INTO questions (questionID, surveyID, questionNo, questionName, type, numOptions, required) VALUES ('$questionID', '$surveyID','$i', '$arrayOfQuestions[$i]', '$arrayOfQuestionTypes[$i]', '$arrayOfNumOptions[$i]','1')";
		$result = mysqli_query($connection, $query);

		if ($result) {
			array_push($arrayOfQuestionIDs, $questionID);
			echo "Question " . ($i + 1) . " inserted succesfully<br>";
		} else {
			echo "Error: " . mysqli_error($connection) . "<br>";
		}
	}
}

// insert default question options into survey:
function insertDefaultOptions($connection, $arrayOfQuestionIDs) {

	// question 1:
	$arrayOfOptions = array(
		"Very dissatisfied",
		"Slightly dissatisfied",
		"Neutral",
		"Slightly satisfied",
		"Very satisfied"
	);

	for ($i = 0; $i < 5; $i++) {
		$query = "INSERT INTO question_options (questionID, optionName, optionNo) VALUES ('$arrayOfQuestionIDs[1]', '$arrayOfOptions[$i]', '$i')";
		$result = mysqli_query($connection, $query);

		if ($result) {
			echo "Option " . ($i + 1) . " inserted successfully<br>";
		} else {
			echo "Error: " . mysqli_error($connection) . "<br>";
		}
	}

	// question 3:

	$arrayOfOptions = array(
		"Highly unlikely",
		"Somewhat unlikely",
		"Neutral",
		"Slightly likely",
		"Highly likely"
	);

	for ($i = 0; $i < 5; $i++) {
		$query = "INSERT INTO question_options (questionID, optionName, optionNo) VALUES ('$arrayOfQuestionIDs[3]', '$arrayOfOptions[$i]', '$i')";
		$result = mysqli_query($connection, $query);

		if ($result) {
			echo "Option " . ($i + 1) . " inserted succesfully<br>";
		} else {
			echo "Error: " . mysqli_error($connection) . "<br>";
		}
	}
}

// inserts pre-defined responses into table, default-responses show off graphing abilities:
function insertDefaultResponses($connection, $arrayOfQuestionIDs) {
	$arrayOfUsers = array();

	$arrayOfUsers[] = "timmy";
	$arrayOfUsers[] = "mandyb";
	$arrayOfUsers[] = "admin";
	$arrayOfUsers[] = "briang";

	// creates pre-defined array of responses:
	$arrayOfResponses = array(
		"2019-12-01",
		"Slightly dissatisfied",
		"Sharing surveys with friends",
		"Neutral",
		"Creating polls for friends",
		"2019-12-02",
		"Slightly satisfied",
		"Site security",
		"Slightly likely",
		"Business use",
		"2019-12-11",
		"Very dissatisfied",
		"Visual appearance",
		"Slightly likely",
		"I haven’t",
		"2019-12-11",
		"Slightly satisfied",
		"Add more features",
		"Highly unlikely",
		"Data gathering"
	);

	$counter = 0;
	$questionCounter = 0;

	// insert each response into table:
	for ($i = 0; $i <= count($arrayOfResponses) - 1; $i++) {

		if ($counter % 4 == 0) {
			$counter = 0;
		}

		if ($questionCounter % 5 == 0) {
			$questionCounter = 0;
		}

		$responseID = md5("af57a209f9e756664ef282d11a385c70" . $arrayOfQuestionIDs[$questionCounter] . $arrayOfUsers[$counter] . $arrayOfResponses[$i]);

		$query = "INSERT INTO responses (questionID, username, responseID, response) VALUES ('{$arrayOfQuestionIDs[$questionCounter]}','{$arrayOfUsers[$counter]}', '$responseID', '$arrayOfResponses[$i]')";
		$result = mysqli_query($connection, $query);

		if ($result) {
			echo "Success entering default response <br>";
		} else {
			echo mysqli_error($connection);
		}

		$counter++;
		$questionCounter++;
	}
}

?>