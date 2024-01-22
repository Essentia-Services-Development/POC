<?php
namespace Elementor\TemplateLibrary;

use Elementor\Api;
use Elementor\Plugin;
use Elementor\TemplateLibrary\Source_Remote;

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

/**
 * Elementor template library remote source.
 *
 * Elementor template library remote source handler class is responsible for
 * handling remote templates from Elementor.com servers.
 *
 * @since 1.0.0
 */
class WPSM_Remote_Source extends Source_Remote {

    /**
     * Get remote template ID.
     *
     * Retrieve the remote template ID.
     *
     * @since 1.0.0
     * @access public
     *
     * @return string The remote template ID.
     */
    public function get_id() {
        return 'remote';
    }

    /**
     * Get remote templates.
     *
     * Retrieve remote templates from Elementor.com servers.
     *
     * @since 1.0.0
     * @access public
     *
     * @param array $args Optional. Nou used in remote source.
     *
     * @return array Remote templates.
     */
    public function get_items( $args = [] ) {
        // @TODO: cache the result
        $library_data = parent::get_items();
        if ( ! defined( 'ELEMENTOR_PRO_VERSION' ) ) {
            foreach( $library_data as $index => $data ) {
                if ( isset( $data['isPro'] ) && $data['isPro'] ) {
                    unset( $library_data[ $index ] );
                }
            }
        }
        return array_merge( $this->get_local_items(), $library_data );


    }

    /**
     * Get remote template data.
     *
     * Retrieve the data of a single remote template from a custom source.
     *
     * @since 1.5.0
     * @access public
     *
     * @param array  $args    Custom template arguments.
     * @param string $context Optional. The context. Default is `display`.
     *
     * @return array Remote Template data.
     */
    public function get_data( array $args, $context = 'display' ) {
        // @TODO: add security check
        if ( is_numeric( $args['template_id'] ) ) {
            return parent::get_data( $args, $context );
        }

        $template_dir = $this->get_local_directory() . $args['template_id'];
        // $data_file = $template_dir . DIRECTORY_SEPARATOR . 'template_data.php';
        $data_file = $template_dir . DIRECTORY_SEPARATOR . 'template.json';
        if ( ! is_dir( $template_dir ) || ! file_exists( $data_file ) ) {
            return new \WP_Error( 'broke', esc_html__( 'template not found', 'rehub-theme' ) );
        }

        ob_start();
        include_once( $data_file );
        $json_data = ob_get_contents();
        ob_end_clean();

        try {
            $data = json_decode( $json_data, true );
        } catch ( Exception $e ) {
            return new WP_Error( 'broke', esc_html__( 'template file data is invalid', 'rehub-theme' ) );
        }

        if ( ! $data ) {
            return new WP_Error('broke', esc_html__('The data is broken', 'rehub-theme') );
        }

        $data['content'] = $this->replace_elements_ids( $data['content'] );
        $data['content'] = $this->process_export_import_content( $data['content'], 'on_import' );

        $post_id = $args['editor_post_id'];
        $document = Plugin::$instance->documents->get( $post_id );
        if ( $document ) {
            $data['content'] = $document->get_elements_raw_data( $data['content'], true );
        }

        return $data;
    }

    private function get_local_items( $templates = [] ) {
        // Read Local Directory
        $template_directory = $this->get_local_directory();
        $handler = opendir( $template_directory );
        if ( ! $handler ) {
            return $templates;
        }


        while ( false !== ( $directory = readdir( $handler ) ) ) {
            if ( in_array( $directory, ['.', '..'] ) ) {
                continue;
            }

            // Check if we have thumbnail and preview file
            $local_dir = $template_directory . $directory;
            if ( ! is_dir( $local_dir ) ) {
                continue;
            }

            // Make sure we have mandatory files
            $invalid_structure = false;
            foreach ( ['thumbnail.jpg', 'config.php'] as $file ) {
                if ( ! file_exists( $local_dir . DIRECTORY_SEPARATOR . $file ) ) {
                    $invalid_structure = true;
                    break;
                }
            }

            // Skip if invalid structure
            if ( $invalid_structure ) {
                continue;
            }

            $local_dir_url = trailingslashit( $this->get_local_url() . $directory );
            $ext_dir_url = trailingslashit( $this->get_ext_url() );
            $template_data = require( $local_dir . DIRECTORY_SEPARATOR . 'config.php' );
            if ( ! isset( $template_data['id'] ) || ! isset( $template_data['tmpl_created'] ) ) {
                continue;
            }

            $templates[] = $this->prepare_template( $template_data );
        }

        return $templates;
    }

    /**
     * @since 2.2.0
     * @access private
     */
    private function prepare_template( array $template_data ) {
        $favorite_templates = $this->get_user_meta( 'favorites' );
		if(is_array($template_data['tags'])){
			$template_data['tags'] = json_encode($template_data['tags']);
		}
        return [
            'template_id' => $template_data['id'],
            'source' => 'remote',
            // 'source' => 'wpsm_local',
            'type' => $template_data['type'],
            'subtype' => $template_data['subtype'],
            'title' => $template_data['title'],
            'thumbnail' => $template_data['thumbnail'],
            'accessLevel' => 0,
            'date' => $template_data['tmpl_created'],
            'author' => $template_data['author'],
            'tags' => json_decode( $template_data['tags'] ),
            'isPro' => false,
            'popularityIndex' => (int) $template_data['popularity_index'],
            'trendIndex' => (int) $template_data['trend_index'],
            'hasPageSettings' => ( '1' === $template_data['has_page_settings'] ),
            'url' => $template_data['url'],
            'favorite' => ! empty( $favorite_templates[ $template_data['id'] ] ),
        ];
    }

    private function get_local_directory() {
        return apply_filters(
            'wpsm_elementor_template_directory',
            get_template_directory() . '/rehub-elementor/templates/templates/'
        );
    }

    private function get_local_url() {
        return apply_filters(
            'wpsm_elementor_template_directory_url',
            get_template_directory_uri() . '/rehub-elementor/templates/templates/'
        );
    }

    private function get_ext_url() {
        return apply_filters(
            'wpsm_elementor_ext_url',
            '//elementor.wpsoul.com/'
        );
    }    
}

class WPSM_Template_Manager extends Manager{
    public function unregister_source( $id ) {
            if ( ! isset( $this->_registered_sources[ $id ] ) ) {
            return false;
        }

        unset( $this->_registered_sources[ $id ] );

        return true;
    }   
}

/*
public function get_source( $id ) {
    $sources = $this->get_registered_sources();

    if ( ! isset( $sources[ $id ] ) ) {
        return false;
    }

    return $sources[ $id ];
}

public function register_source( $source_class, $args = [] ) {
    if ( ! class_exists( $source_class ) ) {
        return new \WP_Error( 'source_class_name_not_exists' );
    }

    $source_instance = new $source_class( $args );

    if ( ! $source_instance instanceof Source_Base ) {
        return new \WP_Error( 'wrong_instance_source' );
    }

    $source_id = $source_instance->get_id();

    if ( isset( $this->_registered_sources[ $source_id ] ) ) {
        return new \WP_Error( 'source_exists' );
    }

    $this->_registered_sources[ $source_id ] = $source_instance;

    return true;
}*/

add_action( 'init', function () {
    // @TODO: make sure elementor is installed
    Plugin::instance()->templates_manager = new WPSM_Template_Manager(); //we enable removing templates again
    Plugin::instance()->templates_manager->unregister_source( 'remote' );
    Plugin::instance()->templates_manager->register_source( 'Elementor\TemplateLibrary\WPSM_Remote_Source' );
}, 0);
