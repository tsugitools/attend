<?php
require_once "../config.php";

use \Tsugi\Core\Settings;
use \Tsugi\Core\LTIX;
use \Tsugi\Util\Net;

// No parameter means we require CONTEXT, USER, and LINK
$LAUNCH = LTIX::requireData(); 

if (!isset($_POST['code'])) {
    $OUTPUT->jsonError('Missing code parameter');
    return;
}

$old_code = Settings::linkGet('code', '');
if ( $old_code == $_POST['code'] ) {
    $PDOX->queryDie("INSERT INTO {$CFG->dbprefix}attend
        (link_id, user_id, ipaddr, attend, updated_at)
        VALUES ( :LI, :UI, :IP, NOW(), NOW() )
        ON DUPLICATE KEY UPDATE updated_at = NOW()",
        array(
            ':LI' => $LINK->id,
            ':UI' => $USER->id,
            ':IP' => Net::getIP()
        )
    );
    $OUTPUT->jsonOutput(array('success'=>1, 'detail'=>'Attendance recorded'));
} else {
    $OUTPUT->jsonError(array('success'=>0, 'detail'=>'Code incorrect'));
}
