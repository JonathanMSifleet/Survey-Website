<?php
// Things to notice:
// This is an empty page where you can provide a simple overview and description of your site
// Consider it the 'welcome' page for your survey web site

// execute the header script:
require_once "header.php";

echo "You may wish to include a short description of your survey site and how to use the main features it has here.<br>";

error_reporting(0);
$connection = mysqli_connect($dbhost, $dbuser, $dbpass, $dbname);
error_reporting(1);

if (! $connection) {
    echo "<br>";
    echo "You must first create the database before the site can be used";
    echo "<br>";
    echo "<a href = create_database.php> Initialise database </a>";
}

// finish of the HTML for this page:
require_once "footer.php";

?>