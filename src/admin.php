<?php
// Things to notice:
// You need to add code to this script to implement the admin functions and features
// Notice that the code not only checks whether the user is logged in, but also whether they are the admin, before it displays the page content
// When an admin user is verified, you can implement all the admin tools functionality from this script, or distribute them over multiple pages - your choice
// execute the header script:
require_once "header.php";
// checks the session variable named 'loggedInSkeleton'
// take note that of the '!' (NOT operator) that precedes the 'isset' function
if (! isset($_SESSION['loggedInSkeleton'])) {
    // user isn't logged in, display a message saying they must be:
    echo "You must be logged in to view this page.<br>";
} // the user must be signed-in, show them suitable page content
else {
    // only display the page content if this is the admin account (all other users get a "you don't have permission..." message):
    $connection = mysqli_connect($dbhost, $dbuser, $dbpass, $dbname);
    if ($_SESSION['username'] == "admin") {
        
        // queries mysql table, outputs results to table
        // this is written by me:
        $query = "SELECT username FROM users"; // +
        $result = mysqli_query($connection, $query); // +
        
        echo "Click a name from the table to view user's data:";
        echo"<br>";
        
        echo"<table border ='1'>";
        echo"<tr><td>username</td></tr>";
        
        while($row = mysqli_fetch_assoc($result)) {
            // if row hyperlink is clicked, set superglobal with user's name
            echo"<tr><td><a href =?username={$row['username']}>{$row['username']}</a></td></tr>"; // turns row result into hyperlink
        }
        echo"</table>";
        
        // print user's data
        if(isset($_GET['username'])) {
            printUserData($dbhost, $dbuser, $dbpass, $dbname);
        }
        ////////////
        
        if(isset($_GET['deleteAccount'])) {
            deleteAccount($dbhost, $dbuser, $dbpass, $dbname);
        }
        ////////////
        
        mysqli_close($connection);
    } else {
        echo "You don't have permission to view this page...<br>";
    }
}
// finish off the HTML for this page:
require_once "footer.php";

// this function gets the username of the selected user from the session superglobal, gets all their information using an SQL query, displays it in a table
// then shows the options to either change the password or delete the account
// this function is written by me:
function printUserData($dbhost, $dbuser, $dbpass, $dbname) {
      
        $username = $_GET["username"];
        
        echo "User selected: " .$username;
        echo "<br>";
        
        $connection = mysqli_connect($dbhost, $dbuser, $dbpass, $dbname);
        $query = "SELECT * FROM users WHERE username = '$username'"; // +
        $result = mysqli_query($connection, $query); // +
                
        echo "User's details:";
        echo"<table border ='1'>";
        echo"<tr><td>username</td><td>firstname</td><td>surname</td><td>password</td><td>email</td><td>number</td><td>DOB</td></tr>";
    
        while($row = mysqli_fetch_assoc($result)) {
            echo"<tr><td>{$row['username']}</td><td>{$row['firstname']}</td><td>{$row['surname']}</td><td>{$row['password']}</td><td>{$row['email']}</td><td>{$row['number']}</td><td>{$row['DOB']}</td></tr>";
        }
        echo "</table>";
        
        
        echo "<a href ={$_SERVER['REQUEST_URI']}&changePassword=true>Change password</a>";
        echo " ";
        //echo "<a href ={$_SERVER['REQUEST_URI']}&editaccount=true>Create user account</a>";
        echo "<a href ={$_SERVER['REQUEST_URI']}&deleteAccount=true>Delete user account</a>";

}

// this function gets the username of the selected user from the session superglobal, then deletes the account via an SQL query
// this function is written by me:
function deleteAccount($dbhost, $dbuser, $dbpass, $dbname) {
    $username = $_GET["username"];
    
    if($username == "admin") {
        echo "The admin account cannot be deleted";
    } else {
        echo "<br>";
        echo "are you sure you want to delete " . $username . "? ";
        echo "<a href ={$_SERVER['REQUEST_URI']}&confirmDeletion=true>Yes</a>";
        echo " ";
        echo "<a href ={$_SERVER['REQUEST_URI']}&confirmDeletion=false>Cancel</a>";
        
        $shouldDeleteAccount = "";
        
        $shouldDeleteAccount = $_GET["confirmDeletion"];
        
        if ($shouldDeleteAccount=="true" ) {
            $connection = mysqli_connect($dbhost, $dbuser, $dbpass, $dbname);
            $query = "DELETE FROM users WHERE username = '$username'";
            $result = mysqli_query($connection, $query); // +
         
            echo "Account deleted";
        }
    }
    
}

?>