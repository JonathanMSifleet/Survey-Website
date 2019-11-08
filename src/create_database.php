<?php

// Create suitable test data for each of those tables
// NOTE: this last one is VERY IMPORTANT - you need to include test data that enables the markers to test all of your site's functionality

// read in the details of our MySQL server:
require_once "header.php"; // for ease of use+
require_once "credentials.php";

// We'll use the procedural (rather than object oriented) mysqli calls

// connect to the host:
$connection = mysqli_connect($dbhost, $dbuser, $dbpass);

$time_pre = microtime(true);

dropDatabase($connection, $dbname);

createDatabase($connection, $dbname);

$arrayOfTableNames[0] = "users";
$arrayOfTableNames[1] = "surveys";
$arrayOfTableNames[2] = "questions";
$arrayOfTableNames[3] = "responses";

$arrayOfCreateQueries[0] = "CREATE TABLE users (username VARCHAR(20), firstname VARCHAR(16), surname VARCHAR(20), password VARCHAR(60), email VARCHAR(64), number VARCHAR(11), dob DATE, PRIMARY KEY(username))"; // phone number is a varchar rather than using tel, because tel relies on american formatting, and there is no html tag for an integer
$arrayOfCreateQueries[1] = "CREATE TABLE surveys (surveyID VARCHAR(32), username VARCHAR(16), title VARCHAR(64), instructions VARCHAR(65534), numQuestions SMALLINT, type VARCHAR(16), topic VARCHAR(12), FOREIGN KEY (username) REFERENCES users(username), PRIMARY KEY (surveyID))";
$arrayOfCreateQueries[2] = "CREATE TABLE questions (questionID INT AUTO_INCREMENT, surveyID VARCHAR(32), questionName VARCHAR(128), type VARCHAR(32), isMandatory BOOLEAN, FOREIGN KEY (surveyID) REFERENCES surveys(surveyID), PRIMARY KEY (questionID))";
$arrayOfCreateQueries[3] = "CREATE TABLE responses (questionID INT, username VARCHAR(20), responseID INT AUTO_INCREMENT,  response VARCHAR(65533), FOREIGN KEY (username) REFERENCES users(username), FOREIGN KEY (questionID) REFERENCES questions(questionID), PRIMARY KEY (responseID))";

for ($i = 0; $i <= 3; $i ++) {
    createTable($connection, $dbname, $arrayOfTableNames[$i], $arrayOfCreateQueries[$i]);
}

insertDefaultUsers($connection);

$time_post = microtime(true);

$timeTaken = calculateTimeTaken($time_pre, $time_post);

echo "<br>Time taken: " . $timeTaken . " seconds";

// we're finished, close the connection:
mysqli_close($connection);

function calculateTimeTaken($time_pre, $time_post)
{
    $timeTaken = $time_post - $time_pre;
    $timeTaken = $timeTaken * 1000;
    $timeTaken = floor($timeTaken);
    $timeTaken = $timeTaken / 1000;

    return $timeTaken;
}

function dropDatabase($connection, $dbname)
{
    // exit the script with a useful message if there was an error:
    if (! $connection) {
        die("Connection failed: " . $mysqli_connect_error);
    }

    // build a statement to create a new database:
    $sql = "DROP DATABASE IF EXISTS " . $dbname;

    // no data returned, we just test for true(success)/false(failure):
    if (mysqli_query($connection, $sql)) {
        echo "Database dropped successfully<br>";
    } else {
        die("Error dropping database: " . mysqli_error($connection));
    }
}

function createTable($connection, $dbname, $tableName, $sql)
{

    // if there's an old version of our table, then drop it:
    $sql = "DROP TABLE IF EXISTS $tableName";

    // no data returned, we just test for true(success)/false(failure):
    if (mysqli_query($connection, $sql)) {
        echo "Dropped existing table: $tableName<br>";
    } else {
        die("Error checking for $tableName table: " . mysqli_error($connection));
    }

    // no data returned, we just test for true(success)/false(failure):
    if (mysqli_query($connection, $sql)) {
        echo "Table created successfully: $tableName";
        echo "<br>";
        echo "<br>";
        
    } else {
        die("Error creating table: " . mysqli_error($connection));
    }
}

function createDatabase($connection, $dbname)
{
    // exit the script with a useful message if there was an error:
    if (! $connection) {
        die("Connection failed: " . $mysqli_connect_error);
    }

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

function insertDefaultUsers($connection)
{
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
            $passwords[$i] = generateAlphanumericString();
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
}

?>