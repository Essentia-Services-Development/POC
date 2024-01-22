<?php

namespace FSPoster\App\Pages\Accounts\Views;

use FSPoster\App\Providers\Pages;
use FSPoster\App\Providers\Helper;

defined( 'ABSPATH' ) or exit;
?>

<div>
	<?php foreach ( $fsp_params[ 'accounts_list' ] as $account_info )
	{
		$username = get_userdata( $account_info[ 'user_id' ] ) !== FALSE ? get_userdata( $account_info[ 'user_id' ] )->nickname : fsp__( 'deleted user' ); ?>
		<div class="fsp-account">
			<div class="fsp-card fsp-account-item" data-id="<?php echo $account_info[ 'id' ]; ?>" data-type="account" data-active="<?php echo isset( $account_info[ 'is_active' ] ) && ! empty( $account_info[ 'is_active' ] ) ? 1 : 0; ?>" data-hidden="<?php echo $account_info[ 'is_hidden' ]; ?>" data-failed="<?php echo $account_info[ 'status' ] === 'error' ? 1 : 0; ?>">
				<div class="fsp-account-inline">
					<div class="fsp-account-image">
						<img src="<?php echo Pages::asset( 'Base', 'img/wordpress.png' ); ?>">
					</div>
					<div class="fsp-account-name">
						<?php echo esc_html( $account_info[ 'name' ] ); ?>
						<span><?php echo esc_html( $account_info[ 'username' ] ); ?></span>
					</div>
				</div>
				<div class="fsp-account-inline fsp-is-buttons-container">
					<?php if ( $account_info[ 'status' ] === 'error' ) { ?>
						<div class="fsp-account-failed fsp-tooltip" data-title="<?php echo esc_html( $account_info[ 'error_msg' ] ); ?>">
							<i class="fas fa-exclamation-triangle"></i>
						</div>
					<?php } ?>
					<div class="fsp-account-is-public fsp-tooltip <?php echo ! $account_info[ 'is_public' ] ? 'fsp-hide' : ''; ?>" data-title="<?php echo fsp__( 'It\'s public for all the users by %s.', [ $username ] ); ?>">
						<i class="far fa-star"></i>
					</div>
					<?php if ( ! empty( $account_info[ 'proxy' ] ) ) { ?>
						<div class="fsp-account-proxy fsp-tooltip" data-title="<?php echo fsp__( 'Proxy' ); ?>: <?php echo esc_html( $account_info[ 'proxy' ] ); ?>">
							<i class="fas fa-globe"></i>
						</div>
					<?php } ?>
					<div class="fsp-account-checkbox">
						<i class="<?php echo empty( $account_info[ 'is_active' ] ) ? 'far fa-check-square' : ( 'fas fa-check-square fsp-is-checked' . ( $account_info[ 'is_active' ] === 'no' ? '' : '-conditionally' ) ); ?>"></i>
					</div>
					<a class="fsp-account-link fsp-tooltip" href="<?php echo Helper::profileLink( $account_info ); ?>" data-title="<?php echo fsp__( 'Profile link' ); ?>" target="_blank">
						<i class="fas fa-external-link-alt"></i>
					</a>
					<div class="fsp-account-more">
						<i class="fas fa-ellipsis-h"></i>
					</div>
				</div>
				<div class="fsp-account-inline fsp-is-select-container">
					<input type="checkbox" class="fsp-form-checkbox fsp-account-selectbox" data-id="<?php echo $account_info[ 'id' ]; ?>" data-type="account">
				</div>
			</div>
		</div>
	<?php } ?>

	<div class="fsp-card fsp-emptiness <?php echo( empty( $fsp_params[ 'accounts_list' ] ) ? '' : 'fsp-hide' ); ?>">
		<div class="fsp-emptiness-image">
			<img src="<?php echo Pages::asset( 'Base', 'img/empty.svg' ); ?>">
		</div>
		<div class="fsp-emptiness-text">
			<?php echo fsp__( 'No %s found!', [ $fsp_params[ 'err_text' ] ] ); ?>
		</div>
		<div class="fsp-emptiness-button">
			<button class="fsp-button fsp-accounts-add-button">
				<i class="fas fa-plus"></i>
				<span><?php echo $fsp_params[ 'button_text' ]; ?></span>
			</button>
		</div>
	</div>
</div>

<?php $count = count( $fsp_params[ 'accounts_list' ] ); ?>
<script>
	FSPObject.modalURL = 'add_wordpress_site';
	FSPObject.accountsCount = <?php echo $count; ?>;
</script>