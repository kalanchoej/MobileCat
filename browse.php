<?
    require_once("util.php");

    function get_browse_html($results) {
        $sm = get_smarty();

        $sm->assign('results', $results);

        return $sm->fetch('pages/search_results.html');
    }

    list($results, $prev, $next, $num) = get_browse_results();

    $html = get_browse_html($results);

    $json_data = array(
        "results" => $results,
        "prev"    => $prev,
        "next"    => $next,
        "num"     => $num,

        "results_html" => $html,
    );

    if (array_key_exists('searcharg', $_REQUEST)) {
        $json_data['searcharg'] = $_REQUEST['searcharg'];
    }

    header("Content-Type: application/javascript; charset=utf-8");
    
    echo json_encode($json_data);
?>
