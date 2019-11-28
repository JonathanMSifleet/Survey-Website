<?php

// execute the header script:
require_once "header.php";

// checks the session variable named 'loggedInSkeleton'
// take note that of the '!' (NOT operator) that precedes the 'isset' function
if (!isset($_SESSION['loggedInSkeleton'])) {
    // user isn't logged in, display a message saying they must be:
    echo "You must be logged in to view this page.<br>";
} // the user must be signed-in, show them suitable page content
else {
    echo <<<_END
  <div class="column">
        <h2>Google Forms</h2>
        <h3>Layout</h3>
        <img src="imgs/Forms/Layout.png" style="max-width:50%;" align="right" alt="Image of Google Forms layout">
        <p>Perhaps the most glaring thing when creating a Google form is that the design is very bold but simple. Google has designed the form creation using material design, which is characterised by its use of straight edges, square elements in the page and lack of shadows or curves. What Google lacks in the complexity of design is made up by its simplicity. Although what I have said is a contradiction, Forms doesn’t suffer from added complexity that other survey sites that I will review do.</p>
        <p>It is noteworthy to add that Forms are not limited to the colour purple. By clicking the colour palette in the top-right, you can change the font-style, header image, theme colour, and background colour. The background colour is defined by the intensity of the theme colour, with the option of adding custom colours, so your choice of colours is not limited, but the fact that pre-defined background colours are pre-determined based upon the main theme colour is a good added-touch as it can allow the colour scheme to look very cohesive</p>

        <h3>Ease of use</h3>
        <p>Despite Google’s use of icons on the side of the main form, I was confused at first on how to add new questions or other information to the form as I didn’t originally see the icons. Even though this criticism could be invalidated as more eagle-eyed users of Forms will likely see it, I think the design could be improved. To improve it, I would make the option to add new questions more obvious, but I think it would be difficult for the average person to suggest good improvements that Google could make to Forms.</p>
        <img src="imgs/Forms/Template.png" style="max-width:50%;" align="right" alt="Image of Google templates">
        <p>However, the ease of use for Forms comes at the sacrifice of templates (see Survey Monkey review). Whilst Forms only has twelve templates which are not a large amount compared to Survey Monkey’s extensive catalogue. Forms’ templates are rather sensible and do not swamp the user with options. This is a benefit as for the general user the templates will serve most of their needs, and if a template does not fit a user’s needs perfectly, they can edit the template to suit their needs.</p>

        <h3>User account set-up / login</h3>
        <p>Perhaps the biggest drawback of Google Forms is that Google Forms is tied into Google’s ecosystem which may dissuade users who do not already have a Google or Gmail account. Whilst I have been a user of Gmail since at least 2013 and use many Google services such as Drive, Docs, etc., the requirement for users to create a Google account is a huge detriment to the otherwise great Google Forms.</p>
        <p>Should someone who does not already have a Google account decide to get one, the user will be required to fill in their name, a secure password and what they’d like their email address to be with the “@gmail.com” prefix amended onto the end. After this, the user will be required to input a phone number which does lock out users who do not have a phone, but if you have a pre-existing Gmail account then you can use the same phone number again without any issues.</p>
        <p>Ultimately, if a user disregards any privacy concerns, they may for using Google’s ecosystem or uses a pre-existing account, the account-setup is quite standard, and the login process is extremely as you only need your email and password like most other websites.</p>

        <h3>Question types</h3>
        <p>Forms, like most other aspects of it, continues to provide a middle-ground in offering just more than the bare minimum without adding so many that the user is swamped with different options, adding to the complexity of creating a survey. Forms employ the following types:</p>
        <ul>
            <li>Multiple choice</li>
            <li>Short Answer</li>
            <li>Paragraph</li>
            <li>Checkboxes</li>
            <li>Dropdown</li>
            <li>Dates</li>
            <li>Times</li>
        </ul>
        <p>I think that Forms has just the right selection of questions as it fulfils all the possible questions, I think I would need, and the question types that it is missing can be recreated using other question types. For example, Survey Monkey includes a star rating system, but this can be recreated using a dropdown.</p>
        <p>Despite my praise for its number of questions, I think more complex question types such as rankings or scales that its competitors have, could be included but I think this functionality could be added in the paragraph option for forms.</p>

        <h3>Analysis tools</h3>
        <img src="imgs/Forms/Graphs.png" style="max-width:50%;" align="right" alt="Image of Google templates">
        <p>Forms enables the survey creator to view either an individual’s response or all the responses with analysis applied in the form of charts such as pie charts, bar charts, etc. By viewing the data from responses in charts, it highlights the most popular results whilst simultaneously giving the creator of the survey an easy-to-understand summary of the survey’s results. In addition to the visual display of data that the graphs and charts give the user, Forms also shows the user how many people voted for each answer which bolsters its ability to summarise data that the creator of the survey may find useful. Additionally, Forms can also record the respondent’s email which could also be useful if the survey creator wanted to ask the respondent some follow-up questions. The respondent does have to consent to their email being recorded which does bypass any privacy issues the user may have.</p>
</div>

_END;
}

// finish off the HTML for this page:
require_once "footer.php";
?>