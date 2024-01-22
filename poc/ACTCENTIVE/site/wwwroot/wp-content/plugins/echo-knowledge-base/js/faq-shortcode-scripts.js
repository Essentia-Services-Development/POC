jQuery(document).ready(function($) {

	/********************************************************************
    	 *                      FAQs shortcode
    	 ********************************************************************/

	$('.epkb-faqs__item__question').on('click', function(){

		var container = $(this).closest('.epkb-faqs__item-container').eq(0);

		if (container.hasClass('epkb-faqs__item-container--active')) {
			container.find('.epkb-faqs__item__answer').stop().slideUp(400);
		} else {
			container.find('.epkb-faqs__item__answer').stop().slideDown(400);
		}
		container.toggleClass('epkb-faqs__item-container--active');
	});
});
