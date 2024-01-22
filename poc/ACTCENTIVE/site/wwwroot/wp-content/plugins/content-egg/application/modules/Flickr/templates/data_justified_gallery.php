<?php
defined( '\ABSPATH' ) || exit;
/*
 * Name: Gallery
 * 
 * @link: http://miromannino.github.io/Justified-Gallery/
 */
__( 'Gallery', 'content-egg-tpl' );
?>

<?php wp_enqueue_style( 'egg-justified-gallery', ContentEgg\PLUGIN_RES . '/justified_gallery/justifiedGallery.min.css' ); ?>
<?php wp_enqueue_script( 'egg-justified-gallery', ContentEgg\PLUGIN_RES . '/justified_gallery/jquery.justifiedGallery.min.js' ); ?>
<?php wp_enqueue_style( 'egg-color-box', ContentEgg\PLUGIN_RES . '/colorbox/colorbox.css' ); ?>
<?php wp_enqueue_script( 'egg-color-box', ContentEgg\PLUGIN_RES . '/colorbox/jquery.colorbox-min.js' ); ?>

<?php
// Helper functions
if ( ! function_exists( 'cegg_IsFlickrStorage' ) ) {

	function cegg_IsFlickrStorage( $items ) {
		foreach ( $items as $item ) {
			if ( ! empty( $item['img_file'] ) ) {
				return false;
			}
		}

		return true;
	}

}
if ( ! function_exists( 'cegg_normalizeFlickr' ) ) {

	function cegg_normalizeFlickr( $img_url ) {
		return preg_replace( '/_\w{1}\.jpg$/', '_m.jpg', $img_url );
	}

}
if ( ! function_exists( 'cegg_getBigFlickr' ) ) {

	function cegg_getBigFlickr( $img_url ) {
		return preg_replace( '/_\w{1}\.jpg$/', '_z.jpg', $img_url );
	}

}
$isFlickrStorage = cegg_IsFlickrStorage( $items );
$rand            = rand( 0, 100000 );
?>

<?php if ( $title ): ?>
    <h3><?php echo esc_html( $title ); ?></h3>
<?php endif; ?>

<div class="cegg-flickr-gallery">
	<?php foreach ( $items as $item ): ?>
		<?php
		$alt = $item['extra']['tags'] ? ( $item['extra']['tags'] ) : esc_attr( $item['keyword'] );
		$alt = ContentEgg\application\helpers\TextHelper::truncate( $alt, 60 );
		$alt .= ' (' . sprintf( __( 'Photo: %s on Flickr', 'content-egg-tpl' ), $item['extra']['author'] ) . ')';
		if ( $isFlickrStorage ) {
			$img     = cegg_normalizeFlickr( $item['img'] );
			$img_big = cegg_getBigFlickr( $item['img'] );
		} else {
			$img = $img_big = $item['img'];
		}
		?>

        <a href="<?php echo $img_big; ?>" rel="gallery<?php echo esc_attr($rand); ?>">
            <img src="<?php echo $img; ?>" alt="<?php echo esc_attr( $alt ); ?>" class="img-thumbnail"/>
        </a>
	<?php endforeach; ?>
</div>
<script>
    jQuery(document).ready(function () {

            jQuery('.cegg-flickr-gallery').justifiedGallery({
                rowHeight: 160,
                lastRow: 'nojustify',
                margins: 1,
				<?php if ($isFlickrStorage): ?>
                sizeRangeSuffixes: {
                    'lt100': '_t',
                    'lt150': '_q',
                    'lt240': '_m',
                    'lt320': '_n',
                    'lt500': '',
                    'lt640': '_z',
                    'lt1024': '_b'
                }
				<?php endif; ?>

            }).on('jg.complete', function () {
                jQuery(this).find('a').colorbox({
                    maxWidth: '80%',
                    maxHeight: '80%',
                    opacity: 0.8,
                    transition: 'elastic',
                    current: ''
                });
            });
        }
    );
</script>