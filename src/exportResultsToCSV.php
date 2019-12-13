<?php
session_start();

require_once "credentials.php";
require_once "helper.php";
$connection = mysqli_connect($dbhost, $dbuser, $dbpass, $dbname);

$tableName = $_SESSION['tableName'];
$arrayOfQuestionNames = $_SESSION['questionNames'];
$fileName = substr($tableName, 14, strlen($tableName));
$fileName = "results_" . $fileName . ".csv";

// output headers so that the file is downloaded rather than displayed
header('Content-type: text/csv');
header('Content-Disposition: attachment; filename=' . $fileName);

// do not cache the file
header('Pragma: no-cache');
header('Expires: 0');

// create a file pointer connected to the output stream
$file = fopen('php://output', 'w');

// column headers:
$arrayOfColumnNames = $arrayOfQuestionNames;
array_unshift($arrayOfColumnNames, "Username");

fputcsv($file, $arrayOfColumnNames);

// query the database
$query = "SELECT * FROM $tableName ORDER BY username ASC";
$result = mysqli_query($connection, $query);

if ($result) {
	// loop over the rows, outputting them
	while ($row = mysqli_fetch_assoc($result)) {
		fputcsv($file, $row);
	}
	// free result set
	mysqli_free_result($result);
}
// closes csv:
fclose($file);

?>