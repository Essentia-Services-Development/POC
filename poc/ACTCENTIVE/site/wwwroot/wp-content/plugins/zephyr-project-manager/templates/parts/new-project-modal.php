<?php 
	/**
	* Template for displaying the New Project modal
	*/

	if ( !defined( 'ABSPATH' ) ) {
		die;
	}

	use ZephyrProjectManager\Core\Projects;
	
	echo Projects::project_modal();
?>
