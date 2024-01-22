/* 
 * Сustomizer Script
 * @package rehub
 */
 
 jQuery(document).ready(function($) {
	'use strict';

	ShowHideFunc(
	   $('#_customize-input-rehub_sticky_nav-radio-0'),
	   $('#_customize-input-rehub_sticky_nav-radio-1'),
	   $('#customize-control-rehub_logo_sticky_url')
	);

	ShowHideFunc(
		$('#_customize-input-woo_compact_loop_btn-radio-0'),
		$('#_customize-input-woo_compact_loop_btn-radio-1'),
		$('#customize-control-woo_wholesale')
	 );
   
	var menuconditionals = {
		"header_seven" : ["header_seven_more_element", "header_seven_wishlist_label", "header_seven_wishlist", "header_seven_login_label", "header_seven_login", "header_seven_cart_as_btn", "header_seven_cart", "header_seven_compare_btn_label", "header_seven_compare_btn"],
		"header_six" : ["header_six_menu", "header_six_src", "header_six_btn_login", "header_six_btn_url", "header_six_btn_txt", "header_six_btn_color", "header_six_btn", "header_six_login"],
		"header_five" : ["header_seven_more_element", "header_seven_wishlist", "header_six_src", "header_six_btn_login", "header_six_btn_url", "header_six_btn_txt", "header_six_btn_color", "header_six_btn", "header_six_login", "header_seven_cart_as_btn", "header_seven_cart", "header_five_menucenter", "header_src_icon", "header_six_menu", "header_seven_compare_btn"],
	};

	var commonitems = [];
	$.each(menuconditionals, function(index, value){
		commonitems = commonitems.concat(value);
	});
	var commonitemsunique = new Set(commonitems);
	commonitems = Array.from(commonitemsunique); //Create array without duplicates

	var selectedheader = $('#_customize-input-rehub_header_style').val(); //Get current value of header style
	ShowHideHeaderElements(menuconditionals[selectedheader], commonitems); //Show items on loading
	$('#_customize-input-rehub_header_style').on('change', function(){
		var selectedValue = $(this).val();
		ShowHideHeaderElements(menuconditionals[selectedValue], commonitems); //Show items on change
	});	

	function ShowHideHeaderElements(showarray, fullarray){
		$.each(fullarray, function(index, value){
			if($.inArray(value, showarray) !== -1){
				$('#customize-control-'+value).fadeIn();
			}else{
				$('#customize-control-'+value).fadeOut();
			}
		});
	}
   
	function ShowHideFunc(button0,button1,container){
		if(button1.is(":checked")){
			container.show();
		}else{
			container.hide();
		}
		button1.click(function(){
			container.fadeIn();
		});
		button0.click(function(){
			container.fadeOut();
		});
	}

	/**
	 * Googe Font Select Custom Control
	 */

	$('.google-fonts-list').on('change', function() {
		var elementWeights = $(this).parent().parent().find('.google-fonts-weights-style');
		var elementStyles = $(this).parent().parent().find('.google-fonts-styles-style');
		var elementSubsets = $(this).parent().parent().find('.google-fonts-subsets-style');
		var selectedFont = $(this).val();
		var customizerControlName = $(this).attr('control-name');
		var elementWeightsCount = 0;
		var elementStylesCount = 0;
		var elementSubsetsCount = 0;

		elementWeights.empty();
		elementStyles.empty();
		elementSubsets.empty();
		
		elementStyles.prop('disabled', false);
		elementSubsets.prop('disabled', false);

		var bodyfontcontrol = _wpCustomizeSettings.controls[customizerControlName];

		var indexes = $.map(bodyfontcontrol.fontslist, function(obj, index) {
			if(obj.family === selectedFont) {
				return index;
			}
		});
		
		var index = indexes[0];
		
		$.each(bodyfontcontrol.fontslist[index].styles, function(val, text) {
			elementStyles.append(
				$('<option></option>').val(text).html(text)
			);
			elementStylesCount++;
		});

		$.each(bodyfontcontrol.fontslist[index].weights, function(val, text) {
			elementWeights.append(
				$('<option></option>').val(text).html(text)
			);
			elementWeightsCount++;
		});

		$.each(bodyfontcontrol.fontslist[index].subsets, function(val, text) {
			elementSubsets.append(
				$('<option></option>').val(text).html(text)
			);
			elementSubsetsCount++;
		});

		if(elementStylesCount == 0) {
			elementStyles.append(
				$('<option></option>').val('').html('Not Available')
			);
			elementStyles.prop('disabled', 'disabled');
		}

		if(elementWeightsCount == 0) {
			elementWeights.append(
				$('<option></option>').val('').html('Not Available')
			);
			elementWeights.prop('disabled', 'disabled');
		}
		
		if(elementSubsetsCount == 0) {
			elementSubsets.append(
				$('<option></option>').val('').html('Not Available')
			);
			elementSubsets.prop('disabled', 'disabled');
		}

		rehubGetAllSelects($(this).parent().parent());
	});

	$('.google_fonts_select_control select').on('change', function() {
		rehubGetAllSelects($(this).parent().parent());
	});

	function rehubGetAllSelects($element) {
		var selectedFont = {
			font: $element.find('.google-fonts-list').val(),
			weights: $element.find('.google-fonts-weights-style').val(),
			styles: $element.find('.google-fonts-styles-style').val(),
			subsets: $element.find('.google-fonts-subsets-style').val(),
		};
		
		var valueData = JSON.stringify(selectedFont);
		
		$element.find('.customize-control-google-font-selection').val(valueData).trigger('change');
	}
   
}); //END Document.ready