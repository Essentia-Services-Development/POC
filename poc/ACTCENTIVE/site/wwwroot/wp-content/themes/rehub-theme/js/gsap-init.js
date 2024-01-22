jQuery(document).ready(function($) {
   'use strict';	
    //GSAP 
    var scrolledfind = false;
    if($('.rh-gsap-wrap').length > 0){
        $('.rh-gsap-wrap').each(function(){

            var scrollargs = {};
            var current = $(this);
            if(current.hasClass('prehidden')){
                current.removeClass('prehidden');
            }
            if(current.closest('.elementor-widget').hasClass('prehidden')){
                current.closest('.elementor-widget').removeClass('prehidden');
            }

            if(current.data('triggertype')){
                var triggertype = current.data('triggertype');
            }else{
                var triggertype = 'custom';
            }

            var anargs = RHGetBasicTween(current);

            if(current.data('path')){
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
                    if($(this).find(shapes[shape]).length > 0){
                        svgarray.push($(this).find(shapes[shape]));
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
                            curanobj.on("mouseenter", function(event) {
                                childanimation.play();
                            });
                            curanobj.on("mouseleave", function(event) {
                                childanimation.reverse();
                            });
                        }else{
                            animation.from($anobj, multiargs, customtime);
                        }
                        
                    }else{
                        if(onhov == "yes"){
                            let childanimation = gsap.timeline();
                            childanimation.to($anobj, multiargs, customtime).reverse();
                            curanobj.on("mouseenter", function(event) {
                                childanimation.play();
                            });
                            curanobj.on("mouseleave", function(event) {
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
                    var $batchobj = $(this).closest('.elementor-widget').find('.col_item');
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
                    var customtrigger = $(this).closest('.elementor-widget');
                }
                let curanobj = $(customtrigger);
                animation.pause();
                animation.reverse();
                curanobj.on("mouseenter", function(event) {
                    if(current.data('videoplay')=='yes'){
                        RHplayVideo($anobj);
                    }
                    animation.play();
                });
                curanobj.on("mouseleave", function(event) {
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
                    var customtrigger = $(this).closest('.elementor-widget');
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
                    var customtrigger = $(this).closest('.elementor-widget');
                }
                scrollargs.trigger = customtrigger;

                if(current.data('triggerstart')){
                    scrollargs.start = current.data('triggerstart');
                }else{
                    scrollargs.start = "top 92%";
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

        });
        if(scrolledfind){
            document.addEventListener('lazyloaded', function(e){
                ScrollTrigger.refresh();
            });
        }
    }

    if($('.rh-reveal-wrap').length > 0){
        $('.rh-reveal-wrap').each(function(){
             
            var revealwrap = $(this); 
            var revealcover = $(this).find(".rh-reveal-block");
            var revealcontent = $(this).find(".rh-reveal-cont"); 
            revealwrap.removeClass('prehidden');
            var tl = gsap.timeline({scrollTrigger: {trigger:revealwrap, start: "top 80%"}});
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
            $(this).find('img.lazyload').each(function(){
                var source = $(this).attr("data-src");
                $(this).attr("src", source).css({'opacity': '1'});
            });            
            if(revealcover.data('reveal-dir')=='lr'){
                tl.from(revealcover,{ duration:$coverspeed, scaleX: 0, transformOrigin: "left", delay: $coverdelay  });
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
        });
    }

    //mouse move
    if($('.rh-prlx-mouse').length > 0){
          
        $(".rh-prlx-mouse").each(function(index, element){

            var mouseargs = {};
            var curmouse = $(this);
            if(curmouse.data('prlx-cur') == "yes"){
                var objtrigger = curmouse;
            }else{
                var objtrigger = $('#content');
            }

            objtrigger.on("mousemove", function(event){
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
                    mouseargs.transformPerspective = 500;
                    mouseargs.transformOrigin = "center center";
                }

                mouseargs.ease = Power1.easeOut;            

                gsap.to(curmouse, mouseargs);
            });
            if(curmouse.data('prlx-rest') == "yes"){
                curmouse.on("mouseleave", function(event){
                    gsap.to(curmouse, {x:0, y:0, rotationY:0, rotationX:0, ease: Power1.easeOut});
                });
            }
        });  
    }

});

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
            vid[0].pause();
        }
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

function RHGetBasicTween(current){
    var anargs = {};
    var $duration = current.data('duration');
    var $duration = parseFloat($duration);
    anargs.duration = $duration;

    if(current.data('x')){
        anargs.x = current.data('x');
    }

    if(current.data('y')){
        anargs.y = current.data('y');
    }

    if(current.data('xo')){
        anargs.xPercent = current.data('xo');
    }

    if(current.data('yo')){
        anargs.yPercent = current.data('yo');
    }

    if(current.data('z')){
        anargs.z = current.data('z');
    }

    if(current.data('width')){
        anargs.width = current.data('width');
    }

    if(current.data('height')){
        anargs.height = current.data('height');
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
        if(anargs.autoAlpha == 0.01){
            anargs.autoAlpha = 0;
        }
    }
    if(current.data('bg')){
        anargs.backgroundColor = current.data('bg');
    }
    if(current.data('origin')){
        anargs.transformOrigin = current.data('origin');
    }
    if(current.data('ease')){
        var $ease = current.data('ease').split('-');
        anargs.ease = $ease[0]+'.'+$ease[1];
        if(anargs.ease === 'power0.none'){           
            anargs.ease = 'none';
        }
    }
    return anargs;
}