<?php

class PeepSoShare
{
	/*
	 * $share_links = array(
	 *		<SHARING_SERVICE> => array(
	 *			'icon' => <URL OF THE ICON TO USE>,
	 *			'url'  => <Share URL, need to have --peepso-url--, this will be replaced by the actual URL
	 *						to be shared>
	 *		);
	 * );
	 */
	private $share_links = array();

	private static $_instance = NULL;

	// list of allowed template tags
	public $template_tags = array(
		'show_links',		// display social sharing links
	);

	private function __construct()
	{
	}

	/*
	 * return singleton instance
	 */
	public static function get_instance()
	{
		if (NULL === self::$_instance)
			self::$_instance = new self();
		return (self::$_instance);
	}

	/*
	 * Returns the social sharing links as an array
	 * @return array The sharing links
	 */
	public function get_links( $all = FALSE)
	{
		$this->share_links = array(
            'peepso_my_profile' => array(
                'label'    => 'My profile',
                'icon'     => 'my-profile',
                'url'      => '#',
                'internal' => TRUE,
                'desc'     => 'Share as your post on this community',
                'class'    => 'SOMETHING',
            ),
            'peepso_copy_link' => array(
                'label'    => __('Copy link','peepso-core'),
                'tooltip'  => __('Click to copy', 'peepso-core'),
                'tooltip-after-click' => __('Copied!', 'peepso-core'),
                'icon'     => 'gcis gci-link',
                'url'      => '--peepso-url--',
                'internal' => TRUE,
                'desc'     => __('Copy the post URL to your clipboard','peepso-core'),
                'class'    => 'ps-js-copy-link',
            ),
			'separator_1' => TRUE,
			'facebook' => array(
				'label' => 'Facebook',
				'icon'  => 'gcib gci-facebook-f',
				'url'   => 'https://www.facebook.com/sharer.php?u=--peepso-url--'
			),
			'twitter' => array(
				'label' => 'Twitter',
				'icon'  => 'gcib gci-twitter',
				'url'   => 'https://twitter.com/share?url=--peepso-url--'
			),
			'linkedin' => array(
				'label' => 'LinkedIn',
				'icon'  => 'gcib gci-linkedin',
				'url'   => 'https://www.linkedin.com/shareArticle?mini=true&url=--peepso-url--&source=' . urlencode(get_bloginfo('name'))
			),
			'reddit' => array(
				'label' => 'Reddit',
				'icon'  => 'gcib gci-reddit-alien',
				'url'   => 'https://www.reddit.com/submit?url=--peepso-url--'
			),
			'pinterest' => array(
				'label' => 'Pinterest',
				'icon'  => 'gcib gci-pinterest-p',
				'url'   => 'https://pinterest.com/pin/create/link/?url=--peepso-url--'
			),
            'whatsapp' => array(
                'label' => 'WhatsApp',
                'icon'  => 'gcib gci-whatsapp',
                'url'   => 'https://api.whatsapp.com/send?text=--peepso-url--'

            ),
            'telegram' => array(
                'label' => 'Telegram',
                'icon'  => 'gcib gci-telegram',
                'url'   => 'https://t.me/share/url?url=--peepso-url--'

            ),
		);

		if(!PeepSo::is_dev_mode('new_sharing')) {
            unset($this->share_links['peepso_my_profile']);
            unset($this->share_links['separator_1']);
        }

		$this->share_links = apply_filters('peepso_share_links', $this->share_links);

		if(!$all) {
            foreach ($this->share_links as $key => $link) {

                if(!PeepSo::get_option('activity_social_sharing_provider_'.$key, 1)) {
                    unset($this->share_links[$key]);
                }
            }
        }

		return $this->share_links;
	}

	/*
	 * Template callback for display share links
	 */
	public function show_links()
	{
		echo '<div class="ps-sharebox">', PHP_EOL;
		$links = $this->get_links();
		if(count($links)) {
            foreach ($links as $key => $link) {
                if(is_array($link)) {
                    $class = '';
                    $tooltip = '';
                    $tooltipSuccess = '';
                    $tooltipClass = '';
                    $class .= isset($link['class']) ? " {$link['class']} " : '';
                    $class .= isset($link['internal']) ? " internal " : '';
                    $tooltip .= isset($link['tooltip']) ? "{$link['tooltip']}" : '';
                    $tooltipSuccess .= isset($link['tooltip-after-click']) ? "{$link['tooltip-after-click']}" : '';
                    $tooltipClass .= isset($link['tooltip']) ? " ps-tooltip ps-tooltip--permalink " : '';
                    echo '<a class="ps-sharebox__item '.$class.' '.$tooltipClass.'" data-tooltip="'.$tooltip.'" data-tooltip-initial="'.$tooltip.'" data-tooltip-success="'.$tooltipSuccess.'" href="', $link['url'], '" target="_blank">', PHP_EOL;
                    //echo '<span class="ps-sharebox__icon ps-icon--social ps-icon--social-', $link['icon'], '">', $link['label'], '</span>', PHP_EOL;
                    echo '<span class="ps-sharebox__icon ps-icon--social" ><i class="', $link['icon'], '"></i></span> <span class="ps-sharebox__title">' . $link['label'] . '</span>' . PHP_EOL;
                } elseif(stristr($key, 'separator')) {
                    // nothing for now?
                }
            }
        } else {
		    echo __('Sorry, it looks like the no social sharing platforms are enabled', 'peepso-core');
        }
		echo '</div>', PHP_EOL;
	}
}

// EOF
