jQuery(document).ready(function($) {
   'use strict';   

   /* OWL CAROUSEL */
   if($(".re_carousel").length > 0){
       $(".re_carousel").each(function(){
            var owl = $(this);
          owl.on('initialized.owl.carousel', function(e) {
            owl.parent().removeClass('loading');
          });
          var carouselplay = (owl.data('auto')==1) ? true : false;
          var showrow = (owl.data('showrow') !='') ? owl.data('showrow') : 4;
          var laizy = (owl.data('laizy') == 1) ? true : false;
          var navdisable = (owl.data('navdisable') == 1) ? false : true;
          var dotenable = (owl.data('dotenable') == 1) ? true : false;
          var loopdisable = (owl.data('loopdisable') == 1) ? false : true;
          var rtltrue = ($('body').hasClass('rtl')) ? true : false;
          if (owl.data('fullrow') == 1) {
             var breakpoint = {
                0:{
                   items:1,
                   nav:true,
                },
                350:{
                   items:2,
                },
                730:{
                   items:3,
                },
                1024:{
                   items:4,
                },                        
                1224:{
                   items:showrow,
                }
             }
          }
          else if (owl.data('fullrow') == 2) {
             var breakpoint = {
                0:{
                   items:1,
                   nav:true,
                },
                768:{
                   items:2,
                },
                1120:{
                   items:3,
                },                        
                1224:{
                   items:showrow,
                }
             }
          } 
          else if (owl.data('fullrow') == 3) {
             var breakpoint = {
                0:{
                   items:1,
                   nav:true,
                },
                768:{
                   items:1,
                },
                1120:{
                   items:1,
                },                        
                1224:{
                   items:showrow,
                }
             }
          }            
          else {
             var breakpoint = {
                0:{
                   items:1,
                   nav:true,
                },
                510:{
                   items:2,
                },
                600:{
                   items:3,
                },            
                1024:{
                   items:showrow,
                }
             }
          }         

          owl.owlCarousel({
            rtl:rtltrue,
             loop:loopdisable,
             dots:dotenable,
             nav: navdisable,
             lazyLoad: laizy,
             autoplay: carouselplay,
             responsiveClass:true,
             navText :["", ""],
             navClass: ["controls prev","controls next"],
             responsive: breakpoint,
             autoplayTimeout : 4000,
             autoplayHoverPause : true,
             autoplaySpeed : 1000,
             navSpeed : 1000,
             dotsSpeed : 1000,
             checkVisible: false,
             dragEndSpeed: 1000
          }); 

          var customnext = owl.closest('.custom-nav-car').find('.cus-car-next');
          if(customnext){
            customnext.click(function(){
                owl.trigger('next.owl.carousel', [1000]);
            });
          }
          var customprev = owl.closest('.custom-nav-car').find('.cus-car-prev');
          if(customprev){
            customprev.click(function(){
                owl.trigger('prev.owl.carousel', [1000]);
            });
          } 
            if(owl.hasClass('rh-ca-connected')){
                var connectedid = $('#' + owl.data('connected'));
                if(connectedid.length > 0){
                    if (connectedid.find('.elementor-tab-title').length > 0){
                        var $connectedtabs = connectedid.find('.elementor-tab-title');
                        connectedid.on("click", $connectedtabs, function(e){
                            var id = parseInt($(e.target).closest('.elementor-tab-title').data('tab'));
                            var id = id - 1;
                            owl.trigger('to.owl.carousel', [id, 1000, true]);
                        });
                        owl.on('changed.owl.carousel', function(e) {
                            var items = e.item.count;
                            var item  = e.item.index - 1;
                            if(item > items) {
                               item = item - items;
                            } else if (item < 1) {
                               item = items;
                            }
                            if(!connectedid.find('.elementor-tab-title[data-tab="'+parseInt(item)+'"]').hasClass("elementor-active")){
                                connectedid.find('.elementor-tab-title[data-tab="'+parseInt(item)+'"]').trigger("click");
                            }
                        })

                    }
                }
            }     

       });
   }

});