<?php
/**
 * Better Business Hours Admin
 *
 * @since 1.0.0
 * @package Better Business Hours
 */

/**
 * Better Business Hours Admin.
 *
 * @since 1.0.0
 */
class BBH_Admin {
	/**
	 * Parent plugin class
	 *
	 * @var   class
	 * @since 1.0.0
	 */
	protected $plugin = null;

	/**
	 * Constructor
	 *
	 * @since  1.0.0
	 * @param  object $plugin Main plugin object.
	 * @return void
	 */
	public function __construct( $plugin ) {
		$this->plugin = $plugin;
		$this->hooks();
	}

	/**
	 * Initiate our hooks
	 *
	 * @since  1.0.0
	 * @return void
	 */
	public function hooks() {
		add_action( 'admin_enqueue_scripts', array( $this, 'register_scripts' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'register_scripts' ) );

		add_action( 'admin_menu', array( $this, 'admin_menu' ) );
	}

	public function admin_menu() {
		$primary = add_menu_page( 'Business Hours', 'Business Hours', 'manage_options', 'bbh', array( $this, 'render_page' ), 'dashicons-store' );
		add_action( 'admin_print_scripts-'.$primary, array( $this, 'admin_print_scripts' ) );
	}

	public function render_page() {
		include( 'admin-page.php' );
	}

	public function register_scripts() {
		wp_register_script( 'bbh-admin', $this->plugin->url('assets/javascripts/admin-bbh.js'), array( 'moment', 'moment-tz', 'availability', 'jquery' ) );

		wp_localize_script( 'bbh-admin', 'bbhSettings', $this->plugin->settings->get() );
		wp_localize_script( 'bbh-admin', 'bbhAvailability', $this->plugin->availability->get() );
		wp_localize_script( 'bbh-admin', 'bbhApi', array(
			'prefix'		=> rest_get_url_prefix(),
			'root'			=> '/'.rest_get_url_prefix().'/bbh/v1',
			'nonce'			=> wp_create_nonce( 'wp_rest' ),
		) );
	}

	public function admin_print_scripts() {
		wp_enqueue_script( 'moment' );
		wp_enqueue_script( 'moment-tz' );
		wp_enqueue_script( 'availability' );
		wp_enqueue_script( 'bbh-admin');
		
		wp_enqueue_style( 'bbh', $this->plugin->url('assets/styles/admin-bbh.css') );
	}
}
