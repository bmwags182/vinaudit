<?php
/**
 * Plugin Name: VIN Submission GForms Addon
 * Plugin URI:
 * Description: Adds the ability to search VINs and get vehicle history reports
 * Version: 0.2.2
 * Author: Bret Wagner
 * Author URI:
 * License: GPL2
 */

/**
*
*  Still not fully satisfied that VIN Audit is accurate/up to date.
*  I would like to test this with many more VIN numbers before I consider
*  myself finished.
*
*  Current Version is functional, not easily deployable at this time.
*
*  Notes for future release:
*  VinAudit Settings needs a "per-form" setting to basically enable running the
*  vin when the form is submitted. Right now my plugin simply runs when the form
*  with an id of "1" is submitted. Not every site will have their first form as the Vin form.
*
*  Also I need to be able to determine which fields are which before this can be deployed.
*  From the form setting page I will need to have a way for users to select which box is the
*  vin number input field since not all forms will be created equal.
*
**/



define( 'GF_SIMPLE_ADDON_VERSION', '0.2.2' );

add_action( 'gform_loaded', array( 'GF_Simple_AddOn_Bootstrap', 'load' ), 5 );

class GF_Simple_AddOn_Bootstrap {

    public static function load() {

        if ( ! method_exists( 'GFForms', 'include_addon_framework' ) ) {
            return;
        }

        require_once( 'class-gfvinaudit.php' );

        GFAddOn::register( 'GFVinAudit' );
    }

}

// The "_1" on the next line of code will need to be changed to match the form id we are working with
add_action('gform_after_submission_1', 'query_vin', 10, 2);

// This will define what happens with the data taken from the first form
function query_vin($entry, $form) {
    // Get values from first form
    global $wpdb;
    $fname = rgar($entry, '3');
    $lname = rgar($entry, '4');
    $email = rgar($entry, '5');
    $settings = get_option('gravityformsaddon_vinaudit_settings');
    $apikey = $settings['api_key'];
    $vin = rgar($entry, '6');
    $url = 'https://api.vinaudit.com/query.php?&key='. $apikey . '&vin='. $vin . '&format=json';

    // Retrieve vin query info
    $json_data = file_get_contents($url);
    $json_a = json_decode($json_data, true);
    $id = $json_a['id'];
    $user = $settings['username'];
    $pass = $settings['password'];
    $url = 'https://api.vinaudit.com/pullreport.php/?id=' . $id . '&user=' . $user. '&pass=' . $pass . '&key=' . $apikey . '&vin=' . $vin . '&format=json';

    // Get Vehicle report
    $json_data = file_get_contents($url);
    $url = $url . '&pdf=1&brand=0';
    $json_a = json_decode($json_data, true);

    // Decode data from report to form fields
    $year = $json_a['specs']['Year'];
    $make = $json_a['specs']['Make'];
    $model = $json_a['specs']['Model'];
    $trim = $json_a['specs']['Trim'];
    $abs = $json_a['specs']['Anti-Brake System'];
    $engine = $json_a['specs']['Engine'];
    $style = $json_a['specs']['Style'];
    $clean = $json_a['clean'];
    $country = $json_a['specs']['Made In'];
    $steering = $json_a['specs']['Steering Type'];
    $tank = $json_a['specs']['Tank Size'];
    $height = $json_a['specs']['Overall Height'];
    $width = $json_a['specs']['Overall Width'];
    $length = $json_a['specs']['Overall Length'];
    $seating = $json_a['specs']['Standard Seating'];
    $optional_seating = $json_a['specs']['Optional Seating'];
    $highway = $json_a['specs']['Highway Mileage'];
    $city = $json_a['specs']['City Mileage'];

    // Check for clean title and adjust for client readability
    if ($clean == "true") {
        $clean = "yes";
    } elseif ($clean == "false" ) {
        $clean = "no";
    } else {
        $clean = "Error";
    }

    /**
    *  Need to fix steering variable when returning "R&P"
    *  This causes issues when passed to the url for gravity forms
    *  as gravity forms sees this as a new query and tries to find
    *  the associated field to fill in data leaving you with just
    *  "R" in the field when submitted.
    *
    *  Will need more VINs to test this with in order to see what
    *  other steering types might cause issues.
    **/

    $pdf_url = 'https://api.vinaudit.com/pullreport.php/?id=' . $id . '&user=' . $user. '&pass=' . $pass . '&key=' . $apikey . '&vin=' . $vin . '&format=json&pdf=1&brand=0';

    // Set values for second page of form for confirmation

    $url = "http://ibuylux.wpengine.com/form-page-2/?fname=" . $fname . "&lname=" . $lname . "&user-email=" . $email . "&vin=" . $vin . "&car-year=" . $year . "&make=" . $make . "&model=" . $model . "&trim=" . $trim . "&style=" . $style . "&clean=" . $clean . "&engine=$engine&abs=$abs&country=$country&tank=$tank&height=$height&length=$length&width=$width&seating=$seating&opt-seat=$optional_seating&hwympg=$highway&citympg=$city";

    header("location: $url"); // After dealing with the first form (VIN submission) and it's data, forward to the next form filling in the pertinent information

    $message = $settings['message'];
    $headers = "From: IBLC <webmaster@ibuyluxurycars.com>";
    $message .= "\n\n\n" . $pdf_url;

    if ($vin) {
        mail($email, 'Your Vehicle History Report', $message, $headers);
    }


}


// run the install scripts upon plugin activation
register_activation_hook(__FILE__,'your_plugin_options_install');

function gf_simple_addon() {
    return GFVinAudit::get_instance();
}

?>
