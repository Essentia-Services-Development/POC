<?php

abstract class PeepSo3_Developer_Tools_Page
{
	public $title = 'Home';

	public $file_extension = 'txt';
	public $file_mime = 'text/plain';

	public $menu_slug = 'SLUG';

	public $description = 'Use the tabs to access the available features';

	public function page_start($export_key = '')
	{
		$PeepSoDeveloperTools = PeepSo3_Developer_Tools::get_instance();
		wp_enqueue_style('peepso-developer-tools', $PeepSoDeveloperTools::assets_path().'/css/developer_tools_common.css');
		?>
        <h1>PeepSo Dev Tools: <?php echo strip_tags($this->title);?></h1>

		<?php echo $this->description;?>

        <div class="wrap peepso_developer_tools-wrap">
        <div class="peepso_developer_tools-content">
        <h1 class="nav-tab-wrapper wp-clearfix">
			<?php
			foreach($PeepSoDeveloperTools->pages_config as $page) {
				$active = ('peepso_developer_tools_' . $page == $_GET['page']) ? 'nav-tab-active' :'';
				printf('<a href="%s" class="nav-tab %s">%s</a>', menu_page_url( 'peepso_developer_tools_'.$page, FALSE ), $active, $PeepSoDeveloperTools->pages[$page]->title);
			}
			?>
        </h1>

		<?php
		if($export_key) {
			// Export Button
			$PeepSoDeveloperTools_buttons = array();
			ob_start();
			?>
            <form action="<?php echo esc_url($_SERVER['REQUEST_URI']); ?>" method="post">
                <input type="hidden" name="export_content" value="<?php echo $export_key; ?>">
                <input type="hidden" name="system_report_export" value="1">
                <input type="submit" value="<?php echo __('&darr; Export', 'wordpress-system-report'); ?>"
                       class="button button-primary">
            </form>
			<?php
			$buttons['export'] = ob_get_clean();

			// Reload button
			ob_start();
			?>
            <form action="<?php echo esc_url($_SERVER['REQUEST_URI']); ?>" method="post">
                <input type="submit" value="<?php echo __('&#8634; Refresh', 'wordpress-system-report'); ?>"
                       class="button button-secondary">
            </form>
			<?php
			$buttons['reload'] = ob_get_clean();

			$buttons = apply_filters('peepso_developer_tools_buttons', $buttons);


			if (count($buttons)) {
				printf('<div class="peepso_developer_tools-action-buttons">%s</div>', implode(' ', $buttons));
			}
		}
	}

	public function page_end()
	{
		?>
        </div>
        </div>
		<?php
	}

	public static function peepso_developer_tools_buttons($buttons){
		return $buttons;
	}

	abstract public function page();

	abstract public function page_data();
}