<?php

if(!class_exists('PeepSo3_Developer_Tools_Page')) {
    require_once(dirname(__FILE__) . '/devtools_page.php');
    //new PeepSoError('Autoload issue: PeepSo3_Developer_Tools_Page not found ' . __FILE__);
}


class PeepSo3_Developer_Tools_Page_Home extends PeepSo3_Developer_Tools_Page
{
	public function __construct()
	{
		$this->title='Home';
	}

	public function page()
	{

		$this->page_start();
		echo $this->page_data();
		$this->page_end('');
	}

	public function page_data()
	{
		$PeepSoDeveloperTools = PeepSo3_Developer_Tools::get_instance();
		ob_start();
		?>
		<div id="welcome-panel" class="welcome-panel">
			<div class="welcome-panel-content">

				<?php
				foreach($PeepSoDeveloperTools->pages_config as $page) {
					if('home' == $page) { continue; }
					?>
					<p>
					<h3>
						<a href="<?php menu_page_url( 'peepso_developer_tools_'.$page);?>"><?php echo $PeepSoDeveloperTools->pages[$page]->title;?></a>
					</h3>
					<?php echo $PeepSoDeveloperTools->pages[$page]->description;?>
					</p>
					<?php
				}
				?>
			</div>
		</div>
		<?php
		return ob_get_clean();
	}

	public static function peepso_developer_tools_buttons($buttons)
	{
		return array();
	}
}

// EOFpage_phpinfo.php