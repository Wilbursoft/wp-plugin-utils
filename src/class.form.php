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
	private $form_id = "";
	private $validate_fail_message = "";
	private $validate_success_message = "";

	// Set to false if post data not valid 
	private $post_valid = true;
	
	// Flag validation errors
	function set_post_invalid(){
		$this->post_valid = false;
	}
	
	// Constructor
	function __construct( 
					$form_name, 
					$form_id,
					$validate_fail_message,
					$validate_success_message
					) {
		
		// Check params
		assert( ! empty( $form_name ) and
				! empty( $form_id ) and
				! empty( $validate_fail_message ) and
				! empty( $validate_success_message ), 
				'none of the params cannot be empty.');
		
		// Assign to class 
		$this->form_name = $form_name;
		$this->form_id = $form_id;
		$this->validate_fail_message = $validate_fail_message;
		$this->validate_success_message = $validate_success_message;
		
	}
	
	// Opens the form html
	function get_form_open_html(){
		
		// Action url
		$form_action = get_permalink();
		
		ob_start();
		
		?> 
		<form method='post' action= '<?php echo $form_action ?>'  id='<?php echo $this->form_id  ?>'> 
		<input type='hidden' name='form_name' value='<?php echo $this->form_name ?>' />
		<?php 
		
		$output = ob_get_contents();
		ob_end_clean();
		return $output;
	}
	
	// Get the form content html
	abstract function get_form_contents_html();
	
	// Handle the form post
	abstract function handle_form_post($post);
	
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
	
	// helper checks of the post belongs to this form
	private function hlp_post_belongs_to_form($post){
		
		return ( isset($post['form_name']) and $this->form_name == $post['form_name'] );
	}
	
	// Are we handling a post for this form
	function get_form_msg_html(){

		// Declare now 
		$output = "";
		
		// Success or failure
	 	if ( $this->post_valid ){
	 		$output = "<p>" . $this->validate_success_message . "</p>";
	 	}
	 	else {
	 		$output = "<p>" . $this->validate_fail_message . "</p>";
	 	}
		
		// Done
		return $output;
	}
	
	
	// Return the full html for the form
	function get_form_html(){
	
		$output = "";
		
		// Need to handle the post??
		global $_POST;
		if( $this->hlp_post_belongs_to_form($_POST) ){
		
			// Hanlde the post
			$this->handle_form_post($_POST);
			
			// Output the message
			$output .= $this->get_form_msg_html();
		}
		
		$output .= $this->get_form_open_html();
		$output .= $this->get_form_contents_html();
		$output .= $this->get_form_close_html();
		
		return $output;
	}

}