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
$rand = rand( 0, 100000 );
?>

<?php if ( $title ): ?>
    <h3><?php echo esc_html( $title ); ?></h3>
<?php endif; ?>

<div class="cegg-flickr-gallery">
	<?php foreach ( $items as $item ): ?>
        <a href="<?php echo esc_url($item['img']); ?>" rel="gallery<?php echo esc_attr($rand); ?>">
            <img src="<?php echo esc_url_raw($item['img']); ?>" alt="<?php echo esc_attr( $item['title'] ); ?>"
                 class="img-thumbnail"/>
        </a>
	<?php endforeach; ?>
</div>
<script>
    jQuery(document).ready(function () {

        jQuery('.cegg-flickr-gallery').justifiedGallery({
            rowHeight: 160,
            lastRow: 'nojustify',
            margins: 1,
        }).on('jg.complete', function () {
            jQuery(this).find('a').colorbox({
                maxWidth: '80%',
                maxHeight: '80%',
                opacity: 0.8,
                transition: 'elastic',
                current: ''
            });
        });
    });
</script>
