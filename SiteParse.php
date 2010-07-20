<?

require_once('IIIParse.php');

class SiteParse extends IIIParse {

    public $catalog_url = "http://www.example.com/";
    public $base_url    = "http://m.example.com/";

    public $catalog_name = "Catalog";
    public $def_scope    = "0";

    public $email_from   = "library@example.com";
    public $feedback_email   = "feedback@example.com";

    public static $cover_image_type = "openlibrary";
    #public static $cover_image_type = "googlebooks"; 
    #public static $cover_image_type = "contentcafe";
    #public static $cc_user = "CONTENTCAFEUSERNAME";                                                                
    #public static $cc_pass = "CONTENTCAFEPASSWORD";
    #public static $cover_image_type = "syndetics";
    #public static $syndetics_client = "CLIENTID";

    public $email_subject_prefix = "Library";
}

?>
