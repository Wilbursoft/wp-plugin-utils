<?php

/**
* Test Utilities
*/
require_once dirname( __FILE__ ) . '/../src/utils.php'; 
require_once dirname( __FILE__ ) . "/../src/class.settings.php";



// Class to handle the settings
class SubClass_Settings extends Settings {
	

	// Constructor
	function __construct() {
		
		// Call parent 
		parent::__construct(
							"CheckData Books",			// $option_page_title
							"wp-check_data-books.php",	// $plugin_file_name
							"check_data_books_settings",	// $option_group_page
							"check_data_books_options"	// $option_name
							);
		
		// Trace
		dbg_trace();
		
		// Our settings sections 
		$this->settings_sections = array (
			
			// Book appearance
			'book_appearance' => array (
				'title'		=> __( 'Book Appearance', 'check_data_books' ),
				'desc_html' => "<p>" . __('These options apply to each of the books.', 'check_data_books')  . "</p>"
				),
				
			// Layout 
			'layout' => array (
				'title'		=> __( 'Layout', 'check_data_books' ),
				'desc_html' => "<p>" . __('These options apply the layout.', 'check_data_books')  . "</p>"
				)
			);

		// Our settings fields 
		$this->settings_fields = array (
			
			// Book height
			'ic_book_min_height'	=> array (
				'title'			=> __('Book Height', 'check_data_books'),
				'units'			=> 'px.',
				'section'		=> 'book_appearance',
				'type'			=> 'integer', 
				'default'		=> 300,
				'min'			=> 10, 
				'max'			=> 1000,
				'format_msg'	=> __( 'Book Height needs to be a whole number from 10-1000 px', 'check_data_books' ), 
		     				
				),
				
			// Book border colour
			'ic_book_border_colour'	=> array (
				'title'			=> __('Book Border Colour', 'check_data_books'),
				'section'		=> 'book_appearance',
				'type'			=> 'colour', 
				'default'		=> '#8224e3',
				'format_msg'	=> __( 'Choose a valid colour for the Book Border Colour field.', 'check_data_books' ), 
		     				
				),
			
			// Max columns
			'ic_max_columns'	=> array (
				'title'			=> __('Columns', 'check_data_books'),
				'units'			=> '',
				'section'		=> 'layout',
				'type'			=> 'integer', 
				'default'		=> 3,
				'min'			=> 1, 
				'max'			=> 20,
				'format_msg'	=> __( 'Columns needs to be a whole number from 1 to 20.', 'check_data_books' ), 

				),		

			);
		
	}
	
	

}




class SubClass_SettingsTest extends WP_UnitTestCase
{

    // Check if settings exists
    static function settings_exist(){
        
        // See if we can get a settings 
        $invalid = 'invalid';
        $value = get_option_array_value(SubClass_Settings::$option_name, 'ic_book_min_height', $invalid);
        return ($invalid != $value);
    }
    
    // Helper to create the custom post type
    static function hlp_create_settings(){

        // Our settings shoud NOT be present
        WP_UnitTestCase::assertTrue(! SubClass_SettingsTest::settings_exist());
        
        // Create the is_settings 
        $ic_settings = new SubClass_Settings();
        $ic_settings->fn_register_settings();
        
        // Our settings should be present
        WP_UnitTestCase::assertTrue(SubClass_SettingsTest::settings_exist());
        
        // Return the object
       return $ic_settings;
    }
    
    // Helper to destroy the custom post type
    static function hlp_destroy_settings(){
        
        // Our settings should be present
        WP_UnitTestCase::assertTrue(SubClass_SettingsTest::settings_exist());
        
        // Unregister them
        SubClass_Settings::unregister_settings();
        
        // Our settings shoud NOT be present
        WP_UnitTestCase::assertTrue(! SubClass_SettingsTest::settings_exist());

    }
    
   
    
    // Run the tests
    public function test_methods(){
        
        
      
        
        // Switch to admin user
        $admin_user_id = $this->factory->user->create( array('role' => 'administrator') );
        wp_set_current_user( $admin_user_id );
        $this->assertTrue( current_user_can( 'manage_options' ));

        // Register settings and set default options 
        $settings = SubClass_SettingsTest::hlp_create_settings();
        
        
        // Test - fn_enqueue_scripts
        $settings->fn_enqueue_scripts("wronghook");
        $this->assertTrue(false === is_script_enqueued('custom-script.js'));
        $settings->fn_enqueue_scripts("settings_page_check_data_books_settings");
        $this->assertTrue(true === is_script_enqueued('custom-script.js'));

        
        /**
         * Test fn_init_settings_submenu
         **/
         
        // Exercise this code - nothing to test
        $settings->fn_init_settings_submenu();
   
         /**
         * Test fn_init_plugin_action_links
         **/
         
        // Check link not inserted 
        $links_array = $settings->fn_init_plugin_action_links(array(),  "otherpage.php");
        $this->assertTrue( 0 == count($links_array));
        
        // Check link inserted 
        $links_array = $settings->fn_init_plugin_action_links(array(),  "wp-check_data-books.php");
        $this->assertTrue( 1 == count($links_array));
        $this->assertTrue( is_valid_html($links_array[0]));
   
        /**
        * Test fn_section_desc_render
        **/
        ob_start();
        $settings->fn_section_desc_render(array());
       // $settings->fn_section_desc_render(array('id' => 'bad_id'));
        $output = ob_get_contents();
        $this->assertTrue( is_valid_html($output));
        ob_end_clean();
         
        /**
         * Test fn_field_render
         **/
        ob_start();
        $settings->fn_field_render(array());
        $output = ob_get_contents();
        $this->assertTrue( is_valid_html($output));
        ob_end_clean();    
        
         /**
         * Test fn_render_options_page
         **/     
        
        // Standard user should produce no form 
   	    $std_user_id = $this->factory->user->create( array('role' => 'user') );
        wp_set_current_user( $std_user_id );
        $this->assertTrue(! current_user_can( 'manage_options' ) );
        
        ob_start();
        $settings->fn_render_options_page();
        $output = ob_get_contents();
        $this->assertTrue( is_valid_html($output));
        $this->assertTrue( false === strpos($output, 'form'));
        ob_end_clean();
        
        // Switch back admin user - should produce form 
        $admin_user_id = $this->factory->user->create( array('role' => 'administrator') );
        wp_set_current_user( $admin_user_id );
        $this->assertTrue( current_user_can( 'manage_options' ) );
    
        ob_start();
        $settings->fn_render_options_page();
        $output = ob_get_contents();
        $this->assertTrue( is_valid_html($output));
        $this->assertTrue( false !== strpos($output, 'form'));
        ob_end_clean();    
        
        /**
         * Test fn_validate_input
         **/     

        
        // No errors yet
        $error_count = 0;
        $this->assertTrue($error_count === count(get_settings_errors(SubClass_Settings::$option_name)));
        
        // Not a number 
        $input = array(
            'ic_book_min_height' => 'not a number'
        );    
        $error_count++;
        $settings->fn_validate_input($input);
        $this->assertTrue($error_count === count(get_settings_errors(SubClass_Settings::$option_name)));

        // Out of range
        $input = array(
            'ic_book_min_height' => '-12'
        );
        $error_count++;
        $settings->fn_validate_input($input);
        $this->assertTrue($error_count === count(get_settings_errors(SubClass_Settings::$option_name)));
        
        // unknown field
        $input = array(
            'bad_index' => 'some field value'
        );
        $error_count++;
        $settings->fn_validate_input($input);
        $this->assertTrue($error_count === count(get_settings_errors(SubClass_Settings::$option_name)));
        
        // Bad colour
        $input = array(
            'ic_book_border_colour' => 'dead beef is bad colour'
        );
        $error_count++;
        $settings->fn_validate_input($input);
        $this->assertTrue($error_count === count(get_settings_errors(SubClass_Settings::$option_name)));
     
        // Good colour
        $input = array(
            'ic_book_border_colour' => '#8994e3'
        );
        $settings->fn_validate_input($input);
        $this->assertTrue($error_count === count(get_settings_errors(SubClass_Settings::$option_name)));
     
        // Bad type
        $settings->settings_fields['ic_bad_field'] = array (
				'title'			=> 'title',
				'section'		=> 'book_appearance',
				'type'			=> 'invalid_type', 
				'default'		=> 'default',
				'format_msg'	=> 'bad msg'

				);
				
	 
        $input = array(
            'ic_bad_field' => 'some value'
        );
        $error_count++;
        $settings->fn_validate_input($input);
        $this->assertTrue($error_count === count(get_settings_errors(SubClass_Settings::$option_name)));
			
		unset($settings->settings_fields['ic_bad_field']);
      
        // Clean up
        SubClass_SettingsTest::hlp_destroy_settings();

       
    }

}


