jQuery(function() {

    "use strict";

    var j = 0;

    var oldlistslug, newlistslug, newpos, oldpos, oldeleid, neweleid, parentslug;
    admin_menu_reorder();
    function admin_menu_reorder() {
        jQuery(".topmenu, .submenu").sortable({
            connectWith: ".sortUls",
            placeholder: "sortable-placeholder",
            beforeStop: function(event, ui) {
              var chk = ui.item.hasClass('ui-state-disabled');
              if (chk === true) {
                  if (typeof (ui.item.parents("li").attr('id')) == "undefined") {
                      jQuery(this).sortable('cancel');
                  } else if(jQuery(this).parents('li').attr('id') != jQuery('#'+ui.item.attr('id')).parents("li").attr('id')) {
                      jQuery(this).sortable('cancel');
                  }
              }
            },
            start: function(event, ui) {
               oldlistslug = ui.item.attr('id');
               oldeleid = ui.item.parent().attr('id');
               oldpos = ui.item.index();
            },
            update: function(event, ui) {
               if(ui.sender) {
                    newlistslug = ui.item.attr('id');
                    neweleid = ui.item.parent().attr('id');
                    newpos = ui.item.index();
                   // parentslug = ui.item.parent().parent().attr('id');
                    parentslug = ui.item.parents("li").attr('id');

                    if(typeof parentslug == "undefined" ) {
                        //alert('custom_admin_menu['+neweleid+'][]');
                        slugname = jQuery('#input-'+oldlistslug).val();
                        jQuery('#input-'+oldlistslug).attr('name', 'custom_admin_menu['+neweleid+'][]');
                        jQuery('#customtitle-'+oldlistslug).attr('name', 'custom_admin_menu['+neweleid+'_title]['+slugname+']');
                    } else if (typeof parentslug != "undefined") {
                        slugname = jQuery('#input-'+parentslug).val();
                        currentslug = jQuery('#input-'+oldlistslug).val();
                        //$("#header ul").append('<li><a href="/user/messages"><span class="tab">Message Center</span></a></li>');
                       // alert('custom_admin_menu['+neweleid+']['+slugname+'][]')
                        jQuery('#input-'+oldlistslug).attr('name', 'custom_admin_menu['+neweleid+']['+slugname+'][]');
                        jQuery('#customtitle-'+oldlistslug).attr('name', 'custom_admin_menu['+neweleid+'_title]['+slugname+']['+currentslug+']');
                        var childli = jQuery('#'+ui.item.attr('id') + ' ol').find('li input[type=hidden]');
                        if(childli.length > 0) {
                            jQuery(childli).each(function(i) {
                                var subids = jQuery(this).parents('li').attr('id');
                                var subslug = jQuery(this).val();
                                var content = jQuery(this).parents('li')[0];
                                jQuery(this).attr('name', 'custom_admin_menu['+neweleid+']['+slugname+'][]');
                                jQuery('#customtitle-'+subids).attr('name', 'custom_admin_menu['+neweleid+'_title]['+slugname+']['+subslug+']');

                                console.log(jQuery(this).parents('li')[0]);
                                //jQuery('#sub_menu ol.menu_child_15').append(jQuery(this).parents('li'));
                                //jQuery('#'+ui.item.parents('li').attr('id') + ' ol.menu_child_15').append(jQuery(this).parents('li'));
                                jQuery('#'+ui.item.parents('li').attr('id') + ' ol').append(content);
                                jQuery(this).parent().remove();
                                subslug = ""; subids = ""; content ="";
                            });
                        }
                    }
                }
            }
        });

        jQuery(".submenu").sortable({
            connectWith: ".subsortUls",
            placeholder: "sortable-placeholder"
        });
    }


   jQuery(".topmenu", ".submenu").disableSelection();

   jQuery("a.admin_menu_edit").on('click', function(e) {
       e.preventDefault();
       var click_id = jQuery(this).attr('id');
           jQuery('#menu_edit_' + click_id).slideToggle('fast');
     });

     jQuery("a.disclose").on('click', function(e) {
       e.preventDefault();
       var disclose_id = jQuery(this).attr('id');
        jQuery('ol#child_' + disclose_id).slideToggle('fast');
        jQuery(this).toggleClass('plus').toggleClass('minus');
     });

    jQuery("a.alter-edit-expand").on('click', function(e) {
         e.preventDefault();
         jQuery(this).next('.alter-menu-contents').slideToggle('fast');
     });

     jQuery(".select_all").on('click', function(e) {
         jQuery("#" + jQuery(this).attr('rel') + " INPUT[type='checkbox']").attr('checked', true);
         return false;
     });

     jQuery(".select_none").on('click', function(e) {
          jQuery("#" + jQuery(this).attr('rel') + " INPUT[type='checkbox']").attr('checked', false);
          return false;
      });
});
