'use strict';

(function ($) {
	let doc = $( document );

	doc.ready( function () {
		var now = new Date(),
			currentMonth = now.getMonth(),
			currentYear = now.getFullYear();

		displayCalendar( currentYear, currentMonth );

		$( "#prev_month" ).click( function () {
			currentMonth--;
			if ( currentMonth === -1 )
			{
				currentMonth = 11;
				currentYear--;
			}

			displayCalendar( currentYear, currentMonth );
		} );

		$( "#next_month" ).click( function () {
			currentMonth++;
			if ( currentMonth === 12 )
			{
				currentMonth = 0;
				currentYear++;
			}

			displayCalendar( currentYear, currentMonth );
		} );

		$( ".plan_posts_list" ).on( 'click', '.remove_plan', function () {
			var scheduleId = $( this ).closest( '.plan_box' ).data( 'schedule-id' );

			FSPoster.confirm( 'Do you want to remove this schedule?<br>Note: if you remove this schedule then all planned posts also will be stopped automatically.', function () {
				FSPoster.ajax( 'delete_schedule', { 'id': scheduleId }, function () {
					displayCalendar( currentYear, currentMonth );
				} );
			} );

		} );

		$( "#calendar_area" ).on( 'click', '.days[data-count]', function () {
			var date = $( this ).attr( 'data-date' );

			$( ".plan_posts_list > .plan_box:not([data-date=\"" + date + "\"])" ).slideUp( 200 );
			$( ".plan_posts_list > .plan_box[data-date=\"" + date + "\"]" ).slideDown( 200 );

			$( "#calendar_area .dayNow" ).removeClass( 'dayNow' );
			$( this ).addClass( 'dayNow' );
		} );
	} );
})( jQuery );

function displayCalendar (_year, _month)
{
	if ( typeof $ === 'undefined' )
	{
		var $ = typeof jQuery === 'undefined' ? null : jQuery;
	}

	FSPoster.ajax( 'schedule_get_calendar', { 'month': _month + 1, 'year': _year }, function (result) {
		var scheduleCountsByDay = {};

		if ( result[ 'days' ].length > 0 )
		{
			$( '.fsp-calendar-emptiness' ).addClass( 'fsp-hide' );
		}
		else
		{
			$( '.fsp-calendar-emptiness' ).removeClass( 'fsp-hide' );
		}

		$( ".plan_posts_list" ).html( '' );

		for ( var date in result[ 'days' ] )
		{
			var tInfo = result[ 'days' ][ date ],
				//replaceAll - js bug, dd-dd-dddd => (dd-1)-dd-dddd
				day = (new Date( tInfo[ 'date' ].replaceAll('-', '\/') )).getDate();

			if ( ! (day in scheduleCountsByDay) )
			{
				scheduleCountsByDay[ day ] = 0;
			}
			scheduleCountsByDay[ day ]++;

			$( ".plan_posts_list" ).append(
				`<div class="fsp-calendar-post plan_box" data-schedule-id="${ tInfo[ 'id' ] }" data-date="${ tInfo[ 'date' ] }">
					<div class="fsp-calendar-post-details">
						<div class="fsp-calendar-post-text">
							<i class="fa fa-thumbtack fa-thumb-tack"></i><span>${ tInfo[ 'title' ] }</span><a ${ tInfo[ 'post_id' ] > 0 ? `href="${ fspConfig.siteURL }/?p=${ tInfo[ 'post_id' ] }" target="_blank"` : '' } class="fsp-tooltip" data-title="${ tInfo[ 'post_data' ] }" ><i class="fas fa-external-link-alt"></i></a>
						</div>
						<div class="fsp-calendar-post-subtext">
							<span>
								<i class="far fa-calendar-alt"></i> ${ tInfo[ 'date' ] }
							</span>
							<span>
								<i class="far fa-clock"></i> ${ tInfo[ 'time' ] }
							</span>
						</div>
					</div>
					<div class="fsp-calendar-post-controls">
						<i class="far fa-trash-alt fsp-tooltip fsp-icon-button remove_plan" data-title="Remove plan"></i>
					</div>
				</div>` );
		}

		$( ".plan_posts_list > .plan_box" ).hide();

		var htmlContent = "",
			febNumberOfDays = "",
			counter = 1,
			dateNow = new Date( _year, _month ),
			month = dateNow.getMonth() + 1,
			year = dateNow.getFullYear(),
			currentDate = new Date();

		if ( month === 2 )
		{
			febNumberOfDays = ((year % 100 !== 0) && (year % 4 === 0) || (year % 400 === 0)) ? '29' : '28';
		}

		let monthNames = [ null, 'January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December' ];
		let dayPerMonth = [ null, '31', febNumberOfDays, '31', '30', '31', '30', '31', '31', '30', '31', '30', '31' ]
		let nextDate = new Date( year, month - 1, 1 );
		let weekdays = nextDate.getDay();
		let weekdays2 = weekdays === 0 ? 7 : weekdays;
		let numOfDays = dayPerMonth[ month ];

		for ( var w = 1; w < weekdays2; w++ )
		{
			htmlContent += "<td class='monthPre'></td>";
		}

		while ( counter <= numOfDays )
		{
			if ( weekdays2 > 7 )
			{
				weekdays2 = 1;
				htmlContent += "</tr><tr>";
			}

			var addClass = counter === currentDate.getDate() && month === (currentDate.getMonth() + 1) && year === currentDate.getFullYear() ? ' dayNow' : '';

			htmlContent += "<td class='days" + addClass + "'" + (counter in scheduleCountsByDay ? ' data-count="' + scheduleCountsByDay[ counter ] + '"' : '') + " data-date=\"" + (year + '-' + FSPoster.zeroPad( month ) + '-' + FSPoster.zeroPad( counter )) + "\"><span>" + counter + "</span></td>";

			weekdays2++;
			counter++;
		}

		$( "#calendar_area" ).html( `<table class="calendar">
			<tr class="yearMonthHead">
				<th colspan="7">${ monthNames[ month ] } ${ year }</th>
			</tr>
			<tr class="dayNames">
				<td>${ fsp__( 'Mon' ) }</td>
				<td>${ fsp__( 'Tue' ) }</td>
				<td>${ fsp__( 'Wed' ) }</td>
				<td>${ fsp__( 'Thu' ) }</td>
				<td>${ fsp__( 'Fri' ) }</td>
				<td>${ fsp__( 'Sat' ) }</td>
				<td>${ fsp__( 'Sun' ) }</td>
			</tr>
			<tr>${ htmlContent }</tr>
		</table>` );

		$( "#calendar_area .days[data-count]:first" ).trigger( 'click' );
	} );
}
