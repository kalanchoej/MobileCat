<?
    require_once('Smarty/Smarty.class.php');
    require 'SiteParse.php';
    
    $sm = new Smarty();
    $settings = new SiteParse();
    foreach($settings as $setting => $value) {
        $sm->assign($setting, $value);
    }



    try {
        /*
        if ($redirect = ar_get('redirect', $_REQUEST)) {
            $sm->assign("redirect", $redirect);
        } else {
            $sm->assign("redirect", $p->base_url);
        }

        if ($error = ar_get('error', $_REQUEST)) {
            $sm->assign("error", true);
        }
        */

        header("Content-Type: text/html; charset=utf-8");
        $sm->display("pages/config.html");

    } catch (Exception $e) {
        $sm->assign("error", $e->GetMessage());
        $sm->display("responses/error.html");
    }
?>
