<?php
/**
 * The Template for displaying store tabs.
 *
 * @package WCfM Markeplace Views Store
 *
 * For edit coping this to yourtheme/wcfm/store 
 *
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

global $WCFM, $WCFMmp;

$store_tabs = $store_user->get_store_tabs();

?>

<?php do_action( 'wcfmmp_store_before_tabs', $store_user->get_id() ); ?>

<div class="tab_links_area mt20">
	<ul class="smart-scroll-desktop mb0 clearfix contents-woo-area rh-big-tabs-ul pt0 pb0 pr0 pl0">
	  <?php foreach( $store_tabs as $store_tab_key => $store_tab_label ) { ?>
	  	<li class="<?php if( $store_tab_key == $store_tab ) echo 'active'; ?> rh-hov-bor-line rh-big-tabs-li below-border pt0 pb0 pr0 pl0"><a href="<?php echo ''.$store_user->get_store_tabs_url( $store_tab_key ); ?>" class="pt15 pb15 pl15 pr15 fontnormal"><?php echo ''.$store_tab_label; ?></a></li>
	  <?php } ?>
	</ul>
</div>


<?php do_action( 'wcfmmp_store_after_tabs', $store_user->get_id() ); ?>