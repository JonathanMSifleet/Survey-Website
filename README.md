University coursework. Grade: 83%

6G5Z2107 - 2CWK50 - 2019/20
Jonathan Sifleet
18014017

MUST BE USING WINDOWS XAMPP OR YOU WILL ENCOUNTER ERRORS (Tested by Stuart Cunningham)

SETUP:
1) Must be running XAMPP version 7.2.5 or later (PHP 7.2.5 or later). If using an older version the survey may/will produce errors.
https://www.apachefriends.org/download.html

2) Copy files from the subdirectory "php files" to xampp/htdocs, e.g. C:\xampp\htdocs

3) Copy the folder imgs to xampp/htdocs, e.g. C:\xampp\htdocs. In the folder that this readme is found, there is an image with the name "File hierarchy.png", this demonstrates how the PHP files and imgs folder should appear in your file explorer.

4) Open xampp and start Apache and MySQL service. If not using default port(80) append the port you are using to the URL in the next step

5) Go to 'http://localhost/' in a browser (preferably chrome) you will be automatically redirected to the about.php page

6) If the database does not already exist you will be prompted to create it on the about page.

7) From here you can sign up, create surveys, answer the default survey view results etc.

OTHER:
- admin account has username: "admin", password: "secret"
- create_data.php has been renamed to init_database.php
- dummy accounts are created in the init_database.php script

TO DO:
- Handle questions where response = null
- Improve use of superglobals (make site more secure)
- Response summaries

EXTRA:
