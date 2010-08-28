<?

require_once('IIIParse.php');

class SiteParse extends IIIParse {

    public $catalog_url = "http://catalog.example.edu";
    public $base_url = "http://m.catalog.example.edu/";

    public $method_type = "mobileinfo";

    public $catalog_name = "Catalog";
    public $scoping = 0;
    public $def_scope = 0;

    public $email_from = "library@example.edu";
    public $feedback_email = "feedback@example.edu";

    public $cover_image_type = "openlibrary";
    
    public $cover_userid = "";
    public $cover_pass = "";

    public $email_subject_prefix = "Library";

    public $scopes = array();

    public $admin_pass = "admin";
}
?>
