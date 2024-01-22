jQuery(document).ready(function($) {
   'use strict';
    //AJAX PAGINATION on click button
    $(document).on('click', '.re_ajax_pagination_btn', function(e){
        e.preventDefault();
        var $this = $(this);        
        var containerid = $this.data('containerid');
        var activecontainer = $('#'+containerid);       
        var sorttype = $this.data('sorttype');
        var filterPanel = activecontainer.siblings('.re_filter_panel');
        var choosenTax = filterPanel.find('.re_tax_dropdown .rh_choosed_tax');
        var tax;
        if(choosenTax.length > 0 && choosenTax.html() != ''){
            tax = choosenTax.attr('data-taxdata');
            if(tax){
                tax = JSON.parse(tax);
            }
        }
        var offset = $this.data('offset');  
        var filterargs = activecontainer.data('filterargs');
        var innerargs = activecontainer.data('innerargs');
        var template = activecontainer.data('template');           
        var data = {
            'action': 're_filterpost',          
            'sorttype': sorttype,
            'filterargs' : filterargs,
            'tax': tax,
            'template' : template, 
            'containerid' : containerid,
            'offset' : offset, 
            'innerargs' : innerargs,
            'security' : rhscriptvars.filternonce     
        };
        $this.parent().find('span').removeClass('active_ajax_pagination');
        $this.addClass('active_ajax_pagination');

        $.ajax({
            type: "POST",
            url: rhscriptvars.ajax_url,
            data: data
        }).done(
            function(response){
                if (response !== 'fail') {
                    activecontainer.find('.re_ajax_pagination').remove();                   
                    if(activecontainer.parent().hasClass('rh-gsap-wrap') || activecontainer.parent().parent().hasClass('rh-gsap-wrap')){
                        var elapend = $(response);
                        activecontainer.append(elapend);
                        var current = activecontainer.closest('.rh-gsap-wrap');
                        var $batchobj = elapend.filter('.col_item');
                        var anargs = RHGetBasicTween(current);
                        RHBatchScrollTrigger(current, anargs, $batchobj);
                    }else{
                        activecontainer.append($(response).hide().fadeIn(1000));
                    }
                    activecontainer.find('.radial-progress').each(function(){
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

                    activecontainer.find('.wpsm-tooltip').each(function(){
                        $(this).tipsy({gravity: "s", fade: true, html: true });
                    });

                    activecontainer.find('.wpsm-bar').each(function(){
                        $(this).find('.wpsm-bar-bar').animate({ width: $(this).attr('data-percent') }, 1500 );
                    });

                    activecontainer.find('.countdown_dashboard').each(function(){
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

                }        
            }
        );         
    });    

    //AJAX PAGINATION infinite scroll on inview
    $(document).on('inview', '.re_aj_pag_auto_wrap .re_ajax_pagination_btn', function(e){
        e.preventDefault();
        var $this = $(this);        
        var containerid = $this.data('containerid');
        var activecontainer = $('#'+containerid);       
        var sorttype = $this.data('sorttype');
        var offset = $this.data('offset');  
        var filterPanel = activecontainer.siblings('.re_filter_panel');
        var choosenTax = filterPanel.find('.re_tax_dropdown .rh_choosed_tax');
        var tax;
        if(choosenTax.length > 0 && choosenTax.html() != ''){
            tax = choosenTax.attr('data-taxdata');
            if(tax){
                tax = JSON.parse(tax);
            }
        }
        var filterargs = activecontainer.data('filterargs');
        var innerargs = activecontainer.data('innerargs');          
        var template = activecontainer.data('template');           
        var data = {
            'action': 're_filterpost',          
            'sorttype': sorttype,
            'filterargs' : filterargs,
            'template' : template, 
            'tax': tax,
            'containerid' : containerid,
            'offset' : offset,   
            'innerargs' : innerargs,
            'security' : rhscriptvars.filternonce     
        };
        $this.parent().find('span').removeClass('re_ajax_pagination_btn');
        $this.parent().find('span').removeClass('active_ajax_pagination');
        $this.addClass('active_ajax_pagination');

        $.ajax({
            type: "POST",
            url: rhscriptvars.ajax_url,
            data: data
        }).done(
            function(response){
                if (response !== 'fail') {
                    activecontainer.find('.re_ajax_pagination').remove();   
                    if(activecontainer.parent().hasClass('rh-gsap-wrap') || activecontainer.parent().parent().hasClass('rh-gsap-wrap')){
                        var elapend = $(response);
                        activecontainer.append(elapend);
                        var current = activecontainer.closest('.rh-gsap-wrap');
                        var $batchobj = elapend.filter('.col_item');
                        var anargs = RHGetBasicTween(current);
                        RHBatchScrollTrigger(current, anargs, $batchobj);
                    }else{
                        activecontainer.append($(response).hide().fadeIn(1000));
                    }       
                    activecontainer.find('.radial-progress').each(function(){
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

                    activecontainer.find('.wpsm-tooltip').each(function(){
                        $(this).tipsy({gravity: "s", fade: true, html: true });
                    });

                    activecontainer.find('.wpsm-bar').each(function(){
                        $(this).find('.wpsm-bar-bar').animate({ width: $(this).attr('data-percent') }, 1500 );
                    });

                    activecontainer.find('.countdown_dashboard').each(function(){
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

                }        
            }
        );         
    });   
}); 