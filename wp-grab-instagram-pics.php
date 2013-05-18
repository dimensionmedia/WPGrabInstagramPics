<?php
/*
Plugin Name: Grab Instagram Pics
Plugin URI: http://www.davidbisset.com/wp-grab-instagram-pics
Description: This plugin will search through recent Instagram posts (containing a certain hashtag), and import those photos along with metadata into WP's media gallery.
Version: 0.1
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
 * TODO: 
 *
 * Rename this class to a proper name for your plugin. Give a proper description of
 * the plugin, it's purpose, and any dependencies it has.
 *
 * Use PHPDoc directives if you wish to be able to document the code using a documentation
 * generator.
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
    	define("WP_GRAB_INSTAGRAM_PICS_PERMISSIONS", "manage_options");

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
	     * Here's where we define the custom functionality for this plugin.
	     */     
	     
		add_action( "admin_post_grab_tweets", array ( $this, 'wpgip_grab_instagram_posts' ) );	
		add_action( "admin_post_wpgip_clear_settings", array ( $this, 'wpgip_clear_settings' ) );	
        add_action( "admin_notices", array ( $this, 'render_msg' ) );

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
		wp_enqueue_style( 'wpgip-name-wpgip-styles', plugins_url( 'css/display.css', __FILE__ ) );
	} // end register_wpgip_styles

	/**
	 * Registers and enqueues wpgip-specific scripts.
	 */
	public function register_wpgip_scripts() {
		wp_enqueue_script( 'wpgip-name-wpgip-script', plugins_url( 'js/display.js', __FILE__ ), array( 'jquery' ) );
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
	 */

	public function wpgip_settings_page() {
	
		$redirect = urlencode( remove_query_arg( 'msg', $_SERVER['REQUEST_URI'] ) );
        $redirect = urlencode( $_SERVER['REQUEST_URI'] );
        
        $action_name = "wpgip_clear_settings";
        $nonce_name = "wp-grab-instagram-pics";
	
	    echo '
	    <div class="wrap">
	        <div id="icon-options-general" class="icon32"><br /></div>
	        <h2>'.__("Grab Instagram Pics Settings").'</h2>
	        <br />'; ?>
	        
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
	        <?php echo '</form>
	    </div>';
	}
	
	/**
	 * Renders the grab page for this plugin.
	 */
	 
	public function wpgip_grab_page() {
	
		$redirect = urlencode( remove_query_arg( 'msg', $_SERVER['REQUEST_URI'] ) );
        $redirect = urlencode( $_SERVER['REQUEST_URI'] );
        
        $action_name = "grab_tweets";
        $nonce_name = "wp-grab-instagram-pics";
	
	    echo '
	    <div class="wrap">
	        <div id="icon-edit-pages" class="icon32"><br /></div>
	        <h2>'.__("Grab InstaGram Posts").'</h2>
	        <br />'; ?>
	        <form action="<?php echo admin_url( 'admin-post.php' ); ?>" method="POST">
            <input type="hidden" name="action" value="<?php echo $action_name; ?>">
            <?php wp_nonce_field( $action_name, $nonce_name . '_nonce', FALSE ); ?>
            <input type="hidden" name="_wp_http_referer" value="<?php echo $redirect; ?>">

            <?php submit_button( 'Grab Posts' ); ?>
        </form>
        <?php echo '
	    </div>';
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
	    //add_settings_field( 'section-options-instagram-consumer-secret', 'Twitter Consumer Secret', array( $this, 'options_instagram_consumer_secret_field_callback' ), 'wp-grab-instagram-pics-options', 'section-options' );
	    //add_settings_field( 'section-options-setOAuthToken', 'setOAuthToken', array( $this, 'options_setOAuthToken_field_callback' ), 'wp-grab-instagram-pics-options', 'section-options' );
	    //add_settings_field( 'section-options-setOAuthTokenSecret', 'setOAuthTokenSecret', array( $this, 'options_setOAuthTokenSecret_field_callback' ), 'wp-grab-instagram-pics-options', 'section-options' );
	    
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
       
		$msg = "settingsreset";       
       
		$url = add_query_arg( 'msg', $msg, urldecode( $_POST['_wp_http_referer'] ) );
		
		wp_safe_redirect( $url );
		
		exit;


    } // end wpgip_clear_settings
    
	/*
	 * wpgip_grab_instagram_posts() is the bulk of the plugin. It interacts with the instagram API to parse through posts via the
	 * hashtag, find images, and save those images (along with metadata) as a WordPress media item
	 *
	 * NOTICE: This is a work in progress with instagram's API. Trying to mediate how to do this better.
	 */
	
	public function wpgip_grab_instagram_posts() {

		// check nonce
        if ( ! wp_verify_nonce( $_POST[ 'wp-grab-instagram-pics' . '_nonce' ], 'grab_tweets' ) )
            die( 'Invalid nonce.' . var_export( $_POST, true ) );
            
        // proceeding forward - woot!
        
        // let's grab the hashtag, henceforth known as the "tag"
	    $tag = esc_attr( get_option( 'wpgip-hashtag' ) );
	    // let's get the client id as well, assigned by instagram developer center
	    $client_id = get_option( 'wpgip-instagram-client-id' );
	    $msg = '';
	    $image_counter = 0;
	    
	    if ($tag && $client_id) { // need a tag to search, and a client id to proceed
	    
		    // let's go grab some instagram posts!
   		    $response = wp_remote_get("https://api.instagram.com/v1/tags/".$tag."/media/recent?client_id=".$client_id);
		    
			// let's update the "last tried" field so someone knows when we last attempted to look
			update_option( 'wpgip_instagram_last_grab', time() );
	           
			if( !is_wp_error( $response ) ) {
					
			    // Decode the response and build an array
			    
			    foreach(json_decode($response['body'])->data as $item){
			    			
				    $instagram_id = $item->id;
			
			        $title = (isset($item->caption))?mb_substr($item->caption->text,0,70,"utf8"):null;
			        
			        $link = $item->link;
			        $created_time = $item->created_time;
			        $standard_src = $item->images->standard_resolution->url; //Caches standard res img path to variable $src
			        $thumbnail_src = $item->images->thumbnail->url; //Caches standard res img path to variable $src
			
			        //get caption / username
			        $caption_username = (isset($item->caption->from->username))?$item->caption->from->username:null; 
			        $caption_username_id = (isset($item->caption->from->id))?$item->caption->from->id:null; 
			
			        //Location coords seemed empty in the results but you would need to check them as mostly be undefined
			        $lat = (isset($item->data->location->latitude))?$item->data->location->latitude:null; // Caches latitude as $lat
			        $lon = (isset($item->data->location->longtitude))?$item->data->location->longtitude:null; // Caches longitude as $lon
			
			        $images[] = array(
				        "instagram_id" => $instagram_id,
				        "title" => htmlspecialchars($title),
				        "link" => htmlspecialchars($link),
				        "created_time" => htmlspecialchars($created_time),
				        "caption_username" => htmlspecialchars($caption_username),
				        "caption_username_id" => htmlspecialchars($caption_username_id),        
				        "standard_src" => htmlspecialchars($standard_src),
				        "thumbnail_src" => htmlspecialchars($thumbnail_src),        
				        "lat" => htmlspecialchars($lat),
				        "lon" => htmlspecialchars($lon) // Consolidates variables to an array
			        );
			    }
			       
			    // First we grab any current images to ensure we don't add duplicates
			    
			    $image_id_array = array();
			    
				$attachments = get_posts( array(
					'post_type' => 'attachment',
					'post_mime_type' => 'image',
					'posts_per_page' => -1,
					'post_parent' => 0
				) );
			
				if ( $attachments ) {
					foreach ( $attachments as $attachment ) {
						$post_meta = get_post_meta ( $attachment->ID );
						$current_images[] = array ( 'post_data' => $attachment, 'post_meta' => $post_meta );
						$image_id_array[] = $post_meta['cz_instagram_image_id'][0]; // makes finding duplicate items easier, but should be a better way!
					}
					
				}
			
				// Ok, now loop through the images grabbed and save into WP anything that we don't have
					
				foreach ($images as $image) {
						
					if ( !in_array($image['instagram_id'], $image_id_array) ) { 
						
						// image doesn't exist - let's upload and add to WP media lib
						
						$url = $image['standard_src'];
						$tmp = download_url( $url );
						$file_array = array(
						    'name' => basename( $url ),
						    'tmp_name' => $tmp
						);
									
						// Check for download errors
						if ( is_wp_error( $tmp ) ) {
						    @unlink( $file_array[ 'tmp_name' ] );
							print_r ($tmp); echo "test";
						}
												
						$id = $this->wpgip_media_handle_sideload( $file_array, 0 );
						
						// Check for handle sideload errors.
						if ( is_wp_error( $id ) ) {
							print_r ($id); echo "test";
						    @unlink( $file_array['tmp_name'] );
						    return $id;
						}
						
						$attachment_url = wp_get_attachment_url( $id );
						
						// add image title (which was the instagram's caption)
						
						$post_content = '<a href="'.$image['link'].'">Taken on ' . date('F jS, Y - g:ia', $image['created_time']);
						
						if ( $image['caption_username'] ) {
							$post_content .= ' by '.$image['caption_username'].' ';	
						} 
						
						$post_content .= '</a>.';
						
						$data = array(
							'ID' => $id,
						    'post_excerpt' => $image['title'],
						    'post_content' => $post_content,
						    'post_title' => $image['title']
						);
						
						wp_update_post( $data );
			
						// add image metadata
			
						add_post_meta($id, 'cz_instagram_image_type', 'cz_gallery_image', true);
						add_post_meta($id, 'cz_instagram_image_id', $image['instagram_id'], true);
						if ( $image['lat'] ) { add_post_meta($id, 'cz_instagram_image_lat', $image['lat'], true); }			
						if ( $image['lat'] ) { add_post_meta($id, 'cz_instagram_image_lat', $image['lat'], true); }
						if ( $image['link'] ) { add_post_meta($id, 'cz_instagram_image_link', $image['link'], true); }
						if ( $image['caption_username'] ) { add_post_meta($id, 'cz_instagram_image_caption_username', $image['caption_username'], true); }
						if ( $image['caption_username_id'] ) { add_post_meta($id, 'cz_instagram_image_caption_username_id', $image['caption_username_id'], true); }
						
						// ok, add one to the counter
						
						$image_counter++;
									
					}
					
				}
				
			
			    $msg = "$image_counter images pulled from Instagram.";
			    
			}

		} else { // if we don't have a tag and client id
		
			if ( !$tag ) {
				$msg = "missing-tag";
			} else if ( !$client_id ) {
				$msg = "missing-client-id";
			}
					
		}

		$url = add_query_arg( 'msg', $msg, urldecode( $_POST['_wp_http_referer'] ) );

        wp_safe_redirect( $url );
        exit;


	} // end wpgip_grab_instagram_posts()
	
		
	/*
	 * Simple render message script
	 */
    public function render_msg()
    {
    
        if ( ! isset ( $_GET['msg'] ) && ! isset ( $_GET['settings-updated'] ) )
            return;

        $text = FALSE;

        if ( 'settingsreset' === $_GET['msg'] )
            $this->msg_text = 'Settings Have Been Reset';

        if ( 'missing-tag' === $_GET['msg'] )
            $this->msg_text = 'A tag/keyword to search for is required.';

        if ( 'missing-client-id' === $_GET['msg'] )
            $this->msg_text = 'You need a "client id" provided by Instagram.';
            
            

        if ( 'true' === $_GET['settings-updated'] )
            $this->msg_text = 'Options Have Been Updated';
                        
        if ( $this->msg_text ) {
        
	        echo '<div class="updated"><p>' . $this->msg_text . '</p></div>';
            
        }
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