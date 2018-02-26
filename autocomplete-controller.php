<?php

use Tygh\Registry;
use Tygh\Http;

if (!defined('BOOTSTRAP')) { die('Access denied'); }

if ($mode == 'autocomplete_city') {
    $params = $_REQUEST;
    $select = array();
    $prefix = array('гор.','г.' ,'г ', 'гор ','город ');
    $condition = '';

    if (defined('AJAX_REQUEST')) {

        $d_country = db_get_fields("SELECT code FROM ?:countries WHERE status = 'A'");

        if (!empty($params['check_country']) && $params['check_country'] != 'undefined') {
            $d_country = array();
            $d_country[] = $params['check_country'];
        }

        $data_states = array();

        if (!empty($d_country)) {
            $condition .= db_quote(" AND c.country_code IN (?a)", $d_country);

            if (!empty($params['check_state']) && $params['check_state'] != 'undefined') {
                $data_states = db_get_fields("SELECT code FROM ?:states WHERE country_code IN (?a) AND status = ?s AND code = ?s", $d_country, 'A', $params['check_state']);
            } else {
                $data_states = db_get_fields("SELECT code FROM ?:states WHERE country_code IN (?a) AND status = ?s", $d_country, 'A');
            }
        }

        if (!empty($data_states)) {
            $condition .= db_quote(" AND c.state_code IN (?a) ", $data_states);

            $params['q'] = str_replace($prefix,'',$params['q']);
            $table_description = '?:rus_city_descriptions';

            $search = trim($params['q']) . "%";

            $join = db_quote("LEFT JOIN ?:rus_cities as c ON c.city_id = d.city_id");

            $condition .= db_quote(" AND c.status = ?s", 'A');

            $cities = db_get_array("SELECT d.city, c.city_code FROM ?:rus_city_descriptions as d ?p WHERE city LIKE ?l AND lang_code = ?s  ?p ORDER BY reg_centr DESC LIMIT ?i", $join , $search , CART_LANGUAGE, $condition, 10);

            if (!empty($cities)) {
                foreach ($cities as $city) {
                    $select[] = array(
                        'code' => $city['city_code'],
                        'value' => $city['city'],
                        'label' => $city['city'],
                    );
                }
            }
        }

        Registry::get('ajax')->assign('autocomplete', $select);
        exit();
    }

    exit();
}

if ($mode == 'save_city') {

    if (defined('AJAX_REQUEST')) {
        $ful_adr_from_google = explode(",", $_REQUEST['adr_from_google']);
        $ful_adr_from_google = array_map('trim', $ful_adr_from_google);
        $check_city_from_db = db_get_field("SELECT city_id FROM ?:rus_city_descriptions WHERE lang_code = ?s AND city = ?s", CART_LANGUAGE, $ful_adr_from_google[0]);
        if (empty($check_city_from_db)) {
            $adr_to_db['city'] = $ful_adr_from_google[0];
            $adr_to_db['lang_code'] = CART_LANGUAGE;
            $adr_to_db['state_code'] = $_REQUEST['check_state'];
            $adr_to_db['country_code'] = $_REQUEST['check_country'];
            $adr_to_db['status'] = "A";
            $city_id = db_query("INSERT INTO ?:rus_cities ?e", $adr_to_db);
            $adr_to_db['city_id'] = $city_id;
            $city_id = db_query("REPLACE INTO ?:rus_city_descriptions ?e", $adr_to_db);
        }
        exit();
    }
    
    exit();
    
}

if ($mode == 'get_state') {

    $state = '';
    $state_id = '';
    if (defined('AJAX_REQUEST')) {
        $state_id = db_get_field("SELECT state_id FROM ?:states WHERE code = ?s", $_REQUEST['state']);
        $state = db_get_field("SELECT state FROM ?:state_descriptions WHERE lang_code = ?s AND state_id = ?s", CART_LANGUAGE, $state_id);
        Registry::get('ajax')->assign('state_short', $_REQUEST['state']);
        $xml = simplexml_load_file('http://maps.google.com/maps/api/geocode/xml?address=' . $state . '&sensor=false&language='.CART_LANGUAGE);
        $state = $xml->result->address_component->long_name;
        Registry::get('ajax')->assign('state_google', $state);
        exit();
    }
    exit();
}
