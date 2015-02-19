<?php
/**
 *
 * Plugin Name: Oxford Debates Wordpress
 * Plugin URI: http://cws-tech.com
 * Description: The online version of the Oxford-style debates adapte the physical model and makes it possible to expand 
 * the capabilities of both speakers and audience. The speakers may argue using web connectivity and multimedia, 
 * and the audience can also comment fixing its position on the proposals of the speakers or raising their own alternatives.
 * Version: 0.5.2
 * Author: Rafa Fernandez
 * Author URI: http://cws-tech.com
 *
 **/

if (!function_exists('is_admin')) {
    header('Status: 403 Forbidden');
    header('HTTP/1.1 403 Forbidden');
    exit();
}

define( 'OXD_VERSION', '0.1.0' );
define( 'OXD_DIR', plugin_dir_path( __FILE__ ) );

if (!class_exists("Oxd")) :

class Oxd {
	var $settings, $options_page;
	
	function __construct() {	
		if (is_admin()) {
			// Load example settings page
			if (!class_exists("Oxd_Settings"))
				require(OXD_DIR . 'oxd-settings.php');
			$this->settings = new OxD_Settings();	
		}

		add_action('init', array($this,'init') );
		add_action('admin_init', array($this,'admin_init') );
		add_action('admin_menu', array($this,'admin_menu') );
		
		register_activation_hook( __FILE__, array($this,'activate') );
		register_deactivation_hook( __FILE__, array($this,'deactivate') );
	}

	function activate($networkwide) {

	}

	function deactivate($networkwide) {

	}

	/*
		Enter our plugin activation code here.
	*/
	function _activate() {}

	/*
		Enter our plugin deactivation code here.
	*/
	function _deactivate() {}

	function init() {
		load_plugin_textdomain( 'oxd', OXD_DIR . 'lang', 
							   basename( dirname( __FILE__ ) ) . '/lang' );
	}

	function admin_init() {
	}

	function admin_menu() {
	}


} // end class
endif;

// Initialize our plugin object.
global $oxd;
if (class_exists("Oxd") && !$oxd) {
    $oxd = new Oxd();	
}	


add_shortcode( 'debates_q', 'oxddebate_listing_shortcode' );
function oxddebate_listing_shortcode( $atts ) {
    ob_start();
    $query = new WP_Query( array(
        'post_type' => 'debate',
        'posts_per_page' => -1,
        'order' => 'ASC',
        'orderby' => 'title',
    ) );
    if ( $query->have_posts() ) { ?>
        <ul class="clothes-listing">
            <?php while ( $query->have_posts() ) : $query->the_post(); ?>
            <li id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
                <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
            </li>
            <?php endwhile;
            wp_reset_postdata(); ?>
        </ul>
    <?php $myvariable = ob_get_clean();
    return $myvariable;
    }
}

function create_debatepost_type() {

			$labels = array(
				'name'                => 'Debates',
				'singular_name'       => 'Debate',
				'menu_name'           => 'Debates',
				'all_items'           => 'All Debates',
				'view_item'           => 'View Debate',
				'add_new'          	  => 'Add Debate',
				'parent_item_colon'	  => '',
			);
			$args = array(
				'labels'              => $labels,
				'supports'            => array('title','editor', 'author', 'thumbnail', 'excerpt', 'comments'),
				'hierarchical'        => false,
				'public'              => true,
				'show_ui'             => true,
				'show_in_menu'        => true,
				'show_in_nav_menus'   => true,
				'show_in_admin_bar'   => true,
				'rewrite'			  => array( 'slug' => 'debate'),
				'menu_position'       => null,
				'has_archive'         => true,
				'publicly_queryable'  => true,
				'capability_type'     => 'post',
			);

			register_post_type( 'debate', $args );
			
		}

add_action( 'init', 'create_debatepost_type' ,0 );

function my_taxonomies_debate() {
   $labels = array(
    'name'              => _x( 'Debates Categories', 'debate' ),
    'singular_name'     => _x( 'Debate Category', 'debate' ),
    'search_items'      => __( 'Search Debate Categories' ),
    'all_items'         => __( 'All Debate Categories' ),
    'parent_item'       => __( 'Parent Debate Category' ),
    'parent_item_colon' => __( 'Parent Debate Category:' ),
    'edit_item'         => __( 'Edit Debate Category' ), 
    'update_item'       => __( 'Update Debate Category' ),
    'add_new_item'      => __( 'Add New Debate Category' ),
    'new_item_name'     => __( 'New Debate Category' ),
    'menu_name'         => __( 'Debate Categories' ),
  );
  $args = array(
    'labels' => $labels,
    'hierarchical' => true,
    'rewrite' => array('slug' => 'debate'),
  );
  register_taxonomy( 'debate_category', 'debate', $args );
}

add_action( 'init', 'my_taxonomies_debate', 0 );

add_action( 'comment_post', 'save_comment_meta_data' );

function save_comment_meta_data( $comment_id ) {
	if ( isset( $_POST['posture'] ) )
   		add_comment_meta( $comment_id, 'posture', $_POST[ 'posture' ] );
}

add_filter( 'get_comment_author_link', 'attach_posture_to_author' );

function attach_posture_to_author( $author ) {
   	$posture = get_comment_meta( get_comment_ID(), 'posture', true );
   	if ( $posture )
  		$author .= " ($posture)";
   	return $author;
}

add_filter( 'template_include', 'template_loader');

function template_loader( $template ) {
	$file = '';
	if ( is_single() && get_post_type() == 'debate' ) {
                        $file   = 'single-debate.php';
                        $find[] = $file;
                        //$find[] = WC()->template_path() . $file;
	}

	if ( is_single() && get_post_type() == 'debate' ) {
                        $file   = 'single-debate.php';
                        $find[] = $file;
                        //$find[] = WC()->template_path() . $file;
	}

	if ( $file ) {
        $template       = locate_template( array_unique( $find ) );
            if ( ! $template  ) {
                   $template = plugin_path() . '/templates/' . $file;
            } 
        }

	return $template;

 	}

/**
    * Get the plugin url.
    * @return string
*/
function plugin_url() {
    return untrailingslashit( plugins_url( '/', __FILE__ ) );
}
 /**
    * Get the plugin path.
    * @return string
*/
function plugin_path() {
    return untrailingslashit( plugin_dir_path( __FILE__ ) );
}

add_filter( 'comments_template',  'comments_template_loader'  );

function comments_template_loader( $template ) {

            if ( get_post_type() !== 'debate' ) {
                    return $template;
            }
  
            $check_dirs = array(
                    trailingslashit( get_stylesheet_directory() ) . plugin_path(),
                    trailingslashit( get_template_directory() ) . plugin_path(),
                    trailingslashit( get_stylesheet_directory() ),
                    trailingslashit( get_template_directory() ),
                    trailingslashit( plugin_path() ) . 'templates/'
            );

            
            foreach ( $check_dirs as $dir ) {
                    if ( file_exists( trailingslashit( $dir ) . 'comments-debate.php' ) ) {
                            return trailingslashit( $dir ) . 'comments-debate.php';
                    }
            }
}

?>