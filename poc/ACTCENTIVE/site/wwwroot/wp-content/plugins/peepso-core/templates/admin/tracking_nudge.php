<div class="psa-tracking__box update-nag tracking-nudge">
  <div class="psa-tracking__box-message">
    <h2><strong>PeepSo</strong> needs your help!</h2>
    <p><?php echo Peepso3_Stats::$desc;?></p>
  </div>
  <div class="psa-tracking__box-actions">
    <a id="ps-gs-notice-dismiss" href="#" class="psa-tracking__box-action psa-tracking__box-action--close ps-js-tracking-nudge-no"><i class="fa fa-times"></i><span><?php echo __('No, thanks!', 'peepso-core'); ?></span></a>
    <a id="ps-gs-notice-enable" href="#" class="psa-tracking__box-action ps-js-tracking-nudge-enable"><i class="fa fa-check"></i> <?php echo __('Enable', 'peepso-core'); ?></a>
  </div>
</div>
<script>
setTimeout(function() {
    jQuery(function( $ ) {
        $( '.ps-js-tracking-nudge-no' ).on( 'click', function( e ) {
            e.preventDefault();
            e.stopPropagation();
            $( this ).closest( '.tracking-nudge' ).remove();
            $.get( window.location.href, { peepso_hide_tracking_nudge: 1 } );
        })
    });

    jQuery(function( $ ) {
        $( '.ps-js-tracking-nudge-enable' ).on( 'click', function( e ) {
            e.preventDefault();
            e.stopPropagation();
            $( this ).closest( '.tracking-nudge' ).remove();
            $.get( window.location.href, { peepso_enable_tracking_nudge: 1 } );
        })
    });
}, 100 );
</script>
