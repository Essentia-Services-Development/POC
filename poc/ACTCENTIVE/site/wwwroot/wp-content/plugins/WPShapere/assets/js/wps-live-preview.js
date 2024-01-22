jQuery(document).ready(function(n) {
  
    "use strict";

    function e(e, a, u, r) {
        "normal" == e ? jQuery(a).css(u, r) : "hover" == e && n(a).hover(function() {
            n(this).css(u, r)
        }, function() {
            n(this).css(u, "")
        })
    }
    jQuery("input.wp-color-picker").each(function() {
        var n = jQuery(this).attr("id"),
            a = "",
            u = "",
            r = "";
        if ("bg_color" == n) var a = "body.wp-admin div#wpwrap",
            u = "background",
            r = "normal";
        else if ("nav_wrap_color" == n) var a = "#adminmenuback, #adminmenuwrap, #adminmenu",
            u = "background-color",
            r = "normal";
        else if ("sub_nav_wrap_color" == n) var a = "#adminmenu .wp-has-current-submenu .wp-submenu, .no-js li.wp-has-current-submenu:hover .wp-submenu, #adminmenu a.wp-has-current-submenu:focus+.wp-submenu, #adminmenu .wp-has-current-submenu .wp-submenu.sub-open, #adminmenu .wp-has-current-submenu.opensub .wp-submenu, #adminmenu .wp-not-current-submenu .wp-submenu, .folded #adminmenu .wp-has-current-submenu .wp-submenu",
            u = "background-color",
            r = "normal";
        else if ("hover_menu_color" == n) var a = "#adminmenu li.menu-top, #adminmenu li.menu-top a, #adminmenu li.opensub>a.menu-top, #adminmenu li>a.menu-top",
            u = "background-color",
            r = "hover";
        else if ("active_menu_color" == n) var a = "#adminmenu li.wp-has-current-submenu a.wp-has-current-submenu, #adminmenu li.current a.menu-top, .folded #adminmenu li.wp-has-current-submenu, .folded #adminmenu li.current.menu-top, #adminmenu .wp-menu-arrow, #adminmenu .wp-has-current-submenu .wp-submenu .wp-submenu-head, #adminmenu .wp-menu-arrow div",
            u = "background-color",
            r = "normal";
        else if ("nav_text_color" == n) var a = "#adminmenu div.wp-menu-image:before, #adminmenu a, #adminmenu .wp-submenu a, #collapse-menu, #collapse-button div:after",
            u = "color",
            r = "normal";
        else if ("menu_hover_text_color" == n) var a = "#adminmenu li.menu-top, #adminmenu li.menu-top a, #adminmenu li.opensub>a.menu-top, #adminmenu li>a.menu-top,#adminmenu li div.wp-menu-image:before",
            u = "color",
            r = "hover";
        else if ("admin_bar_color" == n) var a = "#wpadminbar, #wpadminbar .menupop .ab-sub-wrapper",
            u = "background-color",
            r = "normal";
        else if ("logo_bg_color" == n) var a = "div#wpadminbar li#wp-admin-bar-wat_site_title",
            u = "background-color",
            r = "normal";
        else if ("admin_bar_menu_color" == n) var a = "#wpadminbar a.ab-item, #wpadminbar>#wp-toolbar span.ab-label, #wpadminbar>#wp-toolbar span.noticon",
            u = "color",
            r = "normal";
        else if ("admin_bar_menu_hover_color" == n) var a = "#wpadminbar a, #wpadminbar ul li a, #wpadminbar a.ab-item, #wpadminbar>#wp-toolbar span.ab-label, #wpadminbar>#wp-toolbar span.noticon",
            u = "color",
            r = "hover";
        else if ("menu_active_text_color" == n) var a = "#adminmenu li.wp-has-current-submenu > a.menu-top",
            u = "color",
            r = "normal";
        else if ("sub_nav_hover_color" == n) var a = "#adminmenu li.menu-top .wp-submenu a",
            u = "background-color",
            r = "hover";
        else if ("sub_nav_text_color" == n) var a = "#adminmenu .wp-submenu a, #adminmenu li.menu-top .wp-submenu a",
            u = "color",
            r = "normal";
        else if ("sub_nav_hover_text_color" == n) var a = "#adminmenu li.menu-top .wp-submenu a",
            u = "color",
            r = "hover";
        else if ("submenu_active_text_color" == n) var a = "#adminmenu li.menu-top .wp-submenu li.current a",
            u = "color",
            r = "normal";
        jQuery(this).wpColorPicker({
            change: function() {
                var n = jQuery(this).wpColorPicker("color");
                e(r, a, u, n)
            }
        })
    })

    jQuery("#aof-number-admin_par_menu_v_padding").on("change mousemove", function() {
      var vpadding = jQuery("#admin_par_menu_v_padding").val();
      if(vpadding) {
        jQuery("#adminmenu .wp-submenu-head, #adminmenu a.menu-top").css({"padding-top": vpadding + 'px', "padding-bottom": vpadding + 'px'});
      }
    });

});
