;(function ($, bbh, undefined) {

	var userTime = moment.tz(),
		userTimezone = userTime.format('z'),
		userOffset = userTime.format('Z'),
		siteTime = userTime.tz(bbhSettings.timezone_string),
		siteTimezone = siteTime.format('z'),
		siteOffset = siteTime.format('Z'),
		bbhListings;

	var translateTimeFormat = function(format) {
		switch(format) {
			case 'g:i a':
				return 'h:mm a';
				break;
			case 'g:i A':
				return 'h:mm A';
				break;
			case 'H:i':
				return 'H:mm';
				break;
			default:
				return 'H:mm';
		}
	};

	var translateDateFormat = function(format) {
		switch(format) {
			case 'F j, Y':
				return 'MMMM D, Y';
				break;
			case 'Y-m-d':
				return 'Y-MM-DD';
				break;
			case 'm/d/Y':
				return 'MM/DD/Y';
				break
			case 'd/m/Y':
				return 'DD/MM/Y';
				break;
			default:
				return 'MMMM D, Y';
		}
	}

	var isDifferentTimezone = function(){
		if (userOffset !== siteOffset) {
			return true;
		}
		return false;
	};

	var displayTimezoneInfo = function(){
		if (isDifferentTimezone()) {
			var tzDisplay = $('<div class="better-business-hours-timezone-info"></div>').appendTo(bbhListings),
				dateFormat = bbhListings.data('date-format'),
				localTimezone = $('<div>Business hours are displayed in ' + siteTimezone + '.</div>').appendTo(tzDisplay),
				localTime = $('<div>Local business time is ' + siteTime.format(translateTimeFormat(bbhSettings.time_format) + ' on dddd, ' + translateDateFormat(dateFormat)) + '.</div>').appendTo(tzDisplay);
		}
	};

	var displayOpenClosed = function(){
		var siteDay = siteTime.format('dddd'),
			dayHours = bbhAvailability[siteDay];

		for (var i=0; i<dayHours.length; i++) {
			var isOpen = false;
			var time_start = moment.tz(dayHours[i].time_start, translateTimeFormat(bbhSettings.time_format), bbhSettings.timezone_string);
			var time_end = moment.tz(dayHours[i].time_end, translateTimeFormat(bbhSettings.time_format), bbhSettings.timezone_string);

			if (siteTime.isBetween(time_start, time_end)) {
				isOpen = true;
				break;
			}
		}

		var openClosed = $('<div class="better-business-hours-is-open"></div>').appendTo(bbhListings),
			status = isOpen ? 'Open' : 'Closed',
			message = openClosed.html('Currently: <strong>' + status + '</strong>');
	};

	bbh.init = function(){
		bbhListings = $('.better-business-hours');
		displayOpenClosed();
		displayTimezoneInfo();
	};

}(jQuery, window.bbh = window.bbh ||{}));

jQuery(function(){
	bbh.init();
});