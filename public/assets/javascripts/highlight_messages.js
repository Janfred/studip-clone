document.observe('dom:loaded', function() {
  $$(".effect_highlight").invoke('highlight', {startcolor:"#ffff99"});
});
