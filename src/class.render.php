<?php

/**
* Non admin rendering 
*/

// BEGIN_NAMESPACE_EDIT_MARKER
// <-- namespace statement will get added here ---> 
// END_NAMESPACE_EDIT_MARKER

// Our plugins render class 
abstract class Render {
	
	// Class member variabes
	private $short_code_tag = "";
	
	// Get short code tag
	function get_short_code_tag(){ 
		return $this->short_code_tag; 
	}
	
	// Abstract methods
	
	// Called to render the short code 
	abstract function render_shortcode();
	
	
	// Constructor
	function __construct( $short_code_tag ) {
		
		// Check params
		assert(! empty( $short_code_tag ),	'$short_code_tag cannot be empty.');
			      
		// Assign to class 
		$this->short_code_tag = $short_code_tag;
		
		// Register for init call back
    	add_action( 'init', array ($this, 'fn_register_short_codes'));
	}
	
	// Call back to register short codes. 
	function fn_register_short_codes() {
	  
	    // Add the short code(s)
	    add_shortcode($this->short_code_tag, array ($this, 'fn_render_shortcode'));
	    
	}
	
	// Call back to render the short code 
	function fn_render_shortcode(){
		
		// Call child class 
		return $this->render_shortcode();
	}
 
 
	
}