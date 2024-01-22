<?php
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
/**
 * @version 1.1
 * @package Merlin WP
 * @pkg.version 1.0.0
 * @link https://merlinwp.com/
 * @authors Richard Tabor, BizDirect
 * @copyright Copyright (c) 2019, Merlin WP of Inventionn LLC
 * @license Licensed GPLv3 for open source use
 */
 
class RH_Install_Theme {

	protected $logo;
			
	protected $install_config;
	
	protected $tgmpa;

	public $mobilelogo = '';

	public $slidinglogo = '';

	function __construct() {
		$this->logo = (REHub_Framework::get_option('rehub_logo')) ? '<img src="'. esc_url(REHub_Framework::get_option('rehub_logo')) .'" class="rehub_install_logo_preview" />' : '';
		$this->mobilelogo = (REHub_Framework::get_option('rehub_logo_inmenu_url')) ? '<img src="'. esc_url(REHub_Framework::get_option('rehub_logo_inmenu_url')) .'" class="rehub_install_logo_preview" />' : '';
		if(REHub_Framework::get_option('logo_mobilesliding')){
			$this->slidinglogo = '<img src="'. esc_url(REHub_Framework::get_option('logo_mobilesliding')) .'" class="rehub_install_logo_preview" />';
		}elseif(REHub_Framework::get_option('rehub_logo')){
			$this->slidinglogo = '<img src="'. esc_url(REHub_Framework::get_option('rehub_logo')) .'" class="rehub_install_logo_preview" />';			
		}else{
			$this->slidinglogo = '';
		}
		$this->install_config = $this->_install_config();
		
		/*  */
		add_action('admin_menu', array($this, 'rehub_add_admin_menu'));
		add_action('admin_init', array($this, 'rehub_installer_init'));

		// Get TGMPA.
		if ( !class_exists( 'TGM_Plugin_Activation' ) ) {
			require_once get_template_directory() . '/class-tgm-plugin-activation.php';
		}

		$this->tgmpa = isset( $GLOBALS['tgmpa'] ) ? $GLOBALS['tgmpa'] : TGM_Plugin_Activation::get_instance();
		
		add_filter( 'tgmpa_load', array( $this, 'load_tgmpa' ), 10, 1 );
		add_action( 'tgmpa_register', array( $this, 'register_plugins' ) );

		add_action( 'wp_ajax_rehub_save_installer', array( $this, '_save_steps_data' ) );
		add_action( 'wp_ajax_merlin_plugins', array( $this, '_ajax_plugins' ) );
	}

	/*  */
	public function load_tgmpa( $status ) {
		return is_admin() || current_user_can( 'install_themes' );
	}

	/*  */
	function rehub_add_admin_menu(){
		$this->rehub_installer_init();
	}

	/*  */
	function rehub_installer_init(){
		// Exit if the user does not have proper permissions
		if( !current_user_can( 'manage_options' ) ) {
			return ;
		}
		$this->rehub_installer_admin_init();
	}

	/*  */
	function rehub_installer_admin_init() {
		if( isset($_GET['rehub_install'], $_GET['_wpnonce']) && wp_verify_nonce($_GET['_wpnonce'], '_wpnonce') && $_GET['rehub_install'] == '1' && is_admin() ){
			$this->rehub_steps_call();			
		}
	}

	function rehub_steps_call() {
		
		if(!wp_verify_nonce($_GET['_wpnonce'], '_wpnonce') || empty( $_GET['page'] ) || $this->install_config['installerpage'] != $_GET['page']) {
			return;
		}

		 if(ob_get_length()) {
			ob_end_clean();
		} 
		$step = isset( $_GET['step'] ) ? sanitize_key($_GET['step']) : $this->install_config['start_steps'];
		$title = $this->install_config['steps'][$step]['title'];

		$suffix = '';

		// Add the color picker css file
		wp_enqueue_style('merlin', esc_url(RH_FRAMEWORK_URL .'/install/assets/css/merlin'. $suffix .'.css') , array( 'wp-admin' ), RH_PLUGIN_VER);
		wp_enqueue_script('merlin', esc_url(RH_FRAMEWORK_URL .'/install/assets/js/merlin'. $suffix .'.js') , array( 'jquery-core', 'wp-color-picker' ), RH_PLUGIN_VER);
		
		$texts = array(
			'something_went_wrong' => esc_html__( 'Something went wrong. Please refresh the page and try again!', 'rehub-framework' ),
		);

		// Localize the tgmpa javascript.
 		if (class_exists('TGM_Plugin_Activation')) {
			wp_localize_script(
				'merlin', 'install_params', array(
					'tgm_plugin_nonce' => array(
						'update'  => wp_create_nonce( 'tgmpa-update' ),
						'install' => wp_create_nonce( 'tgmpa-install' ),
					),
					'tgm_bulk_url' => $this->tgmpa->get_tgmpa_url(),
					'ajaxurl' => admin_url( 'admin-ajax.php' ),
					'wpnonce' => wp_create_nonce( 'rehub_install_nonce' ),
					'texts' => $texts,
				)
			);
		}else {
			// If TMGPA is not included.
			wp_localize_script(
				'merlin', 'install_params', array(
					'ajaxurl' => admin_url( 'admin-ajax.php' ),
					'wpnonce' => wp_create_nonce( 'rehub_install_nonce' ),
					'texts'   => $texts,
				)
			);
		}
		
		ob_start();
		
		$this->rehub_install_header(); ?>
		
		<div class="merlin__wrapper">
			<div class="rehub_install_wizard"><?php echo esc_html__('REHub Installation Wizard', 'rehub-framework'); ?></div>
			<div class="merlin__content merlin__content--<?php echo esc_attr( strtolower( $title ) ); ?>">
				<?php $this->rehub_show_steps_body(); ?>
				<?php $this->rehub_output_bottom_dots(); ?>
			</div>
			<?php printf( '<a class="return-to-dashboard" href="%s">%s</a>', esc_url(admin_url('admin.php?page=vpt_option')), esc_html__('Return to dashboard', 'rehub-framework')); ?>
		</div>
		
		<?php $this->rehub_install_footer(); 
		exit;
	}

	/*  */
	function rehub_show_steps_body(){
		$current_step = isset( $_GET['step'] ) ? sanitize_key($_GET['step']) : $this->install_config['start_steps'];
		
		if($this->install_config['total_steps'] == $current_step){
			call_user_func(array($this, 'finish_page'));
		}
		else{
			$current_method = 'rh_step_'. $current_step;
			call_user_func(array($this, $current_method));
		}
	}

	/* STEP 1 - Wellcome page */
	function rh_step_1() {
		$current_step = (isset( $_GET['step'] )) ? sanitize_key($_GET['step']) : $this->install_config['start_steps'];
		$stepDetails = $this->install_config['steps'][$current_step];
		?>
		<div class="merlin__content--transition">
			<?php $this->logoin_header(); ?>
			<h1><?php echo $stepDetails['title']; ?></h1>
			<p><?php echo ($stepDetails['description']) ? $stepDetails['description'] : ''; ?></p>
		</div>
		<footer class="merlin__content__footer">
			<a href="<?php echo esc_url(admin_url('admin.php?page=vpt_option')); ?>" class="merlin__button merlin__button--skip"><?php echo esc_html__( 'Cancel', 'rehub-framework' ); ?></a>
			<a href="<?php echo esc_url($this->step_next_link()); ?>" class="merlin__button merlin__button--next merlin__button--proceed merlin__button--colorchange"><?php echo esc_html__( 'Start', 'rehub-framework' ); ?></a>
			<?php wp_nonce_field('rehub_install_nonce'); ?>
		</footer>
	<?php
	}

	/* STEP 2 - Import Child Theme options */
	function rh_step_2(){
		$current_step = isset( $_GET['step'] ) ? sanitize_key($_GET['step']) : $this->install_config['start_steps'];
		$stepDetails = $this->install_config['steps'][$current_step];
		?>
		<div class="merlin__content--transition">
			<?php $this->logoin_header(); ?>
			<h1><?php echo $stepDetails['title']; ?></h1>
			<p class="notice-text"><?php echo ($stepDetails['description']) ? $stepDetails['description'] : ''; ?></p>
		</div>
		<form action="" method="post">
			<ul class="merlin__drawer--import-content">
				<?php echo $stepDetails['fields']; ?>
			</ul>
			<footer class="merlin__content__footer">
				<a id="skip" href="<?php echo esc_url($this->step_next_link()); ?>" class="merlin__button merlin__button--skip merlin__button--proceed"><?php echo esc_html__('Skip','rehub-framework'); ?></a>
				<a href="<?php echo esc_url($this->step_next_link()); ?>" class="merlin__button merlin__button--next button-next" data-callback="save_settings">
					<span class="merlin__button--loading__text"><?php echo esc_html__('Import', 'rehub-framework'); ?></span><?php echo $this->loading_spinner(); ?>
				</a>
				<?php wp_nonce_field( 'rehub_install_nonce' ); ?>
			</footer>
		</form>
	<?php
	}

	/* STEP 3 - Setup Logo */
	function rh_step_3() {
		$current_step = isset( $_GET['step'] ) ? sanitize_key($_GET['step']) : $this->install_config['start_steps'];
		$stepDetails = $this->install_config['steps'][$current_step];
		wp_enqueue_media();
		?>
		<div class="merlin__content--transition">
			<?php $this->logoin_header(); ?>
			<h1><?php echo $stepDetails['title']; ?></h1>
			<p class="notice-text"><?php echo ($stepDetails['description']) ? $stepDetails['description'] : ''; ?></p>
		</div>
		<form action="" method="post">
			<ul class="merlin__drawer--import-content">
				<?php echo $stepDetails['fields']; ?>
			</ul>
			<footer class="merlin__content__footer">
				<a id="skip" href="<?php echo esc_url($this->step_next_link()); ?>" class="merlin__button merlin__button--skip merlin__button--proceed"><?php echo esc_html__('Skip','rehub-framework'); ?></a>
				<a href="<?php echo esc_url($this->step_next_link() ); ?>" class="merlin__button merlin__button--next button-next" data-callback="save_settings">
					<span class="merlin__button--loading__text"><?php echo esc_html__('Save' ,'rehub-framework'); ?></span><?php echo $this->loading_spinner(); ?>
				</a>
				<?php wp_nonce_field( 'rehub_install_nonce' ); ?>
			</footer>
		</form>
	<?php
	}

	function rh_step_4() {
		$current_step = isset( $_GET['step'] ) ? sanitize_key($_GET['step']) : $this->install_config['start_steps'];
		$stepDetails = $this->install_config['steps'][$current_step];
		wp_enqueue_media();
		wp_enqueue_style('wp-color-picker');
		?>
		<div class="merlin__content--transition">
			<?php $this->logoin_header(); ?>
			<h1><?php echo $stepDetails['title']; ?></h1>
			<p class="notice-text"><?php echo ($stepDetails['description']) ? $stepDetails['description'] : ''; ?></p>
		</div>
		<form action="" method="post">
			<ul class="merlin__drawer--import-content">
				<?php echo $stepDetails['fields']; ?>
			</ul>
			<footer class="merlin__content__footer">
				<a id="skip" href="<?php echo esc_url($this->step_next_link()); ?>" class="merlin__button merlin__button--skip merlin__button--proceed"><?php echo esc_html__('Skip','rehub-framework'); ?></a>
				<a href="<?php echo esc_url($this->step_next_link() ); ?>" class="merlin__button merlin__button--next button-next" data-callback="save_settings">
					<span class="merlin__button--loading__text"><?php echo esc_html__('Save' ,'rehub-framework'); ?></span><?php echo $this->loading_spinner(); ?>
				</a>
				<?php wp_nonce_field( 'rehub_install_nonce' ); ?>
			</footer>
		</form>
	<?php
	}

	function rh_step_5() {
		$current_step = isset( $_GET['step'] ) ? sanitize_key($_GET['step']) : $this->install_config['start_steps'];
		$stepDetails = $this->install_config['steps'][$current_step];
		wp_enqueue_media();
		wp_enqueue_style('wp-color-picker');
		?>
		<div class="merlin__content--transition">
			<?php $this->logoin_header(); ?>
			<h1><?php echo $stepDetails['title']; ?></h1>
			<p class="notice-text"><?php echo ($stepDetails['description']) ? $stepDetails['description'] : ''; ?></p>
		</div>
		<form action="" method="post">
			<ul class="merlin__drawer--import-content">
				<?php echo $stepDetails['fields']; ?>
			</ul>
			<footer class="merlin__content__footer">
				<a id="skip" href="<?php echo esc_url($this->step_next_link()); ?>" class="merlin__button merlin__button--skip merlin__button--proceed"><?php echo esc_html__('Skip','rehub-framework'); ?></a>
				<a href="<?php echo esc_url($this->step_next_link() ); ?>" class="merlin__button merlin__button--next button-next" data-callback="save_settings">
					<span class="merlin__button--loading__text"><?php echo esc_html__('Save' ,'rehub-framework'); ?></span><?php echo $this->loading_spinner(); ?>
				</a>
				<?php wp_nonce_field( 'rehub_install_nonce' ); ?>
			</footer>
		</form>
	<?php
	}

	/* STEP 6 - Setup Wishlist & Comparision pages */
	function rh_step_6(){
		$current_step = isset( $_GET['step'] ) ? sanitize_key($_GET['step']) : $this->install_config['start_steps'];
		$stepDetails = $this->install_config['steps'][$current_step];
		?>
		<div class="merlin__content--transition">
			<?php $this->logoin_header(); ?>
			<h1><?php echo $stepDetails['title']; ?></h1>
			<p class="notice-text"><?php echo ($stepDetails['description']) ? $stepDetails['description'] : ''; ?></p>
		</div>
		<form action="" method="post">
			<ul class="merlin__drawer--import-content">
				<?php echo $stepDetails['fields']; ?>
			</ul>
			<footer class="merlin__content__footer">
				<a id="skip" href="<?php echo esc_url($this->step_next_link() ); ?>" class="merlin__button merlin__button--skip merlin__button--proceed"><?php echo esc_html__('Skip','rehub-framework'); ?></a>
				<a href="<?php echo esc_url($this->step_next_link() ); ?>" class="merlin__button merlin__button--next button-next" data-callback="save_settings">
					<span class="merlin__button--loading__text"><?php echo esc_html__('Save', 'rehub-framework'); ?></span><?php echo $this->loading_spinner(); ?>
				</a>
				<?php wp_nonce_field( 'rehub_install_nonce' ); ?>
			</footer>
		</form>
	<?php
	}

	/* STEP 7 - Install plugins */
	function rh_step_7(){
		// Variables.
		$url = wp_nonce_url( add_query_arg( array( 'plugins' => 'go' ) ), 'rehub_install_nonce' );
		$method = '';
		$fields = array_keys( $_POST );
		$creds  = request_filesystem_credentials( esc_url_raw( $url ), $method, false, false, $fields );

		tgmpa_load_bulk_installer();

		if ( false === $creds ) {
			return true;
		}

		if ( ! WP_Filesystem( $creds ) ) {
			request_filesystem_credentials( esc_url_raw( $url ), $method, true, false, $fields );
			return true;
		}
		
		$current_step = isset( $_GET['step'] ) ? sanitize_key($_GET['step']) : $this->install_config['start_steps'];
		$stepDetails =$this->install_config['steps'][$current_step];
		$plugins = $this->get_tgmpa_plugins();
		$required_plugins = array();
		$count = count( $plugins['all'] );
		
		if( $count ){
			foreach ( $plugins['all'] as $slug => $plugin ) {
				$required_plugins[ $slug ] = $plugin;
			}
		}
		
		$header = $count ? $stepDetails['title'] : $stepDetails['plugins-header-success'];
		$paragraph = $count ? $stepDetails['description'] : $stepDetails['plugins-success%s'];
		$class = $count ? null : 'no-plugins';

		?>
		<div class="merlin__content--transition">
			<?php $this->logoin_header(); ?>
			<h1><?php echo $header; ?></h1>
			<p><?php echo $paragraph; ?></p>
			
		</div>
		
		<form action="" method="post">
			<?php if ( $count ) : ?>
				<ul class=" merlin__drawer--install-plugins">
				<?php if ( !empty( $required_plugins ) ) : ?>
					<?php foreach ( $required_plugins as $slug => $plugin ) : ?>
						<li data-slug="<?php echo esc_attr( $slug ); ?>">
							<input type="checkbox" name="default_plugins[<?php echo esc_attr( $slug ); ?>]" class="checkbox" id="default_plugins_<?php echo esc_attr( $slug ); ?>" value="1" checked>
							<label for="default_plugins_<?php echo esc_attr( $slug ); ?>">
								<i></i>
								<span><?php echo esc_html( $plugin['name'] ); ?></span>
								<?php if($plugin['required']):?>
									<span class="badge">
										<span class="hint--top" aria-label="<?php esc_html_e( 'Required', 'rehub-framework' ); ?>">
											<?php esc_html_e( 'req', 'rehub-framework' ); ?>
										</span>
									</span>
								<?php else:?>
									<span class="badge">
										<span class="hint--top" aria-label="<?php esc_html_e( 'Optional', 'rehub-framework' ); ?>">
											<?php esc_html_e( 'opt', 'rehub-framework' ); ?>
										</span>
									</span>
								<?php endif;?>

							</label>
						</li>
					<?php endforeach; ?>
				<?php endif; ?>
				</ul>
			<?php endif; ?>
			
			<footer class="merlin__content__footer <?php echo esc_attr( $class ); ?>">
				<?php if ( $count ) : ?>
					<?php $this->closer_skip_button(); ?>
					<a id="skip" href="<?php echo esc_url( $this->step_next_link() ); ?>" class="merlin__button merlin__button--skip merlin__button--proceed"><?php echo esc_html__('Skip','rehub-framework'); ?></a>
					<a href="<?php echo esc_url( $this->step_next_link() ); ?>" class="merlin__button merlin__button--next button-next" data-callback="install_plugins">
						<span class="merlin__button--loading__text"><?php echo esc_html__('Install', 'rehub-framework'); ?></span>
						<?php echo $this->loading_spinner(); ?>
					</a>
				<?php else : ?>
					<a href="<?php echo esc_url( $this->step_next_link() ); ?>" class="merlin__button merlin__button--next merlin__button--proceed merlin__button--colorchange"><?php echo esc_html__('Next', 'rehub-framework'); ?></a>
				<?php endif; ?>
				<?php wp_nonce_field( 'rehub_install_nonce' ); ?>
			</footer>
		</form>
	<?php
	}

	/* STEP 8 - Select Website type for Info 
	function rh_step_8(){
		$current_step = isset( $_GET['step'] ) ? sanitize_key($_GET['step']) : $this->install_config['start_steps'];
		$stepDetails = $this->install_config['steps'][$current_step];
		?>
		<div class="merlin__content--transition">
			<?php $this->logoin_header(); ?>
			<h1><?php echo $stepDetails['title']; ?></h1>
			<p><?php echo ($stepDetails['description']) ? $stepDetails['description'] : ''; ?></p>
		</div>
		<ul class="merlin__drawer--import-content">
			<?php echo $stepDetails['fields']; ?>
		</ul>
		<footer class="merlin__content__footer">
			<a id="skip" href="<?php echo esc_url($this->step_next_link()); ?>" class="merlin__button merlin__button--skip merlin__button--proceed"><?php echo esc_html__('Skip','rehub-framework'); ?></a>
			<a id="finish" href="<?php echo esc_url($this->step_next_link()); ?>" class="merlin__button merlin__button--next button-next" data-callback="save_settings">
				<span class="merlin__button--loading__text"><?php echo esc_html__('Finish', 'rehub-framework'); ?></span><?php echo $this->loading_spinner(); ?>
			</a>
			<?php wp_nonce_field( 'rehub_install_nonce' ); ?>
		</footer>
	<?php
	}*/

	/*  */
	function finish_page() {
		update_option( 'rehub_installer_completed', time() ); 
		$stepDetails = $this->install_config['steps'][$this->install_config['total_steps']];
		//$current_type = (isset($_GET['type']) && $_GET['type']) ? sanitize_key($_GET['type']) : '';
	?>	
		<div class="merlin__content--transition">
			<div class="rehub_branding"></div>
			<h1><?php echo $stepDetails['title']; ?></h1>
			<p><?php echo ($stepDetails['description']) ? $stepDetails['description'] : ''; ?></p>
		</div>
		<ul class="merlin__drawer--import-content">
			<?php echo $stepDetails['fields']; ?>
		</ul>	
		<footer class="merlin__content__footer merlin__content__footer--fullwidth">
			<a href="<?php echo esc_url( admin_url( 'admin.php?page=rehub-support' ) ); ?>" class="merlin__button merlin__button--blue merlin__button--fullwidth merlin__button--popin"><?php echo esc_html__( 'Open Support Center', 'rehub-framework' ); ?></a>
		</footer>
	<?php
	}

	/* Echo theme logo and Success chevron in the template */
	function logoin_header(){
		?>
		<div class="rehub_branding"><img src="<?php echo get_template_directory_uri() .'/admin/screens/images/logo.png'; ?>" /></div>
		<svg class="icon icon--checkmark" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 52 52">
			<circle class="icon--checkmark__circle" cx="26" cy="26" r="25" fill="none"/><path class="icon--checkmark__check" fill="none" d="M14.1 27.2l7.1 7.2 16.7-16.8"/>
		</svg>
		<?php
	}

	/*  */
	function closer_skip_button(){
		?>
		<a href="<?php echo esc_url($this->step_next_link()); ?>" class="merlin__button merlin__button--skip merlin__button--closer merlin__button--proceed"><?php echo esc_html__('Skip', 'rehub-framework'); ?></a>
		<?php
	}

	/*  */
	function loading_spinner(){
		$spinner = esc_url(RH_FRAMEWORK_ABSPATH .'/install/assets/images/spinner.php');
		get_template_part($spinner);
	}

	/*  */
	function step_next_link() {
		$current_step = isset( $_GET['step'] ) ? sanitize_key($_GET['step']) : $this->install_config['start_steps'];	
		
		$step = ++$current_step;

		return add_query_arg( 'step', $step );
	}

	/*  */
	function rehub_output_bottom_dots(){
		$current_step = isset( $_GET['step'] ) ? sanitize_key($_GET['step']) : $this->install_config['start_steps']; 
		
		?>
		<ol class="dots">
			<?php for( $i = 1; $i < $this->install_config['total_steps']; $i++ ) :
				$stepDetails = $this->install_config['steps'][$i];
				$class_attr = '';
				$show_link = true;
				if ( $i === $current_step ) {
					$class_attr = 'active';
				} elseif ( $current_step >  $i) {
					$class_attr = 'done';
				}
				if( $show_link ){
				?>
				<li class="<?php echo esc_attr( $class_attr ); ?>">
					<a href="<?php echo esc_url(add_query_arg('step', $i)); ?>" title="<?php echo esc_attr($stepDetails['title'], 'rehub-framework'); ?>"></a>
				</li>
				<?php }
			endfor; ?>
		</ol>
		<?php
	}

	/*  */
	function rehub_install_header() {
		$current_step = isset( $_GET['step'] ) ? sanitize_key($_GET['step']) : $this->install_config['start_steps']; 
		$stepDetails = $this->install_config['steps'][$current_step];
		?>

		<!DOCTYPE html>
		<html xmlns="http://www.w3.org/1999/xhtml" <?php language_attributes(); ?>>
		<head>
			<meta name="viewport" content="width=device-width"/>
			<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
			<title><?php echo $stepDetails['title']; ?></title>
			<?php do_action( 'admin_print_styles' ); ?>
			<?php do_action( 'admin_print_scripts' ); ?>
			
		</head>
		<body class="merlin__body merlin__body--<?php echo esc_attr( $current_step ); ?>">
		<?php
	}

	/*  */
	function rehub_install_footer() {
		?>	 
		</body>
		<?php do_action( 'admin_footer' ); ?>
		<?php do_action( 'admin_print_footer_scripts' ); ?>
		</html>
		<?php
	}
	
	/* Save data durings steps */
	function _save_steps_data(){
		
		if(!wp_verify_nonce($_REQUEST['wpnonce'], 'rehub_install_nonce')) {
			echo json_encode(array("status" => 300,"message" => 'Request not valid'));
			die;
		}

		if( !current_user_can( 'manage_options' ) ) {
			return;
		}
		
		$rehub_option = get_option('rehub_option');
		
		
		if(!empty($rehub_option)){
			foreach($_POST as $postKey => $postValue){
				if($postKey == 'rehub_design_selector'){

					$themes = array_map( 'strtolower', $this->themes() );
					
					if( in_array( $postValue, $themes ) ){
						$options_file_path = get_template_directory() .'/admin/demo/'. $postValue .'-theme.json';
					}else{
						$options_file_path = '';
					}

					update_option( 'rehub_design_selector', $postValue );
				}
				elseif($postKey == 'wishlist'){
					if(empty($rehub_option['wishlistpage']) && $postValue){
						$rehub_option['wishlistpage'] = sanitize_key($this->rehub_create_page(esc_html__('Wishlist', 'rehub-framework'), '[rh_get_user_favorites]'));
						$rehub_option['header_seven_wishlist'] = esc_url(get_permalink($rehub_option['wishlistpage']));
						$this->update_wizard_option('header_seven_wishlist', $rehub_option['header_seven_wishlist']);
					}
				}
				elseif($postKey == 'comparision' ){
					if(empty($rehub_option['compare_page']) && $postValue){
						$rehub_option['compare_page'] = sanitize_key($this->rehub_create_page(esc_html__('Comparison', 'rehub-framework'), '[wpsm_woocharts]'));
						$this->update_wizard_option('compare_page', $rehub_option['compare_page']);
						update_post_meta($rehub_option['compare_page'], 'content_type', 'full_width');
					}
				}
				elseif($postKey == 'blogarchive'){
					if(empty($rehub_option['enable_blog_posttype']) && $postValue){
						$rehub_option['enable_blog_posttype'] = '1';
					}
				}
				elseif($postKey == 'storearchive'){
					if(empty($rehub_option['enable_brand_taxonomy']) && $postValue){
						$rehub_option['enable_brand_taxonomy'] = '1';
					}
				}
				elseif(($postKey == 'rehub_logo' || $postKey == 'rehub_logo_inmenu_url' || $postKey == 'logo_mobilesliding') && $postValue !=''){
					$postValue = esc_url($postValue);
					$rehub_option[$postKey] = $postValue;
					$this->update_wizard_option($postKey, $postValue);

				}
				elseif(isset($rehub_option[$postKey]) && $postValue !=''){
					$postValue = sanitize_text_field($postValue);
					$rehub_option[$postKey] = $postValue;
					$this->update_wizard_option($postKey, $postValue);
				} 
			} 
		}

		if( isset($options_file_path) && !empty($options_file_path)) {
			$options_raw_data = $this->rehub_data_from_file( $options_file_path );
			if ( !is_wp_error( $options_raw_data ) ) {
				$rehub_option = json_decode( $options_raw_data, true );
			}
			else{
				wp_send_json( $options_raw_data );
			}
		}
		
		update_option('rehub_option', $rehub_option);
		$customizer = new REHub_Framework_Customizer();
    	$customizer->rh_save_customizer_options( $rehub_option );				
		
		wp_send_json(
			array(
				'done' => 1,
				'message' => "Stored Successfully",
			)
		);
	}

	private function update_wizard_option($key, $value){
		$rehub_wizard_option = get_option('rehub_wizard_option');
		if(!empty($rehub_wizard_option)){
			$rehub_wizard_option[$key] = $value;
		}else{
			$rehub_wizard_option = array($key=>$value);
		}
		update_option( 'rehub_wizard_option', $rehub_wizard_option );
	}

	/*  */
	function rehub_data_from_file($file_path) {
		$data = file_get_contents($file_path);
		if (!$data) {
			return new WP_Error(
				'failed_reading_file_from_server',
				sprintf(
					__( 'An error occurred while reading a file from your server! Tried reading file from path: %s.', 'rehub-framework' ),
					$file_path
				)
			);
		}
		return $data;
	}

	/*  */
	protected function rehub_create_page($title, $content){
		$post_details = array(
			'post_title'    => $title,
			'post_content'  => $content,
			'post_status'   => 'publish',
			'post_author'   => get_current_user_id(),
			'post_type' => 'page',
		);
		return wp_insert_post($post_details);
	}
	
	/*  */
	public function register_plugins(){
		$plugins = array();
		$design = get_option('rehub_design_selector');

		if( empty($design) )
			return $plugins;
		
		$content = array( 'rewise', 'recompare', 'repick', 'remag', 'rething' );
		//$frontend = array( 'remag', 'recash', 'redirect', 'redeal' );
		//$buddypress = array( 'remarket', 'dokan', 'recash', 'redeal' );
		//$woocommerce = array( 'redirect', 'rewise', 'retour', 'recompare', 'redokannew', 'revendor', 'recart'  );
	
		if( $design != 'repick' && $design != 'recash' && $design != 'remag' && $design != 'remart' && $design != 'reviewit' && $design != 'recart' && $design != 'recompare' && $design != 'redeal' && $design != 'rewise' && $design != 'regame' && $design != 'relearn'){
			$plugins[] = array(
				'name' => 'Elementor',
				'slug' => 'elementor',
				'required'  => true,
				'image_url'          => get_template_directory_uri() . '/admin/screens/images/elementor.jpg',
				'description'			=> 'Elementor Page Builder',	
			);
		}
		if( in_array( $design, $content ) ){
			$plugins[] = array(
				'name' => 'Content Egg',
				'slug' => 'content-egg',
				'required'  => false,
				'description'			=> 'All in one for moneymaking',
				'image_url'          => get_template_directory_uri() . '/admin/screens/images/contentegg.png',	
			);
		}
		$plugins[] = array(
			'name' => 'One Click Demo Import',
			'slug' => 'one-click-demo-import',
			'required'  => false,	
			'image_url' => get_template_directory_uri() . '/admin/screens/images/ocdi.jpg',
			'description' => 'Import demo content and settings',
		);		

		tgmpa( $plugins );
	}
	
	/*  */
	protected function get_tgmpa_plugins() {
		$plugins = array(
			'all' => array(), // Meaning: all plugins which still have open actions.
			'install'  => array(),
			'update'   => array(),
			'activate' => array(),
		);

		foreach ( $this->tgmpa->plugins as $slug => $plugin ) {
			if ( $this->tgmpa->is_plugin_active( $slug ) ) {
				continue;
			} else {
				$plugins['all'][ $slug ] = $plugin;
				if ( ! $this->tgmpa->is_plugin_installed( $slug ) ) {
					$plugins['install'][ $slug ] = $plugin;
				} else {
					if ( $this->tgmpa->can_plugin_activate( $slug ) ) {
						$plugins['activate'][ $slug ] = $plugin;
					}
				}
			}
		}

		return $plugins;
	}
	
	/* Do plugins' AJAX */
	function _ajax_plugins() {

		if ( !check_ajax_referer( 'rehub_install_nonce', 'wpnonce' ) || empty( $_POST['slug'] ) ) {
			exit( 0 );
		}

		$json = array();
		$tgmpa_url = $this->tgmpa->get_tgmpa_url();
		$plugins = $this->get_tgmpa_plugins();

		foreach( $plugins['activate'] as $slug => $plugin ) {
			if ( $_POST['slug'] === $slug ) {
				$json = array(
					'url'           => $tgmpa_url,
					'plugin'        => array( $slug ),
					'tgmpa-page'    => $this->tgmpa->menu,
					'plugin_status' => 'all',
					'_wpnonce'      => wp_create_nonce( 'bulk-plugins' ),
					'action'        => 'tgmpa-bulk-activate',
					'action2'       => - 1,
					'message'       => esc_html__( 'Activating', 'rehub-framework' ),
				);
				break;
			}
		}

		foreach ( $plugins['install'] as $slug => $plugin ) {
			if ( $_POST['slug'] === $slug ) {
				$json = array(
					'url'           => $tgmpa_url,
					'plugin'        => array( $slug ),
					'tgmpa-page'    => $this->tgmpa->menu,
					'plugin_status' => 'all',
					'_wpnonce'      => wp_create_nonce( 'bulk-plugins' ),
					'action'        => 'tgmpa-bulk-install',
					'action2'       => - 1,
					'message'       => esc_html__( 'Installing', 'rehub-framework' ),
				);
				break;
			}
		}

		if ( $json ) {
			$json['hash'] = md5( serialize( $json ) );
			$json['message'] = esc_html__( 'Installing', 'rehub-framework' );
			wp_send_json( $json );
		} else {
			wp_send_json(
				array(
					'done' => 0,
					'message' => esc_html__( 'Error', 'rehub-framework' ),
				)
			);
		}

		exit;
	}
	
	/*  */
	function themes(){
		return array( '', 'ReMag', 'RePick', 'ReThing', 'ReCash', 'ReDirect', 'ReVendor', 'ReWise', 'ReDokanNew', 'ReMarket', 'ReCompare', 'ReCart', 'ReTour', 'ReDokanNew', 'ReDeal', 'ReFashion', 'ReViewit', 'ReDigit', 'ReGame', 'ReLearn', 'ReMart' );
	}
	
	/*  */
	protected function theme_select(){
		$themes = $this->themes();
		$out = '<select name="rehub_design_selector" id="rehub_design_selector">';
		foreach( $themes as $theme ){
			if( empty( $theme ) )
				continue;
			$out .= '<option value="'. strtolower( $theme ) .'">'. $theme .'</option>';
		}
		$out .= '</select>';
		return $out;
	}
	
	/*  */
	protected function theme_screens(){
		$themes = $this->themes();
		$out = '';
		foreach( $themes as $key => $theme ){
			if( empty( $theme ) || $key == 1 )
				continue;
			$out .= '<img src="'. get_template_directory_uri() .'/admin/screens/images/demo'. $key .'_preview.jpg" width="244" class="rehub_install_theme_preview" id="design-'. strtolower( $theme ) .'">';
		}
		$out .= '';
		return $out;
	}
	
	/* Theme installing config array */
	function _install_config(){
		$rehub_mobile_header_bg = REHub_Framework::get_option('rehub_mobile_header_bg');
		if($rehub_mobile_header_bg){
			$rehub_mobile_header_bg = sanitize_hex_color($rehub_mobile_header_bg);
		}
		$rehub_mobile_header_color = REHub_Framework::get_option('rehub_mobile_header_color');
		if($rehub_mobile_header_color){
			$rehub_mobile_header_color = sanitize_hex_color($rehub_mobile_header_color);
		}
		return array(
			'start_steps' => 1,
			'total_steps' => 8,
			'installerpage' => 'rehub_wizard',
			'dev_mode' => false, 
			'steps' => array(
				1 => array(
					'title' => esc_html__('Welcome', 'rehub-framework'),
					'description' => esc_html__( 'This Installation Wizard helps you to setup the necessary options for REHub theme. It is optional & should take only a few minutes.', 'rehub-framework'),
					'fields' => '',
				),
				2 => array(
					'title' => esc_html__('Import Theme options', 'rehub-framework'),
					'description' => '',
					'fields' => '<li class="rehub_install_center">'. $this->theme_select() .'<img src="'. get_template_directory_uri() .'/admin/screens/images/demo1_preview.jpg" width="244" class="rehub_install_theme_preview" id="design-rehub" style="display:block">'. $this->theme_screens() .'</li>',
				),
				3 => array(
					'title' => esc_html__('Upload logo', 'rehub-framework'),
					'description' => esc_html__('Max width is 450px. (1200px for full width, 180px for logo + menu row layout)', 'rehub-framework'),
					'fields' => '<li class="rehub_install_center">
						<input type="hidden" value="" class="regular-text process_custom_images" id="process_custom_images" name="rehub_logo" value="">
						<button type="button" class="set_custom_images merlin__button merlin__button--blue">Set Logo</button>'. $this->logo .'</li>'
				),
				4 => array(
					'title' => esc_html__('Upload mobile logo', 'rehub-framework'),
					'description' => esc_html__('It will be visible on Menu panel only on mobiles. If you keep default colors of mobile header, theme will use colors of menu for mobile header', 'rehub-framework'),
					'fields' => '<li class="rehub_install_center">
						<input type="hidden" value="" class="regular-text process_custom_images" id="process_custom_images" name="rehub_logo_inmenu_url" value="">
						<button type="button" class="set_custom_images merlin__button merlin__button--blue">Set Logo</button>'. $this->mobilelogo .'</li>
						<li class="rehub_install_center"><label for="rehub_mobile_header_bg"><span>'. __('Mobile header background', 'rehub-framework') .'</span></label>
						<input type="text" class="color-field" name="rehub_mobile_header_bg" id="rehub_mobile_header_bg" value="'. $rehub_mobile_header_bg .'"></li>
						<li class="rehub_install_center"><label for="rehub_mobile_header_color"><span>'. __('Mobile header link color', 'rehub-framework') .'</span></label>
						<input type="text" class="color-field" name="rehub_mobile_header_color" id="rehub_mobile_header_color" value="'. $rehub_mobile_header_color.'"></li>'
				),
				5 => array(
					'title' => esc_html__('Upload logo in Sliding panel', 'rehub-framework'),
					'description' => esc_html__('It will be visible on Menu Sliding panel only on mobiles', 'rehub-framework'),
					'fields' => '<li class="rehub_install_center">
						<input type="hidden" value="" class="regular-text process_custom_images" id="process_custom_images" name="logo_mobilesliding" value="">
						<button type="button" class="set_custom_images merlin__button merlin__button--blue">Set Logo</button>'. $this->slidinglogo .'</li>
						<li class="rehub_install_center"><label for="color_mobilesliding"><span>'. __('Background color under logo', 'rehub-framework') .'</span></label>
						<input type="text" class="color-field" name="color_mobilesliding" id="color_mobilesliding" value="'. REHub_Framework::get_option('color_mobilesliding') .'"></li>'
				),
				6 => array(
					'title' => esc_html__('Create pages', 'rehub-framework'),
					'description' => esc_html__('Please, check which system pages you want to create. Later, resave your permalinks in Settings - Permalinks', 'rehub-framework'),
					'fields' => '<li class="merlin__drawer--import-content__list-item status status--pending">
						<input type="checkbox" class="checkbox" name="wishlist" id="wishlist" '.(REHub_Framework::get_option('wishlistpage') ? 'checked': '').'>
						<label for="wishlist"><i></i><span>'. esc_html__('Wishlist', 'rehub-framework') .'</span></label></li>
						<li class="merlin__drawer--import-content__list-item status status--pending">
						<input type="checkbox" class="checkbox" name="comparision" id="comparision" '.(REHub_Framework::get_option('compare_page') ? 'checked': '').'>
						<label for="comparision"><i></i><span>'. esc_html__('Comparison', 'rehub-framework') .'</span></label></li>
						<li class="merlin__drawer--import-content__list-item status status--pending">
						<input type="checkbox" class="checkbox" name="blogarchive" id="blogarchive" '.(REHub_Framework::get_option('enable_blog_posttype') ? 'checked': '').'>
						<label for="blogarchive"><i></i><span>'. esc_html__('Additional Blog section', 'rehub-framework') .'</span></label></li>
						<li class="merlin__drawer--import-content__list-item status status--pending">
						<input type="checkbox" class="checkbox" name="storearchive" id="storearchive" '.(REHub_Framework::get_option('enable_brand_taxonomy') ? 'checked': '').'>
						<label for="storearchive"><i></i><span>'. esc_html__('Affiliate store pages', 'rehub-framework') .'</span></label></li>',
				),
				7 => array(
					'title' => esc_html__('Install plugins', 'rehub-framework'),
					'plugins-header-success' => esc_html__('You are good to go!.', 'rehub-framework'),
					'description' => esc_html__('Do you want to install plugins to prepare the site for demo import.', 'rehub-framework'),
					'plugins-success%s' => esc_html__('The required WordPress plugins are all installed and up to date. Press "Next" to finish the setup wizard.', 'rehub-framework'),
					'fields' => '<li class="rehub_install_center"></li>',
				),
				8 => array(
					'title' =>  esc_html__('Setup Done. Have fun!', 'rehub-framework'),
					'description' => esc_html__('SAVE YOUR TIME. If you have any questions, search them first via QUICK SEARCH in your Support center. ', 'rehub-framework'),
					'fields' => 'You can find there also step by step tutorials and all important links',
				),
			),
		);
	}
}

new RH_Install_Theme();