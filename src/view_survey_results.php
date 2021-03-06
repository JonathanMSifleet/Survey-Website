<?php
require_once "header.php";

if (!isset($_SESSION['loggedInSkeleton'])) {
	// user isn't logged in, display a message saying they must be:
	echo "You must be logged in to view this page.<br>";
} // the user must be signed-in, show them suitable page content
else {

	$connection = mysqli_connect($dbhost, $dbuser, $dbpass, $dbname);

	// if the connection fails, we need to know, so allow this exit:
	if (!$connection) {
		die("Connection failed: " . mysqli_connect_error());
	}

	$surveyID = $_GET['surveyID'];

	$query = "SELECT username FROM surveys WHERE surveyID ='$surveyID'";
	$result = mysqli_query($connection, $query);

	// if the user is not the survey creator or the user is not admin,
	// do not show the survey results:
	if ($result) {
		$row = mysqli_fetch_row($result);

		if ($row[0] == $_SESSION['username'] || $_SESSION['username'] == "admin") {
			$arrayOfQuestionNames = array();
			$arrayOfQuestionIDs = array();
			getSurveyQuestions($connection, $surveyID, $arrayOfQuestionNames, $arrayOfQuestionIDs);

			echo "<h3>" . getSurveyName($connection, $surveyID) . "</h3>";

			// gets array of survey respondents
			$arrayOfRespondents = array();
			getSurveyRespondents($connection, $surveyID, $arrayOfRespondents);

			if (count($arrayOfRespondents) != 0) {

				$numResponses = count($arrayOfRespondents);
				$tableName = "response_CSV_" . $surveyID;
				$_SESSION['tableName'] = $tableName;
				$_SESSION['questionNames'] = $arrayOfQuestionNames;

				// if the survey has respondents, get the survey results:
				getResultsTable($connection, $surveyID, $arrayOfQuestionNames, $arrayOfQuestionIDs, $arrayOfRespondents, $tableName, $numResponses);

				echo "<br>What would you like to do?<br>";

				echo "<ul>";
				echo "<li><a href = view_survey_results.php?surveyID=$surveyID&viewGraphs=true>View graphs</a></li>";
				echo "<li><a href = exportResultsToCSV.php?surveyID=$surveyID>Export results to CSV</a></li>";
				echo "<li><a href = view_survey_results.php?surveyID=$surveyID&showListOfQuestionsToDelete=true>Delete a question</a></li>";
				echo "</ul>";

				if (isset($_GET['viewGraphs'])) {

					// display list of questions:
					echo "<br>Please select a question to view its graph:";
					echo "<ul>";
					for ($i = 0; $i < count($arrayOfQuestionNames); $i++) {
						echo "<li><a href= view_survey_results.php?surveyID=$surveyID&viewGraphs=true&graphToView=$arrayOfQuestionIDs[$i]>$arrayOfQuestionNames[$i]</a></li>";
					}
					echo "</ul>";

					// gets question name from database then draws graph:
					if (isset($_GET['graphToView'])) {

						$questionID = $_GET['graphToView'];

						$query = "SELECT questionName, type FROM questions WHERE questionID = '$questionID'";
						$result = mysqli_query($connection, $query);

						if ($result) {
							$row = mysqli_fetch_row($result);
							$questionName = $row[0];
							$type = $row[1];

							switch ($type) {
								case "checkboxes":
								case "dropdown":
								case "multOption":
									// displays graphs:
									drawGraph($connection, $questionID, $questionName);
									break;
								default:

									echo "The question must have pre-defined options, otherwise a graph cannot be drawn<br>";
									echo "Please refer to the table below:<br>";
							}
						} else {
							echo mysqli_error($connection);
						}
					}
				}

				// if the user has instead decided to delete a survey's question,
				// then display the list of questions to delete:
				if (isset($_GET['showListOfQuestionsToDelete'])) {
					displayQuestionsToDelete($connection, $surveyID, $arrayOfQuestionNames, $arrayOfQuestionIDs);
				}

				// if admin decides to delete a users responses from a survey, delete their responses from the database:
				if (isset($_GET['username'])) {
					deleteUserResponse($connection, $surveyID);
				}

				// displays table of results:
				displayTableOfResults($connection, $tableName, $arrayOfQuestionNames, $surveyID);
			} else {
				// otherwise show message that survey has no respondents:
				echo "No survey responses<br>";
			}
		} else {
			echo "You must be the survey's creator to view the results!";
			echo "<br><br><a href = about.php>Click here to return to the main page</a>";
		}
	} else {
		echo mysqli_error($connection);
	}
}

// finish off the HTML for this page:
require_once "footer.php";

// draws chart based upon question results:
function drawGraph($connection, $questionID, $questionName) {
	$query = "SELECT response, COUNT(response) AS countResponse, type FROM responses INNER JOIN questions USING (questionID) WHERE questionID = '$questionID' GROUP BY response ORDER BY response ASC";
	$result = mysqli_query($connection, $query);

	if ($result) {

		$graphResults = "";

		while ($row = mysqli_fetch_assoc($result)) {

			if (!$row['response'] == "") {
				if ($row['type'] == "checkboxes") {

					$arrayOfCheckboxes = explode(",", $row['response']);

					for ($i = 0; $i < count($arrayOfCheckboxes); $i++) {
						$graphResults = $graphResults . "['" . $arrayOfCheckboxes[$i] . "'," . $row['countResponse'] . "],";
					}
				} else {
					$graphResults = $graphResults . "['" . $row['response'] . "'," . $row['countResponse'] . "],";
				}
			}
		}

		echo <<<_END

    <script type="text/javascript">

      // Load the Visualization API and the corechart package.
      google.charts.load('current', {'packages':['corechart']});

      // Set a callback to run when the Google Visualization API is loaded.
      google.charts.setOnLoadCallback(drawChart);

      // Callback that creates and populates a data table,
      // instantiates the pie chart, passes in the data and
      // draws it.
      function drawChart() {
		
        // Create the data table.
        let data = new google.visualization.DataTable();
		data.addColumn('string', '[key]'); // x axis
		data.addColumn('number', '[key]'); // x axis
        data.addRows([
          $graphResults
        ]);
		
        // Set chart options
        let options = {'title':'$questionName',
                       'width':500,
                       'height':400};
        // Instantiate and draw our chart, passing in some options.
        let chart = new google.visualization.PieChart(document.getElementById('chart_div'));
        chart.draw(data, options);
      }
	</script>
	
	<!--Div that will hold the pie chart-->
    <div id="chart_div"></div>

_END;
	} else {
		echo mysqli_error($connection);
	}
}

// displays the survey results in a table:
function getResultsTable($connection, $surveyID, $arrayOfQuestionNames, $arrayOfQuestionIDs, $arrayOfRespondents, $tableName, $numResponses) {
	echo "<h3>Results:</h3>";
	echo "Number of results: " . $numResponses . "<br>";

	if (!empty($arrayOfQuestionNames)) {
		// gets results:
		createResultsTable($connection, $surveyID, $tableName, $arrayOfQuestionNames, $arrayOfQuestionIDs, $arrayOfRespondents, $numResponses);
	}
}

// displays list of questions to delete:
function displayQuestionsToDelete($connection, $surveyID, &$arrayOfQuestionNames, &$arrayOfQuestionIDs) {
	$numQuestions = count($arrayOfQuestionNames);

	// if survey has no questions, show message
	// saying so:
	if ($numQuestions == 0) {
		echo "</ul>";
		echo "There are no questions to delete<br>";
	} else {
		// otherwise, display list of questions to delete:
		echo "<br>Pick a question to delete:<br>";

		echo "<ul>";

		for ($i = 0; $i < $numQuestions; $i++) {
			echo "<li><a href = view_survey_results.php?surveyID=$surveyID&showListOfQuestionsToDelete=true&deleteQuestion=$arrayOfQuestionIDs[$i]>$arrayOfQuestionNames[$i]</a></li>";
		}
		echo "</ul>";

		// shows confirmation of deletion prompt:
		if (isset($_GET['deleteQuestion'])) {

			echo "<br> Are you sure?<br><br>";
			echo "<a href = {$_SERVER['REQUEST_URI']}&confirmDeletion=true> Yes</a>";
			echo " ";
			echo "<a href = view_survey_results.php?surveyID=$surveyID&showListOfQuestionsToDelete=true>Cancel</a><br>";

			// if confirmation is given, delete the question from the database:
			if (isset($_GET['confirmDeletion'])) {
				initDeleteQuestion($connection, $surveyID, $numQuestions, $arrayOfQuestionNames, $arrayOfQuestionIDs);
			}
		}
	}
}

// required to delete a question from a database:
function initDeleteQuestion($connection, $surveyID, $numQuestions, &$arrayOfQuestionNames, &$arrayOfQuestionIDs) {
	// deletes the question from the database:
	deleteQuestion($connection);
	// updates the number of questions the survey has in the database:
	updateTableNumQuestions($connection, $surveyID, $numQuestions);

	$arrayOfQuestionIDs = array();
	getSurveyQuestions($connection, $surveyID, $arrayOfQuestionNames, $arrayOfQuestionIDs);

	// updates each questions question number in question table:
	updateAllQuestionNums($connection, $surveyID, $arrayOfQuestionIDs); // finish this function
}

// updates each questions question number in question table:
function updateAllQuestionNums($connection, $surveyID, &$arrayOfQuestionIDs) {
	$j = 0;

	for ($i = 0; $i < count($arrayOfQuestionIDs); $i++) {
		$query = "UPDATE questions SET questionNo = '$j' WHERE surveyID = '$surveyID' AND questionID = '{$arrayOfQuestionIDs[$i]}'";
		$result = mysqli_query($connection, $query);

		if (!$result) {
			echo mysqli_error($connection);
		} else {
			$j++;
		}
	}
}

// updates the number of questions the survey has in the database:
function updateTableNumQuestions($connection, $surveyID, $numQuestions) {
	$numQuestions--;

	$query = "UPDATE surveys SET numQuestions = '$numQuestions' WHERE surveyID='$surveyID'";
	$result = mysqli_query($connection, $query);

	if ($result) {
		echo "Question deleted successfully<br>";
	} else {
		echo mysqli_error($connection);
	}
}

// delete the question from the table:
function deleteQuestion($connection) {
	$questionID = $_GET['deleteQuestion'];

	$query = "DELETE FROM questions WHERE questionID = '$questionID'";
	$result = mysqli_query($connection, $query);

	if (!$result) {
		echo mysqli_error($connection);
	}
}

// handles the required operation for creating a user-friendly
// results table:
function createResultsTable($connection, $surveyID, $tableName, $arrayOfQuestionNames, $arrayOfQuestionIDs, $arrayOfRespondents, $numResponses) {
	// if the user-friendly table for this survey exists, drop it:
	dropTable($connection, $tableName);
	// create the user-friendly table:
	createTable($connection, $arrayOfQuestionNames, $tableName);
	// insert responses into the user-friendly table:
	populateTable($connection, $tableName, $arrayOfQuestionIDs, $arrayOfRespondents, $numResponses);
}

// drops a table from a database:
function dropTable($connection, $tableName) {
	$sql = "DROP TABLE IF EXISTS $tableName";
	if (!mysqli_query($connection, $sql)) {
		echo "Error checking for user table: " . mysqli_error($connection);
	}
}

// gets array of survey respondents from the database:
function getSurveyRespondents($connection, $surveyID, &$arrayOfRespondents) {
	$query = "SELECT DISTINCT username FROM responses INNER JOIN questions USING(questionID) WHERE surveyID= '$surveyID'";
	$result = mysqli_query($connection, $query);

	if ($result) {
		while ($row = mysqli_fetch_row($result)) {
			$arrayOfRespondents[] = $row[0];
		}
	} else {
		echo mysqli_error($connection) . "<br>";
	}
}

// gets the survey's name based off the surveyID from the database:
function getSurveyName($connection, $surveyID) {
	$query = "SELECT title FROM surveys WHERE surveyID = '$surveyID'";
	$result = mysqli_query($connection, $query);

	if ($result) {
		$row = mysqli_fetch_row($result);
		return $row[0];
	} else {
		echo mysqli_error($connection) . "<br>";
	}
}

// fetches an array of survey questions from database:
function getSurveyQuestions($connection, $surveyID, &$arrayOfQuestions, &$arrayOfQuestionIDs) {
	$query = "SELECT questionName, questionID FROM questions WHERE surveyID = '$surveyID' ORDER BY questionNo ASC";
	$result = mysqli_query($connection, $query);

	if ($result) {
		while ($row = mysqli_fetch_row($result)) {
			$arrayOfQuestions[] = $row[0];
			$arrayOfQuestionIDs[] = $row[1];
		}
	} else {
		echo mysqli_error($connection) . "<br>";
	}
}

// inserts the user-friendly table into the database:
function createTable($connection, $arrayOfQuestionNames, $tableName) {
	$query = "CREATE TEMPORARY TABLE $tableName (Username VARCHAR(20), PRIMARY KEY(username))";
	$result = mysqli_query($connection, $query);

	if ($result) {
		for ($i = 0; $i < count($arrayOfQuestionNames); $i++) {

			$questionName = $arrayOfQuestionNames[$i];

			$query = "ALTER IGNORE TABLE $tableName ADD `$questionName` VARCHAR(128)";
			$result2 = mysqli_query($connection, $query);

			if (!$result2) {
				echo("Error: " . mysqli_error($connection));
			}
		}
	} else {
		echo("Error: " . mysqli_error($connection));
	}
}

// inserts the responses to the survey into the user-friendly table:
function populateTable($connection, $tableName, $arrayOfQuestionIDs, $arrayOfRespondents, $numResponses) {
	$dataToInsert = array();

	for ($i = 0; $i < $numResponses; $i++) {
		$username = $arrayOfRespondents[$i];
		$dataToInsert[] = $username;

		for ($j = 0; $j < count($arrayOfQuestionIDs); $j++) {

			// concatenates responses
			$query = "SELECT r.response, type, GROUP_CONCAT(r.response ORDER BY r.response ASC SEPARATOR ', ') AS 'concat_response' FROM responses r INNER JOIN questions q USING(questionID) WHERE q.questionID = '{$arrayOfQuestionIDs[$j]}' AND r.username = '$username'";
			$result = mysqli_query($connection, $query);

			if ($result) {
				$row = mysqli_fetch_assoc($result);
				// if question is a checkbox insert concatenated response into table:
				if ($row['type'] == 'checkboxes') {
					$dataToInsert[] = $row['concat_response'];
				} else {
					$dataToInsert[] = $row['response'];
				}
			} else {
				echo mysqli_error($connection) . "<br>";
			}
		}
		insertResponseIntoTable($connection, $tableName, $dataToInsert);
		$dataToInsert = array();
	}
}

// actually inserts the responses:
function insertResponseIntoTable($connection, $tableName, $dataToInsert) {
	$values = implode("','", $dataToInsert);
	$values = "'" . $values . "'";

	$query = "INSERT INTO $tableName VALUES ($values)";
	$result = mysqli_query($connection, $query);

	if (!$result) {
		echo mysqli_error($connection);
	}
}

// displays the user-friendly table of results:
function displayTableOfResults($connection, $tableName, $arrayOfQuestionNames, $surveyID) {
	$query = "SELECT * FROM  $tableName ORDER BY username ASC";
	$result = mysqli_query($connection, $query);

	echo "<br><table>";

	displayHeaders($arrayOfQuestionNames);
	displayRows($result, $surveyID);

	echo "</table>";
}

// displays the table headers:
function displayHeaders($arrayOfQuestionNames) {
	echo "<tr>";
	echo "<th>Username</th>";
	for ($i = 0; $i < count($arrayOfQuestionNames); $i++) {
		echo "<th>{$arrayOfQuestionNames[$i]}</th>";
	}

	if ($_SESSION['username'] == "admin") {
		echo "<th>Delete response</th>";
	}

	echo "</tr>";
}

// display the table rows:
function displayRows($result, $surveyID) {
	while ($row = mysqli_fetch_assoc($result)) {
		echo "<tr>";

		// iterate through associative array:
		foreach ($row as $i => $value) {
			echo "<td>$value</td>";
		}

		if ($_SESSION['username'] == "admin") {
			echo "<td><a href = view_survey_results.php?surveyID=$surveyID&viewResultsInTable=true&username={$row['Username']}>Delete</a></td>";
		}

		echo "</tr>";
	}
}

?>
