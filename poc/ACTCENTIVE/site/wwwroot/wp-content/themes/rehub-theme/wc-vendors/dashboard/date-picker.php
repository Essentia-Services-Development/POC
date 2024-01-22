<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly ?>
<form method="post" class="rh_wcv_date_picker">
		<div class="wpsm-four-fifth">
			<div class="wpsm-one-half">
				<label for="from"><?php esc_html_e( 'From:', 'rehub-theme' ); ?></label>
				<input class="date-pick" type="date" name="start_date" id="from"
				       value="<?php echo esc_attr( date( 'Y-m-d', $start_date ) ); ?>"/>
			</div>
			<div class="wpsm-one-half wpsm-column-last">
				<label for="to"><?php esc_html_e( 'To:', 'rehub-theme' ); ?></label>
				<input type="date" class="date-pick" name="end_date" id="to"
				       value="<?php echo esc_attr( date( 'Y-m-d', $end_date ) ); ?>"/>
			</div>

		</div>
		<div class="wpsm-one-fifth wpsm-column-last">
			<input type="submit" class="btn btn-inverse btn-small"
			       value="<?php esc_html_e( 'Show', 'rehub-theme' ); ?>"/>
		</div>
</form>