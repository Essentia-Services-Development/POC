(function($) {

  "use strict";
  
  $('.login input.input').each(function() {
    label = $(this).parent().children("label").text();
    $(this).attr("placeholder", label);
    $(this).insertBefore($(this).parent());
    $(this).next().remove();
  });

  $('.user-pass-wrap').each(function() {
    label = $(this).children("label").text();
    $(this).children("input").attr("placeholder", label);
    $(this).children("label").remove();
  });

  $('.user-pass1-wrap').each(function() {
    label = $(this).children("label").text();
    $(this).children("input#pass1").attr("placeholder", label);
    $(this).children("label").remove();
  });

})(jQuery);
