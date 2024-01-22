<?php
defined( '\ABSPATH' ) || exit;

/*
  Name: List with no prices
 */

?>

<div class="egg-container cegg-list-no-prices">
	<?php if ( $title ): ?>
        <h3><?php echo \esc_html( $title ); ?></h3>
	<?php endif; ?>

    <div class="egg-listcontainer">
		<?php $i = 0; ?>
		<?php foreach ( $items as $item ): ?>
			<?php $this->renderBlock( 'list_row_no_price', array( 'i' => $i, 'item' => $item ) ); ?>
			<?php $i ++; ?>
		<?php endforeach; ?>

    </div>
</div>


