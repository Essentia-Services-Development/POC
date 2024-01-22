<?php
defined( '\ABSPATH' ) || exit;

use ContentEgg\application\helpers\TemplateHelper;

?>
<?php if ( ! empty( $item['extra']['customerReviews'] ) && ! empty( $item['extra']['customerReviews']['reviews'] ) ): ?>
    <h4>
		<?php esc_html_e( 'Customer reviews', 'content-egg-tpl' ); ?>
    </h4>
	<?php foreach ( $item['extra']['customerReviews']['reviews'] as $review ): ?>
        <div class="cegg-review-block">
            <em><?php echo esc_html( $review['Summary'] ); ?>,
                <small><?php echo esc_html(TemplateHelper::formatDate( $review['Date'] )); ?></small></em>
            <span class="rating_small">
                <?php echo wp_kses_post(str_repeat( "<span>★</span>", (int) $review['Rating'] )); ?><?php echo wp_kses_post(str_repeat( "<span>☆</span>", 5 - (int) $review['Rating'] )); ?>
            </span>
        </div>
        <blockquote><?php echo esc_html( $review['Content'] ); ?></blockquote>
	<?php endforeach; ?>
<?php endif; ?>

<?php if ( ! empty( $item['extra']['editorialReviews'] ) ): ?>
	<?php foreach ( $item['extra']['editorialReviews'] as $review ): ?>
        <h4><?php echo esc_html( $review['Source'] ); ?></h4>
        <p><?php echo wp_kses_data($review['Content']); ?></p>
	<?php endforeach; ?>
<?php endif; ?>

<?php if ( ! empty( $item['extra']['comments'] ) ): ?>
    <h4><?php esc_html_e( 'User reviews', 'content-egg-tpl' ); ?></h4>
	<?php foreach ( $item['extra']['comments'] as $key => $comment ): ?>
        <div class="cegg-review-block">
            <blockquote>
				<?php if ( ! empty( $comment['rating'] ) ): ?>
                    <span class="rating_small">
                        <?php echo wp_kses_post(str_repeat( "<span>★</span>", (int) $comment['rating'] )); ?><?php echo wp_kses_post(str_repeat( "<span>☆</span>", 5 - (int) $comment['rating'] )); ?>
                    </span>
				<?php endif; ?>
				<?php echo wp_kses_data($comment['comment']); ?>
            </blockquote>
        </div>
	<?php endforeach; ?>
    <p class="text-right">
        <a<?php TemplateHelper::printRel(); ?> target="_blank" class="btn btn-info"
                                               href="<?php echo esc_url( $item['url'] ) ?>"><?php esc_html_e( 'View all reviews', 'content-egg-tpl' ); ?></a>
    </p>
<?php endif; ?>

<?php if ( ! empty( $item['extra']['Reviews'] ) ): ?>
    <h4>
		<?php esc_html_e( 'Customer reviews', 'content-egg-tpl' ); ?>
    </h4>
	<?php foreach ( $item['extra']['Reviews'] as $review ): ?>
        <div class="cegg-review-block">
            <em><?php if ( $review['Title'] ): ?><?php echo esc_html( $review['Title'] ); ?>,<?php endif; ?>
                <small><?php echo esc_html(TemplateHelper::formatDate( $review['Date'] )); ?></small></em>
            <span class="rating_small">
                <?php echo wp_kses_post(str_repeat( "<span>★</span>", (int) $review['Rate'] )); ?><?php echo wp_kses_post(str_repeat( "<span>☆</span>", 5 - (int) $review['Rate'] )); ?>
            </span>
        </div>
        <blockquote><?php echo esc_html( $review['Comment'] ); ?></blockquote>
	<?php endforeach; ?>
<?php endif; ?> 
