<?
    require_once('SiteParse.php');

    $isbn = $_REQUEST['isbn'];

    $url = null;

    # Make it save it for a year
    header("Cache-Control: max-age=31536000, public");
    
    try {
        if (SiteParse::$cover_image_type == "syndetics") {
            $client = SiteParse::$syndetics_client;
            $url = "http://www.syndetics.com/index.php?isbn=$isbn/sc.gif&client=$client";
        } elseif (SiteParse::$cover_image_type == "openlibrary") {
            $url = "http://covers.openlibrary.org/b/isbn/$isbn-S.jpg";
        }

        if ($url) {
            $resp = http_parse_message(http_get($url));
        } else {
            throw new Exception("No image type configured.");
        }

        $image = imagecreatefromstring($resp->body);
        if (imagesx($image) > 1) {
            header(sprintf("Content-Type: %s", $resp->headers['Content-Type']));
            echo $resp->body;
        } else {
            throw new Exception("Image not found.");
        }

    // For any exception, return a blank image
    } catch (Exception $e) {
        header("Content-Type: image/jpeg");
        echo file_get_contents("static/nocover.jpg");
    }
?>
