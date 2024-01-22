<?php

namespace Rehub\Gutenberg\Blocks;

defined('ABSPATH') OR exit;

class ComparisonTable{

	public function __construct(){
		add_action('init', array( $this, 'init_handler' ));
	}

	public function init_handler(){
		register_block_type(__DIR__ . '/comparisontable', array(
			'attributes'      => $this->attributes,
			'render_callback' => array( $this, 'render_block' ),
		));
	}

	public $attributes = array(
        'enableBadges' => array('type'    => 'boolean','default' => false ),
		'urlBadges' => array('type'    => 'boolean','default' => false ),
		'enableImage' => array('type'    => 'boolean','default' => true ),
		'enableTitle' => array('type' => 'boolean','default' => true ),
		'enableSubtitle' => array('type' => 'boolean','default' => true ),
		'enableStars' => array('type' => 'boolean', 'default' => true ),
		'enableNumbers' => array( 'type' => 'boolean', 'default' => false ),
		'enableList' => array( 'type' => 'boolean', 'default' => false ),
		'enableListTitle' => array( 'type' => 'boolean', 'default' => true ),
		'enableButton' => array( 'type' => 'boolean', 'default' => true ),
		'enableBottom' => array( 'type' => 'boolean', 'default' => true ),
		'enablePros' => array( 'type' => 'boolean', 'default' => true ),
		'enableCons' => array( 'type' => 'boolean', 'default' => true ),
		'enableSpec' => array( 'type' => 'boolean', 'default' => false ),
		'enableCallout' => array( 'type' => 'boolean', 'default' => false ),
		'titleTag' => array( 'type' => 'string', 'default' => 'div' ),
		'titleFont' => array( 'type' => 'number', 'default' => 18 ),
		'contentFont' => array( 'type' => 'number', 'default' => 14 ),
		'bottomTitle' => array( 'type' => 'string', 'default' => 'Bottom Line' ),
		'prosTitle' => array( 'type' => 'string', 'default' => 'Pros' ),
		'consTitle' => array( 'type' => 'string', 'default' => 'Cons' ),
		'specTitle' => array( 'type' => 'string', 'default' => 'Spec' ),
		'responsiveView' => array( 'type' => 'string', 'default' => 'stacked' ),
		'disablefirst' => array('type'    => 'boolean','default' => false ),
		'extraColumns'=> array( 'type' => 'array', 'default' => array()),
		'firstColumnWidth'=> array( 'type' => 'number', 'default' => 100),
	);

	public function render_block( $settings = array(), $inner_content='' ) {
		$alignclass = (!empty($settings['align'])) ? ' align'.esc_attr($settings['align']).' ' : '';
		ob_start();
	?>
		<div class="<?php echo ''.$alignclass;?>comparison-table <?php echo $settings['responsiveView'] ==='slide' ? 'swiper-container' : ''; ?> <?php echo $settings['responsiveView']; ?> <?php echo $settings['enableBadges'] ? 'has-badges' : ''; ?> <?php echo $settings['disablefirst'] ? 'noheadertable' : ''; ?>" data-table-type="<?php echo $settings['responsiveView']; ?>">
			<div class="comparison-item comparison-header" style="flex: 0 0 <?php echo (int)$settings['firstColumnWidth'];?>px">
				<div class="item-header" data-match-height="itemHeader"></div>
				<?php if($settings['enableBottom']): ?>
					<div class="item-row-description item-row-bottomline" data-match-height="itemBottomline">
						<?php echo $settings['bottomTitle']; ?>
					</div>
				<?php endif; ?>
				<?php if($settings['enablePros']): ?>
					<div class="item-row-description item-row-pros" data-match-height="itemPros">
						<?php echo $settings['prosTitle']; ?>
					</div>
				<?php endif; ?>
				<?php if($settings['enableCons']): ?>
					<div class="item-row-description item-row-cons" data-match-height="itemCons">
						<?php echo $settings['consTitle']; ?>
					</div>
				<?php endif; ?>
				<?php if($settings['enableSpec']): ?>
					<div class="item-row-description item-row-spec" data-match-height="itemSpec">
						<?php echo $settings['specTitle']; ?>
					</div>
				<?php endif; ?>
				<?php if(!empty($settings['extraColumns'])): ?>
						<?php foreach($settings['extraColumns'] as $key=>$value):?>
							<div class="item-row-description item-row-extra row-extra<?php echo (int)$key;?>" data-match-height="row-extra<?php echo (int)$key;?>">
								<?php echo esc_attr($value['content']); ?>
							</div>
						<?php endforeach;?>
					<?php endif; ?>
				<?php if($settings['enableCallout']): ?>
					<div class="item-row-description item-row-callout" data-match-height="itemCallout">&nbsp;</div>
				<?php endif; ?>
			</div>
			<div class="comparison-wrapper <?php echo $settings['responsiveView'] ==='slide' ? 'swiper-wrapper' : ''; ?>" style="font-size: <?php echo $settings['contentFont']; ?>px;">
				<?php echo $inner_content; ?>
			</div>
			<button type="button" class="comparison-control-prev">
				<svg width="22" height="22" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 492 492">
					<path d="M198.608,246.104L382.664,62.04c5.068-5.056,7.856-11.816,7.856-19.024c0-7.212-2.788-13.968-7.856-19.032l-16.128-16.12
						C361.476,2.792,354.712,0,347.504,0s-13.964,2.792-19.028,7.864L109.328,227.008c-5.084,5.08-7.868,11.868-7.848,19.084
						c-0.02,7.248,2.76,14.028,7.848,19.112l218.944,218.932c5.064,5.072,11.82,7.864,19.032,7.864c7.208,0,13.964-2.792,19.032-7.864
						l16.124-16.12c10.492-10.492,10.492-27.572,0-38.06L198.608,246.104z"/>
				</svg>
			</button>
			<button type="button" class="comparison-control-next">
				<svg width="22" height="22" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 492.004 492.004">
					<path d="M382.678,226.804L163.73,7.86C158.666,2.792,151.906,0,144.698,0s-13.968,2.792-19.032,7.86l-16.124,16.12
						c-10.492,10.504-10.492,27.576,0,38.064L293.398,245.9l-184.06,184.06c-5.064,5.068-7.86,11.824-7.86,19.028
						c0,7.212,2.796,13.968,7.86,19.04l16.124,16.116c5.068,5.068,11.824,7.86,19.032,7.86s13.968-2.792,19.032-7.86L382.678,265
						c5.076-5.084,7.864-11.872,7.848-19.088C390.542,238.668,387.754,231.884,382.678,226.804z"/>
				</svg>
			</button>
		</div>
	<?php 
		$output = ob_get_clean();
		return $output;
	}

}