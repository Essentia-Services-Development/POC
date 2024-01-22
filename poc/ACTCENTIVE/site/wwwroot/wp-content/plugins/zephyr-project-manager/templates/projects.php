<?php

/**
 * Project Page
 * Users can create, view, edit and manage projects from this page
 */

if (!defined('ABSPATH')) {
	die;
}

use ZephyrProjectManager\Core\Tasks;
use ZephyrProjectManager\Core\Projects;
use ZephyrProjectManager\Core\Utillities;
use ZephyrProjectManager\Core\Categories;
use ZephyrProjectManager\Base\BaseController;
use ZephyrProjectManager\ZephyrProjectManager;

$settings = Utillities::general_settings();
$manager = ZephyrProjectManager();
$base_url = admin_url('/admin.php?page=zephyr_project_manager_projects');
$base_url = apply_filters('zpm_project_to_categories_url', $base_url);
$categories = $manager::get_categories();
$categories = Categories::sort($categories);
$general_settings = Utillities::general_settings();

$categoryAll = (object) [
	'id' => '-1',
	'name' => __('All', 'zephyr-project-manager'),
	'description' => __('All projects', 'zephyr-project-manager'),
	'color' => $settings['primary_color']
];
$categoryMyProjects = (object) [
	'id' => 'my_projects',
	'name' => __('My Projects', 'zephyr-project-manager'),
	'description' => __('Projects assigned to you.', 'zephyr-project-manager'),
	'color' => $settings['primary_color']
];

array_unshift($categories, $categoryMyProjects);
array_unshift($categories, $categoryAll);
$category_id = isset($_GET['category_id']) ? $_GET['category_id'] : '';
$show_projects = apply_filters('zpm_show_projects', true);
$projects_per_page = $settings['projects_per_page'];
$page = isset($_GET['projects_page']) && !empty($_GET['projects_page']) ? (int) $_GET['projects_page'] : 1;
$offset = ($page - 1) * $projects_per_page;

if ($offset < 0) {
	$offset = 0;
}

$total_pages = Projects::get_total_pages();
$last_view = get_user_meta(get_current_user_id(), 'project_view');
$list_view = $last_view == 'list' ? true : false;
$sortingMethods = Projects::getSortingMethods();
$lastSortingMethod = Projects::getLastSortingMethod();
?>

<div class="zpm_settings_wrap">
	<?php $this->get_header(); ?>
	<div id="zpm_container" class="zpm_add_project">
		<div class="zpm_body" style="display: none;">
			<ul class="nav nav-tabs">
				<li class="active"><a href="#tab-1"><?php _e('New Project', 'zephyr-project-manager'); ?></a></li>
			</ul>
		</div>

		<?php if (isset($_GET['action']) && $_GET['action'] == 'edit_project') : ?>
			<?php include(ZPM_PLUGIN_PATH . '/templates/parts/project-single.php'); ?>
		<?php else : ?>
			<?php if ($show_projects) : ?>
				<?php echo apply_filters('zpm_projects_header', ''); ?>
				<?php if (!empty($category_id) || $settings['enable_category_grouping'] == false) : ?>
					<?php
					$projects = Projects::get_paginated_projects($projects_per_page, $offset);
					$projects = apply_filters('zpm_project_grid_projects', $projects);
					$category = Categories::get_category($category_id);
					$projects = Projects::sort($projects, $lastSortingMethod);

					if ($category_id == '-1' || $category_id == 'all') {
						$category = $categoryAll;
					}

					if ($category_id == 'my_projects') {
						$category = $categoryMyProjects;
					}

					$project_count = sizeof($projects);
					?>

					<?php if ($settings['enable_category_grouping']) : ?>
						<h4 id="zpm-header-breadcrumb">
							<a class="zpm-header-back lnr lnr-chevron-left" href="<?php echo esc_url($base_url); ?>"></a>
							<?php echo esc_html($category->name); ?>
							<small id="zpm-header-description"> - <?php echo zpm_esc_html($category->description); ?></small>
						</h4>
					<?php endif; ?>

					<div id="zpm-projects__view-options">
						<span id="zpm-project-view__archived" class="zpm-button__block zpm-fa-icon zpm-toggle-state zpm-color__hover-primary fa fa-archive" title="<?php _e('View Archived Projects', 'zephyr-project-manager'); ?>"></span>
						<span class="zpm-button__block zpm-project-view__option zpm-color__hover-primary fa fa-th-large <?php echo $list_view ? '' : 'zpm-state__active'; ?>" data-view="grid" title="<?php _e('Grid', 'zephyr-project-manager'); ?>"></span>
						<span class="zpm-button__block zpm-project-view__option zpm-color__hover-primary fa fa-th-list <?php echo $list_view ? 'zpm-state__active' : ''; ?>" data-view="list" title="<?php _e('List', 'zephyr-project-manager'); ?>"></span>
						<span id="zpm-project-view__title"><?php _e('All Projects', 'zephyr-project-manager'); ?></span>
					</div>

					<div id="zpm-projects__filters">
						<select data-zpm-project-sorting>
							<?php foreach ($sortingMethods as $key => $sortingMethod) : ?>
								<option value="<?php esc_attr_e($key); ?>" <?php selected($key, $lastSortingMethod) ?>><?php esc_html_e($sortingMethod) ?></option>
							<?php endforeach; ?>
						</select>
					</div>

					<div id="zpm_projects_holder" class="zpm_body">
						<div id="zpm_project_manager_display" class="<?php echo ($project_count == '0') ? 'zpm_hide' : ''; ?>">
							<div id="zpm_project_page_options">
								<?php if (Utillities::can_create_projects()) : ?>
									<button id="zpm_create_new_project" class="zpm_button"><?php _e('New Project', 'zephyr-project-manager'); ?></button>
								<?php endif; ?>
							</div>
						</div>

						<!-- No projects yet -->
						<?php if ($project_count == '0') : ?>
							<div class="zpm_no_results_message">
								<?php if (Utillities::can_create_projects()) : ?>
									<?php printf(__('No projects created yet. To create a project, click on the \'Add\' button at the top right of the screen or click %s here %s', 'zephyr-project-manager'), '<a id="zpm_first_project" class="zpm_button_link">', '</a>'); ?>
								<?php else : ?>
									<?php _e('No projects created yet.', 'zephyr-project-manager'); ?>
								<?php endif; ?>
							</div>
						<?php endif; ?>

						<!-- Project list/grid -->
						<div id="zpm_project_list" class="<?php echo $list_view ? 'zpm-project-view__list' : ''; ?>">
							<?php include(ZPM_PLUGIN_PATH . '/templates/parts/project_grid.php'); ?>
						</div>

						<div id="zpm-project-pagination">
							<?php if ($total_pages > 1) : ?>
								<?php for ($i = 1; $i <= $total_pages; $i++) : ?>
									<button class="zpm-projects-pagination__page zpm_button zpm_button_inverted <?php echo $page == $i ? 'zpm-pagination__current-page' : ''; ?>" data-page="<?php echo esc_attr($i); ?>"><?php echo esc_html($i); ?></button>
								<?php endfor; ?>
							<?php endif; ?>
						</div>
					</div>

				<?php else : ?>
					<?php $back_url = apply_filters('zpm_project_categories_back_url', ''); ?>
					<h4 id="zpm-header-breadcrumb">

						<?php if (!empty($back_url)) : ?>
							<a class="zpm-header-back lnr lnr-chevron-left" href="<?php echo esc_url($back_url); ?>"></a>
						<?php endif; ?>

						<?php _e('Choose a category', 'zephyr-project-manager'); ?>
					</h4>
					<div class="zpm-grid zpm-category-grid">
						<div class="zpm-grid__row">
							<?php $categoryCount = 0; ?>

							<?php foreach ($categories as $category) : ?>
								<?php if (Categories::canView($category->id)) : ?>
									<?php $categoryCount++; ?>
									<?php Categories::card_html($category); ?>
								<?php endif; ?>
							<?php endforeach; ?>

							<?php if ($categoryCount == 0) : ?>
								<div class="zpm_no_results_message"><?php _e('No categories available.', 'zephyr-project-manager'); ?></div>
							<?php endif; ?>
						</div>
					</div>
				<?php endif; ?>

			<?php else : ?>
				<?php echo apply_filters('zpm_projects_grid', ''); ?>
			<?php endif; ?>

		<?php endif; ?>
	</div>
</div>
<?php $this->get_footer(); ?>