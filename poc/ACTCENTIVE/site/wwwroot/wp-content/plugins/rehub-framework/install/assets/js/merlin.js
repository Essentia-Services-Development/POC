
var Merlin = (function($){

    var t;

    // callbacks from form button clicks.
    var callbacks = {
		save_settings: function(btn){
			var installer = new saveSettings(btn);
			installer.init(btn);
		},
        activate_license: function(btn) {
            var license = new ActivateLicense();
            license.init(btn);
        },
        install_plugins: function(btn){
            var plugins = new PluginManager();
            plugins.init(btn);
        },
        install_content: function(btn){
            var content = new ContentManager();
            content.init(btn);
        }
    };

    function window_loaded(){
    	var 
    	body 		= $('.merlin__body'),
    	body_loading 	= $('.merlin__body--loading'),
    	body_exiting 	= $('.merlin__body--exiting'),
    	drawer_trigger 	= $('#merlin__drawer-trigger'),
    	drawer_opening 	= 'merlin__drawer--opening';
    	drawer_opened 	= 'merlin__drawer--open';

    	setTimeout(function(){
	        body.addClass('loaded');
	    },100); 

    	drawer_trigger.on('click', function(){
        	body.toggleClass( drawer_opened );
        });

    	$('.merlin__button--proceed:not(.merlin__button--closer)').click(function (e) {
		    e.preventDefault();
		    var goTo = this.getAttribute("href");

		    body.addClass('exiting');

		    setTimeout(function(){
		        window.location = goTo;
		    },400);       
		});

        $(".merlin__button--closer").on('click', function(e){

        	body.removeClass( drawer_opened );

            e.preventDefault();
		    var goTo = this.getAttribute("href");

		    setTimeout(function(){
		        body.addClass('exiting');
		    },600);   
		    
		    setTimeout(function(){
		        window.location = goTo;
		    },1100);   
        });

        $(".button-next").on( "click", function(e) {
            e.preventDefault();
            var loading_button = merlin_loading_button(this);
            if ( ! loading_button ) {
                return false;
            }
            var data_callback = $(this).data("callback");
            if( data_callback && typeof callbacks[data_callback] !== "undefined"){
                // We have to process a callback before continue with form submission.
                callbacks[data_callback](this);
                return false;
            } else {
                return true;
            }
        });
    }
	
	function PluginManager(){

    	var body = $('.merlin__body');
        var complete;
        var items_completed 	= 0;
        var current_item 		= "";
        var $current_node;
        var current_item_hash 	= "";

        function ajax_callback(response){
            var currentSpan = $current_node.find("label");
            if(typeof response === "object" && typeof response.message !== "undefined"){
                currentSpan.removeClass( 'installing success error' ).addClass(response.message.toLowerCase());

                // The plugin is done (installed, updated and activated).
                if(typeof response.done != "undefined" && response.done){
                    find_next();
                }else if(typeof response.url != "undefined"){
                    // we have an ajax url action to perform.
                    if(response.hash == current_item_hash){
                        currentSpan.removeClass( 'installing success' ).addClass("error");
                        find_next();
                    }else {
                        current_item_hash = response.hash;
                        jQuery.post(response.url, response, ajax_callback).fail(ajax_callback);
                    }
                }else{
                    // error processing this plugin
                    find_next();
                }
            }else{
                // The TGMPA returns a whole page as response, so check, if this plugin is done.
                process_current();
            }
        }

        function process_current(){
            if(current_item){
                var $check = $current_node.find("input:checkbox");
                if($check.is(":checked")) {
                    jQuery.post(install_params.ajaxurl, {
                        action: "merlin_plugins",
                        wpnonce: install_params.wpnonce,
                        slug: current_item,
                    }, ajax_callback).fail(ajax_callback);
                }else{
                    $current_node.addClass("skipping");
                    setTimeout(find_next,300);
                }
            }
        }

        function find_next(){
            if($current_node){
                if(!$current_node.data("done_item")){
                    items_completed++;
                    $current_node.data("done_item",1);
                }
                $current_node.find(".spinner").css("visibility","hidden");
            }
            var $li = $(".merlin__drawer--install-plugins li");
            $li.each(function(){
                var $item = $(this);

                if ( $item.data("done_item") ) {
                    return true;
                }

                current_item = $item.data("slug");
                $current_node = $item;
                process_current();
                return false;
            });
            if(items_completed >= $li.length){
                // finished all plugins!
                complete();
            }
        }

        return {
            init: function(btn){
                $(".merlin__drawer--install-plugins").addClass("installing");
                $(".merlin__drawer--install-plugins").find("input").prop("disabled", true);
                complete = function(){

                	setTimeout(function(){
				        $(".merlin__body").addClass('js--finished');
				    },1000);

                	body.removeClass( drawer_opened );

                	setTimeout(function(){
				        $('.merlin__body').addClass('exiting');
				    },3000);

                    setTimeout(function(){
				        window.location.href=btn.href;
				    },3500);

                };
                find_next();
            }
        }
    }

    function saveSettings() {
    	var body = $('.merlin__body');
		var complete;
        var notice = $(".notice-text");

        function ajax_callback(r) {
            
            if (typeof r.done !== "undefined") {
            	setTimeout(function(){
			        notice.addClass("lead");
			    },0); 
			    setTimeout(function(){
			        notice.addClass("success");
			        notice.html(r.message);
			    },600); 
                complete();
            } else {
                notice.addClass("lead error");
                notice.html(r.error);
            }
        }

        function do_ajax() {
			var params = {
                action: "rehub_save_installer",
                wpnonce: install_params.wpnonce,
				}
			jQuery('ul.merlin__drawer--import-content').find('input, select').each(function(key, fields){
				
				switch(jQuery(this).attr('type')){
					case 'text':
					case 'hidden':
						params[jQuery(this).attr('name')] = jQuery(this).val();
					break;
					case 'checkbox':
						if(jQuery(this).prop('checked')==true){
							params[jQuery(this).attr('name')] = 1;
						}else{
							params[jQuery(this).attr('name')] = 0;
						}
					break;
					default: 
						params[jQuery(this).attr('name')] = jQuery(this).val();
					break;
				}
			});
            jQuery.post(install_params.ajaxurl, params, ajax_callback).fail(ajax_callback);
        }

        return {
            init: function(btn) {
                complete = function() {

                	setTimeout(function(){
							$(".merlin__body").addClass('js--finished');
						},1500);

                	body.removeClass( drawer_opened );

                	setTimeout(function(){
							$('.merlin__body').addClass('exiting');
						},3500);   

                    	setTimeout(function(){
							window.location.href=btn.href;
						},4000);
		    
                };
                do_ajax();
            }
        }
    }
    
    function merlin_loading_button( btn ){

        var $button = jQuery(btn);

        if ( $button.data( "done-loading" ) == "yes" ) {
        	return false;
        }

        var completed = false;

        var _modifier = $button.is("input") || $button.is("button") ? "val" : "text";
        
        $button.data("done-loading","yes");
        
        $button.addClass("merlin__button--loading");

        return {
            done: function(){
                completed = true;
                $button.attr("disabled",false);
            }
        }

    }

    return {
        init: function(){
            t = this;
            $(window_loaded);
        },
        callback: function(func){
            console.log(func);
            console.log(this);
        }
    }

})(jQuery);

Merlin.init();

jQuery(document).ready(function($) {
    if ($('.set_custom_images').length > 0) {
        if ( typeof wp !== 'undefined' && wp.media && wp.media.editor) {
            $(document).on('click', '.set_custom_images', function(e) {
                e.preventDefault();
                var button = $(this);
                var id = button.parents('li').find('#process_custom_images');

                // Define image_frame as wp.media object
                image_frame = wp.media({
                           title: 'Select Logo',
                           multiple : false,
                           library : {
                                type : 'image',
                            }
                       });
                image_frame.open(button);
                image_frame.on('close',function() {
                  // On close, get selections and save to the hidden input
                  // plus other AJAX stuff to refresh the image preview
					var attachment = image_frame.state().get('selection').first().toJSON();
                    id.val(attachment.url);
                    button.parents('li').find('img').remove();
                    button.parents('li').append('<img src="'+attachment.url+'" class="rehub_install_logo_preview">');
               });
               return false;
            });
        }
    }
	$('#rehub_design_selector').change(function(){
		var selectCurrentDesign = $(this).val();
		selectCurrentDesign = selectCurrentDesign.toLowerCase();
		$(this).parents('li').find('#design-'+selectCurrentDesign).siblings('img').hide();
		$(this).parents('li').find('#design-'+selectCurrentDesign).fadeIn();
	});
	$('#rehub-type-select').change(function(){
		var selectCurrentType = $(this).val();
		var finishLink = $('#finish').attr('href');
		$('#finish').attr('href', finishLink+'&type='+selectCurrentType);
	});
});

(function( $ ) {
 
    // Add Color Picker to all inputs that have 'color-field' class
    $(function() {
        $('.color-field').wpColorPicker();
    });
     
})( jQuery );