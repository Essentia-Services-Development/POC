/**
*
* JavaScript file that handles public side JS
*
*/

(function($){

	"use strict";

	$( document ).ready(function() {

		// Integrations
		// Amedeo theme
		$('body.theme-amedeo').on('click', '.eltdf-quantity-minus', function(e){
			let input = $(this).parent().find('input');
			let minval = $(input).data('min');
			let stepval = $(input).data('step');
			let curval = $(input).val();
			if ( (curval-stepval) < minval){
				e.preventDefault();
				e.stopPropagation();
			}
		});
		// Riode
		// plus minus buttons follow step
		$('body.theme-riode').on('click', '.quantity-plus', function(e){
			//e.preventDefault();
			let input = $(this).parent().find('input');
			let currentval = $(input).val();
			$(input).val(parseInt(currentval)-1);

			input[0].stepUp(1);
			$(input).trigger('input');

		});
		$('body.theme-riode').on('click', '.quantity-minus', function(e){
			//e.preventDefault();
			let input = $(this).parent().find('input');
			let currentval = $(input).val();
			$(input).val(parseInt(currentval)+1);

			input[0].stepDown(1);
			$(input).trigger('input');
		});
		jQuery('body.theme-riode').on('click', '.add_to_cart_button', function(e){
			var qty = jQuery(this).parent().find('input[name="quantity"]').val();
			jQuery(this).attr('data-quantity', qty);
		});
		jQuery('body.theme-riode').on('click', '.d-icon-plus', function(e){
			var qty = jQuery(this).parent().find('input[name="quantity"]').val();
			jQuery(this).parent().parent().find('.add_to_cart_button').attr('data-quantity', qty);
		});
		jQuery('body.theme-riode').on('click', '.d-icon-minus', function(e){
			var qty = jQuery(this).parent().find('input[name="quantity"]').val();
			jQuery(this).parent().parent().find('.add_to_cart_button').attr('data-quantity', qty);
		});
		


		var isIndigo = $('#b2bking_indigo_order_form').val();
		var isCream = $('#b2bking_indigo_order_form.b2bking_cream_order_form').val();

		/* Fix for country selector SCROLL ISSUE in popup (e.g. login in Flatsome theme) */
		$('.b2bking_country_field_selector select').on('select2:open', function (e) {
	        const evt = "scroll.select2";
	        $(e.target).parents().off(evt);
	        $(window).off(evt);
	      });

		/* Conversations START */

		// On load conversation, scroll to conversation end
		// if conversation exists
		if ($('#b2bking_conversation_messages_container').length){
			$("#b2bking_conversation_messages_container").scrollTop($("#b2bking_conversation_messages_container")[0].scrollHeight);
		}

		// On clicking "Send message" inside conversation in My account
		$('body').on('click', '#b2bking_conversation_message_submit', function(){
			// loader icon
			$('<img class="b2bking_loader_icon_button" src="'+b2bking_display_settings.loadertransparenturl+'">').insertBefore('.b2bking_myaccount_conversation_endpoint_button_icon');
			$('.b2bking_myaccount_conversation_endpoint_button_icon').remove();
			// Run ajax request
			var datavar = {
	            action: 'b2bkingconversationmessage',
	            security: b2bking_display_settings.security,
	            message: $('#b2bking_conversation_user_new_message').val(),
	            conversationid: $('#b2bking_conversation_id').val(),
	        };

			$.post(b2bking_display_settings.ajaxurl, datavar, function(response){
				location.reload();
			});
		});

		// On clicking "New conversation" button
		$('body').on('click', '#b2bking_myaccount_make_inquiry_button', function(){
			// hide make inquiry button
			$('#b2bking_myaccount_make_inquiry_button').css('display','none');
			// hide conversations
			$('.b2bking_myaccount_individual_conversation_container').css('display','none');
			// hide conversations pagination
			$('.b2bking_myaccount_conversations_pagination_container').css('display','none');
			// show new conversation panel
			$('.b2bking_myaccount_new_conversation_container').css('display','block');
		});

		// On clicking "Close X" button
		$('body').on('click', '.b2bking_myaccount_new_conversation_close', function(){
			// hide new conversation panel
			$('.b2bking_myaccount_new_conversation_container').css('display','none');
			// show new conversation button
			$('#b2bking_myaccount_make_inquiry_button').css('display','inline-flex');
			// show conversations
			$('.b2bking_myaccount_individual_conversation_container').css('display','block');
			// show pagination
			$('.b2bking_myaccount_conversations_pagination_container').css('display','flex');
			
		});

		// On clicking "Send inquiry" button
		$('body').on('click', '#b2bking_myaccount_send_inquiry_button', function(){
			// if textarea empty OR title empty
			if (!$.trim($("#b2bking_myaccount_textarea_conversation_start").val()) || !$.trim($("#b2bking_myaccount_title_conversation_start").val())) {
				// Show "Text area or title is empty" message
			} else {
				// loader icon
				$('<img class="b2bking_loader_icon_button" src="'+b2bking_display_settings.loadertransparenturl+'">').insertBefore('.b2bking_myaccount_start_conversation_button_icon');
				$('.b2bking_myaccount_start_conversation_button_icon').remove();
				// Run ajax request
				var datavar = {
		            action: 'b2bkingsendinquiry',
		            security: b2bking_display_settings.security,
		            message: $('#b2bking_myaccount_textarea_conversation_start').val(),
		            title: $('#b2bking_myaccount_title_conversation_start').val(),
		            type: $("#b2bking_myaccount_conversation_type").children("option:selected").val(),
		        };

		        // If DOKAN addon exists, pass vendor
		        if (typeof b2bkingdokan_display_settings !== 'undefined') {
		        	datavar.vendor = $('#b2bking_myaccount_conversation_vendor').val();
		        }

		        // If WCFM addon exists, pass vendor
		        if (typeof b2bkingwcfm_display_settings !== 'undefined') {
		        	datavar.vendor = $('#b2bking_myaccount_conversation_vendor').val();
		        }

		        // If MarketKing addon exists, pass vendor
		        if (typeof marketking_display_settings !== 'undefined') {
		        	datavar.vendor = $('#b2bking_myaccount_conversation_vendor').val();
		        }



				$.post(b2bking_display_settings.ajaxurl, datavar, function(response){
					// redirect to conversation
					window.location = response;
				});
			}
		});

		/* Conversations END */

		/* Request a custom quote START*/

		// On clicking "Request a Custom Quote" button
		$('body').on('click', '#b2bking_request_custom_quote_button', function(){

			// If DOKAN addon exists
			if (typeof b2bkingdokan_display_settings !== 'undefined') {
				// check number of vendors
				var vendors = [];
				$('.variation dd.variation-Vendor').each(function(){
					let value = $(this).text().trim();
					if (value.length !== 0){
						if (!vendors.includes(value)){
							vendors.push(value);
						}
					}
				});
				var vendorsNr = vendors.length;
				if (parseInt(vendorsNr) > 1){
					alert(b2bkingdokan_display_settings.request_many_vendors);
					return;
				}
			}

			// If WCFM addon exists
			if (typeof b2bkingwcfm_display_settings !== 'undefined') {
				// check number of vendors
				var vendors = [];
				$('.variation dd.variation-Store').each(function(){
					let value = $(this).text().trim();
					if (value.length !== 0){
						if (!vendors.includes(value)){
							vendors.push(value);
						}
					}
				});

				if (vendors.length == 0){
					// try different structure
					$('.wcfm_dashboard_item_title').each(function(){
						let value = $(this).text().trim();
						if (value.length !== 0){
							if (!vendors.includes(value)){
								vendors.push(value);
							}
						}
					});
				}

				var vendorsNr = vendors.length;
				if (parseInt(vendorsNr) > 1){
					alert(b2bkingwcfm_display_settings.request_many_vendors);
					return;
				}
			}

			// If MarketKing addon exists
			if (typeof marketking_display_settings !== 'undefined') {
				// check number of vendors
				var vendorsNr = $('#marketking_number_vendors_cart').val();
				if (parseInt(vendorsNr) > 1){
					alert(marketking_display_settings.request_many_vendors);
					return;
				}
			}

			// show hidden elements above the button
			$('#b2bking_request_custom_quote_textarea, #b2bking_request_custom_quote_textarea_abovetext, .b2bking_custom_quote_field_container, .b2bking_request_custom_quote_text_label, #b2bking_request_custom_quote_name, #b2bking_request_custom_quote_email, .b2bking_custom_quote_field').css('display','block');
			// replace the button text with "Send custom quote request"
			$('#b2bking_request_custom_quote_button').text(b2bking_display_settings.send_quote_request);

			// On clicking "Send custom quote request"
			$('#b2bking_request_custom_quote_button').addClass('b2bking_send_custom_quote_button');

			$('#b2bking_request_custom_quote_button').removeClass('b2bking_button_quote_shortcode');

		});

		$('body').on('click', '.b2bking_send_custom_quote_button', function(){
		
			var location = 'standard';
			if ($(this).hasClass('b2bking_shortcode_send')){
				location = 'shortcode';
			}

			// if no fields are empty
			let empty = 'no';
			if ($('#b2bking_request_custom_quote_name').val() === '' || $('#b2bking_request_custom_quote_email').val() === ''){
				empty = 'yes';		
			}
			// check all custom fields
			var requiredids = jQuery('#b2bking_quote_required_ids').val();
			let requiredidssplit = requiredids.split(',');
			requiredidssplit.forEach(function(item){
				if ($('#b2bking_field_'+item).val() === ''){
					empty = 'yes';
				}
			});

			if (empty === 'no'){

				// validate email
				if (validateEmail($('#b2bking_request_custom_quote_email').val())){

					
					// run ajax request
					var quotetextids = jQuery('#b2bking_quote_text_ids').val();
					var quotecheckboxids = jQuery('#b2bking_quote_checkbox_ids').val();
					var quotefileids = jQuery('#b2bking_quote_file_ids').val();

					let quotetextidssplit = quotetextids.split(',');
					let quotecheckboxidssplit = quotecheckboxids.split(',');
					let quotefileidssplit = quotefileids.split(',');

					var datavar = {
			            action: 'b2bkingrequestquotecart',
			            security: b2bking_display_settings.security,
			            message: jQuery('#b2bking_request_custom_quote_textarea').val(),
			            name: jQuery('#b2bking_request_custom_quote_name').val(),
			            email: jQuery('#b2bking_request_custom_quote_email').val(),
			            title: b2bking_display_settings.custom_quote_request,
			            location: location,
			            type: 'quote',
			        };

			        datavar.quotetextids = quotetextids;
			        datavar.quotecheckboxids = quotecheckboxids;
			        datavar.quotefileids = quotefileids;

			        quotetextidssplit.forEach(function(item){
			        	let id = 'b2bking_field_'+item;
			        	datavar[id] = jQuery('#b2bking_field_'+item).val();
			        });

			        quotecheckboxidssplit.forEach(function(item){
			        	let id = 'b2bking_field_'+item;
			        	let value = '';

			        	jQuery('#b2bking_field_'+item+':checked').each(function() {
			        	   value+=jQuery(this).parent().find('span').text()+', ';
			        	});
			        	value = value.slice(0, -2);

			        	datavar[id] = value;
			        });

			        if (quotefileids !== ''){
			        	// if there are files
			        	var nroffiles = parseInt(quotefileidssplit.length);
			        	var currentnr = 1;
			        	if (currentnr <= nroffiles){
			        		quotefileidssplit.forEach(function(item, index, array){

			        			let id = 'b2bking_field_'+item;
			        			var fd = new FormData();
			        			var file = jQuery('#b2bking_field_'+item);
			        			var individual_file = file[0].files[0];
			        			fd.append("file", individual_file);
			        			fd.append('action', 'b2bkingquoteupload'); 
			        			fd.append('security', b2bking_display_settings.security); 

			        			// disable button to prevent double-clicks
			        			quote_button_loader();


			        			jQuery.ajax({
			        			    type: 'POST',
			        			    url: b2bking_display_settings.ajaxurl,
			        			    data: fd,
			        			    contentType: false,
			        			    processData: false,
			        			    success: function(response){
			        			        datavar[id] = response;
			        			        if (currentnr === nroffiles){
			        			        	// it is the last file

			        			        	// If MARKETKING addon exists, pass vendor
			        			        	if (typeof marketking_display_settings !== 'undefined') {
			        			        		datavar.vendor = $('#marketking_cart_vendor').val();
			        			        	}

	        			        	        // If DOKAN addon exists, pass vendor
	        			        	        if (typeof b2bkingdokan_display_settings !== 'undefined') {
	        			        	        	var vendors = [];
	        			        	        	$('.variation dd.variation-Vendor').each(function(){
	        			        	        		let value = $(this).text();
	        			        	        		if (!vendors.includes(value)){
	        			        	        			vendors.push(value);
	        			        	        		}
	        			        	        	});
	        			        	        	datavar.vendor = vendors[0];
	        			        	        }

	        			        	        // If WCFM addon exists, pass vendor
	        			        	        if (typeof b2bkingwcfm_display_settings !== 'undefined') {
	        			        	        	var vendors = [];
	        			        	        	$('.variation dd.variation-Store').each(function(){
	        			        	        		let value = $(this).text();
	        			        	        		if (!vendors.includes(value)){
	        			        	        			vendors.push(value);
	        			        	        		}
	        			        	        	});
	        			        	        	datavar.vendor = vendors[0];
	        			        	        }
	        			        	        if (datavar.vendor === undefined){
	        			        	        	// if nothing yet, check additional structures
	        			        	        	var vendors2 = [];
	        			        	        	$('.wcfm_dashboard_item_title').each(function(){
	        			        	        		let value = $(this).text();
	        			        	        		if (!vendors2.includes(value)){
	        			        	        			vendors2.push(value);
	        			        	        		}
	        			        	        	});
	        			        	        	datavar.vendor = vendors2[0];
	        			        	        }
	        			        	        
	        			        			$.post(b2bking_display_settings.ajaxurl, datavar, function(response){
	        			        				let conversationurl = response;

	        			        				// if user is logged in redirect to conversation, else show alert
	        			        				if($('#b2bking_request_custom_quote_name').length){
	        			        					alert(b2bking_display_settings.quote_request_success);
	        			        					$('#b2bking_request_custom_quote_button').css('display','none');
	        			        					location.reload();
	        			        				} else {
	        			        				    window.location = conversationurl;
	        			        				}
	        			        				
	        			        			});
			        			        }
			        			        currentnr++;
			        			    }
			        			});
			        		});

			        	}
			        } else {
			        	// no files

	        	        // If WCFM addon exists, pass vendor
	        	        if (typeof b2bkingwcfm_display_settings !== 'undefined') {
	        	        	var vendors = [];
	        	        	$('.variation dd.variation-Store').each(function(){
	        	        		let value = $(this).text();
	        	        		if (!vendors.includes(value)){
	        	        			vendors.push(value);
	        	        		}
	        	        	});
	        	        	datavar.vendor = vendors[0];
	        	        }

	        	        // if nothing yet, check additional structures
	        	        var vendors2 = [];
	        	        $('.wcfm_dashboard_item_title').each(function(){
	        	        	let value = $(this).text();
	        	        	if (!vendors2.includes(value)){
	        	        		vendors2.push(value);
	        	        	}
	        	        });
	        	        datavar.vendor = vendors2[0];

	        	        // If MARKETKING addon exists, pass vendor
			        	if (typeof marketking_display_settings !== 'undefined') {
			        		datavar.vendor = $('#marketking_cart_vendor').val();
			        	}

	        	        // If DOKAN addon exists, pass vendor
	        	        if (typeof b2bkingdokan_display_settings !== 'undefined') {
	        	        	var vendors = [];
	        	        	$('.variation dd.variation-Vendor').each(function(){
	        	        		let value = $(this).text();
	        	        		if (!vendors.includes(value)){
	        	        			vendors.push(value);
	        	        		}
	        	        	});
	        	        	datavar.vendor = vendors[0];
	        	        }

	        	        quote_button_loader();

	        			$.post(b2bking_display_settings.ajaxurl, datavar, function(response){
	        				let conversationurl = response;

	        				// if user is logged in redirect to conversation, else show alert
	        				if($('#b2bking_request_custom_quote_name').length || parseInt(b2bking_display_settings.quote_without_message) === 1){
	        					alert(b2bking_display_settings.quote_request_success);
	        					$('#b2bking_request_custom_quote_button').css('display','none');
	        					location.reload();
	        				} else {
	        				    window.location = conversationurl;
	        				}
	        				
	        			});
			        }

					
				} else {
					alert(b2bking_display_settings.quote_request_invalid_email);
				}
				
			} else {
				alert(b2bking_display_settings.quote_request_empty_fields);
			}
		});

		function validateEmail(email) {
			if ($('#b2bking_request_custom_quote_email').val() !== undefined){
				var regex = /^([a-zA-Z0-9_.+-])+\@(([a-zA-Z0-9-])+\.)+([a-zA-Z0-9]{2,4})+$/;
				return regex.test(email);
			} else {
				return true;
			}
		}

		function quote_button_loader(){
			jQuery('#b2bking_request_custom_quote_button').attr('disabled', true);
			jQuery('#b2bking_request_custom_quote_button').html(b2bking_display_settings.sending_please_wait);

			// potentially problematic on certain themes 
			/*
			// add loader
			var newbuttonhtml = '<img class="b2bking_loader_icon_button_quote" src="'+b2bking_display_settings.loadertransparenturl+'">'+b2bking_display_settings.sending_please_wait;
			jQuery('#b2bking_request_custom_quote_button').html(newbuttonhtml);

			// determine if loader icon color needs to be changed from white to black
			var textcolor = jQuery('#b2bking_request_custom_quote_button').css('color');
			textcolor = textcolor.split('(')[1].split(')')[0];

			var r = parseInt(textcolor.split(',')[0]);  // extract red
			var g = parseInt(textcolor.split(',')[1].trim());  // extract red
			var b = parseInt(textcolor.split(',')[2].trim());  // extract red

			var luma = 0.2126 * r + 0.7152 * g + 0.0722 * b; // per ITU-R BT.709

			if (luma < 55) {
			    // change color of loader to black
			    jQuery('.b2bking_loader_icon_button_quote').css('filter','invert(1)');
			}
			*/
		}

		/* Request a custom quote END*/

		/* Offers START*/

		// On clicking "add offer to cart"
		$('body').on('click', '.b2bking_offer_add', function(){
			if (b2bking_display_settings.disableofferadd !== 1){
				let offerId = $(this).val();
				// replace icon with loader
				$('<img class="b2bking_loader_icon_button" src="'+b2bking_display_settings.loadertransparenturl+'">').insertBefore($(this).find('.b2bking_myaccount_individual_offer_bottom_line_button_icon'));
				$(this).find('.b2bking_myaccount_individual_offer_bottom_line_button_icon').remove();

				// run ajax request
				var datavar = {
			            action: 'b2bkingaddoffer',
			            security: b2bking_display_settings.security,
			            offer: offerId,
			        };

				$.post(b2bking_display_settings.ajaxurl, datavar, function(response){
					// redirect to cart
					window.location = b2bking_display_settings.carturl;
				});
			}
		});
		
		// offer download
		$('body').on('click','.b2bking_offer_download', function(){
			var logoimg = b2bking_display_settings.offers_logo;
			var offernr = $(this).parent().parent().parent().find('#b2bking_offer_id').val();

			// if images are lazy-loaded, replace
			let logodatasrc = jQuery('#b2bking_img_logo').attr('data-src');
			if (logodatasrc !== undefined && logodatasrc !== ''){
				jQuery('#b2bking_img_logo').attr('src', logodatasrc);
			}

			jQuery('.b2bking_hidden_img').each(function(i){
				let logodatasrcth = jQuery(this).attr('data-src');
				if (logodatasrcth !== undefined && logodatasrcth !== ''){
					jQuery(this).attr('src', logodatasrcth);
				}
			});


			var imgToExport = document.getElementById('b2bking_img_logo');
			var canvas = document.createElement('canvas');
	        canvas.width = imgToExport.width; 
	        canvas.height = imgToExport.height; 
	        canvas.getContext('2d').drawImage(imgToExport, 0, 0);
	  		var dataURL = canvas.toDataURL("image/png"); 

	  		// get all thumbnails 
	  		var thumbnails = [];
	  		var thumbnr = 0;
	  		
	  		if (parseInt(b2bking_display_settings.offers_images_setting) === 1){
		  		// get field;
		  		let field = $(this).parent().parent().parent().find('.b2bking_offers_thumbnails_str').val();
		  		let itemsArray = field.split('|');
		  		// foreach condition, add condition, add new item
		  		itemsArray.forEach(function(item){
		  			if (item !== 'no'){
		  				var idimg = 'b2bking_img_logo'+thumbnr+offernr;
  						var imgToExport = document.getElementById(idimg);
  						var canvas = document.createElement('canvas');
  				        canvas.width = imgToExport.width; 
  				        canvas.height = imgToExport.height; 
  				        canvas.getContext('2d').drawImage(imgToExport, 0, 0);
  				  		let datau = canvas.toDataURL("image/png"); 
  				  		thumbnr++;
  				  		thumbnails.push(datau);
		  			} else {
		  				thumbnails.push('no');
		  			}
		  		});
		  	}

		  	thumbnr = 0;
			var customtext = $(this).parent().parent().parent().find('.b2bking_myaccount_individual_offer_custom_text').text();
			customtext = customtext.replace('\t','').trim();

			var customtextvendor = $(this).parent().parent().parent().find('.b2bking_myaccount_individual_offer_custom_text_vendor').text();
			customtextvendor = customtextvendor.replace('\t','').trim();


			var customtexttitle = b2bking_display_settings.offer_custom_text;
			if (customtext.length === 0 && customtextvendor.length === 0){
				customtexttitle = '';
			}

			
	

			var bodyarray = [];
			bodyarray.push([{ text: b2bking_display_settings.item_name, style: 'tableHeader', margin: [7, 7, 7, 7] }, { text: b2bking_display_settings.item_quantity, style: 'tableHeader', margin: [7, 7, 7, 7] }, { text: b2bking_display_settings.unit_price, style: 'tableHeader', margin: [7, 7, 7, 7] }, { text: b2bking_display_settings.item_subtotal, style: 'tableHeader', margin: [7, 7, 7, 7] }]);

			// get values
			jQuery(this).parent().parent().parent().find('.b2bking_myaccount_individual_offer_element_line').each(function(i){
				let tempvalues = [];

				if (parseInt(b2bking_display_settings.offers_images_setting) === 1){
					if (thumbnails[thumbnr] !== 'no'){
						// add name + images
						tempvalues.push([{ text: jQuery(this).find('.b2bking_myaccount_individual_offer_element_line_item_name').first().text(), margin: [7, 7, 7, 7] },{
								image: thumbnails[thumbnr],
								width: 40,
								margin: [15, 5, 5, 5]
							}]);
					} else {
						// add name only
						tempvalues.push({ text: jQuery(this).find('.b2bking_myaccount_individual_offer_element_line_item_name').first().text(), margin: [7, 7, 7, 7] });
					}
					thumbnr++;
				} else {
					// add name only
					tempvalues.push({ text: jQuery(this).find('.b2bking_myaccount_individual_offer_element_line_item_name').first().text(), margin: [7, 7, 7, 7] });
				}


				tempvalues.push({ text: jQuery(this).find('.b2bking_myaccount_individual_offer_element_line_item:nth-child(2)').text(), margin: [7, 7, 7, 7] });
				tempvalues.push({ text: jQuery(this).find('.b2bking_myaccount_individual_offer_element_line_item:nth-child(3)').text(), margin: [7, 7, 7, 7] });
				tempvalues.push({ text: jQuery(this).find('.b2bking_myaccount_individual_offer_element_line_item:nth-child(4)').text(), margin: [7, 7, 7, 7] });
				bodyarray.push(tempvalues);
			});



			bodyarray.push(['','',{ text: b2bking_display_settings.offer_total+': ', margin: [7, 7, 7, 7], bold: true },{ text: jQuery(this).parent().parent().parent().find('.b2bking_myaccount_individual_offer_bottom_line_total strong').text(), margin: [7, 7, 7, 7], bold: true }]);

			let imgobj = {
						image: dataURL,
						width: parseInt(b2bking_display_settings.offerlogowidth),
						margin: [0, parseInt(b2bking_display_settings.offerlogotopmargin), 0, 30]
					};


			var contentarray =[
					{ text: b2bking_display_settings.offer_details, fontSize: 14, bold: true, margin: [0, 20, 0, 20] },
					{
						style: 'tableExample',
						table: {
							headerRows: 1,
							widths: ['*', '*', '*', '*'],
							body: bodyarray,
						},
						layout: 'lightHorizontalLines'
					},
					{ text: b2bking_display_settings.offer_go_to, link: b2bking_display_settings.offers_endpoint_link, decoration: 'underline', fontSize: 13, bold: true, margin: [0, 20, 40, 8], alignment:'right' },
					{ text: customtexttitle, fontSize: 14, bold: true, margin: [0, 50, 0, 8] },
					{ text: customtextvendor, fontSize: 12, bold: false, margin: [0, 8, 0, 8] },
					{ text: customtext, fontSize: 12, bold: false, margin: [0, 8, 0, 8] },

				];

			var mention_offer_requester = b2bking_display_settings.mention_offer_requester;

			var custom_content_after_logo_left_1 = b2bking_display_settings.custom_content_after_logo_left_1;
			var custom_content_after_logo_left_2 = b2bking_display_settings.custom_content_after_logo_left_2;
			var custom_content_after_logo_center_1 = b2bking_display_settings.custom_content_after_logo_center_1;
			var custom_content_after_logo_center_2 = b2bking_display_settings.custom_content_after_logo_center_2;
			if (custom_content_after_logo_left_1.length !== 0){
				let custom_content = { text: custom_content_after_logo_left_1, fontSize: 12, bold: true, margin: [0, 0, 0, 20], alignment:'left' };
				contentarray.unshift(custom_content);
			}

			if (mention_offer_requester.length !== 0){
				let custom_content = { text: mention_offer_requester + jQuery(this).data('customer'), fontSize: 14, bold: true, margin: [0, 12, 0, 12], alignment:'left' };
				contentarray.unshift(custom_content);
			}
			
			if (custom_content_after_logo_left_2.length !== 0){
				let custom_content = { text: custom_content_after_logo_left_2, fontSize: 12, bold: true, margin: [0, 12, 0, 12], alignment:'left' };
				contentarray.unshift(custom_content);
			}
			if (custom_content_after_logo_center_1.length !== 0){
				let custom_content = { text: custom_content_after_logo_center_1, fontSize: 12, bold: true, margin: [0, 0, 0, 20], alignment:'center' };
				contentarray.unshift(custom_content);
			}
			if (custom_content_after_logo_center_2.length !== 0){
				let custom_content = { text: custom_content_after_logo_center_2, fontSize: 12, bold: true, margin: [0, 12, 0, 12], alignment:'center' };
				contentarray.unshift(custom_content);
			}

			if (logoimg.length !== 0){
				contentarray.unshift(imgobj);
			}

			var custom_content_center_1 = b2bking_display_settings.custom_content_center_1;
			var custom_content_center_2 = b2bking_display_settings.custom_content_center_2;
			var custom_content_left_1 = b2bking_display_settings.custom_content_left_1;
			var custom_content_left_2 = b2bking_display_settings.custom_content_left_2;
			if (custom_content_center_1.length !== 0){
				let custom_content = { text: custom_content_center_1, fontSize: 12, bold: true, margin: [0, 0, 0, 20], alignment:'center' };
				contentarray.unshift(custom_content);
			}
			if (custom_content_center_2.length !== 0){
				let custom_content = { text: custom_content_center_2, fontSize: 12, bold: true, margin: [0, 12, 0, 12], alignment:'center' };
				contentarray.unshift(custom_content);
			}
			if (custom_content_left_1.length !== 0){
				let custom_content = { text: custom_content_left_1, fontSize: 12, bold: true, margin: [0, 0, 0, 20], alignment:'left' };
				contentarray.unshift(custom_content);
			}
			if (custom_content_left_2.length !== 0){
				let custom_content = { text: custom_content_left_2, fontSize: 12, bold: true, margin: [0, 12, 0, 12], alignment:'left' };
				contentarray.unshift(custom_content);
			}

			
			var docDefinition = {
				content: contentarray
			};
			

			if(b2bking_display_settings.pdf_download_lang === 'thai'){

				pdfMake.fonts = {
				  THSarabunNew: {
				    normal: 'THSarabunNew.ttf',
				    bold: 'THSarabunNew-Bold.ttf',
				    italics: 'THSarabunNew-Italic.ttf',
				    bolditalics: 'THSarabunNew-BoldItalic.ttf'
				  }
				};

				docDefinition = {
				  content: contentarray,
				  defaultStyle: {
				    font: 'THSarabunNew'
				  }
				}
			}

			if(b2bking_display_settings.pdf_download_lang === 'japanese'){

				pdfMake.fonts = {
				  Noto: {
				    normal: 'Noto.ttf',
				    bold: 'Noto.ttf',
				    italics: 'Noto.ttf',
				    bolditalics: 'Noto.ttf'
				  }
				};

				docDefinition = {
				  content: contentarray,
				  defaultStyle: {
				    font: 'Noto'
				  }
				}
			}

			if(b2bking_display_settings.pdf_download_font !== 'standard'){

				pdfMake.fonts = {
				  Customfont: {
				    normal: b2bking_display_settings.pdf_download_font,
				    bold: b2bking_display_settings.pdf_download_font,
				    italics: b2bking_display_settings.pdf_download_font,
				    bolditalics: b2bking_display_settings.pdf_download_font
				  }
				};

				docDefinition = {
				  content: contentarray,
				  defaultStyle: {
				    font: 'Customfont'
				  }
				}
			}
			

			pdfMake.createPdf(docDefinition).download(b2bking_display_settings.offer_file_name + '.pdf');
		});
		

		/* Offers END */


		/* Custom Registration Fields START */
		// Dropdown
		addCountryRequired(); // woocommerce_form_field does not allow required for country, so we add it here
		// On load, show hide fields depending on dropdown option
		showHideRegistrationFields();


		if(parseInt(b2bking_display_settings.enable_registration_fields_checkout) === 1){

			$('.country_to_state').trigger('change');
			$('#b2bking_registration_roles_dropdown').change(showHideRegistrationFields);
			$('.b2bking_country_field_selector select').change(showHideRegistrationFields);
			$('select#billing_country').change(showHideRegistrationFields);
		}
		
		function addCountryRequired(){
			$('.b2bking_country_field_req_required').prop('required','true');
			$('.b2bking_custom_field_req_required select').prop('required','true');
		}
		// on state change, reapply required
	//	$('body').on('DOMSubtreeModified', '#billing_state_field', function(){
			//let selectedValue = $('#b2bking_registration_roles_dropdown').val();
			//$('.b2bking_custom_registration_'+selectedValue+' #billing_state_field.b2bking_custom_field_req_required #billing_state').prop('required','true');
			//$('.b2bking_custom_registration_allroles #billing_state_field.b2bking_custom_field_req_required #billing_state').prop('required','true');
	//	});

		function showHideRegistrationFields(){

			if(parseInt(b2bking_display_settings.enable_registration_fields_checkout) === 1){


				// Hide all custom fields. Remove 'required' for hidden fields with required
				$('.b2bking_custom_registration_container').css('display','none');
				$('.b2bking_custom_field_req_required').removeAttr('required');
				$('.b2bking_custom_field_req_required select').removeAttr('required');
				$('.b2bking_custom_field_req_required #billing_state').removeAttr('required');
				
				// Show fields of all roles. Set required
				$('.b2bking_custom_registration_allroles').css('display','block');
				$('.b2bking_custom_registration_allroles .b2bking_custom_field_req_required').prop('required','true');
				$('.b2bking_custom_registration_allroles .b2bking_custom_field_req_required select').prop('required','true');
				setTimeout(function(){
					$('.b2bking_custom_registration_allroles .b2bking_custom_field_req_required #billing_state').prop('required','true');
		        },125);

				// Show all fields of the selected role. Set required
				let selectedValue = $('#b2bking_registration_roles_dropdown').val();
				$('.b2bking_custom_registration_'+selectedValue).css('display','block');
				$('.b2bking_custom_registration_'+selectedValue+' .b2bking_custom_field_req_required').prop('required','true');
				$('.b2bking_custom_registration_'+selectedValue+' .b2bking_custom_field_req_required select').prop('required','true');
				setTimeout(function(){
		        	$('.b2bking_custom_registration_'+selectedValue+' .b2bking_custom_field_req_required #billing_state').prop('required','true');
		        },225);

				// if there is more than 1 country
				if(parseInt(b2bking_display_settings.number_of_countries) !== 1){
					// check VAT available countries and selected country. If vat not available, remove vat and required
					let vatCountries = $('#b2bking_vat_number_registration_field_countries').val();
					let selectedCountry = $('.b2bking_country_field_selector select').val();
					if (selectedCountry === undefined){
						selectedCountry = $('select#billing_country').val();
					}
					if (vatCountries !== undefined){
						if ( (! (vatCountries.includes(selectedCountry))) || selectedCountry.trim().length === 0 ){
							// hide and remove required
							$('.b2bking_vat_number_registration_field_container').css('display','none');
							$('#b2bking_vat_number_registration_field').removeAttr('required');
						}
					}
				}

				// New for My Account VAT
				if (parseInt(b2bking_display_settings.myaccountloggedin) === 1){
					// check VAT countries
					let vatCountries = $('#b2bking_custom_billing_vat_countries_field input').prop('placeholder');
					let billingCountry = $('#billing_country').val();
					if (vatCountries !== undefined){
						if ( (! (vatCountries.includes(billingCountry))) || billingCountry.trim().length === 0){
							$('.b2bking_vat_field_container, #b2bking_checkout_registration_validate_vat_button').removeClass('b2bking_vat_visible, b2bking_vat_hidden').addClass('b2bking_vat_hidden');
							$('.b2bking_vat_field_required_1 input').removeAttr('required');
						} else {
							$('.b2bking_vat_field_container, #b2bking_checkout_registration_validate_vat_button').removeClass('b2bking_vat_visible, b2bking_vat_hidden').addClass('b2bking_vat_visible');
							$('.b2bking_vat_field_required_1 .optional').after('<abbr class="required" title="required">*</abbr>');
							$('.b2bking_vat_field_required_1 .optional').remove();
							$('.b2bking_vat_field_required_1 input').prop('required','true');
						}
					}
				}
			}
			
		}

		// when billing country is changed , trigger update checkout. Seems to be a change in how WooCommerce refreshes the page. In order for this to work well with tax exemptions, run update checkout
		$('#billing_country').on('change', function() {
			setTimeout(function(){
				$(document.body).trigger("update_checkout");
			},1750);
		});
        jQuery('body').on('change', 'input[name="payment_method"]', function(){
        	if (parseInt(b2bking_display_settings.enable_payment_method_change_refresh) === 1){

	        	setTimeout(function(){
					jQuery(document.body).trigger("update_checkout");
				},250);
	        }
        });
		// Hook into updated checkout for WooCommerce
		$( document ).on( 'updated_checkout', function() {

		    // check VAT countries
		    let vatCountries = $('#b2bking_custom_billing_vat_countries_field input').val();
		    let billingCountry = $('#billing_country').val();
		    if (vatCountries !== undefined){
		    	if ( (! (vatCountries.includes(billingCountry))) || billingCountry.trim().length === 0){
		    		$('.b2bking_vat_field_container, #b2bking_checkout_registration_validate_vat_button').removeClass('b2bking_vat_visible, b2bking_vat_hidden').addClass('b2bking_vat_hidden');
		    		$('.b2bking_vat_field_required_1 input').removeAttr('required');
		    	} else {
		    		$('.b2bking_vat_field_container, #b2bking_checkout_registration_validate_vat_button').removeClass('b2bking_vat_visible, b2bking_vat_hidden').addClass('b2bking_vat_visible');
		    		$('.b2bking_vat_field_required_1 .optional').after('<abbr class="required" title="required">*</abbr>');
		    		$('.b2bking_vat_field_required_1 .optional').remove();
		    		$('.b2bking_vat_field_required_1 input').prop('required','true');
		    	}
		    }
		} );

		// VALIDATE VAT AT CHECKOUT REGISTRATION
		$('body').on('click', '#b2bking_checkout_registration_validate_vat_button', function(){

			$('#b2bking_checkout_registration_validate_vat_button').text(b2bking_display_settings.validating);
			var vatnumber = $('#b2bking_vat_number_registration_field').val();
			if (vatnumber === undefined){
				vatnumber = $('.b2bking_vat_field_container input[type="text"]').val().trim();
			} else {
				vatnumber = $('#b2bking_vat_number_registration_field').val().trim();
			}
			
			var datavar = {
	            action: 'b2bkingvalidatevat',
	            security: b2bking_display_settings.security,
	            vat: vatnumber,
	            country: $('#billing_country').val(),
	        };

			$.post(b2bking_display_settings.ajaxurl, datavar, function(response){
				if (response === 'valid'){
					createCookie('b2bking_validated_vat_status','validated_vat', false);
					createCookie('b2bking_validated_vat_number', vatnumber, false);
					$('#b2bking_vat_number_registration_field').prop('readonly', true);
					$('#b2bking_checkout_registration_validate_vat_button').prop('disabled', true);
					$('#b2bking_checkout_registration_validate_vat_button').text(b2bking_display_settings.vatvalid);
					// refresh checkout for prices
					$(document.body).trigger("update_checkout");
				} else if (response === 'invalid'){

					eraseCookie('b2bking_validated_vat_status');

					$('#b2bking_checkout_registration_validate_vat_button').text(b2bking_display_settings.vatinvalid);
				}
			});
		});

		function createCookie(name, value, days) {
		    var expires;

		    if (days) {
		        var date = new Date();
		        date.setTime(date.getTime() + (days * 24 * 60 * 60 * parseFloat(b2bking_display_settings.cookie_expiration_days)));
		        expires = "; expires=" + date.toGMTString();
		    } else {
		        expires = "";
		    }
		    document.cookie = encodeURIComponent(name) + "=" + encodeURIComponent(value) + expires + "; path=/";
		}

		function eraseCookie(name) {
		    createCookie(name, "", -1);
		}

		// if country is changed, re-run validation
		$('.woocommerce-checkout #billing_country').change(function(){
			eraseCookie('b2bking_validated_vat_status');
			$('#b2bking_checkout_registration_validate_vat_button').text(b2bking_display_settings.validatevat);
			$('#b2bking_vat_number_registration_field').prop('readonly', false);
			$('#b2bking_vat_number_registration_field').val('');
			$('#b2bking_checkout_registration_validate_vat_button').prop('disabled', false);
			// refresh checkout for prices
			$(document.body).trigger("update_checkout");
		});

		// Check if delivery country is different than shop country
		if (parseInt(b2bking_display_settings.differentdeliverycountrysetting) === 1){
			// if setting is enabled
			$('#shipping_country').change(exempt_vat_delivery_country);
		}
		function exempt_vat_delivery_country(){
			var datavar = {
	            action: 'b2bkingcheckdeliverycountryvat',
	            security: b2bking_display_settings.security,
	            deliverycountry: $('#shipping_country').val(),
	        };

			$.post(b2bking_display_settings.ajaxurl, datavar, function(response){
				setTimeout(function(){
					$(document.body).trigger("update_checkout");
				}, 250);
			});
		}

		// add validation via JS to checkout
		if (parseInt(b2bking_display_settings.disable_checkout_required_validation) === 0){

			jQuery(function($){
			    $('form.woocommerce-checkout').on( 'click', "#place_order", function(e){
			   		var invalid = 'no';
			        var fields = $(".b2bking_custom_field_req_required");
			        $.each(fields, function(i, field) {
				       	if ($(field).css('display') !== 'none' && $(field).parent().parent().css('display') !== 'none' && $(field).parent().css('display') !== 'none'){
				       		if (!field.value || field.type === 'checkbox'){
				       			let parent = $(field).parent();

				       			let text = parent.find('label').text().slice(0,-2);
				       			if (text === ''){
				       				let parent = $(field).parent().parent();
				       				let text = parent.find('label').text().slice(0,-2);
				       				alert(text + ' ' + b2bking_display_settings.is_required);
				       			} else {
				       				alert(text + ' ' + b2bking_display_settings.is_required);
				       			}
				       			invalid = 'yes';
				       		}
				       	}
			       }); 
			    	
			    	if (invalid === 'yes'){
			    		e.preventDefault();
			    		$('#b2bking_js_based_invalid').val('invalid');
			    	} else {
			    		$('#b2bking_js_based_invalid').val('0');
			    	}     	
	   
			    });
			});
		}

		// force select a country on registration
		$('button.woocommerce-form-register__submit').on('click',function(e){
			if ($('.b2bking_country_field_selector').parent().css('display') !== 'none'){
				if ($('.b2bking_country_field_selector select').val() === 'default'){
					e.preventDefault();
					alert(b2bking_display_settings.must_select_country);
				}
			}
		});




		/* Custom Registration Fields END */

		/* Subaccounts START */
		// On clicking 'New Subaccount'
		$('body').on('click', '.b2bking_subaccounts_container_top_button', function(){
			// Hide subaccounts, show new subaccount
			$('.b2bking_subaccounts_new_account_container').css('display','block');
			$('.b2bking_subaccounts_account_container').css('display','none');
			$('.b2bking_subaccounts_container_top_button').css('display','none');
		});
		// On clicking 'Close X', reverse
		$('body').on('click', '.b2bking_subaccounts_new_account_container_top_close', function(){
			$('.b2bking_subaccounts_new_account_container').css('display','none');
			$('.b2bking_subaccounts_account_container').css('display','block');
			$('.b2bking_subaccounts_container_top_button').css('display','inline-flex');
		});

		// On clicking "Create new subaccount"
		$('body').on('click', '.b2bking_subaccounts_new_account_container_content_bottom_button', function(){
			// clear displayed validation errors
			$('.b2bking_subaccounts_new_account_container_content_bottom_validation_errors').html('');
			let validationErrors = '';
			// get username and email and password
			let username = 123;
			if (parseInt(b2bking_display_settings.disable_username_subaccounts) === 0){
				username = $('input[name="b2bking_subaccounts_new_account_username"]').val().trim();
			}
			let email = $('input[name="b2bking_subaccounts_new_account_email_address"]').val().trim();
			let password = $('input[name="b2bking_subaccounts_new_account_password"]').val().trim();

			if (parseInt(b2bking_display_settings.disable_username_subaccounts) === 0){
				// check against regex
				if (/^(?!.*[_.]$)(?=.{8,30}$)(?![_.])(?!.*[_.]{2})[a-zA-Z0-9._-\d@]+$/.test(username) === false){
					validationErrors += b2bking_display_settings.newSubaccountUsernameError;
				}
			}

			if (/^([a-zA-Z0-9_.+-])+\@(([a-zA-Z0-9-])+\.)+([a-zA-Z0-9]{2,4})+$/.test(email) === false){
				validationErrors += b2bking_display_settings.newSubaccountEmailError;
			}
			if (/^(?=.*[A-Za-z])(?=.*[\d]).{8,}$/.test(password) === false){
				validationErrors += b2bking_display_settings.newSubaccountPasswordError;
			}

			if (validationErrors !== ''){
				// show errors
				$('.b2bking_subaccounts_new_account_container_content_bottom_validation_errors').html(validationErrors);
			} else {
				// proceed with AJAX account registration request

				// get all other data
				let name = $('input[name="b2bking_subaccounts_new_account_name"]').val().trim();
				let lastName = $('input[name="b2bking_subaccounts_new_account_last_name"]').val().trim();
				let jobTitle = $('input[name="b2bking_subaccounts_new_account_job_title"]').val().trim();
				let phone = $('input[name="b2bking_subaccounts_new_account_phone_number"]').val().trim();
				
				// checkboxes are true or false
				let checkboxBuy = $('input[name="b2bking_subaccounts_new_account_container_content_element_checkbox_buy"]').prop('checked'); 
				let checkboxBuyApproval = $('input[name="b2bking_subaccounts_new_account_container_content_element_checkbox_buy_approval"]').prop('checked'); 
				let checkboxViewOrders = $('input[name="b2bking_subaccounts_new_account_container_content_element_checkbox_view_orders"]').prop('checked');
				let checkboxViewSubscriptions = $('input[name="b2bking_subaccounts_new_account_container_content_element_checkbox_view_subscriptions"]').prop('checked');
				let checkboxViewOffers = $('input[name="b2bking_subaccounts_new_account_container_content_element_checkbox_view_offers"]').prop('checked');
				let checkboxViewConversations = $('input[name="b2bking_subaccounts_new_account_container_content_element_checkbox_view_conversations"]').prop('checked');
				let checkboxViewLists = $('input[name="b2bking_subaccounts_new_account_container_content_element_checkbox_view_lists"]').prop('checked');

				// replace icon with loader
				// store icon
				var buttonoriginal = $('.b2bking_subaccounts_new_account_container_content_bottom_button').html();
				$('<img class="b2bking_loader_icon_button" src="'+b2bking_display_settings.loadertransparenturl+'">').insertBefore('.b2bking_subaccounts_new_account_container_content_bottom_button_icon');
				$('.b2bking_subaccounts_new_account_container_content_bottom_button_icon').remove();

				// send AJAX account creation request
				var datavar = {
		            action: 'b2bking_create_subaccount',
		            security: b2bking_display_settings.security,
		            username: username,
		            password: password, 
		            name: name,
		            lastName: lastName,
		            jobTitle: jobTitle,
		            email: email,
		            phone: phone,
		            permissionBuy: checkboxBuy,
		            permissionBuyApproval: checkboxBuyApproval,
		            permissionViewOrders: checkboxViewOrders,
		            permissionViewSubscriptions: checkboxViewSubscriptions,
		            permissionViewOffers: checkboxViewOffers,
		            permissionViewConversations: checkboxViewConversations,
		            permissionViewLists: checkboxViewLists,
		        };

		        // get custom fields
		        let customfields = jQuery('#b2bking_custom_new_subaccount_fields').val().split(';');
		        customfields.forEach(function(textinput) {
		        	let value = jQuery('input[name="'+textinput+'"]').val();
		        	datavar[textinput] = value;
		        });


				$.post(b2bking_display_settings.ajaxurl, datavar, function(response){
					if (response.startsWith('error')){
						console.log(response);
						$('.b2bking_subaccounts_new_account_container_content_bottom_validation_errors').html(b2bking_display_settings.newSubaccountAccountError+' '+response.substring(5));
						// hide loader, restore button
						$('.b2bking_subaccounts_new_account_container_content_bottom_button').html(buttonoriginal);
					} else if (response === 'error_maximum_subaccounts'){
						$('.b2bking_subaccounts_new_account_container_content_bottom_validation_errors').html(b2bking_display_settings.newSubaccountMaximumSubaccountsError);
						// hide loader, restore button
						$('.b2bking_subaccounts_new_account_container_content_bottom_button').html(buttonoriginal);
					} else {
						// go to subaccounts endpoint
						window.location = b2bking_display_settings.subaccountsurl;
					}
				});
			}
		});

		// On clicking "Update subaccount"
		$('body').on('click', '.b2bking_subaccounts_edit_account_container_content_bottom_button', function(){
			// get details and permissions
			let subaccountId = $('.b2bking_subaccounts_edit_account_container_content_bottom_button').val().trim();
			let name = $('input[name="b2bking_subaccounts_new_account_name"]').val().trim();
			let lastName = $('input[name="b2bking_subaccounts_new_account_last_name"]').val().trim();
			let jobTitle = $('input[name="b2bking_subaccounts_new_account_job_title"]').val().trim();
			let phone = $('input[name="b2bking_subaccounts_new_account_phone_number"]').val().trim();

			// checkboxes are true or false
			let checkboxBuy = $('input[name="b2bking_subaccounts_new_account_container_content_element_checkbox_buy"]').prop('checked'); 
			let checkboxBuyApproval = $('input[name="b2bking_subaccounts_new_account_container_content_element_checkbox_buy_approval"]').prop('checked'); 
			let checkboxViewOrders = $('input[name="b2bking_subaccounts_new_account_container_content_element_checkbox_view_orders"]').prop('checked');
			let checkboxViewSubscriptions = $('input[name="b2bking_subaccounts_new_account_container_content_element_checkbox_view_subscriptions"]').prop('checked');
			let checkboxViewOffers = $('input[name="b2bking_subaccounts_new_account_container_content_element_checkbox_view_offers"]').prop('checked');
			let checkboxViewConversations = $('input[name="b2bking_subaccounts_new_account_container_content_element_checkbox_view_conversations"]').prop('checked');
			let checkboxViewLists = $('input[name="b2bking_subaccounts_new_account_container_content_element_checkbox_view_lists"]').prop('checked');

			// replace icon with loader
			$('<img class="b2bking_loader_icon_button" src="'+b2bking_display_settings.loadertransparenturl+'">').insertBefore('.b2bking_subaccounts_edit_account_container_content_bottom_button .b2bking_subaccounts_new_account_container_content_bottom_button_icon');
			$('.b2bking_subaccounts_edit_account_container_content_bottom_button .b2bking_subaccounts_new_account_container_content_bottom_button_icon').remove();

			// send AJAX account creation request
			var datavar = {
	            action: 'b2bking_update_subaccount',
	            security: b2bking_display_settings.security,
	            subaccountId: subaccountId,
	            name: name,
	            lastName: lastName,
	            jobTitle: jobTitle,
	            phone: phone,
	            permissionBuy: checkboxBuy,
	            permissionBuyApproval: checkboxBuyApproval,
	            permissionViewOrders: checkboxViewOrders,
	            permissionViewSubscriptions: checkboxViewSubscriptions,
	            permissionViewOffers: checkboxViewOffers,
	            permissionViewConversations: checkboxViewConversations,
	            permissionViewLists: checkboxViewLists,
	        };

	        // get custom fields
	        let customfields = jQuery('#b2bking_custom_new_subaccount_fields').val().split(';');
	        customfields.forEach(function(textinput) {
	        	let value = jQuery('input[name="'+textinput+'"]').val();
	        	datavar[textinput] = value;
	        });


	        $.post(b2bking_display_settings.ajaxurl, datavar, function(response){
				// go to subaccounts endpoint
				window.location = b2bking_display_settings.subaccountsurl;
			});
		});

		// on clicking close inside subaccount edit
		$('.b2bking_subaccounts_edit_account_container_top_close').on('click',function(){
			// go to subaccounts endpoint
			window.location = b2bking_display_settings.subaccountsurl;
		});

		// on clicking delete user, run same function as reject user
		$('.b2bking_subaccounts_edit_account_container_content_bottom_button_delete').on('click', function(){
			if (confirm(b2bking_display_settings.are_you_sure_delete)){
				// replace icon with loader
				$('<img class="b2bking_loader_icon_button" src="'+b2bking_display_settings.loadertransparenturl+'">').insertBefore('.b2bking_subaccounts_edit_account_container_content_bottom_button_delete .b2bking_subaccounts_new_account_container_content_bottom_button_icon');
				$('.b2bking_subaccounts_edit_account_container_content_bottom_button_delete .b2bking_subaccounts_new_account_container_content_bottom_button_icon').remove();

				var datavar = {
		            action: 'b2bkingrejectuser',
		            security: b2bking_display_settings.security,
		            user: $('.b2bking_subaccounts_edit_account_container_content_bottom_button').val().trim(),
		            issubaccount: 'yes',
		        };

				$.post(b2bking_display_settings.ajaxurl, datavar, function(response){
					// go to subaccounts endpoint
					window.location = b2bking_display_settings.subaccountsurl;
				});
			}
		});

		showHideApproval();
		$('input[name="b2bking_subaccounts_new_account_container_content_element_checkbox_buy').on('change', showHideApproval);
		function showHideApproval(){
			if($('input[name="b2bking_subaccounts_new_account_container_content_element_checkbox_buy').prop('checked')) {
		    	$('.b2bking_checkbox_permission_approval').css('display','flex');
		    } else {      
		    	$('.b2bking_checkbox_permission_approval').css('display','none');
		    }
		}	

		// click on approve / reject order
		$('#b2bking_approve_order').on('click', function(){

			let orderid = $('#b2bking_order_number').val();

			if (confirm(b2bking_display_settings.approve_order_confirm)){
				var datavar = {
		            action: 'b2bking_approve_order',
		            security: b2bking_display_settings.security,
		            orderid: orderid
		        };

				$.post(b2bking_display_settings.ajaxurl, datavar, function(response){

					if (parseInt(b2bking_display_settings.approve_order_redirect_payment) === 0){
						location.reload();
					} else {
						window.location = $('#b2bking_pay_now_url').val();
					}

				});
			}
					
		});

		$('#b2bking_reject_order').on('click', function(){
			let orderid = $('#b2bking_order_number').val();

			if (confirm(b2bking_display_settings.reject_order_confirm)){
				var rejection_reason = window.prompt(b2bking_display_settings.reject_order_email,'');
				var datavar = {
		            action: 'b2bking_reject_order',
		            security: b2bking_display_settings.security,
		            orderid: orderid,
		            reason: rejection_reason
		        };


				$.post(b2bking_display_settings.ajaxurl, datavar, function(response){
					location.reload();
				});
			}
		});
		$('#b2bking_reject_order_subaccount').on('click', function(){ // when subaccount cancels order
			let orderid = $('#b2bking_order_number').val();

			if (confirm(b2bking_display_settings.cancel_order_confirm)){
				var datavar = {
		            action: 'b2bking_reject_order',
		            security: b2bking_display_settings.security,
		            orderid: orderid,
		            reason: ''
		        };

				$.post(b2bking_display_settings.ajaxurl, datavar, function(response){
					location.reload();
				});
			}
		});

		/* Subaccounts END */

		/* Bulk order form START */

		// On clicking dropdown inside cream order form, do not trigger a href
		$('body').on('click', '.b2bking_bulkorder_cream_name select', function(e){
			e.preventDefault();
			e.stopPropagation();
		});

		/* Disallow entering numbers directly based on min max */
		$('body').on('input', '.b2bking_bulkorder_form_container_content_line_qty_indigo', function(e){
		    let v = parseInt($(this).val());
		    let min = parseInt($(this).attr('min'));
		    let max = parseInt($(this).attr('max'));

		    if (v < min){
		        $(this).val(min);
		    } else if (v > max){
		        $(this).val(max);
		    }
		});

		// On clicking "new line", prepend newline to button container
		var pricetextvar = b2bking_display_settings.currency_symbol+'0';
		if (parseInt(b2bking_display_settings.accountingsubtotals) === 1){
			pricetextvar = b2bking_display_settings.price0;
		}
		if (parseInt(b2bking_display_settings.quotes_enabled) === 1){
			pricetextvar = b2bking_display_settings.quote_text;
		}

		$('.b2bking_bulkorder_form_container_newline_button').on('click', function() {
			// Clone template.
			var template = $('.b2bking_bulkorder_form_newline_template').html();
			template = template.replace('pricetext',pricetextvar);
			template = template.replace('display:none','display:initial');
			// add line
			$('.b2bking_bulkorder_form_container_newline_container').before(template);
		});

		// on click 'save list' in bulk order form
		
		$('.b2bking_bulkorder_form_container_bottom_save_button').on('click', function(){
			let title = window.prompt(b2bking_display_settings.save_list_name, "");

			if (title !== '' && title !== null){

				let productString = ''; 
				// loop through all bulk order form lines
				document.querySelectorAll('.b2bking_bulkorder_form_container_content_line_product').forEach(function(textinput) {
					var classList = $(textinput).attr('class').split(/\s+/);
					$.each(classList, function(index, item) {
						// foreach line if it has selected class, get selected product ID 
					    if (item.includes('b2bking_selected_product_id_')) {
					    	let productID = item.split('_')[4];
					    	let quantity = $(textinput).parent().find('.b2bking_bulkorder_form_container_content_line_qty').val();
				    		if (quantity > 0 || parseInt(b2bking_display_settings.lists_zero_qty) === 1){
					    		// set product
					    		productString+=productID+':'+quantity+'|';
					    	}
					    }
					});
				});
				// if not empty, send
				if (productString !== ''){
					// replace icon with loader
					var buttonoriginal = $('.b2bking_bulkorder_form_container_bottom_save_button').html();
					$('<img class="b2bking_loader_icon_button" src="'+b2bking_display_settings.loadertransparenturl+'">').insertBefore('.b2bking_bulkorder_form_container_bottom_save_button_icon');
					$('.b2bking_bulkorder_form_container_bottom_save_button_icon').remove();

					// build pricelist to be saved
					let pricestringsend = '';
					Object.entries(prices).forEach(function (index) {
						let idstring = index[0];
						let price = index[1];
						let id = idstring.split('B2BKINGPRICE')[0];
						pricestringsend += id+':'+price+'|';
					});

					var datavar = {
			            action: 'b2bking_bulkorder_save_list',
			            security: b2bking_display_settings.security,
			            productstring: productString,
			            title: title,
			            pricelist: pricestringsend
			        };


					$.post(b2bking_display_settings.ajaxurl, datavar, function(response){
						// restore button
						$('.b2bking_bulkorder_form_container_bottom_save_button').html(buttonoriginal);
						alert(b2bking_display_settings.list_saved);
					});
				} else {
					alert(b2bking_display_settings.list_empty);
				}	
			}
		});

		var ignoreTime = false;
		// get if there are multiple forms
		if (jQuery('.b2bking_bulkorder_container_final').length > 1){
			// we have multiple forms on same page, must load all initially, ignore latestsearchtime in first 5 seconds
			var initialloadtime = Date.now();
			ignoreTime = true;
			setTimeout(function(){
				ignoreTime = false;
			}, 5000);
		}
		

		var latestSearchTime = Date.now();

		$('body').on('input', '.b2bking_bulkorder_form_container_content_line_qty', function(e){
			let val = $(this).val();
			if(val % 1 != 0){
				$(this).val(parseInt(val));
			}
		});

		$('body').on('input', '.b2bking_bulkorder_form_container_content_line_product', function(){
			let thisSearchTime = Date.now();
			latestSearchTime = thisSearchTime;
			let parent = $(this).parent();
			let inputValue = $(this).val();
			let searchbyval = $('#b2bking_bulkorder_searchby_select').val();
			if (typeof(searchbyval) === "undefined"){
				searchbyval = 'productname';
			}
			parent.find('.b2bking_bulkorder_form_container_content_line_livesearch').html('<img class="b2bking_loader_img" src="'+b2bking_display_settings.loaderurl+'">');
			parent.find('.b2bking_bulkorder_form_container_content_line_livesearch').css('display','block');

			var excludeval = $('.b2bking_bulkorder_exclude').val();
			var productlistval = $('.b2bking_bulkorder_product_list').val();
			var categoryval = $(this).parent().parent().parent().parent().find('.b2bking_bulkorder_category').val();
			var sortby = $('.b2bking_bulkorder_sortby').val();


			if (inputValue.length > 0){ // min x chars

				// set timer for 600ms before loading the ajax search (resource consuming)
				setTimeout(function(){

					// if in the last 2 seconds there's been no new searches or input
					if (thisSearchTime === latestSearchTime || (ignoreTime) ){
						// run search AJAX function 
						let formids = getIdsInForm();
						inputValue = inputValue.trim();
						var datavar = {
				            action: 'b2bking_ajax_search',
				            security: b2bking_display_settings.security,
				            searchValue: inputValue,
				            exclude: excludeval,
				            productlist: productlistval,
				            category: categoryval,
				            sortby: sortby,
				            searchby: searchbyval,
				            idsinform: JSON.stringify(formids),
				            dataType: 'json'
				        };

						$.post(b2bking_display_settings.ajaxurl, datavar, function(response){
							let display = '';
							let results = response;
							if (thisSearchTime === latestSearchTime || (ignoreTime)){
								if (parseInt(results) !== 1234){ // 1234 Integer for Empty
									let resultsObject = JSON.parse(results);
									Object.keys(resultsObject).forEach(function (index) {
										if (index.includes('B2BKINGPRICE')){
											prices[index] = resultsObject[index];
										} else if (index.includes('B2BTIERPRICE')){
											pricetiers[index] = resultsObject[index];
										} else if (index.includes('B2BKINGSTOCK')){
											stock[index] = resultsObject[index];
										} else if (index.includes('B2BKINGIMAGE')){
											images[index] = resultsObject[index];
										} else if (index.includes('B2BKINGMIN')){
											min[index] = resultsObject[index];
										} else if (index.includes('B2BKINGMAX')){
											max[index] = resultsObject[index];
										} else if (index.includes('B2BKINGSTEP')){
											step[index] = resultsObject[index];
										} else if (index.includes('B2BKINGVAL')){
											val[index] = resultsObject[index];
										} else if (index.includes('B2BKINGURL')){
											urls[index] = resultsObject[index];
										} else {
											if (parseInt(b2bking_display_settings.bulkorderformimages) === 1){
												let img = index+'B2BKINGIMAGE';
												if (resultsObject[img] !== 'no' && resultsObject[img] !== '' && resultsObject[img] !== null){
													display += '<div class="b2bking_livesearch_product_result productid_'+index+'">'+resultsObject[index]+'<img class="b2bking_livesearch_image" src="'+resultsObject[img]+'"></div>';
												} else {
													display += '<div class="b2bking_livesearch_product_result productid_'+index+'">'+resultsObject[index]+'</div>';
												}
											} else {
												display += '<div class="b2bking_livesearch_product_result productid_'+index+'">'+resultsObject[index]+'</div>';
											}
											
										}
									});


								} else {
									display = '<span class="b2bking_classic_noproducts_found">'+b2bking_display_settings.no_products_found+'</span>';
								}
								
								parent.find('.b2bking_bulkorder_form_container_content_line_livesearch').html(display);
							}

						});
					}
				}, 600);
				
			} else {
				parent.find('.b2bking_bulkorder_form_container_content_line_livesearch').css('display','none');
			}
		});

		var prices = Object;
		var stock = Object;
		var pricetiers = Object;
		var urls = Object;
		var images = Object;

		var min = Object;
		var max = Object;
		var step = Object;
		var val = Object;

		var currentline;

		// In WooCommerce AJAX add to cart, if 2 add to cart calls run at approx the same time, the WC function sets the cart to specific contents
		// The second WC function that runs can replace the first, and products will be missing
		// The solution we apply is to always wait until a call finishes before we start a new one.
		// we track if it's clear or not
		var addCartClear = 'yes';

		// let's populate prices initially from the html value
		let initialhtmlprices = $('#b2bking_initial_prices').val();
		if (initialhtmlprices !== undefined){
			let htmlprices = initialhtmlprices.split('|');
			htmlprices.forEach(function(textinput) {
				let idprice = textinput.split('-');
				if (idprice[0] !== ''){
					prices[idprice[0]+'B2BKINGPRICE'] = parseFloat(idprice[1]);
					pricetiers[idprice[0]+'B2BTIERPRICE'] = idprice[2];
					stock[idprice[0]+'B2BKINGSTOCK'] = parseInt(idprice[3]);
				}
				
			});
		}


		// on clicking on search result, set result in field
		$('body').on('click', '.b2bking_livesearch_product_result', function(){
			let title = $(this).text();
			let parent = $(this).parent().parent();
			currentline = parent;
			var classList = $(this).attr('class').split(/\s+/);
			$.each(classList, function(index, item) {
			    if (item.includes('productid')) {

			        let productID = item.split('_')[1];
	        		// set input disabled
			        parent.find('.b2bking_bulkorder_form_container_content_line_product').val(title);
			        parent.find('.b2bking_bulkorder_form_container_content_line_product').css('color', b2bking_display_settings.colorsetting );
			        parent.find('.b2bking_bulkorder_form_container_content_line_product').css('font-weight', 'bold');
			        parent.find('.b2bking_bulkorder_form_container_content_line_product').addClass('b2bking_selected_product_id_'+productID);
			        parent.find('.b2bking_bulkorder_form_container_content_line_product').after('<button class="b2bking_bulkorder_clear">'+b2bking_display_settings.clearx+'</button>');
			        parent.find('.b2bking_bulkorder_form_container_content_line_qty').val(1);

			        setTimeout(function(){
			        	parent.find('.b2bking_bulkorder_form_container_content_line_product').prop('readonly', true);
			        	parent.find('.b2bking_bulkorder_form_container_content_line_livesearch').css('display','none');
			        },125);

			        // Set max stock on item
			        if (stock[productID+'B2BKINGSTOCK'] !== null){
			        	parent.find('.b2bking_bulkorder_form_container_content_line_qty').attr('max', stock[productID+'B2BKINGSTOCK']);
			        }

			        if (stock[productID+'B2BKINGMIN'] !== null){
			        	parent.find('.b2bking_bulkorder_form_container_content_line_qty').attr('min', stock[productID+'B2BKINGMIN']);
			        }
			        if (stock[productID+'B2BKINGSTEP'] !== null){
			        	parent.find('.b2bking_bulkorder_form_container_content_line_qty').attr('step', stock[productID+'B2BKINGSTEP']);
			        }
			        if (stock[productID+'B2BKINGVAL'] !== null){
			        	parent.find('.b2bking_bulkorder_form_container_content_line_qty').val(stock[productID+'B2BKINGVAL']);
			        }

			        if (urls[productID+'B2BKINGURL'] !== null){
			        	parent.find('.b2bking_bulkorder_form_container_content_line_product').addClass('b2bking_bulkorder_form_container_content_line_product_url');
			        	parent.find('.b2bking_bulkorder_form_container_content_line_product').attr('data-url', urls[productID+'B2BKINGURL']);
			        }

			        

			        
			       
			    }
			});
			if (parseInt(b2bking_display_settings.quotes_enabled) !== 1){
				calculateBulkOrderTotals();
			}
		});

		$('body').on('click', '.b2bking_bulkorder_clear', function(){
			let parent = $(this).parent();
			currentline = parent;
			let line = parent.find('.b2bking_bulkorder_form_container_content_line_product');
			let qty = parent.find('.b2bking_bulkorder_form_container_content_line_qty');
			line.prop('disabled', false);
			line.prop('readonly', false);
			qty.removeAttr('max');
			qty.removeAttr('min');
			qty.removeAttr('step');

			line.removeAttr("style");
			line.val('');
			qty.val('');
			var classList = line.attr('class').split(/\s+/);
			$.each(classList, function(index, item) {
			    if (item.includes('b2bking_selected_product_id_')) {
			    	line.removeClass(item);
			    }
			});

			if (parseInt(b2bking_display_settings.quotes_enabled) !== 1){
				calculateBulkOrderTotals();
			}

			$(parent).find('.b2bking_bulkorder_form_container_content_line_product_url').removeAttr('data-url');
			$(parent).find('.b2bking_bulkorder_form_container_content_line_product_url').removeClass('b2bking_bulkorder_form_container_content_line_product_url');
			$(this).remove();


		});

		// on click add to cart
		$('.b2bking_bulkorder_form_container_bottom_add_button').on('click', function(){

			let productString = ''; 
			let listval = $(this).val();
			// loop through all bulk order form lines
			document.querySelectorAll('.b2bking_bulkorder_form_container_content_line_product').forEach(function(textinput) {
				var classList = $(textinput).attr('class').split(/\s+/);
				$.each(classList, function(index, item) {
					// foreach line if it has selected class, get selected product ID 
				    if (item.includes('b2bking_selected_product_id_')) {
				    	let productID = item.split('_')[4];
				    	let quantity = $(textinput).parent().find('.b2bking_bulkorder_form_container_content_line_qty').val();
				    	if (quantity > 0){
				    		// set product
				    		productString+=productID+':'+quantity+'|';
				    	}
				    }
				});
			});
			// if not empty, send
			if (productString !== ''){
				// replace icon with loader
				$('<img class="b2bking_loader_icon_button" src="'+b2bking_display_settings.loadertransparenturl+'">').insertBefore('.b2bking_bulkorder_form_container_bottom_add_button_icon');

				$('.b2bking_bulkorder_form_container_bottom_add_button_icon').remove();
				var datavar = {
		            action: 'b2bking_bulkorder_add_cart',
		            security: b2bking_display_settings.security,
		            productstring: productString,
		            listval: listval,
		        };

				$.post(b2bking_display_settings.ajaxurl, datavar, function(response){
					if (parseInt(b2bking_display_settings.redirect_cart_add_cart_classic_form) === 1){
						window.location = b2bking_display_settings.carturl;
					} else {
						// show "added to cart text"
						let svg = '<svg class="b2bking_bulkorder_form_container_bottom_add_button_icon" xmlns="http://www.w3.org/2000/svg" width="21" height="19" fill="none" viewBox="0 0 21 19"><path fill="#fff" d="M18.401 11.875H7.714l.238 1.188h9.786c.562 0 .978.53.854 1.087l-.202.901a2.082 2.082 0 011.152 1.87c0 1.159-.93 2.096-2.072 2.079-1.087-.016-1.981-.914-2.01-2.02a2.091 2.091 0 01.612-1.543H8.428c.379.378.614.903.614 1.485 0 1.18-.967 2.131-2.14 2.076-1.04-.05-1.886-.905-1.94-1.964a2.085 2.085 0 011.022-1.914L3.423 2.375H.875A.883.883 0 010 1.485V.89C0 .399.392 0 .875 0h3.738c.416 0 .774.298.857.712l.334 1.663h14.32c.562 0 .978.53.854 1.088l-1.724 7.719a.878.878 0 01-.853.693zm-3.526-5.64h-1.75V4.75a.589.589 0 00-.583-.594h-.584a.589.589 0 00-.583.594v1.484h-1.75a.589.589 0 00-.583.594v.594c0 .328.26.594.583.594h1.75V9.5c0 .328.261.594.583.594h.584a.589.589 0 00.583-.594V8.016h1.75a.589.589 0 00.583-.594v-.594a.589.589 0 00-.583-.594z"></path></svg>';
						$('.b2bking_bulkorder_form_container_bottom_add_button').html(svg + b2bking_display_settings.added_cart);
						$( document.body ).trigger( 'wc_fragment_refresh' );

					}
				});
			}
		});

		// on product or quantity change, calculate totals
		$('body').on('input', '.b2bking_bulkorder_form_container_content_line_qty', function(){
			// enforce max (stock)
			var max = parseInt($(this).attr('max'));


			var textinput = $(this).parent().find('.b2bking_bulkorder_form_container_content_line_product');
			var classes = $(textinput).attr('class');
			var theme = '';
			if (classes === undefined){
				textinput = $(this).parent().parent().parent().find('.b2bking_bulkorder_form_container_content_line_product');
				classes = $(textinput).attr('class');
				theme = 'cream';
			}

			var productID = 0;
			var classList = classes.split(/\s+/);
			$.each(classList, function(index, item) {
				// foreach line if it has selected class, get selected product ID 
			    if (item.includes('b2bking_selected_product_id_')) {
			    	productID = item.split('_')[4];
			    }
			});

			let totalQuantity = $(this).val();
			var cartQuantity = 0;

			if (b2bking_display_settings.cart_quantities[productID] !== undefined){
				cartQuantity = parseInt(b2bking_display_settings.cart_quantities[productID]);
				totalQuantity = parseInt(totalQuantity) + cartQuantity;
			}

			if (parseInt(b2bking_display_settings.cart_quantities_cartqty) !== 0){
				cartQuantity = parseInt(b2bking_display_settings.cart_quantities_cartqty);
				totalQuantity = parseInt(totalQuantity) + cartQuantity;
			}
			
	        if (totalQuantity > max){
	            $(this).val((max-cartQuantity));

	            let parent = $(this).parent();
	            // get max stock message
	            let newval = b2bking_display_settings.max_items_stock;
	            newval = newval.replace('%s', max);

	            // if message is not set to max stock, set it
	            let currentval = parent.find('.b2bking_bulkorder_form_container_content_line_product').val();

	            if (currentval !== newval){
	            	let originalval = parent.find('.b2bking_bulkorder_form_container_content_line_product').val();
	            	let originalcolor = parent.find('.b2bking_bulkorder_form_container_content_line_product').css('color');
	            	
	            	parent.find('.b2bking_bulkorder_form_container_content_line_product').val(newval);
	            	parent.find('.b2bking_bulkorder_form_container_content_line_product').css('color','rgb(194 25 25)');
	            	setTimeout(function(){
	            		parent.find('.b2bking_bulkorder_form_container_content_line_product').val(originalval);
	            		parent.find('.b2bking_bulkorder_form_container_content_line_product').css('color',originalcolor);
	            	}, 1200);
	            }
	            
	        }

			currentline = $(this).parent();
			if (theme === 'cream'){
				currentline = $(this).parent().parent().parent();
			}
			if (parseInt(b2bking_display_settings.quotes_enabled) !== 1){
				calculateBulkOrderTotals();
			}
		});

		function getIdsInForm(){
			var ids = [];

			// loop through all bulk order form lines
			document.querySelectorAll('.b2bking_bulkorder_form_container_content_line_product').forEach(function(textinput) {
				var classList = $(textinput).attr('class').split(/\s+/);
				$.each(classList, function(index, item) {
					// foreach line if it has selected class, get selected product ID 
				    if (item.includes('b2bking_selected_product_id_')) {
				    	let productID = item.split('_')[4];
				    	ids.push(productID);
				    }
				});
			});

			return ids;

		}

		

		function calculateBulkOrderTotals(){
			let total = 0;
			// loop through all bulk order form lines
			let textinput = currentline.find('.b2bking_bulkorder_form_container_content_line_product');


			var classList = $(textinput).attr('class').split(/\s+/);
			$.each(classList, function(index, item) {
				// foreach line if it has selected class, get selected product ID 
			    if (item.includes('b2bking_selected_product_id_')) {
			    	let productID = item.split('_')[4];
			    	let quantity = $(textinput).parent().find('.b2bking_bulkorder_form_container_content_line_qty').val();
			    	if (quantity > 0){
	    				let index = productID + 'B2BKINGPRICE';
	    				let price = parseFloat(prices[index]);

	    				// find if there's tiered pricing
	    				let indexTiers = productID + 'B2BTIERPRICE';    				
	    				let tieredprice = pricetiers[indexTiers];
	    				// if have tiered price

	    				if (tieredprice !== 0){
	    					// get total quantity (form + cart)
	    					let totalQuantity = quantity;
	    					if (b2bking_display_settings.cart_quantities[productID] !== undefined){
	    						let cartQuantity = parseInt(b2bking_display_settings.cart_quantities[productID]);
	    						totalQuantity = parseInt(quantity) + cartQuantity;
	    					}

	    					if (parseInt(b2bking_display_settings.cart_quantities_cartqty) !== 0){
	    						totalQuantity = parseInt(totalQuantity) + parseInt(b2bking_display_settings.cart_quantities_cartqty);
	    					}

	    					// get all ranges
	    					let ranges = tieredprice.split(';');
	    					let quantities_array = [];
	    					let prices_array = [];
	    					// first eliminate all quantities larger than the total quantity
	    					$.each(ranges, function(index, item) {
	    						let tier_values = item.split(':');
	    						tier_values[0] = parseInt(tier_values[0]);

	    						var tempvalue = tier_values[1];
	    						if (tempvalue !== undefined){
	    							tempvalue = tempvalue.toString().replace(',', '.');
	    						}
	    						tier_values[1] = parseFloat(tempvalue);


	    						if (tier_values[0] <= totalQuantity ){
	    							quantities_array.push(tier_values[0]);
	    							prices_array[tier_values[0]] = tier_values[1];
	    						}
	    					});
	    					
	    					if (quantities_array.length > 0){
	    						// continue and try to find price
	    						let largest = Math.max(...quantities_array);
	    						let finalpricetier = prices_array[largest];
	    						// only set it if the tier price is smaller than the group price
	    						if (price > finalpricetier){
	    							price = finalpricetier;
	    						}
	    					}
	    				}

	    				let subtotal = price * quantity;
	    				subtotal = parseFloat(subtotal.toFixed(2));
	    				setTimeout(function(){
	    					if (parseInt(b2bking_display_settings.accountingsubtotals) === 1){
	    						// get price html via WC PRICE
								var datavar = {
						            action: 'b2bking_accountingsubtotals',
						            security: b2bking_display_settings.security,
						            pricesent: subtotal,
						        };

								$.post(b2bking_display_settings.ajaxurl, datavar, function(response){
									$(textinput).parent().find('.b2bking_bulkorder_form_container_content_line_subtotal').html(response);
								});

	    					} else {
	    						$(textinput).parent().find('.b2bking_bulkorder_form_container_content_line_subtotal').text(b2bking_display_settings.currency_symbol+subtotal);
	    					}
	    				}, 100);

			    	} else {
			    		$(textinput).parent().find('.b2bking_bulkorder_form_container_content_line_subtotal').text(b2bking_display_settings.currency_symbol+0);
			    	}
			    } else {
			    	if (isIndigo === undefined){
				    	if ($(textinput).val() === ''){
				    		$(textinput).parent().find('.b2bking_bulkorder_form_container_content_line_subtotal').text(b2bking_display_settings.currency_symbol+0);	
				    	}
				    }
			    }
			});

			

			document.querySelectorAll('.b2bking_bulkorder_form_container_content_line_product').forEach(function(textinput) {
				var classList = $(textinput).attr('class').split(/\s+/);
				$.each(classList, function(index, item) {
					// foreach line if it has selected class, get selected product ID 
				    if (item.includes('b2bking_selected_product_id_')) {
				    	let productID = item.split('_')[4];
				    	let quantity = $(textinput).parent().find('.b2bking_bulkorder_form_container_content_line_qty').val();
				    	if (quantity > 0){
		    				let index = productID + 'B2BKINGPRICE';
		    				let price = parseFloat(prices[index]);


		    				// find if there's tiered pricing
		    				let indexTiers = productID + 'B2BTIERPRICE';
		    				let tieredprice = pricetiers[indexTiers];

		    				// if have tiered price
		    				if (tieredprice !== 0){
		    					// get total quantity (form + cart)
		    					let totalQuantity = quantity;
		    					if (b2bking_display_settings.cart_quantities[productID] !== undefined){
		    						let cartQuantity = parseInt(b2bking_display_settings.cart_quantities[productID]);
		    						totalQuantity = parseInt(quantity) + cartQuantity;
		    					}

		    					if (parseInt(b2bking_display_settings.cart_quantities_cartqty) !== 0){
		    						totalQuantity = parseInt(quantity) + parseInt(b2bking_display_settings.cart_quantities_cartqty);
		    					}

		    					// get all ranges
		    					let ranges = tieredprice.split(';');
		    					let quantities_array = [];
		    					let prices_array = [];
		    					// first eliminate all quantities larger than the total quantity
		    					$.each(ranges, function(index, item) {
		    						let tier_values = item.split(':');
		    						tier_values[0] = parseInt(tier_values[0]);

		    						var tempvalue = tier_values[1];
		    						if (tempvalue !== undefined){
		    							tempvalue = tempvalue.toString().replace(',', '.');
		    						}
		    						tier_values[1] = parseFloat(tempvalue);

		    						if (tier_values[0] <= totalQuantity ){
		    							quantities_array.push(tier_values[0]);
		    							prices_array[tier_values[0]] = tier_values[1];
		    						}
		    					});
		    					
		    					if (quantities_array.length > 0){
		    						// continue and try to find price
		    						let largest = Math.max(...quantities_array);
		    						let finalpricetier = prices_array[largest];
		    						// only set it if the tier price is smaller than the group price
		    						if (price > finalpricetier){
		    							price = finalpricetier;
		    						}
		    					}
		    				}


		    				let subtotal = price * quantity;
		    				subtotal = parseFloat(subtotal.toFixed(2));

		    				total = total + subtotal;
		    				total = parseFloat(total.toFixed(2));
				    	}
				    }
				});

			});


			if (parseInt(b2bking_display_settings.accountingsubtotals) === 1){
				// get price html via WC PRICE
				var datavar = {
		            action: 'b2bking_accountingsubtotals',
		            security: b2bking_display_settings.security,
		            pricesent: total,
		        };

				$.post(b2bking_display_settings.ajaxurl, datavar, function(response){
					$('.b2bking_bulkorder_form_container_bottom_total .woocommerce-Price-amount').html(response);
				});

			} else {
				$('.b2bking_bulkorder_form_container_bottom_total .woocommerce-Price-amount').text(b2bking_display_settings.currency_symbol+total);	
			}

		}

		// if this is indigo order form
		if (isIndigo !== undefined){
			// add "selected" style to list items
			// get pricing details that will allow to calculate subtotals
			document.querySelectorAll('.b2bking_bulkorder_form_container_content_line_product').forEach(function(textinput) {
				let inputValue = $(textinput).val().split(' (')[0];
				var datavar = {
		            action: 'b2bking_ajax_search',
		            security: b2bking_display_settings.security,
		            searchValue: inputValue,
		            searchType: 'purchaseListLoading',
		            dataType: 'json'
		        };

				$.post(b2bking_display_settings.ajaxurl, datavar, function(response){
					let results = response;
					if (results !== '"empty"'){
						let resultsObject = JSON.parse(results);
						Object.keys(resultsObject).forEach(function (index) {
							if (index.includes('B2BKINGPRICE')){
								prices[index] = resultsObject[index];
							} else if (index.includes('B2BTIERPRICE')){
								pricetiers[index] = resultsObject[index];
							} else if (index.includes('B2BKINGSTOCK')){
								stock[index] = resultsObject[index];
							} else if (index.includes('B2BKINGMIN')){
								min[index] = resultsObject[index];
							} else if (index.includes('B2BKINGMAX')){
								max[index] = resultsObject[index];
							} else if (index.includes('B2BKINGSTEP')){
								step[index] = resultsObject[index];
							} else if (index.includes('B2BKINGVAL')){
								val[index] = resultsObject[index];
							}
						});
					}
				});
				var productID = 0;
				var classList = $(textinput).attr('class').split(/\s+/);
				$.each(classList, function(index, item) {
				    if (item.includes('b2bking_selected_product_id_')) {
				    	productID = item.split('_')[4];
				    }
				});

				// Set max stock on item
				if (stock[productID+'B2BKINGSTOCK'] !== null){
					$(textinput).parent().find('.b2bking_bulkorder_form_container_content_line_qty').attr('max', stock[productID+'B2BKINGSTOCK']);
				}

				currentline = $(textinput).parent();
				if (parseInt(b2bking_display_settings.quotes_enabled) !== 1){
					calculateBulkOrderTotals();
				}
				
			});

			// plus minus buttons
			$('body').on('click', '.b2bking_cream_input_plus_button', function(){
				let input = $(this).parent().find('input');
				input[0].stepUp(1);
				$(input).trigger('input');

			});
			$('body').on('click', '.b2bking_cream_input_minus_button', function(){
				let input = $(this).parent().find('input');
				input[0].stepDown(1);
				$(input).trigger('input');
			});


			// add to cart button
			$('body').on('click', '.b2bking_bulkorder_indigo_add', function(){

				// if configure button
				if ($(this).hasClass('configure')){
					// open product in new tab
					let link = $(this).parent().parent().parent().find('.b2bking_bulkorder_indigo_product_container a').attr('href');
					window.open(link,'_blank');
					return;
				}

				// cancel add to cart function if this is view options button
				if ($(this).hasClass('b2bking_cream_view_options_button')){
					return;
				}
				if ($(this).hasClass('b2bking_none_in_stock')){
					return;
				}

				// cancel if not valid quantity
				if (!$(this).parent().parent().find('.b2bking_bulkorder_form_container_content_line_qty')[0].checkValidity()){
					$(this).parent().parent().find('.b2bking_bulkorder_form_container_content_line_qty')[0].reportValidity();
					return;
				}

				// check that there are no empty (choose an X) attributes, if there are, trigger validation
				let stop = false;
				jQuery(this).parent().parent().parent().find('select').each(function (index) {
					if (!jQuery(this)[0].checkValidity()){
						jQuery(this)[0].reportValidity();
						stop = true;
					}
				});
				if (stop){
					return;
				}

				
				// loader icon
				// if does not have none_in_stock class
				let thisbutton = $(this);

				if (!$(this).hasClass('b2bking_none_in_stock')){
					$(this).html('<img class="b2bking_loader_icon_button_indigo" src="'+b2bking_display_settings.loadertransparenturl+'">');
				}

				let textinput = $(this).parent().parent().find('.b2bking_bulkorder_form_container_content_line_product');

				var classes = $(textinput).attr('class');
				var theme = '';
				if (classes === undefined){
					textinput = $(this).parent().parent().parent().find('.b2bking_bulkorder_form_container_content_line_product');
					classes = $(textinput).attr('class');
					theme = 'cream';
				}


				var productID = 0;
				var classList = classes.split(/\s+/);
				$.each(classList, function(index, item) {
					// foreach line if it has selected class, get selected product ID 
				    if (item.includes('b2bking_selected_product_id_')) {
				    	productID = item.split('_')[4];
				    }
				});

				let qty = $(this).parent().parent().find('.b2bking_bulkorder_form_container_content_line_qty').val();

				// run stock quantity addable check			
				var datavar = {
		            action: 'b2bking_get_stock_quantity_addable',
		            security: b2bking_display_settings.security,
		            id: productID,
		        };

		        var qtyaddable = 9999999;

		        // check stock first
		        if (parseInt(b2bking_display_settings.b2bking_orderform_skip_stock_search) === 0){
		        	$.post(b2bking_display_settings.ajaxurl, datavar, function(response){
		        		qtyaddable = parseInt(response);
		        		stock_order_form_add(qtyaddable, thisbutton, qty, productID);
		        		
		        	});
		        } else {
		        	stock_order_form_add(qtyaddable, thisbutton, qty, productID);
		        }
				
			});

			function stock_order_form_add(qtyaddable, thisbutton, qty, productID){
				// if quantity addable is higher (or equal) than quantity requested, proceed
				if (qtyaddable === 9875678){ // number represents is sold individually, already in cart
					$(thisbutton).addClass('b2bking_none_in_stock');
					$(thisbutton).html(b2bking_display_settings.already_in_cart);
					$(thisbutton).parent().parent().find('.b2bking_bulkorder_form_container_content_line_qty').val(0);
					$(thisbutton).parent().parent().find('.b2bking_bulkorder_form_container_content_line_qty').attr('max', 0);

				} else if (qtyaddable >= qty && qty !== 0){
					var datavar = {
			            action: 'b2bking_bulkorder_add_cart_item',
			            security: b2bking_display_settings.security,
			            productid: productID,
			            productqty: qty,
			        };
			        var attributes = [];
			        jQuery('.variation_'+productID).each(function (index) {
			        	attributes.push($(this).attr('id')+'='+$(this).val());
			        });
			        datavar.attributes = attributes;


			        orderformadd(datavar, thisbutton, qty, qtyaddable);
				
				} else if (qtyaddable === 0){
					// 0 left in stock, permanently grey button
					$(thisbutton).addClass('b2bking_none_in_stock');

					$(thisbutton).html('0 ' + b2bking_display_settings.left_in_stock);
					$(thisbutton).parent().parent().find('.b2bking_bulkorder_form_container_content_line_qty').val(0);
					$(thisbutton).parent().parent().find('.b2bking_bulkorder_form_container_content_line_qty').attr('max', 0);


				} else {
					// x left in stock, temporarily grey button
					$(thisbutton).html(b2bking_display_settings.left_in_stock_low_left + qtyaddable + b2bking_display_settings.left_in_stock_low_right);
					$(thisbutton).addClass('b2bking_low_in_stock');
					// set qty of the item to the qty left
					$(thisbutton).parent().parent().find('.b2bking_bulkorder_form_container_content_line_qty').val(qtyaddable);

					// restore button to default
					setTimeout(function(){
						$(thisbutton).removeClass('b2bking_low_in_stock');
						$(thisbutton).html(b2bking_display_settings.add_to_cart);
					}, 2500);

				}
			}
				

			function orderformadd(datavar, thisbutton, qty, qtyaddable){

				if (addCartClear === 'yes'){
					addCartClear = 'no';
					$.post(b2bking_display_settings.ajaxurl, datavar, function(response){
						if (response === 'success'){

							$(thisbutton).removeClass('b2bking_low_in_stock');
							$(thisbutton).removeClass('b2bking_none_in_stock');

							// Refresh cart fragments
							$( document.body ).trigger( 'wc_fragment_refresh' );

							// update product qty icon
							let currentqty = parseInt($(thisbutton).parent().parent().parent().find('.b2bking_cream_product_nr_icon').text());
							let newqty = parseInt(qty)+currentqty;
							$(thisbutton).parent().parent().parent().find('.b2bking_cream_product_nr_icon').text(newqty);
							$(thisbutton).parent().parent().parent().find('.b2bking_cream_product_nr_icon').removeClass('b2bking_cream_product_nr_icon_hidden');

							if (b2bking_display_settings.cream_form_cart_button === 'cart'){
								let currentqtycorner = parseInt($('#b2bking_bulkorder_cream_filter_cart_text').text());
								let newcurrentqtycorner = parseInt(qty)+currentqtycorner;
								$('#b2bking_bulkorder_cream_filter_cart_text').text(newcurrentqtycorner);
								$('.b2bking_orderform_cart').removeClass('b2bking_orderform_cart_inactive');

							}
							if (b2bking_display_settings.cream_form_cart_button === 'checkout'){
								$('.b2bking_orderform_checkout').removeClass('b2bking_orderform_checkout_inactive');
							}

							let newqtyaddable = qtyaddable-qty;
					
							if (newqtyaddable > 0){
								// set button to 'Add more'
								$(thisbutton).html(b2bking_display_settings.add_more_indigo);
								$(thisbutton).addClass('b2bking_add_more_button');

								// if quantity left is lower than the quantity set in qty, lower qty to the qty left
								if (newqtyaddable < qty){
									$(thisbutton).parent().parent().find('.b2bking_bulkorder_form_container_content_line_qty').val(newqtyaddable);
								}
							} else {
								// 0 left in stock, permanently grey button
								$(thisbutton).addClass('b2bking_none_in_stock');
								$(thisbutton).html('0 ' + b2bking_display_settings.left_in_stock);
								$(thisbutton).parent().parent().find('.b2bking_bulkorder_form_container_content_line_qty').val(0);
								$(thisbutton).parent().parent().find('.b2bking_bulkorder_form_container_content_line_qty').attr('max', 0);

							}

							$( document.body ).trigger( 'b2bking_added_item_cart' );


							setTimeout(function(){

								addCartClear = 'yes';		

							}, 500);						
						}
					});

				} else {
					setTimeout(function(){
						orderformadd(datavar, thisbutton, qty, qtyaddable);
					}, 100);
				}
				
			}	

			// cream order form go cart
			$('body').on('click', '.b2bking_orderform_cart', function(){

				var mainthisparent = $(this).parent().parent().parent();

				if (!$(mainthisparent).find('.b2bking_orderform_cart').hasClass('b2bking_orderform_cart_inactive')){
					window.location = b2bking_display_settings.carturl;
				}
			});
			$('body').on('click', '.b2bking_orderform_checkout', function(){

				var mainthisparent = $(this).parent().parent().parent();

				if (!$(mainthisparent).find('.b2bking_orderform_checkout').hasClass('b2bking_orderform_checkout_inactive')){
					window.location = b2bking_display_settings.checkouturl;
				}

			});


			// search indigo form
			$('.b2bking_bulkorder_search_text_indigo').not('.b2bking_bulkorder_search_text_cream').on('input', function(){

				var mainthis = $(this);
				var mainthisparent = $(mainthis).parent().parent().parent();
				let thisSearchTime = Date.now();
				latestSearchTime = thisSearchTime;

		        if ($(this).length > 0){ // min x chars

		        	// show loader
		        	$(mainthisparent).find('.b2bking_bulkorder_form_container_content_indigo').html('<div class="b2bking_loader_indigo_content"><img class="b2bking_loader_icon_button_indigo" src="'+b2bking_display_settings.loadertransparenturl+'"></div>');


		        	// set timer for 600ms before loading the ajax search (resource consuming)
		        	setTimeout(function(){

		        		// if in the last 2 seconds there's been no new searches or input
		        		if (thisSearchTime === latestSearchTime || (ignoreTime)){

		        			var excludeval = $(mainthisparent).find('.b2bking_bulkorder_exclude').val();
		        			var productlistval = $(mainthisparent).find('.b2bking_bulkorder_product_list').val();

		        			var categoryval = $(mainthisparent).find('.b2bking_bulkorder_category').val();
		        			var sortby = $(mainthisparent).find('.b2bking_bulkorder_sortby').val();


		        			var datavar = {
					            action: 'b2bking_ajax_search',
					            security: b2bking_display_settings.security,
					            searchValue: $(mainthis).val(),
					            dataType: 'json',
					            theme: 'indigo',
					            exclude: excludeval,
					            productlist: productlistval,
					            sortby: sortby,
					            category: categoryval
					        };

							$.post(b2bking_display_settings.ajaxurl, datavar, function(response){

								// 1. populate data for prices
								let display = '';
								let results = response;
								let html = '';
								if (thisSearchTime === latestSearchTime || (ignoreTime)){
									if (parseInt(results) !== 1234){ // 1234 Integer for Empty
										let resultsObject = JSON.parse(results);
										Object.keys(resultsObject).forEach(function (index) {
											if (index.includes('B2BKINGPRICE')){
												prices[index] = resultsObject[index];
											} else if (index.includes('B2BTIERPRICE')){
												pricetiers[index] = resultsObject[index];
											} else if (index.includes('B2BKINGSTOCK')){
												stock[index] = resultsObject[index];
											} else if (index.includes('B2BKINGIMAGE')){
												images[index] = resultsObject[index];
											} else if (index.includes('B2BKINGURL')){
												urls[index] = resultsObject[index];
											} else if (index.includes('B2BKINGMIN')){
												min[index] = resultsObject[index];
											} else if (index.includes('B2BKINGMAX')){
												max[index] = resultsObject[index];
											} else if (index.includes('B2BKINGSTEP')){
												step[index] = resultsObject[index];
											} else if (index.includes('B2BKINGVAL')){
												val[index] = resultsObject[index];
											} else if (index.includes('HTML')){
												html = resultsObject[index];
											}									
										});

										// 2. show html and products
										$(mainthisparent).find('.b2bking_bulkorder_form_container_content_indigo').html(html);

									} else {
										// no products found

										$(mainthisparent).find('.b2bking_bulkorder_form_container_content_indigo').html('<div class="b2bking_bulkorder_indigo_noproducts">'+b2bking_display_settings.no_products_found+'</div><div class="b2bking_bulkorder_form_container_bottom b2bking_bulkorder_form_container_bottom_indigo"></div>');

									}
									
									


								}
							});

						}

					}, 400);
		        }
			});

			// cream order form filters category
			$('.b2bking_orderform_filters').on('click', function(){
				var mainthisparent = $(this).parent().parent().parent();

				// if attributes open, close it first
				if (jQuery('#b2bking_bulkorder_cream_filter_icon_attributes img').attr('src') === b2bking_display_settings.filters_close){
					$('.b2bking_orderform_attributes').click();
				}

				if ($(mainthisparent).find('.b2bking_bulkorder_form_cream_main_container').hasClass('b2bking_filters_open')){
					$(mainthisparent).find('#b2bking_bulkorder_cream_filter_icon img').attr('src',b2bking_display_settings.filters);
					$(mainthisparent).find('.b2bking_orderform_filters').css('background', '#fff');

					$(mainthisparent).find('.b2bking_bulkorder_form_cream_main_container.b2bking_filters_open, .b2bking_bulkorder_form_container_cream_filters.b2bking_filters_open, .b2bking_bulkorder_form_cream_main_container_content.b2bking_filters_open').addClass('b2bking_filters_closed').removeClass('b2bking_filters_open');
				} else {
					$(mainthisparent).find('#b2bking_bulkorder_cream_filter_icon img').attr('src',b2bking_display_settings.filters_close);
					$(mainthisparent).find('.b2bking_orderform_filters').css('background', '#f3f3f3');

					$(mainthisparent).find('.b2bking_bulkorder_form_cream_main_container.b2bking_filters_closed, .b2bking_bulkorder_form_container_cream_filters.b2bking_filters_closed, .b2bking_bulkorder_form_cream_main_container_content.b2bking_filters_closed').addClass('b2bking_filters_open').removeClass('b2bking_filters_closed');
				}

				// show first content sidebar, hide second one
				$(mainthisparent).find('.b2bking_bulkorder_form_container_cream_filters_content_first').css('display','');
				$(mainthisparent).find('.b2bking_bulkorder_form_container_cream_filters_content_second').css('display','none');
			});

			$('.b2bking_orderform_attributes').on('click', function(){
				var mainthisparent = $(this).parent().parent().parent();

				// if filters open, close it first
				if (jQuery('#b2bking_bulkorder_cream_filter_icon img').attr('src') === b2bking_display_settings.filters_close){
					$('.b2bking_orderform_filters').click();
				}


				if ($(mainthisparent).find('.b2bking_bulkorder_form_cream_main_container').hasClass('b2bking_filters_open')){
					$(mainthisparent).find('#b2bking_bulkorder_cream_filter_icon_attributes img').attr('src',b2bking_display_settings.attributes);
					$(mainthisparent).find('.b2bking_orderform_attributes').css('background', '#fff');

					$(mainthisparent).find('.b2bking_bulkorder_form_cream_main_container.b2bking_filters_open, .b2bking_bulkorder_form_container_cream_filters.b2bking_filters_open, .b2bking_bulkorder_form_cream_main_container_content.b2bking_filters_open').addClass('b2bking_filters_closed').removeClass('b2bking_filters_open');
				} else {
					$(mainthisparent).find('#b2bking_bulkorder_cream_filter_icon_attributes img').attr('src',b2bking_display_settings.filters_close);
					$(mainthisparent).find('.b2bking_orderform_attributes').css('background', '#f3f3f3');

					$(mainthisparent).find('.b2bking_bulkorder_form_cream_main_container.b2bking_filters_closed, .b2bking_bulkorder_form_container_cream_filters.b2bking_filters_closed, .b2bking_bulkorder_form_cream_main_container_content.b2bking_filters_closed').addClass('b2bking_filters_open').removeClass('b2bking_filters_closed');
				}

				// show second content sidebar, hide first one
				$(mainthisparent).find('.b2bking_bulkorder_form_container_cream_filters_content_first').css('display','none');
				$(mainthisparent).find('.b2bking_bulkorder_form_container_cream_filters_content_second').css('display','');
			});

			// show clear cream icon
			// on input, show clear if field not empty
			$('.b2bking_bulkorder_search_text_cream').on('input', function(){

				var mainthisparent = $(this).parent().parent().parent().parent();

				let value = $(this).val();
				if (value.length !== 0){
					// show clear
					$(mainthisparent).find('.b2bking_bulkorder_cream_search_icon_clear').removeClass('b2bking_bulkorder_cream_search_icon_hide').addClass('b2bking_bulkorder_cream_search_icon_show');
					$(mainthisparent).find('.b2bking_bulkorder_cream_search_icon_search').removeClass('b2bking_bulkorder_cream_search_icon_show').addClass('b2bking_bulkorder_cream_search_icon_hide');
				} else {
					// show icon
					$(mainthisparent).find('.b2bking_bulkorder_cream_search_icon_clear').removeClass('b2bking_bulkorder_cream_search_icon_show').addClass('b2bking_bulkorder_cream_search_icon_hide');
					$(mainthisparent).find('.b2bking_bulkorder_cream_search_icon_search').removeClass('b2bking_bulkorder_cream_search_icon_hide').addClass('b2bking_bulkorder_cream_search_icon_show');

				}
			});

			$('.b2bking_bulkorder_cream_search_icon_clear').on('click', function(){

				var mainthisparent = $(this).parent().parent().parent().parent();

				$(mainthisparent).find('.b2bking_bulkorder_search_text_cream').val('');
				$(mainthisparent).find('.b2bking_bulkorder_search_text_cream').trigger('input');
				$(mainthisparent).find('.b2bking_bulkorder_search_text_cream').focus();
			});

			// on click filter attributes
			$('.b2bking_bulkorder_filters_list_attributes li').on('click', function(){

				// get and set category
				let cat = $(this).val();
				var mainthisparent = $(this).parent().parent().parent().parent().parent().parent();
				var thisparent = $(this).parent();

				$(thisparent).find('.b2bking_attribute_value').val(cat);
				$(mainthisparent).find('.b2bking_bulkorder_search_text_indigo').trigger('input');

				// underline selected item
				$(this).parent().find('li').each(function (index) {
					$(this).css('text-decoration','none');
				});
				$(this).css('text-decoration','underline');
				
			});

			// on click filter category
			$('.b2bking_bulkorder_filters_list li').on('click', function(){

				// get and set category
				let cat = $(this).val();
				var mainthisparent = $(this).parent().parent().parent().parent().parent().parent();

				$(mainthisparent).find('.b2bking_bulkorder_category').val(cat);
				$(mainthisparent).find('.b2bking_bulkorder_search_text_indigo').trigger('input');

				// underline selected item
				$(mainthisparent).find('.b2bking_bulkorder_filters_list li').each(function (index) {
					$(this).css('text-decoration','none');
				});

				$(this).css('text-decoration','underline');
				
			});

			// on click filter sortby
			$('.b2bking_bulkorder_filters_list_sortby li').on('click', function(){

				var mainthisparent = $(this).parent().parent().parent().parent().parent().parent();

				// get and set category
				let sortby = $(this).attr('value');

				$(mainthisparent).find('.b2bking_bulkorder_sortby').attr('value',sortby);
				$(mainthisparent).find('.b2bking_bulkorder_search_text_indigo').trigger('input');

				// underline selected item
				$(mainthisparent).find('.b2bking_bulkorder_filters_list_sortby li').each(function (index) {
					$(this).css('text-decoration','none');
				});

				$(this).css('text-decoration','underline');

			});

			// search cream form
			$('.b2bking_bulkorder_search_text_indigo.b2bking_bulkorder_search_text_cream').on('input', function(){

				let thisSearchTime = Date.now();
				var mainthis = $(this);
				var mainthisparent = $(mainthis).parent().parent().parent().parent();
				latestSearchTime = thisSearchTime;

		        if ($(this).length > 0){ // min x chars

		        	// show loader
		        	$(mainthisparent).find('.b2bking_bulkorder_form_container_content_indigo').html('<div class="b2bking_loader_indigo_content b2bking_loader_cream_content"><div class="b2bking_loading_products_wrapper"><div class="b2bking_loading_products_text">'+b2bking_display_settings.loading_products_text+'</div><img class="b2bking_loader_icon_button_indigo" src="'+b2bking_display_settings.loadertransparenturl+'"></div></div>');


		        	// set timer for 600ms before loading the ajax search (resource consuming)
		        	setTimeout(function(){

		        		// if in the last 2 seconds there's been no new searches or input
		        		if (thisSearchTime === latestSearchTime || (ignoreTime)){

		        			var excludeval = $(mainthisparent).find('.b2bking_bulkorder_exclude').val();
		        			var productlistval = $(mainthisparent).find('.b2bking_bulkorder_product_list').val();

		        			var categoryval = $(mainthisparent).find('.b2bking_bulkorder_category').val();
		        			var sortby = $(mainthisparent).find('.b2bking_bulkorder_sortby').val();

		        			var attributesval = $(mainthisparent).find('.b2bking_bulkorder_attributes').val();
		        			var attributes = attributesval.split(',');

		        			var datavar = {
					            action: 'b2bking_ajax_search',
					            security: b2bking_display_settings.security,
					            searchValue: $(mainthis).val(),
					            dataType: 'json',
					            theme: 'cream',
					            sku: $(mainthisparent).find('.b2bking_order_form_show_sku').val(),
					            stock: $(mainthisparent).find('.b2bking_order_form_show_stock').val(),
					            exclude: excludeval,
					            productlist: productlistval,
					            category: categoryval,
					            attributes: attributesval,
					            sortby: sortby
					        };

					        attributes.forEach(function(item){
					        	item = item.trim();
					        	datavar['attr_'+item] = $('.b2bking_attribute_value_'+item).val();
					        });

							$.post(b2bking_display_settings.ajaxurl, datavar, function(response){

								// 1. populate data for prices
								let display = '';
								let results = response;
								let html = '';
								if (thisSearchTime === latestSearchTime || (ignoreTime)){
									if (parseInt(results) !== 1234){ // 1234 Integer for Empty
										let resultsObject = JSON.parse(results);
										Object.keys(resultsObject).forEach(function (index) {
											if (index.includes('B2BKINGPRICE')){
												prices[index] = resultsObject[index];
											} else if (index.includes('B2BTIERPRICE')){
												pricetiers[index] = resultsObject[index];
											} else if (index.includes('B2BKINGSTOCK')){
												stock[index] = resultsObject[index];
											} else if (index.includes('B2BKINGIMAGE')){
												images[index] = resultsObject[index];
											} else if (index.includes('B2BKINGURL')){
												urls[index] = resultsObject[index];
											} else if (index.includes('B2BKINGMIN')){
												min[index] = resultsObject[index];
											} else if (index.includes('B2BKINGMAX')){
												max[index] = resultsObject[index];
											} else if (index.includes('B2BKINGSTEP')){
												step[index] = resultsObject[index];
											} else if (index.includes('B2BKINGVAL')){
												val[index] = resultsObject[index];
											} else if (index.includes('HTML')){
												html = resultsObject[index];
											}									
										});

										// 2. show html and products
										$(mainthisparent).find('.b2bking_bulkorder_form_container_content_indigo').html(html);

									} else {
										// no products found

										$(mainthisparent).find('.b2bking_bulkorder_form_container_content_indigo').html('<div class="b2bking_bulkorder_indigo_noproducts b2bking_bulkorder_cream_noproducts"><img class="b2bking_bulkorder_cream_noproducts_img" src="'+b2bking_display_settings.no_products_found_img+'"><div class="b2bking_cream_noproductsfound_text">'+b2bking_display_settings.no_products_found+'</div></div><div class="b2bking_bulkorder_form_container_bottom b2bking_bulkorder_form_container_bottom_indigo b2bking_bulkorder_form_container_bottom_cream"></div>');

									}
									
									


								}
							});

						}

					}, 400); //400
		        }
			});

		}

		// scroll to top of page when next / previous buttons are clicked:
		jQuery('body').on('click', '.b2bking_bulkorder_pagination_button', function(){
		    window.scrollTo(0, 0);
		});

		// pagination
		$('body').on('click', '.b2bking_bulkorder_pagination_button', function(){

			var mainthisparent = $(this).parent().parent().parent().parent().parent().parent();

			var attributesval = $(mainthisparent).find('.b2bking_bulkorder_attributes').val();
			var attributes = attributesval.split(',');

			var datavar = {
	            action: 'b2bking_ajax_search',
	            security: b2bking_display_settings.security,
	            dataType: 'json',
	            theme: b2bking_pagination_theme,
	            sku: $(mainthisparent).find('.b2bking_order_form_show_sku').val(),
	            stock: $(mainthisparent).find('.b2bking_order_form_show_stock').val(),
	            searchValue: '',
	            sortby: $(mainthisparent).find('.b2bking_bulkorder_sortby').val(),
	            paginationdata: b2bking_pagination_data,
	            pagerequested: $(this).val(),

	            category: $(mainthisparent).find('.b2bking_bulkorder_category').val(),
	            productlist: $(mainthisparent).find('.b2bking_bulkorder_product_list').val(),
	            exclude: $(mainthisparent).find('.b2bking_bulkorder_exclude').val(),
	            attributes: attributesval,

	        };

	        attributes.forEach(function(item){
	        	datavar['attr_'+item] = $('.b2bking_attribute_value_'+item).val();
	        });



	        // show loader
	        $(mainthisparent).find('.b2bking_bulkorder_form_container_content_indigo').html('<div class="b2bking_loader_indigo_content b2bking_loader_cream_content"><img class="b2bking_loader_icon_button_indigo" src="'+b2bking_display_settings.loadertransparenturl+'"></div>');

			$.post(b2bking_display_settings.ajaxurl, datavar, function(response){

				// 1. populate data for prices
				let results = response;
				let html = '';
				if (parseInt(results) !== 1234){ // 1234 Integer for Empty
					let resultsObject = JSON.parse(results);
					Object.keys(resultsObject).forEach(function (index) {
						if (index.includes('B2BKINGPRICE')){
							prices[index] = resultsObject[index];
						} else if (index.includes('B2BTIERPRICE')){
							pricetiers[index] = resultsObject[index];
						} else if (index.includes('B2BKINGSTOCK')){
							stock[index] = resultsObject[index];
						} else if (index.includes('B2BKINGIMAGE')){
							images[index] = resultsObject[index];
						} else if (index.includes('B2BKINGURL')){
							urls[index] = resultsObject[index];
						} else if (index.includes('B2BKINGMIN')){
							min[index] = resultsObject[index];
						} else if (index.includes('B2BKINGMAX')){
							max[index] = resultsObject[index];
						} else if (index.includes('B2BKINGSTEP')){
							step[index] = resultsObject[index];
						} else if (index.includes('B2BKINGVAL')){
							val[index] = resultsObject[index];
						} else if (index.includes('HTML')){
							html = resultsObject[index];
						}									
					});

					// 2. show html and products
					$(mainthisparent).find('.b2bking_bulkorder_form_container_content_indigo').html(html);

				}
				
			});
		});


		// show hide variations cream
		$('body').on('click', '.b2bking_cream_view_options_button', function(){
			let parentid = $(this).val();

			if ($(this).hasClass('b2bking_cream_view_options_button_view')){
				// show variations
				$('.b2bking_bulkorder_form_container_content_line_cream_'+parentid).removeClass('b2bking_bulkorder_form_container_content_line_cream_hidden').addClass('b2bking_cream_line_variation_colored');
				$(this).removeClass('b2bking_cream_view_options_button_view');
				$(this).addClass('b2bking_cream_view_options_button_hide');
				$(this).find('.b2bking_cream_view_options_text').removeClass('b2bking_text_active').addClass('b2bking_text_inactive');
				$(this).find('.b2bking_cream_hide_options_text').removeClass('b2bking_text_inactive').addClass('b2bking_text_active');
				
				// parent
				$(this).parent().parent().parent().addClass('b2bking_cream_view_options_button_hide');
				$(this).parent().parent().parent().removeClass('b2bking_cream_view_options_button_view');

			} else {
				if ($(this).hasClass('b2bking_cream_view_options_button_hide')){
					// hide variations
					$('.b2bking_bulkorder_form_container_content_line_cream_'+parentid).addClass('b2bking_bulkorder_form_container_content_line_cream_hidden');
					$(this).addClass('b2bking_cream_view_options_button_view');
					$(this).removeClass('b2bking_cream_view_options_button_hide');
					$(this).find('.b2bking_cream_view_options_text').addClass('b2bking_text_active').removeClass('b2bking_text_inactive');
					$(this).find('.b2bking_cream_hide_options_text').addClass('b2bking_text_inactive').removeClass('b2bking_text_active');

					// parent
					$(this).parent().parent().parent().addClass('b2bking_cream_view_options_button_view');
					$(this).parent().parent().parent().removeClass('b2bking_cream_view_options_button_hide');
				}
			}


		});


		$('body').on('click', '.b2bking_bulkorder_back_top', function(){
			$("html, body").animate({ scrollTop: 0 }, "slow");
		});

		// trigger first search
		jQuery('.b2bking_bulkorder_search_text_indigo').trigger('input');
		

		/* Bulk order form END */

		// Subaccounts Login as Sub
		// when clicking shop as customer
		$('body').on('click', '.b2bking_subaccounts_account_button_login', function(){
			var customerid = $(this).val();
			var datavar = {
	            action: 'b2bkingloginsubaccount',
	            security: b2bking_display_settings.security,
	            customer: customerid,
	        };

	        $.post(b2bking_display_settings.ajaxurl, datavar, function(response){
	        	window.location = b2bking_display_settings.shopurl;
	        });
		});

		$('#b2bking_return_agent').on('click', function(){
			var agentid = $(this).val();
			var agentregistered = $('#b2bking_return_agent_registered').val();

			var datavar = {
	            action: 'b2bkingswitchtoagent',
	            security: b2bking_display_settings.security,
	            agent: agentid,
	            agentdate: agentregistered,
	        };

	        $.post(b2bking_display_settings.ajaxurl, datavar, function(response){
	        	window.location = b2bking_display_settings.subaccountsurl;
	        });
		});


		/* Purchase Lists START */

		// click on purchase list item
		
		$('body').on('click', '.b2bking_bulkorder_form_container_content_line_product', function() {
			let url = $(this).attr('data-url');
			if (url !== undefined){
				if (url.length > 0){
					window.open(url,'_blank');
				}
			}
			
		});

		// Download Purchase Lists
		// On clicking download price list
		$('.b2bking_download_list_button').on('click', function() {
			// get list id
			var classList = $(this).attr('class').split(/\s+/);
			$.each(classList, function(index, item) {
				// foreach line if it has selected class, get selected product ID 
			    if (item.includes('id_')) {
			    	let listid = item.split('_')[1];
			    	window.location = b2bking_display_settings.ajaxurl + '?action=b2bkingdownloadpurchaselist&list='+listid+'&security=' + b2bking_display_settings.security;
			    }
			});
	    });

		// purchase lists data table
		if (typeof $('#b2bking_purchase_lists_table').DataTable === "function") { 
			$('#b2bking_purchase_lists_table').dataTable({
	            "language": {
	                "url": b2bking_display_settings.datatables_folder+b2bking_display_settings.purchase_lists_language_option+'.json'
	            }
	        });
		}

		// on click 'trash' in purchase list
		$('.b2bking_bulkorder_form_container_bottom_delete_button').on('click', function(){
			if(confirm(b2bking_display_settings.are_you_sure_delete_list)){
				let listId = $(this).val();

				// replace icon with loader
				$('<img class="b2bking_loader_icon_button" src="'+b2bking_display_settings.loadertransparenturl+'">').insertBefore('.b2bking_bulkorder_form_container_bottom_delete_button_icon');
				$('.b2bking_bulkorder_form_container_bottom_delete_button_icon').remove();

				var datavar = {
		            action: 'b2bking_purchase_list_delete',
		            security: b2bking_display_settings.security,
		            listid: listId
		        };

				$.post(b2bking_display_settings.ajaxurl, datavar, function(response){
					window.location = b2bking_display_settings.purchaselistsurl;
				});
			}
		});

		
		// on click 'update' in purchase list
		$('.b2bking_bulkorder_form_container_bottom_update_button').on('click', function(){
			let listId = $(this).val();

			let productString = ''; 
			// loop through all bulk order form lines
			document.querySelectorAll('.b2bking_bulkorder_form_container_content_line_product').forEach(function(textinput) {
				var classList = $(textinput).attr('class').split(/\s+/);
				$.each(classList, function(index, item) {
					// foreach line if it has selected class, get selected product ID 
				    if (item.includes('b2bking_selected_product_id_')) {
				    	let productID = item.split('_')[4];
				    	let quantity = $(textinput).parent().find('.b2bking_bulkorder_form_container_content_line_qty').val();
				    	if (quantity > 0 || parseInt(b2bking_display_settings.lists_zero_qty) === 1){
				    		// set product
				    		productString+=productID+':'+quantity+'|';
				    	}
				    }
				});
			});
			// if not empty, send
			if (productString !== ''){
				// replace icon with loader
				var buttonoriginal = $('.b2bking_bulkorder_form_container_bottom_update_button').html();
				$('<img class="b2bking_loader_icon_button" src="'+b2bking_display_settings.loadertransparenturl+'">').insertBefore('.b2bking_bulkorder_form_container_bottom_update_button_icon');
				$('.b2bking_bulkorder_form_container_bottom_update_button_icon').remove();

				var datavar = {
		            action: 'b2bking_purchase_list_update',
		            security: b2bking_display_settings.security,
		            productstring: productString,
		            listid: listId
		        };

				$.post(b2bking_display_settings.ajaxurl, datavar, function(response){
					location.reload();
				});
			}
		});


		// if this is a purchase list
		let isPurchaseList = $('#b2bking_purchase_list_page').val();
		if (isPurchaseList !== undefined){
			// add "selected" style to list items
			$('.b2bking_bulkorder_form_container_content_line_product').css('color', b2bking_display_settings.colorsetting);

			$('.b2bking_bulkorder_form_container_content_line_product').css('font-weight', 'bold' );
			// get pricing details that will allow to calculate subtotals
			document.querySelectorAll('.b2bking_bulkorder_form_container_content_line_product').forEach(function(textinput) {
				let inputValue = $(textinput).val().split(' (')[0];
				var datavar = {
		            action: 'b2bking_ajax_search',
		            security: b2bking_display_settings.security,
		            searchValue: inputValue,
		            searchType: 'purchaseListLoading',
		            dataType: 'json'
		        };

				$.post(b2bking_display_settings.ajaxurl, datavar, function(response){
					let results = response;
					if (results !== '"empty"'){
						let resultsObject = JSON.parse(results);
						Object.keys(resultsObject).forEach(function (index) {
							if (index.includes('B2BKINGPRICE')){
								prices[index] = resultsObject[index];
							} else if (index.includes('B2BTIERPRICE')){
								pricetiers[index] = resultsObject[index];
							} else if (index.includes('B2BKINGSTOCK')){
								stock[index] = resultsObject[index];
							} else if (index.includes('B2BKINGMIN')){
								min[index] = resultsObject[index];
							} else if (index.includes('B2BKINGMAX')){
								max[index] = resultsObject[index];
							} else if (index.includes('B2BKINGSTEP')){
								step[index] = resultsObject[index];
							} else if (index.includes('B2BKINGVAL')){
								val[index] = resultsObject[index];
							}
						});
					}

					var productID = 0;
					var classList = $(textinput).attr('class').split(/\s+/);
					$.each(classList, function(index, item) {
					    if (item.includes('b2bking_selected_product_id_')) {
					    	productID = item.split('_')[4];
					    }
					});

					// Set max stock on item
					if (stock[productID+'B2BKINGSTOCK'] !== null){
						$(textinput).parent().find('.b2bking_bulkorder_form_container_content_line_qty').attr('max', stock[productID+'B2BKINGSTOCK']);
					}

					currentline = $(textinput).parent();
					if (parseInt(b2bking_display_settings.quotes_enabled) !== 1){
						calculateBulkOrderTotals();
					}
				});
				
				
			});

		}
		

		$('body').on('click', '.b2bking_add_cart_to_purchase_list_button', function(){

			let title = window.prompt(b2bking_display_settings.save_list_name, "");
			if (title !== '' && title !== null){

				var datavar = {
		            action: 'b2bking_save_cart_to_purchase_list',
		            security: b2bking_display_settings.security,
		            title: title,
		            dataType: 'json'
		        };

				$.post(b2bking_display_settings.ajaxurl, datavar, function(response){
					$('.b2bking_add_cart_to_purchase_list_button').text(b2bking_display_settings.list_saved);
					$('.b2bking_add_cart_to_purchase_list_button').prop('disabled', true);
				});
			}
		});

		// Tiered Pricing Table Active Color Hover Script
		setTimeout(function(){
			if (parseInt(b2bking_display_settings.is_enabled_color_tiered) === 1){
				setHoverColorTable();
			}
		}, 200);

		$('body').on('input', 'input[name=quantity]', function(){
			let quantity = $(this).val();
			if (parseInt(b2bking_display_settings.is_enabled_color_tiered) === 1){
				setHoverColorTable(quantity);
			}
		});
		$('body').on('change', 'input[name=quantity]', function(){
			let quantity = $(this).val();
			if (parseInt(b2bking_display_settings.is_enabled_color_tiered) === 1){
				setHoverColorTable(quantity);
			}
		});

		$('body').on('change', 'select[name=quantity]', function(){
			let quantity = $(this).val();
			if (parseInt(b2bking_display_settings.is_enabled_color_tiered) === 1){
				setHoverColorTable(quantity);
			}
		});
		$('body').on('change', '.variations select', function(){
			if (parseInt(b2bking_display_settings.is_enabled_color_tiered) === 1){
				setHoverColorTable();
			}
		});

		// table is clickable, allow clicking ranges to set qty
		if (parseInt(b2bking_display_settings.table_is_clickable) === 1){
			jQuery('body').on('click', '.b2bking_tiered_price_table tbody tr', function(){
				var rangetext = jQuery(this).find('td:nth-child(1)').data('range');
				let values = rangetext.split(' - ');
				if (values.length === 2){
					var setqty = parseInt(values[1]);
				} else {
					// is of form 456+
					var setqty = parseInt(rangetext.split('+')[0]);
				}

				// apply min, max, step values
				var min = $('input[name=quantity]').attr('min');
				var max = $('input[name=quantity]').attr('max');
				var step = $('input[name=quantity]').attr('step');

				if (max === undefined || max === '') {
					max = 999999999;
				}
				if (min === undefined || min === '') {
					min = 1;
				}

				setqty = setqty > parseFloat( max ) ? max : setqty;
				setqty = setqty < parseFloat( min ) ? min : setqty;

				// if there is a step
				if (step !== undefined && step !== ''){
					let difference = setqty%step;
					let difmin = 0;
					let difmax = 0; 

					// if current qty does not step
					if (parseInt(difference) !== 0) {

						// get difmin and difmax = numbers to substract or increase to reach step
						if ((setqty - difference) % step === 0){
							difmin = difference;
							difmax = step - difmin;
						} else {
							difmax = difference;
							difmin = step - difmax
						}

						// change it
						// if adding difference doesn't go over max, add it, else substract it
						//setqty = ((setqty + difmax) < parseFloat( max )) ? setqty+difmax : setqty-difmin;

						// above is old algorithm. Here, since we get the max range value, we always want to substract it
						// EXCEPT when in the situation where there's a range in form of '40+', only 1 number, then we go up
						if (values.length === 2){
							setqty = setqty-difmin;
						} else {
							setqty = setqty+difmax;
						}
					}
				}


				$('input[name=quantity]').val(setqty);
				$('input[name=quantity]').trigger('input').trigger('change');
			});
		}

		
		function setHoverColorTable(quantity = 'no'){

			// remove all colors from table
			$('.b2bking_has_color').removeClass('b2bking_has_color');
			// get product id from table
			if ($('.b2bking_shop_table').attr('class') !== undefined){
				var classList = $('.b2bking_shop_table').attr('class').split(/\s+/);
				var productid = 0;
				$.each(classList, function(index, item) {
					// foreach line if it has selected class, get selected product ID 
				    if (item.includes('b2bking_productid_')) {
				    	productid = parseInt(item.split('_')[2]);
				    }
				});
				// get input quantity
				if ($('input[name=quantity]').val() !== undefined){
					var inputQuantity = parseInt($('input[name=quantity]').val());
				} else if ($('select[name=quantity]').val() !== undefined){
					var inputQuantity = parseInt($('select[name=quantity]').val());
				}
				if (quantity !== 'no'){
					if (typeof inputQuantity !== 'undefined') {
						inputQuantity = parseInt(quantity);
					} else {
						var inputQuantity = parseInt(quantity);
					}
				}
				// get cart item quantity

				var cartQuantity = 0;
				if (parseInt(b2bking_display_settings.add_cart_quantity_tiered_table) === 1){

					if (b2bking_display_settings.cart_quantities[productid] !== undefined){
						cartQuantity = parseInt(b2bking_display_settings.cart_quantities[productid]);
					}
					if (parseInt(b2bking_display_settings.cart_quantities_cartqty) !== 0){
						cartQuantity = parseInt(b2bking_display_settings.cart_quantities_cartqty);
					}
				}


				// calculate total quantity of the item
				var totalQuantity = inputQuantity + cartQuantity;


				// go through all ranges and check quantity. 
				// first set it to original price
				$('.b2bking_tiered_active_price').text($('.summary .b2bking_tiered_range_replaced:first').text().split(' – ')[1]);
				// if can't be found,
				if ($('.summary .b2bking_tiered_range_replaced:first').text().split(' – ')[1] === undefined){
					$('.b2bking_tiered_active_price').text($('.b2bking_tiered_range_replaced:first').text().split(' – ')[1]);
				}

				// if exists a specific productid of the page
				if (parseInt(b2bking_display_settings.productid) !== 0){
					var rangereplaced = jQuery('.b2bking_tiered_price_range_replaced_' + b2bking_display_settings.productid + ':first').text();
					if (rangereplaced !== ''){
						$('.b2bking_tiered_active_price').text(rangereplaced.split(' – ')[1]);
					}
				}

				let totalpricevalue2 = $('.b2bking_tiered_range_original_price').val() * inputQuantity;
				if (!$.isNumeric( totalpricevalue2 )){
					totalpricevalue2 = 0;
				}
				
				$('.b2bking_tiered_total_price').text(totalpricevalue2.toFixed(2)+' '+b2bking_display_settings.currency_symbol);

				$('.b2bking_shop_table.b2bking_productid_'+productid+' tr td:nth-child(1)').each(function(){
					let rangeText = $(this).data('range');
					let values = rangeText.split(' - ');

					if (values.length === 2){
						// is of form 123 - 456
						let first = parseInt(values[0]);
						let second = parseInt(values[1]);
						if (totalQuantity >= first && totalQuantity <= second){
							// set color
							$(this).parent().find('td').addClass('b2bking_has_color');
							if (parseInt(b2bking_display_settings.is_enabled_discount_table) === 1){
								$('.b2bking_tiered_active_price').text($(this).parent().find('td:nth-child(3)').text());
								let totalpricevalue = $(this).parent().find('td:nth-child(3) .b2bking_hidden_tier_value').val()*inputQuantity;
								$('.b2bking_tiered_total_price').text(totalpricevalue.toFixed(2)+' '+b2bking_display_settings.currency_symbol);
							} else {
								$('.b2bking_tiered_active_price').text($(this).parent().find('td:nth-child(2)').text());
								let totalpricevalue = $(this).parent().find('td:nth-child(2) .b2bking_hidden_tier_value').val()*inputQuantity;
								$('.b2bking_tiered_total_price').text(totalpricevalue.toFixed(2)+' '+b2bking_display_settings.currency_symbol);

							}
						}
					} else if (!rangeText.includes('+')){
						// exception if the user enters 1 as a quantity in the table
						if (totalQuantity === parseInt(rangeText)){
							$(this).parent().find('td').addClass('b2bking_has_color');
							if (parseInt(b2bking_display_settings.is_enabled_discount_table) === 1){
								$('.b2bking_tiered_active_price').text($(this).parent().find('td:nth-child(3)').text());
								let totalpricevalue = $(this).parent().find('td:nth-child(3) .b2bking_hidden_tier_value').val()*inputQuantity;
								$('.b2bking_tiered_total_price').text(totalpricevalue.toFixed(2)+' '+b2bking_display_settings.currency_symbol);
							} else {
								$('.b2bking_tiered_active_price').text($(this).parent().find('td:nth-child(2)').text());
								let totalpricevalue = $(this).parent().find('td:nth-child(2) .b2bking_hidden_tier_value').val()*inputQuantity;
								$('.b2bking_tiered_total_price').text(totalpricevalue.toFixed(2)+' '+b2bking_display_settings.currency_symbol);
							}
						}
					} else {
						// is of form 456+
						let valuePlus = parseInt(rangeText.split('+')[0]);
						if (totalQuantity >= valuePlus){
							// set color
							$(this).parent().find('td').addClass('b2bking_has_color');
							if (parseInt(b2bking_display_settings.is_enabled_discount_table) === 1){
								$('.b2bking_tiered_active_price').text($(this).parent().find('td:nth-child(3)').text());
								let totalpricevalue = $(this).parent().find('td:nth-child(3) .b2bking_hidden_tier_value').val()*inputQuantity;
								$('.b2bking_tiered_total_price').text(totalpricevalue.toFixed(2)+' '+b2bking_display_settings.currency_symbol);
							} else {
								$('.b2bking_tiered_active_price').text($(this).parent().find('td:nth-child(2)').text());
								let totalpricevalue = $(this).parent().find('td:nth-child(2) .b2bking_hidden_tier_value').val()*inputQuantity;
								$('.b2bking_tiered_total_price').text(totalpricevalue.toFixed(2)+' '+b2bking_display_settings.currency_symbol);
							}
						}
					}
				});

				$( document.body ).trigger( 'b2bking_set_hover_finish' ); 
			}
		}


		//

		/* Purchase Lists END */

		/* Checkout Registration Fields Checkbox*/
		
		if (parseInt(b2bking_display_settings.ischeckout) === 1 && parseInt(b2bking_display_settings.validate_vat_checkout) !== 1){
			showHideCheckout();

			$('#createaccount').change(showHideCheckout);
		}

		function showHideCheckout(){
			if($('#createaccount').prop('checked') || typeof $('#createaccount').prop('checked') === 'undefined') {
		    	$('#b2bking_checkout_registration_main_container_fields, .b2bking_registration_roles_dropdown_section').css('display','block');
		    	$('.b2bking_custom_field_req_required').prop('required','true');

		    } else {      
		    	$('#b2bking_checkout_registration_main_container_fields, .b2bking_registration_roles_dropdown_section').css('display','none');
		    	$('.b2bking_custom_field_req_required').removeAttr('required');
		    }
		}	

		// Fix issue with tiered price range below pricing
		document.querySelectorAll('.b2bking_both_prices_price.b2bking_b2b_price_price').forEach(function(textinput) {

			var str = jQuery(textinput).val();
			if (str !== undefined){
				if (parseInt(str.length) === 0){
					var classList = $(textinput).attr('class').split(/\s+/);
					$.each(classList, function(index, item) {
						// foreach line if it has selected class, get selected product ID 
					    if (item.includes('b2bking_b2b_price_id_')) {
					    	let productID = item.split('_')[4];
					    	// if empty price, find tiered range below and move it inside
					    	var htm = jQuery('.b2bking_tiered_price_range_replaced_'+productID).html();
					    	jQuery('.b2bking_tiered_price_range_replaced_'+productID).remove();
					    	jQuery('.b2bking_both_prices_price.b2bking_b2b_price_price.b2bking_b2b_price_id_'+productID).html(htm);
					    }
					});
				}
			}
		});



		// Support Required Multiple Quantity Step for Individual Variations
		$('body').on('show_variation', '.single_variation_wrap', function ( event, variation ) {
			var quantity_input_var = jQuery( this ).parent().find( '[name=quantity]' );

			if (variation.step !== undefined){
				quantity_input_var.attr( 'step', variation.step ).trigger( 'change' );
			} else {
				// if no step, set it to 1
				quantity_input_var.attr( 'step', 1 ).trigger( 'change' );
			}

			// modify current value
			var qty_val = parseFloat( quantity_input_var.val() );

			if ( isNaN( qty_val ) ) {
				qty_val = variation.min_qty;
			} else {
				qty_val = qty_val > parseFloat( variation.max_qty ) ? variation.max_qty : qty_val;
				qty_val = qty_val < parseFloat( variation.min_qty ) ? variation.min_qty : qty_val;
			}

			if (variation.max_qty === undefined || variation.max_qty === '') {
				variation.max_qty = 999999999;
			}

			// if there is a step
			if (variation.step !== undefined){
				let difference = qty_val%variation.step;
				let difmin = 0;
				let difmax = 0; 

				// if current qty does not step
				if (parseInt(difference) !== 0) {

					// get difmin and difmax = numbers to substract or increase to reach step
					if ((qty_val - difference) % variation.step === 0){
						difmin = difference;
						difmax = variation.step - difmin;
					} else {
						difmax = difference;
						difmin = variation.step - difmax
					}

					// change it
					// if adding difference doesn't go over max, add it, else substract it
					qty_val = ((qty_val + difmax) < parseFloat( variation.max_qty )) ? qty_val+difmax : qty_val-difmin;
				}
			}

			// set values
			quantity_input_var.val(qty_val);


			quantity_input_var.attr( 'min', variation.min_qty ).trigger( 'change' );
			quantity_input_var.attr( 'max', variation.max_qty ).trigger( 'change' );



			/*
			var variation_id = variation.variation_id;
			var quantity_select_var = jQuery( this ).parent().find( '[name=quantity_pq_dropdown]' );
			var cur_val = quantity_input_var.val();

			if(variation_id > 0 && product_quantities[ variation_id ] !== undefined) {
				
				var quantity_dropdown_var = jQuery( this ).parent().find( 'select[name=quantity_pq_dropdown]' );
				var max_qty_var = ( ( !isNaN (parseFloat( product_quantities[ variation_id ][ 'max_qty' ] ) ) && parseFloat( product_quantities[ variation_id ][ 'max_qty' ] ) > 0 ) ? parseFloat( product_quantities[ variation_id ][ 'max_qty' ] ) : ''  );
				var min_qty_var = ( !isNaN (parseFloat( product_quantities[ variation_id ][ 'min_qty' ] ) ) ? parseFloat( product_quantities[ variation_id ][ 'min_qty' ] ) : 1  );
				var default_var = ( !isNaN (parseFloat( product_quantities[ variation_id ][ 'default' ] ) ) ? parseFloat( product_quantities[ variation_id ][ 'default' ] ) : 1  );
				var lowest_var = ( !isNaN (parseFloat( product_quantities[ variation_id ][ 'lowest_qty' ] ) ) ? parseFloat( product_quantities[ variation_id ][ 'lowest_qty' ] ) : 1  );
				
				if ( quantity_dropdown_var.length <= 0 ) {
					quantity_input_var.prop( 'max', max_qty_var );
				}
			}
			*/


		});

		/* set ajax add to cart fragments refresh, for after clicking on add to quote */
		jQuery( document.body ).on( 'added_to_cart', function(){
			setTimeout(function(){
				jQuery( document.body ).trigger( 'wc_fragment_refresh' );
			}, 25);
			setTimeout(function(){
				jQuery( document.body ).trigger( 'wc_fragment_refresh' );
			}, 50);
			setTimeout(function(){
				jQuery( document.body ).trigger( 'wc_fragment_refresh' );
			}, 100);
			setTimeout(function(){
				jQuery( document.body ).trigger( 'wc_fragment_refresh' );
			}, 200);
		});

		// force remove elementor-hidden my account area to prevent issues:
		jQuery('.woocommerce-account .elementor-hidden .woocommerce').remove();


		// payment method discounts fees on order pay page
		 
		$('form#order_review').on('click', 'input[name="payment_method"]', function(){

			const order_id = b2bking_display_settings.orderid;

			$('#place_order').prop('disabled', true);
			
			var paymentMethod = $('input[name="payment_method"]:checked').val();

			// Get Payment Title and strip out all html tags.
			var paymentMethodTitle = $(`label[for="payment_method_${paymentMethod}"]`).text().replace(/[\t\n]+/g,'').trim();

			// On visiting Pay for order page, take the payment method and payment title which are present in the order.
			if ( '' !== b2bking_display_settings.paymentmethod ) {
				paymentMethod = b2bking_display_settings.paymentmethod;
				paymentMethodTitle = $(`label[for="payment_method_${paymentMethod}"]`).text().replace(/[\t\n]+/g,'').trim();
			}

			const data = {
				action: 'b2bking_update_fees',
				security: b2bking_display_settings.security,
				payment_method: paymentMethod,
				payment_method_title: paymentMethodTitle,
				order_id: order_id,
			};

			// We need to set the payment method blank because when second time when it comes here on changing the payment method it should take that changed value and not the payment method present in the order.
			b2bking_display_settings.paymentmethod = '';

			$.post(b2bking_display_settings.ajaxurl, data, function(response){
				$('#place_order').prop('disabled', false);
				if (response && response.fragments) {
					$('#order_review').html(response.fragments);
					$(`input[name="payment_method"][value=${paymentMethod}]`).prop('checked', true);
					$(`.payment_method_${paymentMethod}`).css('display', 'block');
					$(`div.payment_box:not(".payment_method_${paymentMethod}")`).filter(':visible').slideUp(0);
					$(document.body).trigger('updated_checkout');
				}
			});
		});


	});

})(jQuery);
