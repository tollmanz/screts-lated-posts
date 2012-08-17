<?php
/*
Plugin Name: Screts-lated Posts 0.1
Plugin URI: http://github.com/tollmanz/screts-lated-posts
Description: Scalable, retina ready, and responsive related posts widget. It's all the buzz.
Author: Zack Tollman
Version: 0.1
Author URI: http://github.com/tollmanz
*/

// Define contants
define( 'SCRETS_VERSION', '0.1' );
define( 'SCRETS_ROOT', dirname(__FILE__) );
define( 'SCRETS_FILE_PATH', SCRETS_ROOT . '/' . basename(__FILE__) );
define( 'SCRETS_URL', plugins_url( '/', __FILE__ ) );

/**
 * Initiator class.
 *
 * This is a generic class for organizing the different functional
 * pieces of the plugin. It is instantiated on every request, so be
 * careful about how additional function is loaded in the class.
 *
 * @since   0.1
 */
class ScretsLatedPosts {

    /**
     * Loads plugin functionality.
     *
     * @since   0.1
     *
     * @return  void
     */
    public function init() {
        // Load widgets
        require_once ( SCRETS_ROOT . '/includes/widgets/related-posts.php' );
    }
}

$ScretsLatedPosts = new ScretsLatedPosts;
$ScretsLatedPosts->init();