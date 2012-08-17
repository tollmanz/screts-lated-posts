<?php global $screts_related_posts; ?>
<ul>
    <?php foreach ( $screts_related_posts as $key => $post ) : ?>
        <li>
            <a href="<?php echo get_permalink( $post->ID ); ?>" title="<?php echo $post->post_title; ?>">
                <?php echo $post->post_title; ?>
            </a>
        </li>
    <?php endforeach; ?>
</ul>