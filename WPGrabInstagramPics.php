<?php
/*
Plugin Name: Grab Instagram Pics
Plugin URI: http://www.davidbisset.com/wp-grab-instagram-pics
Description: This plugin will search through recent Instagram posts (containing a certain hashtag), and import those photos along with metadata into a custom post type.
Version: 0.3
Author: David Bisset
Author URI: http://www.davidbisset.com
Author Email: dbisset@dimensionmedia.com
License:

  Copyright 2013 David Bisset (dbisset@dimensionmedia.com)

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License, version 2, as
  published by the Free Software Foundation.

  This program is distributed in the hope that it will be useful,
  but WITHOUT ANY WARRANTY; without even the implied warranty of
  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
  GNU General Public License for more details.

  You should have received a copy of the GNU General Public License
  along with this program; if not, write to the Free Software
  Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA

*/

/**
 * This is to be considered a "bare bones" plugin, to be incorporated into other larger plugins and themes.
 *
 * @version	1.0
 */
class WPGrabInstagramPics {

	/*--------------------------------------------*
	 * Attributes
	 *--------------------------------------------*/
	 
	/** Refers to a single instance of this class. */
	private static $instance = null;
	
	/** Refers to the slug of the plugin screen. */
	private $wpgip_screen_slug = null;
	

	/*--------------------------------------------*
	 * Constructor
	 *--------------------------------------------*/
	 
	/**
	 * Creates or returns an instance of this class.
	 *
	 * @return	WPGrabInstagramPics	A single instance of this class.
	 */
	public function get_instance() {
		return null == self::$instance ? new self : self::$instance;
	} // end get_instance;

	/**
	 * Initializes the plugin by setting localization, filters, and administration functions.
	 */
	private function __construct() {

	
		/**
		 * Load needed include files
		 */

		 // There used to be an external instagram lib, but no need now.

		/**
		 * Define globals
		 */
    		if ( ! defined('WP_GRAB_INSTAGRAM_PICS_PERMISSIONS') ) define("WP_GRAB_INSTAGRAM_PICS_PERMISSIONS", "manage_options");

		/**
		 * Load plugin text domain
		 */
		add_action( 'init', array( $this, 'wpgip_textdomain' ) );

	    /*
	     * Add the options page and menu item.
	     */
	    add_action( 'admin_menu', array( $this, 'wpgip_admin_menu' ) );
		add_action( 'admin_init', array( $this, 'wpgip_admin_init' ) );

	    /*
		 * Register site stylesheets and JavaScript
		 */
		add_action( 'wp_enqueue_scripts', array( $this, 'register_wpgip_styles' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'register_wpgip_scripts' ) );

	    /*
		 * Register hooks that are fired when the plugin is activated, deactivated, and uninstalled, respectively.
		 */
		register_activation_hook( __FILE__, array( $this, 'activate' ) );
		register_deactivation_hook( __FILE__, array( $this, 'deactivate' ) );

	    /*
	     * Here's where we define the custom functionality
	     */     
	     
		add_action( "admin_post_grab_instagrams", array ( $this, 'wpgip_grab_instagram_posts' ) );	
		add_action( "admin_post_wpgip_clear_settings", array ( $this, 'wpgip_clear_settings' ) );	
        add_action( "admin_notices", array ( $this, 'render_msg' ) );
        add_action( "init", array ( $this, 'wpgip_register_cpt' ) );
        add_action( "init", array ( $this, 'wpgip_register_tax' ) );




	} // end constructor
	
	
	
	
     


	/**
	 * Fired when the plugin is activated.
	 *
	 * @param	boolean	$network_wide	True if WPMU superadmin uses "Network Activate" action, false if WPMU is disabled or plugin is activated on an individual blog
	 */
	public function activate( $network_wide ) {

	} // end activate

	/**
	 * Fired when the plugin is deactivated.
	 *
	 * @param	boolean	$network_wide	True if WPMU superadmin uses "Network Activate" action, false if WPMU is disabled or plugin is activated on an individual blog
	 */
	public function deactivate( $network_wide ) {

	} // end deactivate

	/**
	 * Loads the plugin text domain for translation
	 */
	public function wpgip_textdomain() {

		$domain = 'wp-grab-instagram-pics-locale';
		$locale = apply_filters( 'wpgip_locale', get_locale(), $domain );
		
        load_textdomain( $domain, WP_LANG_DIR . '/' . $domain . '/' . $domain . '-' . $locale . '.mo' );
        load_plugin_textdomain( $domain, FALSE, dirname( plugin_basename( __FILE__ ) ) . '/lang/' );

	} // end wpgip_textdomain

	/**
	 * Registers and enqueues admin-specific styles.
	 */
	public function register_admin_styles() {

		/*
		 * Check if the plugin has registered a settings page
		 * and if it has, make sure only to enqueue the scripts on the relevant screens
		 */
		
	    if ( isset( $this->wpgip_screen_slug ) ){
	    	
	    	/*
			 * Check if current screen is the admin page for this plugin
			 * Don't enqueue stylesheet or JavaScript if it's not
			 */
	    
			 $screen = get_current_screen();
			 if ( $screen->id == $this->wpgip_screen_slug ) {
			 	wp_enqueue_style( 'wpgip-name-admin-styles', plugins_url( 'css/admin.css', __FILE__ ) );
			 } // end if
	    
	    } // end if
	    
	} // end register_admin_styles

	/**
	 * Registers and enqueues admin-specific JavaScript.
	 */
	public function register_admin_scripts() {

		/*
		 * Check if the plugin has registered a settings page
		 * and if it has, make sure only to enqueue the scripts on the relevant screens
		 */
		
	    if ( isset( $this->wpgip_screen_slug ) ){
	    	
	    	/*
			 * Check if current screen is the admin page for this plugin
			 * Don't enqueue stylesheet or JavaScript if it's not
			 */
	    
			 $screen = get_current_screen();
			 if ( $screen->id == $this->wpgip_screen_slug ) {
			 	wp_enqueue_script( 'wpgip-name-admin-script', plugins_url( 'js/admin.js', __FILE__ ), array( 'jquery' ) );
			 } // end if
	    
	    } // end if

	} // end register_admin_scripts

	/**
	 * Registers and enqueues wpgip-specific styles.
	 */
	public function register_wpgip_styles() {
		// wp_enqueue_style( 'wpgip-name-wpgip-styles', plugins_url( 'css/display.css', __FILE__ ) );
	} // end register_wpgip_styles

	/**
	 * Registers and enqueues wpgip-specific scripts.
	 */
	public function register_wpgip_scripts() {
		// wp_enqueue_script( 'wpgip-name-wpgip-script', plugins_url( 'js/display.js', __FILE__ ), array( 'jquery' ) );
	} // end register_wpgip_scripts

	/**
	 * Registers the administration menu for this plugin into the WordPress Dashboard menu.
	 */
	public function wpgip_admin_menu() {
	    	
	    add_menu_page(
	        __("Grab Instagram Pics : Settings"),
	        __("Grab Instagram Pics"),
	        WP_GRAB_INSTAGRAM_PICS_PERMISSIONS,
	        "wp-grab-instagram-pics",
	        array( $this, 'wpgip_settings_page' )
	    );
	    add_submenu_page(
	        "wp-grab-instagram-pics",
	        __("Grab Instagram Pics : Settings"),
	        __("Settings"),
	        WP_GRAB_INSTAGRAM_PICS_PERMISSIONS,
	        "wp-grab-instagram-pics",
	        array( $this, 'wpgip_settings_page' )
	    );
	    add_submenu_page(
	        "wp-grab-instagram-pics",
	        __("Grab Instagram Pics : Grab Instagram Posts"),
	        __("Grab"),
	        WP_GRAB_INSTAGRAM_PICS_PERMISSIONS,
	        "wp-grab-instagram-pics-items",
	        array( $this, 'wpgip_grab_page' )
	    );
    	
	} // end wpgip_admin_menu
	
	/**
	 * Renders the settings page for this plugin.
	 *
	 * @since 0.1
	 */
	public function wpgip_settings_page() {
		$redirect = urlencode( remove_query_arg( 'msg', $_SERVER['REQUEST_URI'] ) );
		$redirect = urlencode( $_SERVER['REQUEST_URI'] );
        
		$action_name = 'wpgip_clear_settings';
		$nonce_name = 'wp-grab-instagram-pics';

		?>
		<div class="wrap">
			<?php screen_icon( 'options-general' ); ?>
			<h2><?php _e( 'Grab Instagram Pics Settings' ); ?></h2>
			<?php settings_errors(); ?>
	        
	        <?php /* $max_id = esc_attr( get_option( 'wpgip_instagram_gallery_max_id' ) ); ?>
	        <p>Currently, max_id of instagram is <?php echo $max_id; ?></p> */ ?>
	        
			<form action="<?php echo admin_url( 'admin-post.php' ); ?>" method="POST">
				<input type="hidden" name="action" value="<?php echo $action_name; ?>">
				<?php wp_nonce_field( $action_name, $nonce_name . '_nonce', FALSE ); ?>
				<input type="hidden" name="_wp_http_referer" value="<?php echo $redirect; ?>">
				<?php do_settings_sections( 'wp-grab-instagram-pics-stats' ); ?>
				<?php submit_button( 'Clear All Stats' ); ?>
			</form>
	        

			<form action="options.php" method="POST">
				<?php settings_fields( 'wpgip-options-group' ); ?>
				<?php do_settings_sections( 'wp-grab-instagram-pics-options' ); ?>
				<?php submit_button(); ?>
			</form>
		</div>
		<?php
	}
	
	/**
	 * Renders the grab page for this plugin.
	 *
	 * @since 0.1
	 */	 
	public function wpgip_grab_page() {	
		$redirect = urlencode( remove_query_arg( 'msg', $_SERVER['REQUEST_URI'] ) );
		$redirect = urlencode( $_SERVER['REQUEST_URI'] );
        
		$action_name = 'grab_instagrams';
		$nonce_name = 'wp-grab-instagram-pics';
	
		?>
		<div class="wrap">
			<?php screen_icon( 'edit-pages' ); ?>
			<h2><?php _e( 'Grap Instagram Posts' ); ?></h2>

			<form action="<?php echo admin_url( 'admin-post.php' ); ?>" method="POST">
				<input type="hidden" name="action" value="<?php echo $action_name; ?>">
				<?php wp_nonce_field( $action_name, $nonce_name . '_nonce', FALSE ); ?>
				<input type="hidden" name="_wp_http_referer" value="<?php echo $redirect; ?>">
				<?php submit_button( 'Grab Posts' ); ?>
			</form>
		</div>
		<?php
	}


	/**
	 * This inits the sections and fields in the settings screens
	 */
	
	public function wpgip_admin_init() {
	
	    register_setting( 'wpgip-stats-group', 'wpgip-stats' );
	    add_settings_section( 'section-stats', 'Stats', array( $this, 'options_stats_callback' ), 'wp-grab-instagram-pics-stats' );
	    add_settings_field( 'section-stats-last-grab', 'Last Grab', array( $this, 'options_lastgrab_field_callback' ), 'wp-grab-instagram-pics-stats', 'section-stats' );
	    	
	    register_setting( 'wpgip-options-group', 'wpgip-hashtag' );
	    register_setting( 'wpgip-options-group', 'wpgip-instagram-client-id' );
	    add_settings_section( 'section-options', 'Options', array( $this, 'options_section_callback' ), 'wp-grab-instagram-pics-options' );
	    add_settings_field( 'section-options-hashtag', 'Keyword', array( $this, 'options_hashtag_field_callback' ), 'wp-grab-instagram-pics-options', 'section-options' );
	    add_settings_field( 'section-options-instagram-client-id', 'Instagram Client ID', array( $this, 'options_instagram_client_id_field_callback' ), 'wp-grab-instagram-pics-options', 'section-options' );

	    
	} // end wpgip_admin_init

		function options_stats_callback() {
			// nothing to say here, but just in case
		}

		function options_maxid_field_callback() {
		    $setting = esc_attr( get_option( 'wpgip_instagram_gallery_max_id' ) );
		    if (!$setting) {
				echo '<em>No max_id yet</em>';   
		    } else { 
			    echo $setting;
			}
		}
		
		function options_lastgrab_field_callback() {
		    $setting = esc_attr( get_option( 'wpgip_instagram_last_grab' ) );
		    if (!$setting) {
				echo '<em>Nothing has been attempted.</em>'; 
		    } else { 
			    echo date('F jS, Y g:ia T', $setting);
			}
		}
	
		function options_section_callback() {
		    echo 'Keywords are usually hashtags. Include the "#". For example: "#wcmia"';
		}
		
		function options_hashtag_field_callback() {
		    $setting = esc_attr( get_option( 'wpgip-hashtag' ) );
		    echo "<input type='text' name='wpgip-hashtag' value='$setting' />";
		}
				
		function options_instagram_client_id_field_callback() {
		    $setting = esc_attr( get_option( 'wpgip-instagram-client-id' ) );
		    echo "<input type='text' name='wpgip-instagram-client-id' value='$setting' />";
		}
		
	
	
	/*--------------------------------------------*
	 * Core Functions
	 *---------------------------------------------*/


	 
	 
	/*
	 * Register CTP
	 */
	 
	public function wpgip_register_cpt() {
		
	    $labels = array( 
	        'name' => _x( 'Instagram Posts', 'faq' ),
	        'singular_name' => _x( 'Instagram Post', 'faq' ),
	        'add_new' => _x( 'Add New', 'faq' ),
	        'add_new_item' => _x( 'Add New Instagram Post', 'faq' ),
	        'edit_item' => _x( 'Edit Instagram Post', 'faq' ),
	        'new_item' => _x( 'New Instagram Post', 'faq' ),
	        'view_item' => _x( 'View Instagram Post', 'faq' ),
	        'search_items' => _x( 'Search Instagram Posts', 'faq' ),
	        'not_found' => _x( 'No Tweets found', 'faq' ),
	        'not_found_in_trash' => _x( 'No Instagram Posts found in Trash', 'faq' ),
	        'parent_item_colon' => _x( 'Parent Instagram Post:', 'faq' ),
	        'menu_name' => _x( 'Instagram Posts', 'faq' ),
	    );
	    
	    //set up the rewrite rules
	    $rewrite = array(
	        'slug' => 'instagram-posts'
	    );
	
	    $args = array( 
	        'labels' => $labels,
	        'hierarchical' => false,
	        'description' => 'Stored posts from Instagram.',
	        'supports' => array( 'title', 'page-attributes', 'editor', 'thumbnail' ),        
	        'public' => true,
	        'show_ui' => true,
	        'show_in_menu' => true,
	        'show_in_nav_menus' => false,
	        'publicly_queryable' => true,
	        'exclude_from_search' => false,
	        'has_archive' => false,
	        'query_var' => true,
	        'can_export' => true,
	        'rewrite' => $rewrite,
	        'capability_type' => 'post',
	        'register_meta_box_cb' => array ( $this, 'wpgip_add_instagram_posts_metabox' )
	    );
	
	    register_post_type( 'wpgip_instagrams', $args );
    
	}
	
	/*
	 * Add Meta Box For This Post Type
	 */
	
	public function wpgip_add_instagram_posts_metabox() {
		
		add_meta_box('wpgip_instagram_post_information', 'Instagram Post Information', array ( $this, 'wpgip_instagram_posts_meta' ), 'wpgip_instagrams', 'normal', 'default');
		
	}

	/*
	 * Add Fields For Meta Box
	 */
	
	public function wpgip_instagram_posts_meta() {
		global $post;
		
		// Noncename needed to verify where the data originated
		echo '<input type="hidden" name="instagrampostmeta_noncename" id="instagrampostmeta_noncename" value="' . wp_create_nonce( plugin_basename(__FILE__) ) . '" />';
		
		$wpgip_ip_image_id = get_post_meta($post->ID, 'wpgip_ip_image_id', true);		
		$wpgip_ip_lat = get_post_meta($post->ID, 'wpgip_ip_lat', true);
		$wpgip_ip_long = get_post_meta($post->ID, 'wpgip_ip_long', true);
		$wpgip_ip_url = get_post_meta($post->ID, 'wpgip_ip_url', true);
		$wpgip_ip_username = get_post_meta($post->ID, 'wpgip_ip_username', true);
		$wpgip_ip_username_id = get_post_meta($post->ID, 'wpgip_ip_username_id', true);
		$wpgip_ip_datetime = get_post_meta($post->ID, 'wpgip_ip_datetime', true);
		
		// adding for "new" Instagram video
		$wpgip_ip_video_expanded_url = get_post_meta($post->ID, 'wpgip_ip_video_expanded_url', true);
		$wpgip_ip_video_width = get_post_meta($post->ID, 'wpgip_ip_video_width', true);
		$wpgip_ip_video_height = get_post_meta($post->ID, 'wpgip_ip_video_height', true);
		$wpgip_ip_video_type = get_post_meta($post->ID, 'wpgip_ip_video_type', true);
		$wpgip_ip_video_image = get_post_meta($post->ID, 'wpgip_ip_video_image', true);
		$wpgip_ip_video_info = get_post_meta($post->ID, 'wpgip_ip_video_info', true);

				
		// Echo out the fields
		echo '<label>Image ID:</label> <input type="text" name="wpgip_ip_image_id" value="' . $wpgip_ip_image_id  . '" class="widefat" />';
		echo '<label>Lat:</label> <input type="text" name="wpgip_ip_lat" value="' . $wpgip_ip_lat  . '" class="widefat" />';
		echo '<label>Long:</label> <input type="text" name="wpgip_ip_long" value="' . $wpgip_ip_long  . '" class="widefat" />';
		echo '<label>URL:</label> <input type="text" name="wpgip_ip_url" value="' . $wpgip_ip_url  . '" class="widefat" />';
		echo '<label>Username:</label> <input type="text" name="wpgip_ip_username" value="' . $wpgip_ip_username  . '" class="widefat" />';		
		echo '<label>Username ID:</label> <input type="text" name="wpgip_ip_username_id" value="' . $wpgip_ip_username_id  . '" class="widefat" />';
		echo '<label>Datetime:</label> <input type="text" name="wpgip_ip_datetime" value="' . $wpgip_ip_datetime  . '" class="widefat" />';
		
		echo '<br/>';
		echo '<label>Video URL:</label> <input type="text" name="wpgip_ip_video_expanded_url" value="' . $wpgip_ip_video_expanded_url  . '" class="widefat" />';
		echo '<label>Video Width (pixels):</label> <input type="text" name="wpgip_ip_video_width" value="' . $wpgip_ip_video_width  . '" class="widefat" />';
		echo '<label>Video Height (pixels):</label> <input type="text" name="wpgip_ip_video_height" value="' . $wpgip_ip_video_height  . '" class="widefat" />';

		echo '<label>Video Type:</label> <input type="text" name="wpgip_ip_video_type" value="' . $wpgip_ip_video_type  . '" class="widefat" />';
		echo '<label>Video Image (Thumbnail):</label> <input type="text" name="wpgip_ip_video_image" value="' . $wpgip_ip_video_image  . '" class="widefat" />';
		echo '<label>Video Info:</label> <input type="text" name="wpgip_ip_video_info" value="' . $wpgip_ip_video_info  . '" class="widefat" />';



	}


	
	/*
	 * Saving Metabox Data
	 */
	
	public function wpgip_save_events_meta($post_id, $post) {
	
		if ( isset( $_POST['tweetmeta_noncename'] ) ) {
		
			// verify this came from the our screen and with proper authorization,
			// because save_post can be triggered at other times
					
			if ( !wp_verify_nonce( $_POST['tweetmeta_noncename'], plugin_basename(__FILE__) )) {
				return $post->ID;
			}
		
			// Is the user allowed to edit the post or page?
			
			if ( !current_user_can( 'edit_post', $post->ID ))
				return $post->ID;
		
			// OK, we're authenticated: we need to find and save the data
			// We'll put it into an array to make it easier to loop though.
			
			$tweets_meta['wpgip_ip_image_id'] = $_POST['wpgip_ip_image_id'];
			$tweets_meta['wpgip_ip_lat'] = $_POST['wpgip_ip_lat'];
			$tweets_meta['wpgip_ip_long'] = $_POST['wpgip_ip_long'];
			$tweets_meta['wpgip_ip_url'] = $_POST['wpgip_ip_url'];
			$tweets_meta['wpgip_ip_username'] = $_POST['wpgip_ip_username'];
			$tweets_meta['wpgip_ip_username_id'] = $_POST['wpgip_ip_username_id'];
			$tweets_meta['wpgip_ip_datetime'] = $_POST['wpgip_ip_datetime'];
			$tweets_meta['wpgip_ip_video_expanded_url'] = $_POST['wpgtp_tw_video_expanded_url'];
			$tweets_meta['wpgip_ip_video_type'] = $_POST['wpgip_ip_video_type'];
			$tweets_meta['wpgip_ip_video_image'] = $_POST['wpgip_ip_video_image'];
			$tweets_meta['wpgip_ip_video_info'] = $_POST['wpgip_ip_video_info'];
			
			// Add values of $events_meta as custom fields
			
			foreach ($tweets_meta as $key => $value) { // Cycle through the $tweets_meta array
			
				if( $post->post_type == 'revision' ) return; // Don't store custom data twice
				
				$value = implode(',', (array)$value); // If $value is an array, make it a CSV (unlikely)
				
				if(get_post_meta($post->ID, $key, FALSE)) { // If the custom field already has a value
					update_post_meta($post->ID, $key, $value);
				} else { // If the custom field doesn't have a value
					add_post_meta($post->ID, $key, $value);
				}
				
				if(!$value) delete_post_meta($post->ID, $key); // Delete if blank
			}
		
		}
	
	}
	
	
	/*
	 * Register Tax Term
	 *
	 * Note: media_categories places taxonomy for attachments - it was the idea i was running with
	 * but no longer being used primarily anymore
	 *
	 */
	 
	public function wpgip_register_tax() {
	

		// Add new taxonomy, make it hierarchical (like categories)
		$labels = array(
		    'name' => _x( 'Instagram Types', 'taxonomy general name' ),
		    'singular_name' => _x( 'Instagram Types', 'taxonomy singular name' ),
		    'search_items' =>  __( 'Search Instagram Types' ),
		    'all_items' => __( 'All Instagram Types' ),
		    'parent_item' => __( 'Parent Instagram Type' ),
		    'parent_item_colon' => __( 'Parent Instagram Type:' ),
		    'edit_item' => __( 'Edit Instagram Type' ), 
		    'update_item' => __( 'Update Instagram Type' ),
		    'add_new_item' => __( 'Add New Instagram Type' ),
		    'new_item_name' => __( 'New Instagram Type Name' ),
		    'menu_name' => __( 'Instagram Types' ),
		); 	
		
		register_taxonomy('wpgip_instagram_types',array('wpgip_instagrams'), array(
		    'hierarchical' => true,
		    'labels' => $labels,
		    'show_ui' => true,
		    'query_var' => true
		));
		
		// add this tax if it doesn't exist
		
		if ( !taxonomy_exists('wpgip_media_categories') ) {
	
			register_taxonomy('wpgip_media_categories', 'attachment', array(
				// Hierarchical taxonomy (like categories)
				'hierarchical' => true,
				// This array of options controls the labels displayed in the WordPress Admin UI
				'labels' => array(
					'name' => _x( 'Media Category', 'taxonomy general name' ),
					'singular_name' => _x( 'Media Category', 'taxonomy singular name' ),
					'search_items' =>  __( 'Search Media Categories' ),
					'all_items' => __( 'All Media Categories' ),
					'parent_item' => __( 'Parent Media Category' ),
					'parent_item_colon' => __( 'Parent Media Category:' ),
					'edit_item' => __( 'Edit Media Category' ),
					'update_item' => __( 'Update Media Category' ),
					'add_new_item' => __( 'Add New Media Category' ),
					'new_item_name' => __( 'New Media Category Name' ),
					'menu_name' => __( 'Media Categories' ),
				),
				// Control the slugs used for this taxonomy
				'rewrite' => array(
					'slug' => 'media-categories', // This controls the base slug that will display before each term
					'with_front' => false, // Don't display the category base before "/locations/"
					'hierarchical' => true // This will allow URL's like "/locations/boston/cambridge/"
				),
			));
	
		}

		// add the media category option, if it exits	

		if ( taxonomy_exists('wpgip_media_categories') ) {
	
			$term = term_exists('Instagram', 'wpgip_media_categories');
			
			if ($term !== 0 && $term !== null) {
			
				// this exists, do nothing
				
			} else {

				$parent_term_id = 0; // there's no parent (yet)
				
				wp_insert_term(
				  'Instagram', // the term 
				  'wpgip_media_categories', // the taxonomy
				  array(
				    'description'=> 'Posts from the Instagram social network.',
				    'slug' => 'instagram',
				    'parent'=> $parent_term_id
				  )
				);
				
			} // if term isn't null
			
		} // if tax exists
		
	} // wpgip_register_tax
	
	
	/*
	 * This handles what happens when the 'clear all settings' button is pushed on the settings page.
	 * This attempts to remove and/or reset values.
	 */
	
	public function wpgip_clear_settings() {

		// check nonce
        if ( ! wp_verify_nonce( $_POST[ 'wp-grab-instagram-pics' . '_nonce' ], 'wpgip_clear_settings' ) )
            die( 'Invalid nonce.' . var_export( $_POST, true ) );
            
        // proceed with removing options and data

        	// remove the instagram max_id
        	
        	delete_option( 'wpgip_instagram_gallery_max_id' );
        	
        	// clear the "last checked date"
        	
        	delete_option( 'wpgip_instagram_last_grab' );        	
        
       // ok, let's get back to where we were, most likely the settings page
       
		$msg = "settings-reset";       
       
		$url = add_query_arg( 'msg', $msg, urldecode( $_POST['_wp_http_referer'] ) );
		
		wp_safe_redirect( $url );
		
		exit;


    } // end wpgip_clear_settings
    

	 
	 
	/*
	 * wpgip_grab_instagram_posts() wraps around wpgip_do_grab_instagram_posts() and handles security when
	 * the grabbing is called manually via the WordPress backend on the grab page
	 */
	
	public function wpgip_grab_instagram_posts() {

		// check nonce
        if ( ! wp_verify_nonce( $_POST[ 'wp-grab-instagram-pics' . '_nonce' ], 'grab_instagrams' ) )
            die( 'Invalid nonce.' . var_export( $_POST, true ) );
                        
        // since nonce checks out, call the main function
        
       $msg = $this->wpgip_do_grab_instagram_posts();
       
	$url = add_query_arg( 'msg', $msg, urldecode( $_POST['_wp_http_referer'] ) );

       wp_safe_redirect( $url );
       exit;


        
    }
    
    
	/*
	 * wpgip_do_grab_instagram_posts() is the bulk of the plugin. It interacts with the instagram API to parse through posts via the
	 * hashtag, find images, and save those images (along with metadata) as a WordPress media item
	 *
	 * NOTICE: This is a work in progress with instagram's API. Trying to mediate how to do this better.
	 */  
    
	public function wpgip_do_grab_instagram_posts() {
           
        // proceeding forward - woot!
        
        // let's grab the hashtag, henceforth known as the "tag"
	    $tag = esc_attr( get_option( 'wpgip-hashtag' ) );
	    
	    // let's get the client id as well, assigned by instagram developer center
	    $client_id = get_option( 'wpgip-instagram-client-id' );
	    
	    // setup a few variables and arrays
	    $msg = '';
	    $new_instagrams = array();
	    $image_counter = 0;
	    $existing_instagram_post_id_array = array();
	    
	    if ( $tag && $client_id ) { // need a tag to search, and a client id to proceed
	    
		    // let's go grab some instagram posts!
   		    $response = wp_remote_get( 'https://api.instagram.com/v1/tags/' . urlencode($tag) . '/media/recent?client_id=' . $client_id, array( 'sslverify' => false ) );
		    	           
			if ( 200 === wp_remote_retrieve_response_code( $response ) ) {
			
				// let's determine when the last time we looked for instagram posts
				$last_check_date = get_option( 'wpgip_instagram_last_grab', 0 );
									
			    // Decode the response and build an array
			    $body = json_decode( wp_remote_retrieve_body( $response ) );
			    


			    foreach ( $body->data as $item ) { // go through the returned results
			    
					if ( $item->created_time > $last_check_date ) { // if we don't already have this tweet, and we don't want retweets
								    			
					    $instagram_id = $item->id;
			
				        $title = (isset($item->caption))?mb_substr($item->caption->text,0,70,"utf8"):null;
				        
				        $link = $item->link;
				        $created_time = $item->created_time;
				        $standard_src = $item->images->standard_resolution->url; 
				        $thumbnail_src = $item->images->thumbnail->url; 
				
				        // get caption / username
				        $caption_username = (isset($item->caption->from->username))?$item->caption->from->username:null; 
				        $caption_username_id = (isset($item->caption->from->id))?$item->caption->from->id:null; 
				
				        // Location coords seemed empty in the results but you would need to check them as mostly be undefined
				        $lat = (isset($item->data->location->latitude))?$item->data->location->latitude:null; 
				        $lon = (isset($item->data->location->longtitude))?$item->data->location->longtitude:null; 
				        
				        // is there a video w/ this instagram (new as of July 2013) - if so, throw that in
				        if ( !empty($item->videos) ) {
					        $video_url = $item->videos->standard_resolution->url;
					        $video_width = $item->videos->standard_resolution->width; // might not need this
					        $video_height = $item->videos->standard_resolution->height; // might not need this
				        } else {
					        $video_url = $video_width = $video_height = false;
				        }
				
				        $new_instagrams[] = array(
					        "instagram_id" => $instagram_id,
					        "title" => htmlspecialchars($title),
					        "link" => htmlspecialchars($link),
					        "created_time" => htmlspecialchars($created_time),
					        "caption_username" => htmlspecialchars($caption_username),
					        "caption_username_id" => htmlspecialchars($caption_username_id),        
					        "standard_src" => htmlspecialchars($standard_src),
					        "thumbnail_src" => htmlspecialchars($thumbnail_src),        
					        "lat" => htmlspecialchars($lat),
					        "lon" => htmlspecialchars($lon),
					        "video_url" => $video_url,
					        "video_width" => $video_width,
					        "video_height" => $video_height					        
				        );
			        
			        }
			    }
			    
				// let's update the "last tried" field so someone knows when we last attempted to look
				
				update_option( 'wpgip_instagram_last_grab', time() );
			       
			
//				print_r ($new_instagrams); exit;
			
				//
				// Ok, now loop through the $new_instagrams array and save them as WP posts
				//
					
				if ( $new_instagrams ) {

					foreach ($new_instagrams as $new_instagram) {
					
						$featured_image_done_yet = false;
					
						// Let's define the post title
						
						$post_title = wp_strip_all_tags($new_instagram['title']);
					
						// Create post object
						$tweet_post = array(
						  'post_title'    	=> $post_title,
						  'post_content'  	=> wp_strip_all_tags ( $new_instagram['title'] ),
						  'post_date'		=> date('Y-m-d H:i:s', $new_instagram['created_time'] ),
						  'post_type'	  	=> 'wpgip_instagrams',
						  'post_status'   	=> 'publish',
						  'ping_status'	  	=> 'closed'
  						);
						
						// Insert the post into the database
						$post_id = wp_insert_post( $tweet_post );
						
						if ( $post_id ) {
						
							// grab image and attach it to the post
							
							$url = $new_instagram['standard_src'];
							$tmp = download_url( $url );
							$file_array = array(
							    'name' => basename( $url ),
							    'tmp_name' => $tmp
							);
																	
							// Check for download errors
							if ( is_wp_error( $tmp ) ) {
							    @unlink( $file_array[ 'tmp_name' ] );
								print_r ("error: " . $tmp); die();
							}
							
							$attachment_id = $this->wpgip_media_handle_sideload( $file_array, $post_id ); // the $post_id makes this attachment associated with the tweet post
							
							// Check for handle sideload errors.
							
							if ( is_wp_error( $attachment_id ) ) {
							
							    @unlink( $file_array['tmp_name'] );
								print_r ("error: " . $attachment_id); die();
							
							} else {
								
								// no errors? Woot.
								
								if ( !$featured_image_done_yet ) { // make the image the featured image, if there isn't one already
									
									set_post_thumbnail( $post_id, $attachment_id );
									$featured_image_done_yet = true;
									
								}
								
							}
							
							// add metadata

							if ( $new_instagram['instagram_id'] ) { add_post_meta($post_id, 'wpgip_ip_image_id', $new_instagram['instagram_id'], true); }			
							if ( $new_instagram['lat'] ) { add_post_meta($post_id, 'wpgip_ip_lat', $new_instagram['lat'], true); }			
							if ( $new_instagram['lon'] ) { add_post_meta($post_id, 'wpgip_ip_long', $new_instagram['long'], true); }
							if ( $new_instagram['link'] ) { add_post_meta($post_id, 'wpgip_ip_url', $new_instagram['link'], true); }
							if ( $new_instagram['caption_username'] ) { add_post_meta($post_id, 'wpgip_ip_username', $new_instagram['caption_username'], true); }
							if ( $new_instagram['caption_username_id'] ) { add_post_meta($post_id, 'wpgip_ip_username_id', $new_instagram['caption_username_id'], true); }
							if ( $new_instagram['created_time'] ) { add_post_meta($post_id, 'wpgip_ip_datetime', $new_instagram['created_time'], true); }
							
							if ( $new_instagram['video_url'] ) { add_post_meta($post_id, 'wpgip_ip_video_expanded_url', $new_instagram['video_url'], true); }
							if ( $new_instagram['video_width'] ) { add_post_meta($post_id, 'wpgip_ip_video_width', $new_instagram['video_width'], true); }
							if ( $new_instagram['video_height'] ) { add_post_meta($post_id, 'wpgip_ip_video_height', $new_instagram['video_height'], true); }
							
						}
						
						// ok, add one to the counter
						$image_counter++;
					}
					
				} // if $new_instagrams
				
			
			    $msg = "$image_counter images pulled from Instagram.";
			    
			} 
		} else { // if we don't have a tag and client id
		
			if ( !$tag ) {
				$msg = "missing-tag";
			} else if ( !$client_id ) {
				$msg = "missing-client-id";
			}
					
		}

		return $msg;
	} // end wpgip_grab_instagram_posts()
	
		
	/**
	 * Render Messages.
	 *
	 * @since 0.1
	 */
    public function render_msg() {
		$text = false;
    
		if ( ! isset( $_GET['msg'] ) )
			return;

		if ( 'settings-reset' === $_GET['msg'] )
			$text = __( 'Settings have been reset.' );

		if ( 'missing-tag' === $_GET['msg'] )
			$text = _( 'A tag/keyword to search for is required.' );

		if ( 'missing-client-id' === $_GET['msg'] )
			$text = __( 'You need a "client id" provided by Instagram.' );;
                        
		if ( $text )        
			echo '<div class="updated"><p>' . $text . '</p></div>';
    }
	
	
	/*
	 * I had to create my own media handle sideload function because i got a 'white screen' with the official one
	 * with no visible errors that i could see, even in the logs
	 */
	
	public function wpgip_media_handle_sideload($file_array, $post_id, $desc = null, $post_data = array()) {
	        $overrides = array('test_form'=>false);
	
	        $file = wp_handle_sideload($file_array, $overrides);
	        if ( isset($file['error']) )
	                return new WP_Error( 'upload_error', $file['error'] );
	
	        $url = $file['url'];
	        $type = $file['type'];
	        $file = $file['file'];
	        $title = preg_replace('/\.[^.]+$/', '', basename($file));
	        $content = '';
	        
	        /* 
	
	        // use image exif/iptc data for title and caption defaults if possible
	        if ( $image_meta = @wp_read_image_metadata($file) ) {
	                if ( trim( $image_meta['title'] ) && ! is_numeric( sanitize_title( $image_meta['title'] ) ) )
	                        $title = $image_meta['title'];
	                if ( trim( $image_meta['caption'] ) )
	                        $content = $image_meta['caption'];
	        }
	        
	        */
	
	        if ( isset( $desc ) )
	                $title = $desc;
	
	        // Construct the attachment array
	        $attachment = array_merge( array(
	                'post_mime_type' => $type,
	                'guid' => $url,
	                'post_parent' => $post_id,
	                'post_title' => $title,
	                'post_content' => $content,
	        ), $post_data );
	
	        // This should never be set as it would then overwrite an existing attachment.
	        if ( isset( $attachment['ID'] ) )
	                unset( $attachment['ID'] );
	
	        // Save the attachment metadata
	        $id = wp_insert_attachment($attachment, $file, $post_id);
	        if ( !is_wp_error($id) )
	                wp_update_attachment_metadata( $id, wp_generate_attachment_metadata( $id, $file ) );
	
	        return $id;
	}	
} // end class


WPGrabInstagramPics::get_instance();