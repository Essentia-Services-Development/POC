<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly ?>
<?php
//////////////////////////////////////////////////////////////////
// Rehub Log
//////////////////////////////////////////////////////////////////
if(!function_exists('rh_loger')){
function rh_loger( $value, $variable = '' ) {
    if ( true === WP_DEBUG ) {
        if ( is_array( $value ) || is_object( $value ) ) {
            error_log( $variable .' = '. print_r( $value, true ) );
        } else {
            error_log( $variable .' = '. $value );
        }
    }
}
}

if(!function_exists('rh_check_empty_index')){
function rh_check_empty_index( $array, $value ) {
    $return = (!empty($array[$value])) ? $array[$value] : '';
    return $return;
}
}

//////////////////////////////////////////////////////////////////
// Video Output
//////////////////////////////////////////////////////////////////
if (!function_exists('woo_custom_video_output')){
function woo_custom_video_output($args) {
  $defaults = array(
    'class' => 'mb10 rh_videothumb_link mobileblockdisplay',
    'rel' => 'wooyoutube',
    'wrapper' => 1, 
    'title' => 1,
    'onlyone' => '',
    'exceptfirst' => '',
    'fullsize' => '',
    'id' => ''       
  );
  $args = wp_parse_args( $args, $defaults );
  extract( $args, EXTR_SKIP );  
  global $post;
  if($post->post_type == 'product'){
    $post_image_videos = get_post_meta( $post->ID, 'rh_product_video', true );
  }else{
    $post_image_videos = get_post_meta( $post->ID, 'rh_post_image_videos', true );
  }
  if(!empty($post_image_videos)){
    if($title == 1){
      echo '<div class="rh-woo-section-title"><h2 class="rh-heading-icon">'.__('Videos', 'rehub-theme').': <span class="rh-woo-section-sub">'.get_the_title().'</span></h2></div>';
    }   
    $post_image_videos = array_map('trim', explode(PHP_EOL, $post_image_videos));
    if($wrapper == 1) {echo '<div class="modulo-lightbox rh-flex-eq-height compare-full-thumbnails mb20">';}
      if ($rel == 'wooyoutube'){
        $random_key = rand(0, 50);
        $rel = 'wooyoutube_gallery_'.(int)$random_key;
      }
        wp_enqueue_script('modulobox'); wp_enqueue_style('modulobox'); 
        if($exceptfirst){
          array_shift($post_image_videos);
        }
        if($fullsize){
            $size = 'width=800 height=520';
            $nothumb = get_template_directory_uri() . '/images/default/noimage_800_520.png';
        }else{
            $size = 'width=450 height=350';
            $nothumb = get_template_directory_uri() . '/images/default/noimage_450_350.png';            
        }
        $i = 0;
        foreach($post_image_videos as $key=>$video) { 
          $video = trim($video);
            $img = parse_video_url(esc_url($video), "maxthumb");
            $i ++;
            $idclass = ($id) ? 'id='.$id.'-'.$i : '';
            echo '<a '.$idclass.' href="'.esc_url($video).'" data-rel="'.$rel.'" target="_blank" class="'.$class.'" data-poster="'.$img.'" data-thumb="'.$img.'">
            <img '.$size.' data-src="'.$img.'" src="'.$nothumb . '" alt="video '.get_the_title().'" class="lazyload" />';
        echo '</a>';
        if($onlyone) break;
      }
    if($wrapper == 1) {echo '</div>';}    
  }
}
}

/*  */
function bd_cloaking_deal_url( $external_link, $post_id ){
    if( is_string( $post_id ) ){
        $dealstore = get_term_by('slug', $post_id, 'dealstore');
        if( $dealstore ){
            $external_link = get_term_meta( $dealstore->term_id, 'brand_url', true ); 
        }
    }
    return $external_link;
}
add_filter( 'wpsmcal_filter_url', 'bd_cloaking_deal_url', 10, 2 );

/**
 * Gets taxonomy term of th post
 * @param $post as object or post ID as numeric
 * @param $tag false by defult if it needs to get a tag taxonomy
 */
if(!function_exists('rh_get_taxonomy_of_post')){
function rh_get_taxonomy_of_post( $post = '', $tag = false ){
  if( empty( $post ) )
    global $post;
  
  if( is_object( $post ) ){
    $post_type = $post->post_type;
  }elseif( is_numeric( $post ) ){
    $post_type = get_post_type( $post );
  }else{
    $post_type = '';
  }
  
  if( empty( $post_type ) )
    return false;
  
  switch( $post_type ){
    case 'blog':
      $taxonomy = 'blog_category';
      if( $tag )
        $taxonomy = 'blog_tag';
      break;
    default:
      $taxonomy = 'category';
      if( $tag )
        $taxonomy = 'post_tag';
  }
  return $taxonomy;
}
}


//////////////////////////////////////////////////////////////////
// Check plugin active
//////////////////////////////////////////////////////////////////
function rh_check_plugin_active( $plugin ) {
    return in_array( $plugin, (array) get_option( 'active_plugins', array() ) ) || rh_check_plugin_active_for_network( $plugin );
}
function rh_check_plugin_active_for_network( $plugin ) {
    if ( !is_multisite() )
        return false;
    $plugins = get_site_option( 'active_sitewide_plugins');
    if ( isset($plugins[$plugin]) )
        return true;
    return false;
}

function rh_filesystem( $method = 'get_content', $file_path='', $content = '' ){
  if( empty( $file_path ) )
    return;
  
  global $wp_filesystem;
  
  if( empty( $wp_filesystem ) ) {
    require_once ( ABSPATH . '/wp-admin/includes/file.php' );
    WP_Filesystem();
  }
  if( $method == 'get_content' ){
    $result = $wp_filesystem->get_contents( $file_path );
    if( $result && !is_wp_error( $result ) ){
      return $result;
    }else{
      return;
    }
  }elseif( $method == 'put_content' ){
    $result = $wp_filesystem->put_contents( $file_path, $content, FS_CHMOD_FILE );
    if( !is_wp_error( $result ) ){
      return true;
    }else{
      return;
    }
  }else{
    return;
  }
}

//////////////////////////////////////////////////////////////////
// Locate template with support RH grandchild
//////////////////////////////////////////////////////////////////
function rh_locate_template($template_names, $load = false, $require_once = true ) {
    $located = '';
    foreach ( (array) $template_names as $template_name ) {
        if ( !$template_name )
            continue;
        if(defined( 'RH_GRANDCHILD_DIR' ) && file_exists(RH_GRANDCHILD_DIR . $template_name)){
            $located = RH_GRANDCHILD_DIR . '/' . $template_name;
            break;            
        }
        if ( file_exists(get_stylesheet_directory() . '/' . $template_name)) {
            $located = get_stylesheet_directory() . '/' . $template_name;
            break;
        } elseif ( file_exists(get_template_directory() . '/' . $template_name) ) {
            $located = get_template_directory() . '/' . $template_name;
            break;
        }
    } 
    if ( $load && '' != $located )
        load_template( $located, $require_once );
      
    return $located;
}

//////////////////////////////////////////////////////////////////
// Helper Functions
//////////////////////////////////////////////////////////////////
function rehub_kses($html)
{
    $allow = array_merge(wp_kses_allowed_html( 'post' ), array(
        'link' => array(
            'href'    => true,
            'rel'     => true,
            'type'    => true,
        ),
        'script' => array(
            'src' => true,
            'charset' => true,
            'type'    => true,
        ),
        'div' => array(
            'data-href' => true,
            'data-width' => true,
            'data-numposts'    => true,
            'data-colorscheme'    => true,
            'class' => true,
            'id' => true,
            'style' => true,
            'title' => true,
            'role' => true,
            'align' => true,
            'dir' => true,
            'lang' => true,
            'xml:lang' => true,         
        )
    ));
    return wp_kses($html, $allow);
}

function rh_import_tables_from_json( $db_table = '', $path_to_json_file = '' ) {
  
  if( !empty( $db_table ) ){
    global $wpdb;
    $table_name = $wpdb->prefix . $db_table;
    $table_name = esc_attr( $table_name );
    $table_name = esc_sql( $table_name ); 
    //$table_name = '%' . $table_name . '%';   

    if( $wpdb->get_var("SHOW TABLES LIKE '$table_name';") != $table_name )
      return;    
    
    $responce = $wpdb->query( "TRUNCATE {$table_name};" );
    
    if( $path_to_json_file ) {
      $json_data = json_decode( rh_filesystem('get_content', $path_to_json_file), true );

      if(empty($json_data)) return;

      foreach( $json_data as $id => $row ){
        $insert_pairs = array();
        foreach( $row as $key => $val ) {
          $insert_pairs[addslashes( $key )] = addslashes( $val );
        }
        $insert_keys = '`' . implode( '`,`', array_keys( $insert_pairs ) ) . '`';
        $insert_vals = '"' . implode( '","', array_values( $insert_pairs ) ) . '"';

        $wpdb->query( "INSERT INTO `{$table_name}` ({$insert_keys}) VALUES ({$insert_vals});" );
      }
    }
  }
}
  

//////////////////////////////////////////////////////////////////
// EXCERPT
//////////////////////////////////////////////////////////////////

if( !function_exists('kama_excerpt') ) {
function kama_excerpt($args=''){
    global $post;
        parse_str($args, $i);
        $maxchar     = isset($i['maxchar']) ?  (int)trim($i['maxchar']) : 350;
        $sanitize_callback     = isset($i['sanitize_callback']) ?  trim($i['sanitize_callback']) : 'strip_tags';
        $text        = isset($i['text']) ?          trim($i['text'])        : '';
        $save_format = isset($i['save_format']) ?   trim($i['save_format']) : false;
        $save_tags = isset($i['save_tags']) ?   trim($i['save_tags']) : '';
        $echo        = isset($i['echo']) ?          false                   : true;
        $more        = isset($i['more']) ?          true                    : false;        

    $out ='';   
    if (!$text){
        $out = $post->post_excerpt ? $post->post_excerpt : $post->post_content;
        $out = preg_replace ("~\[/?.*?\]~", '', $out ); //delete shortcodes:[singlepic id=3]
        // for <!--more-->
        if($more && !$post->post_excerpt && strpos($post->post_content, '<!--more-->') ){
          preg_match ('/(.*)<!--more-->/s', $out, $match);
          $out = str_replace("\r", '', trim($match[1], "\n"));
          $out = preg_replace( "!\n\n+!s", "</p><p>", $out );
          $out = "<p>". str_replace( "\n", "<br />", $out ) ."</p>";
          if ($echo)
              return print ''.$out;
          return $out;
        }
    }

    $out = $text.$out;
    $out = 'strip_tags' === $sanitize_callback ? strip_tags( $out, $save_tags ) : call_user_func( $sanitize_callback, $out );

    if ( mb_strlen( $out ) > $maxchar ){
        $out = mb_substr( $out, 0, $maxchar );
        $out = preg_replace('@(.*)\s[^\s]*$@s', '\\1 ...', $out );
    }   

    if($save_format){
        $out = str_replace( "\r", '', $out );
        $out = preg_replace( "!\n\n+!", "</p><p>", $out );
        $out = "<p>". str_replace ( "\n", "<br />", trim($out) ) ."</p>";
    }

    if($echo) return print ''.$out;
    return $out;
}
}

// Create the Custom Truncate
if( !function_exists('rehub_truncate') ) {
function rehub_truncate($args=''){
        parse_str($args, $i);
        $maxchar     = isset($i['maxchar']) ?  (int)trim($i['maxchar'])     : 350;
        $text        = isset($i['text']) ?          trim($i['text'])        : '';
        $save_format = isset($i['save_format']) ?   trim($i['save_format'])         : false;
        $echo        = isset($i['echo']) ?          false                   : true;

    $out ='';   

    $out = $text.$out;
    $out = preg_replace ("~\[/?.*?\]~", '', $out );
    $out = strip_tags(strip_shortcodes($out));

    if ( mb_strlen( $out ) > $maxchar ){
        $out = mb_substr( $out, 0, $maxchar );
        $out = preg_replace('@(.*)\s[^\s]*$@s', '\\1 ...', $out ); 
    }   

    if($save_format){
        $out = str_replace( "\r", '', $out );
        $out = preg_replace( "!\n\n+!", "</p><p>", $out );
        $out = "<p>". str_replace ( "\n", "<br />", trim($out) ) ."</p>";
    }

    if($echo) return print ''.$out;
    return $out;
}
}


//////////////////////////////////////////////////////////////////
// Pagination
//////////////////////////////////////////////////////////////////

if( !function_exists('rehub_pagination') ) {
function rehub_pagination() {

    if( is_singular() )
        return;
    global $paged;
    global $wp_query;

    /** Stop execution if there's only 1 page */
    if( $wp_query->max_num_pages <= 1 )
        return;

    $paged = get_query_var( 'paged' ) ? absint( get_query_var( 'paged' ) ) : 1;
    $max   = intval( $wp_query->max_num_pages );

    /** Add current page to the array */
    if ( $paged >= 1 )
        $links[] = $paged;

    /** Add the pages around the current page to the array */
    if ( $paged >= 3 ) {
        $links[] = $paged - 1;
        $links[] = $paged - 2;
    }

    if ( ( $paged + 2 ) <= $max ) {
        $links[] = $paged + 2;
        $links[] = $paged + 1;
    }

    echo '<ul class="page-numbers">' . "\n";

    /** Previous Post Link */
    if ( get_previous_posts_link() )
        printf( '<li class="prev_paginate_link">%s</li>' . "\n", get_previous_posts_link() );

    /** Link to first page, plus ellipses if necessary */
    if ( ! in_array( 1, $links ) ) {
        $class = 1 == $paged ? ' class="active"' : '';

        printf( '<li%s><a href="%s">%s</a></li>' . "\n", $class, esc_url( get_pagenum_link( 1 ) ), '1' );

        if ( ! in_array( 2, $links ) )
            echo '<li class="hellip_paginate_link"><span>&hellip;</span></li>';
    }

    /** Link to current page, plus 2 pages in either direction if necessary */
    sort( $links );
    foreach ( (array) $links as $link ) {
        $class = $paged == $link ? ' class="active"' : '';
        printf( '<li%s><a href="%s">%s</a></li>' . "\n", $class, esc_url( get_pagenum_link( $link ) ), $link );
    }

    /** Link to last page, plus ellipses if necessary */
    if ( ! in_array( $max, $links ) ) {
        if ( ! in_array( $max - 1, $links ) )
            echo '<li class="hellip_paginate_link"><span>&hellip;</span></li>' . "\n";

        $class = $paged == $max ? ' class="active"' : '';
        printf( '<li%s><a href="%s">%s</a></li>' . "\n", $class, esc_url( get_pagenum_link( $max ) ), $max );
    }

    /** Next Post Link */
    if ( get_next_posts_link() )
        printf( '<li class="next_paginate_link">%s</li>' . "\n", get_next_posts_link() );

    echo '</ul>' . "\n";

}
}

//////////////////////////////////////////////////////////////////
// Breadcrumbs
//////////////////////////////////////////////////////////////////

if( !function_exists('dimox_breadcrumbs') ) {
function dimox_breadcrumbs() {

  /* === OPTIONS === */
  $text['home'] = esc_html__('Home', 'rehub-theme');
  $text['category'] = esc_html__('Archive category "%s"', 'rehub-theme');
  $text['search'] = esc_html__('Search results for "%s"', 'rehub-theme');
  $text['tag'] = esc_html__('Posts with tag "%s"', 'rehub-theme');
  $text['author'] = esc_html__('Author archive "%s"', 'rehub-theme');
  $text['404'] = esc_html__('Error 404', 'rehub-theme');

  $show_current = 1; // 1 - show current name of article
  $show_on_home = 0; 
  $show_home_link = 1; // 1 - show link to Home page
  $show_title = 1; // 1 - show titles for links
  $delimiter = ' &raquo; '; // delimiter
  $before = '<span class="current">'; // tag before current 
  $after = '</span>'; // tag after current

  global $post;
  $home_link = home_url('/');
  $link_before = '<span>';
  $link_after = '</span>';
  $link_attr = ' ';
  $link = $link_before . '<a' . $link_attr . ' href="%1$s">%2$s</a>' . $link_after;
  $parent_id = $parent_id_2 = $post->post_parent;
  $frontpage_id = get_option('page_on_front');

  if (is_home() || is_front_page()) {

    if ($show_on_home == 1) echo '<div class="breadcrumb font90 rh_opacity_7"><a href="' . $home_link . '">' . $text['home'] . '</a></div>';

  } else {
    echo '<div class="breadcrumb font90 rh_opacity_7">';
    if ($show_home_link == 1) {
      echo '<a href="' . $home_link . '" >' . $text['home'] . '</a>';
      if ($frontpage_id == 0 || $parent_id != $frontpage_id) echo ''.$delimiter;
    }
    if ( is_category() ) {
      $this_cat = get_category(get_query_var('cat'), false);
      if ($this_cat->parent != 0) {
        $cats = get_category_parents($this_cat->parent, TRUE, $delimiter);
        if ($show_current == 0) $cats = preg_replace("#^(.+)$delimiter$#", "$1", $cats);
        $cats = str_replace('<a', $link_before . '<a' . $link_attr, $cats);
        $cats = str_replace('</a>', '</a>' . $link_after, $cats);
        if ($show_title == 0) $cats = preg_replace('/ title="(.*?)"/', '', $cats);
        echo ''.$cats;
      }
      if ($show_current == 1) echo ''.$before . sprintf($text['category'], single_cat_title('', false)) . $after;
    } elseif ( is_search() ) {
      echo ''.$before . sprintf($text['search'], get_search_query()) . $after;
    } elseif ( is_day() ) {
      echo sprintf($link, get_year_link(get_the_time('Y')), get_the_time('Y')) . $delimiter;
      echo sprintf($link, get_month_link(get_the_time('Y'),get_the_time('m')), get_the_time('F')) . $delimiter;
      echo ''.$before . get_the_time('d') . $after;
    } elseif ( is_month() ) {
      echo sprintf($link, get_year_link(get_the_time('Y')), get_the_time('Y')) . $delimiter;
      echo ''.$before . get_the_time('F') . $after;
    } elseif ( is_year() ) {
      echo ''.$before . get_the_time('Y') . $after;
    } elseif ( is_single() && !is_attachment() ) {
        if ( get_post_type() == 'blog' ) {
            $bloglabel = (rehub_option('blog_posttype_label')) ? rehub_option('blog_posttype_label') : esc_html__('Blog', 'rehub-theme');
            $post_type = get_post_type_object(get_post_type());
            $slug = $post_type->rewrite;
            printf($link, $home_link . $slug['slug'] . '/', $bloglabel);
            if ($show_current == 1) echo ''.$delimiter . $before . get_the_title() . $after;
        }
        else if ( get_post_type() != 'post' ) {
            $post_type = get_post_type_object(get_post_type());
            $slug = $post_type->rewrite;
            printf($link, $home_link . $slug['slug'] . '/', $post_type->labels->singular_name);
            if ($show_current == 1) echo ''.$delimiter . $before . get_the_title() . $after;
        } else {
            $cat = get_the_category();
            if(!empty($cat)){ 
            $cat = $cat[0];
            $cats = get_category_parents($cat, TRUE, $delimiter);
            if ($show_current == 0) $cats = preg_replace("#^(.+)$delimiter$#", "$1", $cats);
            $cats = str_replace('<a', $link_before . '<a' . $link_attr, $cats);
            $cats = str_replace('</a>', '</a>' . $link_after, $cats);
            if ($show_title == 0) $cats = preg_replace('/ title="(.*?)"/', '', $cats);
            echo ''.$cats;
            if ($show_current == 1) echo ''.$before . get_the_title() . $after;
            }
        }
    } elseif ( !is_single() && !is_page() && get_post_type() != 'post' && !is_404() ) {
      $post_type = get_post_type_object(get_post_type());
      echo ''.$before . $post_type->labels->singular_name . $after;
    } elseif ( is_attachment() ) {
      $parent = get_post($parent_id);
      $cat = get_the_category($parent->ID); $cat = (!empty($cat[0])) ? $cat[0] : '';
      if ($cat) {
        $cats = get_category_parents($cat, TRUE, $delimiter);
        $cats = str_replace('<a', $link_before . '<a' . $link_attr, $cats);
        $cats = str_replace('</a>', '</a>' . $link_after, $cats);
        if ($show_title == 0) $cats = preg_replace('/ title="(.*?)"/', '', $cats);
        echo ''.$cats;
      }
      printf($link, get_permalink($parent), $parent->post_title);
      if ($show_current == 1) echo ''.$delimiter . $before . get_the_title() . $after;

    } elseif ( is_page() && !$parent_id ) {
      if ($show_current == 1) echo ''.$before . get_the_title() . $after;
    } elseif ( is_page() && $parent_id ) {
      if ($parent_id != $frontpage_id) {
        $breadcrumbs = array();
        while ($parent_id) {
          $page = get_post($parent_id);
          if ($parent_id != $frontpage_id) {
            $breadcrumbs[] = sprintf($link, get_permalink($page->ID), get_the_title($page->ID));
          }
          $parent_id = $page->post_parent;
        }
        $breadcrumbs = array_reverse($breadcrumbs);
        for ($i = 0; $i < count($breadcrumbs); $i++) {
          echo ''.$breadcrumbs[$i];
          if ($i != count($breadcrumbs)-1) echo ''.$delimiter;
        }
      }
      if ($show_current == 1) {
        if ($show_home_link == 1 || ($parent_id_2 != 0 && $parent_id_2 != $frontpage_id)) echo ''.$delimiter;
        echo ''.$before . get_the_title() . $after;
      }
    } elseif ( is_tag() ) {
      echo ''.$before . sprintf($text['tag'], single_tag_title('', false)) . $after;
    } elseif ( is_author() ) {
        global $author;
      $userdata = get_userdata($author);
      echo ''.$before . sprintf($text['author'], $userdata->display_name) . $after;
    } elseif ( is_404() ) {
      echo ''.$before . $text['404'] . $after;
    } elseif ( has_post_format() && !is_singular() ) {
      echo get_post_format_string( get_post_format() );
    }
    if ( get_query_var('paged') ) {
      if ( is_category() || is_day() || is_month() || is_year() || is_search() || is_tag() || is_author() ) echo ' (';
      echo 'Page ' . get_query_var('paged');
      if ( is_category() || is_day() || is_month() || is_year() || is_search() || is_tag() || is_author() ) echo ')';
    }
    echo '</div><!-- .breadcrumbs -->';
  }
} // end dimox_breadcrumbs()
}


/** Autocontents class
 * Taken from: wp-kama.ru/?p=1513
 * V: 2.9.4
 */
class Kama_Contents{
    // defaults options
    public $opt = array(
        'margin'     => 40,
        'selectors'  => array('h2','h3','h4'),
        'to_menu'    => '↑',
        'title'      => '',
        'css'        => '',
        'min_found'  => 2,
        'min_length' => 1500,
        'page_url'   => '',
        'shortcode'  => 'contents',
        'spec'       => '\'.+$*~=',
        'wrap'       => '',
        'tag_inside' => '',
        'anchor_type' => 'a', // or 'id'
        'markup'      => false,
    );

    public $contents; // collect html contents

    private $temp;

    static $inst;

    function __construct( $args = array() ){
        $this->set_opt( $args );
        return $this;
    }

    static function init( $args = array() ){
        is_null( self::$inst ) && self::$inst = new self( $args );
        return self::$inst;
    }

    function set_opt( $args = array() ){
        $this->opt = (object) array_merge( $this->opt, (array) $args );
    }

    function shortcode( $content, $contents_cb = '' ){
        if( false === strpos( $content, '['. $this->opt->shortcode ) ) 
            return $content; 

        // get contents data
        if( ! preg_match('~^(.*)\['. $this->opt->shortcode .'([^\]]*)\](.*)$~s', $content, $m ) )
            return $content;

        $contents = $this->make_contents( $m[3], $m[2] );

        if( $contents && $contents_cb && is_callable($contents_cb) )
            $contents = $contents_cb( $contents );

        return $m[1] . $contents . $m[3];
    }

    function make_contents( & $content, $tags = '' ){
        $this->temp     = $this->opt;
        $this->temp->i  = 0;
        $this->contents = array();

        if( is_string($tags) && $tags = trim($tags) )
            $tags = array_map('trim', preg_split('~\s+~', $tags ) );

        if( ! $tags )
            $tags = $this->opt->selectors;

        // check tags
        foreach( $tags as $k => $tag ){
            // remove special marker tags and set $args
            if( in_array( $tag, array('embed','no_to_menu') ) ){
                if( $tag == 'embed' ) $this->temp->embed = true;
                if( $tag == 'no_to_menu' ) $this->opt->to_menu = false;

                unset( $tags[ $k ] );
                continue;
            }

            // remove tag if it's not exists in content
            $patt = ( ($tag[0] == '.') ? 'class=[\'"][^\'"]*'. substr($tag, 1) : "<$tag" );
            if( ! preg_match("/$patt/i", $content ) ){
                unset( $tags[ $k ] );
                continue;
            }
        }

        if( ! $tags ) return;

        // set patterns from given $tags
        // separate classes & tags & set
        $class_patt = $tag_patt = $level_tags = array();
        foreach( $tags as $tag ){
            // class
            if( isset($tag[0]) && $tag[0] == '.' ){
                $tag  = substr( $tag, 1 );
                $link = & $class_patt;
            }
            // html tag
            else
                $link = & $tag_patt;

            $link[] = $tag;         
            $level_tags[] = $tag;
        }

        $this->temp->level_tags = array_flip( $level_tags );

        $patt_in = array();
        if( $tag_patt )   $patt_in[] = '(?:<('. implode('|', $tag_patt) .')([^>]*)>(.*?)<\/\1>)';
        if( $class_patt ) $patt_in[] = '(?:<([^ >]+) ([^>]*class=["\'][^>]*('. implode('|', $class_patt) .')[^>]*["\'][^>]*)>(.*?)<\/'. ($patt_in?'\4':'\1') .'>)';

        $patt_in = implode('|', $patt_in );

        // collect and replace
        $_content = preg_replace_callback("/$patt_in/is", array( &$this, 'kama_rh_contents_callback'), $content, -1, $count );

        if( ! $count || $count < $this->opt->min_found )
            return;

        $content = $_content;
        // html
        static $css;
        $embed = !! isset($this->temp->embed);
        $ItemList = $this->opt->markup ? ' itemscope itemtype="https://schema.org/ItemList"' : '';
        $this->contents = 
            ( ( $this->opt->wrap ) ? '<div id="'.$this->opt->wrap.'">' : '' ) .
            ( ( !$embed && $this->opt->title ) ? '<div class="kc__wrap">' : '' ) .
            ( ( ! $css && $this->opt->css )    ? '<style>'. $this->opt->css .'</style>' : '' ) .
            ( ( !$embed && $this->opt->title ) ? '<div class="kc-title kc__title" id="kcmenu">'. $this->opt->title .'</div>'. "\n" : '' ) .
                '<ul class="autocontents"'. ((!$this->opt->title || $embed) ? ' id="kcmenu"' : '') .$ItemList.'>'. "\n". 
                    implode('', $this->contents ) .
                '</ul>'."\n" .
            ( ( !$embed && $this->opt->title ) ? '</div>' : '' ).
            ( ( $this->opt->wrap ) ? '</div>' : '' );
        wp_enqueue_style('rhtoc');
        return $this->contents;
    }

    private function kama_rh_contents_callback( $match ){
        // it's only class selector in pattern
        if( count($match) == 5 ){
            $tag   = $match[1];
            $attrs = $match[2];
            $title = $match[4];

            $level_tag = $match[3]; // class_name
        }
        // it's found tag selector
        elseif( count($match) == 4 ){
            $tag   = $match[1];
            $attrs = $match[2];
            $title = $match[3];

            $level_tag = $tag;
        }
        // it's found class selector
        else{
            $tag   = $match[4];
            $attrs = $match[5];
            $title = $match[7];

            $level_tag = $match[6]; // class_name
        }
        $origtitle = $title;
        $title = strip_tags($title);
        $anchor = $this->kama_rh_sanitize_anchor( $title );
    // set up a anchor fo non-supported languages
    if( empty($anchor) || is_numeric($anchor) )
      $anchor = $tag .'_'. ($this->temp->counter +1);
    
        $opt = & $this->opt;

        $level = @ $this->temp->level_tags[ $level_tag ];
        if( $level > 0 )
            $sub = ( $opt->margin ? ' style="margin-left:'. ($level*$opt->margin) .'px;"' : '') . ' class="sub sub_'. $level .'"';
        else 
            $sub = ' class="top"';

        // collect headers
        $this->temp->counter = empty($this->temp->counter) ? 1 : $this->temp->counter+1;
        $schemalist = ($this->opt->markup) ? ' itemprop="itemListElement" itemscope itemtype="http://schema.org/ListItem"' : '';
        $schemaurl = ($this->opt->markup) ? ' itemprop="url"' : '';
        $schemaposition = ($this->opt->markup) ? '<meta itemprop="position" content="'. $this->temp->counter .'" />' : '';
        $schemaname= ($this->opt->markup) ? '<meta itemprop="name" content="'. $title .'" />' : '';

        $this->contents[] = "\t". '<li'. $sub .$schemalist.'><a href="'. $opt->page_url .'#'. $anchor .'"'.$schemaurl.'>'. $title .'</a>'.$schemaposition.$schemaname.'</li>'. "\n";

        // replace
        $to_menu = $new_el = '';
        if( $opt->to_menu )
            $to_menu = (++$this->temp->i == 1) ? '' : '<a class="kc-gotop kc__gotop" href="'. $opt->page_url .'#kcmenu">'. $opt->to_menu .'</a>';

        $tag_inside_head = ( $opt->tag_inside) ? ' class="'.$opt->tag_inside.'"' : '';
        $new_el = "\n<$tag id=\"$anchor\" $tag_inside_head $attrs>$origtitle</$tag>";
        if( $opt->anchor_type == 'a' )
            $new_el = '<a class="kc-anchor kc__anchor" name="'. $anchor .'"></a>'."\n<$tag $attrs>$title</$tag>";

        return $to_menu . $new_el;
    }

    ## URL transliteration
    function kama_rh_sanitize_anchor( $str ){

    $str = rh_convert_cyr_symbols($str);
    $str = str_replace(array('\'', '"'), '', $str); 
    $spec = preg_quote( $this->opt->spec );
    $str = preg_replace("/[^a-zA-Z0-9_$spec\-]+/", '-', $str ); // all unnecessary on '-'
    $str = strtolower( trim( $str, '-') );
    $str = substr( $str, 0, 70 ); // shorten
    //checks if the string is not empty and creates a new one if it duplicates previous
    if( !empty( $str ) )
      $str = $this->_unique_anchor( $str );

        return $str;
    }
  
  ## adds number at the end if this anchor already exists
  function _unique_anchor( $anch ){
    $temp = & $this->temp;

    // check and unique anchor
    if( empty($temp->anchors) ){
      $temp->anchors = array( $anch => 1 );
    }
    elseif( isset($temp->anchors[ $anch ]) ){
      $lastnum = substr( $anch, -1 );
      $lastnum = is_numeric($lastnum) ? $lastnum + 1 : 2;
      return $this->_unique_anchor( "$anch-$lastnum" );
    }
    else {
      $temp->anchors[ $anch ] = 1;
    }

    return $anch;
  }

    ## Strip shortcode
    function strip_shortcode( $text ){
        return preg_replace('~\['. $this->opt->shortcode .'[^\]]*\]~', '', $text );
    }
}

//RustoLat
function rh_convert_cyr_symbols($str=''){
    if (!$str) return;
    $iso9 = array(
        'А'=>'A', 'Б'=>'B', 'В'=>'V', 'Г'=>'G', 'Д'=>'D', 'Е'=>'E', 'Ё'=>'YO', 'Ж'=>'ZH',
        'З'=>'Z', 'И'=>'I', 'Й'=>'J', 'К'=>'K', 'Л'=>'L', 'М'=>'M', 'Н'=>'N', 'О'=>'O',
        'П'=>'P', 'Р'=>'R', 'С'=>'S', 'Т'=>'T', 'У'=>'U', 'Ф'=>'F', 'Х'=>'H', 'Ц'=>'TS',
        'Ч'=>'CH', 'Ш'=>'SH', 'Щ'=>'SHH', 'Ъ'=>'', 'Ы'=>'Y', 'Ь'=>'', 'Э'=>'E', 'Ю'=>'YU', 'Я'=>'YA', 'Č' => 'C', 'Š' => 'S', 'Ř' => 'R', 'Ď' => 'D', 'Ň'=> 'N', 'Ť'=> 'T', 'Ž' => 'Z', 'Ľ' => 'L', 'Ý'=> 'Y', 'Á'=> 'A', 'Í'=>'I', 'É'=> 'E', 'Ě'=>'E', 'Ů'=>'U', 'Ú'=> 'U','Ä'=>'AE', 'Ö'=>'OE', 'Ü'=>'UE',
        // small
        'а'=>'a', 'б'=>'b', 'в'=>'v', 'г'=>'g', 'д'=>'d', 'е'=>'e', 'ё'=>'yo', 'ж'=>'zh',
        'з'=>'z', 'и'=>'i', 'й'=>'j', 'к'=>'k', 'л'=>'l', 'м'=>'m', 'н'=>'n', 'о'=>'o',
        'п'=>'p', 'р'=>'r', 'с'=>'s', 'т'=>'t', 'у'=>'u', 'ф'=>'f', 'х'=>'h', 'ц'=>'ts',
        'ч'=>'ch', 'ш'=>'sh', 'щ'=>'shh', 'ъ'=>'', 'ы'=>'y', 'ь'=>'', 'э'=>'e', 'ю'=>'yu', 'я'=>'ya','ó' => 'o',
        // other
        'Ѓ'=>'G', 'Ґ'=>'G', 'Є'=>'YE', 'Ѕ'=>'Z', 'Ј'=>'J', 'І'=>'I', 'Ї'=>'YI', 'Ќ'=>'K', 'Љ'=>'L', 'Њ'=>'N', 'Ў'=>'U', 'Џ'=>'DH',          
        'ѓ'=>'g', 'ґ'=>'g', 'є'=>'ye', 'ѕ'=>'z', 'ј'=>'j', 'і'=>'i', 'ї'=>'yi', 'ќ'=>'k', 'љ'=>'l', 'њ'=>'n', 'ў'=>'u', 'џ'=>'dh', 'č' => 'c', 'š' => 's', 'ř' => 'r', 'ď' => 'd', 'ň'=> 'n', 'ť'=> 't', 'ž' => 'z', 'ľ' => 'l', 'ý' => 'y', 'á' => 'a', 'í' => 'i', 'é' => 'e', 'ě' => 'e', 'ů' => 'u', 'ú' => 'u', '.' => '-', '$' => 's', 'ä' => 'ae', 'ö' => 'oe', 'ü' => 'ue', 'ß' => 'ss', '+' => 'plus', '*' => 'a',  '%' => 'percent', '&' => 'and', '$' => 'usd', '€' => 'euro', '’' => '',  '#' => '', '@' => 'mail', '”' => '', '/' => '', '`'=> '',
        //polish
        'ą'=>'a', 'Ą'=>'A', 'ć'=>'c', 'Ć'=>'C', 'ę'=>'e', 'Ę'=>'E', 'ł'=>'l', 'Ł'=>'L',
        'ń'=>'n', 'Ń'=>'N', 'ó'=>'o', 'Ó'=>'O', 'ś'=>'s', 'Ś'=>'S', 'ź'=>'z', 'Ź'=>'Z',
        'ż'=>'z', 'Ż'=>'Z',

        'א'=>'A', 'ב'=>'B', 'ב'=>'V', 'ג'=>'G', 'ד'=>'D', 'ה'=>'H', 'ו'=>'W','ו'=>'V', 'ז'=>'Z', 'ח'=>'H', 'ט'=>'T', 'י'=>'Y', 'כ'=>'K', 'ך'=>'K', 'ל'=>'L', 'מ'=>'M', 'ם'=>'M', 'נ'=>'N', 'ן'=>'N', 'ס'=>'S', 'ס'=>'C', 'פ'=>'P', 'פ'=>'F', 'ף'=>'F', 'ף'=>'P', 'צ'=>'S', 'ץ'=>'S', 'ק'=>'Q', 'ר'=>'R', 'ש'=>'S', 'ת'=>'T',
        //vn
        'à'=>'a', 'á'=>'a', 'ạ'=>'a', 'ã'=>'a', 'ả'=>'a', 'ă'=>'a', 'ằ'=>'a', 'ắ'=>'a', 'ặ'=>'a', 'ẵ'=>'a', 'ẳ'=>'a', 'â'=>'a', 'ầ'=>'a', 'ấ'=>'a', 'ậ'=>'a', 'ẫ'=>'a', 'ẩ'=>'a', 'đ'=>'d', 'è'=>'e', 'é'=>'e', 'ẹ'=>'e', 'ẽ'=>'e', 'ẻ'=>'e', 'ê'=>'e', 'ề'=>'e', 'ế'=>'e', 'ệ'=>'e', 'ễ'=>'e', 'ể'=>'e', 'ì'=>'i', 'í'=>'i', 'ị'=>'i', 'ĩ'=>'i', 'ỉ'=>'i', 'ò'=>'o', 'ò'=>'o', 'ọ'=>'o', 'õ'=>'o', 'ỏ'=>'o', 'ô'=>'o', 'ồ'=>'o', 'ố'=>'o', 'ộ'=>'o', 'ỗ'=>'o', 'ổ'=>'o', 'ơ'=>'o', 'ờ'=>'o', 'ớ'=>'o', 'ợ'=>'o', 'ỡ'=>'o', 'ở'=>'o', 'ù'=>'u', 'ú'=>'u', 'ụ'=>'u', 'ũ'=>'u', 'ủ'=>'u', 'ư'=>'u', 'ừ'=>'u', 'ứ'=>'u', 'ự'=>'u', 'ữ'=>'u', 'ử'=>'u', 'ỳ'=>'y', 'ý'=>'y', 'ỵ'=>'y', 'ỹ'=>'y', 'ỷ'=>'y', 'À'=>'A', 'Á'=>'A', 'Ạ'=>'A', 'Ã'=>'A', 'Ả'=>'A', 'Ă'=>'A', 'Ằ'=>'A', 'Ắ'=>'A', 'Ặ'=>'A', 'Ẵ'=>'A', 'Ẳ'=>'A', 'Â'=>'A', 'Ầ'=>'A', 'Ấ'=>'A', 'Ậ'=>'A', 'Ẫ'=>'A', 'Ẩ'=>'A', 'Đ'=>'D', 'È'=>'E', 'É'=>'E', 'Ẹ'=>'E', 'Ẽ'=>'E', 'Ẻ'=>'E', 'Ê'=>'E', 'Ề'=>'E', 'Ế'=>'E', 'Ệ'=>'E', 'Ễ'=>'E', 'Ể'=>'E', 'Ì'=>'I', 'Í'=>'I', 'Ị'=>'I', 'Ĩ'=>'I', 'Ỉ'=>'I', 'Ò'=>'O', 'Ò'=>'O', 'Ọ'=>'O', 'Õ'=>'O', 'Ỏ'=>'O', 'Ô'=>'O', 'Ồ'=>'O', 'Ố'=>'O', 'Ộ'=>'O', 'Ỗ'=>'O', 'Ổ'=>'O', 'Ơ'=>'O', 'Ờ'=>'O', 'Ớ'=>'O', 'Ợ'=>'O', 'Ỡ'=>'O', 'Ở'=>'O', 'Ù'=>'U', 'Ú'=>'U', 'Ụ'=>'U', 'Ũ'=>'U', 'Ủ'=>'U', 'Ư'=>'U', 'Ừ'=>'U', 'Ứ'=>'U', 'Ự'=>'U', 'Ữ'=>'U', 'Ử'=>'U', 'Ỳ'=>'Y', 'Ý'=>'Y', 'Ỵ'=>'Y', 'Ỹ'=>'Y', 'Ỷ'=>'Y'
    );
    $str = strtr( $str, $iso9 );
    return $str;
}



## Proccesing contents shortcode
add_filter('the_content', 'rehub_contents_shortcode');
function rehub_contents_shortcode( $content ){
    $args = array();
    $args['to_menu']  = '';

    $autocontents = new Kama_Contents($args);   
    if( is_singular() ){        
        return $autocontents->shortcode( $content );
    }
    else{
        return $autocontents->strip_shortcode( $content );
    }
}

## Proccesing toplist shortcode
add_filter('the_content', 'rehubtop_contents_shortcode');
function rehubtop_contents_shortcode( $content ){
    $args = array();
    $args['shortcode'] = 'wpsm_toplist';
    $args['anchor_type'] = 'id';
    $args['wrap'] = 'toplistmenu';
    $args['tag_inside'] = 'wpsm_toplist_heading';
    $args['to_menu']  = ''; 
    $args['selectors'] = array ('h2');
    $args['markup'] = true;
    $toplist = new Kama_Contents($args);

    if( is_singular() ){
        return $toplist->shortcode( $content );
    }
    else{
        return $toplist->strip_shortcode( $content );
    }
}


if(!function_exists('wpsm_stickypanel_shortcode')) {
    function wpsm_stickypanel_shortcode($atts, $content) {  
        $content = do_shortcode($content); 
        wp_enqueue_script( 'contentstickypanel', get_template_directory_uri() . '/js/contentstickypanel.js', array( 'jquery', 'rehubwaypoints' ), 1.2, true ); 
        return '<div id="content-sticky-panel">
            <style scoped>
                #content-sticky-panel{transition: all 0.5s ease; position:sticky; top:100px; background: #fff; border-bottom: none; margin: 0 0 0 -125px;width: 100px;font-size: 10px;line-height: 12px; z-index: 9989; height:0}
                #content-sticky-panel ul, #content-sticky-panel ul li{margin: 0; padding: 0}
                #content-sticky-panel ul{border: 1px solid #ddd; border-bottom: none;background:white}
                #content-sticky-panel a{font-weight: 600;padding: 6px; border-bottom: 1px solid #ddd; text-decoration: none; color: #111; display: block; }
                #content-sticky-panel li.top:before{display: none; z-index: 99999}
                
                #mobileactivate{cursor:pointer; display: none;position:absolute; top: 0; left: 100%; height: 50px; line-height: 50px; opacity: 0.8; width: 30px; background: green; color: #fff; text-align: center; font-size: 15px}
                @media (max-width: 1500px){
                    #content-sticky-panel{height:auto;position: fixed; left: -200px; margin: 0;  width:200px; font-size:13px; line-height:15px}
                    #content-sticky-panel.mobileactive{left: -1px;}
                    #content-sticky-panel a{padding:10px 6px}
                    #mobileactivate{display: block;}
                    #content-sticky-panel ul{overflow-y:scroll}
                }            
            </style>
            <span id="mobileactivate"><i class="rhicon rhi-ellipsis-v" aria-hidden="true"></i></span>' . $content . '
        </div>';  
    } 
}

if( !function_exists('wpsm_contents_shortcode') ){
    function wpsm_contents_shortcode($atts, $content = null){
        if(!is_singular()) return;

        extract(shortcode_atts(array(
            'parent' => 'post',
            'headers' => 'h2,h3',
        ), $atts));

        global $post;
        $selarray = array();
        $selectors = explode(',', $headers);
        $post_content = apply_filters( 'the_content', $post->post_content );
        $args = array(
            'selectors' => $selectors,
            'margin' => 15,
            'to_menu' => false,
            'title' => false,
            'anchor_type' => 'id',
        );

        foreach($selectors as $selector){
            $selarray[] = '.'. $parent .' '. $selector;
        }
        $selstring = implode(', ', $selarray);
        $script = "(function(b,c){var $=b.jQuery||b.Cowboy||(b.Cowboy={}),a;$.rhthrottle=a=function(e,f,j,i){var h,d=0;if(typeof f!==\"boolean\"){i=j;j=f;f=c}function g(){var o=this,m=+new Date()-d,n=arguments;function l(){d=+new Date();j.apply(o,n)}function k(){h=c}if(i&&!h){l()}h&&clearTimeout(h);if(i===c&&m>e){l()}else{if(f!==true){h=setTimeout(i?k:l,i===c?e-m:e)}}}if($.guid){g.guid=j.guid=j.guid||$.guid++}return g};})(this);
        (function($){ $.fn.wpsmContents=function(){var id,h,m=$(this),w=m.closest('.widget'),s=$('{$selstring}'); 
        if(s.length == 0){w.remove();}else{ $.each(s,function(){ h=$(this); $.each(m.find('a'), function(){ if(h.text()==$(this).text()){ id=$(this).attr('href').replace('#',''); h.attr('id', id);}});}); if(id == undefined){w.remove();}} return;}})(jQuery); jQuery('.autocontents').wpsmContents(); 
            var topMenu = jQuery('.autocontents');  
            var menuItems = topMenu.find('a');
            var lastId = '';

            var scrollItems = menuItems.map(function(){
                var elem = jQuery(this).attr('href');
                var item = jQuery(elem);
              if (item.length) { return item; }
            });
            jQuery(window).on('scroll', jQuery.rhthrottle( 250, function(){
                var fromTop = jQuery(this).scrollTop()+55;
                var cur = scrollItems.map(function(){
                    if (jQuery(this).offset().top < fromTop)
                    return this;
                });
                cur = cur[cur.length-1];
                var id = cur && cur.length ? cur[0].id : '';

                if (lastId !== id) {
                    lastId = id;
                    var currentmenuItem = menuItems.filter('[href=\"#'+id+'\"]');
                    var currentmenuIteml = currentmenuItem.offset();
                    menuItems.removeClass('fontbold').parent().removeClass('current');
                    currentmenuItem.addClass('fontbold').parent().addClass('current');
                }                   
            }));";

        wp_add_inline_script( 'rehub', $script);

        //global $pages;
        //if( $pages && count($pages) == 1 ){
            //$pages[0] = $post_content;
        //}

        $contents = Kama_Contents::init($args)->make_contents($post_content);
        return $contents;
    }
}

//Get site favicon
if (!function_exists('rehub_get_site_favicon')) {

    function rehub_get_site_favicon($url) {
        $url = esc_url($url);
        $shop = parse_url($url, PHP_URL_HOST);
        $shop = preg_replace('/^www\./', '', $shop);

        if ($shop){
            if($shop == 'dl.flipkart.com'){
                $shop = 'flipkart.com';
            }
            elseif($shop == 'click.linksynergy.com'){
                $shop = 'linkshare.com';
            } 
            elseif($shop == 'rover.ebay.com'){
                $shop = 'ebay.com';
            }             
            elseif($shop == 'pdt.tradedoubler.com'){
                $shop = 'tradedoubler.com';
            }
            elseif($shop == 'partners.webmasterplan.com'){
                $shop = 'affili.net';
            } 
            elseif($shop == 'ad.zanox.com'){
                $shop = 'zanox.com';
            }                                                
            $d = explode('.', $shop);
            $title = $d[0]; 
            $logo_urls_trans = get_transient('ce_favicon_urls');
            if (empty($logo_urls_trans)){
                $logo_urls_trans = array();
            }
            if(array_key_exists($shop, $logo_urls_trans)){
                $logo_url = $logo_urls_trans[$shop];
                if(is_ssl()) {$logo_url = str_replace('http://', 'https://', $logo_url);}
                return '<img src="'.$logo_url.'" height=16 width=16 alt='.$title.' /> '.$shop;
            }
            else {         
                $img_uri = '//www.google.com/s2/favicons?domain=http://'.$shop;                  
                $new_logo_url = rh_ae_saveimg_towp($img_uri, $title);
                if(!empty($new_logo_url)){
                    $logo_urls_trans[$shop] = $new_logo_url;
                    set_transient('ce_favicon_urls', $logo_urls_trans, 180 * DAY_IN_SECONDS); 
                    if(is_ssl()) {$new_logo_url = str_replace('http://', 'https://', $new_logo_url);} 
                    if($shop == 'amazon'){
                      return '<span class="compare-domain-text fontbold">'.ucfirst($shop).'</span>';
                    } 
                    else{
                      return '<img src="'.$new_logo_url.'" height=16 width=16 alt='.$title.' /> <span class="compare-domain-text fontbold">'.ucfirst($shop).'</span>';
                    }                  
                }
            }            
        }
    }
} 

if (!function_exists('rehub_get_site_favicon_icon')) {

    function rehub_get_site_favicon_icon($url) {
        $url = esc_url($url);
        $shop = parse_url($url, PHP_URL_HOST);
        $shop = preg_replace('/^www\./', '', $shop);
        if ($shop){
            if($shop == 'dl.flipkart.com'){
                $shop = 'flipkart.com';
            }
            elseif($shop == 'click.linksynergy.com'){
                $shop = 'linkshare.com';
            } 
            elseif($shop == 'rover.ebay.com'){
                $shop = 'ebay.com';
            }             
            elseif($shop == 'pdt.tradedoubler.com'){
                $shop = 'tradedoubler.com';
            }
            elseif($shop == 'partners.webmasterplan.com'){
                $shop = 'affili.net';
            }  
            elseif($shop == 'ad.zanox.com'){
                $shop = 'zanox.com';
            }                        
            $d = explode('.', $shop);
            $title = $d[0]; 
            $logo_urls_trans = get_transient('ce_favicon_urls');
            if (empty($logo_urls_trans)){
                $logo_urls_trans = array();
            }
            if(array_key_exists($shop, $logo_urls_trans)){
                $logo_url = $logo_urls_trans[$shop];
                if(is_ssl()) {$logo_url = str_replace('http://', 'https://', $logo_url);}              
                return '<img src="'.$logo_url.'" height=16 width=16 alt='.$title.' />';
            }
            else {         
                $img_uri = '//www.google.com/s2/favicons?domain=http://'.$shop;                  
                $new_logo_url = rh_ae_saveimg_towp($img_uri, $title);
                if(!empty($new_logo_url)){
                    $logo_urls_trans[$shop] = $new_logo_url;
                    set_transient('ce_favicon_urls', $logo_urls_trans, 180 * DAY_IN_SECONDS);
                    if(is_ssl()) {$new_logo_url = str_replace('http://', 'https://', $new_logo_url);} 
                    return '<img src="'.$new_logo_url.'" height=16 width=16 alt='.$title.' />';
                }
            }            
        }       
    }
} 

if(!function_exists('rh_fix_domain')){
    function rh_fix_domain($merchant, $domain){
        if($merchant){
            $merchant = trim($merchant);
        }
        if($merchant == 'Ferrari Store UK'){
            $domain = 'ferrari.com';
        }  
        if($domain == 'dl.flipkart.com'){
            $domain = 'flipkart.com';
        }
        elseif($domain == 'click.linksynergy.com'){
            $domain = 'linkshare.com';
        } 
        elseif($domain == 'pdt.tradedoubler.com'){
            $domain = 'tradedoubler.com';
        }
        elseif($domain == 'rover.ebay.com'){
            $domain = 'ebay.com';
        }         
        elseif($domain == 'partners.webmasterplan.com'){
            $domain = 'affili.net';
        }  
        elseif($domain == 'ad.zanox.com'){
            $domain = 'zanox.com';
        }   
        elseif($domain == 'catalog.paytm.com'){
            $domain = 'paytm.com';
        }                     
        return $domain;
    }
}


//Get site favicon
if (!function_exists('rh_best_syncpost_deal')) {
    function rh_best_syncpost_deal($itemsync = '', $wrapclass = 'mb10 compare-domain-icon', $image='yes') {
        if(empty($itemsync)) return;
        $merchant = (!empty($itemsync['merchant'])) ? $itemsync['merchant'] : '';
        $domain = (!empty($itemsync['domain'])) ? $itemsync['domain'] : '';
        $out = '';
        $out .='<div class="'.$wrapclass.'">';
        $out .='<span>'.__("Best deal at: ", "rehub-theme").'</span>';
        if($image == 'yes'){
            $out .=' <img src="'.esc_attr(\ContentEgg\application\helpers\TemplateHelper::getMerhantIconUrl($itemsync, true)).'" alt="'.$domain.'" height=16 />';          
        }

        if ($merchant){
            $out .='<span class="compare-domain-text fontbold">'.esc_html($merchant).'</span>';
        }
        elseif($domain){
            $out .='<span class="compare-domain-text fontbold">'.esc_html($domain).'</span>';            
        }        
        $out .='</div>';
        return $out;
    }
} 


if(!function_exists('rehub_get_ip')) {
    #get the user's ip address
    function rehub_get_ip() {
        if(function_exists('rh_framework_user_ip')){
            return rh_framework_user_ip();
        }
        else{
            return '127.0.0.3';
        }
    }
}

if (!function_exists('rehub_truncate_title')) {
    #get custom length titles
    function rehub_truncate_title($len = 110, $id = NULL) {
        $title = get_the_title($id);        
        if (!empty($len) && mb_strlen($title)>$len) $title = mb_substr($title, 0, $len-3) . "...";
        return $title;
    }
}

if ( !function_exists( 'rh_serialize_data_review' ) ) {
    function rh_serialize_data_review( $array_data ) {
        serialize( $array_data );
        return $array_data;
    }
}

if ( !function_exists( 'rh_ae_logo_get' ) ) {
    function rh_ae_logo_get( $offerurl, $size=120 ) {
        if ($offerurl){
             $domain = str_ireplace('www.', '', parse_url($offerurl, PHP_URL_HOST));
        }
        if ($domain){   
            if ($domain == 'amazon.de' || $domain == 'amazon.com' || $domain == 'amazon.co.uk' || $domain == 'amazon.es' || $domain == 'amazon.in' || $domain == 'amazon.nl' ){
                return false;
            } 
            elseif ($domain == 'ebay.de' || $domain == 'ebay.com' || $domain == 'ebay.co.uk' || $domain == 'ebay.es' || $domain == 'ebay.in' || $domain == 'ebay.nl' ){
                return get_template_directory_uri().'/images/logos/ebay.png';
            }  
            elseif ($domain == 'aliexpress.com'){
                return get_template_directory_uri().'/images/logos/aliexpress.png';
            }  
            elseif ($domain == 'flipkart.com' || $domain == 'dl.flipkart.com' ){
                return get_template_directory_uri().'/images/logos/flipkart.png';
            }    
            elseif ($domain == 'snapdeal.com'){
                return get_template_directory_uri().'/images/logos/snapdeal.png';
            }  
            elseif ($domain == 'banggood.com'){
                return get_template_directory_uri().'/images/logos/banggood.png';
            }             
            elseif ($domain == 'shopclues.com'){
                return get_template_directory_uri().'/images/logos/shopclues.png';
            }  
            elseif ($domain == 'etsy.com'){
                return get_template_directory_uri().'/images/logos/etsy.png';
            }  
            elseif ($domain == 'wiggle.com' || $domain == 'wiggle.co.uk'){
                return get_template_directory_uri().'/images/logos/wiggle.jpg';
            } 
            elseif ($domain == 'iherb.com' || $domain == 'ru.iherb.com'){
                return get_template_directory_uri().'/images/logos/iherb.jpg';
            }  
            elseif ($domain == 'airbnb.com' || $domain == 'ru.airbnb.com'){
                return get_template_directory_uri().'/images/logos/airbnb.jpg';
            } 
            elseif ($domain == 'infibeam.com'){
                return get_template_directory_uri().'/images/logos/infibeam.png';
            }                                                                                                        
            $logo_urls_trans = get_transient('ae_logo_store_urls');
            if (empty($logo_urls_trans)){
                $logo_urls_trans = array();
            }
            if(array_key_exists($domain, $logo_urls_trans)){
                return $logo_urls_trans[$domain];
            }
            else {
                $d = explode('.', $domain);
                $title = $d[0];  
                $img_uri = '//logo.clearbit.com/'.$domain.'?size='.$size.'';                  
                $new_logo_url = rh_ae_saveimg_towp($img_uri, $title);
                if(!empty($new_logo_url)){
                    $logo_urls_trans[$domain] = $new_logo_url;
                    set_transient('ae_logo_store_urls', $logo_urls_trans, 180 * DAY_IN_SECONDS); 
                    return $new_logo_url;
                }else{
                    return get_template_directory_uri() . '/images/default/wcvendoravatar.png';
                }
            }

        }
    }
}

if ( !function_exists( 'rh_ae_saveimg_towp' ) ) {
    function rh_ae_saveimg_towp($img_uri, $title = '', $check_image_type = true)
    {

        $uploads = wp_upload_dir();
        $newfilename = $title;
        $newfilename = preg_replace('/[^a-zA-Z0-9\-]/', '', $newfilename);
        $newfilename = strtolower($newfilename);
        if (!$newfilename)
            $newfilename = time();
        if (0 === strpos($img_uri, '//')) {
            $img_uri = 'https:' . $img_uri;
        }
        elseif(false === strpos($img_uri, '://')){
            $img_uri = 'https://' . $img_uri;
        }   
        require_once(ABSPATH . 'wp-admin/includes/file.php');     
        $downloadfile = download_url( $img_uri, 5 );
        if (is_wp_error($downloadfile) ){
            return false;
        }

        $newfilename .= '.png';
        $newfilename = wp_unique_filename($uploads['path'], $newfilename);

        if ($check_image_type)
        {
            $filetype = wp_check_filetype($newfilename, null);
            if (substr($filetype['type'], 0, 5) != 'image')
                return false;
        }

        $file_path = $uploads['path'] . DIRECTORY_SEPARATOR . $newfilename;
        $current = rh_filesystem('get_content', $downloadfile);
        if (!rh_filesystem('put_content', $file_path, $current)) {
            return false;
        }
        return trailingslashit($uploads['url']).$newfilename;
    }

}

add_filter( 'gmw_pt_map_icon', 'rh_gmw_post_mapin', 10, 2);
if (!function_exists('rh_gmw_post_mapin')){
    function rh_gmw_post_mapin ($post, $gmw_form){
        global $post;
        $postid = $post->ID;
        return get_template_directory_uri() . '/images/default/mappostpin.png';        
    }
}

if(!function_exists('rh_gmw_fl_search_query_args')){
    function rh_gmw_fl_search_query_args($form){
        if(isset($form['gmw_args']['address']))
            return $form;

        $form['type'] = 'newest'; // active, popular, online
        return $form;
    }
    add_filter('gmw_fl_search_query_args', 'rh_gmw_fl_search_query_args');
}

if (!function_exists('rh_gmw_post_in_popup')){
    function rh_gmw_post_in_popup ($output, $post, $gmw_form){
        $address   = ( !empty( $post->formatted_address ) ) ? $post->formatted_address : $post->address;
        $permalink = get_permalink( $post->ID );
        $thumb     = get_the_post_thumbnail( $post->ID );
        
        $output                  = array();
        $output['start']         = "<div class=\"gmw-pt-info-window-wrapper wppl-pt-info-window\">";
        $output['thumb']         = "<div class=\"thumb wppl-info-window-thumb\">{$thumb}</div>";
        $output['content_start'] = "<div class=\"content wppl-info-window-info\"><table>";
        $output['title']         = "<tr><td><div class=\"title wppl-info-window-permalink\"><a href=\"{$permalink}\">{$post->post_title}</a></div></td></tr>";
        $output['address']       = "<tr><td><span class=\"address\">{$gmw_form['labels']['info_window']['address']}</span>{$address}</td></tr>";
        
        if ( isset( $post->distance ) ) {
            $output['distance'] = "<tr><td><span class=\"distance\">{$gmw_form['labels']['info_window']['distance']}</span>{$post->distance} {$gmw_form['units_array']['name']}</td></tr>";
        }
        
        if ( !empty( $gmw_form['search_results']['additional_info'] ) ) {
        
            foreach ( $gmw_form['search_results']['additional_info'] as $field ) {
                if ( isset( $post->$field ) ) {
                    $output[$gmw_form['labels']['info_window'][$field]] = "<tr><td><span class=\"{$gmw_form['labels']['info_window'][$field]}\">{$gmw_form['labels']['info_window'][$field]}</span>{$post->$field}</td></tr>";
                }
            }
        }
        
        $output['content_end'] = "</table></div>";
        $output['end']         = "</div>";
        return $output;
    }
}
//add_filter( 'gmw_pt_info_window_content', 'rh_gmw_post_in_popup', 10, 3);

//////////////////////////////////////////////////////////////////
// Hex to RGBA
//////////////////////////////////////////////////////////////////
if (!function_exists('hex2rgba')){
function hex2rgba($color, $opacity = false) {
 
    $default = 'rgb(0,0,0)';
 
    //Return default if no color provided
    if(empty($color))
          return $default; 
 
    //Sanitize $color if "#" is provided 
        if ($color[0] == '#' ) {
            $color = substr( $color, 1 );
        }
 
        //Check if color has 6 or 3 characters and get values
        if (strlen($color) == 6) {
                $hex = array( $color[0] . $color[1], $color[2] . $color[3], $color[4] . $color[5] );
        } elseif ( strlen( $color ) == 3 ) {
                $hex = array( $color[0] . $color[0], $color[1] . $color[1], $color[2] . $color[2] );
        } else {
                return $default;
        }
 
        //Convert hexadec to rgb
        $rgb =  array_map('hexdec', $hex);
 
        //Check if opacity is set(rgba or rgb)
        if($opacity){
            if(abs($opacity) > 1)
                $opacity = 1.0;
            $output = 'rgba('.implode(",",$rgb).','.$opacity.')';
        } else {
            $output = 'rgb('.implode(",",$rgb).')';
        }
 
        //Return rgb(a) color string
        return $output;
}
}

//////////////////////////////////////////////////////////////////
// CSS minify
//////////////////////////////////////////////////////////////////
if (!function_exists('rehub_quick_minify')){ 
function rehub_quick_minify( $css ) {
    $css = preg_replace( '/\s+/', ' ', $css );
    $css = preg_replace( '/\/\*[^\!](.*?)\*\//', '', $css );
    $css = preg_replace( '/(,|:|;|\{|}) /', '$1', $css );
    $css = preg_replace( '/ (,|;|\{|})/', '$1', $css );
    $css = preg_replace( '/(:| )0\.([0-9]+)(%|em|ex|px|in|cm|mm|pt|pc)/i', '${1}.${2}${3}', $css );
    $css = preg_replace( '/(:| )(\.?)0(%|em|ex|px|in|cm|mm|pt|pc)/i', '${1}0', $css );
    return trim( $css );
}
}

//////////////////////////////////////////////////////////////////
// Get cross taxonomy
//////////////////////////////////////////////////////////////////
if (!function_exists('rh_get_crosstaxonomy')){ 
function rh_get_crosstaxonomy( $parent, $ids, $showtax ) {
    global $wpdb;
    $tags = $wpdb->get_results($wpdb->prepare("SELECT DISTINCT terms2.term_id as tag_id, terms2.name as tag_name, terms2.slug as tag_slug, null as tag_link FROM $wpdb->posts as p1 LEFT JOIN $wpdb->term_relationships as r1 ON p1.ID = r1.object_ID LEFT JOIN $wpdb->term_taxonomy as t1 ON r1.term_taxonomy_id = t1.term_taxonomy_id LEFT JOIN $wpdb->terms as terms1 ON t1.term_id = terms1.term_id, $wpdb->posts as p2 LEFT JOIN $wpdb->term_relationships as r2 ON p2.ID = r2.object_ID LEFT JOIN $wpdb->term_taxonomy as t2 ON r2.term_taxonomy_id = t2.term_taxonomy_id LEFT JOIN $wpdb->terms as terms2 ON t2.term_id = terms2.term_id WHERE t1.taxonomy = %s AND p1.post_status = 'publish' AND terms1.term_id IN (%d) AND t2.taxonomy = %s AND p2.post_status = 'publish' AND p1.ID = p2.ID ORDER by tag_name", $parent, $ids, $showtax));    
    $count = 0;
    foreach ($tags as $tag) {
        $tags[$count]->tag_link = get_tag_link($tag->tag_id);
        $count++;
    }
    return $tags;
}
}

//////////////////////////////////////////////////////////////////
// Get Remote page via wordpress
//////////////////////////////////////////////////////////////////
function rh_get_remote_page( $url, $caller_id = '' ) {
  $response = wp_remote_get( $url, array(
    'timeout' => 30,
    'sslverify' => false,
    'user-agent' => 'Mozilla/5.0 ( Windows NT 6.3; WOW64; rv:35.0 ) Gecko/20100101 Firefox/35.0',
  ) );

  if ( is_wp_error( $response ) ) {
    return false;
  }
  $rh_request_result = wp_remote_retrieve_body( $response );
  if ( $rh_request_result == '' ) {
    return false;
  }
  return $rh_request_result;
}


//////////////////////////////////////////////////////////////////
// Get position in ratings
//////////////////////////////////////////////////////////////////
function rh_get_product_position( $id, $taxonomy = 'product_cat', $key = 'rehub_review_overall_score', $posttype = 'product' ){
  // get terns of the current post
  $terms = get_the_terms( $id, $taxonomy );
  
  if ( ! $terms || is_wp_error( $terms ) )
    return;
  
  global $wpdb;
  $db_prefix = $wpdb->prefix;
  // id of the first or parent term of the post
  $top_term_id = $terms[0]->term_id;
  // name of the first or parent term of the post
  $top_term_name = $terms[0]->name;
  $top_term_link = get_term_link( (int)$top_term_id, $taxonomy );
  // add chosen term to array for fetching posts
  $terms = array( $top_term_id );
  // get child terms of the the chosen term
  $terms = array_merge( $terms, get_term_children( $top_term_id, $taxonomy ) );
  $str_terms = implode(',', $terms);
  
  // sql query which fetch post ids from chosen term and sort them by value of 'rehub_review_overall_score' meta key (from low to high)
  $sql_query = $wpdb->prepare("SELECT $wpdb->posts.ID FROM $wpdb->posts LEFT JOIN $wpdb->term_relationships ON ($wpdb->posts.ID = $wpdb->term_relationships.object_id) INNER JOIN $wpdb->postmeta ON ($wpdb->posts.ID = $wpdb->postmeta.post_id ) WHERE 1=1 AND ($wpdb->term_relationships.term_taxonomy_id IN (%s)) AND ($wpdb->postmeta.meta_key = %s) AND $wpdb->posts.post_type = %s AND ($wpdb->posts.post_status = 'publish') GROUP BY $wpdb->posts.ID ORDER BY $wpdb->postmeta.meta_value+0 DESC", $str_terms, $key, $posttype);
  
  // make a query to DB
  $db_response = $wpdb->get_results( $sql_query ); // and get response like Array( [0] => stdClass Object ( [ID] => $id ) )

  if( empty($db_response) )
    return;
  
  // search the current post id in array and get its index
  foreach( $db_response as $key => $response ) {
    if( $response->ID == $id )
      break;
  }
  // return array to output on the frontend
  $position = array( 'rate_pos' => $key +1, 'cat_name' => $top_term_name, 'link' => $top_term_link );

  return $position;
}

//////////////////////////////////////////////////////////////////
// Sanitize Arrays
//////////////////////////////////////////////////////////////////
function rh_sanitize_multi_arrays($data = array()) {
  if (!is_array($data) || empty($data)) {
    return array();
  }
  foreach ($data as $k => $v) {
    if (!is_array($v) && !is_object($v)) {
        if($k == 'contshortcode'){
            $data[sanitize_key($k)] = wp_kses_post($v);
        }elseif($k=='attrelpanel'){
            $data[sanitize_key($k)] = filter_var( $v, FILTER_SANITIZE_SPECIAL_CHARS );
        }else{
            $data[sanitize_key($k)] = sanitize_text_field($v);
        }
    }
    if (is_array($v)) {
      $data[$k] = rh_sanitize_multi_arrays($v);
    }
  }
  return $data;
}


//////////////////////////////////////////////////////////////////
// AMP CUSTOMIZATIONS
//////////////////////////////////////////////////////////////////


//////////////////////////////////////////////////////////////////
// 1.1 AMP HEADER META
//////////////////////////////////////////////////////////////////


add_action( 'amp_post_template_css', 'rh_amp_additional_css_styles', 11 );

function rh_amp_additional_css_styles( $amp_template ) {
    // only CSS here please...
    ?>
h1, h2, h3, h4, h5, h6, .rehub-main-font, .rehub-btn-font, .wpsm-button, .btn_offer_block, .offer_title, .rh-deal-compact-btn, .egg-container .btn, .cegg-price, .rehub-body-font, body{font-family: -apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,Oxygen-Sans,Ubuntu,Cantarell,"Helvetica Neue",sans-serif;}
<?php 
  $boxshadow = '';
  if (rehub_option('rehub_btnoffer_color')) {
    $btncolor = rehub_option('rehub_btnoffer_color');
  } 
  else {
      $btncolor = REHUB_BUTTON_COLOR;      
  }
?>
<?php if (rehub_option('enable_smooth_btn') == 1):?>
    <?php $boxshadow = hex2rgba($btncolor, 0.25);?>
.price_count, .rehub_offer_coupon, a.btn_offer_block{border-radius: 100px}
<?php elseif (rehub_option('enable_smooth_btn') == 2):?>
  .price_count, .rehub_offer_coupon, a.btn_offer_block{border-radius: 4px}
<?php endif;?>    
.rh-cat-label-title a,.rh-cat-label-title a:visited,a.rh-cat-label-title,a.rh-cat-label-title:visited{font-style:normal;background-color:#111;padding:3px 6px;color:#fff;font-size:11px;white-space:nowrap;text-decoration:none;display:inline-block;margin:0 5px 5px 0;line-height:1}.post-meta-big{margin:0 0 5px;padding:0 0 15px;color:#aaa;border-bottom:1px solid #eee;overflow:hidden}a.btn_offer_block,.rh-deal-compact-btn,.btn_block_part a.btn_offer_block,.wpsm-button.rehub_main_btn,.widget_merchant_list .buttons_col,#toplistmenu > ul li:before,a.btn_offer_block:visited,.rh-deal-compact-btn:visited,.wpsm-button.rehub_main_btn:visited
{ background: none <?php echo ''.$btncolor ?>; color: #fff; border:none;text-decoration: none; outline: 0;  
  <?php if($boxshadow) :?>border-radius: 100px;box-shadow: -1px 6px 19px <?php echo ''.$boxshadow;?>;
  <?php else:?>border-radius: 0;box-shadow: 0 2px 2px #E7E7E7;
  <?php endif; ?>
}
<?php if(function_exists('ampforwp_is_amp_endpoint')):?>
  .amp-wp-article-content {margin: 0 16px;}.amp-wp-article{margin: 1.5em auto;}
<?php else:?>
.wpsm-button.small{padding:5px 12px;line-height:12px;font-size:12px}.wpsm-button.medium{padding:8px 16px;line-height:15px;font-size:15px}.wpsm-button.big{padding:12px 24px;line-height:22px;font-size:22px}.wpsm-button.giant{padding:16px 30px;line-height:30px;font-size:30px}.wpsm_box.gray_type{color:#666;background:#f9f9f9;}.wpsm_box.red_type{color:#de5959;background:#ffe9e9;}.wpsm_box.green_type{color:#5f9025;background:#ebf6e0;}.wpsm_box.blue_type{color:#5091b2;background:#e9f7fe;}.wpsm_box.yellow_type{color:#c4690e;background:#fffdf3;}.wpsm_box.solid_border_type{border:1px solid #CCC}.wpsm_box.transparent_type{background-color:transparent}.wpsm_box{color:#363636;min-height:52px;padding: 20px 28px;margin: 0 0 30px 0;overflow:auto}.wpsm_box.warning_type{background-color:#FFF7F4;color:#A61818}.wpsm_box.standart_type{background-color:#F9F9F9;}.wpsm_box.info_type{background-color:#F0FFDE;}.wpsm_box.error_type{background-color:#FFD3D3;color:#DC0000}.wpsm_box.download_type{background-color:#E8F9FF;}.wpsm_box.note_type{background-color:#FFFCE5;}.wpsm_box.download_type i,.wpsm_box.error_type i,.wpsm_box.info_type i,.wpsm_box.note_type i,.wpsm_box.standart_type i,.wpsm_box.warning_type i{font-weight:400;font-style:normal;vertical-align:baseline;font-size:27px;float:left;margin:0 14px 10px 0}.wpsm_box.warning_type i:before{content:"❗";color:#E25B32}.wpsm_box.info_type i:before{content:"ℹ";color:#53A34C}.wpsm_box.error_type i:before{content:"❗";color:#DC0000}.wpsm_box.download_type i:before{content:"↓";color:#1AA1D6}.wpsm_box.note_type i:before{content:"ℹ";color:#555}a.wpsm-button{margin:0 5px 8px 0;cursor:pointer;display:inline-block;outline:0;background:#aaa;border:1px solid #7e7e7e;color:#fff;font-weight:700;padding:4px 10px;line-height:.8em;text-decoration:none;text-align:center;white-space:normal;box-shadow:0 1px 2px rgba(0,0,0,.2);position:relative;font-size:15px;box-sizing:border-box;font-style:normal}.wpsm-table table{border-collapse:separate;padding-bottom:1px;width:100%;margin:10px 0 20px;border-spacing:0;font-size:14px}.wpsm-table table tr td,.wpsm-table table tr th{padding:12px 15px;border-bottom:1px solid #e8e8e8;text-align:left;vertical-align:middle}.wpsm-table table tr th{background:#222;color:#FFF;font-size:15px;font-weight:700;text-transform:uppercase}.wpsm-table table tbody tr td{background:#FAFAFA}.wpsm-table table tbody tr:nth-child(2n+1) td{background:#fff}.wpsm-divider{display:block;width:100%;height:0;margin:0;background:0 0;border:none}.wpsm-divider.solid_divider{border-top:1px solid #e6e6e6}.wpsm-divider.dashed_divider{border-top:2px dashed #e6e6e6}.wpsm-divider.dotted_divider{border-top:3px dotted #e6e6e6}.wpsm-divider.double_divider{height:5px;border-top:1px solid #e6e6e6;border-bottom:1px solid #e6e6e6}.wpsm-divider.clear_divider{clear:both}.wpsm_pretty_list ul li a{display:inline-block;line-height:18px;text-decoration:none;}.darklink ul li a{color:#111}.wpsm_pretty_list ul li{position:relative;list-style-type:none;margin:0;padding:10px 20px 10px 28px;border-radius:100px}.wpsm_pretty_list.small_gap_list ul li{padding:6px 12px 6px 28px}.wpsm_pretty_list ul li:before{text-align:center;position:absolute;top:0;bottom:0;left:0;width:15px;height:15px;margin:auto;line-height:1}.wpsm_pretty_list.wpsm_pretty_hover ul li:hover{padding:10px 20px 10px 34px}.wpsm_pretty_list.small_gap_list.wpsm_pretty_hover ul li:hover{padding:6px 12px 6px 34px}.wpsm_pretty_list.wpsm_pretty_hover ul li:hover:before{left:12px}.font130 .wpsm_pretty_list ul li{padding-left:34px}.rtl .wpsm_pretty_list ul li a:before{left:auto;right:0}.rtl .wpsm_pretty_list ul li{padding:12px 28px 12px 20px}.rtl .wpsm_pretty_list.small_gap_list ul li{padding:6px 28px 6px 12px}.rtl .wpsm_pretty_list.wpsm_pretty_hover ul li:hover{padding:10px 34px 10px 20px}.rtl .wpsm_pretty_list.small_gap_list.wpsm_pretty_hover ul li:hover{padding:6px 34px 6px 12px}.rtl .wpsm_pretty_list.wpsm_pretty_hover ul li:hover:before{right:12px;left:auto}.rtl .font130 .wpsm_pretty_list ul li{padding-right:34px}.wpsm_arrowlist ul li:before{content:"→"}.wpsm_checklist ul li:before{content:"✔";color:#1abf3d}.wpsm_starlist ul li:before{content:"★"}.wpsm_bulletlist ul li:before{content:"∙"}.wpsm_pretty_hover ul li:hover:before{color:#fff}.wpsm-bar{position:relative;display:block;margin-bottom:15px;width:100%;background:#eee;}.wpsm-bar-title{position:absolute;top:0;left:0;font-weight:700;font-size:13px;color:#fff;background:#6adcfa}.wpsm-bar-title span{display:block;background:rgba(0,0,0,.1);padding:0 20px;height:28px;line-height:28px}.wpsm-bar-bar{width:0;background:#6adcfa}.wpsm-bar-percent{position:absolute;right:10px;top:0;font-size:11px;height:28px;line-height:28px;color:#444;color:rgba(0,0,0,.4)}.wpsm-clearfix:after{content:".";display:block;clear:both;visibility:hidden;line-height:0;height:0}.wpsm-titlebox{margin:0 0 30px;padding:15px 20px 12px;position:relative;border:3px solid #E7E4DF}.wpsm-titlebox>strong:first-child{background:#fff;float:left;font-size:16px;font-weight:600;left:11px;line-height:18px;margin:0 0 -9px;padding:0 10px;position:absolute;text-transform:uppercase;top:-10px}
<?php endif;?>
.rh-flex-center-align{align-items: center; display: flex;flex-direction: row;}.rh-flex-right-align{margin-left: auto}.position-relative{position:relative}.mobileblockdisplay,.mobilesblockdisplay{display:block}#content-sticky-panel {display:none}.amp-rh-article-header{margin:0 16px;}.amp-wp-article-featured-image{margin-top:10px}.floatleft{float:left}.floatright{float:right}.post-meta-big img{border-radius:50%}.post-meta-big a{text-decoration:none;color:#111}.post-meta-big span.postthumb_meta{color:#c00}.post-meta-big span.comm_count_meta svg,.post-meta-big span.postthumb_meta svg{padding-right:4px;line-height:12px;vertical-align:middle}.post-meta-big span.postthumb_meta svg path{fill:#c00}.post-meta-big span.comm_count_meta svg path{fill:#999}.authortimemeta{line-height:18px;font-weight:700}.date_time_post{font-size:13px;font-weight:400}.postviewcomm{line-height:28px;font-size:14px}.amp-rh-title{font-size:28px;line-height:34px;margin:0 0 25px}strong{font-weight:700}.single_price_count del{opacity:.3;font-size:80%}.btn_block_part a.btn_offer_block,.rehub_quick_offer_justbtn a.btn_offer_block{display:block;padding:10px 16px;font-size:16px;font-weight:700;text-transform:uppercase;margin-bottom:10px}.rehub_offer_coupon{display:block;padding:7px 14px;border:1px dashed #888;text-align:center;position:relative;font-size:14px;clear:both}.single_priced_block_amp{text-align:center}.single_price_count{font-size:22px;margin-bottom:10px;font-weight:700;display:block}.rehub_main_btn,.wpsm-button.rehub_main_btn,a.btn_offer_block{padding:14px 20px;display:inline-block;position:relative;line-height:18px;font-weight:700; text-align:center; font-size:20px; display:block}.text-center{text-align:center}.mr5{margin-right:5px}.mr10{margin-right:10px}.mr15{margin-right:15px}.mr20{margin-right:20px}.mr25{margin-right:25px}.mr30{margin-right:30px}.ml5{margin-left:5px}.ml10{margin-left:10px}.ml15,.ml20{margin-left:20px}.ml25{margin-left:25px}.ml30{margin-left:30px}.mt10{margin-top:10px}.mt5{margin-top:5px}.mt15{margin-top:15px}.mt20{margin-top:20px}.mt25{margin-top:25px}.mt30{margin-top:30px}.mb0{margin-bottom:0}.mb5{margin-bottom:5px}.mb10{margin-bottom:10px}.mb15{margin-bottom:15px}.mb20{margin-bottom:20px}.mb25{margin-bottom:25px}.mb30,.mb35{margin-bottom:30px}.mt0{margin-top:0}.ml0{margin-left:0}.mr0{margin-right:0}.amp-wp-article-content .aff_tag amp-img,.amp-wp-article-content .widget_merchant_list .merchant_thumb amp-img,.amp-wp-article-content a.btn_offer_block .mtinside amp-img{display:inline-block;margin:0 4px;vertical-align:middle}.product_egg .deal-box-price{font-size:27px;line-height:40px;margin-bottom:10px}.aff_tag{font-size:14px}.priced_block{margin-bottom:5px; margin-top:10px}
<?php if(rehub_option('amp_default_css_disable') == ''):?>
.flowhidden,.pros_cons_values_in_rev,.rate_bar_wrap,.review-top,.rh-cartbox{overflow:hidden}.widget_merchant_list .buttons_col{border-radius:0}.rh-tabletext-block-heading,.rh-tabletext-block-left,.rh-tabletext-block-right{display:block;margin-bottom:25px}ins{text-decoration:none}.redcolor{color:#b00}.greencolor{color:#009700}.whitecolor{color:#fff}.tabledisplay{display:table;width:100%}.rowdisplay{display:table-row}.celldisplay{display:table-cell;vertical-align:middle}.img-thumbnail-block,.inlinestyle{display:inline-block}.fontbold{font-weight:700}.lineheight20{line-height:20px}.lineheight15{line-height:15px}.border-top{border-top:1px solid #eee}.font90,.font90 h4{font-size:90%}.font80,.font80 h4{font-size:80%}.font70,.font70 h4{font-size:70%}.font110,.font110 h4{font-size:110%}.font120{font-size:120%}.font130{font-size:130%}.font140{font-size:140%}.font150{font-size:150%}.font250{font-size:250%}.pr5{padding-right:5px}.pr15{padding-right:15px}.rh-cartbox{box-shadow:rgba(0,0,0,.15) 0 1px 2px;background:#fff;padding:20px;position:relative;border-top:1px solid #efefef}.no-padding,.rh-cartbox.no-padding{padding:0}.rh-line{height:1px;background:#ededed;clear:both}.rh-line-right{border-right:1px solid #ededed}.rh-line-left{border-left:1px solid #ededed}.fontnormal,.fontnormal h4{font-weight:400}.wpsm-button.rehub_main_btn.small-btn{font-size:17px;padding:9px 16px;text-transform:none;margin:0}.clearfix:after,.clearfix:before{content:"";display:table}.clearfix:after{clear:both}a.rh-cat-label-title.rh-dealstore-cat{background-color:green}.floatright.postviewcomm{margin-top:15px;float:none}.re-line-badge{color:#fff;padding:5px 10px;background:#77B21D;text-shadow:0 1px 0 #999;font-weight:bold; font-size: 10px;line-height:14px;position:relative;text-transform:uppercase;display:inline-block;z-index:999}.re-line-badge.re-line-small-label{display:inline-block;padding:3px 6px;margin:0 5px 5px 0;text-align:center;white-space:nowrap;font-size:11px;line-height:11px}.rh-cat-list-title{margin:0 0 8px;line-height:11px;display:inline-block}.rate-bar{position:relative;display:block;margin-bottom:34px;width:100%;background:#ddd;height:14px;}.rate-bar-percent,.rate-bar-title{position:absolute;top:-21px;font-size:14px}.rate-bar-title{left:0}.rate-bar-title span{display:block;height:18px;line-height:18px}.rate-bar-bar{height:14px;width:0;background:#E43917}.rate-bar-percent{right:0;height:18px;line-height:18px;font-weight:700}.rate_bar_wrap{clear:both;background:#f2f2f2;padding:20px;margin-bottom:25px;border:1px dashed #aaa;box-shadow:0 0 20px #F0F0F0}.review-top .overall-score{background:#E43917;width:100px;text-align:center;float:left;margin:0 20px 10px 0}.review-top .overall-score span.overall{font-size:52px;color:#FFF;padding:8px 0;display:block;line-height:52px}.review-top .overall-score span.overall-text{background:#000;display:block;color:#FFF;font-weight:700;padding:6px 0;text-transform:uppercase;font-size:11px}.review-top .overall-score .overall-user-votes{background-color:#111;color:#fff;font-size:11px;line-height:11px;padding:8px 0}.review-top .review-text span.review-header{font-size:32px;font-weight:700;color:#000;line-height:32px;display:block;margin-bottom:9px}.review-top .review-text p{margin:0}.rate_bar_wrap_two_reviews .l_criteria{margin:0 0 35px;padding:8px 0;overflow:hidden}.rate_bar_wrap_two_reviews .l_criteria span.score_val{text-align:right;float:right;font:36px/36px Arial}.rate_bar_wrap_two_reviews .score_val{border-bottom:3px solid #E43917}.rate_bar_wrap_two_reviews .l_criteria span.score_tit{font-size:16px;line-height:36px;text-transform:uppercase;float:left}.user-review-criteria .rate-bar-bar{background-color:#ff9800}.rate_bar_wrap_two_reviews .user-review-criteria .score_val{border-bottom:3px solid #ff9800}.rate_bar_wrap .review-criteria{margin-top:20px;border-top:1px dashed #d2d2d2;border-bottom:1px dashed #d2d2d2;padding:40px 0 0;}.rate_bar_wrap_two_reviews .review-criteria{border:none;padding:0;margin-top:0}.review-header{display:block;font-size:20px;font-weight:700}.rate_bar_wrap .your_total_score .user_reviews_view_score{float:right}.rate-bar-bar.r_score_1{width:10%}.rate-bar-bar.r_score_2{width:20%}.rate-bar-bar.r_score_3{width:30%}.rate-bar-bar.r_score_4{width:40%}.rate-bar-bar.r_score_5{width:50%}.rate-bar-bar.r_score_6{width:60%}.rate-bar-bar.r_score_7{width:70%}.rate-bar-bar.r_score_8{width:80%}.rate-bar-bar.r_score_9{width:90%}.rate-bar-bar.r_score_10{width:100%}.pros_cons_values_in_rev{border-bottom:1px dashed #d2d2d2;margin:20px 0 10px;padding:0 0 10px}.wpsm_cons .title_cons,.wpsm_pros .title_pros{margin:0 0 15px;font-size:16px;font-style:italic;font-weight:700}.rating_bar,.wpsm-table{overflow:auto}.wpsm_pros .title_pros{color:#58c649}.wpsm_cons .title_cons{color:#f24f4f}.rating_bar{margin:15px 0 0}.widget_merchant_list{border:3px solid #eee;padding:1px;background:#fff;line-height:22px}.table_merchant_list{display:table-row}.table_merchant_list>div{display:table-cell;margin:0;vertical-align:middle}.widget_merchant_list .merchant_thumb{font-size:13px;border-bottom:1px solid #eee}.table_merchant_list a{display:block;text-decoration:none;color:#111;padding:8px 5px}.widget_merchant_list .price_simple_col{text-align:center;background-color:#f5f9f0;border-bottom:1px solid #eee;font-size:14px;font-weight:700}ul.slides{margin:0 0 20px}ul.slides li{list-style:none}.carousel-style-deal .deal-item-wrap .deal-detail h3{font-size:16px;line-height:20px}.aff_offer_links .table_view_block,.egg_grid .small_post{padding:15px 10px;border-top:1px dotted #ccc}.aff_offer_links .table_view_block:first-child,.egg_grid .small_post:first-child{border-top:none;box-shadow:none}.egg_grid .small_post .affegg_grid_title{font-size:16px;line-height:22px;margin-bottom:25px;font-weight:700}
.border-grey-bottom{border-bottom: 1px solid #eee;}.pb15{padding-bottom: 15px}.pt15{padding-top: 15px}.rh_list_mbl_im_left .rh_listcolumn_image {float: left;min-width: 120px;max-width: 120px;padding:0 15px}.rtl .rh_list_mbl_im_left .rh_listcolumn_image {float: right;}.rh_list_mbl_im_left > .mobileblockdisplay > div:not(.rh_listcolumn_image){margin: 0 0 12px 130px; text-align: left;}.rtl .rh_list_mbl_im_left > .mobileblockdisplay > div:not(.rh_listcolumn_image){margin: 0 130px 12px 0; text-align: right;}.widget_merchant_list .buttons_col a{color:#fff;font-weight:700;padding:8px 10px;white-space:nowrap;text-align:center}.sale_a_proc{z-index:9;width:36px;height:36px;border-radius:50%;background-color:#4D981D;font:12px/36px Arial;color:#fff;display:block;text-decoration:none;text-align:center;position:absolute;top:10px;left:10px}.best_offer_badge{color:red}.small_post figure{position:relative}.amp-section-thumbs,.amp-section-videos{padding:30px 0}.amp-section-thumbs img{height:auto}.amp-wp-article-content .amp-section-thumbs amp-img{border:1px solid #eee;margin:2px;max-width:100px}.rehub-amp-subheading svg{vertical-align:middle;margin:0 5px;display:inline-block}.rehub-amp-subhead{vertical-align:middle;display:inline-block;font-weight:700;font-size:18px;line-height:25px}.-amp-accordion-header{padding:14px}.masonry_grid_fullwidth.egg_grid,.rehub_feat_block{margin-bottom:25px;box-shadow:0 2px 8px #f1f1f1;padding:20px;border:1px solid #f4f4f4}.additional_line_merchant,.popup_cont_div,.price-alert-form-ce,.pricealertpopup-wrap,.r_show_hide,.rehub_woo_tabs_menu,.rh-table-price-graph{display:none}.price_count del,.price_count strike,.price_simple_col strike{opacity:.3}.yes_available{color:#4D981D}.egg-logo amp-img,.widget_logo_list .offer_thumb amp-img{max-height:50px;max-width:80px}.table_div_list>a{display:table;width:100%;float:none;border: 1px solid #ddd;vertical-align:middle;border-radius:100px;text-decoration:none;margin-bottom:10px}.table_div_list img{max-height: 30px;vertical-align:middle}.table_div_list>a>div{display:table-cell;margin:0;vertical-align:middle;}.widget_logo_list .offer_thumb{width:110px;text-align:center;border-right:1px solid #eee;padding:10px 15px;}.widget_logo_list .price_simple_col{text-align:left;font-size:16px;color:#111;font-weight:bold;padding:8px 15px;line-height:20px;width:auto;}.widget_logo_list .buttons_col{width:40px;text-align:center;}
.widget_logo_list .buttons_col i{font-size:20px}.col_wrap_two .product_egg .col_item .buttons_col{margin-bottom:25px}a.btn_offer_block .mtinside{text-align:right;position:absolute;bottom:-19px;left:0;color:#ababab;text-shadow:none;font:11px/11px Arial;text-transform:none}ul.featured_list{margin:15px;text-align:left;padding:0}.rh_opacity_7{opacity: 0.7}.rh_opacity_5{opacity: 0.5}.rh_opacity_3{opacity: 0.3}.wpsm_box{display:block;padding:15px;margin:0 0 20px;font-size:15px}.wpsm-button.green{background:#43c801;border-color:#43c801}.wpsm-button.white{border:1px solid #ccc;background-color:#fff;color:#111;text-shadow:none;box-shadow:0 1px 1px rgba(0,0,0,.1)}.wpsm-button.left{float:left}.wpsm-button.right{float:right;margin-right:0;margin-left:5px}.wpsm-button.small i{padding-right:5px}.wpsm-button.medium i{padding-right:8px}.wpsm-button.big i{padding-right:10px}.wpsm-button.wpsm-flat-btn{border-radius:0;font-weight:400}.wpsm-bar-title,.wpsm-bar-title span{border-top-left-radius:3px;border-bottom-left-radius:3px}.wpsm-bar,.wpsm-bar-bar{border-radius:3px;height:28px}.popup_cont_inside{padding:20px}a.add_user_review_link{color:#111}.amp-wp-article .comment-button-wrapper a{background:#43c801;border-color:#43c801;box-shadow:0 1px 2px rgba(0,0,0,.2);color:#fff;font-size:16px}amp-sidebar .toggle-navigationv2 ul li a{font-size:15px;line-height:22px}#toplistmenu ul{counter-reset:item;list-style:none;box-shadow:0 4px 12px #e0e0e0;margin:0 4px 12px;border:1px solid #ddd;border-top:none}#toplistmenu ul li{list-style:none;padding:15px 15px 15px 5px;margin:0;border-top:1px solid #ddd}.autocontents li.top{counter-increment:list;counter-reset:list1;font-size:105%}#toplistmenu>ul li:before{border-radius:50%;color:#fff;content:counter(item);counter-increment:item;float:left;height:25px;line-height:25px;margin:3px 20px 20px 15px;text-align:center;width:25px;font-weight:700;font-size:16px; position:static}.autocontents li.top:before{content:counter(list) '. '}#toplistmenu ul li a{font-size:18px;line-height:14px;border-bottom:1px dotted #111;text-decoration:none}.egg-listcontainer{text-align:center}.egg-item .cegg-price-row .cegg-price{font-size:32px;line-height:30px;white-space:nowrap;font-weight:700;margin-bottom:15px;display:inline-block}.text-right{text-align:right}.egg-container .egg-listcontainer .row-products{border-bottom:1px solid #ddd;margin:0;padding:15px 0}.egg-container .h4,.egg-container h4{font-size:1.2em}.egg-container .text-muted{color:#777;font-size:.9em;line-height:.9em}.amp-wp-article-content .offer_price .cegg-thumb amp-anim,.amp-wp-article-content .offer_price .cegg-thumb amp-img{display:inline-block;margin:0 0 15px}.rh_comments_list{margin:2.5em 16px}.rh_comments_list ul{margin:0}.comment-meta{font-size:13px;margin-bottom:10px}.comment-content{padding:15px;background:#f7f7f7}.user_reviews_view_criteria_line{overflow:hidden;margin:0 0 4px}.rh_comments_list>ul>li{background:#FFF;border:1px solid #eee;box-shadow:0 1px 1px #ededed;height:auto;max-width:100%;position:relative;list-style:none;margin:0 0 18px;padding:12px 20px 20px}.user_reviews_average{font-size:115%;overflow:hidden;display:block;font-weight:700;margin-bottom:15px}.comment-content-review{margin:25px 0 10px;font-size:13px}.user_reviews_view_pros{margin-top:20px}.user_reviews_view_pros .user_reviews_view_pc_title{color:#00a100}.user_reviews_view_cons .user_reviews_view_pc_title{color:#c00}.cons_comment_item,.pros_comment_item{list-style:disc;margin:0 0 0 15px}.rh_comments_list .rate-bar,.rh_comments_list .rate-bar-bar{height:9px;clear:both;margin:0}.relatedpost .related_posts ol li{border:1px solid #ededed;padding:15px 18px;box-sizing:border-box}.relatedpost .no_related_thumbnail{padding:15px 18px}.relatedpost .related_posts h3{font-size:18px}.amp-wp-footer{background:#f7f7f7;border-color:#eee}#pagination .next{margin-bottom: 20px}.val_sim_price_used_merchant{font-size: 10px; display: block;}.table_merchant_list .val_sim_price_used_merchant{font-size: 9px;}.cegg-rating > span {display: inline-block;position: relative;font-size: 30px;color: #F6A123;}.product_egg .image{position:relative}mark{background-color: #fed700; color: #000}
<?php endif;?>
<?php if(rehub_option('amp_custom_css')):?>
    <?php echo rehub_kses(rehub_option('amp_custom_css')); // amphtml content; no kses ?>
<?php endif;?>

    <?php
}

// Logo
add_action( 'amp_post_template_css', 'rh_amp_additional_css_logo' );
function rh_amp_additional_css_logo( $amp_template ) {
  if ( rehub_option( 'rehub_logo_amp' ) && !function_exists('ampforwp_custom_template')) : 
  ?>
   .amp-wp-header a {background-image: url( '<?php echo rehub_option( 'rehub_logo_amp' ); ?>' );background-repeat: no-repeat;background-size: contain;background-position: center top;display: block;height: 32px;width: 100%;text-indent: -9999px;}
    <?php endif;
}

// Add meta description from Seo By Yoast
add_filter( 'amp_post_template_metadata', 'rehub_amp_update_metadata', 10, 2 );
function rehub_amp_update_metadata( $metadata, $post ) {
    if ( class_exists('WPSEO_Frontend') ) {
        $front = WPSEO_Frontend::get_instance();
        $desc = $front->metadesc( false );
        if ( $desc ) {
            $metadata['description'] = $desc;
        }
    }
    return $metadata;
}

add_action('ampforwp_post_before_design_elements', 'rehub_amp_add_custom_before_title' );
if(!function_exists('rehub_amp_add_custom_before_title')){
    function rehub_amp_add_custom_before_title(){
        if(rehub_option('amp_custom_in_header_top')):
            echo '<div class="amp-wp-article-content">'.do_shortcode(rehub_option('amp_custom_in_header_top')).'</div><div class="clearfix mb20"></div>';    
        endif;
    }    
}

add_action('ampforwp_after_post_content', 'rehub_amp_add_custom_in_footer' );
if(!function_exists('rehub_amp_add_custom_in_footer')){
    function rehub_amp_add_custom_in_footer(){
        if(rehub_option('amp_custom_in_footer')):
            echo do_shortcode(rehub_option('amp_custom_in_footer')).'<div class="clearfix"></div>';   
        endif;
    }    
}

add_action('amp_post_template_footer', 'rehub_amp_add_custom_footer_section' );
if(!function_exists('rehub_amp_add_custom_footer_section')){
    function rehub_amp_add_custom_footer_section(){
        if(rehub_option('amp_custom_in_footer_section')):
            echo rehub_option('amp_custom_in_footer_section');   
        endif;
    }    
}

add_action('amp_post_template_head', 'rehub_amp_add_custom_header_section' );
if(!function_exists('rehub_amp_add_custom_header_section')){
    function rehub_amp_add_custom_header_section(){
        if(rehub_option('amp_custom_in_head_section')):
            echo rehub_option('amp_custom_in_head_section');    
        endif;
    }    
}

add_action('amp_post_template_head', 'rehub_amp_add_custom_scripts' );
if(!function_exists('rehub_amp_add_custom_scripts')){
    function rehub_amp_add_custom_scripts(){
    ?>     
        <?php
            global $post;
            $postid = $post->ID;
            if(!$postid || function_exists( 'ampforwp_is_amp_endpoint' ) ) return;
        ?>
        <?php 
            $post_image_gallery = get_post_meta( $postid, 'rh_post_image_gallery', true );
            $post_image_videos = get_post_meta( $postid, 'rh_post_image_videos', true );
        ?>
        <?php if(!empty($post_image_videos) || !empty($post_image_gallery) ) :?>
            <script async custom-element="amp-accordion" src="https://cdn.ampproject.org/v0/amp-accordion-0.1.js"></script>
        <?php endif;?>      
        <?php if(!empty($post_image_gallery) ) :?>
            <script async custom-element="amp-image-lightbox" src="https://cdn.ampproject.org/v0/amp-image-lightbox-0.1.js"></script>
        <?php endif;?>
        <?php if(!empty($post_image_videos) ) :?>
            <script async custom-element="amp-youtube" src="https://cdn.ampproject.org/v0/amp-youtube-0.1.js"></script>
        <?php endif;?>
        <script async custom-element="amp-social-share" src="https://cdn.ampproject.org/v0/amp-social-share-0.1.js"></script>
    
    <?php
    }    
}

add_filter( 'amp_post_template_file', 'rehub_amp_delete_custom_title_section', 11, 3 ); //Delete AMP custom plugin title section
if(!function_exists('rehub_amp_delete_custom_title_section')){
    function rehub_amp_delete_custom_title_section( $file, $type, $post ) {
        if ( 'ampforwp-the-title' === $type ) {
            $file = rh_locate_template('amp/title-section.php');
        }
        elseif ( 'ampforwp-meta-info' === $type ) {
            $file = '' ;
        }   
        elseif ( 'ampforwp-comments' === $type ) {
            $file = rh_locate_template('amp/comments.php');
        }         
        return $file;
    }
}

add_action('ampforwp_before_post_content', 'rehub_amp_add_custom_before_content' );
if(!function_exists('rehub_amp_add_custom_before_content')){
    function rehub_amp_add_custom_before_content(){
        include(rh_locate_template('amp/before-content.php'));
    }    
}

if(!function_exists('rh_generate_incss')) {
    function rh_generate_incss($type='', $random = '', $atts=array()) {  
        $output = '<style scoped>';
        if($type === 'masonry'){
            $output .= '
                .masonry_grid_fullwidth { margin-bottom: 20px; display: flex;flex-wrap: wrap;flex-direction: row; }
                .small_post { padding: 20px 25px;position: relative; float: left; background-color: #fff; display: flex !important;flex-wrap: wrap;justify-content: space-between;flex-direction: row;}
                .masonry_grid_fullwidth .small_post { border: 1px solid #e3e3e3; }
                .masonry_grid_fullwidth.loaded .small_post { display: block; }
                .masonry_grid_fullwidth.loaded { background: none transparent; min-height: 10px; padding-bottom: 20px }
                .small_post > p { font-size: 14px; color: #666; margin-bottom: 15px; line-height: 18px }
                .small_post h2 {font-size: 20px; line-height: 22px; }
                .small_post .meta, .small_post h2 { clear: both }
                .small_post figure > a { width: 100%; }
                .small_post figure > a img { width: 100%; height: auto; }
                .small_post .wprc-container{position: absolute; z-index: 999; bottom:0; left: 0; opacity: 0; transition: all 0.4s ease; margin: 0 !important}
                .small_post:hover .wprc-container{opacity: 1}
                .small_post .wprc-container .wprc-switch{ float: left;}
                .small_post .wprc-content img.loading-img{width: auto !important; height: auto !important;}
                .small_post:hover .social_icon_inimage{ right: 10px; opacity: 1}
                .small_post:hover .favour_in_image{opacity: 1}
                .social_icon_inimage{ position: absolute; z-index: 10; top:50px; right: -100px; opacity: 0; transition: all 0.4s ease;}
                .social_icon_inimage span.share-link-image{ width: 50px; height: 50px; line-height: 50px; display:block; margin-bottom: 5px; font-size: 24px}
                .small_social_inimage.social_icon_inimage span.share-link-image{ width: 38px; height: 38px; line-height: 38px; font-size: 19px;}
                .social_icon_inimage span:hover{ top:0; right: 2px}
                @media screen and (max-width: 1023px) and (min-width: 768px) {
                .col_wrap_three .small_post, .col_wrap_fourth .small_post, .col_wrap_fifth .small_post {width: 47%; margin: 0 1.5% 20px;}
                }
                @media (max-width: 767px) {
                .social_icon_inimage span.share-link-image{width: 35px; height: 35px; line-height: 35px; font-size: 18px; margin-bottom: 15px}
                .small_post .social_icon_inimage {right: 10px;opacity: 1;}
                .small_post .favour_in_image{opacity: 1}
                .small_post .wprc-container {opacity: 1;}
                }
            ';           
        }
        else if($type === 'offergrid'){
            $output .= '
              .offer_grid .sale_tag_inwoolist .sale_letter{font-size: 33px;line-height:33px}.offer_grid .sale_tag_inwoolist{width: 130px}
              .offer_grid figure {position: relative; text-align: center; margin: 0 auto 15px auto; overflow: hidden;  vertical-align: middle; }
              .offer_grid.coupon_grid figure img {height: 80px;}
              .offer_grid figure img{width: auto;display: inline-block;transition: all ease-in-out .2s;}
              .offer_grid.col_item{border: 1px solid rgba(159,159,159, 0.35); padding: 12px; transition: box-shadow 0.4s ease;}
              .offer_act_enabled.col_item{padding-bottom: 53px}
              .offer_grid .price_count{font-weight: bold; font-size:17px;padding: 0;}
              .offer_grid .price_count del {display: block;font-size: 13px;color: #666;vertical-align: top;font-weight: normal; text-align: left;}
              .offer_grid .rehub_offer_coupon span{ font-size: 14px; text-transform: none;}
              .offer_grid h3 { height: 36px; font-size: 15px; line-height:18px; font-weight:normal !important }
              .col_wrap_fifth .offer_grid h3{font-size: 14px;}
              .col_wrap_six .offer_grid h3{font-size: 13px; line-height:16px; height: 32px;}
              .offer_grid:hover{   box-shadow: 0 0 20px #ddd;}
              .offer_grid .aff_tag img{max-width: 60px; }
              .offer_grid .cat_link_meta a{color: #555; text-transform: uppercase; font-size: 11px}
              .offer_grid .date_ago{font-size: 11px}
              .offer_grid{ background-color: #fff}
              .offer_grid span.cat_link_meta:before{display: none;}
              .offer_grid .priced_block .btn_offer_block, .offer_grid .post_offer_anons{display: block;}
              .vendor_for_grid .admin img{border-radius: 50%; max-width: 22px; max-height: 22px}
              .date_for_grid i{margin: 0 3px }
              .date_for_grid{color: #999;}
              .re_actions_for_grid {height: 38px;position: absolute;left: 0;right: 0;bottom: 1px;z-index: 2;}
              .re_actions_for_grid .btn_act_for_grid {width: 33.33%;height: 38px;float: left;line-height: 38px;color: #656d78;text-align: center;display: block;padding: 0;position: relative;font-size: 14px}
              .re_actions_for_grid.two_col_btn_for_grid .btn_act_for_grid{width: 50%}
              .btn_act_for_grid:hover{background-color: #f7f7f7}
              .offer_grid_com .btn_act_for_grid .table_cell_thumbs, .offer_grid_com .btn_act_for_grid:hover .thumbscount{display: none;}
              .btn_act_for_grid:hover .table_cell_thumbs{display: inline;}
              .btn_act_for_grid .thumbplus, .btn_act_for_grid .thumbminus{margin-bottom: 3px}
              .btn_act_for_grid .thumbscount:before {content: "\e86d";line-height: 38px;display: inline-block;margin-right: 8px;}
              .re_actions_for_grid .thumbscount{float: none; margin: 0; line-height: 38px; font-size: inherit;}
              .comm_number_for_grid:before {content: "\e932";margin-right: 5px;}
              .re_actions_for_grid .thumbplus.heartplus{font-size: 15px}
              .offer_grid_com .meta_for_grid{overflow: hidden; line-height: 18px}
              .offer_grid_com .store_for_grid{text-align: left;line-height: 12px;}
              .offer_grid .info_in_dealgrid {margin-bottom: 7px;}
              .offer_grid .not_masked_coupon{margin: 10px auto 0 auto;font-size: 12px;background: #e7f9dd;padding: 6px;border-color: #42A40D;color: #37840D;display: block;}
              .no_padding_wrap .offer_grid.col_item{border: 1px solid #eee; border-top: none; border-left: none}
              .no_padding_wrap .eq_grid{border: 1px solid #eee; border-right: none; border-bottom: none; padding: 0}
              .offer_grid.mobile_grid .price_count{font-size:20px}
              .offer_grid.mobile_grid .price_count del{opacity:1; color:#dd7064; display:inline-block; margin: 0 3px}
              .offer_grid.mobile_grid .rh_notice_wrap{font-size: 75% !important;font-weight: normal !important;opacity: 0.5;}
              .offer_grid.mobile_grid.offer_grid .cat_link_meta a{text-transform:none}
              .mobile_grid .two_col_btn_for_grid{margin-top:25px; position:absolute; bottom:0; left:0; right:0; padding:0 12px 12px 12px}
              .mobile_grid.offer_grid .info_in_dealgrid{margin-bottom:0}
              .mobile_grid.offer_grid h3 { height: 54px; display: -webkit-box;overflow: hidden;-webkit-box-orient: vertical;-webkit-line-clamp: 3; }

              @media(max-width: 1024px){
                .offer_grid_com .btn_act_for_grid .table_cell_thumbs, .offer_grid_com .btn_act_for_grid:hover .thumbscount{display: inline;}
                .btn_act_for_grid .thumbscount:before{display: none;}
                .btn_act_for_grid .table_cell_thumbs .thumbplus{margin-right: 8px}
                .rtl .btn_act_for_grid .table_cell_thumbs .thumbplus{margin-left: 8px; margin-right: 0}
              }
              @media(max-width: 767px){
                .coupon_grid .rh_notice_wrap{height: 20px}
                .coupon_grid .grid_desc_and_btn{ text-align:center; border-top: 1px dashed #ccc; padding-top: 15px; text-align: center;}
              }
              @media (max-width: 567px){
                .mobile_compact_grid.col_item {width: 100% !important;margin: 0 0 14px 0 !important;}
                .mobile_compact_grid figure{float: left;width: 110px !important; margin: 0 15px 8px 0 !important;}
                .offer_grid figure img, figure.eq_figure img{height:120px;}
                .mobile_compact_grid .grid_onsale{padding:1px 5px; font-size:11px}
                .mobile_compact_grid .grid_desc_and_btn{float: left; width: calc(100% - 130px) !important; border-top:none !important; padding-top:0 !important;text-align: inherit !important;}
                .mobile_compact_grid .priced_block{margin: 0}
                .mobile_compact_grid .priced_block .btn_offer_block{display: block; margin: 0 0 14px 0}
                .mobile_compact_grid.offer_grid h3{height: auto; min-height: 1px; margin: 0 0 5px 0 !important}
                .mobile_compact_grid .rehub_offer_coupon{left: 0; width: 100%; margin: 10px 0;}
                .mobile_compact_grid .priced_block .btn_offer_block{padding: 10px 12px}
                .mobile_compact_grid .meta_for_grid{clear: both;}
                .mobile_compact_grid .priced_block .btn_offer_block:not(.coupon_btn):before{top: 10px}
                .rtl .mobile_compact_grid figure{float: right; margin: 0 0 8px 15px !important;}
                .rtl .mobile_compact_grid .grid_desc_and_btn{float: right;}

                .mobile_grid.mobile_compact_grid.offer_grid h3{font-size:14px}
                .offer_grid.mobile_grid .price_count{font-size:18px}
                .offer_grid.mobile_grid.col_item{padding:7px !important}
                .offer_grid.mobile_grid .re_actions_for_grid{display:none}
                .offer_grid.mobile_grid .thumbplus, .offer_grid.mobile_grid .thumbminus{border:none}
                .offer_grid.mobile_grid .thumbminus{margin-right:4px}
                .mobile_grid .two_col_btn_for_grid{margin-top:5px; position:static; padding:0}
                .mobile_grid.mobile_compact_grid figure img{height:100px}

              }
            ';           
        }
        else if($type === 'gridmart'){
            $output .= '.grid_mart .product{padding: 12px 0 55px 0;border: 1px solid #eee;background-color: #fff;}.grid_mart .no_btn_enabled.product{padding: 12px 0 5px 0;}.grid_mart .product img{max-height:175px}.grid_mart .rh_woo_star span{font-size:15px !important}.grid_mart .rh_woo_star span.ml10{font-size:13px !important}.grid_mart .rh-custom-quantity input.minus-quantity, .grid_mart .rh-custom-quantity input.plus-quantity{width:30px; height:30px; line-height:30px; border:none; font-weight:normal}.grid_mart .quantity.rh-custom-quantity input.qty{width:20px; height:30px; line-height:30px; border:none;font-weight:normal;font-size: 15px;}.grid_mart .rh-woo-quantity .rh-custom-quantity{border: 1px solid #eee;border-radius: 4px;}.grid_mart .woo_grid_compact:hover{box-shadow:none; border:none}.grid_mart .button_action{left:auto; right:10px}.woocommerce .grid_mart .product .price{font-weight:normal; font-size:22px; line-height:22px}.woocommerce .grid_mart .product .pricevariable .price{font-size:17px;}.grid_mart .text-clamp-2{height:40px;line-height:20px}.grid_mart_content{padding:0 15px}@media (max-width:500px){.grid_mart_content{padding:0 10px}.grid_mart .woo_grid_compact{padding-left:10px !important;padding-right:10px !important}.woocommerce .grid_mart .product .price{font-size:19px}.woocommerce .grid_mart .product .pricevariable .price{font-size:15px;}}
       
            ';           
        }
        else if($type === 'threecol'){
            $output .= '
              .news_in_thumb figure:before {bottom: 0;content: "";display: block;height: 80%;width: 100%;position: absolute;z-index: 1;pointer-events: none;background: linear-gradient(to bottom,rgba(0,0,0,0) 0%,rgba(0,0,0,0.6) 100%);transition: 0.5s;}
              .news_in_thumb .text_in_thumb{position: absolute;bottom: 0px;color: #ffffff; z-index: 9; white-space:normal;}
              .news_in_thumb:hover .text_in_thumb{padding-bottom: 25px}
              .news_in_thumb:hover figure:before{opacity: 0.8}
              .wpsm_three_col_posts{overflow: hidden; position: relative; margin-bottom: 25px}
              .wpsm_three_col_posts .col-item{ width: 32.66%; position: relative; z-index: 2}
              .wpsm_three_col_posts .col-item figure img{ width: 100%; max-height: 240px}
              .wpsm_three_col_posts .custom_col_label{ position: absolute; left: 20px; top:20px;z-index: 9;}
              @media(max-width: 767px){
                .wpsm_three_col_posts .col-item figure {height: 150px;}
              }
              @media(max-width: 550px){ 
                .wpsm_three_col_posts .col-item figure{margin-bottom: 0; height: 180px}
              }
            ';           
        }
        else if($type === 'headertopline'){
            $output .= '
              .header-top { border-bottom: 1px solid #eee; min-height: 30px; overflow: visible;  }
              .header-top .top-nav a { color: #111111; }
              .header-top .top-nav li { float: left; font-size: 12px; line-height: 14px; position: relative;z-index: 99999999; }
              .header-top .top-nav > ul > li{padding-left: 13px; border-left: 1px solid #666666; margin: 0 13px 0 0;}
              .header-top .top-nav ul { list-style: none; }
              .header-top .top-nav a:hover { text-decoration: underline }
              .header-top .top-nav li:first-child { margin-left: 0px; border-left: 0px; padding-left: 0; }
              .top-nav ul.sub-menu{width: 160px;}
              .top-nav ul.sub-menu > li > a{padding: 10px;display: block;}
              .top-nav ul.sub-menu > li{float: none; display: block; margin: 0}
              .top-nav ul.sub-menu > li > a:hover{background-color: #f1f1f1; text-decoration: none;}
              .header_top_wrap .icon-in-header-small{float: right;font-size: 12px; line-height:12px;margin: 10px 7px 10px 7px}
              .header-top .top-nav > ul > li.menu-item-has-children > a:before{font-size: 12px}
              .header-top .top-nav > ul > li.menu-item-has-children > a:before { font-size: 14px; content: "\f107";margin: 0 0 0 7px; float: right; }
              .top-nav > ul > li.hovered ul.sub-menu{top: 22px}
              .top-nav > ul > li.hovered ul.sub-menu { opacity: 1; visibility: visible;transform: translateY(0); left: 0; top: 100% }
              .header_top_wrap.dark_style { background-color: #000; width: 100%; border-bottom: 1px solid #3c3c3c; color: #ccc }
              .header_top_wrap.dark_style .header-top a.cart-contents, .header_top_wrap.dark_style .icon-search-onclick:before {color: #ccc}
              .header_top_wrap.dark_style .header-top { border: none;}
              #main_header.dark_style .header-top{border-color: rgba(238, 238, 238, 0.22)}
              .header_top_wrap.dark_style .header-top .top-nav > ul > li > a { color: #b6b6b6 }
            ';           
        }
        else if($type === 'woobreadcrumbs'){
            $output .= '
              nav.woocommerce-breadcrumb {font-size: 14px;margin: 5px 0 30px 0; line-height: 18px;}
              nav.woocommerce-breadcrumb a{text-decoration: none;color:#111}
              .woocommerce-breadcrumb span.delimiter {margin: 0 12px;}
              .woocommerce-breadcrumb span.delimiter+a {padding: 4px 8px;background-color: #f5f5f5;border-radius: 3px;color:#111 !important; display: inline-block;margin-bottom: 5px; line-height:13px;}
            ';           
        }
        else if($type === 'icontoolbar'){
            $output .= '
                #rhNavToolWrap{position:fixed; background:white; bottom:0;left:0;right:0;box-shadow: 0 0 9px rgb(0 0 0 / 12%); z-index:100000}
                #rhNavToolbar{height:55px;}
                #rhNavToolWrap .user-dropdown-intop-menu{left:0;right:0;bottom:100%;border-width: 1px 0 0 0;}
                #rhNavToolWrap .user-dropdown-intop.user-dropdown-intop-open{position:static}
                #rhNavToolWrap .wpsm-button{font-size: 0;line-height: 0;}
                #rhNavToolWrap .wpsm-button i{font-size: 15px;padding: 0;}
                .wcfm-dashboard-page #rhNavToolWrap{display:none !important}
            ';           
        }
        else if($type === 'fullwidthopt'){
            $output .= '
              #rh_p_l_fullwidth_opt .title_single_area h1{ font-size: clamp(36px, 4vw, 46px); line-height: 48px; }
              #rh_p_l_fullwidth_opt .title_single_area .post-meta span{margin-right:20px;}
              span.cat_link_meta:before, span.comm_count_meta:before{opacity:0.33;}
              .post-readopt.full_width .post-inner{margin-left:auto !important; margin-right:auto !important}
            ';           
        }
        else if($type === 'fullgutenberg'){
            $output .= '
                .rh-container.full_gutenberg{width:100%; padding:0 !important}
                .fullgutenberg .post > *{max-width:850px; margin-left:auto; margin-right:auto}
                .fullgutenberg .post > *.alignwide{max-width:1200px;}
                .fullgutenberg.fullgutenberg_reg .post > *{max-width:1200px;}
                .fullgutenberg.fullgutenberg_reg .post > *.alignwide{max-width:1350px;}
                .fullgutenberg.fullgutenberg_ext .post > *{max-width:1530px;}
                .fullgutenberg.fullgutenberg_ext .post > *.alignwide{max-width:1650px;}
                .fullgutenberg .post > *.alignfull{max-width:100vw;}
                @media (max-width:1200px){
                    .fullgutenberg .post > *.alignwide{margin-left:15px; margin-right:15px}
                }
                @media (max-width:767px){
                    .fullgutenberg .post > *:not(.alignfull){margin-left:15px; margin-right:15px}
                }
            ';           
        }
        else if($type === 'menunearlogo'){
            $output .= '
              #re_menu_near_logo > ul > li{float: left; font-size:16px; margin: 0 10px; line-height: 34px; font-weight: bold;}
              #re_menu_near_logo > ul > li i{margin: 0 6px 0 0}
              #re_menu_near_logo > ul > li a{color: #111}
            ';           
        }
        else if($type === 'featgrid'){
            $output .= '
              .wpsm_featured_wrap{overflow: hidden; margin-bottom: 35px}
              .side-twocol .columns { height: 220px; position: relative; overflow: hidden; }
              .side-twocol .columns .col-item{height: 100%}
              .side-twocol .news_in_thumb figure{min-height: 100px; margin: 0}
              .side-twocol figure img, .side-twocol figure{height: 100%; width: 100% }
              .col-feat-grid{z-index:2; background-position: center center;background-size: cover; position: relative;}
              .col-feat-grid.item-1, .col-feat-50{ width: 50%; float: left; height: 450px}
              .col-feat-50 .col-feat-grid{ width: calc(50% - 5px); float: left; height: 220px}
              .feat_overlay_link{width: 100%;height: 100%;position: absolute;z-index: 10; bottom: 0; left: 0; right: 0;}
              .col-feat-grid.item-1 .text_in_thumb h2{ font-size: 28px; line-height: 34px}
              .featured_grid .wcvendors_sold_by_in_loop{color: #eee}
              .featured_grid .wcvendors_sold_by_in_loop a{color: #fff}
              .blacklabelprice {margin: 0 0 12px 0;background: #000000;padding: 8px;display: inline-block;font-weight: bold;}
              .blacklabelprice del{opacity: 0.8 !important;font-weight: normal; color: #fff !important}
              .feat-grid-overlay .price del{opacity: 0.7}
              .col-feat-grid:after {bottom: 0;content: "";display: block;height: 80%;width: 100%;position: absolute;z-index: 1;pointer-events: none;background: linear-gradient(to bottom,rgba(0,0,0,0) 0%,rgba(0,0,0,0.6) 100%);transition: 0.5s;}
              .col-feat-grid .feat-grid-overlay{position: absolute;bottom: 0px;color: #ffffff; z-index: 9; white-space:normal;}
              .col-feat-grid:hover .text_in_thumb {padding-bottom: 25px}
              .col-feat-grid:hover:after{opacity: 0.8}
              .lazy-bg-loaded.col-feat-grid{background-size:cover; background-repeat:no-repeat}

              @media screen and (max-width: 1224px) and (min-width: 1024px) {
              .col-feat-grid.item-1, .col-feat-50{ height: 380px}
              .col-feat-50 .col-feat-grid{height: 185px}
              .side-twocol .columns {height: 200px}
              }
              @media screen and (max-width: 1023px) {
              .col-feat-grid.item-1, .col-feat-50 { float: none; width: 100%; margin-bottom: 10px; overflow: hidden;  }
              .col-feat-50{margin: 0}
              .side-twocol .columns{  width: 48.5%; float: left;}
              .side-twocol .col-1 {margin: 0 3% 0 0;}
              .side-twocol .columns a.comment{ display:none}
              .side-twocol .columns{height: auto;}
              }
              @media only screen and (max-width: 767px) {
              .col-feat-50 .col-feat-grid{ height: 200px}
              .col-feat-50 {height: auto;}
              .col-feat-grid.item-1{height: 350px} 
              .col-feat-grid.item-1 .text_in_thumb h2{font-size: 21px;line-height: 24px}
              }
              @media only screen and (max-width: 400px) {.col-feat-grid.item-1{height: 260px} }
            ';           
        }
        else if($type === 'fullwidthphotowoo'){
            $output .= '
              #rh_post_layout_inimage{color:#fff; background-position: center center; background-repeat: no-repeat; background-size: cover; background-color: #333;position: relative;width: 100%;z-index: 1;}
              .rh_post_layout_inner_image #rh_post_layout_inimage{min-height: 500px;}
              #rh_post_layout_inimage .rh_post_breadcrumb_holder{z-index: 2;position: absolute;top: 0;left: 0;min-height: 35px;}
              .rh_post_layout_fullimage .rh-container{overflow: hidden; z-index:2; position:relative; min-height: 420px;}
              .rh_post_layout_inner_image .rh_post_header_holder{position: absolute;bottom: 0;padding: 0 20px 0;z-index: 2;color: white;width: 100%; }
              .rtl #rh_post_layout_inimage .rh_post_breadcrumb_holder {left:auto;right: 0;}
              .rtl #rh_post_layout_inimage .woocommerce-message:before, .rtl #rh_post_layout_inimage .woocommerce-error:before, .rtl #rh_post_layout_inimage .woocommerce-info:before{right: 0; left: auto;}
              .rtl #rh_post_layout_inimage .woocommerce-message, .rtl #rh_post_layout_inimage .woocommerce-error, .rtl #rh_post_layout_inimage .woocommerce-info{padding: 1em 3em 0 0 !important;}
              .rh_post_layout_fullimage .title_single_area h1{ font-size: 44px; line-height: 46px; }
              .rh_post_layout_fullimage .review_big_circle{float: left; margin-right: 20px;}
              .rtl .rh_post_layout_fullimage .review_big_circle{float: right; margin: 0 0 0 20px;}
              .rh_post_layout_fullimage .review_big_circle .radial-progress .inset{color: #fff; background-color: #2a2a2a}
              .woo_full_photo_booking .woo-price-area{margin: 0; padding: 20px; font-size: 22px; position: absolute; bottom: 0; left: 0;right: 0}
              .woo_full_photo_booking .woo-price-area .price{margin: 0; font-size:25px}
              .woo_full_photo_booking .rh-big-tabs-li.active a{border: none;}
              .woo_full_photo_booking .post_share{margin: 0}
              .woo_full_photo_booking .goto_more_offer_section{display: block;}
              .rh-woo-fullimage-holder{position: absolute; bottom: 0; z-index: 2;color: white; width: 100%;}
              .rh-woo-fullimage-holder h1{font-size: 35px; letter-spacing: 0}
              @media screen and (max-width: 1023px) and (min-width: 768px){
                  .rh_post_layout_inner_image #rh_post_layout_inimage, .rh_post_layout_fullimage .rh-container{min-height: 370px;}
                  #rh_post_layout_inimage .title_single_area h1{font-size: 28px; line-height: 34px}
              }
              @media screen and (max-width: 767px){   
                  .rh_post_layout_inner_image #rh_post_layout_inimage, .rh_post_layout_fullimage .rh-container{min-height: 300px;}
                  #rh_post_layout_inimage .title_single_area h1{font-size: 24px; line-height: 24px}   
              }
              @media screen and (max-width: 767px){
                  .rh_post_layout_fullimage .review_big_circle{float: none; margin: 0 0 20px 0;}
              }
            ';           
        }
        else if($type === 'newsblock'){
            $output .= '
              .rh_news_wrap_two{max-width: 840px}
              .news_third_col, .news_sec_col{width: 28.5%}
              .rh_news_wrap_two .re_ajax_pagination{display: none;}
              .news_out_thumb .post-meta a, .news_out_thumb .post-meta span{ color: #aaa}
              .news_out_tabs{min-height: 300px}
            ';           
        }
        else if($type === 'rhtablinks'){
            $output .= '
              .rh_tab_links{ overflow: hidden; margin: 0 0 30px 0}
              .rh_tab_links_bottomline{ position: relative;}
              .rh_tab_links_bottomline:after{position: absolute; content: " "; width: 100%; bottom: 0; left: 0; border-bottom: 1px solid #e0dadf; z-index: 1;}
              .rh_tab_links a{float:left; position: relative; display: inline-block; border: 1px solid #ddd; background-color: #fff;padding: 14px 28px; font-size:14px; line-height: 18px; text-decoration: none;margin: 0 0 0 -1px;}
              .rh_tab_links a.active, .rh_tab_links a:hover{ z-index:2;}
              .rh_tab_links a:first-child{margin:0}
              .rh_tab_links_bottomline a{border-bottom: none}
              @media(max-width: 1023px){
                .rh_tab_links a{padding: 10px 18px}
              @media(max-width: 767px){
                .rh_tab_links a.active:after{font-family: rhicons;}
                .rh_tab_links a{ display: none; float: none; margin: -1px 0 0 0;}
                .rh_tab_links a.showtabmobile, .rh_tab_links a.active{display:block}
                .rh_tab_links a.active:after { float: right; content: "\f078";  }
              }
              }
            ';           
        }
        else if($type === 'rightaffpost'){
          $output .= '
              .right_aff { float: right; width: 35%; margin: 0 0 0 20px; position: relative; z-index:1; }
              .right_aff .priced_block .btn_offer_block, .right_aff .priced_block .button { position: absolute; top: -26px; right: 0; padding: 15px; box-shadow: none; }
              .separate_sidebar_bg .right_aff .priced_block .btn_offer_block{right: -26px; box-shadow: none !important;}
              body.noinnerpadding .right_aff .priced_block .btn_offer_block{right: 0; top:0;}
              .right_aff .priced_block .price_count { position: absolute; top: -38px; left: 0; padding: 28px 12px 22px 12px; font-size:15px; line-height: 15px; font-weight: bold; text-shadow: 0 1px 1px #FFF9E7;    background: #F9CC50;color: #111; }
              .right_aff .priced_block .price_count:before { width: 0; height: 0; border-style: solid; border-width: 13px 0 0 8px; border-color: transparent transparent transparent #967826; content: ""; position: absolute; top: 0; right: -8px }
              .right_aff .priced_block .price_count .triangle_aff_price { width: 0; height: 0; border-style: solid; border-color: #f9cc50 transparent transparent transparent; content: ""; position: absolute; top: 100%; left: 0 }
              .right_aff .priced_block .price_count ins { border: 1px dashed #444; padding: 5px 0; border-left: none; border-right: none; }
              .right_aff .priced_block .price_count del{display: none;}
              .right_aff .priced_block .price_count, .right_aff .priced_block .btn_offer_block, .right_aff .priced_block .button, .custom_search_box button[type="submit"]{border-radius: 0 !important}
              .post .right_aff .priced_block{ margin: 20px 0 26px 0}
              .right_aff .rehub_offer_coupon{display: block;}
              .right_aff .priced_block .btn_offer_block:active{ top: -25px} 
              body.noinnerpadding .right_aff .priced_block .price_count{top:-13px;}
              body.noinnerpadding .right_aff{border-top: 1px solid #eee}
              .right_aff .not_masked_coupon{margin-top: 40px}
              .ameb_search{ font-size: 12px; line-height: 12px; text-align: right; margin-top:8px }
              .ameb_search a{ display: block; margin-bottom: 10px}
              @media screen and (max-width: 1023px) and (min-width: 768px){
                  .right_aff .priced_block .btn_offer_block, .right_aff .priced_block .button{right: -25px}
              }
              @media screen and (max-width: 767px){   
                  .right_aff { width: 100%; margin: 0 0 30px 0} 
                  .rh_post_layout_corner{margin-top: 25px}
              }
          ';           
        }
        else if($type === 'widgetfilters'){
            $output .= '
              .woocommerce .wc-layered-nav-rating .star-rating{width: auto;}
              .woocommerce .wc-layered-nav-rating .star-rating span{line-height: 18px;font-size: 18px;display: inline-block; position: static; padding: 0; color: #ccc}
              .woocommerce .wc-layered-nav-rating .star-rating:before, .woocommerce .wc-layered-nav-rating .star-rating span:before{display: none;}
              .woocommerce .widget_layered_nav ul { margin: 0; padding: 0; border: 0; list-style: none outside; overflow-y: auto; max-height: 166px; }
              .woocommerce .widget_layered_nav ul li{ padding: 0 0 2px; list-style: none; font-size: 14px; line-height: 22px }
              .woocommerce .widget_layered_nav ul li:after{ content: ""; display: block; clear: both; }
              .woocommerce .widget_layered_nav ul li a, .woocommerce .widget_layered_nav ul li span.count{ padding: 1px 0; float: left; color: #111}
              .woocommerce .widget_layered_nav ul li span.count{padding: 0 2px; font-size: 80%; opacity: 0.8}
              .widget_layered_nav ul li a:before, .widget_layered_nav_filters ul li a:before { display: inline-block; font-size: 100%; margin-right: .618em; font-weight: normal; line-height: 1em; width: 1em; content: "\f111"; color: #555; }
              .widget_layered_nav_filters ul li a:before { color: #fff }
              .widget_layered_nav ul li:not(.chosen) a.rh_swatch_filter:before{display: none;}
              .widget_layered_nav ul li a.rh_swatch_filter{display: -webkit-flex;-webkit-align-items: center;align-items: center;display: -ms-flexbox;display: flex;-ms-flex-align: center;-webkit-box-align: center;flex-direction: row;margin-bottom: 5px;}
              .widget_layered_nav ul li a.rh_swatch_text .rh_attr_name{display: none;}
              .widget_layered_nav ul li a:hover:before, .widget_layered_nav_filters ul li a:hover:before { content: "\e907";}
              .widget_layered_nav ul li.chosen a:before, .widget_layered_nav_filters ul li.chosen a:before { content: "\e907"; }
              .widget_layered_nav ul li.chosen a:hover:before, .widget_layered_nav_filters ul li.chosen a:hover:before { content: "\f057"; }
              .widget_layered_nav.widget .title, .widget_price_filter.widget .title, .prdctfltr-widget.widget .title{font-size: 16px; padding-bottom: 10px}
              .woocommerce .widget_layered_nav ul small.count{ float: right; margin-left: 6px; font-size: 1em; padding: 1px 0; color: #777; }
              .woocommerce .widget_layered_nav_filters ul{ margin: 0; padding: 0; border: 0; list-style: none outside; overflow: hidden; }
              .woocommerce .widget_layered_nav_filters ul li { float: left; padding: 0 1px 1px 0; list-style: none; }
              .woocommerce .widget_layered_nav_filters ul li a{ padding: 2px 6px; color: #fff; border-radius: 3px; float: left; background-color: #111 }
              .woocommerce .widget_price_filter .price_slider { margin-bottom: 22px; }
              .woocommerce-widget-layered-nav-dropdown{min-height: 30px}
              .woocommerce .widget_price_filter .price_slider_amount #min_price, .woocommerce .widget_price_filter .price_slider_amount #max_price{display:none}
              .woocommerce .widget_price_filter .price_slider_amount { text-align: right; line-height: 2.4em; font-size: .8751em; padding-bottom: 1px }
              .woocommerce .widget_price_filter .price_slider_amount .button { font-size: 1.15em; }
              .woocommerce .widget_price_filter .price_slider_amount .button { float: left; }
              .woocommerce .widget_price_filter .ui-slider{ position: relative; text-align: left; }
              .woocommerce .widget_price_filter .ui-slider .ui-slider-handle { position: absolute; z-index: 2; width: 16px; height: 16px; border: 1px solid #aeaeae; cursor: pointer; outline: 0; top: -6px; margin-left: 0; border-radius: 50% !important; background: #fff}
              .woocommerce .widget_price_filter .ui-slider .ui-slider-range{ position: absolute; z-index: 1; font-size: .7em; display: block; border: 0; border-radius: 1em; }
              .woocommerce .widget_price_filter .price_slider_wrapper .ui-widget-content {border:none; border-radius: 1em; background: #333; margin-top: 5px   }
              .woocommerce .widget_price_filter .ui-slider-horizontal { height: 4px; }
              .woocommerce .widget_price_filter .ui-slider-horizontal .ui-slider-range { top: 0; height: 100%; }
              .woocommerce .widget_price_filter .ui-slider-horizontal .ui-slider-range-min { left: -1px; }
              .woocommerce .widget_price_filter .ui-slider-horizontal .ui-slider-range-max { right: -1px; }
              .widget_price_filter.widget .title:after{display: none;}
              .woocommerce .widget_price_filter .ui-slider .ui-slider-handle:last-child{margin-left: -16px}
              ul li.wc-layered-nav-rating{margin: 0 0 10px 0}
              ul li.wc-layered-nav-rating a{color: #111}
              .select2-dropdown{z-index:999999 !important}
              select.dropdown_product_cat{ border: 1px solid #e1e1e1; width: 100%;}
            form.search-form.product-search-form [type=submit]{position:static}
            ';           
        }
        else if($type === 'niceselect'){
            $output .= '
            .nice-select{-webkit-tap-highlight-color:transparent;background-color:#fff;border-radius:5px;border:1px solid #e1e1e1;box-sizing:border-box;clear:both;cursor:pointer;display:block;float:left;font-family:inherit;font-size:14px;font-weight:400;height:38px;line-height:36px;outline:0;padding-left:18px;padding-right:30px;position:relative;text-align:left!important;transition:all .2s ease-in-out;-webkit-user-select:none;user-select:none;white-space:nowrap;width:auto}.nice-select:hover{border-color:#dbdbdb}.nice-select:after{border-bottom:2px solid #999;border-right:2px solid #999;content:"";display:block;height:5px;margin-top:-4px;pointer-events:none;position:absolute;right:12px;top:50%;transform-origin:66% 66%;transform:rotate(45deg);transition:all .15s ease-in-out;width:5px}.nice-select.open:after{transform:rotate(-135deg)}.nice-select.open .list{opacity:1;pointer-events:auto;transform:scale(1) translateY(0)}.nice-select.disabled{border-color:#ededed;color:#999;pointer-events:none}.nice-select.disabled:after{border-color:#ccc}.nice-select.wide{width:100%}.nice-select.wide .list{left:0!important;right:0!important}.nice-select.right{float:right}.nice-select.right .list{left:auto;right:0}.nice-select.small{font-size:12px;height:36px;line-height:34px}.nice-select.small:after{height:4px;width:4px}.nice-select.small .option{line-height:34px;min-height:34px}.nice-select .list{background-color:#fff;border-radius:5px;box-shadow:0 0 0 1px rgba(68,68,68,.11);box-sizing:border-box;margin-top:4px;opacity:0;overflow:hidden;padding:0;pointer-events:none;position:absolute;top:100%;left:0;transform-origin:50% 0;transform:scale(.75) translateY(-21px);transition:all .2s cubic-bezier(.5,0,0,1.25),opacity .15s ease-out;z-index:9999999}.nice-select .list:hover .option:not(:hover){background-color:transparent!important}.nice-select .option{margin:0;cursor:pointer;font-weight:400;line-height:32px;list-style:none;min-height:32px;outline:0;padding-left:18px;padding-right:29px;text-align:left;transition:all .2s}.nice-select .option.focus,.nice-select .option.selected.focus,.nice-select .option:hover{background-color:#f6f6f6}.nice-select .option.selected{font-weight:700}.nice-select .option.disabled{background-color:transparent;color:#999;cursor:default}.no-csspointerevents .nice-select .list{display:none}.no-csspointerevents .nice-select.open .list{display:block}
            .product-search-form .nice-select{border-radius: 0; height: 38px; line-height: 36px; border-width: 1px 0 1px 1px}
            .sidebar .product-search-form .nice-select{display: none}
            .search-header-contents form.search-form .nice-select{line-height: 74px; height: 74px;border-right-width: 1px;font-size: 16px;padding-left: 25px;padding-right: 35px;}
            ';           
        }
        else if($type === 'vertbookable'){
            $output .= '
              .rh_vert_bookable .wc-bookings-booking-form{padding: 0; margin: 0 0 25px 0; border: none}
              .rh_vert_bookable .wc-bookings-booking-form fieldset label{width: 29%}
              .rh_vert_bookable .form-field-wide{font-size: 12px; color: #bbb; padding: 0 0 8px 0}
              .rh_vert_bookable .form-field-wide label{font-size: 14px; color: #111;}
              .rh_vert_bookable .wc-bookings-booking-form .form-field.form-field-wide input[type=number]{width: 100%; font-size: 15px; float: none; }
            ';           
        }
        else if($type === 'cewoosection'){
            $output .= '
              .rh_post_layout_compare_full .title_single_area h1{ font-size: 24px; line-height: 30px; }
              .rh_post_layout_compare_full{overflow: hidden; margin-bottom: 25px; margin-top: 20px}
              .noinnerpadding .rh_post_layout_compare_full{padding: 20px; border: 1px solid #eee;}
              .rh-boxed-container .rh_post_layout_compare_full{padding: 20px; }
              .rh_post_layout_compare_full .featured_list{margin: 0 0 20px 0; font-size: 15px; line-height: 22px}
              .rh_post_layout_compare_full .featured_list li{margin: 0 0 5px 15px; list-style: disc;}
              .rh_post_layout_compare_full .top_share .post_share{margin-bottom: 0}
              .meta-in-compare-full{overflow: hidden;padding: 10px 0; border-top: 1px solid #eee; border-bottom: 1px solid #eee; margin: 0 0 20px 0}
              .ce_woo_auto_sections .rh_post_layout_compare_full{border: 1px solid #ededed;}
              @media screen and (max-width: 1023px) and (min-width: 768px) {
                .rh_post_layout_compare_full .wpsm-one-half{width: 100%; margin-right: 0}
                .rh_post_layout_compare_full .wpsm-one-half.wpsm-column-first{margin-bottom: 25px}
              }
              @media screen and (max-width: 767px){
                .rh_post_layout_compare_full .wpsm-one-half, .rh_post_layout_compare_full .wpsm-one-third, .rh_post_layout_compare_full .wpsm-two-third{width: 100%; margin-right: 0}
                .rh_post_layout_compare_full figure{text-align: center;}
                .rh_post_layout_compare_full .wpsm-button-new-compare{margin-top:12px;}
                .rh_post_layout_compare_full .wpsm-one-half.wpsm-column-first{margin-bottom: 25px}  
              }
            ';           
        }
        else if($type === 'section_w_sidebar'){
            $output .= '
            .sections_w_sidebar .woo-price-area{ visibility: visible;opacity: 1; height: 40px;transition: visibility 0.5s, opacity 0.5s linear, height 0.5s;}
            .sections_w_sidebar .floatactive.woo-price-area{  visibility: hidden;opacity: 0; height:0; }
            .sections_w_sidebar .vendor_store_details{background: #fff}
            .sections_w_sidebar nav.woocommerce-breadcrumb{margin-bottom: 10px}
            .rh-336-content-area .rh-sticky-wrap-column{position:static !important; transform:none !important}
            ';           
        }
        else if($type === 'footerdark'){
            $output .= '
              .footer-bottom.dark_style{background-color: #000000;}
              .footer-bottom.dark_style .footer_widget { color: #f5f5f5}
              .footer-bottom.dark_style .footer_widget .title, .footer-bottom.dark_style .footer_widget h2, .footer-bottom.dark_style .footer_widget a, .footer-bottom .footer_widget.dark_style ul li a{color: #f1f1f1;}
              .footer-bottom.dark_style .footer_widget .widget_categories ul li:before, .footer-bottom.dark_style .footer_widget .widget_archive ul li:before, .footer-bottom.dark_style .footer_widget .widget_nav_menu ul li:before{color:#fff;}
            ';           
        }
        else if($type === 'footerbottomdark'){
            $output .= '
              footer#theme_footer.dark_style { background: none #222; }
              footer#theme_footer.dark_style div.f_text, footer#theme_footer.dark_style div.f_text a:not(.rehub-main-color) {color: #f1f1f1;}
            ';           
        }
        else if($type === 'rhgutcomparison'){
            $output .= '
                .comparison-table .comparison-wrapper{display:flex;-webkit-flex:1;-ms-flex:1;flex:1;width:100%}.comparison-item{-webkit-flex:1 0 0;-ms-flex:1 0 0px;flex:1 0 0;position:relative;background:#fff;padding-top:0;border-top:1px solid rgba(206,206,206,.5);border-bottom:1px solid rgba(206,206,206,.5);border-right:1px solid rgba(206,206,206,.5)}.comparison-item.comparison-header{-webkit-flex:0 0 100px;-ms-flex:0 0 100px;flex:0 0 100px;border-left:1px solid rgba(206,206,206,.5)}.comparison-item .item-badge,.comparison-item.comparison-header{text-align:center;font-weight:600;text-transform:uppercase;font-size:13px;line-height:18px}.comparison-item .item-badge{position:absolute;top:-50px;left:-1px;right:-1px;height:50px;margin:0;padding:0 15px;background-color:#ccc;color:#fff;z-index:1;display:-webkit-flex;display:-ms-flexbox;display:flex;-webkit-align-items:center;-ms-flex-align:center;align-items:center;-webkit-justify-content:center;-ms-flex-pack:center;justify-content:center}.comparison-item .item-header{padding:0 15px 25px;text-align:center;position:relative}.comparison-item .item-header>:last-child{margin:0}.comparison-item .item-header .item-number{position:absolute;left:20px;top:20px;font-size:15px;line-height:35px;width:35px;height:35px;font-weight:700;text-align:center;border-radius:50%;background:green;color:#fff}.comparison-item .item-title{margin-top:0;margin-bottom:0;font-weight:600;font-size:18px;line-height:1.5;color:#333e48}.comparison-item .item-subtitle{color:#7a7a7a;font-size:15px;line-height:22px;margin:0 0 15px}.comparison-item .product-image{padding-top:25px;display:table;width:100%;table-layout:fixed;height:185px;margin-bottom:25px}.comparison-item .product-image .image{display:table-cell;vertical-align:middle;width:100%;height:100%}.comparison-item .product-image .image img{max-height:160px;width:auto!important;height:auto;display:block;margin:0 auto}.comparison-item .item-row-description{border-top:1px solid rgba(206,206,206,.5);padding:15px;font-size:inherit;line-height:1.3}.comparison-item .item-row-description .item-row-title{display:none;margin:-15px -15px 15px;padding:7px 15px;border-bottom:1px solid rgba(206,206,206,.5);font-weight:700;font-size:14px;text-transform:uppercase;text-align:center}.comparison-item .item-row-description.item-row-callout{text-align:center}.comparison-item .rehub-item-btn{font-size:16px;line-height:1;padding:12px 25px;text-transform:none;display:inline-block;color:#fff;fill:#fff;border:none;text-decoration:none;outline:0;text-shadow:none;box-shadow:-1px 6px 19px rgba(0,0,0,.2);border-radius:4px}.comparison-item .rehub-item-btn:hover{box-shadow:-1px 6px 13px rgba(0,0,0,.4)!important}.comparison-item .item-list{margin:0 0 20px;font-size:15px}.comparison-item .item-list-title{margin:0 0 12px}.comparison-item .item-list ul{list-style-type:none;padding:0;margin:0}.comparison-item .item-list ul li{margin:0;line-height:1.4}.comparison-item .item-list ul li::marker{display:none;color:transparent}.comparison-item .item-list ul li a{color:#000;font-weight:400;font-size:15px;text-decoration:underline}.comparison-item .item-list ul li a:hover{text-decoration:none}.comparison-item .item-rating{margin-bottom:15px;margin-left:auto!important;margin-right:auto!important}.comparison-item .item-rating .item-stars-rating{display:-webkit-flex;display:-ms-flexbox;display:flex;-webkit-justify-content:center;-ms-flex-pack:center;justify-content:center;overflow:hidden;font-size:1.5rem;height:1.5rem;line-height:1;color:#ff5900;position:relative;margin:0 auto}.comparison-item .item-rating .item-stars-rating .item-star{overflow:hidden;width:22px;height:22px;text-align:center}.comparison-table.has-badges .comparison-item{margin-top:50px}[dir=rtl] .comparison-item.comparison-item{border-left:1px solid rgba(206,206,206,.5);border-right:0}[dir=rtl] .comparison-item.comparison-header{border-left:1px solid rgba(206,206,206,.5);border-right:1px solid rgba(206,206,206,.5);text-align:right}[dir=rtl] .comparison-item .item-rating .item-stars-rating{-webkit-flex-direction:row-reverse;-ms-flex-direction:row-reverse;flex-direction:row-reverse}.comparison-table{position:relative;display:-webkit-flex;display:-ms-flexbox;display:flex;width:100%;margin-bottom:30px}.comparison-control-next,.comparison-control-prev{display:none}.comparison-item.swiper-slide{overflow:visible}@media (max-width:767.98px){.comparison-table.slide .comparison-item .item-badge,.comparison-table.stacked .comparison-item .item-badge{position:static;margin:0 -15px}.comparison-table.overflow .comparison-wrapper{overflow-x:auto}.comparison-table.overflow .comparison-item{width:200px;-webkit-flex:0 0 auto;-ms-flex:0 0 auto;flex:0 0 auto}.comparison-table.overflow .comparison-header{width:100px;-webkit-flex:0 0 auto;-ms-flex:0 0 auto;flex:0 0 auto}.comparison-table.stacked .comparison-wrapper{-webkit-flex-wrap:wrap;-ms-flex-wrap:wrap;flex-wrap:wrap}.comparison-table.slide{-webkit-flex-wrap:wrap;-ms-flex-wrap:wrap;flex-wrap:wrap}.comparison-table.slide .comparison-wrapper{-webkit-flex-wrap:nowrap;-ms-flex-wrap:nowrap;flex-wrap:nowrap;flex:0 0 auto}.comparison-table.slide .comparison-header,.comparison-table.stacked .comparison-header{display:none}.comparison-table.slide .comparison-item,.comparison-table.stacked .comparison-item{-webkit-flex:0 0 auto;-ms-flex:0 0 auto;flex:0 0 auto;width:100%;border-left:1px solid rgba(206,206,206,.5);margin:20px 0}.comparison-table.slide .comparison-item .item-row-description .item-row-title,.comparison-table.stacked .comparison-item .item-row-description .item-row-title{display:block;background-color:#f4f4f4;padding:10px 15px}[dir=rtl] .comparison-table.slide .comparison-item.comparison-item,[dir=rtl] .comparison-table.stacked .comparison-item.comparison-item{border-right:1px solid rgba(206,206,206,.5)}.comparison-table.slide .comarison-controls{display:block}.comparison-table.slide .comparison-control-next,.comparison-table.slide .comparison-control-prev{display:block;z-index:3;position:absolute;top:200px;border:none;background-color:transparent;outline:0!important;font-size:2rem;width:2rem;height:2rem;line-height:1}.comparison-control-next.swiper-button-disabled,.comparison-control-prev.swiper-button-disabled{opacity:.4}.comparison-control-next svg,.comparison-control-prev svg{display:block}.comparison-control-next svg path,.comparison-control-prev svg path{fill:#000}.comparison-control-prev{left:0}.comparison-control-next{right:0}.comparison-table.slide .comparison-control-prev{animation:lefttorightarrow 2.3s ease-in-out 0s infinite}.comparison-table.slide .comparison-control-next{animation:righttoleftarrow 2.3s ease-in-out 0s infinite}}@keyframes lefttorightarrow{0%{transform:translateX(0)}50%{transform:translateX(12px)}100%{transform:translateX(0)}}@keyframes righttoleftarrow{0%{transform:translateX(0)}50%{transform:translateX(-12px)}100%{transform:translateX(0)}}@media (min-width:768px){.comparison-wrapper{overflow-x:auto}.comparison-wrapper .comparison-item{min-width:190px}}
            ';           
        }
        else if($type === 'rhgutslider'){
            $output .= '
                .rh-slider__inner{position:relative;display:block;background:#fff;box-shadow:0 2px 20px rgba(0,0,0,.15);max-height:640px;overflow:hidden;border-radius:6px}.rh-slider__inner:after{content:"";display:block;padding-top:100%;transition:all .4s ease 0s}.rh-slider-item{display:block;position:absolute;top:0;left:0;width:100%;height:100%;text-decoration:none;outline:none;opacity:0}.rh-slider-item img{display:block;position:absolute;top:45px;left:45px;width:calc(100% - 90px);height:calc(100% - 90px);object-fit:contain}.rh-slider-item--visible{opacity:1;transition:opacity .5s ease 0s;z-index:50}.rh-slider-arrow{z-index:200;position:absolute;top:calc(50% - 30px);width:40px;height:60px;display:flex;align-items:center;justify-content:center;cursor:pointer;border:none;background:linear-gradient(138.42deg,#1c9294 15.07%,#1c944c 88.46%);transition-property:all;transition-duration:.3s;color:#fff;font-size:20px;line-height:1}.rh-slider-arrow:hover{opacity:.85}.rh-slider-arrow--prev{left:0;border-radius:0 4px 4px 0}.rh-slider-arrow--next{right:0;border-radius:4px 0 0 4px}.rh-slider-thumbs{position:relative;display:none;margin-top:20px;padding:0 5px;max-height:76px;overflow:hidden}.rh-slider-thumbs__row{margin:0 -5px;display:flex;flex-wrap:nowrap;align-items:center;justify-content:flex-start;overflow:scroll}.rh-slider-thumbs-item{position:relative;width:100%;max-width:73px;flex:0 0 73px;margin:0 5px 5px;border:1px solid #e7e4df;border-radius:6px;cursor:pointer}.rh-slider-thumbs-item:after{content:"";display:block;padding-top:100%}.rh-slider-thumbs-item img{display:block;position:absolute;top:10px;left:10px;width:calc(100% - 20px);height:calc(100% - 20px);object-fit:contain}.rh-slider-thumbs-item--active{border:2px solid #ef5323}.rh-slider-dots{z-index:200;position:absolute;bottom:10px;left:0;width:100%;padding:0 10px;text-align:center;line-height:1}.rh-slider-dots__item{display:inline-block;zoom:1;width:12px;height:12px;margin:0 5px;background-color:grey;border-radius:50%;font-size:0;cursor:pointer;border:none;transition-property:all;transition-duration:.3s}.rh-slider-dots__item--active,.rh-slider-dots__item:hover{background-color:#1c9294}@media only screen and (min-width:768px){.rh-slider__inner{max-height:340px}.rh-slider__inner:after{padding-top:340px}.rh-slider-thumbs{display:block}.rh-slider-dots{display:none}}
            ';           
        }
        else if($type === 'footerbottomwhite'){
            $output .= '
              footer#theme_footer.white_style { background: none #fff; border-top: 1px solid #eee;}
              footer#theme_footer.white_style div.f_text, footer#theme_footer.white_style div.f_text a:not(.rehub-main-color) {color: #000;}
            ';           
        }
        else if($type === 'rhofferlistingfull'){
            $output .= '
                .gc-offer-listing{position:relative;margin-bottom:30px}.gc-offer-listing .gc-offer-listing__title{margin:0 0 15px;color:#333;font-size:20px;font-weight:700;line-height:28px}.gc-offer-listing .gc-offer-listing__title a{text-decoration:none}.gc-offer-listing .gc-offer-listing__copy{line-height:22px;font-size:15px}.gc-offer-listing .gc-offer-listing__read-more{display:block;color:#334dfe;font-size:13px;cursor:pointer;margin-top:6px;line-height:18px}.gc-offer-listing .blockstyle,.gc-offer-listing .gc_offer_coupon{display:block}.gc-offer-listing .gc-list-badge{position:absolute;top:0;left:0;z-index:20;background-color:#334dfe;padding:7px 12px 7px 15px;line-height:16px;color:#fff}.gc-offer-listing .gc-list-badge-title{font-size:14px}.gc-offer-listing .gc-list-badge-arrow{content:"";display:block;width:0;height:0;border:15px solid #334dfe;border-top-width:30px;border-left-width:0;border-bottom-color:transparent;border-right-color:transparent;position:absolute;right:-15px;top:0}.gc-offer-listing .priced_block .btn_offer_block{margin:0 auto;display:block;font-size:18px;line-height:20px;padding:13px 20px;text-transform:uppercase;background:none var(--gcbtnbg);color:var(--gcbtncolor);border-radius:4px;font-weight:700;border:none;text-decoration:none}.gc-offer-listing .priced_block .gc_offer_coupon{display:flex;align-items:center;vertical-align:top;cursor:pointer;border:1px dashed green;text-align:center;position:relative;font-size:14px;clear:both;line-height:18px;background-color:#e9ffdd;color:green;border-radius:4px;margin-top:12px}.gc-offer-listing .gc_offer_coupon:hover{border:1px dashed #000}.gc-offer-listing .gc_offer_coupon:hover svg{color:#000}.gc-offer-listing .gc_offer_coupon svg{font-size:14px;padding:6px 6px 6px 0;width:35px;fill:#007501}.gc-offer-listing .gc_offer_coupon span{width:100%;background:none transparent;border:none;text-align:center;padding:6px 15px;font-size:16px}.gc-offer-listing .gc_offer_coupon.expired_coupon{border:1px dashed grey;background:#d3d3d3;color:grey}.gc-offer-listing .gc_offer_coupon.expired_coupon span{text-decoration:line-through}.gc-offer-listing .gc_offer_coupon.expired_coupon svg{display:none}.gc-offer-listing-item{width:100%;margin-top:20px}@media only screen and (min-width:768px){.gc-offer-listing-item{margin-top:-1px}.gc-offer-listing{box-shadow:0 5px 23px rgba(188,207,219,.35)}}.gc-offer-listing-item:first-of-type{margin-top:0}.gc-offer-listing-item .gc-offer-listing-item__wrapper{box-shadow:inset 0 0 0 1px rgba(206,206,206,.4);background:#fff}@media only screen and (min-width:768px){.gc-offer-listing-item .gc-offer-listing-item__wrapper{width:100%;display:flex;flex-wrap:nowrap}}.gc-offer-listing-image{position:relative;display:flex;align-items:center;width:100%;text-align:center}@media only screen and (min-width:768px){.gc-offer-listing-image{min-width:200px;max-width:200px;border-right:1px solid rgba(206,206,206,.4)}}.gc-offer-listing-image figure{position:relative;display:flex;justify-content:center;align-items:center;margin:0 auto;width:100%;height:100%;border-radius:4px}.gc-offer-listing-image img{max-height:100%;border-radius:4px;object-fit:scale-down;flex:0 0 auto;max-height:120px}.gc-offer-listing-score{display:flex;align-items:flex-start;justify-content:center;padding:0 25px 12px 25px}.gc-offer-listing-score svg{width:20px;height:20px;margin-right:6px}.gc-offer-listing-score .gc-lrating{background-color:#fff;border:2px solid #eee;margin:0 auto;text-align:center;display:block;width:120px}.gc-offer-listing-score .gc-lrating-body{font-size:24px;font-weight:700;margin:2px;padding:8px}.gc-offer-listing-score .gc-lrating-bottom{background-color:#f9f9f9;font-size:14px;margin:2px;padding:6px;text-transform:uppercase;flex-grow:1;display:none}.gc-colorrating{text-align:center;border-radius:4px;background-color:#4e4eff;color:#fff;font-weight:700;position:absolute;top:15px;right:15px;width:65px;font-size:20px;line-height:40px}.gc-offer-listing-content{flex-grow:1;flex-basis:0;width:100%}.gc-offer-listing-cta{display:flex;flex-direction:column;justify-content:flex-start;width:100%;text-align:center}@media only screen and (min-width:768px){.gc-offer-listing-cta{min-width:250px;max-width:250px}.gc-colorrating{position:static;width:90px;font-size:25px;line-height:52px}}.gc-offer-listing-price{margin:0 0 8px 0;font-size:22px;font-weight:700;line-height:24px}.gc-offer-listing-price span{display:inline-block}.gc-offer-listing-price del{display:inline-block;vertical-align:top;margin-left:5px;font-size:80%;font-weight:400;color:grey;opacity:.4}.gc-offer-listing-price del .amount{text-decoration:line-through}.gc-offer-listing-cta .price ins{text-decoration:none}.gc-offer-listing-disclaimer{padding:10px 15px;font-size:11.5px;color:grey;background-color:rgba(7,107,156,.1);line-height:22px}.gc-offer-listing-content,.gc-offer-listing-cta,.gc-offer-listing-image{padding:18px 25px}.gc-offer-listing-contwrap{display:flex;flex-grow:1;flex-direction:column}.gc-offer-listing-number{display:none}@media only screen and (min-width:768px){.gc-offer-listing-score{padding:0 25px 25px 25px;justify-content:flex-start}.gc-offer-listing-score .gc-lrating{margin:0}}@media only screen and (min-width:1200px){.gc-offer-listing-content,.gc-offer-listing-cta,.gc-offer-listing-image,.gc-offer-listing-score{padding:30px}.gc-offer-listing-contwrap{flex-direction:row}.gc-offer-listing-number{display:inline-block;height:40px;text-align:center;z-index:1;position:absolute;top:50%;left:-20px;font-size:22px;line-height:40px;width:40px;border-radius:50%;text-align:center;font-weight:700;background:#4bb866;color:#fff;margin-top:-20px}.gc-offer-listing-score .gc-lrating-bottom{display:block}.gc-offer-listing-score .gc-lrating{margin:0 auto}}.gc-listing-expand{background-color:#e9f5fd;padding:20px 30px}.gc-listing-expand-label{margin:20px 0 0 0;font-size:14px;display:inline-block;cursor:pointer;opacity:.5}
            ';           
        }
        else if($type === 'rhpb-video'){
            $output .= '
                .rhpb-video{position:relative;margin-bottom:30px;max-width:100%}.rhpb-video.vimeo:not(.alignwide):not(.alignfull),.rhpb-video.youtube:not(.alignwide):not(.alignfull){width:100%!important;height:auto!important}.rhpb-video-wrapper{position:relative;height:inherit}.rhpb-video-wrapper:before{content:"";display:block;padding-top:56.25%}.rhpb-video-header{text-align:center}.rhpb-video-header .rhpb-video-header-title{font-size:24px;line-height:1.3;font-weight:700;margin:0 0 15px}.rhpb-video-header .rhpb-video-header-description{font-size:16px;line-height:1.5;margin:0 0 25px}.rhpb-video .rhpb-video-element,.rhpb-video .rhpb-video-element iframe{position:absolute;left:0;top:0;width:100%;height:100%;object-fit:cover;border:0}.rhpb-video .rhpb-video-element{object-fit:contain}.rhpb-video .video-container{position:absolute;left:0;top:0;width:100%;height:100%;padding:0;margin:0}.rhpb-video .video-container iframe{display:block;height:100%;width:100%}.rhpb-video-overlay{position:absolute;left:0;top:0;width:100%;height:100%;z-index:3;background-color:rgba(0,0,0,.1);background-size:cover;background-position:center;background-repeat:no-repeat;cursor:pointer}.rhpb-video-overlay .rhpb-play-icon{position:absolute;left:50%;top:50%;transform:translate(-50%,-50%);transition:all .3s ease;opacity:.8;z-index:3;width:32px;height:44px;border-radius:50%;padding:18px 20px 18px 28px;box-sizing:content-box}.rhpb-video-overlay .rhpb-play-icon span{display:block;position:relative;z-index:3;width:0;height:0;border-left:32px solid #fff;border-top:22px solid transparent;border-bottom:22px solid transparent}.rhpb-video-overlay .rhpb-play-icon:after{animation-delay:.5s}.rhpb-video-overlay .rhpb-overlay-color{position:absolute;left:0;top:0;width:100%;height:100%}.rhpb-video-overlay:hover .rhpb-play-icon{opacity:1}.rhpb-video-popup .slbContentOuter{width:100%;max-width:1000px}.rhpb-video-popup .slbContentOuter .slbContent:before{content:"";display:block;padding-top:56.25%}.rhpb-video-popup .slbContentOuter .slbContent .video-container,.rhpb-video-popup .slbContentOuter .slbContent iframe,.rhpb-video-popup .slbContentOuter .slbContent video{position:absolute;left:0;top:0;width:100%;height:100%;object-fit:cover}.rhpb-video-popup .slbCloseBtn{font-size:30px;width:40px;height:40px;line-height:40px;right:0;top:-50px}@keyframes pulsevideobutton{0%{transform:scale(.5);opacity:0}50%{opacity:1}100%{transform:scale(1.2);opacity:0}}
            ';           
        }
        else if($type === 'rhpb-howto'){
            $output .= '
                .gc-howto{margin:45px 0 30px 0;border:3px solid #e7e4df;border-top:none;position:relative;counter-reset:gchowto}.gc-howto__heading{font-size:19px;font-weight:700;line-height:20px;padding:0 15px;text-transform:uppercase}.gc-howto__title{display:-webkit-flex;display:-ms-flexbox;display:flex;-webkit-align-items:center;-ms-flex-align:center;align-items:center;position:absolute;width:100%;top:-10px;height:22px}.gc-howto__line{-webkit-flex-grow:1;-ms-flex-positive:1;flex-grow:1;height:3px;background-color:#e7e4df}.gc-howto__description{padding:35px 25px 25px}.gc-howtoitem__step{position:relative;counter-increment:gchowto;padding:18px 25px}.gc-howtoitem__heading{font-weight:700;font-size:20px;line-height:24px;margin:0 0 25px;padding:0 50px;position:relative}.gc-howtoitem__heading:before{content:counter(gchowto);color:#fcd000;font-size:1.5rem;font-weight:700;position:absolute;--size:32px;line-height:var(--size);width:var(--size);height:var(--size);top:-5px;left:0;background:#000;border-radius:50%;text-align:center}.gc-howtoitem__content{font-size:16px;line-height:24px}
            ';           
        }
        else if($type === 'rhgutreviewheading'){
            $output .= '
                .rh-review-heading{flex-wrap:wrap}.rh-review-heading__position span{line-height:.78}.rh-review-heading__position span:after{content:"."}.rh-review-heading__logo{margin-top:10px;width:100%}.rh-review-heading__logo img{width:auto;max-height:60px}.rh-review-heading__logo-container{max-height:60px;max-width:150px}
                @media only screen and (min-width:480px){.rh-review-heading{flex-wrap:nowrap}.rh-review-heading__logo{margin-top:0;width:auto}}
                .rtl .rh-review-heading .rh-review-heading__position{margin-right:0!important;margin-left:15px}
            ';           
        }
        else if($type === 'footerwhite'){
            $output .= '
              .footer-bottom.white_style{border-top: 1px solid #eee;}
              .footer-bottom.white_style .footer_widget {color: #111;}
              .footer-bottom.white_style .footer_widget .title, .footer-bottom.white_style .footer_widget h2, .footer-bottom.white_style .footer_widget a, .footer-bottom .footer_widget.white_style ul li a{color: #000;}

            ';           
        }
        else if($type === 'filterstore'){
            $output .= '
              .re_filter_instore .re_filter_panel{box-shadow: none;}
              .re_filter_instore .re_filter_panel ul.re_filter_ul li span{padding: 8px 12px; margin: 0 8px 0 0}
              .re_filter_instore ul.re_filter_ul li span:before{margin: 0 5px 0 0; color: #999; font-weight: normal;}
              .re_filter_instore ul.re_filter_ul li:nth-child(2) span:before{color: #7baf34; content: "\f02c" }
              .re_filter_instore ul.re_filter_ul li:nth-child(3) span:before{color: #fb7203; content: "\f0c4"}
              .re_filter_instore ul.re_filter_ul li:nth-child(4) span:before{color: #57a8d6; content: "\f295"}
              .re_filter_instore ul.re_filter_ul li:nth-child(5) span:before{color: #bbb; content: "\f253"; }
              .re_filter_instore span.re_filtersort_btn:hover, .re_filter_instore span.active.re_filtersort_btn{color: #111 !important; background-color: #eee !important}
              @media screen and (max-width: 767px) {
                .re_filter_instore .re_filter_panel ul.re_filter_ul li span{margin: 0 0 8px 0}
              }
            ';           
        }
        else if($type === 'brandcategory'){
            $output .= '
              .rh_category_tab ul.cat_widget_custom {margin: 0;padding: 0;border: 0;list-style: none outside;overflow-y: auto;max-height: 166px;}
              .rh_category_tab ul.cat_widget_custom li {padding: 0 0 4px;list-style: none;font-size: 14px;line-height: 22px;}
              .rh_category_tab ul.cat_widget_custom li a, .category_tab ul.cat_widget_custom li span {padding: 1px 0;color: #111;}
              .rh_category_tab ul.cat_widget_custom li span.counts {padding: 0 2px;font-size: 80%;opacity: 0.8;}
              .rh_category_tab ul.cat_widget_custom li a:before {display: inline-block;font-size: 100%;margin-right: .618em;line-height: 1em;width: 1em;content: "\f111";color: #555;}
              .rh_category_tab ul.cat_widget_custom li a:hover:before, .rh_category_tab ul.cat_widget_custom li a.active:before {content: "\e907";color: #85c858;}
              .rh_category_tab ul.cat_widget_custom li a span.drop_list { float: none; font: 400 14px arial; color: #666; background-color: transparent; padding: 0 }
              .rh_category_tab ul.cat_widget_custom ul.children li { font-size: 12px; color: #787878; padding: 0 10px; margin-bottom: 3px;}
              .rh_category_tab ul.cat_widget_custom li ul.children li a span.drop_list { display: none; }
              .rtl .rh_category_tab ul.cat_widget_custom li a:before{margin-left: .618em;margin-right: 0;}
            ';           
        }
        else if($type === 'postwideimage'){
            $output .= '
                #rh_wide_inimage figure{height:550px;}
                @media (max-width:567px){
                    #rh_wide_inimage figure{height:250px;}
                }
            ';           
        }
        else if($type === 'singlebigoffer'){
            $output .= '
              .rh_post_layout_big_offer .rh-float-panel .priced_block .btn_offer_block{font-size: 21px; padding: 12px 35px; white-space: nowrap;}
              .rh_post_layout_big_offer .title_single_area h1{font-size: 26px; line-height: 28px}
              .rh_post_layout_big_offer .brand_logo_small img{display: block;margin-bottom: 18px}
              .wpsm_score_box .priced_block .btn_offer_block{max-width:320px;}
            ';           
        }
        else if($type === 'singleimagefull'){
            $output .= '
              #rh_post_layout_inimage{color:#fff;position: relative;width: 100%;z-index: 1;}
              .rh_post_layout_inner_image #rh_post_layout_inimage{min-height: 500px;}
              #rh_post_layout_inimage .breadcrumb a, #rh_post_layout_inimage h1, #rh_post_layout_inimage .post-meta span a, #rh_post_layout_inimage .post-meta a.admin, #rh_post_layout_inimage .post-meta a.cat, #rh_post_layout_inimage .post-meta{color: #fff;text-shadow: 0 1px 1px #000;}

              .rh_post_layout_fullimage .rh-container{overflow: hidden; z-index:2; position:relative; min-height: 420px;}
              .rh_post_layout_inner_image .rh_post_header_holder{position: absolute;bottom: 0;padding: 0 20px 0;z-index: 2;color: white;width: 100%; }

              @media screen and (max-width: 1023px) and (min-width: 768px){
                  .rh_post_layout_inner_image #rh_post_layout_inimage, .rh_post_layout_fullimage .rh-container{min-height: 370px;}
                  #rh_post_layout_inimage .title_single_area h1{font-size: 28px; line-height: 34px}
              }

              @media screen and (max-width: 767px){   
                  .rh_post_layout_inner_image #rh_post_layout_inimage, .rh_post_layout_fullimage .rh-container{min-height: 300px;}
                  #rh_post_layout_inimage .title_single_area h1{font-size: 24px; line-height: 24px}   
              }

              .rtl #rh_post_layout_inimage .rh_post_breadcrumb_holder {left:auto;right: 0;}
              .rh_post_layout_fullimage .title_single_area h1{ font-size: 46px; line-height: 48px; }
            ';           
        }
        else if($type === 'lazybgsceleton'){
            $output .= '
                .'.$random.' .lazy-bg-loaded.rh-sceleton{background: url("'.$atts['imageurl'].'") no-repeat center center transparent;background-size:cover}
            ';           
        }
        else if($type === 'rhcountdown'){
            $output .= '
                .rh-countdownwrap{display:flex;align-items:center}.rh-countdownwrap .rh-countdown{display:flex;text-align:center}.rh-countdownwrap .rh-countdown__item{margin:0 .5rem;padding:10px 15px;border-radius:9px;background-color:#c3cfe2;color:#f5f7fa;display:flex;justify-content:center;font-family:Helvetica,sans-serif;font-size:80px;align-items:center}.rh-countdownwrap .rh-countdown__colon{display:flex;flex-direction:column;justify-content:space-evenly;padding:0 0 20px 0}.rh-countdownwrap .rh-countdown__colon-item{width:1rem;height:1rem;background-color:#f5f7fa;border-radius:50%}
            ';           
        }
        else if($type === 'rhcolortitlebox'){
            $output .= '
            .rh-colortitlebox{margin-bottom:30px;background:#fff;line-height:24px;font-size:90%}.rh-colortitlebox .rh-colortitle-inbox{display:flex;align-content:center;padding:15px;font-weight:700;font-size:110%; line-height:25px}.rh-colortitlebox .rh-colortitle-inbox i{line-height:25px; margin:0 10px; font-size:23px}.rh-colortitlebox .rh-colortitle-inbox svg{width:25px;margin-right:10px}.rh-colortitlebox .rh-colortitle-inbox-label{flex-grow:1}.rh-colortitlebox .rh-colortitlebox-text{padding:20px}.rh-colortitlebox-text>*{margin-bottom:20px}.rh-colortitlebox-text>:last-child{margin-bottom:0}
            ';           
        }

        else if($type === 'woosingleimage'){
            $output .= '
                @media (min-width:480px){.attachment-shop_single, .attachment-full, .woo-image-part figure img{max-height:'.(int)$atts['height'].'px; width: auto !important;}}
                @media (max-width:479px){.woocommerce-product-gallery figure div:first-child{height:250px}.woocommerce-product-gallery figure div:first-child > a > img{max-height:250px}}
            ';           
        }
        else if($type === 'widgettopoffers'){
            $output .= '
              .widget.top_offers, .widget.cegg_widget_products{border: 1px solid rgba(206,206,206,0.4); padding: 15px;  background: #fff}
              .widget.top_offers .title, .widget.cegg_widget_products .title{ font-size: 18px; margin-bottom: 15px; text-transform: uppercase; border:none;}
              .widget.top_offers .title:before, .widget.cegg_widget_products .title:before{ font-size: 22px; color: #fff; padding-right: 10px; content: "\f2eb";}
              .widget.top_offers.rh_latest_compare_widget .title:before{content: "\f643"}
              .widget.top_offers .title, .widget.cegg_widget_products .title{ color: #fff; padding: 7px; text-align: center; position: relative;}
              .widget.top_offers .title:after, .widget.cegg_widget_products .title:after {top: 100%;left: 50%;border: solid transparent;content: " ";height: 0;width: 0;position: absolute;pointer-events: none;border-width: 8px;margin-left: -8px;}
            ';           
        }
        else if($type === 'postreadopt'){
            $output .= '
              .post-readopt .post-inner > h2{font-size: 28px; line-height: 34px}
              .post-readopt .post-inner > h3{font-size: 24px; line-height: 30px}
              .post-readopt .title_single_area h1{ font-size: 38px; line-height: 40px; }
              #rh_p_l_fullwidth_opt .post-readopt{max-width:900px; margin-left:auto; margin-right:auto}
               @media (min-width: 1024px){
                .post-readopt .ph-article-featured-image{    margin-left: 2.04082%;margin-right: 2.04082%;}
                .post-readopt.full_width .post-inner, .post-readopt:not(.main-side){margin-left: auto;margin-right: auto; max-width: 800px}
                .post-readopt.w_sidebar .post-inner{margin-left: 4%;margin-right: 4%;}
                .post-readopt blockquote p{font-size: 28px; line-height: 1.3em; }
                .post-readopt .wpsm_box, .post-readopt .rate_bar_wrap{font-size: 18px; line-height: 30px}
                .post-readopt .title_comments{display: none;}
                .post-readopt .post-meta-left{width: 100px; color: #666; text-transform: uppercase;}
                .post-readopt .leftbarcalc{width: calc(100% - 140px);}
              }
              @media (min-width:500px){
                .post-readopt .post-inner, .post-readopt:not(.main-side), .post-readopt .post{font-size: 18px;line-height: 1.85em;}
              }
            ';           
        }
        else if($type === 'postfullmax'){
            $output .= '
                .full_width .post-inner{max-width:960px; margin-left:auto; margin-right:auto}
            ';           
        }
        else if($type === 'fullwidthextended'){
            $output .= '
              .woo_full_width_extended .price del{display: block;}
              .woocommerce .woo_full_width_extended div.product:not(.product-type-variable) .summary .price{font-size: 1.8em}
            ';           
        }
        else if($type === 'accordion'){
            $output .= '
              .wpsm-accordion .wpsm-accordion-trigger { outline: none; display: block;padding: 15px; border: 1px solid #ddd; text-transform: none; font-weight: normal; font-size: 15px !important; line-height: 21px !important; margin: 10px 0 0; cursor: pointer; background: none #f9f9f9; position: relative; z-index: 1 }
              .wpsm-accordion .wpsm-accordion-trigger:before { content: "+"; display: inline-block; margin-right: 5px; font-size: 16px; }
              .wpsm-accordion .wpsm-accordion-trigger:hover { background-color: #eee; text-decoration: none; }
              .wpsm-accordion .wpsm-accordion-item.open .wpsm-accordion-trigger{ background: none #eee; text-decoration: none; }
              .wpsm-accordion .wpsm-accordion-item.open .wpsm-accordion-trigger:before { content: "–"; }
              .wpsm-accordion .wpsm-accordion-item.close .accordion-content{display: none;}
              .wpsm-accordion .accordion-content { background-color: #fff; padding: 15px; border: 1px solid #ddd; border-top: 0px; position: relative; z-index: 0 }
              .wpsm-accordion .accordion-content p:last-child { margin: 0px; }
              .rtl .wpsm-accordion .wpsm-accordion-trigger:before{margin-left: 5px;}
            ';           
        }
        else if($type === 'tabs'){
            $output .= '
              .tabs-menu:not(.rh-tab-shortcode) li{ list-style:none !important;cursor:pointer; float: left; margin: 0 8px 8px 0; text-decoration: none; background: #000;transition: all 0.3s; text-align: center; padding: 8px 14px; font-weight:700; font-size: 15px; line-height:16px; color: #fff; text-transform: uppercase; outline: 0}
              .wpsm-tabs ul.tabs-menu { display: block; margin: 0; padding: 0; margin-bottom: -1px;z-index: 1;position: relative; }
              .wpsm-tabs ul.tabs-menu li { display: block; height: 40px; padding: 0; float: left; margin: 0; outline: none;}
              .wpsm-tabs ul.tabs-menu li span { display: block; text-decoration: none; padding: 0px 20px; line-height: 38px; border: solid 1px #ddd; border-width: 1px 1px 0 0; margin: 0; background-color: #f5f5f5; font-size: 1em; }
              .wpsm-tabs ul.tabs-menu li span:hover {  background: #eee; }
              .wpsm-tabs ul.tabs-menu .current span { background: #fff; line-height: 36px; position: relative; margin: 0; border-top: 3px solid #fb7203;font-weight: bold;border-bottom: 1px solid #fff;}
              .wpsm-tabs ul.tabs-menu .current span:hover { background: #fff; }
              .wpsm-tabs ul.tabs-menu li:first-child span { margin-left: 0; border-left: 1px solid #ddd;}
              .wpsm-tabs .tab-content { background: #fff; padding: 20px; border: solid 1px #ddd; position: relative;z-index: 0 }
              .rtl .wpsm-tabs ul li{float: right;}
              .rtl .wpsm-tabs ul li:first-child span{border-left:none;}
              .rtl .wpsm-tabs ul li:last-child span{border-left: 1px solid #ddd;}
              @media screen and (max-width: 500px) {
                .wpsm-tabs ul.tabs-menu li{float:none !important}.wpsm-tabs ul.tabs-menu li span{border-left: 1px solid #ddd}
              }
            ';           
        }
        else if($type === 'fullwidthadvanced'){
            $output .= '
              .woo_full_width_advanced nav.woocommerce-breadcrumb{margin: 5px 0 20px 0; font-size: 13px}
              .review_score_min{text-align: left; width: 130px}
              .review_score_min th{background: none transparent !important; width: 82px}
              .woo-desc-w-review .woo_desc_part {width: calc(100% - 160px);}
              .woo-desc-w-review table{width: 100%}
              @media only screen and (max-width: 479px) {
                .woo-desc-w-review table td{text-align: right;}
                .review_score_min th{width: auto;}
              }
            ';           
        }
        else if($type === 'fullwidthmarketplace'){
            $output .= '
            .rh-300-content-area .woo-price-area, .rh-300-content-area .woo-price-area p{font-size:36px}
            .rh-300-content-area .woo-price-area .price del{font-size:45%; display:block}
            .rh-300-content-area .woo-price-area p{margin:0}
            .sticky-psn.rh-300-sidebar{z-index:99}
            ul.rh-big-tabs-ul .rh-big-tabs-li a{font-size:15px; padding:10px 14px}
              .woo_full_width_advanced nav.woocommerce-breadcrumb{margin: 5px 0 20px 0; font-size: 13px}
              .review_score_min{text-align: left; width: 130px}
              .review_score_min th{background: none transparent !important; width: 82px}
              .woo-desc-w-review .woo_desc_part {width: calc(100% - 160px);}
              .woo-desc-w-review table{width: 100%}
              .re_wooinner_cta_wrapper, .rh-300-sidebar .widget{border: 1px solid rgba(0,0,0,.1);border-radius:4px}
              .product_meta a{color:grey;font-style:italic}
              .top-woo-area{border-radius:4px}
              .rh-300-sidebar .widget{padding:15px}
              .rh-300-sidebar .widget .title{border:none; padding:0}
              .rh-300-sidebar .widget .title:after{display:none}
              .vendor_store_details{margin:0; background:none; border:none}
              .vendor_store_details_image, .vendor_store_details_single{padding: 0 10px 10px 0;}
              .rtl .vendor_store_details_image, .rtl .vendor_store_details_single{padding: 0 0 10px 10px;} 
              .content-woo-section--seller h2{border-bottom: 1px solid rgba(206,206,206,0.3); padding-bottom:10px; font-size:20px; font-weight:normal}
              @media only screen and (max-width: 479px) {
                .woo-desc-w-review table td{text-align: right;}
                .review_score_min th{width: auto;}
              }
            ';           
        }
        else if($type === 'barcompare'){
            $output .= '
              .wpsm-bar-compare .wpsm-bar-title, .wpsm-bar-compare .wpsm-bar-title span{background-color: transparent; font-size: 15px; font-weight: normal; z-index: 1;}
              .wpsm-bar-compare .wpsm-bar-title span{padding: 0 12px}
              .wpsm-bar-compare .wpsm-bar-bar{background-color: #aaa}
              .wpsm-bar-compare .wpsm-bar-percent{color: #fff; font-size: 14px; font-weight: bold;}
              .wpsm-bar-compare .wpsm-bar{background-color: #cdcdcd}
            ';           
        }
        else if($type === 'woodarkdir'){
            global $post;
            $custombg = get_post_meta($post->ID, '_woo_code_bg', true);
            if($custombg){
                $overcolor = hex2rgba($custombg, 0.65);
                $overcolorbg = hex2rgba($custombg, 0.25);
            }
            elseif (rehub_option('rehub_third_color')) {
                $overcoloror = rehub_option('rehub_third_color');
                $overcolor = hex2rgba($overcoloror, 0.83);
                $overcolorbg = hex2rgba($overcoloror, 0.25);
            }   
            else {
                $overcolor = 'rgb(18 1 94 / 83%)';
                $overcolorbg = '#090425';
            }
            $output .= '
              
              .top-woo-area .rh-post-layout-image-mask {background:'.$overcolor.'}
              .overbg.rh-post-layout-image-mask {background:'.$overcolorbg.'}
              .woodarkdir nav.woocommerce-breadcrumb{margin: 0px 0px 16px 0; font-size: 13px; line-height: 14px}
              .woodarkdir ul.rh-big-tabs-ul .rh-big-tabs-li.active a{color:#fff}
              #re-compare-icon-fixed{box-shadow:none}
              .cmp_crt_block .rate-bar-bar, .cmp_crt_block .rate-bar{height:10px}
              .woodarkdir .widget.top_offers, .woodarkdir .widget.cegg_widget_products{background:transparent}
              .woodarkdir .widget.top_offers h5 a, .woodarkdir .widget.cegg_widget_products h5 a{color:#fff}
              .woodarkdir .smart-scroll-desktop:hover::-webkit-scrollbar-thumb{background-color:#111}
              .woo-image-part .rh_videothumb_link:before{font-size:26px; height:30px; width:30px; margin: -13px 0 0 -13px;line-height:26px}
            ';           
        }
        else if($type === 'woodirectory'){
            $output .= '
              .woo_directory_layout p.price{margin-top: 0; margin-bottom: 0}
              .woo_directory_layout nav.woocommerce-breadcrumb{margin: 0px 0px 20px 0; font-size: 13px; line-height: 14px}
              .woo_directory_layout .woo-image-part{width: 150px;max-width: 150px}
              .woo_directory_layout h1{font-size: 24px;line-height: 28px;margin-bottom: 15px;}
              #rh-model-td-trigger .bluecolor{display:none}
              @media screen and (max-width: 767px){ .woo_directory_layout .score_text_r{margin-bottom: 5px}}
            ';           
        }
        else if($type === 'woostack'){
            $output .= '
            @media (min-width:768px){
            #woostackwrapper{
                display: grid;
                grid-auto-flow: column;
                grid-auto-columns: 1fr;
                grid-column-gap: 40px;
                grid-row-gap: 20px;
                grid-template-columns: 1fr minmax(400px,30%);
                -ms-grid-rows: auto;
                grid-template-rows: auto;
                align-items: flex-start;
            }}
            @media (max-width:768px){
                .rh_videothumb_link:before{font-size:26px; height:30px; width:30px; margin: -13px 0 0 -13px;line-height:26px}
            }
            ';           
        }
        else if($type === 'imagenavdot'){
            $output .= '
                #rh-product-images-dots{position:fixed;left:2%}
                .rhdot{width: 9px;height: 9px;border-radius: 100%;background: #999;display: block;border: 2px solid #fff;transition: border-color .5s;}
                .rhdot.current{border-color: transparent;}
            ';           
        }
        else if($type === 'cewooblocks'){
            $output .= '
              .ce_woo_blocks nav.woocommerce-breadcrumb{font-size: 13px; margin-bottom: 18px}
              .ce_woo_blocks .woo_bl_title h1{font-size: 22px; line-height: 26px; margin: 0 0 15px 0; font-weight: normal;}
              .review_score_min{text-align: left; width: 130px}
              .review_score_min th{background: none transparent !important; width: 82px}
              .woo-desc-w-review .woo_desc_part {width: calc(100% - 160px);}
              .woo-desc-w-review table{width: 100%}
              @media only screen and (max-width: 479px) {
                .woo-desc-w-review table td{text-align: right;}
                .review_score_min th{width: auto;}
              }
            ';           
        }
        else if($type === 'woocart'){
            $output .= '
            .woocommerce .cart-collaterals{margin-bottom:30px}
            .woocommerce table.cart, .woocommerce .cart-collaterals table{border:none}
            .woocommerce table.cart thead th, .woocommerce .cart-collaterals table thead th{border-right:none; font-size:15px}
            .woocommerce table.cart td, .woocommerce table.cart th, .woocommerce .cart-collaterals table th, .woocommerce .cart-collaterals table td{border-right:none; background:transparent; padding:15px}
            .woocommerce .cart-collaterals table tr:last-child th, .woocommerce .cart-collaterals table tr:last-child td {border-bottom: none;    padding-top: 22px;}
            table.shop_table_responsive td:not([colspan]):after{border-left:none !important}
            .woocommerce table.cart tr td:last-child, .woocommerce table.cart tr th:last-child {
                text-align: right;
            }
            .cart-collaterals ul li{list-style:none !important}
            .cart_totals .wc-proceed-to-checkout{ margin-top: 22px}
            tr.order-total th {
                font-size: 18px;
            }
            tr.order-total strong .amount {
                font-size: 22px;
            }
            .form-row-wide input[type="text"], .form-row-wide textarea, .form-row-wide select {
                box-shadow:none; border: 1px solid #ddd; font-size:15px; padding: 10px;
            }
              .woocommerce table.cart .product-thumbnail { min-width: 100px; text-align: center;}
              .woocommerce table.cart img { width: 50px; height: auto; }
              .woocommerce table.cart th{ vertical-align: middle; }
              .woocommerce table.cart a.remove{ display: block; font-size: 32px; height: 1em; width: 1em; text-align: center; line-height: 1; border-radius: 100%; color: red; text-decoration: none; margin: 0 auto; }
              .woocommerce table.cart a.remove:hover{ background-color: red; color: #fff; }
              .woocommerce table.cart td.actions { text-align: right; padding: 20px 0; border-bottom:none }
              .woocommerce table.cart td.actions .coupon { float: left; }
              .woocommerce table.cart td.actions .coupon label{ display: none; }
              .woocommerce table.cart td.actions .coupon .input-text{ box-shadow: none !important; float: left; border: 1px solid #ddd; padding: 7px 12px !important; margin: 0 4px 0 0; outline: 0; line-height: 1em; }
              .woocommerce table.cart td.actions .button.alt { float: right; margin-left: .25em; }
              .woocommerce table.cart input{ margin: 0; vertical-align: middle; line-height: 1em; }
              .woocommerce .cart-collaterals:after, .woocommerce-page .cart-collaterals:after { content: ""; display: block; clear: both; }
              .woocommerce .cart-collaterals .cross-sells .products, .woocommerce-page .cart-collaterals .cross-sells .products { float: none; }
              .woocommerce .cart-collaterals .shipping_calculator { width: 48%; text-align: right; margin: 20px 0 0 0; }
              .woocommerce .cart-collaterals .shipping_calculator:after { content: ""; display: block; clear: both; }
              .woocommerce .cart-collaterals .shipping_calculator .button { width: 100%; float: none; display: block; }
              .woocommerce .cart-collaterals .shipping_calculator .col2-set .col-1, .woocommerce .cart-collaterals .shipping_calculator .col2-set .col-2{ width: 47%; }
              .woocommerce .cart-collaterals .cart_totals { padding: 25px 25px 30px 25px;border: 3px solid #EFEFEF; font-size: 15px; }
                .woocommerce .cart-collaterals h2{font-size:20px; margin: 0 0 20px 0;    text-transform: uppercase;}
              .woocommerce .cart-collaterals .cart_totals p { margin: 0;line-height: 20px;color: #999;font-size: 14px; }
              .woocommerce .cart-collaterals .cart_totals p small { color: #777; font-size: .83em; }
              .woocommerce .cart-collaterals .cart_totals table { border-collapse: separate; border-radius: 5px; margin: 0 0 6px; padding: 0;}
              .woocommerce .cart-collaterals .cart_totals table tr:first-child th, .woocommerce .cart-collaterals .cart_totals table tr:first-child td { border-top: 0; }
              .woocommerce .cart-collaterals .cart_totals table td, .woocommerce .cart-collaterals .cart_totals table th { padding: 6px 3px; }
              .woocommerce .cart-collaterals .cart_totals table small { display: block; color: #777; }
              .woocommerce .cart-collaterals .cart_totals table select { width: 100%; }
              .woocommerce .cart-collaterals .cart_totals .discount td { color: #247600; }
              .woocommerce .cart-collaterals .cart_totals tr td, .woocommerce .cart-collaterals .cart_totals tr th {padding: 10px 15px; border-bottom: 1px solid #E6E6E6;}
              .woocommerce .cart-collaterals .cart_totals tr td:last-child,  .woocommerce .cart-collaterals .cart_totals tr th:last-child{text-align: right}
              .woocommerce .cart-collaterals .cart_totals a.button.alt { display: block; font-size: 18px; width: 100%;}
              @media (min-width:1300px){
                    .woocommerce-cart-form{width:67%; float:left}
                    .woocommerce .cart-collaterals { width: 30%;float: right; }
              }
              @media screen and (max-width: 767px){
                .woocommerce table.cart img{width: 100px;}
              }
              @media only screen and (max-width: 767px) and (min-width: 480px) {
                .woocommerce .cart-collaterals .shipping_calculator, .woocommerce .cart-collaterals .cart_totals { float: none !important; width: 100% !important; }
                .woocommerce table.cart td.actions .coupon, .woocommerce table.cart input.button { margin-bottom: 10px !important;}
              }
              @media only screen and (max-width: 479px) {
                .woocommerce .cart-collaterals .cart_totals, .woocommerce .cart-collaterals .shipping_calculator, .woocommerce table.cart td.actions .coupon { float: none !important; width: 100% !important; }
                .woocommerce table.cart td.actions .coupon .input-text{width: 100%; display: block; margin: 0 0 20px 0}
              }
              .rtl .woocommerce .cart-collaterals .cart_totals tr td:last-child, .rtl .woocommerce .cart-collaterals .cart_totals tr th:last-child, .rtl .woocommerce table.cart tr td:last-child, .rtl .woocommerce table.cart tr th:last-child{text-align: left}
              .rtl .woocommerce table.cart td.actions .coupon, .rtl .woocommerce table.cart td.actions .coupon .input-text{float:right}
              .rtl .woocommerce table.cart td.actions .coupon .input-text{margin-left:4px}
            ';           
        }
        else if($type === 'woomyaccount'){
            $output .= '
              .woocommerce-MyAccount-content .woocommerce-info{background-color: #fff;border:none;box-shadow:none}
              .woocommerce-MyAccount-navigation{float: left; width: 200px}
              .woocommerce-MyAccount-navigation > ul > li{list-style:none !important}
              .woocommerce-MyAccount-content {float: right;width: calc(100% - 220px);border: 1px solid #eee;padding: 18px;background: #fafafa; }
              .woocommerce-MyAccount-navigation ul{margin: 0 0 20px 0; overflow: hidden;}
              .woocommerce-MyAccount-navigation ul li{list-style: none; margin: 0; padding: 10px 0;border-top: 1px solid #eee;}
              .woocommerce-MyAccount-navigation ul li a{display: block;  font-weight: bold;text-decoration: none; font-size: 14px;}
              .woocommerce-MyAccount-navigation ul li a:not(.is-active){color: #111}
              .woocommerce-MyAccount-navigation ul li a:before{display:inline-block;content:"\f101";line-height:1.618;margin-left:.53em;width:1.387em;text-align:right;float:right;opacity:.25}.woocommerce-MyAccount-navigation ul li.woocommerce-MyAccount-navigation-link--dashboard a:before{content:"\f1de";}.woocommerce-MyAccount-navigation ul li.woocommerce-MyAccount-navigation-link--orders a:before{content:"\f291"}.woocommerce-MyAccount-navigation ul li.woocommerce-MyAccount-navigation-link--downloads a:before{content:"\f1c6"}.woocommerce-MyAccount-navigation ul li.woocommerce-MyAccount-navigation-link--edit-address a:before{content:"\f015"}.woocommerce-MyAccount-navigation ul li.woocommerce-MyAccount-navigation-link--payment-methods a:before{content:"\f09d"}.woocommerce-MyAccount-navigation ul li.woocommerce-MyAccount-navigation-link--edit-account a:before{content:"\f007"}.woocommerce-MyAccount-navigation ul li.woocommerce-MyAccount-navigation-link--customer-logout a:before{content:"\f08b"}.woocommerce-MyAccount-navigation ul li.woocommerce-MyAccount-navigation-link--subscriptions a:before{content:"\f021"}
              .woocommerce-MyAccount-navigation ul li a:hover:before, .woocommerce-MyAccount-navigation ul li.is-active a:before{opacity:1}
              .woocommerce-MyAccount-content legend{font-size: 18px; padding: 20px 0}
              .woocommerce-MyAccount-content fieldset{margin: 0 0 10px 0}
              .woocommerce-account.page .post{overflow: hidden}
              .woocommerce table.my_account_orders { font-size: .85em; }
              .woocommerce table.my_account_orders th, .woocommerce table.my_account_orders td { padding: 12px 8px; vertical-align: middle; }
              .woocommerce table.my_account_orders .button {margin: 6px;font-size: 13px;text-transform: none;font-weight: normal;padding: 5px 10px;}
              .woocommerce table.my_account_orders .order-actions{ text-align: center; }
              .woocommerce table.my_account_orders .order-actions .button { margin: .125em 0 .125em .25em; }
              #customer_login{position: relative;}
              #customer_login::before {border-right: 1px solid #eee;height: 100%;position: absolute;content: " ";right: 50%;top: 0;}
              #customer_login .col-1, #customer_login .col-2{}
              #customer_login .col-1{padding-right: 20px}
              #customer_login .col-2{padding-left: 20px}
              #customer_login h2{font-weight: 400}
              @media screen and (max-width: 767px){
                #customer_login::before{display: none;}
                #customer_login .col-1{padding-right: 0}
                #customer_login .col-2{padding-left: 0}
                .woocommerce-MyAccount-content, .woocommerce-MyAccount-navigation{float: none; width: 100%; margin: 0 0 20px 0}
              }
            ';           
        }
        else if($type === 'woocheckout'){
            $output .= '
              .woocommerce .checkout_coupon .button{ padding: 10px 16px}
              .woocommerce table.shop_table.woocommerce-checkout-review-order-table thead th, .woocommerce table.shop_table.woocommerce-checkout-review-order-table tfoot td, .woocommerce table.shop_table.woocommerce-checkout-review-order-table tfoot th{text-transform:uppercase;border-right: none; background: none transparent; padding: 0.8em 0}
              .woocommerce table.shop_table.woocommerce-checkout-review-order-table{border:none}
              .woocommerce table.shop_table.woocommerce-checkout-review-order-table .product-total, .woocommerce table.shop_table.woocommerce-checkout-review-order-table tfoot td{text-align: right}
              .woocommerce table.shop_table.woocommerce-checkout-review-order-table td{border-right:none; padding: 0.3em 0; font-size: 90%}
              .woocommerce form.checkout_coupon{ border-bottom: 1px dashed #ccc; padding: 0 0 20px 0; margin: 2em 0; text-align: left; overflow: auto; }
              #customer_details{float: left; width: 48%;}
              .re_woocheckout_order{float: right; width: 48%; border: 3px solid #dbdbdb; padding:30px;  background-color: #fff;}
              .re_woocheckout_order h3{margin-top: 0;text-transform: uppercase;}
              a.about_paypal, .lost_password a{font-size: 80%}
              a.about_paypal{display: block;}
              .woocommerce .woocommerce-checkout .col2-set .col-1, .woocommerce .woocommerce-checkout .col2-set .col-2{float: none; width: 100%}
              .woocommerce .checkout .col-2 h3 { float: left; clear: none; }
              .woocommerce .checkout .col-2 .notes { clear: left; }
              .woocommerce .checkout .col-2 .form-row-first { clear: left; }
              .woocommerce .checkout div.shipping-address{ padding: 0; clear: left; width: 100%; }
              .woocommerce .checkout #shiptobilling{ float: right; line-height: 1.62em; margin: 0; padding: 0; }
              .woocommerce .checkout #shiptobilling label { font-size: .6875em; }
              .woocommerce .checkout .shipping_address { clear: both; }
              .woocommerce #payment ul.payment_methods { margin: 0; list-style: none outside; }
              .woocommerce #payment ul.payment_methods:after { content: ""; display: block; clear: both; }
              .woocommerce #payment ul.payment_methods li{ line-height: 1.8em; text-align: left; margin: 0; font-weight: normal; list-style: none;}
              .woocommerce #payment ul.payment_methods li input{ margin: 0 1em 0 0; }
              .woocommerce #payment ul.payment_methods li img{ vertical-align: middle; margin: -2px 10px 0 .5em; position: relative; }
              .woocommerce #payment #place_order { float: right; margin: 0; }
              .processing.woocommerce-checkout .place-order:before{display:inline-block; content: "\f021" !important; animation: fa-spin 1s linear infinite; margin: 5px 10px; float: right;}
              .woocommerce #payment .terms{ padding: 0 1em 0; text-align: right; }
              .woocommerce #payment div.payment_box{ position: relative; padding: 1em 2%; margin: 1em 0 1em 0; font-size: .92em; border-radius: 2px; line-height: 1.5em; background: #c5eafd; box-shadow: 0 1px 2px 0 rgba(0,0,0,0.25);  color: #5e5e5e; text-shadow: 0 1px 0 rgba(255,255,255,0.8); }
              .woocommerce #payment div.payment_box p:last-child{ margin-bottom: 0; }
              .woocommerce #payment div.payment_box #cc-expire-month, .woocommerce #payment div.payment_box #cc-expire-year{ width: 48%; float: left; }
              .woocommerce #payment div.payment_box #cc-expire-year{ float: right; }
              .woocommerce #payment div.payment_box span.help{ font-size: 11px; color: #777; line-height: 13px; font-weight: normal; }
              .woocommerce #payment div.payment_box .form-row{ margin: 0 0 1em; }
              .woocommerce #payment div.payment_box .form-row select { width: 48%; float: left; margin-right: 3.8%; }
              .woocommerce #payment div.payment_box .form-row select:nth-child(3n) { margin-right: 0 }
              .woocommerce #payment div.payment_box:after { content: ""; display: block; border: 8px solid #c5eafd; border-right-color: transparent; border-left-color: transparent; border-top-color: transparent; position: absolute; top: 0; left: 0; margin: -1em 0 0 2em; }
              #wpmc-prev{margin: 0 10px}
              @media screen and (max-width: 767px){
                #customer_details, .re_woocheckout_order{ width: 100%; float: none; }
              }

            ';           
        }
        else if($type === 'woocompactlayout'){
            $output .= '
              .woo_compact_layout h1{font-size: 24px;line-height: 28px;margin-bottom: 15px;}
              .woo_compact_layout .woo-image-part{width: 130px; height: 130px}
              .woo_compact_layout .woo-image-part img{max-height: 146px; border: 1px solid #ededed;}
              .woo_compact_layout div.product .single_add_to_cart_button{width: auto; padding: 10px 22px}
              .woo_compact_layout .woo-top-actions .woo-button-actions-area .wpsm-button.rhwoosinglecompare, .woo_compact_layout .woo-top-actions .woo-button-actions-area .heartplus{border: 1px solid #f1f1f1; color: #f1f1f1;}
              .woo_compact_layout .woo-button-actions-area .wpsm-button.rhwoosinglecompare:hover, .woo_compact_layout .woo-button-actions-area .heartplus:hover{background: #ff6c00}
              .woocommerce .woo_compact_layout .single_add_to_cart_button, .woocommerce .woo_compact_layout div.product form.cart .button{box-shadow: none !important;}
              .woo_compact_layout .right_aff{border:none !important; width: auto}
              .woo_compact_layout .right_aff p{margin-top: 0; margin-bottom: 0}
              .right_aff { float: right; width: 35%; margin: 0 0 0 20px; position: relative; }
              .right_aff .priced_block .btn_offer_block, .right_aff .priced_block .button { position: absolute; top: -26px; right: 0; padding: 15px; box-shadow: none; }
              .right_aff .priced_block .price_count { position: relative; top: -38px; left: 0; padding: 28px 12px 22px 12px; font-size:15px; line-height: 15px; font-weight: bold; text-shadow: 0 1px 1px #FFF9E7;    background: #F9CC50;color: #111; }
              .right_aff .priced_block .price_count:before { width: 0; height: 0; border-style: solid; border-width: 13px 0 0 8px; border-color: transparent transparent transparent #967826; content: ""; position: absolute; top: 0; right: -8px }
              .right_aff .priced_block .price_count .triangle_aff_price { width: 0; height: 0; border-style: solid; border-color: #f9cc50 transparent transparent transparent; content: ""; position: absolute; top: 100%; left: 0 }
              .right_aff .priced_block .price_count ins { border: 1px dashed #444; padding: 5px 0; border-left: none; border-right: none; }
              .right_aff .priced_block .price_count del{display: none;}
              .right_aff .priced_block .price_count, .right_aff .priced_block .btn_offer_block, .right_aff .priced_block .button, .custom_search_box button[type="submit"]{border-radius: 0 !important}
              .post .right_aff .priced_block{ margin: 20px 0 26px 0}
              .right_aff .rehub_offer_coupon{display: block;}
              .right_aff .priced_block .btn_offer_block:active{ top: -25px} 
              .right_aff .not_masked_coupon{margin-top: 40px}
              .wpsm_score_box .rate_bar_wrap{ background-color: transparent; padding: 0; border: none; box-shadow: none; margin: 0}
              .wpsm_inside_scorebox .rate_bar_wrap .review-criteria{ border: none}
              .wpsm_score_box .rate-bar, .wpsm_score_box .rate-bar-bar, .cmp_crt_block .rate-bar, .cmp_crt_block .rate-bar-bar{ height: 9px}
              @media screen and (max-width: 1023px) and (min-width: 768px){
                  .right_aff .priced_block .btn_offer_block, .right_aff .priced_block .button{right: -25px}
              }
            ';           
        }
        $output .='</style>';
        return apply_filters('rh_generate_incss_filter', $output );
    } 
}

//FILTER FUNCTION FOR MDTF
if(class_exists('MetaDataFilter')){
    
    add_filter('rh_category_args_query', 'rh_module_args_filter');
    add_filter('rh_module_args_query', 'rh_module_args_filter');
    add_action('rh_after_module_args_query', 'rh_module_args_filter_after');
    add_action('rh_after_category_args_query', 'rh_module_args_filter_after');

    if (!function_exists('rh_module_args_filter')){
        function rh_module_args_filter($args){
            $additional_tax_query_array = array();
            if (is_category()){
                $catID = get_query_var( 'cat' );
                $additional_tax_query_array[] = array(
                    'taxonomy' => 'category',
                    'field' => 'term_id',
                    'terms' => array($catID)
                );
                $_REQUEST['MDF_ADDITIONAL_TAXONOMIES'] = $additional_tax_query_array;               
            } 
            if(is_tax('dealstore')){
                $tagid = get_queried_object()->term_id;
                $additional_tax_query_array[] = array(
                    'taxonomy' => 'dealstore',
                    'field' => 'term_id',
                    'terms' => array($tagid)
                );
                $_REQUEST['MDF_ADDITIONAL_TAXONOMIES'] = $additional_tax_query_array;                 
            }             
            if(MetaDataFilter::is_page_mdf_data()){   
                $_REQUEST['mdf_do_not_render_shortcode_tpl'] = true;
                $_REQUEST['mdf_get_query_args_only'] = true;
                do_shortcode('[meta_data_filter_results]');
                $args = $_REQUEST['meta_data_filter_args']; 
            }
            return $args;
        }
    }
    if (!function_exists('rh_module_args_filter_after')){
        function rh_module_args_filter_after($wp_query){           
            if(MetaDataFilter::is_page_mdf_data()){
                $_REQUEST['meta_data_filter_found_posts']=$wp_query->found_posts;
            }
        }
    }   
}

// Exclude scripts from JS delay WP Rocket.
function rh_wp_rocket__exclude_from_delay_js( $excluded_strings = array() ) {

    // MUST ESCAPE PERIODS AND PARENTHESES!
	$excluded_strings[] = "/jquery-?[0-9.](.*)(.min|.slim|.slim.min)?.js";
	$excluded_strings[] ="/jquery-migrate(.min)?.js";
	$excluded_strings[] = '/js/inview\.js';
	$excluded_strings[] = '/js/hoverintent\.js';
	$excluded_strings[] = "/js/pgwmodal\.js";	
	$excluded_strings[] = "/js/unveil\.js";	
	$excluded_strings[] = "/js/yall\.js";
	$excluded_strings[] = "/js/countdown\.js";
	$excluded_strings[] = "/js/custom\.js";
	$excluded_strings[] = "/js/quantity\.js";	
	$excluded_strings[] = "/js/wooswatch\.js";
	$excluded_strings[] = "/js/jquery\.flexslider-min\.js";
	$excluded_strings[] = "/js/flexinit\.js";
	$excluded_strings[] = "/js/jquery\.totemticker\.js";
	$excluded_strings[] = "/js/jquery\.carouFredSel-6\.2\.1-packed\.js";
	$excluded_strings[] = "/js/jquery\.sticky\.js";
	$excluded_strings[] = "/js/jquery\.nouislider\.full\.min\.js";	
	$excluded_strings[] = "/js/wpsm_googlemap\.js";
	$excluded_strings[] = "/js/owl\.carousel\.min\.js";
	$excluded_strings[] = "/js/owlinit\.js";
	$excluded_strings[] = "/js/video_playlist\.js";
	$excluded_strings[] = "/js/stickysidebar\.js";
	$excluded_strings[] = "/js/tablechart\.js";
	$excluded_strings[] = "/js/comparechart\.js";
	$excluded_strings[] = "/js/jquery\.waypoints\.min\.js";
	$excluded_strings[] = "/js/jquery\.justifiedGallery\.min\.js";
	$excluded_strings[] = "/js/modulobox\.min\.js";
	$excluded_strings[] = "/js/particles\.min\.js";
	$excluded_strings[] = "gsap\.min\.js";
	$excluded_strings[] = "/js/ScrollTrigger\.min\.js";
	$excluded_strings[] = "/js/gsap-init\.js";
	$excluded_strings[] = "/js/SplitText\.min\.js";
	$excluded_strings[] = "/js/DrawSVGPlugin\.min\.js";
	$excluded_strings[] = "/js/MotionPathPlugin\.min\.js";
	$excluded_strings[] = "/js/videocanvas\.js";
	$excluded_strings[] = "/js/blobcanvas\.js";
	$excluded_strings[] = "/js/lottie\.min\.js";
	$excluded_strings[] = "/js/lottie-init\.js";
	$excluded_strings[] = "/js/videolazy\.js";
	$excluded_strings[] = "/js/wishcount\.js";
	$excluded_strings[] = "/js/filterpanel\.js";
	$excluded_strings[] = "/js/elajaxloader\.js";
	$excluded_strings[] = "/js/alignfull\.js";
	$excluded_strings[] = "/js/vertmenu\.js";
	return $excluded_strings;
}
add_filter( 'rocket_delay_js_exclusions', 'rh_wp_rocket__exclude_from_delay_js' );

function rh_safelist_css_wprocket($safelist) {
    $safelist[] = '/css/ajaxsearch.css';
	$safelist[] = '/css/comparesearch.css';
	$safelist[] = '/css/dynamiccomparison.css';
	$safelist[] = '/css/modelviewer.css';
	$safelist[] = '/css/quantity.css';
    $safelist[] = '/css/niceselect.css';
	$safelist[] = '/css/slidingpanel.css';
	$safelist[] = '.mb(.*)';
	$safelist[] = '.mt(.*)';
	$safelist[] = '.mr(.*)';
	$safelist[] = '.ml(.*)';
	$safelist[] = '.pt(.*)';
	$safelist[] = '.pb(.*)';
	$safelist[] = '.pr(.*)';
	$safelist[] = '.pl(.*)';
	$safelist[] = '.floatleft';
	$safelist[] = '.floatright';
	$safelist[] = '.fa-spin';
	$safelist[] = '.re_loadingafter';
	$safelist[] = '.re_loadingbefore';
	$safelist[] = '.rh-line(.*)';
	$safelist[] = '.rhi-(.*)';
	$safelist[] = '.width-(.*)';
	$safelist[] = '.height-(.*)';
	$safelist[] = '.lineheight(.*)';
	$safelist[] = '.font(.*)';
	$safelist[] = '.rh-flex(.*)';
	$safelist[] = '.rhhidden';
	$safelist[] = '.flowhidden';
	$safelist[] = '.inlinestyle';
	$safelist[] = '.text-center';
	$safelist[] = '.redcolor';
	$safelist[] = '.orangecolor';
	$safelist[] = '.whitecolor';
	$safelist[] = '.blackcolor';
	$safelist[] = '.greencolor';
	$safelist[] = '.greycolor';
	$safelist[] = '.bluecolor';
    return $safelist;
}
add_filter( 'rocket_rucss_safelist', 'rh_safelist_css_wprocket');

if(!function_exists('rh_post_code_loop')){
	function rh_post_code_loop($template=''){
		block_template_part( 'post-loop' );
	}
}