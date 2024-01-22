<?php defined( '\ABSPATH' ) || exit; ?>
<?php if ( ! empty( $item['extra']['objectives'] ) ): ?>
    <h3><?php esc_html_e( 'What Will I Learn?', 'content-egg-tpl' ); ?></h3>
    <ul>
		<?php foreach ( $item['extra']['objectives'] as $objective ): ?>
            <li><?php echo esc_html( $objective ); ?></li>
		<?php endforeach; ?>
    </ul>
<?php endif; ?>
<?php if ( ! empty( $item['extra']['objectives'] ) ): ?>
    <h3><?php esc_html_e( 'Requirements', 'content-egg-tpl' ); ?></h3>
    <ul>
		<?php foreach ( $item['extra']['prerequisites'] as $prerequisite ): ?>
            <li><?php echo esc_html( $prerequisite ); ?></li>
		<?php endforeach; ?>
    </ul>
<?php endif; ?>
<?php if ( ! empty( $item['extra']['target_audiences'] ) ): ?>
    <h3><?php esc_html_e( 'Target audience', 'content-egg-tpl' ); ?></h3>
    <ul>
		<?php foreach ( $item['extra']['target_audiences'] as $target_audience ): ?>
            <li><?php echo esc_html( $target_audience ); ?></li>
		<?php endforeach; ?>
    </ul>
<?php endif; ?>








