;(function ($, window, document, undefined) {

	/* ----------------------------------------- */
	/* Globals */
	/* ----------------------------------------- */
	var opts,
		daysOfTheWeek,
		orderedDaysOfTheWeek,
		table, tbody, cells,
		isMouseDown = false,
		erasing = false,
		startRowIndex = null,
		startCellIndex = null;

	daysOfTheWeek = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
	orderedDaysOfTheWeek = daysOfTheWeek;

	/* ----------------------------------------- */
	/* Helpers */
	/* ----------------------------------------- */
	var stringToMoment;

	stringToMoment = function(str) {
		var time = moment(str, ['H:mm','HH:mm','h:mma','hh:mma', 'h:mmA','hh:mmA','h:mm','hh:mm']);

		return time;
	}

	/* ----------------------------------------- */
	/* Shift Week */
	/* ----------------------------------------- */
	var updateWeekOrder;

	updateWeekOrder = function(){
		var index = daysOfTheWeek.indexOf(opts.start_of_week);

		if (index < 0) {
			opts.start_of_week = 'Sunday';
		}

		var start_of_week = opts.start_of_week,
			currentFirstDay = orderedDaysOfTheWeek.shift();

		while (start_of_week != currentFirstDay) {
			orderedDaysOfTheWeek.push(currentFirstDay);
			currentFirstDay = orderedDaysOfTheWeek.shift();
		}
		orderedDaysOfTheWeek.unshift(currentFirstDay);
	};

	/* ----------------------------------------- */
	/* Table Rendering */
	/* ----------------------------------------- */

	var maybeCreateTable,
		createDaysHeaders,
		createTimeRows,
		createTimeCells,
		renderTable;

	maybeCreateTable = function(container){
		table = container.find('table');

		if (table.length < 1) {
			table = $('<table></table>', {
				'class': 'availability bordered'
			});

			container.append(table);
		}
	};

	createDaysHeaders = function() {
		var thead = $('<thead></thead>'),
			theadRow = $('<tr></tr>').appendTo(thead);

		theadRow.append('<th>&nbsp;</th>');

		for (var i=0; i<orderedDaysOfTheWeek.length; i++) {
			theadRow.append('<th>' + orderedDaysOfTheWeek[i] + '</th>');
		}

		table.append(thead);
	}

	createTimeRows = function() {
		var time_start = stringToMoment(opts.time_start),
			time_end = stringToMoment(opts.time_end),
			counter = $.extend(true, {}, time_start),
			timeRow,
			timeHalfRow,
			halfTime,
			fullTime;

		tbody = $('<tbody></tbody>');

		while (moment(counter).isBefore(time_end)) {
			halfTime = counter.format('H:mm');
			halfTime = moment(counter, 'H:mm').add(30, 'minutes');
			fullTime = counter.format('H:mm');
			fullTime = moment(counter, 'H:mm').add(1, 'hours');

			timeRow = $('<tr></tr>').data('time_start', counter).data('time_end', halfTime);
			timeHalfRow = $('<tr></tr>').data('time_start', halfTime).data('time_end', fullTime);

			var timeHead = $('<th>' + counter.format(opts.time_format) + '</th>').appendTo(timeRow);
			var timeHalfHead = $('<th>&nbsp;</th>').appendTo(timeHalfRow);

			for (var i=0; i<orderedDaysOfTheWeek.length; i++) {
				createTimeCells(timeRow, timeHalfRow, i);
			}
			tbody.append(timeRow).append(timeHalfRow);

			counter = moment(counter).add(1, 'hours');
		}

		table.append(tbody);
		cells = table.find('td');
	}

	createTimeCells = function(row, halfRow, i){
		var selAvail = opts.selectedAvailability,
			rowtime_start = row.data('time_start'),
			rowtime_end = row.data('time_end'),
			halfRowtime_start = halfRow.data('time_start'),
			halfRowtime_end = halfRow.data('time_end'),
			currentDay = orderedDaysOfTheWeek[i],
			currentDayAvail = selAvail[currentDay],
			cellClass = '',
			halfCellClass = '';

		for (var j=0; j<selAvail[currentDay].length; j++) {
			var blocktime_start = stringToMoment(selAvail[currentDay][j].time_start);
			var blocktime_end = stringToMoment(selAvail[currentDay][j].time_end);

			if (moment(rowtime_start).isBetween(blocktime_start, blocktime_end) || moment(rowtime_end).isBetween(blocktime_start, blocktime_end) || rowtime_start.isSame(blocktime_start) || rowtime_end.isSame(blocktime_end)) {
				cellClass = 'selected';
			}
			if (moment(halfRowtime_start).isBetween(blocktime_start, blocktime_end) || moment(halfRowtime_end).isBetween(blocktime_start, blocktime_end) || halfRowtime_start.isSame(blocktime_start) || halfRowtime_end.isSame(blocktime_end)) {
				halfCellClass = 'selected';
			}
		}

		var timeCell = $('<td></td>', {'class': cellClass}).data('day', currentDay);
		var timeHalfCell = $('<td></td>', {'class': halfCellClass}).data('day', currentDay);

		row.append(timeCell);
		halfRow.append(timeHalfCell);
	};

	renderTable = function(container) {
		maybeCreateTable(container);
		table.empty();
		createDaysHeaders(table);
		createTimeRows(table);
		initSelection(container);
		container.trigger('avail.rendered');
	}

	/* ----------------------------------------- */
	/* Table Highlighting */
	/* ----------------------------------------- */

	var selectTo,
		initSelection;

	selectTo = function(cell) {
		var row = cell.parent(),
			cellIndex = cell.index(),
			rowIndex = row.index(),
			rowStart,
			rowEnd,
			cellStart,
			cellEnd;

		if (rowIndex < startRowIndex) {
			rowStart = rowIndex;
			rowEnd = startRowIndex;
		} else {
			rowStart = startRowIndex;
			rowEnd = rowIndex;
		}

		if (cellIndex < startCellIndex) {
			cellStart = cellIndex;
			cellEnd = startCellIndex;
		} else {
			cellStart = startCellIndex;
			cellEnd = cellIndex;
		}

		for (var i=rowStart; i<=rowEnd; i++) {
			var rowCells = tbody.find('tr').eq(i).find('td');
			for (var j=cellStart; j<=cellEnd; j++) {
				if (erasing) {
					rowCells.eq(j-1).removeClass('selected');
				} else {
					rowCells.eq(j-1).addClass('selected');
				}
			}
		}
	};

	initSelection = function(container){
		cells.on('mousedown', function(e){
			isMouseDown = true;
			var cell = $(this);

			if (cell.hasClass('selected')) {
				erasing = true;
				cell.removeClass('selected');
				table.addClass('erasing');
			} else {
				erasing = false;
				cell.addClass('selected');
				table.addClass('drawing');
			}
			startCellIndex = cell.index();
			startRowIndex = cell.parent().index();

			container.trigger('avail.selection.start');

			return false;
		})
		.on('mouseover', function(){
			if (!isMouseDown) return;
			$(this).addClass('active');
			selectTo($(this));
		})
		.on('mouseout', function(){
			$(this).removeClass('active');
		})
		.on('selectstart touchstart', function(){
			return false;
		});

		table.mouseup(function(e) {
			isMouseDown = false;
			table.removeClass('erasing drawing');
			container.trigger('avail.selection.end');
			updateSelectedAvailability(table);
		});
	};

	/* ----------------------------------------- */
	/* Update the Availability Data */
	/* ----------------------------------------- */

	var updateSelectedAvailability;

	updateSelectedAvailability = function(){

		var cellsPerRow = tbody.find('tr').first().find('td').length;
		var updatedAvail = {};

		for (var i=1; i<=cellsPerRow; i++) {
			var j = i+1;
			var column = tbody.find('td:nth-child(' + j + ')');
			var day = column.first().data('day');
			var isBlock = false;
			var blockCount = 0;

			updatedAvail[day] = [];

			column.each(function(idx){
				var $this = $(this);

				if ($this.hasClass('selected') && !isBlock) {
					updatedAvail[day].push({time_start: $this.parent('tr').data('time_start').format('H:mm')});
					if (column.length-1 === idx) {
						var blockObj = updatedAvail[day][blockCount];
						blockObj.time_end = $this.parent('tr').data('time_end').format('H:mm');
					}
					isBlock = true;
				} else if (!$this.hasClass('selected') && isBlock) {
					var blockObj = updatedAvail[day][blockCount];
					blockObj.time_end = $this.parent('tr').data('time_start').format('H:mm');
					isBlock = false;
					blockCount++;
				} else if ($this.hasClass('selected') && column.length-1 === idx ) {
					var blockObj = updatedAvail[day][blockCount];
					blockObj.time_end = $this.parent('tr').data('time_end').format('H:mm');
				}
			});
		}

		table.parent().trigger('avail.update', updatedAvail);
	};

	/* ----------------------------------------- */
	/* Availability plugin */
	/* ----------------------------------------- */
	$.fn.availability = function(options){
		var $container = this;

		opts = $.extend({}, $.fn.availability.defaults, options);

		function availabilityInit(){
			updateWeekOrder();
			$container.each(function(){
				var $this = $(this);
				renderTable($this);
				updateSelectedAvailability(table);
			});
		}

		availabilityInit();
	};

	$.fn.availability.defaults = {
		start_of_week: 'Monday',
		time_start: '9:00a',
		time_end: '6:00p',
		time_format: 'h:mm a',
		selectedAvailability: {
			'Monday': [],
			'Tuesday': [],
			'Wednesday': [],
			'Thursday': [],
			'Friday': [],
			'Saturday': [],
			'Sunday': []
		}
	};

}(jQuery, window, document));