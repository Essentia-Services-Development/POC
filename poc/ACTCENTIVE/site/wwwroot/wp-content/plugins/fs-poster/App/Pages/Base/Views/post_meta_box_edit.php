<?php

namespace FSPoster\App\Pages\Base\Views;

use FSPoster\App\Providers\DB;
use FSPoster\App\Providers\Date;
use FSPoster\App\Providers\Pages;
use FSPoster\App\Providers\Helper;

defined( 'ABSPATH' ) or exit;
?>

<link rel="stylesheet" href="<?php echo Pages::asset( 'Base', 'css/fsp-metabox.css' ); ?>">
<link rel="stylesheet" href="<?php echo Pages::asset( 'Share', 'css/fsp-sharing-popup.css' ); ?>">

<div class="fsp-metabox fsp-is-mini">
	<?php if ( ! $fsp_params[ 'is_attachment' ] ) { ?>
		<div class="fsp-form-group">
			<input id="fspImageID" type="hidden" value="<?php echo $fsp_params[ 'imageID' ] ?>">
			<div id="fspMediaBtn" class="fsp-form-image <?php echo $fsp_params[ 'imageID' ] > 0 ? 'fsp-hide' : ''; ?>">
				<i class="fas fa-camera"></i>
				<div><?php echo fsp__( 'You can share a custom image instead of the default featured image.  Click here to add an image, otherwise the featured image will be shared. The Featured image option must be selected in the posting type settings.' ); ?></div>
			</div>
			<div id="imageShow" class="fsp-form-image-preview <?php echo $fsp_params[ 'imageID' ] > 0 ? '' : 'fsp-hide'; ?>" data-id="<?php echo $fsp_params[ 'imageID' ]; ?>">
				<img src="<?php echo esc_html( $fsp_params[ 'imageURL' ] ); ?>">
				<i class="fas fa-times" id="closeImg"></i>
			</div>
		</div>
	<?php } ?>
	<div class="fsp-metabox-p">
		<?php echo fsp__( 'Shared on:' ); ?>
	</div>
	<div class="fsp-card-body fsp-metabox-accounts">
		<?php
		$feedsCount = 0;
		foreach ( $fsp_params[ 'feeds' ] as $feedInf )
		{
			$node_infoTable = $feedInf[ 'node_type' ] === 'account' ? 'accounts' : 'account_nodes';
			$node_info      = DB::fetch( $node_infoTable, $feedInf[ 'node_id' ] );

			if ( empty( $node_info ) )
			{
				continue;
			}

			$subName = $node_info[ 'driver' ] . ' > ';

			if( $node_info[ 'driver' ] === 'mastodon' )
			{
				$subName .= json_decode( $node_info[ 'options' ], TRUE )['server'];
			}
			else if( $node_info[ 'driver' ] === 'webhook' )
			{
				$subName .= $node_info[ 'username' ];
			}
			else
			{
				$subName .= $feedInf[ 'node_type' ];
			}

			if ( $feedInf[ 'node_type' ] === 'account' )
			{
				$node_info[ 'node_type' ] = 'account';
			}

			$feedsCount++;
			?>

			<div class="fsp-sharing-account" data-id="<?php echo (int) $feedInf[ 'id' ]; ?>">
				<div class="fsp-sharing-account-image">
					<img src="<?php echo Helper::profilePic( $node_info ); ?>" onerror="FSPoster.no_photo( this );">
				</div>
				<div class="fsp-sharing-account-info">
					<a target="_blank" <?php echo $node_info[ 'driver' ] == 'webhook' ? '' : 'href="' . Helper::profileLink( $node_info ) . '"'; ?> class="fsp-sharing-account-info-text">
						<?php echo esc_html( $node_info[ 'name' ] ); ?>
					</a>
					<div class="fsp-sharing-account-info-subtext">
						<?php echo ucfirst( $subName ) . ( $feedInf[ 'feed_type' ] === 'story' ? ' > Story' : '' ); ?>
					</div>
				</div>
				<div class="fsp-sharing-account-status">
					<?php if ( $feedInf[ 'status' ] === 'ok' )
					{
						if ( $node_info[ 'driver' ] === 'twitter' )
						{
							$username = isset( $node_info[ 'username' ] ) ? $node_info[ 'username' ] : '';
						}
						else if ( $node_info[ 'driver' ] === 'google_b' )
						{
							$username = isset( $node_info[ 'node_id' ] ) ? $node_info[ 'node_id' ] : '';
						}
						else if ( $node_info[ 'driver' ] === 'wordpress' )
						{
							$username = isset( $node_info[ 'options' ] ) ? $node_info[ 'options' ] : '';
						}
						else if ( $node_info[ 'driver' ] === 'mastodon' )
						{
							$username = json_decode( $node_info[ 'options' ], TRUE ) . '/@' . $node_info[ 'username' ];
						}
						else
						{
							$username = ( isset( $node_info[ 'screen_name' ] ) ? $node_info[ 'screen_name' ] : '' );
						}
						?>

						<a href="<?php echo Helper::postLink( $feedInf[ 'driver' ] == 'discord' ? $feedInf[ 'driver_post_id2' ] : ( $feedInf[ 'driver' ] == 'webhook' ? $feedInf[ 'id' ] : $feedInf[ 'driver_post_id' ] ), $feedInf[ 'driver' ] . ( $feedInf[ 'driver' ] === 'instagram' ? $feedInf[ 'feed_type' ] : '' ), $username ); ?>" target="_blank">
							<i class="fas fa-external-link-alt"></i>
						</a>
						<div class="fsp-status fsp-is-success fsp-tooltip" data-title="<?php echo fsp__( 'Posted successfully.' ); ?>">
							<i class="fas fa-check"></i>
						</div>
					<?php }
					else if ( $feedInf[ 'is_sended' ] != '1' )
					{
						$message       = fsp__( 'Going to share in a minute.' );
						$shareTimerSec = Date::epoch( $feedInf[ 'send_time' ] ) - Date::epoch();

						if ( $shareTimerSec > 60 )
						{
							$message = fsp__( 'Going to share after %d minute(s).', [ (int) ( $shareTimerSec / 60 ) ] );
						} ?>

						<div class="fsp-status fsp-is-warning fsp-tooltip" data-title="<?php echo $message; ?>">
							<i class="fas fa-clock"></i>
						</div>
					<?php } else { ?>
						<div class="fsp-status fsp-is-danger fsp-tooltip" data-title="<?php echo fsp__( 'The post is failed. %s', [ esc_html( $feedInf[ 'error_msg' ] ) ] ); ?>">
							<i class="fas fa-times"></i>
						</div>
					<?php } ?>
				</div>
			</div>
		<?php }
		if ( ! $feedsCount )
		{
			echo fsp__( 'The post hasn\'t been shared on any account! <a href=\'https://www.fs-poster.com/documentation/commonly-encountered-issues#issue11\' target=\'_blank\'>Why?</a>', [], FALSE );
		} ?>
	</div>
	<div class="fsp-card-footer fsp-is-right">
		<?php if ( get_post_status( $fsp_params[ 'parameters' ][ 'post' ]->ID ) === 'publish' ) { ?>
			<button type="button" class="fsp-button fsp-is-gray" data-load-modal="share_saved_post" data-parameter-post_id="<?php echo $fsp_params[ 'parameters' ][ 'post' ]->ID ?>"><?php echo empty( $fsp_params[ 'feeds' ] ) ? fsp__( 'SHARE' ) : fsp__( 'SHARE AGAIN' ); ?></button>
			<button type="button" class="fsp-button fsp-is-red" data-load-modal="add_schedule" data-parameter-post_ids="<?php echo $fsp_params[ 'parameters' ][ 'post' ]->ID ?>"><?php echo fsp__( 'SCHEDULE' ); ?></button>
		<?php } else if ( get_post_status( $fsp_params[ 'parameters' ][ 'post' ]->ID ) === 'future' ) { ?>
			<button type="button" class="fsp-button fsp-is-red" data-load-modal="edit_wp_native_schedule" data-parameter-post_id="<?php echo $fsp_params[ 'parameters' ][ 'post' ]->ID ?>"><?php echo fsp__( 'EDIT SCHEDULE' ); ?></button>
		<?php } ?>
	</div>
</div>

<script>
	( function ( $ ) {
		let doc = $( document );

		doc.ready( function () {
			let frame = wp.media( {
				title: fsp__( 'Select or upload image' ), button: {
					text: fsp__( 'Use this media' )
				}, multiple: false, library: {
					type: 'image'
				}
			} );

			frame.on( 'select', function () {
				let attachment = frame.state().get( 'selection' ).first().toJSON();

				$( '#fspImageID' ).val( attachment.id );
				$( '#imageShow' ).removeClass( 'fsp-hide' ).data( 'id', attachment.id ).children( 'img' ).attr( 'src', attachment.url );
				$( '#fspMediaBtn' ).addClass( 'fsp-hide' );

				FSPoster.ajax( 'save_featured_image', {
					'postId': '<?php echo (int) $fsp_params[ 'parameters' ][ 'post' ]->ID; ?>',
					'imageId': $( '#fspImageID' ).val()
				} );
			} );

			$( '#fspMediaBtn' ).click( function ( event ) {
				frame.open();
			} );

			$( '#closeImg' ).click( function () {
				$( '#imageShow' ).addClass( 'fsp-hide' ).children( 'img' ).attr( 'src', '' ).data( 'id', 0 );
				$( '#fspMediaBtn, #fspShareURL' ).removeClass( 'fsp-hide' );

				FSPoster.ajax( 'save_featured_image', {
					'postId': '<?php echo (int) $fsp_params[ 'parameters' ][ 'post' ]->ID; ?>',
					'imageId': 0
				} );
			} );

			<?php if ( isset( $fsp_params[ 'check_not_sended_feeds' ] ) && $fsp_params[ 'check_not_sended_feeds' ][ 'cc' ] > 0 ) { ?>
			FSPoster.loadModal( 'share_feeds', {
				'post_id': '<?php echo (int) $fsp_params[ 'parameters' ][ 'post' ]->ID; ?>',
				'dont_reload': '1'
			}, true );
			<?php } ?>
		} );
	} )( jQuery );
</script>