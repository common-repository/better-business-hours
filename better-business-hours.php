<?php
/**
 * Plugin Name: Better Business Hours
 * Description: Easily set and display your business hours
 * Version:     1.0.3.2
 * Author:      TylerDigital
 * Author URI:  https://tylerdigital.com
 * Donate link: https://tylerdigital.com
 * License:     GPLv2
 * Text Domain: better-business-hours
 * Domain Path: /languages
 *
 * @link https://tylerdigital.com
 *
 * @package Better Business Hours
 * @version 1.0.1
 */

/**
 * Copyright (c) 2017 TylerDigital (email : support@tylerdigital.com)
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License, version 2 or, at
 * your discretion, any later version, as published by the Free
 * Software Foundation.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 */

/**
 * Built using generator-plugin-wp
 */


/**
 * Autoloads files with classes when needed
 *
 * @since  1.0.0
 * @param  string $class_name Name of the class being requested.
 * @return void
 */
function better_business_hours_autoload_classes( $class_name ) {
	if ( 0 !== strpos( $class_name, 'BBH_' ) ) {
		return;
	}

	$filename = strtolower( str_replace(
		'_', '-',
		substr( $class_name, strlen( 'BBH_' ) )
	) );

	Better_Business_Hours::include_file( $filename );
}
spl_autoload_register( 'better_business_hours_autoload_classes' );

/**
 * Main initiation class
 *
 * @since  1.0.0
 */
final class Better_Business_Hours {

	/**
	 * Current version
	 *
	 * @var  string
	 * @since  1.0.0
	 */
	const VERSION = '1.0.1';

	/**
	 * URL of plugin directory
	 *
	 * @var string
	 * @since  1.0.0
	 */
	protected $url = '';

	/**
	 * Path of plugin directory
	 *
	 * @var string
	 * @since  1.0.0
	 */
	protected $path = '';

	/**
	 * Plugin basename
	 *
	 * @var string
	 * @since  1.0.0
	 */
	protected $basename = '';

	/**
	 * Singleton instance of plugin
	 *
	 * @var Better_Business_Hours
	 * @since  1.0.0
	 */
	protected static $single_instance = null;

	/**
	 * Instance of BBH_Admin
	 *
	 * @since 1.0.0
	 * @var BBH_Admin
	 */
	protected $admin;

	/**
	 * Instance of BBH_Shortcode
	 *
	 * @since 1.0.0
	 * @var BBH_Shortcode
	 */
	protected $shortcode;

	/**
	 * Instance of BBH_Api
	 *
	 * @since 1.0.0
	 * @var BBH_Api
	 */
	protected $api;

	/**
	 * Instance of BBH_Api_Settings
	 *
	 * @since 1.0.0
	 * @var BBH_Api_Settings
	 */
	protected $api_settings;

	/**
	 * Instance of BBH_Settings
	 *
	 * @since 1.0.0
	 * @var BBH_Settings
	 */
	protected $settings;

	/**
	 * Creates or returns an instance of this class.
	 *
	 * @since  1.0.0
	 * @return Better_Business_Hours A single instance of this class.
	 */
	public static function get_instance() {
		if ( null === self::$single_instance ) {
			self::$single_instance = new self();
		}

		return self::$single_instance;
	}

	/**
	 * Sets up our plugin
	 *
	 * @since  1.0.0
	 */
	protected function __construct() {
		$this->basename = plugin_basename( __FILE__ );
		$this->url      = plugin_dir_url( __FILE__ );
		$this->path     = plugin_dir_path( __FILE__ );
	}

	/**
	 * Attach other plugin classes to the base plugin class.
	 *
	 * @since  1.0.0
	 * @return void
	 */
	public function plugin_classes() {
		// Attach other plugin classes to the base plugin class.
		$this->admin = new BBH_Admin( $this );
		$this->shortcode = new BBH_Shortcode( $this );
		$this->settings = new BBH_Settings( $this );
		$this->availability = new BBH_Availability( $this );
	} // END OF PLUGIN CLASSES FUNCTION

	public function api_classes() {
		$this->api_settings = new BBH_Api_Settings( $this );
		$this->api_availability = new BBH_Api_Availability( $this );
	}

	/**
	 * Add hooks and filters
	 *
	 * @since  1.0.0
	 * @return void
	 */
	public function hooks() {
		add_action( 'init', array( $this, 'init' ), 9 );
		require( self::dir( 'includes/class-widget.php' ) );
		add_action( 'rest_api_init', array( $this, 'api_classes' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'register_scripts' ), 5 );
		add_action( 'wp_enqueue_scripts', array( $this, 'register_scripts' ), 5 );
	}

	public function register_scripts() {
		wp_register_script( 'moment', $this->url('assets/javascripts/moment-with-locales.js' ) );
		wp_register_script( 'moment-tz', $this->url('assets/javascripts/moment-timezone-with-data.js'), array( 'moment' ) );
		wp_register_script( 'availability', $this->url('assets/javascripts/jquery.availability.js'), array( 'moment', 'moment-tz', 'jquery' ) );
	}

	/**
	 * Activate the plugin
	 *
	 * @since  1.0.0
	 * @return void
	 */
	public function _activate() {
		// Make sure any rewrite functionality has been loaded.
		flush_rewrite_rules();
	}

	/**
	 * Deactivate the plugin
	 * Uninstall routines should be in uninstall.php
	 *
	 * @since  1.0.0
	 * @return void
	 */
	public function _deactivate() {}

	/**
	 * Init hooks
	 *
	 * @since  1.0.0
	 * @return void
	 */
	public function init() {
		if ( $this->check_requirements() ) {
			load_plugin_textdomain( 'better-business-hours', false, dirname( $this->basename ) . '/languages/' );
			$this->plugin_classes();
		}
	}

	/**
	 * Check if the plugin meets requirements and
	 * disable it if they are not present.
	 *
	 * @since  1.0.0
	 * @return boolean result of meets_requirements
	 */
	public function check_requirements() {
		if ( ! $this->meets_requirements() ) {

			// Add a dashboard notice.
			add_action( 'all_admin_notices', array( $this, 'requirements_not_met_notice' ) );

			// Deactivate our plugin.
			add_action( 'admin_init', array( $this, 'deactivate_me' ) );

			return false;
		}

		return true;
	}

	/**
	 * Deactivates this plugin, hook this function on admin_init.
	 *
	 * @since  1.0.0
	 * @return void
	 */
	public function deactivate_me() {
		deactivate_plugins( $this->basename );
	}

	/**
	 * Check that all plugin requirements are met
	 *
	 * @since  1.0.0
	 * @return boolean True if requirements are met.
	 */
	public static function meets_requirements() {
		// Do checks for required classes / functions
		// function_exists('') & class_exists('').
		// We have met all requirements.
		return true;
	}

	/**
	 * Adds a notice to the dashboard if the plugin requirements are not met
	 *
	 * @since  1.0.0
	 * @return void
	 */
	public function requirements_not_met_notice() {
		// Output our error.
		echo '<div id="message" class="error">';
		echo '<p>' . sprintf( __( 'Better Business Hours is missing requirements and has been <a href="%s">deactivated</a>. Please make sure all requirements are available.', 'better-business-hours' ), admin_url( 'plugins.php' ) ) . '</p>';
		echo '</div>';
	}

	/**
	 * Magic getter for our object.
	 *
	 * @since  1.0.0
	 * @param string $field Field to get.
	 * @throws Exception Throws an exception if the field is invalid.
	 * @return mixed
	 */
	public function __get( $field ) {
		switch ( $field ) {
			case 'version':
				return self::VERSION;
			case 'basename':
			case 'url':
			case 'path':
			case 'admin':
			case 'shortcode':
			case 'api_settings':
			case 'settings':
			case 'availability':
			case 'api_availability':
				return $this->$field;
			default:
				throw new Exception( 'Invalid '. __CLASS__ .' property: ' . $field );
		}
	}

	/**
	 * Include a file from the includes directory
	 *
	 * @since  1.0.0
	 * @param  string $filename Name of the file to be included.
	 * @return bool   Result of include call.
	 */
	public static function include_file( $filename ) {
		$file = self::dir( 'includes/class-'. $filename .'.php' );
		if ( file_exists( $file ) ) {
			return include_once( $file );
		}
		return false;
	}

	/**
	 * This plugin's directory
	 *
	 * @since  1.0.0
	 * @param  string $path (optional) appended path.
	 * @return string       Directory and path
	 */
	public static function dir( $path = '' ) {
		static $dir;
		$dir = $dir ? $dir : trailingslashit( dirname( __FILE__ ) );
		return $dir . $path;
	}

	/**
	 * This plugin's url
	 *
	 * @since  1.0.0
	 * @param  string $path (optional) appended path.
	 * @return string       URL and path
	 */
	public static function url( $path = '' ) {
		static $url;
		$url = $url ? $url : trailingslashit( plugin_dir_url( __FILE__ ) );
		return $url . $path;
	}
}

/**
 * Grab the Better_Business_Hours object and return it.
 * Wrapper for Better_Business_Hours::get_instance()
 *
 * @since  1.0.0
 * @return Better_Business_Hours  Singleton instance of plugin class.
 */
function better_business_hours() {
	return Better_Business_Hours::get_instance();
}

// Kick it off.
add_action( 'plugins_loaded', array( better_business_hours(), 'hooks' ) );

register_activation_hook( __FILE__, array( better_business_hours(), '_activate' ) );
register_deactivation_hook( __FILE__, array( better_business_hours(), '_deactivate' ) );
