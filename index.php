<?php
require_once "../config.php";

// The Tsugi PHP API Documentation is available at:
// http://do1.dr-chuck.com/tsugi/phpdoc/namespaces/Tsugi.html

use \Tsugi\Core\Settings;
use \Tsugi\Core\LTIX;
use \Tsugi\Util\Net;

// No parameter means we require CONTEXT, USER, and LINK
$LAUNCH = LTIX::requireData(); 

// Model
$p = $CFG->dbprefix;
$old_code = Settings::linkGet('code', '');

if ( isset($_POST['code']) && isset($_POST['set']) && $USER->instructor ) {
    Settings::linkSet('code', $_POST['code']);
    $_SESSION['success'] = 'Code updated';
    header( 'Location: '.addSession('index.php') ) ;
    return;
} else if ( isset($_POST['clear']) && $USER->instructor ) {
    $rows = $PDOX->queryDie("DELETE FROM {$p}attend WHERE link_id = :LI",
            array(':LI' => $LINK->id)
    );
    $_SESSION['success'] = 'Data cleared';
    header( 'Location: '.addSession('index.php') ) ;
    return;
} else if ( isset($_POST['code']) ) { // Student
    if ( $old_code == $_POST['code'] ) {
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
        $_SESSION['success'] = __('Attendance Recorded...');
    } else {
        $_SESSION['error'] = __('Code incorrect');
    }
    header( 'Location: '.addSession('index.php') ) ;
    return;
}

// View
$OUTPUT->header();
$OUTPUT->bodyStart();
$OUTPUT->flashMessages();
$OUTPUT->welcomeUserCourse();

echo("<!-- Handlebars version of the tool -->\n");
echo('<div id="attend-div"><img src="'.$OUTPUT->getSpinnerUrl().'"></div>'."\n");

$OUTPUT->footerStart();
$OUTPUT->templateInclude(array('attend'));

if ( $USER->instructor ) {
?>
<script>
$(document).ready(function(){
    $.getJSON('<?= addSession('getrows.php') ?>', function(rows) {
        window.console && console.log(rows);
        context = { 'rows' : rows,
            'instructor' : true,
            'old_code' : '<?= $old_code ?>'
        };
        tsugiHandlebarsToDiv('attend-div', 'attend', context);
    }).fail( function() { alert('getJSON fail'); } );
});
</script>
<?php } else { ?>
<script>
$(document).ready(function(){
    tsugiHandlebarsToDiv('attend-div', 'attend', {});
});
</script>
<?php
} // End $USER->instructor
$OUTPUT->footerEnd();

