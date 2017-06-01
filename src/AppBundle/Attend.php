<?php

namespace AppBundle;

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use \Tsugi\Core\Settings;
use \Tsugi\Util\Net;

class Attend {
    public function get(Request $request, Application $app)
    {
        global $CFG, $PDOX;
        $context = array();
        $context['old_code'] = Settings::linkGet('code', '');

        $p = $CFG->dbprefix;
        if ( $app['tsugi']->user->instructor ) {
            $rows = $PDOX->allRowsDie("SELECT user_id,attend,ipaddr FROM {$p}attend
                    WHERE link_id = :LI ORDER BY attend DESC, user_id",
                    array(':LI' => $app['tsugi']->link->id)
            );
            $context['rows'] = $rows;
        }
        return $app['twig']->render('Attend.twig', $context);
    }

    public function post(Request $request, Application $app)
    {
        global $CFG, $PDOX;
        $p = $CFG->dbprefix;
        $old_code = Settings::linkGet('code', '');
        if ( isset($_POST['code']) && isset($_POST['set']) && $app['tsugi']->user->instructor ) {
            Settings::linkSet('code', $_POST['code']);
            $app->tsugiFlashSuccess('Code updated');
        } else if ( isset($_POST['clear']) && $app['tsugi']->user->instructor ) {
            $PDOX->queryDie("DELETE FROM {$p}attend WHERE link_id = :LI",
                    array(':LI' => $app['tsugi']->link->id)
            );
            $app->tsugiFlashSuccess('Data cleared');
        } else if ( isset($_POST['code']) ) { // Student
            if ( $old_code == $_POST['code'] ) {
                $PDOX->queryDie("INSERT INTO {$p}attend
                    (link_id, user_id, ipaddr, attend, updated_at)
                    VALUES ( :LI, :UI, :IP, NOW(), NOW() )
                    ON DUPLICATE KEY UPDATE updated_at = NOW()",
                    array(
                        ':LI' => $app['tsugi']->link->id,
                        ':UI' => $app['tsugi']->user->id,
                        ':IP' => Net::getIP()
                    )
                );
                $app->tsugiFlashSuccess(__('Attendance Recorded...'));
            } else {
                $app->tsugiFlashSuccess(__('Code incorrect'));
            }
        }
        return $app->tsugiReroute('main');
    }
}
