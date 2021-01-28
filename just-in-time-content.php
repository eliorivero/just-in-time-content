<?php
/**
 * Plugin Name: Just in Time Content
 * Plugin URI: https://startfunction.com/just-in-time-content-for-wordpress
 * Description: Displays content based on specified time.
 * Version: 1.0.0
 * Author: Elio Rivero
 * Author URI: https://startfunction.com/
 * License: GPLv2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Domain Path: /languages
 * Text Domain: startfunction
 * Requires at least: 5.3
 * Requires PHP: 7.0
 */

defined( 'ABSPATH' ) || exit;

define( 'SF_JITC_VERSION', '1.0.0' );
define( 'SF_JITC_URI', plugins_url( '' , __FILE__ ) );
define( 'SF_JITC_DIR', plugin_dir_path( __FILE__ ) );

$name = 'startfunction_jitc';

/**
 * Load localization file
 */
add_action( 'plugins_loaded', function() {
	load_plugin_textdomain( 'startfunction', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' ); 
} );

/**
 * Create Settings Page
 * @since 1.0.0
 */
require_once 'startfunction-admin.php';
$startfunction_jitc = new StartFunction_JITC_Admin( array(
	'basefile' => SF_JITC_DIR . basename(__FILE__),
	'base_uri' => SF_JITC_URI,
	'prefix' 	 => $name
));

add_action( 'plugins_loaded', function() use ( $startfunction_jitc ) {
	$now = new DateTime( wp_date( 'H:i', current_datetime()->getTimestamp() ) );
	$replace_home_page = 0;

	$morning_start_time = new DateTime( $startfunction_jitc->get( 'morning_start_time_sel') );
	$morning_end_time = new DateTime( $startfunction_jitc->get( 'morning_end_time_sel') );
	if ( $morning_start_time <= $now && $now <= $morning_end_time ) {
		$replace_home_page = $startfunction_jitc->get('morning_page_sel');
	} else {
		$noon_start_time = new DateTime( $startfunction_jitc->get( 'noon_start_time_sel') );
		$noon_end_time = new DateTime( $startfunction_jitc->get( 'noon_end_time_sel') );
		if ( $noon_start_time <= $now && $now <= $noon_end_time ) {
			$replace_home_page = $startfunction_jitc->get('noon_page_sel');
		} else {
			$afternoon_start_time = new DateTime( $startfunction_jitc->get( 'afternoon_start_time_sel') );
			$afternoon_end_time = new DateTime( $startfunction_jitc->get( 'afternoon_end_time_sel') );
			if ( $afternoon_start_time <= $now && $now <= $afternoon_end_time ) {
				$replace_home_page = $startfunction_jitc->get('afternoon_page_sel');
			} else {
				$night_start_time = new DateTime( $startfunction_jitc->get( 'night_start_time_sel') );
				$night_end_time = new DateTime( $startfunction_jitc->get( 'night_end_time_sel') );
				if ( $night_start_time <= $now && $now <= $night_end_time ) {
					$replace_home_page = $startfunction_jitc->get('night_page_sel');
				}
			}
		}
	}

	if ( ! is_admin() && $replace_home_page ) {
		add_filter( 'option_page_on_front', function () use ( $replace_home_page ) {
			return $replace_home_page;
		} );
	}

} );
