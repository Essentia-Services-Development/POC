<?php defined( '\ABSPATH' ) || exit; ?>
<?php if ( ! empty( $item['extra']['visible_instructors'] ) ): ?>
	<?php foreach ( $item['extra']['visible_instructors'] as $instructor ): ?>
        <div class="row well well-sm">
            <div class="col-md-1">
                <img src="<?php echo esc_attr( $instructor['image_50x50'] ); ?>"/>
            </div>
            <div class="col-md-11">
				<?php esc_html_e( 'Created by', 'content-egg-tpl' ); ?>:
                <strong><?php echo esc_html( $instructor['display_name'] ); ?></strong><br>
                <em><?php echo esc_html( $instructor['job_title'] ); ?></em>
            </div>
        </div>

	<?php endforeach; ?>
<?php endif; ?>
    <div class="panel panel-warning">
        <div class="panel-body">
			<?php if ( ! empty( $item['extra']['avg_rating'] ) ): ?>
				<?php esc_html_e( 'Rating:', 'content-egg-tpl' ); ?><?php echo round( $item['extra']['avg_rating'], 2 ); ?> (<?php echo $item['extra']['num_reviews']; ?><?php esc_html_e( 'reviews', 'content-egg-tpl' ); ?>)
                &nbsp;&nbsp;&nbsp;
			<?php endif; ?>
			<?php if ( ! empty( $item['extra']['num_subscribers'] ) ): ?>
				<?php echo $item['extra']['num_subscribers']; ?><?php esc_html_e( 'students enrolled', 'content-egg-tpl' ); ?>
			<?php endif; ?>

        </div>
    </div>
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