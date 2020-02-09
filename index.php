<?php
require_once "../config.php";

// The Tsugi PHP API Documentation is available at:
// http://do1.dr-chuck.com/tsugi/phpdoc/

use \Tsugi\Core\LTIX;
use \Tsugi\Util\Net;
use \Tsugi\Core\Settings;
use \Tsugi\UI\SettingsForm;

// No parameter means we require CONTEXT, USER, and LINK
$LAUNCH = LTIX::requireData();

// If settings were updated
if ( SettingsForm::handleSettingsPost() ) {
    header( 'Location: '.addSession('index.php') ) ;
    return;
}

// Handle Post Data
$p = $CFG->dbprefix;
$old_code = $LAUNCH->link->getJsonKey('code', '');
$old_code = $LAUNCH->link->settingsGet('code', $old_code);
$send_grade = $LAUNCH->link->settingsGet('grade');
$match = $LAUNCH->link->settingsGet('match');
$ip = Net::getIP();

if ( isset($_POST['clear']) && $USER->instructor ) {
    $PDOX->queryDie("DELETE FROM {$p}attend WHERE link_id = :LI",
            array(':LI' => $LINK->id)
    );
    $_SESSION['success'] = 'Data cleared';
    header( 'Location: '.addSession('index.php') ) ;
    return;
} 

if ( isset($_POST['code']) ) { // Student
    if ( $old_code != $_POST['code'] ) {
        $_SESSION['error'] = __('Code incorrect');
        header( 'Location: '.addSession('index.php') ) ;
        return;
    }

    if ( strlen($match) > 0 && substr($match, 0, 1) == '/' ) {
        if (!preg_match($match, $ip) ) {
            $_SESSION['error'] = __('IP Address '.$ip.' does not match (regex).');
            header( 'Location: '.addSession('index.php') ) ;
            return;
        }
    }

    if ( strlen($match) > 0 && substr($match, 0, 1) != '/' ) {
        if ( strpos($match, $ip) === false ) {
            $_SESSION['error'] = __('IP Address '.$ip.' does not match.');
            header( 'Location: '.addSession('index.php') ) ;
            return;
        }
    }

    // Passed all the tests..
    $PDOX->queryDie("INSERT INTO {$p}attend
        (link_id, user_id, ipaddr, attend, updated_at)
        VALUES ( :LI, :UI, :IP, NOW(), NOW() )
        ON DUPLICATE KEY UPDATE updated_at = NOW()",
        array(
            ':LI' => $LINK->id,
            ':UI' => $USER->id,
            ':IP' => Net::getIP()
        )
    );

    if ( $send_grade && isset($LAUNCH->link) && $LAUNCH->link ) {
        if ($LAUNCH->result && $LAUNCH->result->id && $RESULT->grade < 1.0 ) {
            $RESULT->gradeSend(1.0, false);
        }
    }

    $_SESSION['success'] = __('Attendance Recorded...');
    header( 'Location: '.addSession('index.php') ) ;
    return;
}

// Prepare for view
if ( $USER->instructor ) {
    $rows = $PDOX->allRowsDie("SELECT A.user_id,attend,A.ipaddr, displayname, email
            FROM {$p}attend AS A
            JOIN {$p}lti_user AS U ON U.user_id = A.user_id
            WHERE link_id = :LI ORDER BY attend DESC, user_id",
            array(':LI' => $LINK->id)
    );
} else {
    $rows = false;
}

// Render view
$OUTPUT->header();
$OUTPUT->bodyStart();
$OUTPUT->topNav();

if ( $USER->instructor ) {
    echo('<div style="float:right;">');
    echo('<form method="post" style="display: inline">');
    echo('<input type="submit" class="btn btn-warning" name="clear" value="'.__('Clear data').'">');
    echo("</form>\n");
    SettingsForm::button(false);
    echo('</div>');

    $OUTPUT->welcomeUserCourse();
    echo('<br clear="all">');
    SettingsForm::start();
    echo("<p>Configure the LTI Tool<p>\n");
    SettingsForm::text('code',__('Code'));
    SettingsForm::checkbox('grade',__('Send a grade'));
    SettingsForm::text('match',__('This can be a prefix of an IP address like "142.16.41" or if it starts with a "/" it can be a regular expression (PHP syntax)'));
    echo("<p>Your current IP address is ".htmlentities(Net::getIP())."</p>\n");
    SettingsForm::done();
    SettingsForm::end();
}

$OUTPUT->flashMessages();

echo("<!-- Classic single-file version of the tool -->\n");

// Ask the user for the code
if ( $USER->instructor ) {
    echo("<p>");
    if ( strlen($old_code) < 1 ) {
        echo(__("Use the setting link to configure the attendance code."));
    } else {
        echo(__("You can use the setting link to change the attendance code."));
    }
    echo("</p>\n");
} else {
    echo('<form method="post">');
    echo(__("Enter code:")."\n");
    echo('<input type="text" name="code" value=""> ');
    echo('<input type="submit" class="btn btn-normal" name="set" value="'.__('Record attendance').'"><br/>');
    echo("\n</form>\n");
}

// Check the regex
if ( $USER->instructor && strlen($match) > 0 && substr($match, 0, 1) == '/' ) {
   @preg_match($match, $ip);
   if ( preg_last_error() != PREG_NO_ERROR ) {
       echo('<p style="color:red;">Syntax error in regular expression '.htmlentities($match)."</p>\n");
    }
}

if ( $rows ) {
    echo('<table border="1">'."\n");
    echo("<tr><th>".__("User")."</th><th>".__("Attendance")."</th><th>".__("IP Address")."</th></tr>\n");
    foreach ( $rows as $row ) {
        echo "<tr><td>";
        echo('<a href="#" onclick="alert(\'');
        echo($row['user_id']);
        if ( strlen($row['email']) > 0 || strlen($row['displayname']) > 0 ) echo(' | ');
        if ( strlen($row['email']) > 0 ) {
            echo(htmlent_utf8($row['email']));
            if ( strlen($row['displayname']) > 0 ) echo(' | ');
        }
        if ( strlen($row['displayname']) > 0 ) {
            echo(htmlent_utf8($row['displayname']));
        }
        echo('\'); return false;">');
        echo(' &nbsp;*******&nbsp; ');
        echo('</a>');
        echo("</td><td>");
        echo($row['attend']);
        echo("</td><td>");
        echo(htmlent_utf8($row['ipaddr']));
        echo("</td></tr>\n");
    }
    echo("</table>\n");
}

$OUTPUT->footer();
