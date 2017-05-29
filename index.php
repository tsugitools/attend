<?php
require_once "../config.php";

// The Tsugi PHP API Documentation is available at:
// http://do1.dr-chuck.com/tsugi/phpdoc/namespaces/Tsugi.html

use \Tsugi\Core\Settings;
use \Tsugi\Core\LTIX;
use \Tsugi\Util\Net;

// No parameter means we require CONTEXT, USER, and LINK
$LAUNCH = LTIX::requireData(); 

$old_code = Settings::linkGet('code', '');

// View
$OUTPUT->header();
$OUTPUT->bodyStart();
$OUTPUT->flashMessages();
$OUTPUT->welcomeUserCourse();

echo("<!-- tmpljs version of the tool -->\n");
echo('<div id="application">'."\n");
echo('<div id="option-notification"></div>'."\n");
echo('<div id="result"><img src="'.$OUTPUT->getSpinnerUrl().'"></div>'."\n");
echo('<div id="table"></div>'."\n");
echo("</div>\n");

$OUTPUT->footerStart();

?>
<script>
function notify(type, msg) {
    if (type == undefined) {
        $('#option-notification').html('');
    } else {
        $('#option-notification').html(tmpl('tmpl-notify', {type: type, msg: msg}));
    }
}
function doAjax(script, post) {
        $.ajax({
            url: script,
            type: 'POST',
            dataType: 'text',
            data: post
        }).done(function(res) {
            notify('success', 'Your selection was saved.');
        }).fail(function(err) {
            notify('danger', '<strong>Error</strong> server call failed.');
        }).always(function() {
        });
}
</script>

<script type="text/x-tmpl" id="tmpl-notify">
    <div class="alert alert-{%=o.type%}">{%#o.msg%}</div>
</script>

<?php
if ( $USER->instructor ) {
?>
<script type="text/x-tmpl" id="instructor-form">
    Enter code:
    <input type="text" value="<?= $old_code ?>" id="instructor-code">
    <input type="submit" class="btn btn-normal" id="instructor-submit"
        value="Update Code">
    <input type="submit" class="btn btn-warning" id="instructor-clear"
        value="Clear data"><br/>
    </form>
</script>

<script type="text/x-tmpl" id="instructor-table">
    {% if (o.length > 0 ) { %}
        <table border="1">
        <tr><th>User</th><th>Attendance</th><th>IP Address</th></tr>
        {% for (var i=0; i<o.length; i++) { var row = o[i]; %}
            <tr><td>{%= row.user_id %}</td><td>{%= row.attend %}</td><td>{%= row.ipaddr %}</td></tr>
        {% } %}
        </table>
    {% } %}
</script>

<script>

$(document).ready(function(){
    document.getElementById("result").innerHTML = tmpl("instructor-form", {});

    $('#instructor-submit').on('click', function(event) {
        event.preventDefault();
        doAjax('<?= addSession('newcode.php') ?>', {code: $('#instructor-code').val()});
    });
    $('#instructor-clear').on('click', function(event) {
        event.preventDefault();
        notify();
        doAjax('<?= addSession('clear.php') ?>', {});
    });

    $.getJSON('<?= addSession('getrows.php') ?>', function(rows) {
        window.console && console.log(rows);
        document.getElementById("table").innerHTML = tmpl("instructor-table", rows);
    }).fail( function() {
        notify('danger', 'Unable to load attendance data'); 
    });
});

</script>
<?php

} else { // Student view
?>
<script type="text/x-tmpl" id="student-form">
    Enter code:
    <input type="text" name="code" value="" id="student-code">
    <input type="submit" class="btn btn-normal" id="student-submit"
        value="Record attendance"><br/>
    </form>
</script>

<script>

$(document).ready(function(){
    document.getElementById("result").innerHTML = tmpl("student-form", {});

    $('#student-submit').on('click', function(event) {
        event.preventDefault();
        doAjax('<?= addSession('attend.php') ?>', {code: $('#student-code').val()});
    });
});
</script>
<?php
} // End Student view

$OUTPUT->footerEnd();

