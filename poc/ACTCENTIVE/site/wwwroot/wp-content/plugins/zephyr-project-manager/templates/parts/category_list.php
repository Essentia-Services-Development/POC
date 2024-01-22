<?php

	/**
	* Template for displaying the category list
	*
	* @package ZephyrProjectManager
	*
	*/
	if ( !defined( 'ABSPATH' ) ) {
		die;
	}

	use ZephyrProjectManager\Core\Categories;

	$category_count = Categories::get_category_total();
	$categories = Categories::get_categories();
	$categories = Categories::sort($categories);
?>

<?php foreach ($categories as $category) : ?>
	<div class="zpm_category_row" zpm-ripple="" data-ripple="rgba(0, 0, 0, 0.09)" data-category-id="<?php echo esc_attr($category->id) ?>">
		<span class="zpm_category_color" data-zpm-color="<?php echo esc_attr($category->color); ?>" style="background:<?php echo esc_attr($category->color); ?>"></span>
		<span class="zpm_category_name"><?php echo esc_html($category->name); ?></span>
		<?php echo ($category->description !== '') ? ' - <span class="zpm_category_description">' . zpm_esc_html($category->description) . '</span>' : ''; ?>
		<span class="zpm_category_actions">
			<span class="zpm_delete_category" data-category-id="<?php echo esc_attr($category->id) ?>">
				<i class="zpm_delete_category_icon lnr lnr-cross"></i>
			</span>
		</span>
	</div>
<?php endforeach; ?>