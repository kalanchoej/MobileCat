<?

require_once('IIIParse.php');

class SiteParse extends IIIParse {

    public $catalog_url = "https://lance.searchmobius.org";
    public $base_url = "https://m.lance.searchmobius.org/";

    public $method_type = "mobileinfo";
    #public $method_type = "scrape";

    public $catalog_name = "Lance";

    public $email_from = "no-reply@searchmobius.org";
    public $feedback_email = "feedback@searchmobius.org";

    #public $cover_image_type = "syndetics";
    #public $cover_image_type = "openlibrary";
    #public $cover_image_type = "contentcafe";
    public $cover_image_type = "googlebooks";

    # Because some catalogs use non-standard item table layouts, set the values below to the appropriate array keys
    #public $loc_td = 0;
    #public $call_td = 1;
    #public $status_td = 2;
    
    public $cover_userid = "";
    public $cover_pass = "";

    public $email_subject_prefix = "Library ";

    # If users must enter a PIN number to log in, set this to 1
    public $uses_pin = 1;

    # to enable scoping, set this to 1
    public $scoping = 1;

    # define scopes within here
    public $scopes = array(
        # copy the following line (without the leading '#') for each scope your catalog uses, replacing [NAME] and [VALUE] with each associated scope
        #   array("name" => "[NAME]", "value" => "[VALUE]"),
        #
        # example:
        #   array("name" => "Reference", "value" => "1"),
        #   array("name" => "Books", "value" => "2"),
        #   .. etc
	array("name" => "Search entire collection", "value" => "50"),
	array("name" => "A.T. Still", "value" => "3"),
	array("name" => "Culver-Stockton", "value" => "1"),
	array("name" => "Hannibal-Lagrange", "value" => "2"),
	array("name" => "Linn State", "value" => "4"),
	array("name" => "MAAC", "value" => "5"),
	array("name" => "Truman", "value" => "6"),
   ); 
   # end scopes

}
?>
