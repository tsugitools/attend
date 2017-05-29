<?php
require_once "../config.php";

use \Tsugi\Core\Settings;
use \Tsugi\Core\LTIX;
use \Tsugi\Util\Net;

// No parameter means we require CONTEXT, USER, and LINK
$LAUNCH = LTIX::requireData(); 

if ( ! $USER->instructor ) {
  header('HTTP/1.1 403 Must be instructor');
  echo '<h1>Must be instructor<h1>';
  exit();
}

if (!isset($_POST['code'])) {
  header('HTTP/1.1 400 entries parameter not set');
  echo '<h1>Form submission invalid<h1>';
  echo '<p>Please submit an `code` post parameter</p>';
  exit();
}

$PDOX->queryDie("DELETE FROM {$CFG->dbprefix}attend WHERE link_id = :LI",
    array(':LI' => $LINK->id)
);

$OUTPUT->jsonOutput(array('success'=>1, 'detail'=>'Data cleared'));
