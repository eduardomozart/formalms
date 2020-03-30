import { Calendar } from '@fullcalendar/core';
import itLocale from '@fullcalendar/core/locales/it';
import dayGridPlugin from '@fullcalendar/daygrid';

export const RenderDashBoardCalendar = () => {
  const els = document.querySelectorAll('.js-dashboard-calendar')

  if (els.length) {
    for (let i = 0; i < els.length; i++) {
      /* const actions = els[i].getAttribute('data-action');
      const actionsArray = actions.split(',');
      const sourcesArray = [];
      let event = {};

       for (i = 0; i < actionsArray.length; i++) {
        if (actionsArray[i] == 'getElearningCalendar') {

        } else if (actionsArray[i] == 'getClassroomCalendar') {

        } else if (actionsArray[i] == 'getReservationCalendar') {

        }
      }*/


      const calendar = new Calendar(els[i], {
        plugins: [dayGridPlugin],
        locale: itLocale,
        height: 'auto',
        eventSources: [
          {
            events: (fetchInfo, successCallback, failureCallback) => {
              $.ajax({
                type: 'post',
                url: window.dashboardCalendarAjaxUrl,
                data: {
                  blockAction: 'getElearningCalendar',
                  authentic_request: window.dashboardCalendarAjaxSignature,
                  block: 'DashboardBlockCalendarLms'
                },
                success: function (data) {
                  const parsedData = JSON.parse(data);
                  successCallback(
                      parsedData.response.map((item) => {
                        return {
                          title: item.title,
                          start: item.startDate,
                          type: item.type,
                          status: item.status,
                          description: item.description,
                          hours: item.hours
                        }
                      })
                  )
                },
                error: function (e) {
                  failureCallback(
                      () => console.log(e)
                  )
                }
              });
            },
            color: '#A478EA'
          },
          {
            events: (fetchInfo, successCallback, failureCallback) => {
              $.ajax({
                type: 'post',
                url: window.dashboardCalendarAjaxUrl,
                data: {
                  blockAction: 'getClassroomCalendar',
                  authentic_request: window.dashboardCalendarAjaxSignature,
                  block: 'DashboardBlockCalendarLms'
                },
                success: function (data) {
                  const parsedData = JSON.parse(data);

                  successCallback(
                      parsedData.response.map((item) => {
                        return {
                          title: item.title,
                          start: item.startDate,
                          type: item.type,
                          status: item.status,
                          description: item.description,
                          hours: item.hours
                        }
                      })
                  )
                },
                error: function (e) {
                  failureCallback(
                      () => console.log(e)
                  )
                }
              });
            },
            color: '#007CC8'
          },
          {
            events: (fetchInfo, successCallback, failureCallback) => {
              $.ajax({
                type: 'post',
                url: window.dashboardCalendarAjaxUrl,
                data: {
                  blockAction: 'getReservationCalendar',
                  authentic_request: window.dashboardCalendarAjaxSignature,
                  block: 'DashboardBlockCalendarLms'
                },
                success: function (data) {
                  const parsedData = JSON.parse(data);

                  successCallback(
                      parsedData.response.map((item) => {
                        return {
                          title: item.title,
                          start: item.startDate,
                          type: item.type,
                          status: item.status,
                          description: item.description,
                          hours: item.hours
                        }
                      })
                  )
                },
                error: function (e) {
                  failureCallback(
                      () => console.log(e)
                  )
                }
              });
            },
            color: '#007CC8'
          },
        ],
        eventClick: function() {
          // renderPopup(event);
          // console.log(item)
        },
        eventRender: function(item) {
          // console.log(item);
          if (item.event.extendedProps.status) {
            item.el.classList.add('is-open');
          } else {
            item.el.classList.add('is-closed');
          }
          renderPopup(item);
        }
      });

      calendar.render();
    }
  }

}

const renderPopup = (item) => {
  let el = '';
  const type = item.event.extendedProps.type === 'classroom' ? 'classroom' : 'elearning';
  const desc = item.event.extendedProps.description;
  const hours = item.event.extendedProps.hours;
  const title = item.event.title;
  // console.log(item.event)

  el += '<div class="d-popup">';
  el += '<div class="d-popup__item is-' + type + '">';
  el += '<div class="d-popup__title">' + title + '</div>';
  el += '<div class="d-popup__type">' + type + '</div>';
  el += '<div class="d-popup__desc">' + desc + '</div>';
  el += '<div class="d-popup__hours">' + hours + '</div>';
  el += '<div class="d-popup__triangle"></div>';
  el += '</div>';
  el += '</div>';

  $(item.el).append(el);
}
