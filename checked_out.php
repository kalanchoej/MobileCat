<?
    require_once("util.php");

    $sm = get_smarty();
    $p = new SiteParse();

    try { 
        if (!ar_get('userid', $_SESSION)) {
            $query = http_build_query(array(
                "redirect" => $p->base_url . "checked_out",
            ));
            
            header("Location: " . $p->base_url . "login?" . $query);
            
        } else {
            $url = ar_get('url', $_REQUEST);
            $items = $p->get_checked_out_items($_SESSION['name'], $_SESSION['code'], $_SESSION['userid'], $url);

            header("Content-Type: text/html; charset=utf-8");
            $sm->assign("items", $items);
            
            $sm->display("pages/checked_out.html");
        }
    } catch (Exception $e) {
        $sm->assign("error", $e->GetMessage());
        $sm->display("responses/error.html");
    }
?>

