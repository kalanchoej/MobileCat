<?

require_once('IIIParse.php');

class SiteParse extends IIIParse {
    function SiteParse() {
        foreach(json_decode(gzuncompress(file_get_contents('.settings'))) as $setting => $value) {
            $this->$setting = $value;
        }
    }
}
?>
