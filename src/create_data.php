<?php

// Things to notice:
// This file is the first one we will run when we mark your submission
// Its job is to:
// Create your database (currently called "skeleton", see credentials.php)...
// Create all the tables you will need inside your database (currently it makes a simple "users" table, you will probably need more and will want to expand fields in the users table to meet the assignment specification)...
// Create suitable test data for each of those tables
// NOTE: this last one is VERY IMPORTANT - you need to include test data that enables the markers to test all of your site's functionality

// read in the details of our MySQL server:
require_once "header.php"; // for ease of use+
require_once "credentials.php";

// We'll use the procedural (rather than object oriented) mysqli calls

// connect to the host:
$connection = mysqli_connect($dbhost, $dbuser, $dbpass);

// exit the script with a useful message if there was an error:
if (! $connection) {
    die("Connection failed: " . $mysqli_connect_error);
}

// build a statement to create a new database:
$sql = "CREATE DATABASE IF NOT EXISTS " . $dbname;

// no data returned, we just test for true(success)/false(failure):
if (mysqli_query($connection, $sql)) {
    echo "Database created successfully, or already exists<br>";
} else {
    die("Error creating database: " . mysqli_error($connection));
}

// connect to our database:
mysqli_select_db($connection, $dbname);

// /////////////////////////////////////////
// //////////// USERS TABLE //////////////
// /////////////////////////////////////////

// if there's an old version of our table, then drop it:
$sql = "DROP TABLE IF EXISTS users";

// no data returned, we just test for true(success)/false(failure):
if (mysqli_query($connection, $sql)) {
    echo "Dropped existing table: users<br>";
} else {
    die("Error checking for existing table: " . mysqli_error($connection));
}

// make our table:
// notice that the username field is a PRIMARY KEY and so must be unique in each record
$sql = "CREATE TABLE users (username VARCHAR(20), firstname VARCHAR(16), surname VARCHAR(20), password VARCHAR(60), email VARCHAR(64), number VARCHAR(11), dob DATE, PRIMARY KEY(username))"; // +
                                                                                                                                                                                              // phone number is a varchar rather than using tel, because tel relies on american formatting, and there is no html tag for an integer

// no data returned, we just test for true(success)/false(failure):
if (mysqli_query($connection, $sql)) {
    echo "Table created successfully: users<br>";
} else {
    die("Error creating table: " . mysqli_error($connection));
}

// put some data in our table:
// create an array variable for each field in the DB that we want to populate

// this whole section is edditted:
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
// end of editted section

// loop through the arrays above and add rows to the table:
for ($i = 0; $i < count($usernames); $i ++) {

    // this is made by me:
    if ($i == 0) {
        $passwords[$i] = 'secret'; // manually overrides admin password
    } else {
        $passwords[$i] = encryptInput(generateAlphanumericString());
    }
    // ///////

    $passwords[$i] = encryptInput($passwords[$i]); // encrypt password before entering DB +

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

// /////////////
// survey table:

// if there's an old version of our table, then drop it:
$sql = "DROP TABLE IF EXISTS surveys";

// no data returned, we just test for true(success)/false(failure):
if (mysqli_query($connection, $sql)) {
    echo "Dropped existing table: surveys<br>";
} else {
    die("Error checking for existing table: " . mysqli_error($connection));
}

// make our table:
$sql = "CREATE TABLE surveys (UID VARCHAR(32), username VARCHAR(16), title VARCHAR(64), Instructions VARCHAR(65534), NoOfQuestions SMALLINT, PRIMARY KEY (UID))";

// no data returned, we just test for true(success)/false(failure):
if (mysqli_query($connection, $sql)) {
    echo "Table created successfully: surveys<br>";
} else {
    die("Error creating table: " . mysqli_error($connection));
}

// we're finished, close the connection:
mysqli_close($connection);
?>