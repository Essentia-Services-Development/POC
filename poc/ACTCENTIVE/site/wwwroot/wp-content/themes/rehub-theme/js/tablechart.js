//CHARTS
var table_charts = function() {
    if(jQuery('.table_view_charts').length > 0){
        jQuery('.table_view_charts').each(function(index){
            var rowcount = jQuery(this).find('.top_chart_row_found').data('rowcount');
            for (var rowcountindex = 0; rowcountindex < rowcount; rowcountindex++) {
                 //Equal height for each row
                 var heightArray = jQuery(this).find('li.row_chart_'+ rowcountindex +'').map( function(){
                    return  jQuery(this).height();
                 }).get();
                 var maxHeight = Math.max.apply( Math, heightArray);
                 jQuery(this).find('li.row_chart_'+ rowcountindex +'').height(maxHeight);

                 //Find differences
                 var recomparecolvalue;
                 jQuery(this).find('.top_chart_wrap li.row_chart_'+ rowcountindex +'').each(function(n) {
                    if (jQuery(this).html() != recomparecolvalue && n > 0) {
                       jQuery(this).closest('.table_view_charts').find('li.row_chart_'+ rowcountindex +'').addClass('row-is-different');
                    }
                    else {
                       recomparecolvalue = jQuery(this).html();
                    }
                 });
            }
            var carheight = jQuery(this).find('.top_chart_first').height();
            jQuery(this).find('.caroufredsel_wrapper').height(carheight+2);
        });
    }
}

jQuery(document).ready(function($) {
	"use strict";

    $('.table_view_charts').on('click', '.re-compare-show-diff', function(e){
        if ($(this).is(':checked')){
            $(this).closest('.table_view_charts').find('li[class^="row_chart"]').filter(':not(.heading_row_chart)').filter(':not(.row-is-different)').addClass('low-opacity');
        } 
        else {
            $(this).closest('.table_view_charts').find('li[class^="row_chart"]').filter(':not(.heading_row_chart)').filter(':not(.row-is-different)').removeClass('low-opacity');
        }     
    }); 

});

jQuery(document).ready(function($) {
   //CAROUSELS
   var makesiteCarousel = function() {
      if(jQuery().carouFredSel) {                 

         jQuery('.top_chart_wrap').each(function() {
            var carousel = jQuery(this).find('.top_chart_carousel');
            var directionrtl = (jQuery('body.rtl').length > 0) ? "right" : "left";
            var windowsize = jQuery(this).width(); 
            if (windowsize <= 200) {
               var passItems = 2;                                
            } else if (windowsize > 200 && windowsize <= 480) {
               var passItems = 2;               
            } else if (windowsize > 480) {
               var passItems = 4;
            }            
            carousel.carouFredSel({
               circular: false,
               infinite: false,                
               responsive: true,
               direction: directionrtl,
               auto: {
                  play: false
               },
               swipe: {
                  onTouch: true,
                  onMouse: true,
                  onAfter : function () {
                     var items = carousel.triggerHandler("currentVisible");
                     carousel.children().removeClass( "activecol" );
                     items.addClass( "activecol" );
                     carousel.find('.heightauto').removeClass('heightauto');
                  }                  
               },               
               items: {
                  height: 'variable',
                  width: 220,  
                  visible   : {
                     min      : 2,
                     max      : passItems
                  },
               },
               scroll : {
                  onAfter : function () {
                     var items = carousel.triggerHandler("currentVisible");
                     carousel.children().removeClass( "activecol" );
                     carousel.find('.heightauto').removeClass('heightauto');
                     items.addClass( "activecol" );
                  }          
               },               
               prev: {
                  button: function() {return jQuery(this).parent().parent().parent().children(".top_chart_controls").find(".prev");} 
               },
               next: {
                  button: function() {return jQuery(this).parent().parent().parent().children(".top_chart_controls").find(".next");}
               },
               pagination  : function() {return jQuery(this).parent().parent().parent().children(".top_chart_controls").find(".top_chart_pagination");},
               height: 'variable',
               width: "100%", 
               onCreate: function () {
                  var items = carousel.triggerHandler("currentVisible");
                  items.addClass( "activecol" );
                  table_charts();
               }                           
            });
         });         

      }
    }   
    //table_charts();
    makesiteCarousel();
    if(jQuery(".sticky-cell").length > 0){
        jQuery(".sticky-cell").parent().each(function() {
            var $this = jQuery(this);
            var outerheight = $this.outerHeight() + 'px';
            $this.wrap(function() {
                return '<div class="sticky-wrapper" style="height:'+ outerheight +'"></div>';
            });
        }); 
    }

   if(jQuery(".table_view_charts").length > 0){
        jQuery(".table_view_charts").each(function() {
            jQuery(this).removeClass('loading');
        });
    } 
});

if(jQuery(".sticky-cell").length > 0){

    var stickyscroll = function() {
        jQuery(".sticky-cell").parent().each(function() {
            var cell = jQuery(this);
            var scroll = jQuery(window).scrollTop();
            var length = jQuery(this).closest('.table_view_charts').height() - jQuery(this).height() - stickyheight + jQuery(this).closest('.table_view_charts').offset().top;
            var stickyheight = (jQuery('.re-stickyheader').length > 0) ? jQuery('.re-stickyheader').height() : 0;
            var width = cell.outerWidth();

            //console.log(scroll);

            if (scroll < jQuery('.table_view_charts').offset().top) {
                cell.closest('.sticky-wrapper').removeClass('is-sticky');
                cell.css({
                   'position': '',
                   'top': '',
                   'width': '',
                });             
            } else if (scroll > length) { 
                cell.closest('.sticky-wrapper').removeClass('is-sticky');
                cell.css({
                   'position': '',
                   'top': '',
                   'width': '',
                });                     
            } else {
                cell.closest('.sticky-wrapper').addClass('is-sticky');
                cell.css({
                   'position': 'fixed',
                   'top': stickyheight + 'px',
                   'width': width
                });
            }
        });
    }

    jQuery(window).on("scroll", function () {
        stickyscroll();
    });

    jQuery(window).on("resize", function () {
        setTimeout(function(){
           jQuery(".sticky-cell").parent().each(function() {
                var currentcell = jQuery(this);
                
                    var width = currentcell.closest('.top_chart_item').outerWidth();
                    currentcell.css({'width': width});
                
            });
        }, 100);
    });
 
} 