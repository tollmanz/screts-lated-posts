<?php
/*
Plugin Name: Screts-lated Posts 0.6
Plugin URI: http://github.com/tollmanz/screts-lated-posts
Description: Scalable, retina ready, and responsive related posts widget. It's all the buzz.
Author: Zack Tollman
Version: 0.6
Author URI: http://github.com/tollmanz
*/

// Define contants
define( 'SCRETS_VERSION', '0.6' );
define( 'SCRETS_ROOT', dirname(__FILE__) );
define( 'SCRETS_FILE_PATH', SCRETS_ROOT . '/' . basename(__FILE__) );
define( 'SCRETS_URL', plugins_url( '/', __FILE__ ) );

/**
 * Register the Related Posts widgets.
 *
 * @since   0.1
 * @return  void
 */
function screts_register_related_posts_widget() {
	register_widget( 'scretsRelatedPosts' );
}
add_action( 'widgets_init', 'screts_register_related_posts_widget' );

/**
 * Define the Related Posts widget.
 *
 * This class includes the standard overriden functions for a WP Widget.
 * Additionally, it includes all of the code for processing the related
 * posts.
 *
 * @since   0.1
 */
class scretsRelatedPosts extends WP_Widget {

	/**
	 * Define the widget.
	 *
	 * @since   0.1
	 *
	 * @return  scretsRelatedPosts
	 */
	public function __construct() {
		parent::__construct( 'screts_related_posts', __( 'Related Posts', 'scretslated-posts' ), array(
			'classname' => 'scret-related-posts',
			'description' => "The most friggin' advanced related posts widget ever."
		) );
	}

	/**
	 * Display the widget content.
	 *
	 * Calls the function that generates the related posts output and
	 * displays them within the widget context as defined by before/after
	 * widget and title.
	 *
	 * @since   0.1
	 *
	 * @param   array           $args               Global widget arguments.
	 * @param   array           $instance           Widget instance values.
	 * @return  void
	 */
	public function widget( $args, $instance ) {
		if ( ! is_single() )
			return;

		$related_posts_html = $this->generate_related_posts( $instance['number'] );

		if ( false === $related_posts_html )
			return;

		echo $args['before_widget'];

		echo $args['before_title'] . esc_html( $instance['title'] ) . $args['after_title'];

		echo $related_posts_html;

		echo $args['after_widget'];
	}

	/**
	 * Render the widget form HTML.
	 *
	 * @since   0.1
	 *
	 * @param 	array		    $instance		    Array of current values for fields.
	 * @return 	void
	 */
	public function form( $instance ) {
		$title = ( isset( $instance['title'] ) ) ? $instance['title'] : '';
		$number = ( isset( $instance['number'] ) ) ? $instance['number'] : '';
	?>
		<p>
			<label for="<?php echo $this->get_field_id('title'); ?>"><?php echo __( 'Title', 'scretslated-posts' ); ?>:</label>
			<input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo esc_attr( $title ); ?>" />
		</p>
		<p>
			<label for="<?php echo $this->get_field_id( 'number' ); ?>"><?php echo __( 'Number of items to show', 'scretslated-posts' ); ?>:</label>
			<input id="<?php echo $this->get_field_id( 'number' ); ?>" name="<?php echo $this->get_field_name( 'number' ); ?>" type="text" value="<?php echo absint( $number ); ?>" size="3" />
		</p>
	<?php
	}

	/**
	 * Sanitize the form input.
	 *
	 * @since   0.1
	 *
	 * @param 	array 		    $new_instance       New field values.
	 * @param 	array 		    $old_instance		Previous field values.
	 * @return 	array							    Sanitized field values.
	 */
	public function update( $new_instance, $old_instance ) {
		$title = ( isset( $new_instance['title'] ) ) ? esc_html( $new_instance['title'] ) : '';
		$number = ( isset( $new_instance['number'] ) && $new_instance['number'] > 0 ) ? absint( $new_instance['number'] ) : '';

		return array( 'title' => $title, 'number' => $number );
	}

	/**
	 * Issues query and generates markup for the related posts.
	 *
	 * Related posts, as defined by being in either the same category or tag as the
	 * current post, are queried for using tax_query. The query results are passed to
	 * the widget-related-posts.php template, which defines the HTML markup for the
	 * post display.
	 *
	 * @since   0.1
	 *
	 * @param   int             $number             Number of posts to display in this widget instance.
	 * @return  string                              The related posts HTML.
	 */
	public function get_related_posts( $number ) {
		// Set the number default if not set
		$number = ( empty( $number ) || absint( $number ) > 0 ) ? 3 : absint( $number );

		// Generate a cache key for the post
		$cache_key = 'screts-posts-' . get_the_ID() . $number;

		// Attempt to get HTML from cache
		$related_posts_html = get_transient( $cache_key );

		// If a cache object was not found, regenerate it now
		if ( false === $related_posts_html ) {
			// Get the related posts HTML
			$related_posts_html = $this->retrieve_related_posts( $number );

			// Cache whatever is returned for 12 hours
			set_transient( $cache_key, $related_posts_html, 60 * 60 * 12 );
		}

		// Return the related posts
		if ( ! empty( $related_posts_html ) )
			return $related_posts_html;
		else
			return false;
	}

	/**
	 * Queries for the posts and generates the HTML.
	 *
	 * A new instance of WP_Query is instantiated. The resulting posts are used to generate
	 * HTML using the widget template.
	 *
	 * @param   int             $number             Number of posts to display in this widget instance.
	 * @return  string                              The related posts HTML.
	 */
	public function retrieve_related_posts( $number ) {
		// Get the post's categories and tags
		$categories = get_the_category();
		$category_ids = false !== $categories && ! is_wp_error( $categories ) ? wp_list_pluck( $categories, 'term_id' ) : false;

		$tags = get_the_tags();
		$tag_ids = false !== $tags && ! is_wp_error( $tags ) ? wp_list_pluck( $tags, 'term_id' ) : false;

		// Execute related posts query
		$args = array(
			'post_type' => 'post',
			'posts_per_page' => $number,
			'tax_query' => array(
				'relation' => 'OR'
			),
			'post__not_in' => array( get_the_ID() ),
			'orderby' => 'rand'
		);

		// Add get by category
		if ( false !== $category_ids ) {
			$args['tax_query'][] = array(
				'taxonomy' => 'category',
				'field' => 'id',
				'terms' => $category_ids
			);
		}

		// Add get by tag
		if ( false !== $tag_ids ) {
			$args['tax_query'][] = array(
				'taxonomy' => 'post_tag',
				'field' => 'id',
				'terms' => $tag_ids
			);
		}

		// If categories and tags are not set, remove the tax_query
		if ( false === $category_ids && false === $tag_ids )
			unset( $args['tax_query'] );

		global $screts_related_posts;
		$screts_related_posts = new WP_Query( $args );

		// Start object buffering to capture template output to save to cache
		ob_start();
		require ( SCRETS_ROOT . '/templates/widget-related-posts.php' );
		$related_posts_html = ob_get_contents();
		ob_end_clean();

		return $related_posts_html;
	}
}