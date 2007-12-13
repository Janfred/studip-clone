document.observe('dom:loaded', function() {
  var indicator = $('ajax_notification');
  if (indicator) {
    Ajax.Responders.register({
      onCreate:   function(request) {
        if (Ajax.activeRequestCount) {
          request.usability_timer = setTimeout(function() {
            indicator.show();
          }, 100);
        }
      },
      onComplete: function(request) {
        clearTimeout(request.usability_timer);
        if (!Ajax.activeRequestCount) {
          indicator.hide();
        }
      }
    });
  }
});
