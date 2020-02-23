<?php

/**
* Test Utilities
*/
require_once dirname( __FILE__ ) . "/../src/class.render.php";

class RenderTest extends WP_UnitTestCase
{



    public function test_dbg_trace()
    {
        $this->assertTrue( true);
        
        $render_class = new Render();
    }
    
}