<? 
    require_once("util.php");

    clear_prev_search();
    $sm = get_smarty();

    try {
        $sm->assign("jsfiles", array("static/index.js"));
        $sm->assign("is_index", true);
	if(isset($_REQUEST['scope'])) $sm->assign("scope", $_REQUEST['scope']);

        header("Content-Type: text/html; charset=utf-8");
        $sm->display('pages/index.html');

    } catch (Exception $e) {
        $sm->assign("error", $e->GetMessage());
        $sm->display("responses/error.html");
    }

?>
