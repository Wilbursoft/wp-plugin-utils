# wp-plugin-utils
Utilities and classes for WordPress plugins \
![Tests](https://github.com/Wilbursoft/wp-plugin-utils/workflows/Tests/badge.svg)

# Usage

To use this library in your plugin

1) go to the root of your plugin directory and pull in as a sub module 

    
    $ git submodule add https://github.com/Wilbursoft/wp-plugin-utils.git
    
You now have a sub directory 'wp-plugin-utils/src' with the raw librarby code in it!
But thats not ready to use yet, you need to put it in to a unique name space for this plugin so 
it does not clash whwne used by multiple plugins 

2) Run the following where 'my-plugin-folder' is your plugin folder 

    
    $ php ./wp-plugin-utils/pull-src2lib.php my-plugin-folder

You now have a sub directory 'wp-plugin-utils/lib' with a copy of the code ready to use. 
You will see a line has been inserted at the top to move the coe into a unique name space .

    // BEGIN_NAMESPACE_EDIT_MARKER
    namespace my_plugin_folder\plugin_utils; 
    // END_NAMESPACE_EDIT_MARKER

Note: The hyphons in my-plugin-folder are now underscores my_plugin_folder. Hyphiones are invalid chars. 

3) Now you include in the normal way, but you reference via the name space. 
You can make the namespace reference prefix shorter by aliasing with use.

    require_once dirname( __FILE__ ) .'/wp-plugin-utils/lib/utils.php';
    use wp_action_network_signup\plugin_utils as utils;

    // trace
    utils\dbg_trace("");
