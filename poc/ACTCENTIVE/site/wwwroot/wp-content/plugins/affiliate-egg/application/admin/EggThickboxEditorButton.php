<?php

namespace Keywordrush\AffiliateEgg;

defined('\ABSPATH') || exit;

/**
 * EggThickboxEditorButton class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link http://www.keywordrush.com/
 * @copyright Copyright &copy; 2014 keywordrush.com
 */
class EggThickboxEditorButton {

    public function __construct()
    {
        //add button to editor
        $pages_with_editor_button = array('post.php', 'post-new.php');
        foreach ($pages_with_editor_button as $editor_page)
        {
            \add_action("load-{$editor_page}", array($this, 'add_editor_buttons'));
        }
        //admin_post_(action) hook
        \add_action('admin_post_eggs_thickbox', array($this, 'eggs_thickbox'));

        \add_action('admin_enqueue_scripts', array($this, 'load_scripts'));
    }

    function load_scripts()
    {
        \wp_enqueue_style('custom-mce-style', PLUGIN_RES . '/css/custom-mce-style.css');
    }

    public function add_editor_buttons()
    {

        \add_thickbox(); // usually already loaded by media upload functions
        \wp_enqueue_script("affegg-quicktags-button", PLUGIN_RES . '/js/quicktags-button.js', array('quicktags', 'media-upload'));
        \wp_localize_script("affegg-quicktags-button", "affegg_editor_button", array(
            'caption' => 'Affiliate Egg',
            'title' => __('Add storefronts', 'affegg'),
            'thickbox_title' => __('Add storefronts', 'affegg'),
            'thickbox_url' => get_admin_url(get_current_blog_id()) . 'admin-post.php?action=eggs_thickbox',
                )
        );

        // TinyMCE integration
        //\add_filter('mce_external_plugins', array($this, 'add_tinymce_plugin'));
        //\add_filter('mce_buttons', array($this, 'add_tinymce_button'));
        //\add_action('admin_print_styles', array($this, 'add_affegg_hidpi_css'), 21);
    }

    public function eggs_thickbox()
    {
        set_current_screen('eggs_thickbox');
        AffiliateEggAdmin::getInstance()->render('eggs_thickbox', array(
            'table' => new EggThickboxTable(EggModel::model(), array('owner_check' => true, 'numeric_search' => true))));
    }

    public function add_tinymce_button(array $buttons)
    {
        $buttons[] = 'affegg_insert_table';
        return $buttons;
    }

    public function add_tinymce_plugin(array $plugins)
    {
        $plugins['affegg_tinymce'] = PLUGIN_RES . '/js/tinymce-button.js';
        return $plugins;
    }

    public function add_affegg_hidpi_css()
    {
        echo '<style type="text/css">@media print,(-o-min-device-pixel-ratio:5/4),(-webkit-min-device-pixel-ratio:1.25),(min-resolution:120dpi){';
        echo '#content_affegg_insert_table img{display:none}';
        echo '}</style>' . "\n";
    }

}
