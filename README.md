6G5Z2107 - 2CWK50 - 2019/20
Jonathan Sifleet
18014017

SETUP:
1) Must be running XAMPP version 7.2 or later (PHP 7.2 or later). If using an older version the survey may/will produce errors.
https://www.apachefriends.org/download.html

2) Copy files in the subdirectory "php files" to xampp/htdocs, e.g. C:\xampp\htdocs

3) Open xampp and start Apache and MySQL service. If not using default port(80) append the port you are using to the URL in the next step

4) Go to 'http://localhost/' you will be automatically redirected to the about.php page

5) If the database does not already exist you will be prompted to create it.

6) From here you can sign up, create surveys, answer the default survey view results etc.

OTHER:
- create_data.php has been renamed to init_database.php
- dummy accounts are created in the init_database.php script
- admin account has username: "admin", password: "secret"

TO DO:
- Add checkbox with multiple responses to graphs
- Improve use of superglobals (make site more secure)
- Response summaries

EXTRA:
I have included my requirements as a .docx