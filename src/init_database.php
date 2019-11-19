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
createUserTable($connection);
createSurveyTable($connection);
createQuestionTable($connection);
createQuestionOptionsTable($connection);
createResponseTable($connection);

insertDefaultUsers($connection);
createDefaultSurvey($connection);

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

function createUserTable($connection)
{
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

function createSurveyTable($connection)
{
    // if there's an old version of our table, then drop it:
    $sql = "DROP TABLE IF EXISTS surveys";
    // no data returned, we just test for true(success)/false(failure):
    if (mysqli_query($connection, $sql)) {
        echo "Dropped existing table: surveys<br>";
    } else {
        die("Error checking for survey table: " . mysqli_error($connection));
    }
    // make our table:
    $sql = "CREATE TABLE surveys (surveyID VARCHAR(32), username VARCHAR(16), title VARCHAR(64), instructions VARCHAR(500), numQuestions SMALLINT, topic VARCHAR(12), FOREIGN KEY (username) REFERENCES users(username) ON DELETE CASCADE, PRIMARY KEY (surveyID))";
    // no data returned, we just test for true(success)/false(failure):
    if (mysqli_query($connection, $sql)) {
        echo "Table created successfully: surveys<br>";
    } else {
        die("Error creating table: " . mysqli_error($connection));
    }
}

function createQuestionTable($connection)
{
    // if there's an old version of our table, then drop it:
    $sql = "DROP TABLE IF EXISTS questions";
    // no data returned, we just test for true(success)/false(failure):
    if (mysqli_query($connection, $sql)) {
        echo "Dropped existing table: questions<br>";
    } else {
        die("Error checking for existing table: " . mysqli_error($connection));
    }
    // make our table:
    $sql = "CREATE TABLE questions (questionID VARCHAR(32), surveyID VARCHAR(32), questionNo VARCHAR(3), questionName VARCHAR(128), type VARCHAR(32), numOptions SMALLINT, required BOOLEAN, FOREIGN KEY (surveyID) REFERENCES surveys(surveyID) ON DELETE CASCADE, PRIMARY KEY (questionID))";
    // no data returned, we just test for true(success)/false(failure):
    if (mysqli_query($connection, $sql)) {
        echo "Table created successfully: questions<br>";
    } else {
        die("Error creating table: " . mysqli_error($connection));
    }
}

function createQuestionOptionsTable($connection)
{
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

function createResponseTable($connection)
{
    // if there's an old version of our table, then drop it:
    $sql = "DROP TABLE IF EXISTS responses";
    // no data returned, we just test for true(success)/false(failure):
    if (mysqli_query($connection, $sql)) {
        echo "Dropped existing table: responses<br>";
    } else {
        die("Error checking for existing table: " . mysqli_error($connection));
    }
    // make our table:
    $sql = "CREATE TABLE responses (questionID VARCHAR(32), username VARCHAR(20), responseID VARCHAR(32), response VARCHAR(65533), FOREIGN KEY (username) REFERENCES users(username) ON DELETE CASCADE, FOREIGN KEY (questionID) REFERENCES questions(questionID) ON DELETE CASCADE, PRIMARY KEY (responseID))";
    // no data returned, we just test for true(success)/false(failure):
    if (mysqli_query($connection, $sql)) {
        echo "Table created successfully: responses<br>";
    } else {
        die("Error creating table: " . mysqli_error($connection));
    }
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

function createDefaultSurvey($connection)
{
    $surveyID = "";

    insertDefaultSurvey($connection, $surveyID);

    $arrayOfQuestionIDs = array();

    insertDefaultQuestions($connection, $surveyID, $arrayOfQuestionIDs);
    insertDefaultOptions($connection, $surveyID, $arrayOfQuestionIDs);
}

function insertDefaultSurvey($connection, &$surveyID)
{
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

function insertDefaultQuestions($connection, $surveyID, &$arrayOfQuestionIDs)
{
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

    for ($i = 0; $i <= 4; $i ++) {
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

function insertDefaultOptions($connection, $surveyID, $arrayOfQuestionIDs)
{

    // question 1:
    $arrayOfOptions = array(
        "Very dissatisfied",
        "Slightly dissatisfied",
        "Neutral",
        "Slightly satisfied",
        "Very satisfied"
    );

    for ($i = 0; $i <= 4; $i ++) {
        $query = "INSERT INTO question_options (questionID, optionName, optionNo) VALUES ('$arrayOfQuestionIDs[1]', '$arrayOfOptions[$i]', '$i')";
        $result = mysqli_query($connection, $query);

        if ($result) {
            echo "Option " . ($i + 1) . " inserted succesfully<br>";
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

    for ($i = 0; $i <= 4; $i ++) {
        $query = "INSERT INTO question_options (questionID, optionName, optionNo) VALUES ('$arrayOfQuestionIDs[3]', '$arrayOfOptions[$i]', '$i')";
        $result = mysqli_query($connection, $query);

        if ($result) {
            echo "Option " . ($i + 1) . " inserted succesfully<br>";
        } else {
            echo "Error: " . mysqli_error($connection) . "<br>";
        }
    }
}

?>