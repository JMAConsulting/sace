(function($, Drupal) {
   $(document).ready(function() {
console.log('a');
		$('.js-drupal-fullcalendar').fullCalendar({
      select: function(start, end) {
				var eventData = {
						start: start,
						end: end
					};
					$('.js-drupal-fullcalendar').fullCalendar('renderEvent', eventData, true);
			},
      slotDuration: '1:00',
      defaultView: 'agendaWeek',
      selectable: true,
			selectHelper: true,
      editable: true,
			eventLimit: true,
      slotEventOverlap: false,
      eventOverlap: false,
      minTime: '8:00:00',
      maxTime: '21:00:00',
      allDaySlot: false
    });
    });
})(jQuery, Drupal);
