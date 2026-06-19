/**
* 2007-2023 Helloshop
*
* Tracking Center
*
*  @author    Helloshop <modules@helloshop.com>
*  @copyright 2007-2023 Helloshop
*  @license   license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
*  @Website: http://www.Helloshop.com
*/

// var key = false;
// var active = false;
// console.log(active);
// var range = false;
// var filter = function() {
//     $('.delay').empty();
//     $('#fieldset_delivered_1 .loading').show();
//     $.ajax({
//         type: 'POST',
//         url: url_ajax,
//         dataType: 'json',
//         data: {
//             controller : 'AdmindeliveryorderautoupdateAjax',
//             action : 'delayFilter',
//             ajax : true,
//             key: key,
//             range: range,
//             secure_key: secure_key,
//         },
//         success: function(data)
//         {
//             setDelayContent(data);
//         }
//     });
// }
// $(document).ready(function() {
//     key = 'month';
//     active = $('.filter[filter="month"]').addClass('active');
//     $('.date-range .filter').click(function() {
//         if (active) {
//             active.removeClass('active');
//         }
//         if ($(this).is(active)) {
//             active = false;
//             key = false;
//         } else {
//             active = $(this);
//             active.addClass('active');
//             key = active.attr('filter');
//         }
//         filter();
//     })
//     $('input[name="date_range"]').daterangepicker();
//     $('input[name="date_range"]').on('apply.daterangepicker', function(ev, picker) {
//         range = {
//             start: picker.startDate.format('YYYY-MM-DD'),
//             end: picker.endDate.format('YYYY-MM-DD')
//         };
//         if (active) {
//             active.removeClass('active');
//             active = false;
//             key = false;
//         }
//         key = 'range';
//         filter();
//     });



// })