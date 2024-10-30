<?php
	$weekdays = BBH_Settings::get_weekdays();
	$availability = Better_Business_Hours::get_instance()->availability->get();
	$settings = Better_Business_Hours::get_instance()->settings->get();
	$time_format = get_option('date_format');
?>

<div class="better-business-hours" data-date-format="<?php echo $time_format; ?>">
	<ol class="better-business-hours-listing">
		<?php foreach ($weekdays as $weekday) : ?>
			<li>
				<span class="better-business-hours-listing-day"><?php echo $weekday; ?></span>
				<span class="better-business-hours-listing-time-blocks">
					<?php if ( count( $availability[ $weekday ] ) > 0 )  : ?>
						<?php foreach ($availability[$weekday] as $time_block) : ?>
							<span class="better-business-hours-listing-time-block">
								<span class="better-business-hours-listing-time-block-start"><?php echo date( $settings['time_format'] , strtotime( $time_block['time_start'] ) ); ?></span>
								-
								<span class="better-business-hours-listing-time-block-end"><?php echo date( $settings['time_format'] , strtotime( $time_block['time_end'] ) ); ?></span>
							</span>
						<?php endforeach; ?>
					<?php else : ?>
						<span class="better-business-hours-listing-time-block better-business-hours-listing-closed">Closed</span>
					<?php endif; ?>
				</span>
			</li>
		<?php endforeach; ?>
	</ol>
</div>
<?php if ( current_user_can( 'manage_options' ) ): ?>
	<div class="better-business-hours-admin-edit">
		<a href="<?php echo admin_url( 'admin.php?page=bbh' ); ?>">
			Edit Business Hours &amp; Settings (admin)
		</a>
	</div>
<?php endif ?>