$(document).ready(function(){

  /* put the status popin at right place */
  var first_alert_item = $('table#alert tr.alert-item:first');
  if (first_alert_item.length) {
    popup.css('top', (first_alert_item.offset().top - 10) + 'px');
  }
  
  /* add the status popin to document */
  $('body').append(popup);
});

