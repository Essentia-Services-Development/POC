<?php

namespace FSPoster\App\Pages\Base\Views;

use FSPoster\App\Providers\Pages;
use FSPoster\App\Providers\Helper;

defined( 'ABSPATH' ) or exit;
?>

<div class="fsp-box-container">
	<div class="fsp-card fsp-box">
		<div class="fsp-box-info">
			<i class="fas fa-info-circle"></i><?php echo fsp__( 'A new version %s is available. Please update the plugin.', [ esc_html( Helper::getVersion() ) ] ); ?>
		</div>
		<div class="fsp-box-logo">
			<img src="<?php echo Pages::asset( 'Base', 'img/logo.png' ); ?>">
		</div>
		<div class="fsp-form-group">
			<input type="text" id="fspPurchaseKey" autocomplete="off" class="fsp-form-input" placeholder="<?php echo fsp__( 'Enter the purchase key' ); ?>">
		</div>
		<div class="fsp-form-group">
			<button type="button" class="fsp-button" id="fspUpdateBtn"><?php echo fsp__( 'UPDATE AND ACTIVATE' ); ?></button>
		</div>
	</div>
</div>