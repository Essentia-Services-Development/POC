<div class="notice notice-warning peepso ps-notice">

    <h3>Welcome to PeepSo <?php echo PeepSo::PLUGIN_VERSION;?></h3>

    <p><i class="gci gci-arrow-alt-circle-down"></i> Visit the <a href="<?php echo admin_url('admin.php?page=peepso-installer');?>"><b>PeepSo installer</b></a> to <b>install add-ons</b> and check for <b>new releases</b>.</p>

    <?php if(!PeepSo3_Helper_Addons::get_license()) { ?>
        <p><i class="gci gci-gift"></i> Get the most out of PeepSo: start with the <a href="<?php echo admin_url('admin.php?page=peepso-installer&action=peepso-free');?>"><b>PeepSo Free Bundle</b></a> or check out our <a href="https://www.PeepSo.com/pricing/ref/429" target="_blank"><b>full pricing</b></a>.</p>
        <p></p>
    <?php } ?>

    <?php PeepSoTemplate::exec_template('admin','admin_notice_help');?>

    <p>
		<a id="ps-gs-notice-dismiss" href="#" class="ps-notice__dismiss ps-gs-notice-dismiss ps-js-gs-notice-dimiss">
      <i class="gcir gci-times-circle"></i>
		</a>
	</p>
</div>
<script>
setTimeout(function() {
	jQuery(function( $ ) {
		$( '.ps-js-gs-notice-dimiss' ).on( 'click', function( e ) {
			e.preventDefault();
			e.stopPropagation();
			$( this ).closest( '.notice' ).remove();
			$.get( window.location.href, { peepso_hide_installer: 1 } );
		})
	});
}, 100 );
</script>