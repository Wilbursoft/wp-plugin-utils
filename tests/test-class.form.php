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
        $this->get_form_contents_html_was_called = true;
        $output = "<input type='text' name='test_email_val'  />";
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

	    // Case #2- Right form empry fields
	    $form_to_test = new Form_Sublcass( $form_name, $form_id,  $failed_msg, $success_msg);
 	    $_POST['form_name'] = $form_name;
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
        
        // Restore
        $_POST = $tmp_POST;

    }
    
}