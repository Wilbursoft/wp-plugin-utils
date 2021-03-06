<?php

/**
* Test Utilities
*/
require_once dirname( __FILE__ ) . "/../src/utils.php";

class CurlRequest_Test extends WP_UnitTestCase{
    
    public function test_methods(){
        
        $this->assertTrue(true);

        $url = "https://example.org";
        $request = new CurlRequest($url);
        $request->set_option( CURLOPT_POST, false);
        $request->execute();
        $request->get_info(CURLINFO_HTTP_CODE);
        $request->get_exec_output();
        $request->close();

    }
    
}

class A_Base_class {
    function override_me(){}
    function donot_override_me(){}
}

class A_Sub_class extends A_Base_class {
    function override_me(){}
}

class UtilsTest extends WP_UnitTestCase
{



    public function test_dbg_trace()
    {
        dbg_trace ("test trace");
        $this->assertTrue( true);

    }
    
    public function test_method_is_overriden(){
        
        $sub_class_instance = new A_Sub_class();
        
        $this->assertTrue( method_is_overriden('A_Base_class', $sub_class_instance, 'override_me'));
        $this->assertTrue( ! method_is_overriden('A_Base_class', $sub_class_instance, 'donot_override_me'));

    }

    
    
    public function test_methods()
    {

            // beginsWidth +ve
       		$this->assertTrue( beginsWith("hello freddy","hello"));

            // beginsWidth -ve
       		$this->assertTrue( !beginsWith("hello freddy","freddy"));
       		
       		// endsWidth +ve
       		$this->assertTrue( endsWith("hello freddy","freddy"));

       		// endsWidth -ve
       		$this->assertTrue( !endsWith("hello freddy","hello"));
       		$this->assertTrue( !endsWith("hello freddy","fredd"));
       		
    }
    
    public function test_options(){
  
   		
   		$this->assertTrue( 45 == get_option_array_value('test_option_array', 'value1', 45));
   		$this->assertTrue( 'fred' == get_option_array_value('test_option_array', 'value1', 'fred'));
   		$test_option_array = array(
    			'value1'    => 'frog',
    			'value2'    => 'fish',
    		);
			
        update_option('test_option_array', $test_option_array);
        $this->assertTrue( 'fred' != get_option_array_value('test_option_array', 'value1', 'fred'));
        $this->assertTrue( 'frog' == get_option_array_value('test_option_array', 'value1', 'fred'));
        $this->assertTrue( 'frog' == get_option_array_value('test_option_array', 'value1', 'frog'));
        $this->assertTrue( 'fish' == get_option_array_value('test_option_array', 'value2', 'frog'));
        $this->assertTrue( 'henry' == get_option_array_value('test_option_array', 'value3', 'henry'));



    }
   
    public function test_options_twice(){
  
        $this->test_options();
    }
    
    public function test_is_valid_html(){
  
        $this->assertTrue(is_valid_html("<p> </p>"));
        $this->assertTrue(! is_valid_html("<p> missing close tag"));
        $this->assertTrue(! is_valid_html("<td> miss match tag </tr>"));
        $this->assertTrue(is_valid_html('<input type="hidden" id="ic_info_card_nonce" name="ic_info_card_nonce" value="7dfc5d6075" />'));
        
        ob_start();
        $this->assertTrue(!is_valid_html('<input type="hidden" id="ic_info_card_nonce" name="ic_info_card_nonce" value="7dfc5d6075" >'));
        $output = ob_get_contents();
        ob_end_clean();
        $this->assertTrue("" == $output);

        ob_start();
        $this->assertTrue(!is_valid_html('<input type="hidden" id="ic_info_card_nonce" name="ic_info_card_nonce" value="7dfc5d6075" >',true));
        $output = ob_get_contents();
        ob_end_clean();
        $this->assertTrue("" != $output);


    }    
    
    public function test_is_valid_colour(){
        
        $this->assertTrue(is_valid_colour('#8224e3'));
        $this->assertTrue(is_valid_colour('#8224e3'));
        $this->assertTrue(is_valid_colour('#000000'));
        $this->assertTrue(is_valid_colour('#ffffff'));        
        $this->assertTrue(is_valid_colour('#010101'));
        $this->assertTrue(is_valid_colour('#999999'));        
      
        
        $this->assertTrue(! is_valid_colour('fred'));
        $this->assertTrue(! is_valid_colour('8224e3'));
        $this->assertTrue(! is_valid_colour('#82f24e3'));
        $this->assertTrue(! is_valid_colour('rgb(0,10,10)'));        
        $this->assertTrue(! is_valid_colour('green'));
        $this->assertTrue(! is_valid_colour(''));        
        $this->assertTrue(! is_valid_colour('fred'));
        $this->assertTrue(! is_valid_colour('8224e3'));        
        $this->assertTrue(! is_valid_colour('fred'));
        $this->assertTrue(! is_valid_colour('#824e3'));
    }
   
      public function test_is_valid_email(){
        
        $this->assertTrue(is_valid_email('test@test.com'));
          
        $this->assertTrue(! is_valid_email(''));
        $this->assertTrue(! is_valid_email('fred'));
        $this->assertTrue(! is_valid_email('#824e3'));
    }
   
    
    public function test_is_valid_integer_in_range(){
        
        $this->assertTrue(is_valid_integer_in_range(1,-2,2));
        $this->assertTrue(is_valid_integer_in_range(-1,-2,2));
        $this->assertTrue(is_valid_integer_in_range('1',-2,2));
        $this->assertTrue(is_valid_integer_in_range('-1','-2','2'));
        $this->assertTrue(is_valid_integer_in_range(2,-2,2));
        $this->assertTrue(is_valid_integer_in_range(-2,-2,2));
        $this->assertTrue(is_valid_integer_in_range('2',-2,2));
        $this->assertTrue(is_valid_integer_in_range('-2','-2','2'));
        
        $this->assertTrue(false === is_valid_integer_in_range('3','-2','2'));
        $this->assertTrue(false === is_valid_integer_in_range('3','-2','2'));
        $this->assertTrue(false === is_valid_integer_in_range(3,-2,'2'));
        $this->assertTrue(false === is_valid_integer_in_range(3,'-2','2'));    
        $this->assertTrue(false === is_valid_integer_in_range('one','-2','2')); 
        $this->assertTrue(false === is_valid_integer_in_range('-1.1','-2','2'));
    }
    
    
    public function test_is_enqueued(){
       
        // is_script_enqueued
        $this->assertTrue(false === is_script_enqueued('path/test-utils-script.js'));
        wp_enqueue_script( 'test-utils-script', 'path/test-utils-script.js'); 
        $this->assertTrue(true === is_script_enqueued('path/test-utils-script.js'));
        
        // is_style_enqueued
        wp_enqueue_style( 'dummy-style', 'path/dummy-style.css'); 
        $this->assertTrue(true === is_style_enqueued('path/dummy-style.css'));    
        $this->assertTrue(false === is_style_enqueued('path/test-utils-style.css'));
        wp_enqueue_style( 'test-utils-style', 'path/test-utils-style.css'); 
        $this->assertTrue(true === is_style_enqueued('path/test-utils-style.css'));   
        
        $this->assertTrue(false === helper_is_enqueued(null,""));   

       
    }
    
    public function test_get_font_awesome_icon_list(){
        
        $array_of_icons = get_font_awesome_icon_list();
        $this->assertTrue(50 < count($array_of_icons));   

    }
    
}

