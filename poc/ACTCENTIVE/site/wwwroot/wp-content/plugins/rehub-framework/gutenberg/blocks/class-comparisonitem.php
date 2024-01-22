<?php

namespace Rehub\Gutenberg\Blocks;

defined( 'ABSPATH' ) OR exit;

class ComparisonItem extends Basic {
	protected $name = 'comparison-item';

	protected $attributes = array(
		'productBadge' => array( 'type' => 'string', 'default' => '' ),
		'badgeColor' => array( 'type' => 'string', 'default' => '#7635f3' ),
		'numberValue' => array( 'type' => 'string', 'default' => '' ),
		'numberColor' => array( 'type' => 'string', 'default' => '#390' ),
		'productImage' => array( 'type' => 'object', 'default' => array() ),
		'productTitle' => array( 'type' => 'string', 'default' => '' ),
		'productSubtitle' => array( 'type' => 'string', 'default' => '' ),
		'starRating' => array( 'type' => 'number', 'default' => 5 ),
		'bottomText' => array( 'type' => 'string', 'default' => '' ),
		'prosText' => array( 'type' => 'string', 'default' => '' ),
		'consText' => array( 'type' => 'string', 'default' => '' ),
		'specText' => array( 'type' => 'string', 'default' => '' ),
		'buttonUrl' => array( 'type' => 'string', 'default' => '' ),
		'buttonText' => array( 'type' => 'string', 'default' => 'Check Prices' ),
		'buttonRel' => array( 'type' => 'boolean', 'default' => false ),
		'buttonTarget' => array( 'type' => 'boolean', 'default' => false ),
		'buttonColor' => array( 'type' => 'string', 'default' => '#7635f3' ),
		'listTitle' => array( 'type' => 'string', 'default' => 'Check Latest Prices' ),
		'listItems' => array( 'type' => 'array', 'default' => array() ),
		'responsiveView' => array( 'type' => 'string', 'default' => 'stacked' ),
        // State variables
        'enableBadge' => array( 'type' => 'boolean', 'default' => false ),
		'urlBadges' => array('type'    => 'boolean','default' => false ),
        'enableBadges' => array('type'    => 'boolean','default' => false ),
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
		'extraColumns'=> array( 'type' => 'array', 'default' => array()),
		'extraFirstColumns'=> array( 'type' => 'array', 'default' => array())
	);

	protected function render( $settings = array(), $inner_content = '' ) {
		ob_start();
	?>
		<div class="comparison-item <?php echo $settings['responsiveView'] ==='slide' ? 'swiper-slide' : ''; ?>">
			<div class="item-header" data-match-height="itemHeader">
				<?php if($settings['enableBadge'] && $settings['enableBadges']): ?>
					<?php if($settings['urlBadges'] && $settings['enableButton']):?>
						<a 
							class="item-badge re_track_btn" 
							style="background-color: <?php echo esc_attr($settings['badgeColor']); ?>;"
							href="<?php echo esc_url($settings['buttonUrl']) ?>"
							rel="<?php echo (!empty($settings['buttonRel'])) ? 'nofollow sponsored' : ''; ?>"
							target="<?php echo (!empty($settings['buttonTarget'])) ? '_blank' : ''; ?>"
						>
							<?php echo wp_kses_post($settings['productBadge']) ?>
						</a>
					<?php else:?>
						<div class="item-badge" style="background-color: <?php echo esc_attr($settings['badgeColor']); ?>;"><?php echo wp_kses_post($settings['productBadge']) ?></div>
					<?php endif;?>
				<?php endif; ?>

				<?php if($settings['numberValue'] && $settings['enableNumbers']): ?>
					<div class="item-number" style="background-color: <?php echo esc_attr($settings['numberColor']); ?>;"><?php echo esc_attr($settings['numberValue']) ?></div>
				<?php endif; ?>
				<?php if($settings['enableImage'] && !empty($settings['productImage'])): ?>
					<div class="product-image">
						<div class="image">
						<?php 
								if(!empty($settings['productImage']['id'])){
									echo wp_get_attachment_image($settings['productImage']['id'], 'full', false);
								}
								else if(!empty($settings['productImage']['url'])){
									echo '<img width="160" height="160" src="'.esc_url($settings['productImage']['url']).'" class="attachment-full size-full" alt="" loading="lazy">';
								}
							?>
						</div>
					</div>
				<?php endif; ?>
				<?php if($settings['enableTitle'] && !empty($settings['productTitle'])): ?>
					<<?php echo esc_attr($settings['titleTag']); ?> class="item-title" style="font-size: <?php echo esc_attr($settings['titleFont']); ?>px;"><?php echo wp_kses_post($settings['productTitle']) ?></<?php echo esc_attr($settings['titleTag']); ?>>
				<?php endif; ?>
				<?php if($settings['enableSubtitle'] && !empty($settings['productSubtitle'])): ?>
					<div class="item-subtitle"><?php echo wp_kses_post($settings['productSubtitle']) ?></div>
				<?php endif; ?>
				<?php if($settings['enableStars']): ?>
					<div class="item-rating">
                        <div class="item-stars-rating">
							<?php for( $i=0; $i < round($settings['starRating'], 0, PHP_ROUND_HALF_DOWN); $i++ ): ?>
								<svg xmlns="http://www.w3.org/2000/svg" x="0px" y="0px" width="24" height="24" viewBox="33 -90 360 360">
									<polygon stroke="#F6A123" stroke-width="20" stroke-linecap="square" stroke-linejoin="miter" fill="transparent" points="281.1,129.8 364,55.7 255.5,46.8 214,-59 172.5,46.8 64,55.4 146.8,129.7 121.1,241 212.9,181.1 213.9,181 306.5,241 "></polygon>
									<polygon fill="#F6A123" points="281.1,129.8 364,55.7 255.5,46.8 214,-59 172.5,46.8 64,55.4 146.8,129.7 121.1,241 213.9,181.1 213.9,181 306.5,241 "></polygon>
								</svg>
							<?php endfor; ?>
							<?php if( is_float($settings['starRating']) ): ?>
								<svg xmlns="http://www.w3.org/2000/svg" x="0px" y="0px" width="24" height="24" viewBox="33 -90 360 360">
									<polygon stroke="#F6A123" stroke-width="20" stroke-linecap="square" stroke-linejoin="miter" fill="transparent" points="281.1,129.8 364,55.7 255.5,46.8 214,-59 172.5,46.8 64,55.4 146.8,129.7 121.1,241 212.9,181.1 213.9,181 306.5,241 "></polygon>
									<polygon fill="#F6A123" points="281.1,129.8 364,55.7 255.5,46.8 214,-59 172.5,46.8 64,55.4 146.8,129.7 121.1,241 213.9,181.1 213.9,181 306.5,241 "></polygon>
									<polygon fill="#fff" stroke="#F6A123" stroke-width="10" stroke-linecap="square" stroke-linejoin="miter" points="364,55.7 255.5,46.8 214,-59 213.9,181 306.5,241 281.1,129.8 "></polygon>
								</svg>
							<?php endif; ?>
							<?php for( $i=0; $i < (5 - round($settings['starRating'])); $i++ ): ?>
								<svg xmlns="http://www.w3.org/2000/svg" x="0px" y="0px" width="24" height="24" viewBox="33 -90 360 360">
									<polygon stroke="#F6A123" stroke-width="20" stroke-linecap="square" stroke-linejoin="miter" fill="transparent" points="281.1,129.8 364,55.7 255.5,46.8 214,-59 172.5,46.8 64,55.4 146.8,129.7 121.1,241 212.9,181.1 213.9,181 306.5,241 "></polygon>
								</svg>
							<?php endfor; ?>
						</div>
					</div>
				<?php endif; ?>
				<?php if($settings['enableList']): ?>
					<div class="item-list">
						<?php if($settings['enableListTitle']): ?>
							<div class="item-list-title"><?php echo esc_attr($settings['listTitle']) ?></div>
							<ul class="item-list-links">
								<?php foreach($settings['listItems'] as $item): ?>
									<li><?php echo ''.$item['key']; ?></li>
								<?php endforeach; ?>
							</ul>
						<?php endif; ?>
					</div>
				<?php endif; ?>
				<?php if($settings['enableButton']): ?>
					<a 
						href="<?php echo esc_url($settings['buttonUrl']) ?>"
						rel="<?php echo (!empty($settings['buttonRel'])) ? 'nofollow' : ''; ?>"
						target="<?php echo (!empty($settings['buttonTarget'])) ? '_blank' : ''; ?>"
						style="background-color: <?php echo esc_attr($settings['buttonColor']) ?>" 
						class="rehub-item-btn">
						<?php echo ''.$settings['buttonText'] ?>
					</a>
				<?php endif; ?>
			</div>
			<?php if($settings['enableBottom']): ?>
				<div class="item-row-description item-row-bottomline" data-match-height="itemBottomline">
					<?php if($settings['responsiveView'] !== 'overflow'): ?>
						<div class="item-row-title"><?php echo ''.$settings['bottomTitle'] ?></div>
					<?php endif; ?>
					<?php echo ''.$settings['bottomText'] ?>
				</div>
			<?php endif; ?>
			<?php if($settings['enablePros']): ?>
				<div class="item-row-description item-row-pros" data-match-height="itemPros">
					<?php if($settings['responsiveView'] !== 'overflow'): ?>
						<div class="item-row-title"><?php echo ''.$settings['prosTitle'] ?></div>
					<?php endif; ?>
					<?php echo ''.$settings['prosText'] ?>
				</div>
			<?php endif; ?>
			<?php if($settings['enableCons']): ?>
				<div class="item-row-description item-row-cons" data-match-height="itemCons">
					<?php if($settings['responsiveView'] !== 'overflow'): ?>
						<div class="item-row-title"><?php echo ''.$settings['consTitle'] ?></div>
					<?php endif; ?>
					<?php echo ''.$settings['consText'] ?>
				</div>
			<?php endif; ?>
			<?php if($settings['enableSpec']): ?>
				<div class="item-row-description item-row-spec" data-match-height="itemSpec">
					<?php if($settings['responsiveView'] !== 'overflow'): ?>
						<div class="item-row-title"><?php echo $settings['specTitle'] ?></div>
					<?php endif; ?>
					<?php echo ''.$settings['specText'] ?>
				</div>
			<?php endif; ?>
			<?php if(!empty($settings['extraColumns'])): ?>
				<?php foreach($settings['extraColumns'] as $key=>$value):?>
					<div class="item-row-description item-row-extra row-extra<?php echo (int)$key;?>" data-match-height="row-extra<?php echo (int)$key;?>">
						<?php if($settings['responsiveView'] !== 'overflow' && !empty($settings['extraFirstColumns'])): ?>
							<div class="item-row-title"><?php echo esc_attr($settings['extraFirstColumns'][$key]['content']) ?></div>
						<?php endif; ?>
						<?php echo wp_kses_post($value['content']); ?>
					</div>
				<?php endforeach;?>
			<?php endif; ?>
			<?php if($settings['enableCallout']): ?>
				<div class="item-row-description item-row-callout" data-match-height="itemCallout">
				<a 
						href="<?php echo esc_url($settings['buttonUrl']) ?>"
						rel="<?php echo (!empty($settings['buttonRel'])) ? 'nofollow' : ''; ?>"
						target="<?php echo (!empty($settings['buttonTarget'])) ? '_blank' : ''; ?>"
						style="background-color: <?php echo esc_attr($settings['buttonColor']) ?>" 
						class="rehub-item-btn">
						<?php echo ''.$settings['buttonText'] ?>
					</a>
				</div>
			<?php endif; ?>
		</div>
	<?php 
		$output = ob_get_clean();
		return $output;
	}
}