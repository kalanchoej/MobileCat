<?
    require_once("util.php");

    $sm = get_smarty();
    $p = new SiteParse();

    try {
        if ($name = ar_get('name', $_REQUEST)) {
            $_SESSION['name'] = $name;
        }

        if ($code = ar_get('code', $_REQUEST)) {
            $_SESSION['code'] = $code;
        }

	if ($pin = ar_get('pin', $_REQUEST)) {
	    $_SESSION['pin'] = $pin;
	}

        if ($resp = $p->login($name, $code, $pin)) {
            $_SESSION['userid'] = $resp['userid'];
            header("Location: " . $_REQUEST['redirect']);

        } 

        elseif($name == 'admin' and $code == $p->admin_pass) {
            $_SESSION['userid'] = $resp['userid'];
            header("Location: settings.php");
        }
        else {
            do_logout();
          
            if (ar_get('small', $_REQUEST)) {
                throw new Exception("Invalid login.");
            } else {
                $query = http_build_query(array(
                    "error" => 1,
                    "redirect" => $_REQUEST['redirect'],
                ));

                header("Location: " . $p->base_url . "login?$query");
            }
        }

    } catch (Exception $e) {
        $sm->assign("error", $e->GetMessage());
        $sm->display("responses/error.html");
    }
?>
