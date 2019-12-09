<?php

// function to sanitise (clean) user data:
function sanitise($str, $connection)
{
	if (get_magic_quotes_gpc()) {
		// just in case server is running an old version of PHP with "magic quotes" running:
		$str = stripslashes($str);
	}

	// escape any dangerous characters, e.g. quotes:
	$str = mysqli_real_escape_string($connection, $str);
	// ensure any html code is safe by converting reserved characters to entities:
	$str = htmlentities($str);
	// return the cleaned string:
	return $str;
}

// validates input based upon field type
function validateInput($input, $fieldToChange)
{
	switch ($fieldToChange) {
		case $fieldToChange == "email":
			return validateStringLength($input, 1, 64);
		case $fieldToChange == "password":
			return validatePassword($input, 12, 32);
		case $fieldToChange == "firstname":
			return validateName($input, 2, 16);
		case $fieldToChange == "surname":
			return validateName($input, 2, 20);
		case $fieldToChange == "number":
			return validatePhoneNumber($input);
		case $fieldToChange == "dob":
			return validateDate($input); // "email";
		default:
			return "";
	}
}

// if the input is contains only non-numbers and is the correct length then return an empty string, if the data is invalid return a help message
function validateName($field, $minlength, $maxlength) // master function +
{
	$errors = "";
	$errors = $errors . checkIsNonNumeric($field);
	$errors = $errors . validateStringLength($field, $minlength, $maxlength);
	return $errors;
}

// this function checks if an inputted email address is valid, and then returns an error message if it isn't
function validateEmail($field, $minLength, $maxLength)
{
	$errors = "";
	$errors = $errors . validateStringLength($field, $minLength, $maxLength);
	$errors = $errors . checkIsEmail($field);
	return $errors;
}

// if the data is valid return an empty string, if the data is invalid return a help message
function validateStringLength($field, $minlength, $maxlength) // edit function name
{
	// echo "String length: " . strlen($field);
	if (strlen($field) < $minlength) {
		// wasn't a valid length, return a help message:
		return "Input length: " . strlen($field) . ", minimum length: " . $minlength;
	} elseif (strlen($field) > $maxlength) {
		// wasn't a valid length, return a help message:
		return "Input length: " . strlen($field) . ", maximum length: " . $maxlength;
	} else {
		// data was valid, return an empty string:
		return "";
	}
}

// this function checks if an input is 0 chracters long and returns a message, if the input is larger than 0 characters
// send a different message
function checkIfLengthZero($field)
{
	if (strlen($field) == 0) {
		return true;
	} else {
		return false;
	}
}

// if the input contains the @ symbol then return an empty string, if the data is invalid return a help message
function checkIsEmail($field)
{
	if (strpos($field, '@') == false) {
		return "Email must contain an '@'";
	} else {
		return "";
	}
}

// if the input is contains only numbers then return an empty string, if the data is invalid return a help message
function checkIsNonNumeric($field)
{
	$charArray = str_split($field);
	$lengthOfCharArray = count($charArray);

	for ($i = 0; $i < $lengthOfCharArray; $i++) {
		if (is_numeric($charArray[$i]) == true) {
			return "Must not contain any numbers ";
		}
	}
	return "";
}

// if the input contains only numbers then return an empty string, if the data is invalid return a help message
function checkOnlyNumeric($field)
{
	if (is_numeric($field) == false) {
		return "Must not contain any characters ";
	} else {
		return "";
	}
}

// if the input is 11 digits long return an empty string, if the data is invalid return a help message
function validatePhoneNumber($field)
{

	// could add functionality that only allows specific number prefixs
	// e.g. 01, 07, 08, etc... but https://en.wikipedia.org/wiki/Telephone_numbers_in_the_United_Kingdom
	// A number can start with every combination of 0X. e.g. 01, 02, 03, 04 etc...
	if (is_numeric($field)) { // check if phone number is numerical
		if (strlen($field) == "11") { // check if phone number is correct length
			return "";
		} else {
			return "Phone number must be 11 digits long, yours was " . strlen($field) . " digits long";
		}
	} else {
		return "Phone number must not contain any characters";
	}
}

// if the input date is less than 13 years ago or more than 120, return an empty string, if the data is invalid return a help message
function validateDate($field)
{
	$todaysDate = date('Y-m-d');

	$inputYear = substr($field, 0, 4);
	$inputYear = (int)$inputYear;

	$todaysYear = substr($todaysDate, 0, 4);
	$todaysYear = (int)$todaysYear;

	if ($todaysYear - $inputYear > 120) {
		return "You must be less than 120 years old";
	} else if ($todaysYear - $inputYear < 13) {
		return "GDPR requires that data cannot be stored about people younger than 13";
	} else {
		return "";
	}
}

// if password length = 0, generate a random password,
// otherwise check if password is correct length
function validatePassword($field, $minLength, $maxLength)
{
	if (strlen($field) == 0) {
		return "Generate random password";
	} else {
		return validateStringLength($field, $minLength, $maxLength);
	}
}

// this function encrypts a user input
function encryptInput($input)
{
	return password_hash($input, PASSWORD_BCRYPT); // leave third parameter empty to generate random salt every time
}

// this function generates 32 random alphanumeric characters, converts them to ascii, combines the combination of characters, then returns the combination
function generateAlphanumericString()
{
	$charArray = createArrayOfUsableCharacters();
	$lengthOfCharArray = count($charArray) - 1;

	$tempPassword[] = "";

	for ($i = 0; $i <= 31; $i++) {
		$randNumber = rand(0, $lengthOfCharArray);
		$tempPassword[$i] = chr($charArray[$randNumber]);
	}

	$finalPassword = "";

	for ($i = 0; $i <= 31; $i++) {
		$finalPassword = $finalPassword . $tempPassword[$i];
	}

	return $finalPassword;
}

// this function creates an array of all alphanumeric characters
function createArrayOfUsableCharacters()
{
	$charArray[] = "";

	$j = 0;

	// get characters 0 to 9
	for ($i = 48; $i <= 57; $i++) {
		$charArray[$j] = $i;
		$j++;
	}

	// get capital letters
	for ($i = 65; $i <= 90; $i++) {
		$charArray[$j] = $i;
		$j++;
	}

	// get lower case letters
	for ($i = 97; $i <= 122; $i++) {
		$charArray[$j] = $i;
		$j++;
	}

	return $charArray;
}

// this function validates all user inputs, and adds each validation message to an array of errors
function createArrayOfAccountErrors($username, $email, $password, $firstname, $surname, $number, $DOB, $todaysDate, &$arrayOfErrors)
{
	$arrayOfErrors[0] = validateStringLength($username, 1, 20);
	$arrayOfErrors[1] = validateEmail($email, 3, 64);
	$arrayOfErrors[2] = validatePassword($password, 12, 32);
	$arrayOfErrors[3] = validateName($firstname, 2, 16); // see line below +
	$arrayOfErrors[4] = validateName($surname, 2, 20); // shortest last name I've ever seen was a girl called "Ng" +
	$arrayOfErrors[5] = validatePhoneNumber($number);
	$arrayOfErrors[6] = validateDate($DOB);
}

// checks to make sure input is a number, and then validates the size of the integer
function validateNumberOfQuestion($input, $minNo, $maxNo)
{
	$errors = checkOnlyNumeric($input);
	$errors = $errors . validateIntSize($input, $minNo, $maxNo);
	return $errors;
}

// checks integer is correct size
function validateIntSize($input, $minNo, $maxNo)
{
	if ($input < $minNo) {
		// wasn't a valid length, return a help message:
		return "Input length: " . $input . ", minimum length: " . $minNo;
	} elseif ($input > $maxNo) {
		// wasn't a valid length, return a help message:
		return "Input length: " . $input . ", maximum length: " . $maxNo;
	} else {
		// data was valid, return an empty string:
		return "";
	}
}

// gets the name of a superglobal from the current URL
function getSuperGlobalName($inputURL)
{
	$tempString = $inputURL;

	if (containsAmpersand($tempString)) {
		while (containsAmpersand($tempString)) {
			$tempString = removeAmpersand($tempString);
		}
		$tempString = substr($tempString, 0, strlen($tempString) - 5); // removes '=true' from end of string
		$tempString = substr($tempString, 6, strlen($tempString)); // removes 'change' from beginning of string
		return strtolower($tempString);
	} else {
		return "";
	}
}

// returns true if input contains the '&' symbol
function containsAmpersand($inputString)
{
	// convert input into array of chars
	$arrayOfChars = str_split($inputString);
	$inputLength = count($arrayOfChars);

	// for each char in array, check if it is the '&' symbol
	for ($i = 0; $i < $inputLength; $i++) {
		if ($arrayOfChars[$i] == '&') {
			return true;
		}
	}
	return false;
}

// removes ampersand from string
function removeAmpersand($inputString)
{
	$stringLength = strlen($inputString);
	$locationOfAmpersand = getAmpersandLocation($inputString);
	return substr($inputString, $locationOfAmpersand + 1, $stringLength); // trim variable
}

// returns the location in the input that the '&' symbol exists
function getAmpersandLocation($inputString)
{
	// convert input into array of chars
	$arrayOfChars = str_split($inputString);
	$inputLength = count($arrayOfChars);

	// for each char in array, check if it is the '&' symbol
	// if found, return the location of the symbol
	for ($i = 0; $i <= $inputLength; $i++) {
		if ($arrayOfChars[$i] == '&') {
			return $i;
		}
	}
	return 127;
}

// determines the field type bust upon the superglobal name
function determineFieldType($superGlobalName, &$minLength, &$maxLength)
{
	switch ($superGlobalName) {
		case $superGlobalName == "email":
			$minLength = 3;
			$maxLength = 64;
			return "email";
		case $superGlobalName == "password":
			$maxLength = 32;
			return "password";
		case $superGlobalName == "firstname":
			$minLength = 2;
			$maxLength = 16;
			return "text";
		case $superGlobalName == "surname":
			$minLength = 3;
			$maxLength = 24;
			return "text";
		case $superGlobalName == "number":
			$minLength = 11;
			$maxLength = 11;
			return "text";
		case $superGlobalName == "dob":
			return "date";
		default:
			return "";
	}
}

// calculates the earliest date that a user can be born
function calcEarliestDate($todaysDate)
{
	$minDate = substr($todaysDate, 0, 4);
	$minDate = (int)$minDate;
	$minYear = $minDate - 120;
	$minDate = substr($todaysDate, 4, strlen($todaysDate));
	$minDate = $minYear . $minDate;
	return $minDate;
}

// calculates the latest date that a user can be born
function calcLatestDate($todaysDate)
{
	$maxDate = substr($todaysDate, 0, 4);
	$maxDate = (int)$maxDate;
	$maxYear = $maxDate - 13;
	$maxDate = substr($todaysDate, 4, strlen($todaysDate));
	$maxDate = $maxYear . $maxDate;
	return $maxDate;
}

// initialises every element in an array with a null value
function initEmptyArray(&$array, $size)
{
	for ($i = 0; $i <= $size; $i++) {
		$array[$i] = "";
	}
}

// this function gets the username of the selected user from the session superglobal, gets all their information using an SQL query, displays it in a table
// then shows the options to either change the password or delete the account
function printUserData($connection, $origin, $username)
{
	echo "<br>";

	$query = "SELECT * FROM users WHERE username = '$username'";
	$result = mysqli_query($connection, $query);

	if ($result) {

		// displays the table headers for the users data
		echo "Account details:";
		echo "<table>";
		echo "<tr><th>Username</th><th>Email address</th><th>Password (hash)</th><th>First name</th><th>Surname</th><th>Phone Number</th><th>Date of Birth</th></tr>";

		// displays the user data
		while ($row = mysqli_fetch_assoc($result)) {
			echo "<tr><td>{$row['username']}</td><td>{$row['email']}</td><td>{$row['password']}</td><td>{$row['firstname']}</td><td>{$row['surname']}</td><td>{$row['number']}</td><td>{$row['dob']}</td></tr>";
		}
		echo "</table>";
	} else {
		echo mysqli_error($connection) . "<br>";
	}
}

// displays the account detail edit options:
function printOptionsToEdit($origin, $username)
{
	$URL = $origin . "?username=" . $username;

	echo "<br>";
	echo "<a href =$URL&changeEmail=true>Change email</a>";
	echo " ";
	echo "<a href =$URL&changePassword=true>Change password</a>";
	echo " ";
	echo "<a href =$URL&changeFirstname=true>Change firstname</a>";
	echo " ";
	echo "<a href =$URL&changeSurname=true>Change surname</a>";
	echo " ";
	echo "<a href =$URL&changeNumber=true>Change number</a>";
	echo " ";
	echo "<a href =$URL&changedob=true>Change date of birth</a>";
	echo " ";
	echo "<a href =$URL&deleteAccount=true>Delete user account</a>";
}

// this function gets the select user's username from the session superglobal, asks the admin to fill in a new password for the user
// then updates the user's password via an SQL query
function changeUserDetails($connection, $fieldToChange, $fieldType, $minLength, $maxLength)
{
	// if the user has inputted a new input then
	if (isset($_POST['newInput'])) {

		$currentUsername = $_SESSION['username'];

		echo "Change user details:";
		echo "<br>";

		// validate input:
		$newInput = sanitise($_POST['newInput'], $connection);
		$input_val = validateInput($newInput, $fieldToChange);

		// validate password
		if ($input_val == "Generate random password") {
			$newInput = generateAlphanumericString();
			$input_val = validateInput($newInput, $fieldToChange);
		}

		// if there are no errors then encrypt the new password
		if ($input_val == "") {
			if ($fieldType == "password") {
				$newInput = encryptInput($newInput);
				echo "<br>";
				echo "Insert a new password if your browser hasn't automatically saved your password";
			}
			// update database with new details
			$query = "UPDATE users SET $fieldToChange='$newInput' WHERE username = '$currentUsername'";
			$result = mysqli_query($connection, $query);

			echo "<br>";
			if ($result) {
				echo ucfirst($fieldToChange) . " changed";
			} else {
				echo mysqli_error($connection) . "<br>";
			}
		} else {
			echo "<br>";
			echo "Updating field failed: " . $input_val;
		}
	} else {
		showUserDataFieldForm($fieldToChange, $fieldType);
	}
}

// displays the input form for user to update their account details
function showUserDataFieldForm($fieldToChange, $fieldType)
{
	$currentURL = $_SERVER['REQUEST_URI'];
	$fieldToDisplay = ucfirst($fieldToChange);

	echo "<form action='$currentURL' method='post'>";
	echo "<br>Please fill in the following fields:<br>";

	if ($fieldToDisplay == "Dob") {

		$todaysDate = date('Y-m-d'); // get current date: +

		$minDate = calcEarliestDate($todaysDate);
		$maxDate = calcLatestDate($todaysDate);

		echo "$fieldToDisplay: <input type='$fieldType' min=$minDate max=$maxDate name='newInput'>";
	} else {
		echo "$fieldToDisplay: <input type='$fieldType' name='newInput'>";
	}

	echo <<<_END
        <br><br>
        <input type="submit" value="Submit">
        </form>
_END;
}

// this function gets the username of the selected user from the session superglobal, then deletes the account via an SQL query
function deleteAccount($connection, $username)
{
	echo "<br>";

	if ($username == "admin") {
		// display message that the admin account cannot be deleted:
		echo "The admin account cannot be deleted";
	} else {
		// display confirmation prompt that the account should be deleted:
		echo "<br>Are you sure you want to delete the account " . $username . "?<br>";
		echo "<a href ={$_SERVER['REQUEST_URI']}&confirmDeletion=true>Yes</a>";
		echo " ";
		echo "<a href =admin.php?username=$username>Cancel</a><br>";

		// if confirmation is given delete the account from the database:
		if (isset($_GET["confirmDeletion"])) {
			$query = "DELETE FROM users WHERE username = '$username'";
			$result = mysqli_query($connection, $query);

			echo "<br>";
			if ($result) {
				// show success message:
				echo "Account deleted";
			} else {
				echo mysqli_error($connection) . "<br>";
			}
		}
	}
}

// required to enact the account detail edit
function enactEdit($connection)
{
	if (isset($_GET['deleteAccount'])) {
		deleteAccount($connection, $_GET['username']);
	} else {

		$superGlobalName = getSuperGlobalName($_SERVER['REQUEST_URI']);

		$minLength = null;
		$maxLength = null;
		$fieldType = determineFieldType($superGlobalName, $minLength, $maxLength);

		echo "<br>";

		if ($superGlobalName !== "") {
			changeUserDetails($connection, $superGlobalName, $fieldType, $minLength, $maxLength);
		}
	}
}

// displays the account creation form:
function displayCreateAccountForm($username, $email, $password, $firstname, $surname, $number, $dob, $todaysDate, $arrayOfAccountErrors)
{
	$minDate = calcEarliestDate($todaysDate);
	$maxDate = calcLatestDate($todaysDate);

	$currentURL = $_SERVER['REQUEST_URI'];

	// form to create account:
	echo <<<_END
        <form action="$currentURL" method="post">
          Please fill in the following fields:<br>
          Username: <input type="text" name="username" minlength="3" maxlength="20" value="$username" required> $arrayOfAccountErrors[0]
          <br>
          Email: <input type="email" name="email" minlength="3" maxlength="64" value="$email" required> $arrayOfAccountErrors[1]
          <br>
          Password: <input type="password" name="password" maxlength="32" value="$password"> Leave blank for an auto-generated password $arrayOfAccountErrors[2]
          <br>
          First name: <input type="text" name="firstname" minlength="2" maxlength="16" value="$firstname" required> $arrayOfAccountErrors[3]
          <br>
          Surname: <input type="text" name="surname" minlength="2" maxlength="24" value="$surname" required> $arrayOfAccountErrors[4]
          <br>
          Phone number: <input type="text" name="number" min=length"11" maxlength="11" value="$number" required> $arrayOfAccountErrors[5]
          <br>
          Date of birth: <input type="date" name="dob" min="$minDate" max="$maxDate" value="$dob" required> $arrayOfAccountErrors[6]
          <br>
          <input type="submit" value="Submit">
        </form>
_END;
}

// inserts new account into database:
function createAccount($connection, $username, $email, $password, $firstname, $surname, $number, $dob, $todaysDate, $arrayOfAccountCreationErrors)
{

	$randomPasswordGenerated = false;
	$plaintextPassword = "";

	// if password length = 0, generate a random password
	if (checkIfLengthZero($password)) {
		$randomPasswordGenerated = true;
		$password = generateAlphanumericString();
		$plaintextPassword = $password;
	}

	// creates an array of account errors
	createArrayOfAccountErrors($username, $email, $password, $firstname, $surname, $number, $dob, $todaysDate, $arrayOfAccountCreationErrors);

	// concatenate all the validation results together ($errors will only be empty if ALL the data is valid):
	$errors = implode('', $arrayOfAccountCreationErrors);

	// check that all the validation tests passed before inserting information into the database:
	if ($errors == "") {

		$password = encryptInput($password);

		// try to insert the new details:
		$query = "INSERT INTO users (username, firstname, surname, password, email, number, dob) VALUES ('$username','$firstname','$surname','$password','$email','$number', '$dob')";
		$result = mysqli_query($connection, $query);

		// if no data returned, we set result to true(success)/false(failure):
		if ($result) {
			// show a successful signup message:
			echo "Account creation was successful<br><br>";
			echo "Your password is: " . $plaintextPassword . "<br><br>";


			echo "<a href = sign_in.php>Click here to sign in</a><br>";
		} else {
			displayCreateAccountForm($username, $email, $password, $firstname, $surname, $number, $dob, $todaysDate, $arrayOfAccountCreationErrors);
			echo mysqli_error($connection) . "<br>";
		}
	} else {
		// validation failed, show the form again with guidance:
		displayCreateAccountForm($username, $email, $password, $firstname, $surname, $number, $dob, $todaysDate, $arrayOfAccountCreationErrors);
		// show an unsuccessful signin message:

		echo mysqli_error($connection) . "<br>";

		echo "Account creation failed, please check the errors shown above and try again<br>";
	}
	// we're finished with the database, close the connection:
	mysqli_close($connection);
}

// sanitise user inputs when they are creating their account:
function sanitiseUserData($connection, &$username, &$email, &$password, &$firstname, &$surname, &$number, &$dob)
{
	$username = sanitise($_POST['username'], $connection);
	$email = sanitise($_POST['email'], $connection);
	$password = sanitise($_POST['password'], $connection);
	$firstname = sanitise($_POST['firstname'], $connection);
	$surname = sanitise($_POST['surname'], $connection);
	$number = sanitise($_POST['number'], $connection);
	$dob = sanitise($_POST['dob'], $connection);
}

// displays the create question prompt
function displayCreateQuestionPrompt($surveyID, $numQuestionsInserted, $numQuestions)
{
	if ($numQuestionsInserted < $numQuestions) {
		$nextQuestionURL = "create_question.php?surveyID=$surveyID&numQuestionsInserted=$numQuestionsInserted";
		echo "<a href= $nextQuestionURL> Click here to create new question </a><br>";
	} else {
		echo "<br>Survey completed!<br>";
		echo "<a href = surveys_manage.php> Click here to return to 'My Surveys' </a><br>";
	}
}

// drops a table from a database:
function dropTable($connection, $tableName)
{
	$sql = "DROP TABLE IF EXISTS $tableName";
	if (!mysqli_query($connection, $sql)) {
		echo "Error checking for user table: " . mysqli_error($connection);
	}
}

// prints list of user surveys
function printSurveys($connection, $result, $userIsAdmin)
{
	// if user is admin print all surveys from database:
	echo "<table>";
	if ($userIsAdmin) {
		echo "<tr><th>Survey ID</th><th>Username</th><th>Title</th><th>Topic</th><th>Survey link</th><th>View results</th><th>Delete survey</th></tr>";
		while ($row = mysqli_fetch_assoc($result)) {
			echo "<tr><td>{$row['surveyID']}</td><td>{$row['username']}</td><td>{$row['title']}</td><td>{$row['topic']}</td><td><a href = answer_survey.php?surveyID={$row['surveyID']}&questionsAnswered=0>Survey link</a></td><td><a href = view_survey_results.php?surveyID={$row['surveyID']}>View Results</a><td><a href = ?deleteSurvey=true&surveyID={$row['surveyID']}> Delete</a></td></tr>";
		}
	} else {
		// if user is not the admin, only display the user's surveys:
		echo "<tr><th>Survey ID</th><th>Title</th><th>Topic</th><th>Survey link</th><th>View results</th><th>Delete survey</th></tr>";
		while ($row = mysqli_fetch_assoc($result)) {
			echo "<tr><td>{$row['surveyID']}</td><td>{$row['title']}</td><td>{$row['topic']}</td><td><a href = answer_survey.php?surveyID={$row['surveyID']}&questionsAnswered=0> Survey link</a></td><td><a href = view_survey_results.php?surveyID={$row['surveyID']}>View Results</a></td><td><a href = ?deleteSurvey=true&surveyID={$row['surveyID']}> Delete</a></td></tr>";
		}
	}
	echo "</table>";
}

// deletes user responses from database
function deleteUserResponse($connection, $surveyID)
{
	if(isset($_GET['username'])) {
		$username = $_GET['username'];
	} else {
		$username = $_SESSION['username'];
	}

	$query = "DELETE r.* FROM responses r INNER JOIN questions q USING (questionID) WHERE q.surveyID = '$surveyID' AND r.username = '$username'";
	$result = mysqli_query($connection, $query);

	// display success message if there are no errors:
	if ($result) {
		echo "<br>Successfully deleted response<br>";
	} else {
		echo mysqli_error($connection);
	}
}

// prints variable value, used for debugging
function echoVariable($variableToEcho)
{
	echo "<br>Variable value: " . $variableToEcho . "<br>";
}


