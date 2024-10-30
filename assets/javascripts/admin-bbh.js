;(function ($, bbh, undefined) {

	var scheduleContainer,
		settings = {
			start_of_week: bbhSettings.start_of_week,
			time_start: bbhSettings.time_start,
			time_end: bbhSettings.time_end,
			time_format: bbhSettings.time_format,
			selectedAvailability: bbhAvailability
		};

	var translateTimeFormat = function(){
		switch(settings.time_format) {
			case 'g:i a':
				settings.time_format = 'h:mm a';
				break;
			case 'g:i A':
				settings.time_format = 'h:mm A';
				break;
			case 'H:i':
				settings.time_format = 'H:mm';
				break;
			default:
				settings.time_format = 'H:mm';
		}
	};

	var tableInit = function() {
		scheduleContainer.empty();
		scheduleContainer.availability(settings);
	};

	var updateAvailability = function(){
		var msgContainer = $('#hours-section-header');

		scheduleContainer.on('avail.update', function(e, avail){
			settings.selectedAvailability = avail;
			$.ajax({
				url: bbhApi.root+'/availability',
				type: 'POST',
				data: avail,
				beforeSend: function(xhr){
					xhr.setRequestHeader('X-WP-Nonce', bbhApi.nonce);
					displayStatus(msgContainer, 'pending', 'Saving...');
				},
				success: function(response) {
					displayStatus(msgContainer, 'success', 'Saved!');
				},
				failure: function(error) {
					displayStatus(msgContainer, 'error', 'Error: Couldn\'t save changes');
					console.warn(error);
				}
			});
		});
	};

	var displayStatus = function(container, type, message) {
		container.find('.message').remove();

		var icon;

		switch (type) {
			case 'pending':
				icon = $('<span class="dashicons dashicons-image-rotate"></span>');
				break;
			case 'error':
				icon = $('<span class="dashicons dashicons-warning"></span>');
				break;
			case 'success':
				icon = $('<span class="dashicons dashicons-yes"></span>');
				break;
			default:
				icon = $('');
		}

		var msgContainer = $('<span class="message ' + type + '"></span>');
		msgContainer.text(message);
		msgContainer.prepend(icon);

		container.append(msgContainer);

		if (type == 'success') {
			msgContainer.delay(2000).fadeOut(500, function(){
				$(this).remove()
			});
		}
	};

	var displayInitialSettings = function(){
		var settingsContainers = $('.bbh-setting');

		settingsContainers.each(function(){
			var $this = $(this),
				select = $this.find('select'),
				value = select.val(),
				displayValue = select.find('[value="' + value + '"]').text(),
				valueContainer = $this.find('.bbh-display-value').text(displayValue);
			editSetting($this);
			saveSetting(select);
		});
	};

	var editSetting = function(setting) {
		var link = setting.find('a'),
			valueDisplay = setting.find('.bbh-display-value'),
			select = setting.find('select');

		link.on('click', function(e){
			e.preventDefault();
			link.hide();
			valueDisplay.hide();
			select.show();
		});
	};

	var saveSetting = function(select) {
		var msgContainer = $('#setting-section-head');

		select.on('change', function(e){
			var $this = $(this),
				newVal = $this.val(),
				displayVal = $this.find('[value="' + newVal + '"]').text(),
				link = $this.siblings('a'),
				display = $this.siblings('.bbh-display-value'),
				data = {};

			data[select.attr('name')] = newVal;
			select.hide();
			link.show();
			display.text(displayVal).show();
			settings[select.attr('name')] = newVal;
			translateTimeFormat();

			$.ajax({
				url: bbhApi.root+'/settings',
				type: 'POST',
				data: data,
				beforeSend: function(xhr){
					xhr.setRequestHeader('X-WP-Nonce', bbhApi.nonce);
					displayStatus(msgContainer, 'pending', 'Saving...');
				},
				success: function(response) {
					displayStatus(msgContainer, 'success', 'Saved!');
					tableInit();
				},
				failure: function(error) {
					displayStatus(msgContainer, 'error', 'Error: Couldn\'t save changes');
					console.warn(error);
				}
			});
		});
	};

	bbh.init = function() {
		scheduleContainer = $('div#availability');
		translateTimeFormat();
		tableInit();
		updateAvailability();
		displayInitialSettings();
	};

}(jQuery, window.bbh = window.bbh || {}));


jQuery(function(){
	bbh.init();
});