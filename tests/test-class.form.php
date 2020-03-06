<?php

/**
* Test Utilities
*/
require_once dirname( __FILE__ ) . "/../src/class.form.php";
require_once dirname( __FILE__ ) . "/../src/utils.php";

class Form_Sublcass extends Form {
    
    // Flag 
    public $get_form_contents_html_was_called = false;
    public $handle_form_post_was_called = false;

    
    // Generate the form input html
    function get_form_contents_html()
    {
        // Flag called
        $this->get_form_contents_html_was_called = true;
        
        // Custom input
        $output = "<input type='text' name='test_email_val'  />";      
        
        // Use helper for another input 
        $output .= $this->hlp_get_input_html(
            "test_field_mame",          // $field_name 
            "test_field_txt",           // $field_text
            "test_field_placeholder",   // $placholder_text
            "div_class",                // $div_class, 
            "label_class",              // $label_class, 
            "input_class"                // $input_class
            );
            
        // USe helper for submit
        $output .= $this->hlp_get_submit_html(
            "submit_name",          // $submit_name
            "submit_text",          // $submit_text,
            "submit_div_class",     // $div_class, 
            "submit_button-class"   // $input_class
            );
            
         // Done  
        return $output;
    }
    
    // Handle the form save
    function handle_form_post ($post) {
        $this->handle_form_post_was_called = true;
        
        if ( !is_valid_email(get_value($post,'test_email_val',''))){
            $this->set_post_invalid();
        }
    }

}



class Form_Test extends WP_UnitTestCase
{


    public function test_form_render()
    {
        /**
         * Test rendering
         **/
         
        
        $form_name = 'my_test_form';
        $form_id = 'my_test_id';
        $failed_msg = 'failed-abc';
        $success_msg = 'succees-xyz';
        $form_to_test = new Form_Sublcass(
                                        $form_name, 
                                        $form_id,
                                        $failed_msg,
                                        $success_msg);
        
        $this->assertTrue( false === $form_to_test->get_form_contents_html_was_called );
        
		// Get it in one go
        $form_html = $form_to_test->get_form_html();
        $this->assertTrue(is_valid_html($form_html));
        $this->assertTrue( true === $form_to_test->get_form_contents_html_was_called );

        // Get it in bits
		$form_open_html = $form_to_test->get_form_open_html();
		$form_content_html  = $form_to_test->get_form_contents_html();
		$form_close_html  = $form_to_test->get_form_close_html();

		// Check the bits
		$this->assertTrue(! is_valid_html($form_open_html));
		$this->assertTrue(false !== strpos($form_open_html, $form_name));
		$this->assertTrue(false !== strpos($form_open_html, $form_id));
		$this->assertTrue(false !== strpos($form_open_html, '<form'));

		$this->assertTrue(is_valid_html($form_content_html));
		$this->assertTrue(! is_valid_html($form_close_html));
		$this->assertTrue(false !== strpos($form_close_html, '</form'));
    
        // Assemble the bits
        $assembled_html = $form_open_html . $form_content_html . $form_close_html;
        $this->assertTrue(is_valid_html($form_content_html));
        
        $this->assertTrue( $assembled_html === $form_html);
    }
    
    public function test_post(){

         /**
         * Test post handling 
         **/
        
        $form_name = 'my_test_form';
        $form_id = 'my_test_id';
        $failed_msg = 'failed-abc';
        $success_msg = 'succees-xyz';
        
        // Make a copy of to restore later
        global $_POST;
        $tmp_POST = $_POST;

 		// Case #1 - Post for a different form
 	    $form_to_test = new Form_Sublcass( $form_name, $form_id,  $failed_msg, $success_msg);
 	    $_POST['form_name'] = 'different_form';
        $form_html = $form_to_test->get_form_html();
        $this->assertTrue( false === $form_to_test->handle_form_post_was_called );
        $this->assertTrue(is_valid_html($form_html));
        $this->assertTrue(false === strpos($form_html, $failed_msg));
        $this->assertTrue(false === strpos($form_html, $success_msg));

	    // Case #2.1 - Right form empty fields, missing nonce
	    $form_to_test = new Form_Sublcass( $form_name, $form_id,  $failed_msg, $success_msg);
 	    $_POST['form_name'] = $form_name;
        $form_html = $form_to_test->get_form_html();
        $this->assertTrue( false === $form_to_test->handle_form_post_was_called );
        $this->assertTrue(is_valid_html($form_html));
        $this->assertTrue(false === strpos($form_html, $failed_msg));
        $this->assertTrue(false === strpos($form_html, $success_msg));
        
        // Case #2.2 - Right form empty fields, bad nonce
	    $form_to_test = new Form_Sublcass( $form_name, $form_id,  $failed_msg, $success_msg);
   	    $_POST[$form_to_test->hlp_get_nonce_name()] = 'deadbeef';
        $form_html = $form_to_test->get_form_html();
        $this->assertTrue( false === $form_to_test->handle_form_post_was_called );
        $this->assertTrue(is_valid_html($form_html));
        $this->assertTrue(false === strpos($form_html, $failed_msg));
        $this->assertTrue(false === strpos($form_html, $success_msg));
        
        // Case #2.3 - Right form empty fields, good nonce
	    $form_to_test = new Form_Sublcass( $form_name, $form_id,  $failed_msg, $success_msg);
   	    $_POST[$form_to_test->hlp_get_nonce_name()] = wp_create_nonce($form_to_test->hlp_get_nonce_action());
        $form_html = $form_to_test->get_form_html();
        $this->assertTrue( true === $form_to_test->handle_form_post_was_called );
        $this->assertTrue(is_valid_html($form_html));
        $this->assertTrue(false !== strpos($form_html, $failed_msg));
        $this->assertTrue(false === strpos($form_html, $success_msg));
        
	    // Case #3- Right form bad field value
	    $form_to_test = new Form_Sublcass( $form_name, $form_id,  $failed_msg, $success_msg);
 	    $_POST['form_name'] = $form_name;
 	    $_POST['test_email_val'] = 'notanemail';
        $form_html = $form_to_test->get_form_html();
        $this->assertTrue( true === $form_to_test->handle_form_post_was_called );
        $this->assertTrue(is_valid_html($form_html));
        $this->assertTrue(false !== strpos($form_html, $failed_msg));
        $this->assertTrue(false === strpos($form_html, $success_msg));
        
    	// Case #4- correct
	    $form_to_test = new Form_Sublcass( $form_name, $form_id,  $failed_msg, $success_msg);
 	    $_POST['form_name'] = $form_name;
 	    $_POST['test_email_val'] = 'test@test.com';
        $form_html = $form_to_test->get_form_html();
        $this->assertTrue( true === $form_to_test->handle_form_post_was_called );
        $this->assertTrue(is_valid_html($form_html));
        $this->assertTrue(false === strpos($form_html, $failed_msg));
        $this->assertTrue(false !== strpos($form_html, $success_msg));
        
        // cover set_post_invalid() with an override string
        $new_error_msg = 'new error message';
        $form_to_test->set_post_invalid($new_error_msg);
        $form_html = $form_to_test->get_form_html();
        $this->assertTrue(is_valid_html($form_html));
        $this->assertTrue(false !== strpos($form_html, $new_error_msg));




        
        // Restore
        $_POST = $tmp_POST;

    }
    
     public function test_helpers(){

         /**
         * Test post handling 
         **/
        $form_name = 'my_test_form';
        $form_id = 'my_test_id';
        $failed_msg = 'failed-abc';
        $success_msg = 'succees-xyz';
        $form_to_test = new Form_Sublcass( $form_name, $form_id,  $failed_msg, $success_msg);
        $form_html = $form_to_test->get_form_html();
        $this->assertTrue(is_valid_html($form_html));
        $this->assertContains('form', $form_html);
        $this->assertContains("test_field_mame", $form_html);
        $this->assertContains('test_field_txt', $form_html);
        $this->assertContains('test_field_placeholder', $form_html);
        $this->assertContains('div_class', $form_html);
        $this->assertContains('label_class', $form_html);
        $this->assertContains('input_class', $form_html);
        $this->assertContains('submit_name', $form_html);
        $this->assertContains('submit_text', $form_html);
        $this->assertContains('submit_div_class', $form_html); 
        $this->assertContains('submit_button', $form_html);

     }
    
    
}