<?php
// 1) папку Install ложим в корень плагина rehub-framework
// 2) подключаем исталлятор в функции includes() класса REHub_Framework в плагине

	/* Include required core files */
	public function includes() {

//..................//
		if( is_admin() ) {
			//......................................//
			//theme wizard
			require_once RH_FRAMEWORK_ABSPATH .'/install/index.php';
		}
//..................//
	}

// 3) сылка для инсталлятора на Главной стр-це темы. \wp-content\themes\rehub-theme\admin\screens\welcome.php
?>
    <div class="feature-section">
        <strong>Some important tutorials to make your site better:</strong>
        <ul>
			<!-- //................................// -->
			<li><a href="<?php echo esc_url(wp_nonce_url(admin_url('plugins.php?page=rehub_wizard&rehub_install=1'), '_wpnonce'));?>"><?php echo esc_html__("Run Installation Wizard","rehub-theme") ?></a></li>

        </ul>
    </div>