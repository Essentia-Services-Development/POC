(function( $ ) {
  "use strict";
  //Show content after page load
  $('.loading_form_text').remove();
  $('#aof_options_framework').fadeIn();

  //Vertical Tab
  $('#aof_options_tab').aofOptionsTabs({
      type: 'vertical', //Types: default, vertical, accordion
      width: 'auto', //auto or any width like 600px
      fit: true, // 100% fit in a container
      closed: 'accordion', // Start closed if in accordion view
      tabidentify: 'hor_1', // The tab groups identifier
      activetab_bg: '#1098F7',
      inactive_bg: 'transparent',
      activate: function(event) { // Callback function if tab is switched
      }
  });
  $(function() {
      $('.aof-wpcolor').wpColorPicker();
  });
  //remove preview image
  $('.aof-image-preview').on('click', '.img-remove', function( event ){
        event.preventDefault();
        var id = $(this).closest("div").attr("id");
        $("#" + id).find(" .imgpreview_" + id).remove();
        $("#" + id).find(" i.img-remove").remove();
        $("#" + id).find(" .aof_image_url").val("");
        $(this).find().remove();
        return false;
    });

  // Uploading files
  var file_frame;

    $('.aof-image-preview').on('click', function( event ){
      event.preventDefault();
      var divid = $(this).attr("id");
      //alert(divid);
      // Create the media frame.
      file_frame = wp.media.frames.file_frame = wp.media({
      title: 'Select Image',
      button: {
          text: 'Choose'
      },
      multiple: false  // Set to true to allow multiple files to be selected
      });

      // When an image is selected, run a callback.
      file_frame.on( 'select', function() {
          var selection = file_frame.state().get('selection');

          selection.map( function( attachment ) {
              //alert(divid);
              attachment = attachment.toJSON();
              $("#" + divid + " .imgpreview_" + divid).remove();
              $("#" + divid).find(" i.img-remove").remove();
              $("#" + divid).append('<i class="dashicons dashicons-no-alt img-remove"></i><img class="imgpreview_' + divid + '" src="' + attachment.url + '" />');
              $("#" + divid + " .aof_image_url").val(attachment.url);
          });

      });

      // Finally, open the modal
      file_frame.open();
    });

    $('.aof_font_family').on('change', function() {
        var selected = $(':selected', this);
        var font_type = $(selected).parent().attr('class');
      //var selected = $(this.options[this.selectedIndex]).closest('optgroup').attr('class');
      $(this).next('input.aof-font-type').val(font_type);
    });

    $('.aof-number').each(function() {
        if ( !$(this).prev().is('.aof-number-slider') ) {
                 return;
          }
      $(this).prev().slider({
              range: "min",
              min: Number( $(this).attr('min') ),
              max: Number( $(this).attr('max') ),
              value: Number( $(this).val() ),
              animate: "fast",
              slide: function (event, ui) {
                  $(ui.handle).parent().next().val(ui.value).trigger('change');
              },
              change: function (event, ui) {
                  $(ui.handle).parent().next().val(ui.value).trigger('change');
              },
         });
    });
    $(".aof-number").bind('input propertychange', function() {
        $(this).prev().slider('option', 'value', $(this).val());
    });
    $(".aof-image-select").imagepicker({show_label: true});

})( jQuery );
