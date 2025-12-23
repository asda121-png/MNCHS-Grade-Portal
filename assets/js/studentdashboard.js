document.addEventListener('DOMContentLoaded', function() {
    var calendarEl = document.getElementById('calendar');
    
    if (calendarEl) {
        var calendar = new FullCalendar.Calendar(calendarEl, {
            initialView: 'dayGridMonth',
            headerToolbar: {
                left: 'prev,next today',
                center: 'title',
                right: 'dayGridMonth,listMonth'
            },
            googleCalendarApiKey: window.GOOGLE_API_KEY || '',
            eventSources: [
                {
                    googleCalendarId: 'en.philippines#holiday@group.v.calendar.google.com',
                    className: 'gcal-event',
                    color: '#d32f2f'
                },
                {
                    url: '../../server/api/events.php?action=get',
                    failure: function() {
                        console.log('Could not fetch custom events (might be restricted or empty).');
                    },
                    color: '#800000'
                }
            ],
            height: 'auto'
        });
        calendar.render();
    }
});