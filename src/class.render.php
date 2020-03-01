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
	
	// Get the dynamic css action name
	function get_dynamic_css_action_name(){
		return ('dyn_css-' . $this->short_code_tag); 
	}
	// Abstract & overridable methods
	
	// Called to render the short code 
	abstract function render_shortcode();
	
	// Called to render the dynamic css content
	// Override if dynamic css needed.
	function render_dynamic_css(){}
	
	// Constructor
	function __construct( $short_code_tag ) {
		
		// Check params
		assert(! empty( $short_code_tag ),	'$short_code_tag cannot be empty.');
			      
		// Assign to class 
		$this->short_code_tag = $short_code_tag;
		
		
		// Register for init call back
    	add_action( 'init', array ($this, 'fn_register_short_codes'));
  
		// Add in hooks if sub class wants todo dynamic css
		if( method_is_overriden('Render', $this, 'render_dynamic_css')){
			
	    	// Scripts and CSS
			add_action( 'wp_enqueue_scripts', array($this, 'fn_enqueue_scripts') );
			
			// Ajax actions to handle dynamic css
			add_action('wp_ajax_'. $this->get_dynamic_css_action_name(), array($this,'fn_dynamic_css'));
	    	add_action('wp_ajax_nopriv_' . $this->get_dynamic_css_action_name(), array($this,'fn_dynamic_css'));

		}
		else{
			
			// Trace
			dbg_trace(); 
		}
	}
	
	// Enqueue scripts and styles
	function fn_enqueue_scripts()
	{
		// Trace
		dbg_trace();
		
		  // Style sheets
		wp_enqueue_style(
		            'render' . $this->get_dynamic_css_action_name(),
		            admin_url('admin-ajax.php').'?action=' . $this->get_dynamic_css_action_name(),
		            array(),
		            time(),
		            'all'
		        	);
	}
  
	// Call back to generate dynamic css
	// @codeCoverageIgnoreStart
	function fn_dynamic_css(){
	
		// Do content type header here, outside of unit test coverage
		header("Content-type: text/css; charset: UTF-8");
		
		// Create the actual CSS body
		$this->render_dynamic_css();
	
	}
	// @codeCoverageIgnoreEnd

	
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