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
    if($_POST) {
        # open settings
        $filename = "SiteParse.php";
        $f = fopen($filename, "r");
        $contents = fread($f, filesize($filename));
        fclose($f);

        foreach($_POST as $name => $value) {
            # not allowed to change password in config form
            if($name == 'scoping') $enable_scoping = 1;

            # if $name is found in $contents, replace the whole line
            //$contents = preg_replace("/$name.*/", "$name = \"$value\";", $contents);
            if(array_key_exists($name, $settings)) {
                $settings->$name = $value;
            }
        }

        # initialize our scopes
        if(isset($enable_scoping)) {
            # gather scopes
            $scopes = $settings->find_scopes($settings->catalog_url."/search/X");

            # pop off default scope
            $default_scope = array_pop($scopes);

            $settings->scoping = 1; 
            $settings->def_scope = $default_scope;
            $settings->scopes = $scopes;
        } else {
            $settings->scoping = 0;
            $settings->def_scope = 0;
            $settings->scopes = array();
        }

        # validation and standardization

        # check for trailing slash in base_url
        if(substr($settings->base_url, -1) != '/') $settings->base_url = $settings->base_url.'/';
        # remove trailing slashes from catalog_url
        if(substr($settings->catalog_url, -1) == '/') $settings->catalog_url = rtrim($settings->catalog_url, '/');

        # write new settings to file
        $f = fopen(".settings", "w");
        $contents = fwrite($f, gzcompress(json_encode($settings)));
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
