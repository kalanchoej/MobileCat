<?

require_once('simple_html_dom.php');

ini_set("memory_limit", "30M");

class IIIParse {

    public $base_url      = null;
    public $catalog_url   = null;

    public $email_subject = null;
    
    public $email_from    = null;

    public $catalog_name  = "DefaultCatalog";
    public $def_scope     = "1";
    public $def_type      = "X";

    public $feedback_email = "feedback@example.com";

    public $max_value_length = null;
    
    # Keys you would like to display from the bibDetail table
    public $detail_keys = array("Publisher", "Edition");
    public $email_detail_keys = array("Title");

    public $cover_image_config = null;
    
    public $mobile_domains = array(
        "AT&T"          => "txt.att.net",
        "Verizon"       => "vtext.com",
        "T-Mobile"      => "tmomail.net",
        "Sprint"        => "messaging.sprintpcs.com",
        "Nextel"        => "messaging.nextel.com",
        "Virgin"        => "vmobl.com",
        "Boost"         => "myboostmobile.com",
        "Alltel"        => "message.alltel.com",
        "MetroPCS"      => "mymetropcs.com",
        "Cricket"       => "mms.mycricket.com"
    );

    public $available_status = array("available");
    public $invalid_request_locs = '/^where/i';

    public $email_subject_prefix = "Catalog";

    protected function get_browse_url($searcharg, $searchscope, $searchtype) {
        $query = http_build_query(array(
            "searchtype"   => $searchtype,
            "searcharg"    => $searcharg,
            "searchscope"  => $searchscope,
        ));
        
       
        $url = $this->catalog_url . '/search/?' . $query;

        return $url;
    }

    protected function find_num_results($html) {
        $itag = $html->find("div.browseSearchtoolMessage i", 0);
        if (preg_match('/\d+/', $itag->innertext, $matches)) {
            return $matches[0];
        } else {
            return 0;
        }
    }

    protected function find_nav_links($html) {
        $links_to_get = array(
            'Next' => '',
            'Prev' => '',
        );
        
        $anchors = $html->find("td.browsePager a");
        foreach ($anchors as $a) {
            foreach ($links_to_get as $key => $value) {
                if ($a->innertext == $key) {
                    $links_to_get[$key] = $this->catalog_url . $a->href;
                }
            }
        }

        return $links_to_get;
    }

    protected function translate_material($material) {
        return strtolower($material);
    }

    protected function parse_record($row) {
        $info = array();
        $spans = $row->children();
        
        # These are the standard first 4 fields
        $info['title'] = ptext($spans[0]);
        
        $info['isbn'] = starting_digits(ptext($spans[1]));
        if ($info['isbn']) {
            $info['cover_image'] = $this->find_cover_image($info['isbn']);
        } else {
            $info['cover_image'] = $this->base_url . "static/nocover.jpg";
        }
        
        $info['media'] = $this->translate_material(($spans[2]->first_child()->alt));
        $info['bibid'] = $spans[3]->first_child()->value;
        
        # Everything after that is included in its own "extra" array
        $info['extra'] = array_map("ptext", array_slice($spans, 4));

        return $info;
    }
        
    protected function find_records($html) {
        $rows = $html->find("span.mobileinfo");

        # An array of arrays to hold the parsed info
        $records = array();

        foreach ($rows as $row) {
            array_push($records, $this->parse_record($row));
        }

        return $records;
    }
            
            
    protected function shorten_value($val, $extra="") {
        // Short circuit when this is undefined
        if ($this->max_value_length == null) {
            return $val;
        }
        
        # We have our $val that we can shorten, and possibly some extra
        # text that must fit on the same line.
        $extra_len = strlen($extra);
        $val_len   = strlen($val);
        $total_len = $val_len + $extra_len;


        # This is how much we must remove from the $val (if it's positive)
        $remove_len = $total_len - $this->max_value_length;
        
        if ($remove_len > 0) {
            return substr($val, 0, $val_len - $remove_len - 3) . '...';
        } else {
            return $val;
        }
    }

    protected function find_bib_details($html) {
        $info = array();

        $tds = $html->find("table.bibDetail td.bibInfoLabel");

        # Get all details from first table
        foreach ($tds as $td) {
            $label = trim($td->innertext);
            $str = $td->next_sibling()->find("text");
            
            $str_text = Array();
            foreach ($str as $value) {
                $str_text[] = $value->plaintext;
            }
            $value = trim(join($str_text));

            $info[$label] = $this->shorten_value($value, $label);
        }

        return $info;
    }

    protected function find_locs($html) {

        $table = $html->find("table.bibItems", 0);

        # If there's no location table, it's probably an electronic item, so
        # return nothing
        if (!$table) {
            return array();
        }

        $locs = array();

        $trs = $table->find("tr.bibItemsEntry");

        foreach ($trs as $tr) {
            $loc = array();

            # Get all columns of the location table for this row
            $tds = $tr->find("td");

            # First two columns show the physical location and the call number
            # in an <a> tag.
            $loc['location'] = ptext($tds[0]);
            $loc['call']     = ptext($tds[1]);

            # Third column shows availability status. Remove superfluous nbsp;
            $loc['status']   = strtolower(ptext($tds[2]));

            if (in_array($loc['status'], $this->available_status)) {
                $loc['available'] = true;
            }

            # Add to the list keyed by the location name
            $locs[$loc['location']][] = $loc;
        }
   
        return $locs;
    }

    protected function process_link($a) {
        $href = $a->href;
        $text = $a->innertext;

        return array($href, $this->shorten_value($text));
    }
            

    protected function find_links($html) {

        $table = $html->find("table.bibLinks", 0);

        # If no table, then this doesn't have any associated links
        if (!$table) {
            return array();
        }

        $links = array();

        $anchors = $table->find("a");
        foreach ($anchors as $a) {
            list($href, $text) = $this->process_link($a);

            array_push($links, array($href, $text));
        }

        return $links;
    }

    protected function find_orders($html) {
        $rows = $html->find(".bibOrderEntry");
        $orders = array();

        foreach ($rows as $row) {
            $orders[] = ptext($row);
        }

        return $orders;
    }

    protected function find_request_url($html) {
        $url = $html->find("a[href*=\/request]", 0);

        if ($url) {
            return $url->href;
        } else {
            return null;
        }
    }

    protected function get_browse_results($html) {

        $navlinks = $this->find_nav_links($html);
        $num      = $this->find_num_results($html);
        $results  = $this->find_records($html);

        $html->clear();
        unset($html);

        return array($results, $navlinks['Prev'], $navlinks['Next'], $num);

    }

    protected function find_cover_image($isbn) {
        return $this->base_url . "/cover_image?isbn=$isbn";
    }

    protected function find_checked_out_items($userid, $html) {
        $items = array();
        $rows = $html->find(".patFuncEntry");
        
        foreach ($rows as $row) {
            $item = array();
            $cols = $row->children();

            $cbox = $cols[0]->first_child();
            $item['checkbox_name'] = $cbox->name;
            $item['itemid'] = $cbox->value;
            $item['title'] = ptext($cols[1]);

            # ILL items will have anchor=null, no bibid, no full record.
            $anchor = $cols[1]->first_child();  
            if ($anchor) {
                $item['bibid'] = 'b' . ending_digits($anchor->href);
            }

            $item['barcode'] = ptext($cols[2]);
            # use innertext to preserve the html colors of status
            $item['status']  = strtolower($cols[3]->innertext);
            $item['call']    = ptext($cols[4]);

            $query = http_build_query(array(
                "renewsome" => "TRUE",
                $cbox->name => $cbox->value,
                "sortByDueDate" => "byduedate"
            ));
           
            $item['renew_link'] = $this->catalog_url . '/patroninfo~S' . $this->def_scope . "/$userid/items?" . $query;

            $items[] = $item;
        }

        return $items;
    }

    public function get_request_form($name, $code, $request_url) {
        $login_resp = $this->login($name, $code);

        $resp = http_parse_message(http_get(
            $this->catalog_url . $request_url, array("cookies" => $login_resp['cookies'])
        ));

        $html = str_get_dom($resp->body);
       
        $loc_options = array();
        
        $select = $html->find("select[name=locx00]", 0);
        if (!$select) {
            throw new Exception("Can't find request form.");
        }
        $options_htmls = $select->find("option");

        foreach ($options_htmls as $opt) {
            $txt = ptext($opt);
            if (!preg_match($this->invalid_request_locs, $txt)) {
                $loc_options[$opt->value] = $txt;
            }
        };

        return $loc_options;
    }

    public function send_item_request($name, $code, $request_url, $formdata) {
        $login_resp = $this->login($name, $code);

    }


    // Logs in and returns an array of cookie kvps
    public function login($name, $code) {
        $query = http_build_query(array(
            "name" => $name,
            "code" => $code,
        ));

        $login_url = $this->catalog_url . "/patroninfo/?$query";

        $resp = http_parse_message(http_get($login_url));

        # If there is any body in the response, we failed to login
        if (strlen($resp->body) > 0) {
            return null;
        } else {
            $cookies = cookie_strings_to_array($resp->headers['Set-Cookie']);
            $loc     = $resp->headers['Location'];
            return array(
                "userid"   => get_userid_from_patron_loc($loc),
                "cookies"  => $cookies,
            );
        }
    }


    public function get_checked_out_items($name, $code, $userid, $url=null) {
        $login_resp = $this->login($name, $code);

        # If renewing items, this url will be non-null
        if ($url == null) {
            $url = $this->catalog_url . '/patroninfo~S' . $this->def_scope . "/$userid/items";
        }

        $args = array(
            "sortByDueDate" => "byduedate"
        );
        $args_str = http_build_query($args);

        $resp = http_parse_message(http_get(
            "$url?$args_str", array("cookies" => $login_resp['cookies'])
        ));

        $html = str_get_dom($resp->body);
        
        # array of arrays for each checked out item, properties:
        # checkbox_name (renew0, renew1, etc.)
        # itemid  (i123435)
        # bibid (b234453)
        # title
        # barcode
        # status
        # call

        $items = $this->find_checked_out_items($userid, $html);

        return $items;
    }
    
    # Return all info key values in one dash-delimited string
    protected function get_info_all($info) {
        $vals = array();
        foreach ($this->detail_keys as $key) {
            if (ar_get($key, $info)) {
                $vals[] = $info[$key];
            }
        }

        return join(' - ', $vals);
    }
    
    public function get_record($bibid) {
        $html = file_get_dom(
            $this->catalog_url . "/record=" . $bibid
        );

        # Basic info such as author, title, etc.
        $info = $this->find_bib_details($html);
        $info_all = $this->get_info_all($info);

        if (empty($info)) {
            throw new Exception("Invalid bibid");
        }

        # Book locations (if available)
        $locs = $this->find_locs($html);

        # Links to click on (if available)
        $links  = $this->find_links($html);
        $orders = $this->find_orders($html);

        # Request button
        $request_url = $this->find_request_url($html);

        $img = null;
        if (($isbn = starting_digits(ar_get('ISBN', $info)))) {
            $img = $this->find_cover_image($isbn);
        } else {
            $img = $this->base_url . "static/nocover.jpg";
        }

        return array(
            "info"        => $info, 
            "info_all"    => $info_all, 
            "locations"   => $locs, 
            "links"       => $links, 
            "orders"      => $orders, 
            "cover_image" => $img,
            "bibid"       => $bibid,
            "request_url" => $request_url,
        );
    }

    public function get_next_browse($url) {
        return $this->get_browse_results(file_get_dom($url));
    }

    public function get_browse($searcharg, $searchscope=null, $searchtype=null,$url=null) {

        $searchscope = $searchscope ? $searchscope :  $this->def_scope;
        $searchtype  = $searchtype  ? $searchtype  :  $this->def_type;

        return $this->get_browse_results(
            file_get_dom(
                $this->get_browse_url($searcharg, $searchscope, $searchtype)
            )
        );
    }

    protected function find_hidden_fields($html) {
        $hiders = $html->find("input[type=hidden]");

        $kvp = array();

        foreach ($hiders as $hider) {
            $kvp[$hider->name] = $hider->value;
        }

        return $kvp;
    }

    # Parse bibItemsEntry from a "request this item" page
    protected function find_request_loc($html) {
        $table = $html->find("table.bibItems", 0);

        if (!$table) {
            throw new Exception("Invalid request location.");
        }

        $trs   = $table->find("tr.bibItemsEntry");

        $locs = array();

        foreach ($trs as $tr) {
            $tds = $tr->find("td");
            
            $loc = array();

            $loc['itemid']   = $tds[0]->find("input[type=radio]", 0)->value;
            $loc['location'] = ptext($tds[1]);
            $loc['call']     = ptext($tds[2]);
            $loc['status']   = strtolower(ptext($tds[3]));

            $locs[] = $loc;
        }

        return $locs;
    }

    public function get_request_loc($name, $code, $ref, $post_params) {
        $login_resp = $this->login($name, $code);
        
        # We want to both submit to and use this URL as the referrer
        $url = $this->catalog_url . $ref;

        $body = http_request_body_encode($post_params, array());

        $resp = http_parse_message(http_post_data(
            $url, $body, array(
                "cookies"    => $login_resp['cookies'],
                "referer"    => $url,
            )
        ));

        $html = str_get_dom($resp->body);

        $locs   = $this->find_request_loc($html);
        $hidden = $this->find_hidden_fields($html); 
        
        $resp = array(
            "locs"    => $locs,
            "hidden"  => $hidden,
        );

        return $resp;
    }

    public function submit_item_request($name, $code, $ref, $post_params) {
        $login_resp = $this->login($name, $code);

        # We want to both submit to and use this URL as the referrer
        $url = $this->catalog_url . $ref;

        $body = http_request_body_encode($post_params, array());

        $resp = http_parse_message(http_post_data(
            $url, $body, array(
                "cookies"    => $login_resp['cookies'],
                "referer"    => $url,
            )
        ));

        if (preg_match('/Your request for.*was successful/i', $resp->body)) {
            return true;
        } else {
            throw new Exception("Unable to request item");
        }
    }
}

function concat ($v1, $v2) {
    return $v1 . $v2;
}

function notblank ($v) {
    return preg_match('/\w+/', $v);
}

function ptext ($v) {
    return str_replace("&nbsp;", "", strip_tags(trim($v->plaintext)));
}

function starting_digits($v) {
    return regex_or_null('/^\d+/', $v);
}

function ending_digits($v) {
    return regex_or_null('/\d+$/', $v);
}

function regex_or_null($regex, $v) {
    if (preg_match($regex, $v, $matches)) {
        return $matches[0];
    } else {
        return null;
    }
}

function get_loc_name($one_loc) {
    return $one_loc['location'];
}

function cookie_strings_to_array($cookie_strings) {
    $cookies = array();

    foreach ($cookie_strings as $cookie) {
        $cookie_obj = http_parse_cookie($cookie);

        foreach ($cookie_obj->cookies as $k => $v) {
            $cookies[$k] = $v;
        }
    }

    return $cookies;
}

function get_userid_from_patron_loc($patron_loc) {
    if (preg_match('/^\/patroninfo~S\d+\/(\d+)/', $patron_loc, $matches)) {
        return $matches[1];
    } else {
        return null;
    }
}

?>
