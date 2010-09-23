<?
    require_once('Smarty/Smarty.class.php');
    require 'SiteParse.php';
    
    $sm = new Smarty();
    $settings = new SiteParse();
    foreach($settings as $setting => $value) {
        $sm->assign($setting, $value);
    }

    try {
        header("Content-Type: text/html; charset=utf-8");
        $sm->display("pages/config.html");

    } catch (Exception $e) {
        $sm->assign("error", $e->GetMessage());
        $sm->display("responses/error.html");
    }
?>
