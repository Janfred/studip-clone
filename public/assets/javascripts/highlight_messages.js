Event.observe(window, 'load', function() {
  document.getElementsByClassName("effect_highlight").each(
    function(e) { new Effect.Highlight(e); }
  );
});