/* exportTableToCSV($connection, $tableName, $arrayOfQuestionNames);

function exportTableToCSV($connection, $tableName, $arrayOfQuestionNames)
{
    // output headers so that the file is downloaded rather than displayed
    header('Content-type: text/csv');
    header('Content-Disposition: attachment; filename="demo.csv"');

    // do not cache the file
    header('Pragma: no-cache');
    header('Expires: 0');

    // create a file pointer connected to the output stream
    $file = fopen('php://output', 'w');

    // column headers:

    $arrayOfColumnNames = $arrayOfQuestionNames;
    array_unshift($arrayOfColumnNames, "Username");

    fputcsv($file, $arrayOfColumnNames);

    //query the database
    $query = "SELECT * FROM $tableName ORDER BY username ASC";

    if ($rows = mysqli_query($connection, $query)) {
        // loop over the rows, outputting them
        while ($row = mysqli_fetch_assoc($rows)) {
            fputcsv($file, $row);
        }
    }
} */