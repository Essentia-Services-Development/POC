!function(t){t.fn.countDown=function(e){return"string"==typeof e?t(this).data("countDown")[e].apply(this)||this:this.each(function(){var a,o=t(this),n=new Date;function s(e,a,n){o.find(e+" .digit").each(function(e){!function(t,e,a){var o=t.find(".top"),n=t.find(".bottom");o.html()!=e+""&&(o.html(e||"0").slideDown(a),n.animate({height:0},a,function(){n.html(e||"0").css({height:"100%"}),o.hide()}))}(t(this),0==e?Math.floor(a/10):a%10,n)})}o.data("countDown")||(e.targetDate?n=new Date(e.targetDate.month+"/"+e.targetDate.day+"/"+e.targetDate.year+" "+e.targetDate.hour+":"+e.targetDate.min+":"+e.targetDate.sec+(e.targetDate.utc?" UTC":"")):e.targetOffset&&(n.setFullYear(e.targetOffset.year+n.getFullYear()),n.setMonth(e.targetOffset.month+n.getMonth()),n.setDate(e.targetOffset.day+n.getDate()),n.setHours(e.targetOffset.hour+n.getHours()),n.setMinutes(e.targetOffset.min+n.getMinutes()),n.setSeconds(e.targetOffset.sec+n.getSeconds())),o.find(".digit").html('<div class="top"></div><div class="bottom"></div>'),o.data("countDown",{stop:function(){null!=a&&(clearInterval(a),a=void 0)},start:function(){if(null==a){var t=Math.floor((+n-+new Date)/1e3);t<0&&(t=0);var r=function(){!function(t,a){secs=t%60,mins=Math.floor(t/60)%60,hours=Math.floor(t/60/60)%24,e.omitWeeks?(days=Math.floor(t/60/60/24),weeks=Math.floor(t/60/60/24/7)):(days=Math.floor(t/60/60/24)%7,weeks=Math.floor(t/60/60/24/7));s(".seconds_dash",secs,a),s(".minutes_dash",mins,a),s(".hours_dash",hours,a),s(".days_dash",days,a),s(".weeks_dash",weeks,a),t<=0&&function(){o.data("countDown").stop(),e.onComplete&&e.onComplete.apply(o)}()}(t,500),t-=1};r(),t>0&&(a=setInterval(r,1e3))}}}),o.data("countDown").start())})}}(jQuery);
!function(e,i){"object"==typeof exports&&"undefined"!=typeof module?i(exports):"function"==typeof define&&define.amd?define(["exports"],i):i((e=e||self).window=e.window||{})}(this,function(e){"use strict";function g(){return i||"undefined"!=typeof window&&(i=window.gsap)&&i.registerPlugin&&i}function j(e,i,t){t=!!t,e.visible!==t&&(e.visible=t,e.traverse(function(e){return e.visible=t}))}function k(e){return("string"==typeof e&&"="===e.charAt(1)?e.substr(0,2)+parseFloat(e.substr(2)):e)*t}function l(e){(i=e||g())&&(d=i.core.PropTween,f=1)}var i,f,d,u={x:"position",y:"position",z:"position"},t=Math.PI/180;"position,scale,rotation".split(",").forEach(function(e){return u[e+"X"]=u[e+"Y"]=u[e+"Z"]=e});var n={version:"3.0.0",name:"three",register:l,init:function init(e,i){var t,n,o,r,s,a;for(r in f||l(),i){if(t=u[r],o=i[r],t)n=~(s=r.charAt(r.length-1).toLowerCase()).indexOf("x")?"x":~s.indexOf("z")?"z":"y",this.add(e[t],n,e[t][n],~r.indexOf("rotation")?k(o):o);else if("scale"===r)this.add(e[r],"x",e[r].x,o),this.add(e[r],"y",e[r].y,o),this.add(e[r],"z",e[r].z,o);else if("opacity"===r)for(s=(a=e.material.length?e.material:[e.material]).length;-1<--s;)a[s].transparent=!0,this.add(a[s],r,a[s][r],o);else"visible"===r?e.visible!==o&&(this._pt=new d(this._pt,e,r,o?0:1,o?1:-1,0,0,j)):this.add(e,r,e[r],o);this._props.push(r)}}};g()&&i.registerPlugin(n),e.ThreePlugin=n,e.default=n;if (typeof(window)==="undefined"||window!==e){Object.defineProperty(e,"__esModule",{value:!0})} else {delete e.default}});
(function($) {
    "use strict";

    var scrolledfind = false;

    function multiParallax() {
        //BG parallax
        if($('.rh-parallax-bg-true').length > 0){
            var scrollTop = $(window).scrollTop();
            $('.rh-parallax-bg-true').each(function() {
                var paralasicValue = $(this).prop('class').match(/rh-parallax-bg-speed-([0-9]+)/)[1];
                var paralasicValue = parseInt(paralasicValue)/100;
                var backgroundPos = $(this).css('backgroundPosition').split(" ");
                if (backgroundPos[0] == '100%'){
                    var bgx = 'right';
                }
                else if (backgroundPos[0] == '50%'){
                    var bgx = 'center';
                }
                else if (backgroundPos[0] == '0%'){
                    var bgx = 'left';
                }else{
                    var bgx = backgroundPos[0];
                } 
                if (backgroundPos[1] == '0%'){
                    var bgy = 'top';
                }
                else if (backgroundPos[1] == '50%'){
                    var bgy = 'center';
                }
                else if (backgroundPos[1] == '100%'){
                    var bgy = 'bottom';
                } 
                else{
                    var bgy = backgroundPos[1];
                }                                                              
                $(this).css('background-position', ''+bgx+' '+bgy+' -' + scrollTop * paralasicValue + 'px');
            }); 
        }        
    } 

    function RHBatchScrollTrigger(current, anargs, $batchobj){
        var scrollargs = {};
        if(current.data('triggerstart')){
            scrollargs.start = current.data('triggerstart');
        }else{
            scrollargs.start = "top 92%";
        }
        if(current.data('triggerend')){
            scrollargs.end = current.data('triggerend');
        }
        var batchenter = {};
        var batchenterback = {};
        var batchleave = {};
        var batchleaveback = {};
        var batchinit = {};
        for(let batchitem in anargs){
            if(batchitem == 'x' || batchitem == 'y' || batchitem == 'xPercent' || batchitem == 'yPercent' || batchitem == 'rotation' || batchitem == 'rotationX' || batchitem == 'rotationY'){
                batchenter[batchitem] = 0;
                batchenterback[batchitem] = 0;
                batchleave[batchitem] = -anargs[batchitem];
                batchleaveback[batchitem] = anargs[batchitem];
                batchinit[batchitem] = anargs[batchitem];
            }
            if(batchitem == 'scale' || batchitem == 'scaleX' || batchitem == 'scaleY' || batchitem == 'autoAlpha'){
                batchenter[batchitem] = 1;
                batchenterback[batchitem] = 1;
                batchleave[batchitem] = anargs[batchitem];
                batchleaveback[batchitem] = anargs[batchitem];
                batchinit[batchitem] = anargs[batchitem];
            }
            if(batchitem == 'transformOrigin' || batchitem == 'duration'){
                batchinit[batchitem] = anargs[batchitem];
            }
        }
        batchenter.overwrite = batchleave.overwrite = batchenterback.overwrite = batchleaveback.overwrite = true;

        if(current.data('batchint')){
            var batchstagger = parseFloat(current.data('batchint'));
        }else{
            var batchstagger = 0.15;
        }
        batchenter.stagger = {each: batchstagger};
        batchenterback.stagger = {each: batchstagger};
        if(current.data('batchrandom') == 'yes'){
            batchenter.stagger.from = "random";
            batchenterback.stagger.from = "random";
        }

        gsap.set($batchobj, batchinit);
        scrollargs.onEnter = batch => gsap.to(batch, batchenter);
        scrollargs.onLeave = batch => gsap.to(batch, batchleave);
        scrollargs.onEnterBack = batch => gsap.to(batch, batchenterback);
        scrollargs.onLeaveBack = batch => gsap.to(batch, batchleaveback);                
        ScrollTrigger.batch($batchobj, scrollargs);
    }  

    function RHplayVideo(el) {
        let vid = el.find("video");
        if (vid.length && vid.find('source').length) {
            if(vid[0].paused){
                vid[0].play();
            }
        }
    }

    function RHpauseVideo(el) {
        let vid = el.find("video");
        if (vid.length && vid.find('source').length) {
            if(!vid[0].paused){
              //console.log('pause');
                vid[0].pause();
            }
        }
    }

    var RehubWidgetsScripts = function( $scope, $ ) {

        if($scope.find('.re_carousel').length > 0){
          var owl = $scope.find('.re_carousel');
          owl.on('initialized.owl.carousel', function(e) {
            owl.parent().removeClass('loading');
          });
          var carouselplay = (owl.data('auto')==1) ? true : false;
          var showrow = (owl.data('showrow') !='') ? owl.data('showrow') : 4;
          var laizy = (owl.data('laizy') == 1) ? true : false;
          var navdisable = (owl.data('navdisable') == 1) ? false : true;
          var loopdisable = (owl.data('loopdisable') == 1) ? false : true;
          var dotenable = (owl.data('dotenable') == 1) ? true : false;
          var rtltrue = (jQuery('body').hasClass('rtl')) ? true : false;
          if (owl.data('fullrow') == 1) {
             var breakpoint = {
                0:{
                   items:1,
                   nav:true,
                },
                530:{
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
             autoplayTimeout : 8000,
             autoplayHoverPause : true,
             autoplaySpeed : 1000,
             navSpeed : 800,
             dotsSpeed : 800
          }); 

          var customnext = owl.closest('.custom-nav-car').find('.cus-car-next');
          if(customnext){
            customnext.click(function(){
            owl.trigger('next.owl.carousel', [800]);
          });
          }
          var customprev = owl.closest('.custom-nav-car').find('.cus-car-prev');
          if(customprev){
            customprev.click(function(){
            owl.trigger('prev.owl.carousel', [800]);
          });
          }   
            if(owl.hasClass('rh-ca-connected')){
                var connectedid = jQuery('#' + owl.data('connected'));
                if(connectedid.length > 0){
                    if (connectedid.find('.elementor-tab-title').length > 0){
                        var $connectedtabs = connectedid.find('.elementor-tab-title');
                        connectedid.on("click", $connectedtabs, function(e){
                            var id = parseInt(jQuery(e.target).closest('.elementor-tab-title').data('tab'));
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
        };

        if($scope.find('.main_slider').length > 0){
            var slider = $scope.find('.main_slider');
            slider.flexslider({
                animation: "slide",
                start: function(slider) {
                   slider.removeClass('loading');
                }
            });
        }

        $('.wpsm-bar').each(function(){
            $(this).find('.wpsm-bar-bar').animate({ width: $(this).attr('data-percent') }, 1500 );
        }); 

        $('.rate-bar').each(function(){
            $(this).find('.rate-bar-bar').css("width", $(this).attr('data-percent'));
        });                

        $(".countdown_dashboard").each(function(){
            $(this).show();
            var id = $(this).attr("id");
            var day = $(this).attr("data-day");
            var month = $(this).attr("data-month");
            var year = $(this).attr("data-year");
            var hour = $(this).attr("data-hour");
            var min = $(this).attr("data-min");
            $(this).countDown({
                targetDate: {
                    "day":      day,
                    "month":    month,
                    "year":     year,
                    "hour":     hour,
                    "min":      min,
                    "sec":      0
                },
                omitWeeks: true,
                onComplete: function() { $("#"+ id).hide() }
            });            
        });

        if ($('.wpsm-tooltip').length > 0) {
            $(".wpsm-tooltip").tipsy({gravity: "s", fade: true, html: true });
        }        

        if($scope.find('.tabs-menu').length > 0){
          var curtabmenu = $scope.find('.tabs-menu');
          curtabmenu.on('click', 'li:not(.current)', function() {
              var tabcontainer = $(this).closest('.tabs');
              if(tabcontainer.length == 0) {
                  var tabcontainer = $(this).closest('.elementor-widget-wrap');
              }
              $(this).addClass('current').siblings().removeClass('current');
              tabcontainer.find('.tabs-item').hide().removeClass('stuckMoveDownOpacity').eq($(this).index()).show().addClass('stuckMoveDownOpacity');   
          });
          curtabmenu.find('li:first-child').trigger('click');

        }        

        $('.radial-progress').each(function(){
          $(this).find('.circle .mask.full, .circle .fill:not(.fix)').animate({  borderSpacing: $(this).attr('data-rating')*18 }, {
              step: function(now,fx) {
                $(this).css('-webkit-transform','rotate('+now+'deg)'); 
                $(this).css('-moz-transform','rotate('+now+'deg)');
                $(this).css('transform','rotate('+now+'deg)');
              },
              duration:'slow'
          },'linear');

          $(this).find('.circle .fill.fix').animate({  borderSpacing: $(this).attr('data-rating')*36 }, {
              step: function(now,fx) {
                $(this).css('-webkit-transform','rotate('+now+'deg)'); 
                $(this).css('-moz-transform','rotate('+now+'deg)');
                $(this).css('transform','rotate('+now+'deg)');
              },
              duration:'slow'
          },'linear');                     
        });

        if($scope.find('.swiper-slide').length > 0){
            var link = $scope.find(".swiper-slide a").first().attr('href');
            if (typeof link !== 'undefined' && link !== null) {
                var links = link.split(';');
                var elements = $scope.find(".swiper-slide:not(.swiper-slide-duplicate)");
                for (var i = elements.length - 1; i >= 0; i--) {
                    if (typeof links[i] !== 'undefined' && links[i] !== null) {
                        jQuery(this).find("[data-swiper-slide-index='" + i + "'] a").attr('href',links[i]);
                    }
                }
            }
        }

        //GSAP
        if($scope.find('.rh-gsap-wrap').length > 0){

            var scrollargs = {};
            var anargs = {};
            var current = $scope.find('.rh-gsap-wrap');

            var $duration = current.data('duration');
            var $duration = parseFloat($duration);
            anargs.duration = $duration;
            if(current.hasClass('prehidden')){
                current.removeClass('prehidden');
            }
            if($scope.hasClass('prehidden')){
                $scope.removeClass('prehidden');
            }
            if(current.data('triggertype')){
                var triggertype = current.data('triggertype');
            }else{
                var triggertype = 'custom';
            }            

            if(current.data('x')){
                anargs.x = current.data('x');
            }

            if(current.data('y')){
                anargs.y = current.data('y');
            }

            if(current.data('z')){
                anargs.z = current.data('z');
            }

            if(current.data('xo')){
                anargs.xPercent = current.data('xo');
            }

            if(current.data('yo')){
                anargs.yPercent = current.data('yo');
            }

            if(current.data('r')){
                anargs.rotation = current.data('r');
            }

            if(current.data('rx')){
                anargs.rotationX = current.data('rx');
            }

            if(current.data('ry')){
                anargs.rotationY = current.data('ry');
            }

            if(current.data('width')){
                anargs.width = current.data('width');
            }

            if(current.data('height')){
                anargs.height = current.data('height');
            }

            if(current.data('s')){
                anargs.scale = current.data('s');
            }

            if(current.data('sx')){
                anargs.scaleX = current.data('sx');
            }

            if(current.data('sy')){
                anargs.scaleY = current.data('sy');
            }
            if(current.data('boxshadow')){
                anargs.boxShadow = current.data('boxshadow').toString();
                let colorarray = anargs.boxShadow.split('#');
                gsap.set(current, {boxShadow: "0 0 0 0 #"+ colorarray[1]+""});
            }
            if(current.data('o')){
                anargs.autoAlpha = parseInt(current.data('o'))/100;
            }
            if(current.data('bg')){
                anargs.backgroundColor = current.data('bg');
            }
            if(current.data('origin')){
                anargs.transformOrigin = current.data('origin');
            }

            if(current.data('path')){
                if(current.data('path') == 'custom'){
                    var argspathcustom = {};
                    if(current.data('from')=='yes'){
                        argspathcustom.start = 1;
                        argspathcustom.end = 0;
                    }
                    if($('.copy-motion-path').length==0){
                        MotionPathHelper.create(current, argspathcustom);
                    }
                    
                }else{
                    anargs.motionPath = {
                        path: current.data('path'),
                        immediateRender: true
                    }
                    if(current.data('path-align')){
                        anargs.motionPath.align = current.data('path-align');
                    }
                    anargs.motionPath.alignOrigin = [];
                    if(current.data('path-alignx') !== null && current.data('path-alignx') !== undefined){
                        anargs.motionPath.alignOrigin[0] = parseFloat(current.data('path-alignx'));
                    }else{
                        anargs.motionPath.alignOrigin[0] = 0.5;
                    }
                    if(current.data('path-aligny') !== null && current.data('path-aligny') !== undefined){
                        anargs.motionPath.alignOrigin[1] = parseFloat(current.data('path-aligny'));
                    }else{
                        anargs.motionPath.alignOrigin[1] = 0.5;
                    }
                    if(current.data('path-orient')){
                        anargs.motionPath.autoRotate = true;
                    }
                }
            }

            if(current.data('ease')){
                var $ease = current.data('ease').split('-');
                anargs.ease = $ease[0]+'.'+$ease[1];
                if(anargs.ease === 'power0.none'){           
                    anargs.ease = 'none';
                }
            }

            if(current.data('stagger')){
                var stagerobj = current.data('stagger');
                if(stagerobj.indexOf(".") == 0 || stagerobj.indexOf("#") == 0){
                    var $anobj = $(stagerobj);
                }else{
                    var $anobj = $('.'+stagerobj);
                }
            }else if(current.data('text')){
                var $texttype = current.data('text');
                var splittextobj = current.children();
                var split = new SplitText(splittextobj, {type: $texttype});
                if($texttype == 'chars'){
                    var $anobj = split.chars;
                }else if($texttype == 'words'){
                    var $anobj = split.words;
                }else{
                    var $anobj = split.lines;
                }
            }else if(current.data('svgdraw')){
                var svgarray = [];
                var shapes = ['path', 'line', 'polyline', 'polygon', 'rect', 'ellipse', 'circle'];
                for (var shape in shapes){
                    if($scope.find(shapes[shape]).length > 0){
                        svgarray.push($scope.find(shapes[shape]));
                    }
                }
                $anobj = svgarray;
                if(current.data('from')=='yes'){
                    anargs.drawSVG = "0%";
                }else{
                    anargs.drawSVG = "100%";
                }
                if(current.data('bg')){
                    anargs.stroke = current.data('bg');
                }
                
            }
            else{
                if(current.data('customobject')){
                    if(current.data('customobject').indexOf("#") == 0){
                        var customobject = current.data('customobject');
                    }else{
                        var customobject = '#'+current.data('customobject');
                        $anobj = customobject;
                    }                
                }else{
                    $anobj = current;
                }
            }

            if(current.data('stagger') || current.data('text') || current.data('svgdraw')){
                anargs.stagger = {};
                if(current.data('stdelay')){
                    anargs.stagger.each = current.data('stdelay');
                }else{
                    anargs.stagger.each = 0.2;
                }
                if(current.data('strandom') == 'yes'){
                    anargs.stagger.from = "random";
                }
            }

            var animation = gsap.timeline();
            if(current.data('from')=='yes'){
                //var animation = gsap.from($anobj, anargs);
                animation.from($anobj, anargs);
            }else{
                animation.to($anobj, anargs);
                //var animation = gsap.to($anobj, anargs);
            }
            if(current.data('delay')){
                animation.delay(current.data('delay'));
            }
            if(current.data('loop')=='yes'){
                if(current.data('yoyo')=='yes'){
                    animation.yoyo(true);
                }
                animation.repeat(-1);
                if(current.data('delay') && current.data('repeatdelay')=='yes'){
                    animation.repeatDelay(current.data('delay'));
                }
            }

            var multianimations = current.data('multianimations');
            if(multianimations){
            
                for(var curr = 0; curr < multianimations.length; curr++){

                    let rx = multianimations[curr].multi_rx;
                    let ry = multianimations[curr].multi_ry;
                    let r = multianimations[curr].multi_r;
                    let px = multianimations[curr].multi_x;
                    let py = multianimations[curr].multi_y;
                    let pxo = multianimations[curr].multi_xo;
                    let pyo = multianimations[curr].multi_yo;
                    let sc = multianimations[curr].multi_scale;
                    let scx = multianimations[curr].multi_scale_x;
                    let scy = multianimations[curr].multi_scale_y;
                    let width = multianimations[curr].multi_width;
                    let height = multianimations[curr].multi_height;
                    let autoAlpha = multianimations[curr].multi_opacity;
                    let bg = multianimations[curr].multi_bg;
                    let origin = multianimations[curr].multi_origin;
                    let de = multianimations[curr].multi_delay;
                    let ea = multianimations[curr].multi_ease;
                    let du = multianimations[curr].multi_duration;
                    let from = multianimations[curr].multi_from;
                    let customtime = multianimations[curr].multi_time;
                    let customobj = multianimations[curr].multi_obj;
                    let onhov = multianimations[curr].multi_hover;
                    let curanobj = $anobj;
                    
                    let multiargs = {};
                    if(rx) multiargs.rotationX = parseFloat(rx);
                    if(ry) multiargs.rotationY = parseFloat(ry);
                    if(r) multiargs.rotation = parseFloat(r);
                    if(px) multiargs.x = parseFloat(px);
                    if(py) multiargs.y = parseFloat(py);
                    if(pxo) multiargs.xPercent = parseFloat(pxo);
                    if(pyo) multiargs.yPercent = parseFloat(pyo);
                    if(sc) multiargs.scale = parseFloat(sc);
                    if(scx) multiargs.scaleX = parseFloat(scx);
                    if(scy) multiargs.scaleY = parseFloat(scy);
                    if(autoAlpha) multiargs.autoAlpha = parseInt(autoAlpha)/100;
                    if(du) multiargs.duration = parseFloat(du);
                    if(de) multiargs.delay = parseFloat(de);
                    if(origin) multiargs.transformOrigin = origin;
                    if(!customtime) customtime = ">";
                    if(ea){
                        var $ease = ea.split("-");
                        multiargs.ease = $ease[0]+"."+$ease[1];
                        if(multiargs.ease === "power0.none"){           
                            multiargs.ease = "none";
                        }
                    }
                    if(customobj && $(customobj).length > 0){
                        $anobj = $(customobj);
                    }
                    if(from=="yes"){
                        if(onhov == "yes"){
                            let childanimation = gsap.timeline();
                            childanimation.from($anobj, multiargs, customtime).reverse();
                            curanobj.mouseenter(function(event) {
                                childanimation.play();
                            });
                            curanobj.mouseleave(function(event) {
                                childanimation.reverse();
                            });
                        }else{
                            animation.from($anobj, multiargs, customtime);
                        }
                        
                    }else{
                        if(onhov == "yes"){
                            let childanimation = gsap.timeline();
                            childanimation.to($anobj, multiargs, customtime).reverse();
                            curanobj.mouseenter(function(event) {
                                childanimation.play();
                            });
                            curanobj.mouseleave(function(event) {
                                childanimation.reverse();
                            });
                        }else{
                            animation.to($anobj, multiargs, customtime);
                        } 
                    }
                }
            
            }
            if(triggertype == 'load'){
                if(current.data('videoplay')=='yes'){
                    RHplayVideo($anobj);
                }
                animation.play();
            }
            else if(triggertype == 'batch'){
                scrolledfind = true;
                if(current.data('customtrigger')){
                  
                    var batchobj = current.data('customtrigger');
                    if(batchobj.indexOf(".") == 0){
                        var $batchobj = $(batchobj);
                    }else{
                        var $batchobj = $('.'+batchobj);
                    }              
                }else{
                    var $batchobj = $scope.find('.col_item');
                }
                RHBatchScrollTrigger(current, anargs, $batchobj);
            }
            else if(triggertype == 'hover'){
                if(current.data('customtrigger')){
                    if(current.data('customtrigger').indexOf("#") == 0){
                        var customtrigger = current.data('customtrigger');
                    }else{
                        var customtrigger = '#'+current.data('customtrigger');
                    }                
                }else{
                    var customtrigger = $scope;
                }
                let curanobj = $(customtrigger);
                animation.pause();
                animation.reverse();
                curanobj.mouseenter(function(event) {
                    if(current.data('videoplay')=='yes'){
                        RHplayVideo($anobj);
                    }
                    animation.play();
                });
                curanobj.mouseleave(function(event) {
                    if(current.data('videoplay')=='yes'){
                        RHpauseVideo($anobj);
                    }
                    animation.reverse();
                });

            }
            else if(triggertype == 'click'){
                if(current.data('customtrigger')){
                    if(current.data('customtrigger').indexOf("#") == 0){
                        var customtrigger = current.data('customtrigger');
                    }else{
                        var customtrigger = '#'+current.data('customtrigger');
                    }                
                }else{
                    var customtrigger = $scope;
                }
                let curanobj = $(customtrigger);
                animation.pause();
                animation.reverse();
                curanobj.click(
                    function(event) {
                        if(current.data('videoplay')=='yes'){
                            RHplayVideo($anobj);
                        }
                        animation.play();
                    }
                );

            }
            else{
                scrolledfind = true;
                if(current.data('customtrigger')){
                    if(current.data('customtrigger').indexOf("#") == 0){
                        var customtrigger = current.data('customtrigger');
                    }else{
                        var customtrigger = '#'+current.data('customtrigger');
                    }                
                }else{
                    var customtrigger = $scope;
                }
                scrollargs.trigger = customtrigger;

                if(current.data('triggerstart')){
                    scrollargs.start = current.data('triggerstart');
                }else{
                    scrollargs.start = "top 85%";
                }
                if(current.data('triggerend')){
                    scrollargs.end = current.data('triggerend');
                }

                if(current.data('triggerscrub')){
                    scrollargs.scrub = parseFloat(current.data('triggerscrub'));
                } 
                if(current.data('triggersnap')){
                    scrollargs.snap = parseFloat(current.data('triggersnap'));
                }
                if(current.data('pinned')){
                    scrollargs.pin = true;             
                }else{
                    if($scope.parent().hasClass('pin-spacer')){
                        $scope.unwrap();
                        $scope.removeAttr("style");
                    }
                }
                if(current.data('pinspace')){
                    scrollargs.pinSpacing = false;             
                }
                if(current.data('triggeraction')){
                    scrollargs.toggleActions = current.data('triggeraction');
                }else{
                    scrollargs.toggleActions = 'play pause resume reverse';
                }
                scrollargs.animation = animation;
                if(current.data('videoplay')=='yes'){
                    scrollargs.onToggle = self => self.isActive ? RHplayVideo($anobj) : RHpauseVideo($anobj);
                }
                ScrollTrigger.create(scrollargs);
            } 
        }

        //reveal
        if($scope.find('.rh-reveal-wrap').length > 0){
            var tl = gsap.timeline({paused: true}); 
            var revealwrap = $scope.find(".rh-reveal-wrap"); 
            var revealcover = $scope.find(".rh-reveal-block");
            var revealcontent = $scope.find(".rh-reveal-cont"); 
            revealwrap.removeClass('prehidden');
            if(revealcover.data('reveal-speed')){
                var $coverspeed = revealcover.data('reveal-speed');
            }else{
                var $coverspeed = 0.5;
            } 
            if(revealcover.data('reveal-delay')){
                var $coverdelay = revealcover.data('reveal-delay');
            }else{
                var $coverdelay = 0;
            } 
            $scope.find('img.lazyload').each(function(){
                var source = $(this).attr("data-src");
                $(this).attr("src", source).css({'opacity': '1'});
            });             
            if(revealcover.data('reveal-dir')=='lr'){
                tl.from(revealcover,{ duration:$coverspeed, scaleX: 0, transformOrigin: "left", delay: $coverdelay });
                tl.to(revealcover,{ duration:$coverspeed, scaleX: 0, transformOrigin: "right" }, "reveal");
            }else if(revealcover.data('reveal-dir')=='rl'){
                tl.from(revealcover,{ duration:$coverspeed, scaleX: 0, transformOrigin: "right", delay: $coverdelay });
                tl.to(revealcover,{ duration:$coverspeed, scaleX: 0, transformOrigin: "left" }, "reveal");
            }
            else if(revealcover.data('reveal-dir')=='tb'){
                tl.from(revealcover,{ duration:$coverspeed, scaleY: 0, transformOrigin: "top", delay: $coverdelay });
                tl.to(revealcover,{ duration:$coverspeed, scaleY: 0, transformOrigin: "bottom" }, "reveal");
            }
            else if(revealcover.data('reveal-dir')=='bt'){
                tl.from(revealcover,{ duration:$coverspeed, scaleY: 0, transformOrigin: "bottom", delay: $coverdelay });
                tl.to(revealcover,{ duration:$coverspeed, scaleY: 0, transformOrigin: "top" }, "reveal");
            }
            tl.from(revealcontent,{ duration:1, autoAlpha: 0 }, "reveal"); 
            revealwrap.elementorWaypoint(function(direction) {
                tl.play();
            }, { offset: 'bottom-in-view' });          
        }

        //mouse move
        if($scope.find('.rh-prlx-mouse').length > 0){
            var mouseargs = {};
            var curmouse = $scope.find('.rh-prlx-mouse');
            if(curmouse.data('prlx-cur') == "yes"){
                var objtrigger = curmouse;
            }else{
                var objtrigger = $('#content');
            }

            objtrigger.mousemove(function(event){
                var xPos = (event.clientX/ objtrigger.width())-0.5,
                yPos = (event.clientY/ objtrigger.height())-0.5; 
                if(curmouse.data('prlx-xy')){
                    var $speedx = curmouse.data('prlx-xy');
                    mouseargs.x = xPos * $speedx;
                    mouseargs.y = yPos * $speedx;
                }

                if(curmouse.data('prlx-tilt')){
                    var $speedtilt = curmouse.data('prlx-tilt');
                    mouseargs.rotationY = xPos * $speedtilt;
                    mouseargs.rotationX = yPos * $speedtilt;
                    mouseargs.transformPerspective = 700;
                    mouseargs.transformOrigin = "center center";
                }

                mouseargs.ease = Power1.easeOut;            

                gsap.to(curmouse, mouseargs);
            });
            if(curmouse.data('prlx-rest') == "yes"){
                curmouse.mouseleave(function(event){
                    gsap.to(curmouse, {x:0, y:0, rotationY:0, rotationX:0, ease: Power1.easeOut});
                });
            }
  
        }        
    }

    var RehubElCanvas = function($scope, $) {

        if($scope.find('.rh-video-canvas').length > 0){
            rhloadVideo();

            // Play video when page resizes
            $(window).on("resize", function() {
                rhloadVideo();
            });

            function rhloadVideo() {
                var videocurrent = $scope.find('.rh-video-canvas');

                var mainbreakpoint = (typeof videocurrent.data("breakpoint") !=='undefined') ? parseInt(videocurrent.data("breakpoint")) : 1200;
                var tabletbreakpoint = 1024;
                var mobilebreakpoint = 768;

                var mainposter = (typeof videocurrent.data("poster") !=='undefined') ? videocurrent.data("poster") : '';
                var fallbackposter = (typeof videocurrent.data("fallback") !=='undefined') ? videocurrent.data("fallback") : '';
                var tabletposter = (typeof videocurrent.data("fallback-tablet") !=='undefined') ? videocurrent.data("fallback-tablet") : '';
                var mobileposter = (typeof videocurrent.data("fallback-mobile") !=='undefined') ? videocurrent.data("fallback-mobile") : '';     

                var mp4source = (typeof videocurrent.data("mp4") !=='undefined') ? videocurrent.data("mp4") : '';
                var ogvsource = (typeof videocurrent.data("ogv") !=='undefined') ? videocurrent.data("ogv") : '';
                var webmsource = (typeof videocurrent.data("webm") !=='undefined') ? videocurrent.data("webm") : '';

                var isgsaptrigger = (typeof videocurrent.parent().attr("data-videoplay") !=='undefined') ? true : false;

                // Add source tags if not already present
                if ($(window).width() > mainbreakpoint) {
                    if(mainposter){
                        videocurrent.attr('poster', mainposter);
                    }
                    if (videocurrent.find('source').length < 1) {
                        if(mp4source){
                            var source1 = document.createElement('source');
                            source1.setAttribute('src', mp4source);
                            source1.setAttribute('type', 'video/mp4');
                            videocurrent.append(source1);                           
                        }

                        if(webmsource){
                            var source2 = document.createElement('source');
                            source2.setAttribute('src', webmsource);
                            source2.setAttribute('type', 'video/webm');
                            videocurrent.append(source2);                           
                        }

                        if(ogvsource){
                            var source3 = document.createElement('source');
                            source3.setAttribute('src', ogvsource);
                            source3.setAttribute('type', 'video/ogg');
                            videocurrent.append(source3);                           
                        }                                               
                    }
                }

                // Remove existing source tags for mobile
                if ($(window).width() <= mainbreakpoint) {
                    videocurrent.find('source').remove();
                    if(fallbackposter){
                        videocurrent.attr('poster', fallbackposter);
                    }
                }               

                if(tabletposter && $(window).width() <= tabletbreakpoint){
                    videocurrent.attr('poster', tabletposter);
                }
                if(mobileposter && $(window).width() <= mobilebreakpoint){
                    videocurrent.attr('poster', mobileposter);
                }               

                
            }
        }

        //SVG blobs
        if($scope.find('.rh-svgblob-wrapper').length > 0){
            var blobobj = $scope.find('.rh-svgblob-wrapper');   
                
            var id_scope = blobobj.attr('data-id');

            //console.log(elementSettings);

            var numPoints = parseInt(blobobj.data('numpoints'));
            var minRadius = parseInt(blobobj.data('minradius'));
            var maxRadius = parseInt(blobobj.data('maxradius'));
            var minDuration = parseInt(blobobj.data('minduration'));
            var maxDuration = parseInt(blobobj.data('maxduration'));
            var tensionPoints = parseInt(blobobj.data('tensionpoints'));

            var blob1 = createBlob({
                element: document.querySelector("#rhblobpath-"+id_scope),
                numPoints: numPoints, //5,
                centerX: 300,
                centerY: 300,
                minRadius: minRadius, //200,
                maxRadius: maxRadius, //225,
                minDuration: minDuration,
                maxDuration: maxDuration,
                tensionPoints: tensionPoints,
            });

            function createBlob(options) {
               
                var points = [];  
                var path = options.element;
                var slice = (Math.PI * 2) / options.numPoints;
                var startAngle = random(Math.PI * 2);
              
                var tl = gsap.timeline({
                    onUpdate: update
                });  
              
                for (var i = 0; i < options.numPoints; i++) {
                    var angle = startAngle + i * slice;
                    var duration = random(options.minDuration, options.maxDuration);
                    
                    var point = {
                        x: options.centerX + Math.cos(angle) * options.minRadius,
                        y: options.centerY + Math.sin(angle) * options.minRadius
                    };   
                    
                    var tween = gsap.to(point, {
                        duration: duration,
                        x: options.centerX + Math.cos(angle) * options.maxRadius,
                        y: options.centerY + Math.sin(angle) * options.maxRadius,
                        repeat: -1,
                        yoyo: true,
                        ease: Sine.easeInOut
                    });
                    
                    tl.add(tween, -random(duration));
                    points.push(point);
                }
              
                options.tl = tl;
                options.points = points;
              
                function update() {
                    path.setAttribute("d", cardinal(points, true, options.tensionPoints));
                }
                return options;
            }

            // Cardinal spline - a uniform Catmull-Rom spline with a tension option
            function cardinal(data, closed, tension) {
              
              if (data.length < 1) return "M0 0";
              if (tension == null) tension = 1;
              
              var size = data.length - (closed ? 0 : 1);
              var path = "M" + data[0].x + " " + data[0].y + " C";
              
              for (var i = 0; i < size; i++) {
                
                var p0, p1, p2, p3;
                
                if (closed) {
                  p0 = data[(i - 1 + size) % size];
                  p1 = data[i];
                  p2 = data[(i + 1) % size];
                  p3 = data[(i + 2) % size];
                  
                } else {
                  p0 = i == 0 ? data[0] : data[i - 1];
                  p1 = data[i];
                  p2 = data[i + 1];
                  p3 = i == size - 1 ? p2 : data[i + 2];
                }
                    
                var x1 = p1.x + (p2.x - p0.x) / 6 * tension;
                var y1 = p1.y + (p2.y - p0.y) / 6 * tension;

                var x2 = p2.x - (p3.x - p1.x) / 6 * tension;
                var y2 = p2.y - (p3.y - p1.y) / 6 * tension;
                
                path += " " + x1 + " " + y1 + " " + x2 + " " + y2 + " " + p2.x + " " + p2.y;
              }
              
              return closed ? path + "z" : path;
            }

            function random(min, max) {
                if (max == null) { max = min; min = 0; }
                if (min > max) { var tmp = min; min = max; max = tmp; }
                return min + (max - min) * Math.random();
            }
        }
    }

    var RehubPCanvas = function($scope, $) {

        if($scope.find('.rh-particle-canvas-true').length > 0){
            var $particleobj = $scope.find('.rh-particle-canvas-true');
            var particleid = $particleobj.attr("id");
            var particlejson = $particleobj.data('particlejson');
            particlesJS(particleid, particlejson, function() {console.log("callback - particles.js config loaded");});
        }
    }

    $(window).on('elementor/frontend/init', function () {
        elementorFrontend.hooks.addAction('frontend/element_ready/widget', RehubWidgetsScripts);
        elementorFrontend.hooks.addAction('frontend/element_ready/rh_a_canvas.default', RehubElCanvas); 
        elementorFrontend.hooks.addAction('frontend/element_ready/rh_p_canvas.default', RehubPCanvas);       
    });

    $(window).on('resize scroll', function() {
        multiParallax();
    });   

    document.addEventListener('lazyloaded', function(e){
        ScrollTrigger.refresh();
    });  

})(jQuery); 