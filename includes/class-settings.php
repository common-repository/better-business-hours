<?php
/**
 * Better Business Hours Settings
 *
 * @since 1.0.0
 * @package Better Business Hours
 */

/**
 * Better Business Hours Settings.
 *
 * @since 1.0.0
 */
class BBH_Settings {
	/**
	 * Parent plugin class
	 *
	 * @var   class
	 * @since 1.0.0
	 */
	protected $plugin = null;

	protected $option_name = 'bbh_settings';

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
			'schema' => '2017-04-26',
			'timezone_string' => get_option( 'timezone_string' ),
			'start_of_week' => get_option( 'start_of_week', 1 ),
			'time_format' => get_option( 'time_format' ),
			'time_start' => "8",
			'time_end' => "18",
		);
		$weekdays = $this->get_weekdays( array(
			'sorted_by_user_settings' => false,
		) );
		$this->defaults['start_of_week'] = $weekdays[$this->defaults['start_of_week']];
		return $this->defaults;
	}

	public static function get_weekdays( $args=array() ) {
		$args = shortcode_atts( array(
			'sorted_by_user_settings' => true,
		), $args );

		$weekdays = array(
			'Sunday',
			'Monday',
			'Tuesday',
			'Wednesday',
			'Thursday',
			'Friday',
			'Saturday',
		);

		if ( empty( $args['sorted_by_user_settings'] ) ) {
			return $weekdays;
		}

		$settings = Better_Business_Hours::get_instance()->settings->get();
		$start_of_week_index = array_search( $settings['start_of_week'], $weekdays );
		for ($i=0; $i < count( $weekdays ); $i++) { 
			$current_index = ($start_of_week_index + $i) % 7;
			$current_weekday = $weekdays[$current_index];
			$reordered_weekdays[$current_weekday] = $current_weekday;
		}

		return $reordered_weekdays;
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
			if ( $setting === '' ) {
				$settings[$key] = $default_settings[$key];
			}
		}

		if ( empty( $settings['timezone_string'] ) ) {
			$settings['timezone_string'] = 'UTC';
		}

		return $settings;
	}

	public function update( $new_settings ) {
		$existing_settings = $this->get();
		$merged_settings = shortcode_atts( $existing_settings, $new_settings );
		return $this->set( $merged_settings );
	}

	private function set( $new_settings ) {
		$default_settings = $this->get_defaults();
		$new_settings['schema'] = $default_settings['schema'];
		return update_option( $this->option_name, json_encode( $new_settings ) );
	}
}
