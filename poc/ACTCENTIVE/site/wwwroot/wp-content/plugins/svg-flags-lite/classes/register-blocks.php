<?php

namespace WPGO_Plugins\SVG_Flags;

/*
 *    Register blocks
 */
class Register_Blocks
{
    protected  $module_roots ;
    /* Main class constructor. */
    public function __construct( $module_roots )
    {
        $this->module_roots = $module_roots;
        add_filter(
            'block_categories',
            array( &$this, 'add_block_category' ),
            10,
            2
        );
        add_action( 'plugins_loaded', array( &$this, 'register_dynamic_blocks' ) );
    }
    
    /**
     * Add custom block category.
     */
    public function add_block_category( $categories, $post )
    {
        return array_merge( $categories, array( array(
            'slug'  => 'svg-flags',
            'title' => __( 'SVG Flags', 'svg-flags' ),
        ) ) );
    }
    
    /**
     * Register the dynamic blocks.
     *
     * @since 2.1.0
     *
     * @return void
     */
    public function register_dynamic_blocks()
    {
        // svg-flag block atts.
        $svg_flag_attr = array(
            'flag'          => array(
            'type'    => 'string',
            'default' => '{"value":"GB","label":"United Kingdom"}',
        ),
            'size'          => array(
            'type'    => 'string',
            'default' => '5',
        ),
            'size_unit'     => array(
            'type'    => 'string',
            'default' => 'em',
        ),
            'square'        => array(
            'type'    => 'boolean',
            'default' => false,
        ),
            'caption'       => array(
            'type'    => 'boolean',
            'default' => false,
        ),
            'inline'        => array(
            'type'    => 'boolean',
            'default' => false,
        ),
            'inline_valign' => array(
            'type'    => 'string',
            'default' => 'middle',
        ),
            'random'        => array(
            'type'    => 'boolean',
            'default' => false,
        ),
        );
        // Register the blocks.
        register_block_type( 'svg-flags/svg-flag', array(
            'render_callback' => array( SVG_Flag_Shortcode::get_instance(), 'render_svg_flag_block' ),
            'attributes'      => $svg_flag_attr,
        ) );
        // svg-flag-image block atts.
        $svg_flag_image_attr = array(
            'flag'          => array(
            'type'    => 'string',
            'default' => '{"value":"GB","label":"United Kingdom"}',
        ),
            'size'          => array(
            'type'    => 'string',
            'default' => '5',
        ),
            'size_unit'     => array(
            'type'    => 'string',
            'default' => 'em',
        ),
            'square'        => array(
            'type'    => 'boolean',
            'default' => false,
        ),
            'caption'       => array(
            'type'    => 'boolean',
            'default' => false,
        ),
            'inline'        => array(
            'type'    => 'boolean',
            'default' => false,
        ),
            'inline_valign' => array(
            'type'    => 'string',
            'default' => 'middle',
        ),
            'random'        => array(
            'type'    => 'boolean',
            'default' => false,
        ),
        );
        register_block_type( 'svg-flags/svg-flag-image', array(
            'render_callback' => array( SVG_Flag_Image_Shortcode::get_instance(), 'render_svg_flag_image_block' ),
            'attributes'      => $svg_flag_image_attr,
        ) );
        // svg-flag-grid block atts.
        $svg_flag_grid_attr = array(
            'flag'          => array(
            'type'    => 'string',
            'default' => '{"value":"GB","label":"United Kingdom"}',
        ),
            'size'          => array(
            'type'    => 'string',
            'default' => '5',
        ),
            'size_unit'     => array(
            'type'    => 'string',
            'default' => 'em',
        ),
            'square'        => array(
            'type'    => 'boolean',
            'default' => false,
        ),
            'caption'       => array(
            'type'    => 'boolean',
            'default' => false,
        ),
            'inline'        => array(
            'type'    => 'boolean',
            'default' => false,
        ),
            'inline_valign' => array(
            'type'    => 'string',
            'default' => 'middle',
        ),
            'random'        => array(
            'type'    => 'boolean',
            'default' => false,
        ),
        );
        return;
        register_block_type( 'svg-flags/svg-flag-grid', array(
            'render_callback' => array( SVG_Flag_Grid_Shortcode::get_instance(), 'render_svg_flag_grid_block' ),
            'attributes'      => $svg_flag_grid_attr,
        ) );
    }

}
/* End class definition */