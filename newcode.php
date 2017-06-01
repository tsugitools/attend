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

if ( ! isset($_POST['code'])) {
    $OUTPUT->jsonError('Missing code parameter');
    return;
}

Settings::linkSet('code', $_POST['code']);

$OUTPUT->jsonOutput(array('success'=>1, 'detail'=>'Code Updated'));
