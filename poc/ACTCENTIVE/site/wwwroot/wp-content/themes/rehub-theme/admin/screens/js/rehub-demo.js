 jQuery(document).ready(function(e) {
   	jQuery('.spoil-re .re-show-hide').on("click", function() {
      jQuery(this).parent().find('.open-re-onclk').slideToggle();
    });
});