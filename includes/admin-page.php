<div class="wrap">
	<h1>Better Business Hours</h1>

	<h2 id="setting-section-head">Settings</h2>
	<?php $settings = Better_Business_Hours::get_instance()->settings->get(); ?>

	<div class="bbh-settings">
		<dl class="bbh-setting">
			<dt>Week starts on</dt>
			<dd>
				<span class="bbh-display-value"></span>
				<a href="#">Edit</a>
				<select name="start_of_week">
					<?php foreach( BBH_Settings::get_weekdays() as $weekday ): ?>
						<option value="<?php echo $weekday; ?>" <?php selected( $settings['start_of_week'], $weekday, true ); ?>>
							<?php echo $weekday; ?>
						</option>
					<?php endforeach; ?>
				</select>
			</dd>
		</dl>

		<dl class="bbh-setting">
			<dt>Day starts at</dt>
			<dd>
				<span class="bbh-display-value"></span>
				<a href="#">Edit</a>
				<select name="time_start">
					<?php for( $i=0; $i<24; $i++ ): ?>
						<option value="<?php echo $i; ?>" <?php selected( $settings['time_start'], $i, true ); ?>>
							<?php
							$time = $i%12;
							if ( $time === 0 ) {
								$time = 12;
							}
							if ( $i < 12 ) {
								$time .= 'am';
							} else {
								$time .= 'pm';
							}
							echo $time;
							?>
						</option>
					<?php endfor; ?>
				</select>
			</dd>
		</dl>

		<dl class="bbh-setting">
			<dt>Day ends at</dt>
			<dd>
				<span class="bbh-display-value"></span>
				<a href="#">Edit</a>
				<select name="time_end">
					<?php for( $i=0; $i<24; $i++ ): ?>
						<option value="<?php echo $i; ?>" <?php selected( $settings['time_end'], $i, true ); ?>>
							<?php
							$time = $i%12;
							if ( $time === 0 ) {
								$time = 12;
							}
							if ( $i < 12 ) {
								$time .= 'am';
							} else {
								$time .= 'pm';
							}
							echo $time;
							?>
						</option>
					<?php endfor; ?>
				</select>
			</dd>
		</dl>

		<dl class="bbh-setting">
			<dt>Timezone</dt>
			<dd>
				<span class="bbh-display-value"></span>
				<a href="#">Edit</a>
				<select name="timezone_string">
					<?php
					$timezone_strings = DateTimeZone::listIdentifiers();
					?>
					<?php foreach( $timezone_strings as $value ): ?>
						<option value="<?php echo $value; ?>" <?php selected( $settings['timezone_string'], $value, true ); ?>>
							<?php echo $value; ?>
						</option>
					<?php endforeach; ?>
				</select>
			</dd>
		</dl>

		<dl class="bbh-setting">
			<dt>Time Format</dt>
			<dd>
				<span class="bbh-display-value"></span>
				<a href="#">Edit</a>
				<select name="time_format">
					<?php
					$time_formats = array(
						'g:i a' => '1:45 am',
						'g:i A' => '1:45 AM',
						'H:i' => '01:45',
					);
					?>
					<?php foreach( $time_formats as $value => $label ): ?>
						<option value="<?php echo $value; ?>" <?php selected( $settings['time_format'], $value, true ); ?>>
							<?php echo $label; ?>
						</option>
					<?php endforeach; ?>
				</select>
			</dd>
		</dl>
	</div>

	<h2 id="hours-section-header">Business Hours</h2>
	<h4>Click/drag below to indicate your availability</h4>

	<div id="availability"></div>

	<div id="instructions">
		<h3>Simply type <code>[business_hours]</code> anywhere in your content to display your hours</h3>
		<h3>Or add the Business Hours <a href="<?php echo admin_url( 'widgets.php' ); ?>">widget</a></h3>
	</div>

</div>