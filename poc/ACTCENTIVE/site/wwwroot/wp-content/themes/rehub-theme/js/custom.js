/*
   Author: Igor Sunzharovskyi
   Author URI: https://wpsoul.com
*/

function reshowNav(){'use strict'; jQuery(this).addClass('hovered'); }
function rehideNav(){'use strict'; jQuery(this).removeClass('hovered');}
/*Throttle option*/
(function(b,c){var $=b.jQuery||b.Cowboy||(b.Cowboy={}),a;$.throttle=a=function(e,f,j,i){var h,d=0;if(typeof f!=="boolean"){i=j;j=f;f=c}function g(){var o=this,m=+new Date()-d,n=arguments;function l(){d=+new Date();j.apply(o,n)}function k(){h=c}if(i&&!h){l()}h&&clearTimeout(h);if(i===c&&m>e){l()}else{if(f!==true){h=setTimeout(i?k:l,i===c?e-m:e)}}}if($.guid){g.guid=j.guid=j.guid||$.guid++}return g};$.debounce=function(d,e,f){return f===c?a(d,e,false):a(d,f,e!==false)}})(this);


var re_main_search = {

    init: function init() {

        // Search icon show/hide
        jQuery(document).on( 'click', '.icon-search-onclick', function(e) {
            e.preventDefault();
            e.stopPropagation();  
            document.getElementById('rhSplashSearch').classList.toggle( 'top-search-onclick-open' );
            document.getElementById('rhSplashSearch').classList.toggle( 'css-ani-trigger' );
            setTimeout(function() {
                document.getElementById('rhSplashSearch').querySelector('input[type=text]').focus();
            }, 600);
            
        });
  
        jQuery(document).on("click", function(e){ 
            if( jQuery(e.target).closest(".head_search").length || jQuery(e.target).closest(".custom_search_box").length) 
                return;
            jQuery( '.re-aj-search-wrap' ).removeClass( 're-aj-search-open' ).empty();
            e.stopPropagation();           
        });

        jQuery(document).on( 'click', '#close-src-splash', function(e) {
            document.getElementById('rhSplashSearch').classList.remove('top-search-onclick-open');  
            document.getElementById('rhSplashSearch').classList.remove('css-ani-trigger'); 
            document.getElementById('rhSplashSearch').querySelector('input[type=text]').blur();       
        });        

    }
};

//MOBILE MENU AND MEGAMENU
var NavOverlayRemoved = true;
var revMenuStyle = function() { 
    var menu = jQuery('#rhslidingMenu'),
        openMenu = jQuery('.dl-trigger'),
        navMenu = jQuery('#slide-menu-mobile'),
        menuList = jQuery('#slide-menu-mobile > .menu'),
        subMenu = menu.find('.sub-menu'),
        mobilecustomheader = jQuery('#rhmobpnlcustom'),
        mobsidebar = jQuery('#rh_woo_mbl_sidebar'),
        mobsidebartrigger = jQuery('#mobile-trigger-sidebar');
    menuList.addClass('off-canvas');
    if (menuList.find('.close-menu').length === 0) {
        menuList.append('<li class="close-menu rh-close-btn position-relative text-center cursorpointer rh-circular-hover mt10 mb10 margincenter"><span><i class="rhicon rhi-times whitebg roundborder50p rh-shadow4 abdposleft" aria-hidden="true"></i></span></li>');
    }
    if(mobilecustomheader.length > 0){
        menuList.prepend('<li id="mobtopheaderpnl">'+mobilecustomheader.html()+'</li>');
    }
    jQuery('#slide-menu-mobile .menu-item-has-children').children('a').after('<span class="submenu-toggle text-center cursorpointer blackcolor"><i class="rhicon rhi-angle-right"></i></span>');
    jQuery('#slide-menu-mobile .menu-item-has-children:not(.rh-mobile-linkable)').children('a').addClass('submenu-toggle');
    menuList.on('click', '.submenu-toggle', function(evt) {
        evt.preventDefault();
        jQuery(this)
            .siblings('.sub-menu')
            .addClass('sub-menu-active');
    });
    subMenu.each(function() {
        var $this = jQuery(this);
        if ($this.find('.back-mb').length === 0) {
            $this.prepend('<li class="back-mb"><span class="rehub-main-color"><i class="rhicon rhi-chevron-left mr10"></i> '+rhscriptvars.back+'</span></li>');
        }
        menu.on('click', '.back-mb span', function(evt) {
            evt.preventDefault();
            jQuery(this)
                .parent()
                .parent()
                .removeClass('sub-menu-active');
        });
    });
    openMenu.on('click', function(e) {
        e.preventDefault();
        e.stopPropagation();        
        jQuery('#wpadminbar').css('z-index', '999');
        //document.querySelector('.rh-outer-wrap').classList.add('rh-outer-wrap-move');
        navMenu.fadeIn(100);
        menuList.addClass('off-canvas-active');
        jQuery(this).addClass('toggle-active');                
        if(NavOverlayRemoved){
            jQuery('body').append(jQuery('<div class="offsetnav-overlay"></div>').hide().fadeIn());
            NavOverlayRemoved = false;
        }
    });
    mobsidebartrigger.on('click', function(e) {
        e.preventDefault();
        e.stopPropagation();        
        mobsidebar.toggleClass('activeslide');
        if(NavOverlayRemoved){
            jQuery('body').append(jQuery('<div class="offsetnav-overlay"></div>').hide().fadeIn());
            NavOverlayRemoved = false;
        }       
    });    
    jQuery(document).on('click touchstart', '.close-menu, .offsetnav-overlay', function(event) {
        //event.preventDefault();
        event.stopPropagation();        
        setTimeout(function(){ 
            //document.querySelector('.rh-outer-wrap').classList.remove('rh-outer-wrap-move');
            menuList.removeClass('off-canvas-active');
            openMenu.removeClass('toggle-active');
            jQuery('.sub-menu').removeClass('sub-menu-active'); 
            mobsidebar.removeClass('activeslide');               
            if(!NavOverlayRemoved){
                jQuery('.offsetnav-overlay').remove();
                NavOverlayRemoved = true;
            } 
            return false;           
        }, 100);                      
    });
}

jQuery(document).ready(function($) {
   'use strict';

    /* better alerts*/
    (function(){$.simplyToast=function(e,t,n){function u(){$.simplyToast.remove(o)}n=$.extend(true,{},$.simplyToast.defaultOptions,n);var r='<div class="simply-toast rh-toast rh-toast-'+(t?t:n.type)+" "+(n.customClass?n.customClass:"")+'">';if(n.allowDismiss)r+='<span class="rh-toast-close" data-dismiss="alert">&times;</span>';r+=e;r+="</div>";var i=n.offset.amount;$(".simply-toast").each(function(){return i=Math.max(i,parseInt($(this).css(n.offset.from))+this.offsetHeight+n.spacing)});var s={position:n.appendTo==="body"?"fixed":"absolute",margin:0,"z-index":"999999",display:"none","min-width":n.minWidth,"max-width":n.maxWidth};s[n.offset.from]=i+"px";var o=$(r).css(s).appendTo(n.appendTo);switch(n.align){case"center":o.css({left:"50%","margin-left":"-"+o.outerWidth()/2+"px"});break;case"left":o.css("left","20px");break;default:o.css("right","20px")}if(o.fadeIn)o.fadeIn();else o.css({display:"block",opacity:1});if(n.delay>0){setTimeout(u,n.delay)}o.find('[data-dismiss="alert"]').removeAttr("data-dismiss").click(u);return o};$.simplyToast.remove=function(e){if(e.fadeOut){return e.fadeOut(function(){return e.remove()})}else{return e.remove()}};$.simplyToast.defaultOptions={appendTo:"body",customClass:false,type:"info",offset:{from:"top",amount:20},align:"right",minWidth:250,maxWidth:450,delay:4e3,allowDismiss:true,spacing:10}})();          

    // Header and menu
    var res_nav = $(".top_menu").html();   
    $("#slide-menu-mobile").html(res_nav);
    if ($('#re_menu_near_logo').length > 0) { 
        var header_responsive_menu = $("#re_menu_near_logo ul").html();
        $("#slide-menu-mobile ul.menu").append(header_responsive_menu);
    } 
    if ($('#main_header .top-nav ul.menu').length > 0) { 
        var header_top_menu_add = $("#main_header .top-nav ul.menu").html();
        $("#slide-menu-mobile ul.menu").append(header_top_menu_add);
    }  
    if ($('#main_header .top_custom_content').length > 0) { 
        var header_top_menu_add = $("#main_header .top_custom_content").html();
        $("#slide-menu-mobile ul.menu").append('<li><div class="pt15 pb15 pl15 pr15 top_custom_content_mobile font80">'+header_top_menu_add+'</div></li>');
    }   
    const rhtoolicons = document.getElementById("rhNavToolbar"); 
    const rhtooldiv = "<div class='rh-flex-center-align rh-flex-grow1 rh-flex-justify-center'></div>";   
    if ($('.rh_woocartmenu_cell').length > 0) { 
        let prep = $(".rh_woocartmenu_cell").html();
        if(rhtoolicons){
            rhtoolicons.insertAdjacentHTML('afterbegin', rhtooldiv.replace("div", "div id='rhWoocartTool'"));
            $( "#rhWoocartTool" ).prepend(prep);
        }else{
            $( "#main_header .responsive_nav_wrap #mobile-menu-icons" ).append(prep);
        }
    } 
    const mobileinmenu = document.querySelectorAll('.mobileinmenu');
    if(mobileinmenu.length){
        if(rhtoolicons){
            mobileinmenu.forEach((item, index)=>{
                let clone = item.cloneNode(true);
                let toolwrap = rhtooldiv.replace("div", "div id='rhToolicon"+index+"'");
                rhtoolicons.insertAdjacentHTML('afterbegin', toolwrap);
                document.getElementById("rhToolicon"+index+"").prepend(clone);
            });
        }else{
            $( "#main_header .responsive_nav_wrap #mobile-menu-icons" ).append( $(".logo-section .mobileinmenu").clone()); 
            var mobilelogo = document.querySelector("a.logo_image_mobile img");
            if(mobilelogo !== null){
                if(document.body.classList.contains('rtl')){
                    mobilelogo.setAttribute("style", "right:55px; left:auto; transform:none;");
                }else{
                    mobilelogo.setAttribute("style", "left:55px; transform:none;");
                }
            }
        }
    }             
    if ($('#logo_mobile_wrapper').length > 0) {
        $( ".responsive_nav_wrap #dl-trigger" ).after($('#logo_mobile_wrapper').html() );
        $( ".logo_image_insticky, header .logo" ).addClass('hideontablet');
    }       
    if ($('.main-nav .logo-inmenu').length > 0) { 
        $( "#main_header .responsive_nav_wrap #dl-menu .menu-item.logo-inmenu" ).remove();
    }       

    $("nav.top_menu > ul li.menu-item-has-children").hoverIntent({
        over: reshowNav,
        out: rehideNav,
        timeout: 120,
        interval: 100
    });

    $("#main_header .top-nav > ul li.menu-item-has-children").hoverIntent({
        over: reshowNav,
        out: rehideNav,
        timeout: 120,
        interval: 100
    });    

    revMenuStyle();
    re_main_search.init();   

   /* scroll to # */
   $(document).on('click','.rehub_scroll, #kcmenu a, .kc-gotop', function (e) {
      e.preventDefault();
      if (typeof $(this).data('scrollto') !== 'undefined') {
         var target = $(this).data('scrollto');
         var hash = $(this).data('scrollto');
      } 
      else {
         var target = $(this.hash + ', a[name="'+ this.hash.replace(/#/,"") +'"]').first();
         var hash = this.hash;
      }

      var $target = $(target);
      if($target.length !==0){
          $('html, body').stop().animate({
             'scrollTop': $target.offset().top - 45
          }, 500, 'swing', function () {
            if(history.pushState) {
              history.pushState(null, null, hash);
            }
            else {
              window.location.hash = hash;
            }
          });
      }
   });   

   /*bar*/
   if($('.wpsm-bar').length > 0){
       $('.wpsm-bar').each(function(){
          $(this).find('.wpsm-bar-bar').animate({ width: $(this).attr('data-percent') }, 1500 );
       });
   }      

    if($(".countdown_dashboard").length > 0){
        $(".countdown_dashboard").each(function(){
            $(this).show();
            var id = $(this).attr("id");
            var day = $(this).attr("data-day");
            var month = $(this).attr("data-month");
            var year = $(this).attr("data-year");
            var hour = $(this).attr("data-hour");
            var min = $(this).attr("data-min");
            //var curtime = $(this).attr("data-currenttime");
            $(this).countDown({
                targetDate: {
                    "day":      day,
                    "month":    month,
                    "year":     year,
                    "hour":     hour,
                    "min":      min,
                    "sec":      0
                },
                //currenttime: curtime,
                omitWeeks: true,
                onComplete: function() { $("#"+ id).hide() }
            });            
        });
    }

    /* offer archive dropdown */  
    $(document).on('click', '.r_offer_details .r_show_hide', function(e){
        let element = $(this).closest('.r_offer_details').find('.open_dls_onclk');
        if(!element.hasClass('rh_collapse_in')){
            let y = element.position().top + window.scrollY - 50;
            window.scroll({
                top: y,
                behavior: 'smooth'
            });
        }
        element.toggleClass('rh_collapse_in');
        $(this).closest('.r_offer_details').find('.hide_dls_onclk').toggleClass('rhhidden');
        $(this).toggleClass('r_show_active');
    });  

    //close the sliding panel
    $('.rh-sslide-panel').on('click', function(event){
        if( $(event.target).is('.rh-sslide-panel') || $(event.target).is('.rh-sslide-close-btn') ) { 
            $('.rh-sslide-panel').removeClass('active');
            $('.rh-sslide-panel').find('.widget_shopping_cart').html("");
            event.preventDefault();
        }
    });    

    /* responsive video*/
    $('.rh-container').find( 'iframe[src*="player.vimeo.com"], iframe[src*="youtube.com"]' ).each( function() {
        var $video = $(this);
        if ( $video.parents( 'object' ).length ) return;
        if ($video.parent().hasClass('rhpb-video-wrapper')) return;
        if ($video.parent().hasClass('video-container')) return;
        if ($video.parent().hasClass('wp-block-embed__wrapper')) return;
        if ($video.parent().parent().hasClass('slides')) return;
        if ( ! $video.prop( 'id' ) ) $video.attr( 'id', 'rvw' + Math.floor( Math.random() * 999999 ) );
        $video.wrap( '<div class="video-container"></div>');
    });                       

   // Coupon Modal
   $(document).on("click", ".masked_coupon:not(.expired_coupon)", function(e){
    e.preventDefault();
      var $this = $(this);
      var codeid = $this.data('codeid');
      var codetext = $this.data('codetext');
      var issearch = window.location.search;
      if(issearch){
        var codeidtext = "&codeid=";
        var codetexttext = "&codetext=";
        if (typeof URLSearchParams !== 'undefined') {
            let params = new URLSearchParams(issearch.substring(1));
            params.delete('codeid');
            params.delete('codetext');
            issearch = '?' + params.toString();
        }
      }else{
        var codeidtext = "?codeid=";
        var codetexttext = "?codetext=";        
      }
      if (typeof $this.data('codeid') !== 'undefined') {var couponpage = window.location.pathname + issearch + codeidtext + codeid;}
      if (typeof $this.data('codetext') !== 'undefined') {var couponpage = window.location.pathname + issearch + codetexttext + codetext;}
      var couponcode = $this.data('clipboard-text'); 
      var destination = $this.data('dest'); 
      window.open(couponpage);
      if( destination != "" || destination != "#" ){
         window.location.href= destination;
      }      
   });

   if($('#coupon_code_in_modal').length > 0){
        var codeid = $('#coupon_code_in_modal').data('couponid');
        var $change_code = $(".rehub_offer_coupon.masked_coupon:not(.expired_coupon)[data-codeid='" + codeid + "']");
        var couponcode = $change_code.data("clipboard-text");
        $change_code.removeClass("rh-deal-compact-btn masked_coupon woo_loop_btn coupon_btn btn_offer_block wpsm-button").addClass("not_masked_coupon").html( "<i class=\'rhicon rhi-scissors fa-rotate-180\'></i><span class=\'coupon_text\'>"+ couponcode +"</span>" );                                  
        $change_code.closest(".reveal_enabled").removeClass("reveal_enabled");
        $.pgwModal({
            titleBar: false,
            maxWidth: 650,
            target: "#coupon_code_in_modal",
            mainClassName : "pgwModal coupon-reveal-popup",
        });        
   }

   $(document).on("click", "a.not_masked_coupon", function(e){
      e.preventDefault();
   });

   $(document).on("click", ".csspopuptrigger", function(e){
      e.preventDefault();
      var destination = '#' + $(this).data('popup');
      $(destination).toggleClass('active');
      $('body').addClass('flowhidden');
   });

   $(document).on("click", ".csspopup .cpopupclose", function(e){
      e.preventDefault();
      $(this).closest('.csspopup').removeClass('active');
      $('body').removeClass('flowhidden');
   });

   $(document).on("click", ".toggle-this-table", function(e){
      e.preventDefault();
      $(this).closest('.rh-tabletext-block').toggleClass('closedtable');
   }); 

   if($(".rehub_offer_coupon.masked_coupon.expired_coupon").length > 0){
       $(".rehub_offer_coupon.masked_coupon.expired_coupon").each(function() {
          var couponcode = $(this).data('clipboard-text');
          $(this).removeClass('masked_coupon woo_loop_btn coupon_btn btn_offer_block wpsm-button').addClass('not_masked_coupon').text(couponcode);
          $(this).closest('.reveal_enabled').removeClass('reveal_enabled');
       }); 
   }           

    //external links
   $('.ext-source').replaceWith(function(){
      return '<a href="' + $(this).data('dest') + '" target="_blank" rel="nofollow sponsored">' + $(this).html() + '</a>';
   });

   $('.int-source').replaceWith(function(){
      return '<a href="' + $(this).data('dest') + '">' + $(this).html() + '</a>';
   });       

   //Sharing popups JS
   jQuery(document).on( 'click', '.share-link-image', function( event ) {
      var href    = jQuery( this ).data( "href" ),
         service = jQuery( this ).data( 'service' ),
         width   = 'pinterest' == service ? 750 : 600,
         height  = 'twitter' == service ? 250 : 'pinterest' == service ? 320 : 300,
         top     = ( screen.height / 2 ) - height / 2,
         left    = ( screen.width / 2 ) - width / 2;
      var options = 'top=' + top + ',left=' + left + ',width=' + width + ',height=' + height;
      event.preventDefault();
      event.stopPropagation();
      window.open( href, service, options );
   });    

    // Search icon show/hide 
    $(window).on("resize", function(){
        var w = $(window).width();
        if (w > 1023){
            $('#slide-menu-mobile').hide();
            $('.offsetnav-overlay').hide();
        }
    });                     

}); //END Document.ready

// Rate bar annimation
jQuery(function($){
'use strict';  
  $(document).ready(function(){   
    $(document).on('inview', '.rate_bar_wrap', function(event, visible) {
      if (visible) {
        $('.rate-bar').each(function(){
         $(this).find('.rate-bar-bar').animate({ width: $(this).attr('data-percent') }, 1500 );
        });
         $(document).off('inview', '.rate_bar_wrap');
      }
    });

    $('.radial-progress').each(function(){
        $(this).find('.circle .mask.full, .circle .fill:not(.fix)').animate({  borderSpacing: $(this).attr('data-rating')*18 }, {
            step: function(now,fx) {
              $(this).css('transform','rotate('+now+'deg)');
            },
            duration:'slow'
        },'linear');

        $(this).find('.circle .fill.fix').animate({  borderSpacing: $(this).attr('data-rating')*36 }, {
            step: function(now,fx) {
              $(this).css('transform','rotate('+now+'deg)');
            },
            duration:'slow'
        },'linear');                   

      });

  });
});  
  
   
//Scroll To top
if(jQuery('.post-inner').length > 0){
    var postheight = jQuery('.post-inner').height() + jQuery('#main_header').height() - 100;
    jQuery(window).on('scroll', jQuery.throttle( 250, function(){
    'use strict';
       
       if (jQuery(this).scrollTop() > 500) {
          jQuery('#topcontrol, #float-posts-nav').addClass('scrollvisible');
       } else {
          jQuery('#topcontrol').removeClass('scrollvisible');
          jQuery('#float-posts-nav').removeClass('scrollvisible');
       }
       if (jQuery(this).scrollTop() > postheight) {
          jQuery('#float-posts-nav').addClass('openedprevnext');
       } else {
          jQuery('#float-posts-nav').removeClass('openedprevnext');
       } 

    }));    
}