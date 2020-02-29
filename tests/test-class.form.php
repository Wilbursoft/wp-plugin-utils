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

        $output = "<input type='text' size='18' placeholder='' name='ans_mv_EMAIL' id='ans_mv_EMAIL' class='ans_input' />";
        return $output;
    }
    
    // Handle the form save
    function handle_form_post () {
        $this->handle_form_post_was_called = true;
    }

}



class Form_Test extends WP_UnitTestCase
{


    public function test_methods()
    {
        /**
         * Test rendering
         **/
         
        
        $form_name = 'my_test_form';
        $form_id = 'my_test_id';
        $form_to_test = new Form_Sublcass($form_name, $form_id);
        
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

        /**
         * Test post handling 
         **/
        
        $form_name = 'my_test_form';
        $form_id = 'my_test_id';
        $form_to_test = new Form_Sublcass($form_name, $form_id);
        $this->assertTrue( false === $form_to_test->handle_form_post_was_called );
        $form_to_test->fn_form_post();
        $this->assertTrue( true === $form_to_test->handle_form_post_was_called );

    }
    
}