<?php

/**
* Test Utilities
*/
require_once dirname( __FILE__ ) . "/../src/class.render.php";
require_once dirname( __FILE__ ) . "/../src/utils.php";


class Render_SubClass extends Render {
    
    static public $test_render_html = "<div> <h1> test heading  </h1> </div>";
    
    // Instanciate the abstract method
    function render_shortcode(){
        return self::$test_render_html ;
    }
    
    // Static member to access to private methods for testing
    static function test_methods_privately($test_case){
        
        // Do the init
        $short_code_tag = 'short_code';
        ob_start();
        $renderer = new Render_SubClass($short_code_tag);
        $renderer->fn_register_short_codes();
        $output = ob_get_contents();
        $test_case->assertTrue("" == $output);
        ob_end_clean();
        
        // Check at least one 
        global $shortcode_tags;
        $test_case->assertTrue( 1 <= count($shortcode_tags));
        
        // Check it's there 
        $test_case->assertTrue(isset($shortcode_tags[$short_code_tag]));
        
        // Check short code getter
        $test_case->assertTrue($short_code_tag === $renderer->get_short_code_tag());
        
        // Call render
        $test_case->assertTrue(is_valid_html($renderer->fn_render_shortcode()));
        $test_case->assertTrue(is_valid_html(self::$test_render_html === $renderer->fn_render_shortcode()));

    }

}


class Render_SubClass_with_dynamic_css extends Render_SubClass {
    
    function render_dynamic_css(){}

}


class Render_Test extends WP_UnitTestCase
{



    public function test_methods()
    {
        $this->assertTrue( true);
        
        Render_SubClass::test_methods_privately($this);
        
    }
    
    public function test_dynamic_css(){
        
        $dynamic_css_render = new Render_SubClass_with_dynamic_css('render_dynamic_css_short_code');
        $dynamic_css_render->fn_enqueue_scripts();
    }
}