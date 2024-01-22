<?php defined( '\ABSPATH' ) || exit; ?>
<?php if ( ! empty( $item['extra']['keySpecs'] ) ): ?>
    <div class="cegg-features-box">
        <h4 class="cegg-no-top-margin"><?php esc_html_e( 'Highlights', 'content-egg-tpl' ); ?></h4>
        <ul class="cegg-feature-list">
			<?php foreach ( $item['extra']['keySpecs'] as $spec ): ?>
                <li><?php echo \esc_html( $spec ); ?></li>
			<?php endforeach; ?>
        </ul>
    </div>

<?php endif; ?>
