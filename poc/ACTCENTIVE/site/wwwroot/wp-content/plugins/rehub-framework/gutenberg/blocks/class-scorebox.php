<?php


namespace Rehub\Gutenberg\Blocks;
defined('ABSPATH') OR exit;


class Scorebox{

	public function __construct(){
		add_action('init', array( $this, 'init_handler' ));
	}

	public function init_handler(){
		register_block_type(__DIR__ . '/scorebox', array(
			'attributes'      => $this->attributes,
			'render_callback' => array( $this, 'render_block' ),
		));
	}

	public $attributes = array(
		'title'       => array(
			'type'    => 'string',
			'default' => '',
		),
		'label' => array(
			'type'    => 'string',
			'default' => '',
		),
		'labelicon' => array(
			'type'    => 'boolean',
			'default' => false,
		),
		'disablepros' => array(
			'type'    => 'boolean',
			'default' => false,
		),
		'schemaenable' => array(
			'type'    => 'boolean',
			'default' => false,
		),
		'coverenable' => array(
			'type'    => 'boolean',
			'default' => false,
		),
		'enableinner' => array(
			'type'    => 'boolean',
			'default' => false,
		),
		'innerbottom' => array(
			'type'    => 'boolean',
			'default' => false,
		),
		'score'       => array(
			'type'    => 'number',
			'default' => 7
		),
		'boxradius'       => array(
			'type'    => 'number',
			'default' => 0
		),
		'scorebgColor'   => array(
			'type'    => 'string',
			'default' => '#ffffff',
		),
		'scoretextColor'   => array(
			'type'    => 'string',
			'default' => '#111111',
		),
		'labelColor'   => array(
			'type'    => 'string',
			'default' => '#cd0000',
		),
		'scorecircleColor'   => array(
			'type'    => 'string',
			'default' => '#1CC600',
		),
		'prosTitle'   => array(
			'type'    => 'string',
			'default' => 'POSITIVES',
		),
		'buttons'   => array(
			'type'    => 'array',
			'default' => array(
				array(
				'url'=> '',
				'btntitle'=> 'Check lowest prices',
				'newTab' => '',
				'noFollow' => '',
				'textcolor' => '#ffffff',
				'bgcolor' => '#cc0000',
				'bggradient' => '',
				'radius'=> 3
				)
				
			),
		),
		'positives'   => array(
			'type'    => 'array',
			'default' => array(
				array(
				'title'=> 'Positive Item 1',
				),
				array(
					'title'=> 'Positive Item 2',
					)
				
			),
		),
		'consTitle'   => array(
			'type'    => 'string',
			'default' => 'NEGATIVES',
		),
		'negatives'   => array(
			'type'    => 'array',
			'default' => array(
				array(
				'title'=> 'Negative Item 1',
				),
				array(
					'title'=> 'Negative Item 2',
					)
				
			),
		),
		'bgColor'   => array(
			'type'    => 'string',
			'default' => '',
		),
		'textColor'   => array(
			'type'    => 'string',
			'default' => '',
		),
		'prosColor'   => array(
			'type'    => 'string',
			'default' => '',
		),
		'consColor'   => array(
			'type'    => 'string',
			'default' => '',
		),
		'prosiconColor'   => array(
			'type'    => 'string',
			'default' => '',
		),
		'consiconColor'   => array(
			'type'    => 'string',
			'default' => '',
		),
		'thumbnail'        => array(
			'type'    => 'object',
			'default' => array(
				'id'     => '',
				'url'    => '',
				'width'  => '',
				'height' => ''
			),
		),
		'schemafields'  => array(
			'type'    => 'object',
			'default' => array(
				'mpn'     => '12345',
				'sku'     => '999GC',
				'count'      => 5,
				'currency'   => 'USD',
				'price'   => '',
				'brand' => 'Brand'
			),
		),
	);

	public function render_block($settings = array(), $inner_content=''){
		extract($settings);
		$scorecalculate = 440 - (440 * $score * 10) / 100;

		$schemarender = ($schemaenable) ? ' itemtype="http://schema.org/Product" itemscope' : '';
		$schemaoffer = ($schemaenable) ? ' itemprop="offers" itemtype="http://schema.org/Offer" itemscope' : '';
		$schemarating = ($schemaenable) ? ' itemprop="aggregateRating" itemtype="http://schema.org/AggregateRating" itemscope' : '';
		$schemaname = ($schemaenable) ? ' itemprop="name"' : '';
		$schemadescription = ($schemaenable) ? ' itemprop="description"' : '';
		$schemaurl = ($schemaenable) ? ' itemprop="url"' : '';
		$fullclass = ($coverenable) ? ' imagefullcover' : '';

		$out = '<div class="rh-scorebox"'.$schemarender.'>';
			if($schemaenable){
				$out .= '<meta itemprop="mpn" content="'.esc_attr($schemafields['mpn']).'" />';
				$out .= '<meta itemprop="sku" content="'.esc_attr($schemafields['sku']).'" />';
				$out .= '<meta itemprop="image" content="'.esc_url($thumbnail['url']).'" />';
				$out .='<div itemprop="brand" itemtype="http://schema.org/Brand" itemscope>
				<meta itemprop="name" content="'.esc_attr($schemafields['brand']).'" />
				</div>';
			}
			$out .= '<div class="rh-scorebox__left">';
				$out .='<div class="rh-scorebox__wrap" style="background-color: '.esc_attr($bgColor).'; border-radius:'.(int)$boxradius.'px; overflow:hidden">';
					$out .= '<div class="rh-scorebox__image'.$fullclass.'" style="max-height:240px">';
						if(!empty($thumbnail['id'])){
							$out .= wp_get_attachment_image($thumbnail['id'], 'full', false);
						}
						else if(!empty($thumbnail['url'])){
							$out .= '<img src="'.esc_url($thumbnail['url']).'" class="attachment-full size-full" alt="" loading="lazy">';
						}
					$out .='</div>';
					$out .= '<div class="rh-scorebox__cont">';
						$out .= '<div class="rh-scorebox__score" style="background-color: '.esc_attr($scorebgColor).'; color: '.esc_attr($scoretextColor).'"'.$schemarating.'>';
							if($schemaenable){
								$scoreuser = $score/2;
								$out .='<meta itemprop="reviewCount" content="'.(int)$schemafields["count"].'" />';
								$out .='<meta itemprop="ratingValue" content="'.(float)$scoreuser.'" />';
							}
							$out .='<svg viewBox="0 0 154 154" style="transform: rotate(270deg); width: 80px; height: 80px; position: absolute">
								<circle cx="70" cy="70" r="70" style="stroke: #ffffff7d; stroke-dashoffset: '.$scorecalculate.'; stroke-width: 14px; transform: translate(7px, 7px); fill: none">
								</circle>
								<circle cx="70" cy="70" r="70" style="stroke-dasharray: 440px; stroke: '.esc_attr($scorecircleColor).'; stroke-dashoffset: '.(float)$scorecalculate.'; stroke-width: 14px; transform: translate(7px, 7px); fill: none; stroke-linecap: round"></circle>
							</svg>
							<div class="rh-scorebox__number">
							'.(float)$score.'
							</div>';
						$out .='</div>';
						$out .= '<div class="rh-scorebox__label" style="color: '.esc_attr($labelColor).'; fill: '.esc_attr($labelColor).'">';
							if($labelicon){
								$out .= '<i class="rhicon rhi-star"></i>';
							}
							$out .='<span>'.wp_kses_post($label).'</span>';
						$out .='</div>';
						$out .='<div class="rh-scorebox__title" style="color: '.esc_attr($textColor).'">';
							$out .='<span'.$schemaname.'>'.wp_kses_post($title).'</span>';
						$out .='</div>';
						$out .='<div class="rh-scorebox__buttons"'.$schemaoffer.'>';
						if($schemaenable){
							$out .='<meta itemprop="availability" content="https://schema.org/InStock" />';
							$out .='<meta itemprop="priceCurrency" content="'.esc_attr($schemafields["currency"]).'" />';
							$out .='<meta itemprop="itemCondition" content="https://schema.org/NewCondition" />';
							$out .='<meta itemprop="price" content="'.(float)$schemafields["price"].'" />';
						}

						foreach ($buttons as $index=>$button){
							$bgcolor = (!empty($button['bgcolor'])) ? $button['bgcolor'] : '';
							$bggradient = (!empty($button['bggradient'])) ? $button['bggradient'] : '';
							$radius = (!empty($button['radius'])) ? $button['radius'] : '';
							$textcolor = (!empty($button['textcolor'])) ? $button['textcolor'] : '';
							$urltarget = (!empty($button['newTab'])) ? ' target="_blank"' : '';
							$urlrel = (!empty($button['noFollow'])) ? ' rel="nofollow sponsored"' : '';
							$btntitle = (!empty($button['btntitle'])) ? $button['btntitle'] : '';
							$url = (!empty($button['url'])) ? $button['url'] : '';
							$urllink = apply_filters('gutencon_url_filter', $url);
							$urllink = apply_filters('rh_post_offer_url_filter', $urllink);
							$urlschema = ($index==0 && $schemaenable) ? $schemaurl : '';
							
							$out .='<a class="re_track_btn rh-scorebox__button" style="background-color: '.esc_attr($bgcolor).'; color: '.esc_attr($textcolor).'; background-image: '.esc_attr($bggradient).'; border-radius: '.(int)$radius.'px" href="'.esc_url($urllink).'"'.$urltarget.$urlrel.$urlschema.'>';
								$out .='<span>'.wp_kses_post($btntitle).'</span>';
							$out .='</a>';
						}
						$out .='</div>';
					$out .='</div>';
				$out .='</div>';
			$out .='</div>';
			$out .='<div class="rh-scorebox__right">';
				if($enableinner && !$innerbottom){
					$out .='<div class="rh-scorebox__inner">';
						$out .='<div'.$schemadescription.'>'.wp_kses_post($inner_content).'</div>';
					$out .='</div>';
				}
				if(!$disablepros){
					$out .='<div class="rh-scorebox__pros">';
						$out .='<div class="rh-scorebox__criterias-title rh-scorebox__criterias-title-pros" style="color:'.esc_attr($prosColor).'">'.wp_kses_post($prosTitle).'</div>';
						$out .='<ul class="rh-scorebox__list rh-scorebox__list-pros">';
							foreach($positives as $positive){
								$prostitle = (!empty($positive['title'])) ? $positive['title'] : '';
								$out .='<li class="rh-scorebox__list-item">';
									$out .='<i class="rhicon rhi-thumbs-up" style="color: '.esc_attr($prosiconColor).'"></i>';
									$out .='<span>'.wp_kses_post($prostitle).'</span>';
								$out .='</li>';

							}
						$out .='</ul>';
					$out .='</div>';
					$out .='<div class="rh-scorebox__cons">';
						$out .='<div class="rh-scorebox__criterias-title rh-scorebox__criterias-title-cons" style="color:'.esc_attr($consColor).'">'.wp_kses_post($consTitle).'</div>';
						$out .='<ul class="rh-scorebox__list rh-scorebox__list-cons">';
							foreach($negatives as $negative){
								$constitle = (!empty($negative['title'])) ? $negative['title'] : '';
								$out .='<li class="rh-scorebox__list-item">';
									$out .='<i class="rhicon rhi-thumbs-down" style="color: '.esc_attr($consiconColor).'"></i>';
									$out .='<span>'.wp_kses_post($constitle).'</span>';
								$out .='</li>';
							}
						$out .='</ul>';
					$out .='</div>';
				}
				if($enableinner && $innerbottom){
					$out .='<div class="rh-scorebox__inner">';
						$out .='<div'.$schemadescription.'>'.wp_kses_post($inner_content).'</div>';
					$out .='</div>';
				}
			$out .='</div>';
		$out .='</div>';
		return $out;
	}
}