<?php

/**
* Non admin rendering 
*/

// BEGIN_NAMESPACE_EDIT_MARKER
// <-- namespace statement will get added here ---> 
// END_NAMESPACE_EDIT_MARKER

// Our form class 
abstract class Form {
	
	// Class member variabes
	private $form_name = "";
	
	// Constructor
	function __construct( $form_name, $form_id ) {
		
		// Check params
		assert(! empty( $form_name ),	'$form_name cannot be empty.');
		assert(! empty( $form_id ),		'$form_id cannot be empty.');
		
		// Assign to class 
		$this->form_name = $form_name;
		$this->form_id = $form_id;
		
		// Callback for form post
		add_action( "admin_post_nopriv_{$form_name}", array($this, 'fn_form_post' ));
		add_action( "admin_post_{$form_name}", array($this, 'fn_form_post' ));
	}
	
	
	
	
	// Opens the form html
	function get_form_open_html(){
		
		// Action url
		$form_action = esc_url( admin_url('admin-post.php') );
		
		ob_start();
		
		?> 
		<form method='post' action= '<?php echo $form_action ?>'  id='<?php echo $this->form_id  ?>'> 
		<input type='hidden' name='action' value='<?php echo $this->form_name ?>' />
		<?php 
		
		$output = ob_get_contents();
		ob_end_clean();
		return $output;
	}
	
	// Get the form content html
	abstract function get_form_contents_html();
	
	// Handle the form post
	abstract function handle_form_post();
	
	// Call back for the form post
	function fn_form_post(){
		$this->handle_form_post();
	}
	
	// Closes the form html
	function get_form_close_html(){
		
		ob_start();
		
		?> 
		</form>
		<?php 
		
		$output = ob_get_contents();
		ob_end_clean();
		return $output;
	}
	
	
	// Return the full html for the form
	function get_form_html(){
	
		$output = "";
		$output .= $this->get_form_open_html();
		$output .= $this->get_form_contents_html();
		$output .= $this->get_form_close_html();
		
		return $output;
	}

}