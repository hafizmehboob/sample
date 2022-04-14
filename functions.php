<?php
/** Custom Cities Tables * */
function custom_citites_tables() {
    require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
    global $wpdb;
    $cities = $wpdb->prefix . 'custom_cities';
    $streets = $wpdb->prefix . 'custom_streets';
    $sql = 'CREATE TABLE ' . $cities . '(
        cities_id int NOT NULL AUTO_INCREMENT,
        cities_name varchar(255),
        PRIMARY KEY (cities_id))';
    dbDelta($sql);
    $sqlForStreets = 'CREATE TABLE ' . $streets . '(
        streets_id int NOT NULL AUTO_INCREMENT,
        streets_name varchar(255),
        cities_id int,
        PRIMARY KEY (streets_id),
        FOREIGN KEY (cities_id) REFERENCES ' . $wpdb->prefix . 'custom_cities(cities_id))';
    dbDelta($sqlForStreets);

    $xml_url = "https://data.gov.il/dataset/321/resource/d04feead-6431-427f-81bc-d6a24151c1fb/download/d04feead-6431-427f-81bc-d6a24151c1fb.xml";
    $xmlfile = simplexml_load_file($xml_url);
    $xmlRowCount = count($xmlfile->ROW);
    $count = 0;
    $citiesData = array();
    $streetData = array();
    $makeCityUnique = array();
   // $makeStreetsUnique = array();
    foreach ($xmlfile->ROW as $xml) {
        $cityName = trim(str_replace('(שבט)', '', $xml->שם_ישוב[0]));
        $streetName = trim(str_replace('(שבט)', '',$xml->שם_רחוב[0]));
        if (!in_array((string)$cityName, $makeCityUnique, true)) {
            $count++;
            $citiesData[] = ["cities_id" => $count, "cities_name" => $cityName];
            $makeCityUnique[] = $cityName;
            $streetData[] = ["streets_name" => $streetName, "cities_id" => $count];
        } else{
            //if(!in_array((string)$streetName, $makeStreetsUnique, true)){
           // $makeStreetsUnique[] = $streetName;
            $streetData[] = ["streets_name" => $streetName, "cities_id" => $count];
        }
        
    }
     $citiesData = array_map("unserialize", array_unique(array_map("serialize", $citiesData)));
     $streetData = array_map("unserialize", array_unique(array_map("serialize", $streetData)));

    global $wpdb;
    $q = "INSERT INTO " . $wpdb->prefix . "custom_cities" . " (cities_name) VALUES ";
    foreach ($citiesData as $an_item) {
        $q .= $wpdb->prepare(
                "(%s),",
                $an_item['cities_name']
        );
    }
    $q = rtrim($q, ',') . ';';
    $wpdb->query($q);

    $streetQuery = "INSERT INTO " . $wpdb->prefix . "custom_streets" . " (streets_name,cities_id) VALUES ";
    foreach ($streetData as $street_item) {
        $streetQuery .= $wpdb->prepare(
                "( %s, %d),",
                $street_item['streets_name'], $street_item['cities_id']
        );
    }
    $streetQuery = rtrim($streetQuery, ',') . ';';
    $wpdb->query($streetQuery);
}	
if(!get_option( 'my_run_only_once_01' )){
    update_option('my_run_only_once_01', 1);
    add_action('init', 'custom_citites_tables');
}

function get_streets_form_DB() {
    global $wpdb;
    $cityId = (int) $_POST['cityId'];
    $results = $wpdb->get_results("SELECT * FROM " . $wpdb->prefix . 'custom_streets' . " WHERE cities_id=" . $cityId." ORDER BY streets_name ASC");
    $streetsDiv = "<option value='' disabled selected>בחר רחוב</option>";
    foreach ($results as $getStreets){
        $streetsDiv .= "<option value='" . $getStreets->streets_id . "'>" . $getStreets->streets_name . "</option>";
    }
    echo $streetsDiv;
}

add_action('wp_ajax_nopriv_get_streets_form_DB', 'get_streets_form_DB');
add_action('wp_ajax_get_streets_form_DB', 'get_streets_form_DB');

?>