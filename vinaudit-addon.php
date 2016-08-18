<?php
/*
Plugin Name: Gravity Forms VinAudit Addon
Plugin URI:
Description: Plugin created to allow users to integrate with VinAudit to get vehicle information
and history reports.

Version: 1.0.3
Author: Bret Wagner
Author URI: http://bwagner-webdev.com

------------------------------------------------------------------------
Copyright 2016 Bret Wagner

This program is distributed without any kind of warranty or guarantee
implied or otherwise. Use at your own risk.

Software written by Bret Wagner is written for personal use and not inteded
for public access or use. Any use by anyone is deemed as agreement that the
author is not to be held responsible for any damage caused by this program
or any variation thereof
*/

define( 'GF_VINAUDIT_ADDON_VERSION', '1.0.3' );

add_action( 'gform_loaded', array( 'GF_VinAudit_AddOn_Bootstrap', 'load' ), 5 );

class GF_VinAudit_AddOn_Bootstrap {

    public static function load() {

        if ( ! method_exists( 'GFForms', 'include_addon_framework' ) ) {
            return;
        }

        require_once( 'class-gfvinauditaddon.php' );

        GFAddOn::register( 'GFVinAudit' );
    }

}

function mail_dev($message, $subject) {
    $admin = "bwagner@drivestl.com";
    $headers = "From: Test App <test@drivestl.com>";
    $message = wordwrap( $message, 70 );
    mail($admin, $subject, $message, $headers);
}

// The "_1" on the next line of code will need to be changed to match the form id we are working with
add_action('gform_after_submission', 'check_enabled', 10, 2);

function check_enabled($entry, $form) {
    // check form settings to see if checking the vin is enabled on the submitted form
    $id = $form['id'];
    $form_meta = GFAPI::get_form($id);
    $vinaudit = $form_meta['vinaudit'];
    // $settings = GFAPI::get_form_settings($form);
    // $vinaudit = $settings['vinaudit'];
    if ( $vinaudit['enabled'] == '1' ) {
        // $message = print_r($entry, true);
        // mail_dev($message, "getting entry ids");
        query_vin($entry, $form);
    }
}

function query_vin($entry, $form) {
    // This will actually take the value from the vin field and query it
    $id = $form['id'];
    $form_meta = GFAPI::get_form($id);
    global $wpdb;
    $settings = get_option('gravityformsaddon_vinaudit_settings');
    $apikey = $settings['api_key'];
    $username = $settings['username'];
    $password = $settings['password'];
    $vinaudit = $form_meta['vinaudit'];
    $vin = get_vin_id($form);
    $admin_email = $vinaudit['admin_email'];
    $email_admin_enabled = $vinaudit['email_admin'];
    $email_user_enabled = $vinaudit['email_user'];
    $url = "https://api.vinaudit.com/query.php?&key=$apikey&vin=$vin&format=json";
    // mail_dev($url, 'Query URL');
    $fname = rgar($entry, get_fname($form));
    $lname = rgar($entry, get_lname($form));
    $vin_id = get_vin_id($form);
    $email_id = get_user_email($form);
    $vin = rgar($entry, $vin_id);
    $user_email = rgar($entry, $email_id);
    $results = file_get_contents($url);
    $query = json_decode($results, true);
    $qid = $query['id'];
    $user_message = $vinaudit['user_message'];
    $user_message .= "\n\n\n";
    $admin_message = $vinaudit['admin_message'];
    $admin_message .= "\n\n\n";
    $report_url = "https://api.vinaudit.com/pullreport.php/?id=$qid&user=$username&pass=$password&key=$apikey&vin=$vin&format=json&pdf=1&brand=0";

    // mail_dev($report_url . "\n\n\n\n" . "Vin: $vin \nUser email: $user_email", "developer notify");

    if ($email_admin_enabled == '1') {
        mail($admin_email, "New Vehicle Report", $admin_message . $report_url, "From: iBuyLC <webmaster@ibuylc.com>");
    }

    if ($email_user_enabled == '1' ) {
        mail($user_email, "Your Vehicle History Report", $user_message . $report_url, "From: Bret Wagner <bwagner@drivestl.com>");
    }

    // mail_dev($vinaudit['form_enabled'], "form 2 setting");
    if ( $vinaudit['form_enabled'] == '1' ) {
    second_form($entry, $form, $vin, $qid, $fname, $lname, $user_email, $vin);
    }

}

function get_vin_id($form) {
    $form = GFAPI::get_form($form['id']);
    $vinaudit = $form['vinaudit'];

    $fields = $form['fields'];
    foreach ( $fields as $field ) {
        if ( $field['label'] == $vinaudit['vin'] ) {
            $vin_id = $field['id'];
            $vin_id = strval($vin_id);
        }
    }
    return $vin_id;
}

function get_user_email($form) {
    $form = GFAPI::get_form($form['id']);
    $vinaudit = $form['vinaudit'];
    $fields = $form['fields'];
    foreach ( $fields as $field ) {
        if ( $field['label'] == $vinaudit['user_email'] ) {
            $email_id = $field['id'];
            $emial_id = strval($email_id);
        }
    }
    return $email_id;
}

// This will define what happens with the data taken from the first form

function get_field_choices($form) {
        $choices = array(array('label'=>"",'value'=>""),);
        $form = GFAPI::get_form($form['id']);
        foreach ($form['fields'] as $field) {
            $field_title = $field['label'];
            $choice = array(
                            'label' => $field_title,
                            'value' => $field_title,
                            );
            array_push( $choices, $choice );
        }
        unset($choice);
        return $choices;
}
function second_form($entry, $form, $vin, $qid, $fname, $lname, $user_email, $vin) {
    // setup variables for second form fields
    // still need to get first name, last name, email to forward to the new page.
    $form_data = GFAPI::get_form($form['id']);
    $vinaudit = $form_data['vinaudit'];

    $fname_var = $vinaudit['fname_var'];
    $lname_var = $vinaudit['lname_var'];
    $email_var = $vinaudit['email_var'];
    $vin_var = $vinaudit['vin_var'];
    $year_var = $vinaudit['year_var'];
    $make_var = $vinaudit['make_var'];
    $model_var = $vinaudit['model_var'];
    $trim_var = $vinaudit['trim_var'];
    $abs_var = $vinaudit['abs_var'];
    $engine_var = $vinaudit['engine_var'];
    $style_var = $vinaudit['style_var'];
    $clean_var = $vinaudit['clean_var'];
    $origin_var = $vinaudit['country_var'];
    $tank_var = $vinaudit['tank_var'];
    $steering_var = $vinaudit['steering_var'];
    $height_var = $vinaudit['height_var'];
    $width_var = $vinaudit['width_var'];
    $length_var = $vinaudit['length_var'];
    $seating_var = $vinaudit['seating_var'];
    $opt_seating_var = $vinaudit['opt_seating_var'];
    $highway_var = $vinaudit['highway_var'];
    $citympg_var = $vinaudit['city_var'];
    $form_location = $vinaudit['next_page'];
    $home = get_home_url();
    $settings = get_option('gravityformsaddon_vinaudit_settings');
    $apikey = $settings['api_key'];
    $username = $settings['username'];
    $password = $settings['password'];
    $report_url = "https://api.vinaudit.com/pullreport.php/?id=$qid&user=$username&pass=$password&key=$apikey&vin=$vin&format=json";

    $report = file_get_contents($report_url);
    $data = json_decode($report, true);
    $year = $data['specs']['Year'];
    $make = $data['specs']['Make'];
    $model = $data['specs']['Model'];
    $trim = $data['specs']['Trim'];
    $abs = $data['specs']['Anti-Brake System'];
    $engine = $data['specs']['Engine'];
    $style = $data['specs']['Style'];
    $clean = $data['clean'];
    $country = $data['specs']['Made In'];
    $steering = $data['specs']['Steering Type'];
    $tank = $data['specs']['Tank Size'];
    $height = $data['specs']['Overall Height'];
    $width = $data['specs']['Overall Width'];
    $length = $data['specs']['Overall Length'];
    $seating = $data['specs']['Standard Seating'];
    $optional_seating = $data['specs']['Optional Seating'];
    $highway = $data['specs']['Highway Mileage'];
    $city = $data['specs']['City Mileage'];

    if ( $steering == "R&P") {
        $steering = "Rack and Pinion";
    }

    if ( $clean == '1' ) {
        $clean = 'yes';
    } elseif ( $clean == '0' ) {
        $clean = 'no';
    } else {
        $clean = "error getting clean title value";
    }
    $url = $home . $form_location . "/?$fname_var=$fname&$lname_var=$lname&$vin_var=$vin&$steering_var=$steering&$email_var=$user_email&$year_var=$year&$make_var=$make&$model_var=$model&$trim_var=$trim&$abs_var=$abs&$engine_var=$engine&$style_var=$style&$clean_var=$clean&$origin_var=$country&$tank_var=$tank&$height_var=$height&$width_var=$width&$length_var=$length&$seating_var=$seating&$opt_seating_var=$optional_seating&$highway_var=$highway&$citympg_var=$city";

    header("location: $url");

}

function get_fname($form) {
    $form = GFAPI::get_form($form['id']);
    $vinaudit = $form['vinaudit'];
    $fields = $form['fields'];
    foreach ( $fields as $field ) {
        if ( $field['label'] == $vinaudit['fname_field'] ) {
            $fname_id = $field['id'];
            $fname_id = strval($fname_id);
        }
    }
    return $fname_id;
}
function get_lname($form) {
    $form = GFAPI::get_form($form['id']);
    $vinaudit = $form['vinaudit'];
    $fields = $form['fields'];
    foreach ( $fields as $field ) {
        if ( $field['label'] == $vinaudit['lname_field'] ) {
            $lname_id = $field['id'];
            $lname_id = strval($lname_id);
        }
    }
    return $lname_id;
}

function gf_simple_addon() {
    return GFVinAudit::get_instance();
}
