<?php

/**
 * Settings
 * Manages admin configurable options and settings for the plugin
 */

// BEGIN_NAMESPACE_EDIT_MARKER
// <-- namespace statement will get added here ---> 
// END_NAMESPACE_EDIT_MARKER


// Class to handle the settings
class Settings {
	
	// Static constants
	static public $option_page_title ="";
	static public $plugin_file_name = "";
	static public $option_group_page = "";
	static public $option_name = "";
	
	// Initialise in constructor 
	public $settings_sections =  null;
	public $settings_fields = null;
	
	// Constructor
	function __construct(
					$option_page_title,
					$plugin_file_name,
					$option_group_page,
					$option_name
					) {
		

		// Check params
		assert( ! empty( $option_page_title) and 
				! empty( $plugin_file_name ) and 
				! empty( $option_group_page ) and 
				! empty( $option_name ) ,
				'none of the params can be empty.');

		// Assign to class 
		self::$option_page_title = $option_page_title;
		self::$plugin_file_name = $plugin_file_name;
		self::$option_group_page = $option_group_page;
		self::$option_name = $option_name;

		
		// Register init 
		add_action( 'admin_init', array($this,'fn_register_settings' ));
		
		// Register for settings sub menu
		add_action( 'admin_menu', array($this,'fn_init_settings_submenu' ));

		// Register to add settings link in the plugin page 
		add_filter( 'plugin_action_links', array($this,'fn_init_plugin_action_links'), 10, 2 );
	
		// Scripts and CSS
		add_action( 'admin_enqueue_scripts', array($this,'fn_enqueue_scripts' ));

	}
	
	
	// Enqueue scripts and styles
	function fn_enqueue_scripts($hook) {
	 
		 if("settings_page_" . self::$option_group_page != $hook){
		 	return;
		 }
		 
	    // Add the color picker css file       
	    wp_enqueue_style( 'wp-color-picker' ); 
	     
	    // Include our custom jQuery file with WordPress Color Picker dependency
	    wp_enqueue_script( 'custom-script-handle', plugins_url( 'custom-script.js', __FILE__ ), array( 'wp-color-picker' ), false, true ); 

    
	}
		
		
	// Init settings structure with wp
	function fn_register_settings() {
		
		// Trace
		dbg_trace();

		// register a new setting
		register_setting( 
				self::$option_group_page, 
				self::$option_name,
				array($this, 'fn_validate_input')
				);
	  
        	
        // Loop through adding sections
		foreach($this->settings_sections as $section_id => $section_details) {

				// register appearance section 
				add_settings_section(
				 $section_id,								// string $id
				 $section_details['title'],					// string $title
				 array($this, 'fn_section_desc_render'),	// callable $callback
				 self::$option_group_page					// string $page
				);

    		}
	
			
		// Loop through adding fields
		$defaults = array();
		foreach($this->settings_fields as $field_id => $field_details) {
			
			// The section should exist	
			$section = $field_details['section'];
			assert(isset ( $this->settings_sections[ $section ] ), "bad section '$section' in field definitions.");

			// Add this field
			add_settings_field( 
				$field_id,								// string $id
				$field_details['title'],				// string $title
				array($this,'fn_field_render'),			// callable $callback
				self::$option_group_page,				// string $page
				$section,								// string $section = 'default' 
				['id' => $field_id] 					// array $args = array() )
			);
			
			// Look for any missing values that need defaults
			$defaults[$field_id] = get_option_array_value(self::$option_name, $field_id, $field_details['default']);

		}
		
		// Update defaults
		update_option( self::$option_name , $defaults);
		
	}
	
	// un register settings
	static function unregister_settings() {
		
		// Trace
		dbg_trace();
		
		// Delete options 
		delete_option( self::$option_name );
		
		// Un register settings
		unregister_setting( self::$option_group_page, self::$option_name );

	}
	
	
	// Init settings sub menu 
	function fn_init_settings_submenu() {
	
		// Trace
		dbg_trace();
		
		// Add the menu item
		add_options_page(
			self::$option_page_title,				// $page_title
			self::$option_page_title,				// $menu_title
			'manage_options',						// $capability
			self::$option_group_page,				// $menu_slug
			array($this,'fn_render_options_page')	// $function
		);
		

	}
	
	// Add settings link in plugin page
	function fn_init_plugin_action_links($links_array, $plugin_file_name ){
	
		// Trace
		dbg_trace();
		
		// check its this plugin
		if( false !== strpos( $plugin_file_name, self::$plugin_file_name ) ) {
			
			$url =  get_admin_url(null, 'options-general.php?page=' . self::$option_group_page );
			array_unshift( $links_array, '<a href="' . $url .'">Settings</a>' );
		}
		
		// done
		return $links_array;
	}
	
	// Section call back -  appearance  
	function fn_section_desc_render( $args ){
		
		// Trace
		dbg_trace();
		
		// Check key
		if(!isset($args['id']) or !isset($this->settings_sections[$args['id']])){
			dbg_trace("id not set or setting not found.");
			return;
		}
		
		// Get the setting
		$setting = $this->settings_sections[$args['id']];
		
		// out put the description 
		echo ($setting['desc_html']);
	
	}
	
	// Field call back - card_height
	function fn_field_render( $args ){
	
		// Trace
		dbg_trace();
		
		// Check field 
	    if( !isset($args['id']) or !isset( $this->settings_fields[$args['id']] ) ) {
	    	$msg = "field is unknown";
	        dbg_trace($msg);
	        echo("<p>" . $msg . "</p>");
	        return;
	    }
	     
		// Get the field details
		$id = $args['id'];
		$field_details = $this->settings_fields[$id];
	        
		// Get the current value
		$value = sanitize_text_field(get_option_array_value(self::$option_name, $id, $field_details['default'] ));
		$units = get_value($field_details, 'units', ' ');
		
		// Format the field html
		$field = "";

		// switch on type
        switch($field_details['type']){
        	
			
			case 'colour':
	        
				$field = "<input class='tc-colour-field' id='" . $id . "' type='text' name='" . self::$option_name . "[" . $id . "]' value='" . $value . "'> ". $units . " </input>";
				break;
		
			case 'integer':
			case 'text':
			default:
       
	            // integer, text are the same, assume anything else is the same
				$field = "<input id='" . $id . "' type='text' name='" . self::$option_name . "[" . $id . "]' value='" . $value . "'> ". $units . " </input>";
				break;
	        }
		
		// out put
		echo ($field);
	}
	
	// Input validation call back 
	function fn_validate_input( $input ) {
 
	    // Array to store validated options
	    $output = array();
	     
	    // Loop through incoming options
	    foreach( $input as $id => $value ) {
	         
	        // Check field 
	        if( ! isset( $this->settings_fields[$id] ) ) {
	        	
	        	// No field 
	        	dbg_trace("field is unknown.");
	        	
	        	// Format error 
				add_settings_error( 
					self::$option_name, 
					$id, 
					__( 'Unknown field.', 'wp-plugin-utils' ), 
					'error' 
					);
	        	
	        	// Next item
	        	continue;
	        }
	    
	        // Get the field details
	        $field_details = $this->settings_fields[$id];
	        
	        // Get current value or default
	        $current_value = get_option_array_value (self::$option_name, $id, $field_details['default']);

		    // Strip tags and handle quoted strings
		    $value = strip_tags( stripslashes( $value ));
	       
	        // switch on type
	        $field_type = $field_details['type'];
	        switch($field_type){
	        	
	        	case 'integer':
	        		
	        		// Get min and max
	        		$min = get_value($field_details,'min', - PHP_INT_MAX );
	        		$max = get_value($field_details,'max', PHP_INT_MAX );
	        		
	        		// Check valid
	    			if( !is_valid_integer_in_range($value, 1, 1000) ){
	     			
		     			// Format error 
		     			add_settings_error( 
		     				self::$option_name, 
		     				$id, 
		     				$field_details['format_msg'],
		     				'error'
		     			);
		     				
		     			// Restore current value or default
		     			$value = $current_value;
	    			}
	    			
	    			// Integer done
	        		break;
	        		
	        	case 'colour':
	        		
	        		// check valid
	    			if( !is_valid_colour($value) ){
	     			
		     			// Format error 
		     			add_settings_error( 
		     				self::$option_name, 
		     				$id, 
		     				$field_details['format_msg'],
		     				'error'
		     			);
		     				
		     			// Restore current value or default
		     			$value = $current_value;
	    			}
	        		
	        		// Colour done
	        		break;
	        
	        	case 'string':
	        		
	        		// check valid
	    			if( empty($value) ){
	     			
		     			// Format error 
		     			add_settings_error( 
		     				self::$option_name, 
		     				$id, 
		     				$field_details['format_msg'],
		     				'error'
		     			);
		     				
		     			// Restore current value or default
		     			$value = $current_value;
	    			}
	        		
	        		// String done
	        		break;	
	        		
	        	default:
	       
		            // Format error 
					add_settings_error( 
						self::$option_name, 
						$id, 
						__( 'Thats an unkown field.', 'wp-plugin-utils' ), 
						'error' );
						
					// Trace unexpected type
	            	dbg_trace("unexpected field type.");
	       
	        }
	        
           // Set the output 
           $output [ $id ] = $value;
	    
	    } 
	    
	     
	    // Done 
	    return $output;
	 
	}
	
	
	// Render the form 
	function fn_render_options_page() {
		
		// check user capabilities
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}
	 
		
		// open bock 
		echo ("<div class='wrap'>");
		
			// heading 
			echo("<h1>");
				echo esc_html( get_admin_page_title() ); 
			echo("</h1>");
			
			// open form 
			echo("<form action='options.php' method='post'>");
	
				// Security fields
				settings_fields( self::$option_group_page );
				
				// Settings them selves
				do_settings_sections( self::$option_group_page );
				
				// Submit button 
				submit_button( 'Save Settings' );
		
			// Close form
			echo("</form>");
		
		// Close block
		echo("</div>");
		
	}
	
	

}



