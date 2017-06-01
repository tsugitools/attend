<?php
require_once "../config.php";

use \Tsugi\Core\Settings;
use \Tsugi\Core\LTIX;

// No parameter means we require CONTEXT, USER, and LINK
$LAUNCH = LTIX::requireData();

if ( ! $USER->instructor ) {
    $OUTPUT->jsonAuthError('Must be instructor');
    return;
}

$PDOX->queryDie("DELETE FROM {$CFG->dbprefix}attend WHERE link_id = :LI",
    array(':LI' => $LINK->id)
);

$OUTPUT->jsonOutput(array('success'=>1, 'detail'=>'Data cleared'));
