<?php

namespace FSPoster\App\Pages\Share\Views;

use FSPoster\App\Providers\DB;
use FSPoster\App\Providers\Pages;
use FSPoster\App\Providers\Helper;

defined( 'ABSPATH' ) or exit;
?>

<link rel="stylesheet" href="<?php echo Pages::asset( 'Share', 'css/fsp-sharing-popup.css' ); ?>">

<div class="fsp-modal-body">
	<div class="fsp-modal-close fsp-share-popup-modal-close" data-modal-close="true" onclick="<?php echo isset( $fsp_params[ 'parameters' ][ 'dont_reload' ] ) ? '' : 'location.reload();'; ?>">
		<i class="fas fa-times"></i>
	</div>
	<div class="fsp-sharing-image">
		<img src="<?php echo Pages::asset( 'Share', 'img/share-waiting.svg' ); ?>">
	</div>
	<div class="fsp-sharing-text">
		<?php echo fsp__( 'Posting feeds:' ); ?>&nbsp;<span id="finished_count">0</span>
		/
		<span id="all_count">0</span>
	</div>
	<div class="fsp-sharing-bar">
		<div id="share_progress_bar"></div>
	</div>
	<div class="fsp-sharing-accounts">
		<?php
		$feedCount = 0;

		foreach ( $fsp_params[ 'parameters' ][ 'feeds' ] as $feedInf )
		{
			$node_infoTable = $feedInf[ 'node_type' ] === 'account' ? 'accounts' : 'account_nodes';
			$node_info      = DB::fetch( $node_infoTable, $feedInf[ 'node_id' ] );

			if ( ! $node_info )
			{
				continue;
			}

			$subName = ucfirst( $node_info[ 'driver' ] ) . ' > ';

			if( $node_info[ 'driver' ] === 'webhook' ){
				$subName .= $node_info[ 'username' ];
			}
			else if( $node_info[ 'driver' ] === 'mastodon' ){
				$subName .= json_decode( $node_info[ 'options' ], TRUE )[ 'server' ];
			}
			else{
				$subName .= $feedInf[ 'node_type' ];
			}

			if ( $node_info[ 'driver' ] === 'google_b' )
			{
				$username = isset( $node_info[ 'node_id' ] ) ? $node_info[ 'node_id' ] : '';
			}
			else if ( $node_info[ 'driver' ] === 'wordpress' )
			{
				$username = isset( $node_info[ 'options' ] ) ? $node_info[ 'options' ] : '';
			}
			else
			{
				$username = isset( $node_info[ 'screen_name' ] ) ? $node_info[ 'screen_name' ] : ( isset( $node_info[ 'username' ] ) ? $node_info[ 'username' ] : ' - ' );
			}
			?>

			<div class="fsp-sharing-account" data-id="<?php echo (int) $feedInf[ 'id' ]; ?>" data-interval="<?php echo (int) $feedInf[ 'interval' ]; ?>" data-status="<?php echo (int) $feedInf[ 'is_sended' ]; ?>">
				<div class="fsp-sharing-account-image">
					<img src="<?php echo Helper::profilePic( $node_info ); ?>" onerror="FSPoster.no_photo( this );">
				</div>
				<div class="fsp-sharing-account-info">
					<a target="_blank" <?php echo $node_info[ 'driver' ] == 'webhook' ? '' : 'href="' . Helper::profileLink( $node_info ) . '"'; ?> class="fsp-sharing-account-info-text">
						<?php echo esc_html( $node_info[ 'name' ] ); ?>
						<i class="fas fa-external-link"></i>
					</a>
					<div class="fsp-sharing-account-info-subtext">
						<?php echo $subName . ( $feedInf[ 'feed_type' ] != '' ? ' > ' . ucfirst( esc_html( $feedInf[ 'feed_type' ] ) ) : '' ); ?>
					</div>
				</div>
				<div class="fsp-sharing-account-link">
					<?php if ( ! empty( $feedInf[ 'driver_post_id' ] ) ) { ?>
						<a href="<?php echo Helper::postLink( $node_info[ 'driver_post_id' ], $node_info[ 'driver' ] . ( $node_info[ 'driver' ] === 'instagram' ? $feedInf[ 'feed_type' ] : '' ), $username ); ?>" target="_blank">
							<i class="fas fa-external-link"></i>&nbsp;<?php echo fsp__( 'Post link' ); ?>
						</a>
					<?php } ?>
				</div>
				<div class="fsp-sharing-account-status">
					<?php
					if ( $feedInf[ 'is_sended' ] == '1' && $feedInf[ 'status' ] != 'ok' )
					{
						?>
						<div class="fsp-status fsp-is-danger fsp-tooltip" data-title="<?php echo esc_html( $feedInf[ 'error_msg' ] ); ?>">
							<i class="fas fa-times"></i>
						</div>
						<?php
					}
					else if ( $feedInf[ 'is_sended' ] == '1' && $feedInf[ 'status' ] === 'ok' )
					{
						?>
						<div class="fsp-status fsp-is-success fsp-tooltip" data-title="<?php echo fsp__( 'SUCCESS' ); ?>">
							<i class="fas fa-check"></i>
						</div>
						<?php
					}
					?>
				</div>
			</div>
			<?php $feedCount++;
		} ?>
	</div>
</div>

<script>
	jQuery( document ).ready( function () {
		FSPoster.load_script( '<?php echo Pages::asset( 'Share', 'js/fsp-sharing-popup.js' ); ?>' );
	} );

	FSPObject.feedsCount = <?php echo $feedCount; ?>;
</script>
