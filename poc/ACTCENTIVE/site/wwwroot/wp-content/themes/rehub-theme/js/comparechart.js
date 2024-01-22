jQuery(document).ready(function($) {
	"use strict";
	//Compare multigroup functions
	if ($('#re-compare-bar').length > 0) {
		$.post(rhscriptvars.ajax_url, {
			action: 're_compare_panel',
			security: comparechart.comparenonce
		}, function (response) {
			//$('#re-compare-icon-fixed').addClass(response.cssactive);
			var pageids = response.pageids;
			var total_comparing_ids = response.total_comparing_ids;
			var pageid;
			var total_comparing_id;

			for (let index = 0; index < pageids.length; index++) {
				var pageid = pageids[index];
				$('.re-compare-wrap-'+ pageid).html(response.content[pageid]);
				$('.re-compare-tab-' + pageid + ' span').text(response.count[pageid]);
				$('.re-compare-tab-' + pageid).attr('data-comparing', response.comparing[pageid]);			
			}

			for (let index = 0; index < total_comparing_ids.length; index++){//Here we ensure to deactivate compare buttons if item is in compare
				var total_comparing_id = total_comparing_ids[index];
				var comparebtn = $('.addcompare-id-' + total_comparing_id);
				if(comparebtn.hasClass('not-incompare')) {
	        		comparebtn.removeClass('not-incompare').addClass('comparing'); 	
	        	}
			}
			
			pageid = $('#re-compare-bar-tabs').children("ul").children("li:first").attr("data-page");
			$('.re-compare-tab-' + pageid).attr('data-comparing', response.comparing[pageid]);

			var  total_count = response.total_count;
			if( '' == total_count ) {
				total_count = 0;
			}
			if (total_count > 0){
				if($('.top_chart').length > 0){

				}
				else{
					$('#re-compare-icon-fixed').removeClass('rhhidden');
				}
				$('.re-compare-icon-toggle .re-compare-notice').text(total_count);
			}				
			
			var compareurl = $('#re-compare-bar-tabs').children("ul").children("li:first").attr("data-url");
			var comparing = $('#re-compare-bar-tabs').children("ul").children("li:first").attr("data-comparing");
			
			comparing = (comparing) ? '?compareids=' + comparing : '';
			$('.re-compare-destin').attr('data-compareurl', compareurl + comparing); 
			
			$('#re-compare-bar-tabs').children("ul").children("li").on("click", function(){
				pageid = parseInt($(this).attr("data-page"));
				compareurl = $(this).attr("data-url");
				comparing = $(this).attr("data-comparing");
				comparing = (comparing) ? '?compareids=' + comparing : '';
				$('.re-compare-destin').attr('data-compareurl', compareurl + comparing); 
			});			
		});      
	}

	//compare multigroup button
	$(document).on('click', '.wpsm-button-new-compare', function(e){
	  	var thistoggle = $(this);
	  	var panel = $('#re-compare-bar');       
	  	var compareID = thistoggle.data('addcompare-id');
	  	var alltoggles = $('.addcompare-id-' + compareID); 
	  	alltoggles.addClass('loading');
	  	if(thistoggle.hasClass('not-incompare')) {       
	     	$.post(rhscriptvars.ajax_url, {
	        	action: 're_add_compare',
	        	compareID: compareID,
	        	perform: 'add',
	        	security: comparechart.comparenonce
	     	}, function (response) {   
	        	//panel.addClass('active'); 
	        	alltoggles.removeClass('not-incompare').removeClass('loading');
	        	alltoggles.addClass('comparing'); 
	        	if($('.top_chart').length > 0){

				}
				else{
					$('#re-compare-icon-fixed').removeClass('rhhidden');
				}
			
	        	$('.re-compare-wrap-' + response.pageid).append(response.content).find(".re-compare-item:last").hide().fadeIn('slow');
	        	$('.re-compare-tab-' + response.pageid+' span').text(response.count);
				$('.re-compare-tab-' + response.pageid).attr('data-comparing', response.comparing);

				var  total_count = $('.re-compare-icon-toggle .re-compare-notice').first().text();
				$('.re-compare-icon-toggle .re-compare-notice').text(parseInt(total_count) + 1);				
			
				var compareurl = $('.re-compare-tab-' + response.pageid).data('url');
				$('.re-compare-destin').attr('data-compareurl', compareurl + '?compareids=' + response.comparing); 

				$('.re-compare-icon-toggle').addClass('proccessed');
				setTimeout(function() {
				   $('.re-compare-icon-toggle').removeClass('proccessed');
				}, 300);				

	     	}); 
	  	} else {
	     	$('.compare-item-' + compareID).css({'opacity': '.17'});         
	     	$.post(rhscriptvars.ajax_url, {
	        	action: 're_add_compare',
	        	compareID: compareID,
	        	perform: 'remove',
	        	security: comparechart.comparenonce
	     	}, function (response) {
	        	alltoggles.addClass('not-incompare');
	        	alltoggles.removeClass('comparing').removeClass('loading');
			
	        	$('.compare-item-' + compareID).remove(); 
	        	$('.re-compare-tab-' + response.pageid + ' span').text(response.count);
				$('.re-compare-tab-' + response.pageid).attr('data-comparing', response.comparing);

				var total_count = $('.re-compare-icon-toggle .re-compare-notice').first().text();
				$('.re-compare-icon-toggle .re-compare-notice').text(parseInt(total_count) - 1);
			
				var compareurl = $('.re-compare-tab-' + response.pageid).data('url'); 
			
	        	if(total_count <= 1) {
	           		panel.removeClass('active');
	           		$('#re-compare-icon-fixed').addClass('rhhidden');
	        	}

	        	$('.re-compare-destin').attr('data-compareurl', compareurl + '?compareids=' + response.comparing); 

				$('.re-compare-icon-toggle').addClass('proccessed');
				setTimeout(function() {
				   $('.re-compare-icon-toggle').removeClass('proccessed');
				}, 300);

	     	});                
	  	} 
	});  

	//Compare multigroup close button
	$('body').on('click', '.re-compare-new-close', function(e){
	  	var block = $(this).parent();
	  	var panel = $('#re-compare-bar');       
	  	var compareID = block.data('compareid');
	  	var alltoggles = $('.addcompare-id-' + compareID);
	  	block.css({'opacity': '.17'});
	  	$.post(rhscriptvars.ajax_url, {
	     	action: 're_add_compare',
	     	compareID: compareID,
	     	perform: 'remove',
	     	security: comparechart.comparenonce    
	  	}, function (response) { 
	     	alltoggles.addClass('not-incompare').removeClass('comparing');           
	     	block.remove(); 
		 
	     	$('.re-compare-tab-' + response.pageid + ' span').text(response.count);
		 	$('.re-compare-tab-' + response.pageid).attr('data-comparing', response.comparing);

			var  total_count = $('.re-compare-icon-toggle .re-compare-notice').first().text();
			$('.re-compare-icon-toggle .re-compare-notice').text(parseInt(total_count) - 1);		 	
		 
			var compareurl = $('.re-compare-tab-' + response.pageid).data('url'); 
			var comparing = $('.re-compare-tab-' + response.pageid).data('comparing');
		 
	    	if(total_count <= 1) {
	        	panel.removeClass('active');
	        	$('#re-compare-icon-fixed').addClass('rhhidden');
	    	}
	    	$('.re-compare-destin').attr('data-compareurl', compareurl + '?compareids=' + response.comparing);         
	  	});   
	}); 

	// Compare multigroup click button
	$( 'body' ).on("click", ".re-compare-destin", function(e){
	  	var $this = $(this);
	  	var $error = 0;
	  
	  	let iftab = $this.closest('#re-compare-bar-tabs');
		if(iftab.length > 0){
			var check_tab = $( "#re-compare-bar-tabs ul li.current span" );
			if( '0' == check_tab.text() ) {
				$this.after('<p class="re-compare-error">'+ comparechart.item_error_add +'</p>');
				$error = 1;
			} else if( '1' == check_tab.text() ) {
				$this.after('<p class="re-compare-error">'+ comparechart.item_error_comp +'</p>');
				$error = 1;
			}
		}
		setTimeout(function() {
		   	$('p.re-compare-error').remove();
		}, 4500);
	  
	  	var compareurl = $this.attr('data-compareurl'); 
	  	if( compareurl != "" && $error == 0 ){
	     	window.location.href= compareurl;
	  	}
	}); 

	$("#re-compare-bar-tabs").lightTabs();

	$(document).on('click', '.re-compare-icon-toggle, #re-compare-icon-fixed', function(event){
		event.preventDefault();
		$('#re-compare-bar').addClass('active');
	});	

   
   //Compare close button in chart
   $(document).on('click touchstart', '.re-compare-close-in-chart', function(e){
      var block = $(this).closest('.top_rating_item'); 
      $(this).closest('.table_view_charts').find('li').removeClass('row-is-different');      
      var compareID = block.data('compareid');    
      var alltoggles = $('.addcompare-id-' + compareID);  
      block.css({'opacity': '.17'});
      $.post(rhscriptvars.ajax_url, {
         action: 're_add_compare',
         compareID: compareID,
         perform: 'remove',
         security: comparechart.comparenonce    
      }, function (response) {           
         block.remove();
         table_charts();
	     alltoggles.addClass('not-incompare');
	     alltoggles.removeClass('comparing').removeClass('loading');  
		$('.compare-item-' + compareID).remove(); 
		$('.re-compare-tab-' + response.pageid + ' span').text(response.count);
		$('.re-compare-tab-' + response.pageid).attr('data-comparing', response.comparing);

		var total_count = $('.re-compare-icon-toggle .re-compare-notice').first().text();
		$('.re-compare-icon-toggle .re-compare-notice').text(parseInt(total_count) - 1);

		var compareurl = $('.re-compare-tab-' + response.pageid).data('url'); 

		if($('#re-compare-bar-tabs div ').length == 0) {
				panel.removeClass('active');
		} else { 
			$('.re-compare-destin').attr('data-compareurl', compareurl + '?compareids=' + response.comparing);          
		}

		$('.re-compare-icon-toggle').addClass('proccessed');
		setTimeout(function() {
		   $('.re-compare-icon-toggle').removeClass('proccessed');
		}, 300);	            
         if (typeof (history.pushState) != "undefined") {
            var obj = { Page: 'Compare items', Url: window.location.pathname + '?compareids=' + response.comparing };
            history.pushState(obj, obj.Page, obj.Url);
         } else {
            window.location.href= window.location.pathname + '?compareids=' + response.comparing;
         }
         window.location.reload();                                     
      }); 
                 
   });	

});

(function($){				
	jQuery.fn.lightTabs = function(options){
		var createTabs = function(){
			tabs = this;
			i = 0;
			showPage = function(i){
				$(tabs).children("div").children("div").hide();
				$(tabs).children("div").children("div").eq(i).show();
				$(tabs).children("ul").children("li").removeClass("current");
				$(tabs).children("ul").children("li").eq(i).addClass("current");
			}	
			showPage(0);
			$(tabs).children("ul").children("li").each(function(index, element){
				$(element).attr("data-id", i);
				i++;                        
			});
			$(tabs).children("ul").children("li").on("click", function(){
				showPage(parseInt($(this).attr("data-id")));
			});				
		};		
		return this.each(createTabs);
	};	
})(jQuery);

(function(window) {

	'use strict';

	var dataSearch = document.getElementById('compare_search_data');

	if( dataSearch == null ){
		return;
	}

	var mainContainer = document.querySelector('.rh-outer-wrap'),		
		openCtrl = document.getElementById('btn_search'),
		closeCtrl = document.getElementById('btn_search_close'),
		searchContainer = document.querySelector('.comp-search'),
		inputSearch = searchContainer.querySelector('.comp-search-input'),
		outputSearch = searchContainer.querySelector('.comp-ajax-search-wrap');

	function runCompareSearch() {
		initEvents();	
	}

	function initEvents() {
		openCtrl.addEventListener('click', openSearch);
		closeCtrl.addEventListener('click', closeSearch);
		document.addEventListener('keyup', function(ev) {
			// escape key.
			if( ev.keyCode == 27 ) {
				closeSearch();
			}
			
			setTimeout(function(){
				doSearch(inputSearch);
			}, 100);
		});
	}

	function openSearch() {
		mainContainer.classList.add('rh-outer-wrap-move');
		searchContainer.classList.add('comp-search-open');
		setTimeout(function() {
			inputSearch.focus();
		}, 600);
	}

	function closeSearch() {
		mainContainer.classList.remove('rh-outer-wrap-move');
		outputSearch.classList.remove('comp-ajax-search-open');
		outputSearch.innerHTML = "";
		searchContainer.classList.remove('comp-search-open');
		inputSearch.blur();
		inputSearch.value = '';
	}
	
    function doSearch(elem) {
        var posttype = dataSearch.dataset.posttype;
        var terms = dataSearch.dataset.terms;
		var taxonomy = dataSearch.dataset.taxonomy;
        var search_query = elem.value;
		
        if (search_query == '') {
			outputSearch.innerHTML = "";
            return;
        }
		
		jQuery('#btn_search_close i').attr('class', 'rhicon fa-spin rhi-spinner-third');
		
        jQuery.ajax({
            type: 'POST',
            url: rhscriptvars.ajax_url,
            data: {
                action: 'add_to_compare_search',
                search_query : search_query,
                posttype : posttype,
                terms : terms,
				taxonomy : taxonomy,
				security: comparechart.comparenonce
            },
            success: function(data, textStatus, XMLHttpRequest){

				var responseObject = JSON.parse(data);
				outputSearch.innerHTML = responseObject.compare_html;
				
				if (window.innerHeight < 700){
				  outputSearch.classList.add('comp-ajax-search-overflow');
				}
				jQuery('#btn_search_close i').attr('class', 'rhicon rhi-times');
				outputSearch.classList.add('comp-ajax-search-open');
            },
            error: function(MLHttpRequest, textStatus, errorThrown){
                console.log(errorThrown);
            }
        });
    }

	runCompareSearch();

})(window);