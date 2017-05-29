<?php
require_once "../config.php";

// The Tsugi PHP API Documentation is available at:
// http://do1.dr-chuck.com/tsugi/phpdoc/namespaces/Tsugi.html

use \Tsugi\Core\Settings;
use \Tsugi\Core\LTIX;
use \Tsugi\Util\Net;

$LAUNCH = LTIX::requireData(); 
if ( ! $USER->instructor ) {
    $OUTPUT->jsonError('not authorized');
    return;
}

$rows = $PDOX->allRowsDie("SELECT user_id,attend,ipaddr 
    FROM {$CFG->dbprefix}attend
     WHERE link_id = :LI ORDER BY attend DESC, user_id",
     array(':LI' => $LINK->id)
);

$OUTPUT->jsonOutput($rows);

