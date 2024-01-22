/**
*
* JavaScript file that controls global admin notices (enables permanent dismissal)
*
*/
(function($){

	"use strict";

	$( document ).ready(function() {


		/* Admin notice permanent dismissal */
		$('body').on('click', '.marketkingpro_activate_woocommerce_notice button', function(){
			// Run ajax function that permanently dismisses notice
			var datavar = {
	            action: 'marketkingpro_dismiss_activate_woocommerce_admin_notice',
	            security: marketkingpro_notice.security,
	        };

			$.post(ajaxurl, datavar, function(response){
				// do nothing
			});

		});

		$('body').on('click', '.marketking_announcements_howto_notice button', function(){
			// Run ajax function that permanently dismisses notice
			var datavar = {
	            action: 'marketking_dismiss_announcements_howto_admin_notice',
	            security: marketkingpro_notice.security,
	        };

			$.post(ajaxurl, datavar, function(response){
				// do nothing
			});
		});

		$('body').on('click', '.marketking_refunds_howto_notice button', function(){
			// Run ajax function that permanently dismisses notice
			var datavar = {
	            action: 'marketking_dismiss_refunds_howto_admin_notice',
	            security: marketkingpro_notice.security,
	        };

			$.post(ajaxurl, datavar, function(response){
				// do nothing
			});
		});

		$('body').on('click', '.marketking_roptions_howto_notice button', function(){
			// Run ajax function that permanently dismisses notice
			var datavar = {
	            action: 'marketking_dismiss_roptions_howto_admin_notice',
	            security: marketkingpro_notice.security,
	        };

			$.post(ajaxurl, datavar, function(response){
				// do nothing
			});
		});

		$('body').on('click', '.marketking_rfields_howto_notice button', function(){
			// Run ajax function that permanently dismisses notice
			var datavar = {
	            action: 'marketking_dismiss_rfields_howto_admin_notice',
	            security: marketkingpro_notice.security,
	        };

			$.post(ajaxurl, datavar, function(response){
				// do nothing
			});
		});

		$('body').on('click', '.marketking_badges_howto_notice button', function(){
			// Run ajax function that permanently dismisses notice
			var datavar = {
	            action: 'marketking_dismiss_badges_howto_admin_notice',
	            security: marketkingpro_notice.security,
	        };

			$.post(ajaxurl, datavar, function(response){
				// do nothing
			});
		});

		$('body').on('click', '.marketking_vitems_howto_notice button', function(){
			// Run ajax function that permanently dismisses notice
			var datavar = {
	            action: 'marketking_dismiss_vitems_howto_admin_notice',
	            security: marketkingpro_notice.security,
	        };

			$.post(ajaxurl, datavar, function(response){
				// do nothing
			});
		});

		$('body').on('click', '.marketking_verifications_howto_notice button', function(){
			// Run ajax function that permanently dismisses notice
			var datavar = {
	            action: 'marketking_dismiss_verifications_howto_admin_notice',
	            security: marketkingpro_notice.security,
	        };

			$.post(ajaxurl, datavar, function(response){
				// do nothing
			});
		});

		$('body').on('click', '.marketking_memberships_howto_notice button', function(){
			// Run ajax function that permanently dismisses notice
			var datavar = {
	            action: 'marketking_dismiss_memberships_howto_admin_notice',
	            security: marketkingpro_notice.security,
	        };

			$.post(ajaxurl, datavar, function(response){
				// do nothing
			});
		});

		$('body').on('click', '.marketking_abusereports_howto_notice button', function(){
			// Run ajax function that permanently dismisses notice
			var datavar = {
	            action: 'marketking_dismiss_abusereports_howto_admin_notice',
	            security: marketkingpro_notice.security,
	        };

			$.post(ajaxurl, datavar, function(response){
				// do nothing
			});
		});

		$('body').on('click', '.marketking_commissionrules_howto_notice button', function(){
			// Run ajax function that permanently dismisses notice
			var datavar = {
	            action: 'marketking_dismiss_commissionrules_howto_admin_notice',
	            security: marketkingpro_notice.security,
	        };

			$.post(ajaxurl, datavar, function(response){
				// do nothing
			});
		});

		$('body').on('click', '.marketking_messages_howto_notice button', function(){
			// Run ajax function that permanently dismisses notice
			var datavar = {
	            action: 'marketking_dismiss_messages_howto_admin_notice',
	            security: marketkingpro_notice.security,
	        };

			$.post(ajaxurl, datavar, function(response){
				// do nothing
			});
		});

		$('body').on('click', '.marketking_grules_howto_notice button', function(){
			// Run ajax function that permanently dismisses notice
			var datavar = {
	            action: 'marketking_dismiss_grules_howto_admin_notice',
	            security: marketkingpro_notice.security,
	        };

			$.post(ajaxurl, datavar, function(response){
				// do nothing
			});
		});

		$('body').on('click', '.marketking_groups_howto_notice button', function(){
			// Run ajax function that permanently dismisses notice
			var datavar = {
	            action: 'marketking_dismiss_groups_howto_admin_notice',
	            security: marketkingpro_notice.security,
	        };

			$.post(ajaxurl, datavar, function(response){
				// do nothing
			});
		});

		$('body').on('click', '.marketking_sellerdocs_howto_notice button', function(){
			// Run ajax function that permanently dismisses notice
			var datavar = {
	            action: 'marketking_dismiss_sellerdocs_howto_admin_notice',
	            security: marketkingpro_notice.security,
	        };

			$.post(ajaxurl, datavar, function(response){
				// do nothing
			});
		});

	});

})(jQuery);