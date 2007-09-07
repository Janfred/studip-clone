Event.observe(window, 'load', function() {
  var indicator = $('ajax_notification');
  if (indicator) {
    Ajax.Responders.register({
      onCreate:   function() {
        if (Ajax.activeRequestCount) {
          indicator.show();
        }
      },
      onComplete: function() {
        if (!Ajax.activeRequestCount) {
          indicator.hide();
        }
      }
    });
  }
});