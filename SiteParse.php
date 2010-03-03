<?

require_once('IIIParse.php');

class SiteParse extends IIIParse {

    public $catalog_url = "http://tripod.brynmawr.edu";
    #public $base_url    = "http://tricycle.brynmawr.edu/bseitzmobile/";
    public $base_url    = "http://m.tripod.brynmawr.edu/";

    public $catalog_name = "Tripod";
    public $def_scope    = "10";

    public $email_from   = "tripod@trilogy.brynmawr.edu";
    public $feedback_email   = "tricomobile@brynmawr.edu";

    public static $cover_image_type = "syndetics";
    public static $syndetics_client = "trico";
    #public static $cover_image_type = "openlibrary";

    public $email_subject_prefix = "Tripod";

    public $material_types = array(
        "BOOKS"             => "Book",
        "DOC DISCARD"       => "",
        "MUSIC SCORES"      => "Score",
        "MUSIC CD"          => "CD",
        "MAPS"              => "Map",
        "OTHER MEDIA"       => "",
        "OTHER MUSIC"       => "Audio",
        "SPOKEN RECORD"     => "Audio Book",
        "E-JOURNALS"        => "E-Journals",
        "PICTURES"          => "Picture",
        "MUSIC LP"          => "LP",
        "COMPUTER FILE"     => "Electronic Resource",
        "KIT"               => "Kit",
        "ARCHIVES/MSS"      => "Archives/Manuscripts",
        "OBJECTS"           => "Object",
        "JOURNAL/SERIAL"    => "Serial",
        "MSS CODEX"         => "Manuscript",
        "E-MUSIC"           => "E-Music",
        "VHS"               => "VHS",
        "E-BOOKS"           => "E-Book",
        "DVD"               => "DVD",
        "PHOTOGRAPHS"       => "Photograph",
        "LASER DISC"        => "Laser Disc",
    ); 

    protected function process_link($a) {
        $href = $a->href;
        $text = $a->innertext;

        if (preg_match('/^Connect .*(BRYN MAWR|HAVERFORD|SWARTHMORE)/', $text, $matches)) {
            $college = ucwords(strtolower($matches[1]));
            $text = "Connect from $college";
        }

        return array($href, $this->shorten_value($text));
    }

    protected function translate_material($material) {
        if ($val = ar_get($material, $this->material_types)) {
            return $val;
        } else {
            return ucwords(strtolower($material));
        }
    }
}

?>
