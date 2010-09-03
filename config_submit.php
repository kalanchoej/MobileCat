<?
    # this whole block of for $smarty and $settings might be replaced by util.php once integrated
    require 'SiteParse.php';
    require 'util.php';

    $smarty = get_smarty();
    $settings = new SiteParse();
    foreach($settings as $setting => $value) {
        $smarty->assign($setting, $value);
    }

    # handle changes submitted
    if($_POST['admin_pass'] != $settings->admin_pass) {                           
        $error = "Invalid administrator password, please try again.";         
    } elseif($_POST) {
        # open settings
        $filename = "SiteParse.php";
        $f = fopen($filename, "r+");
        $contents = fread($f, filesize($filename));
        fclose($f);
        
        foreach($_POST as $name => $value) {
            # not allowed to change password in config form; todo: change password in config form
            if($name == 'admin_pass') continue;
            if($name == 'scoping') $enable_scoping = 1;

            # if $name is found in $contents, replace the whole line
            $contents = preg_replace("/$name.*/", "$name = \"$value\";", $contents);
        }

        # initialize our scopes
        if($enable_scoping) {
            # gather scopes
            $scopes = $settings->find_scopes($settings->catalog_url."/search/X");

            # pop off default scope
            $default_scope = array_pop($scopes);

            $scopes_string .= 'array(';
            foreach($scopes as $scope) { 

                $scopes_string .= 'array("name" => "'.$scope['name'].'", "value" => "'.$scope['value'].'"),';
            }
            $scopes_string .= ')';

            # enable scoping in file
            $contents = preg_replace("/scoping.*/", "scoping = 1;", $contents);
            $contents = preg_replace("/def_scope.*/", "def_scope = $default_scope;", $contents);

            # write scopes to file
            $contents = preg_replace("/scopes.*/", "scopes = $scopes_string;", $contents);
        } else {
            # $scoping = 0
            $contents = preg_replace("/scoping.*/", "scoping = 0;", $contents);

            # $def_scope = 0
            $contents = preg_replace("/def_scope.*/", "def_scope = 0;", $contents);

            # $scopes = array()
            $contents = preg_replace("/scopes.*/", "scopes = null;", $contents);
        }

        # write new settings to file
        $f = fopen($filename, "w");
        $contents = fwrite($f, $contents);
        fclose($f);
    }
    
    if(isset($error)) {
        $smarty->assign('error', $error);
        $smarty->display('responses/error.html');    
    } else {
        # display form, including existing values in form
        header("Location: settings.php");
    }
?>
