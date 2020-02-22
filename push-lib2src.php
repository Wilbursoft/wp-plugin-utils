<?php

/**
* 
*/


// Globals
$from_dir = "";
$to_dir = "";
$marker_line_text = 'BEGIN_NAMESPACE_EDIT_MARKER';
$replacement_line = "// <-- namespace statement will get added here ---> \n";


// Are we being called from push-lib2src??
if ('push-lib2src' === basename(realpath($argv[0]),".php" )) {

    // directories src -> lib
    $from_dir = dirname(__FILE__) . '/lib';
    $to_dir = dirname(__FILE__) . '/src'; 
    
    echo "push-lib2src - copying *.php from ./lib to ./src removing name space line \n";

}
else { // pull-src2lib
  
    // Check for args
    if ( ! ( isset($argv) and isset($argv[1]))) {
        echo ("ERROR: no namespace specified. \n");
        echo ("USAGE: $ php ./lib2src.php [namespace] \n");
        echo ("where [namespace] is the namespace to use. \n");
        die();
    }

  
    // Get the name space and normalise 
    $raw_namespace = $argv[1];
    $namespace = str_replace('-', '_', $raw_namespace);

    // Notify it was changed 
    if ($raw_namespace !== $namespace){
        
        echo "Note: namespace was normalised from $raw_namespace to $namespace .\n";
    }
    
    // directories lib -> src
    $from_dir = dirname(__FILE__) . '/src';
    $to_dir = dirname(__FILE__) . '/lib';
    
    // Marker and replacement text
    $replacement_line = "namespace $namespace\plugin_utils;";
    echo "pull-src2lib - copying *.php from ./src to ./lib adding '$replacement_line'  \n";
    $replacement_line = "$replacement_line \n";
    
}





// Make the src dir if not exits
if (!file_exists($to_dir)) {
    mkdir($to_dir, 0777, true);
}

// Loop over from fir
$dir = new DirectoryIterator($from_dir);
foreach ($dir as $fileinfo) {
      
    // Only want php files
    if ('php' == $fileinfo->getExtension()){
        
        // Build filenames 
        $file_name  = $fileinfo->getFilename();
        $from_path = $from_dir . '/' . $file_name;
        $to_path = $to_dir . '/' . $file_name;
        echo $file_name . "\n";
        
        // Open files
        $from_handle = fopen($from_path, "r") or die("ERROR: Unable to open file $from_path for reading.\n");
        $to_handle = fopen($to_path, "w+") or die("ERROR: Unable to open file $to_path for truncate and writing.\n");

        // Loop line by line
        $marker_found_on_last_line = false;
        $replacement_done = false;
        while (($line = fgets($from_handle)) !== false) {
            
            
            // Was the marker on the lasts line??
            if ( $marker_found_on_last_line) {
                
                // Do the replacement
                $line = $replacement_line;
                $marker_found_on_last_line = false;
                $replacement_done = true;

            } elseif ( ! $replacement_done ) {
                
                // Look for marker
                if( false !== strpos( $line, $marker_line_text)) {
                    
                    // Found 
                    $marker_found_on_last_line = true;
                }
            }
            
            
            // Write the line
            if (false == fputs($to_handle, $line)){
                die("ERROR: Unable to write to  $to_path . \n");
            }
                
        }
        
       // Close
       fclose($from_handle);
       fclose($to_handle);
       
       // Look for error
       if ( ! $replacement_done) {
           die("ERROR: Marker txt '$marker_line_text' was not found in '$from_path' .\n");
       }

    }

}
