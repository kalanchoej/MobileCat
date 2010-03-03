<? 

    require_once("util.php");
    $sm = get_smarty();

    try {
        list($results, $prev, $next, $num) = get_browse_results();

        $sm->assign("searcharg", $_REQUEST['searcharg']);
        $sm->assign("prev", $prev);
        $sm->assign("next", $next);
        $sm->assign("num", $num);
        $sm->assign("results", $results);

        if (blank_search()) {
            $sm->assign("blanksearch", true);
        }

        header("Content-Type: text/html; charset=utf-8");
        $sm->display('pages/search_nojs_main.html');

    } catch (Exception $e) {
        $sm->assign("error", $e->GetMessage());
        $sm->display("responses/error.html");
    }

?>
