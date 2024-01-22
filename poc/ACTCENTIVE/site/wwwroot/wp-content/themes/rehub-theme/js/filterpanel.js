jQuery(document).ready(function($) {
   'use strict';
    //AJAX SORTING PANEL
    $('.re_filter_panel').on('click', '.re_filtersort_btn:not(.active)', function(e){
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
            'innerargs' : innerargs,
            'security' : rhscriptvars.filternonce     
        };
        $this.closest('ul').addClass('activeul'); 
        $this.addClass('re_loadingbefore');         
        activecontainer.addClass('sortingloading');

        $.ajax({
            type: "POST",
            url: rhscriptvars.ajax_url,
            data: data
        }).done(function(response){
            if (response !== 'fail') {                  
                if(activecontainer.parent().hasClass('rh-gsap-wrap') || activecontainer.parent().parent().hasClass('rh-gsap-wrap')){
                    var elapend = $(response);
                    activecontainer.html(elapend);
                    var current = activecontainer.closest('.rh-gsap-wrap');
                    var $batchobj = elapend.filter('.col_item');
                    var anargs = RHGetBasicTween(current);
                    RHBatchScrollTrigger(current, anargs, $batchobj);
                }else{
                    activecontainer.html($(response).hide().fadeIn(1000));
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
            $this.closest('.re_filter_panel').find('span').removeClass('active');
            $this.removeClass('re_loadingbefore').addClass('active');               
            activecontainer.removeClass('sortingloading'); 
            $this.closest('ul').removeClass('activeul'); 
            if($this.closest('ul').hasClass('re_tax_dropdown')){
                var taxDropdown = $this.closest('.re_tax_dropdown');
                taxDropdown.find('.rh_choosed_tax').html($this.html()).show().attr('data-taxdata', $this.attr('data-sorttype'));
                
                taxDropdown.find('.rh_tax_placeholder').hide();
                filterPanel.find('.re_filter_ul li:first-child span').addClass('active');
            }                
        });         
    });  

    //Collapse filters in sort panel
    $('.re_filter_panel').on('click', '.re_filter_ul .re_filtersort_btn.active', function(e) {
         e.preventDefault();
         $(this).closest('.re_filter_panel').find('ul.re_filter_ul span').toggleClass('showfiltermobile');
    });

    //Collapse filters in tax dropdown
    $('.re_tax_dropdown').on('click', '.label', function(e) {
        e.stopPropagation();
        e.preventDefault();
        $(this).closest('.re_tax_dropdown').toggleClass('active');
    }); 
    $( document ).on('click', function() {
        $( '.re_tax_dropdown' ).removeClass('active');
     }); 
}); 