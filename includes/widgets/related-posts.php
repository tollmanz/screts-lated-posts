<?php

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
            'description' => 'The most friggin\' advanced related posts widget ever.'
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

        echo $args['before_widget'];

        echo $args['before_title'] . $instance['title'] . $args['after_title'];

        $this->generate_related_posts( $instance['number'] );

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
        $title = isset( $instance['title'] ) ? $instance['title'] : '';
        $number = isset( $instance['number'] ) ? $instance['number'] : '';
    ?>
        <p>
            <label for="<?php echo $this->get_field_id('title'); ?>"><?php echo __( 'Title', 'scretslated-posts' ); ?>:</label>
            <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo $title; ?>" />
        </p>
        <p>
            <label for="<?php echo $this->get_field_id( 'number' ); ?>"><?php echo __( 'Number of items to show', 'scretslated-posts' ); ?>:</label>
            <input id="<?php echo $this->get_field_id( 'number' ); ?>" name="<?php echo $this->get_field_name( 'number' ); ?>" type="text" value="<?php echo $number; ?>" size="3" />
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
        $title = isset( $new_instance['title'] ) ? $new_instance['title'] : '';
        $number = isset( $new_instance['number'] ) ? $new_instance['number'] : '';

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
     * @return  void
     */
    public function generate_related_posts( $number ) {
        global $post;

        // Get the post's categories and tags
        $categories = get_the_category();
        $category_ids = wp_list_pluck( $categories, 'term_id' );

        $tags = get_the_tags();
        $tag_ids = wp_list_pluck( $tags, 'term_id' );

        // Execute related posts query
        global $screts_related_posts;
        $screts_related_posts = query_posts( array(
            'post_type' => 'post',
            'posts_per_page' => $number,
            'tax_query' => array(
                'relation' => 'OR',
                array(
                    'taxonomy' => 'category',
                    'field' => 'id',
                    'terms' => $category_ids
                ),
                array(
                    'taxonomy' => 'post_tag',
                    'field' => 'id',
                    'terms' => $tag_ids
                )
            ),
            'post__not_in' => array( $post->ID ),
            'orderby' => 'rand'
        ) );

        require ( SCRETS_ROOT . '/templates/widget-related-posts.php' );
    }
}