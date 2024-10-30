<?php
/**
 * Better Business Hours Shortcode
 *
 * @since 1.0.0
 * @package Better Business Hours
 */

/**
 * Better Business Hours Shortcode.
 *
 * @since 1.0.0
 */
class BBH_Shortcode {
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
		add_shortcode( 'business_hours', array( $this, 'business_hours_shortcode' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'register_scripts' ) );
	}

	public function register_scripts() {
		// wp_register_script( 'bbh', $this->plugin->url( 'assets/javascripts/bbh.js' ), array( 'jquery', 'moment', 'moment-tz' ), $this->plugin::VERSION, true );
		wp_register_script( 'bbh', $this->plugin->url( 'assets/javascripts/bbh.js' ), array( 'jquery', 'moment', 'moment-tz' ), time(), true );
		wp_localize_script( 'bbh', 'bbhSettings', $this->plugin->settings->get() );
		wp_localize_script( 'bbh', 'bbhAvailability', $this->plugin->availability->get() );

		// wp_enqueue_style( 'bbh', $this->plugin->url('assets/styles/bbh.css'), array(), $this->plugin::VERSION );
		wp_enqueue_style( 'bbh', $this->plugin->url('assets/styles/bbh.css'), array(), time() );
		wp_enqueue_script( 'bbh' );
	}

	public function business_hours_shortcode( $atts ) {
		$atts = shortcode_atts( array(

		), $atts, 'business_hours' );

		ob_start();
		include $this->plugin->dir( 'includes/template-display-hours.php' );
		$output = ob_get_clean();

		return $output;
	}
}
