<?php
// Things to notice:
// This is an empty page where you can provide a simple overview and description of your site
// Consider it the 'welcome' page for your survey web site

// execute the header script:
require_once "header.php";

echo <<<_END

<h3>Features:</h3>
<ul>
<li>Creation of surveys</li>
<li>Answering of surveys</li>
<li>Sharing of surveys</li>
<li>View survey results
<ul>
<li>Export results to csv</li>
</ul>
</li>
</li>
<li>Account creation</li>
<li>User can edit account details</li>
<li>Admin can delete questions, surveys, responses and user accounts</li>
</ul>
_END;

error_reporting(0);
$connection = mysqli_connect($dbhost, $dbuser, $dbpass, $dbname);
error_reporting(1);

if (!$connection) {
    echo "<br>You must first create the database before the site can be used<br>";
    echo "<a href = init_database.php> Initialise database </a><br>";
} elseif (isset($_SESSION['loggedInSkeleton'])) {
    echo "<a href=  answer_survey.php?surveyID=af57a209f9e756664ef282d11a385c70&questionsAnswered=0> Click here to answer the default survey</a><br>";
} else {
    echo "Please log in or sign-up to access site functionality<br>";
}
// finish of the HTML for this page:
require_once "footer.php";

?>