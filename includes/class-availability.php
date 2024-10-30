<?php
/**
 * Better Business Hours Availability
 *
 * @since 1.0.0
 * @package Better Business Hours
 */

/**
 * Better Business Hours Availability.
 *
 * @since 1.0.0
 */
class BBH_Availability {
	/**
	 * Parent plugin class
	 *
	 * @var   class
	 * @since 1.0.0
	 */
	protected $plugin = null;

	protected $option_name = 'bbh_availability';

	protected $defaults = null;
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
	}

	public function get_defaults() {
		if ( !empty( $this->defaults ) ) {
			return $this->defaults;
		}

		$this->defaults = array(
			'schema' => '2017-05-11',
		);
		$weekdays = BBH_Settings::get_weekdays();
		foreach ($weekdays as $weekday_key => $weekday) {
			if ( in_array( $weekday, array( 'Saturday', 'Sunday' ) ) ) {
				$this->defaults[$weekday] = array();
				continue;
			}
			$this->defaults[$weekday] = array( array(
				'time_start' => "09:00",
				'time_end' => "17:00",
			) );
		}

		return $this->defaults;
	}

	public function get() {
		$default_settings = $this->get_defaults();
		$settings_json = get_option( $this->option_name, json_encode( array() ) );
		$settings = json_decode( $settings_json, true );

		$settings = shortcode_atts(
			$default_settings,
			$settings
		);
		foreach ($settings as $key => $setting) {
			if ( empty( $setting ) ) {
				$settings[$key] = array();
			}
		}

		return $settings;
	}

	public function update( $new_settings ) {
		return $this->set( $new_settings );
	}

	private function set( $new_settings ) {
		$default_settings = $this->get_defaults();
		$new_settings['schema'] = $default_settings['schema'];
		foreach ($new_settings as $key => $setting) {
			if ( empty( $setting ) ) {
				$new_settings[$key] = array();
			}
		}
		return update_option( $this->option_name, json_encode( $new_settings ) );
	}
}
