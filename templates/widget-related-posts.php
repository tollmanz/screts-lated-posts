<?php global $screts_related_posts; ?>
<?php if ( $screts_related_posts->have_posts() ) : ?>
    <ul>
        <?php while ( $screts_related_posts->have_posts() ) : $screts_related_posts->the_post(); ?>
            <li>
                <a href="<?php the_permalink() ?>" title="<?php the_title_attribute(); ?>">
                    <?php the_title(); ?>
                </a>
            </li>
        <?php endwhile; ?>
    </ul>
<?php endif; ?>
<?php wp_reset_postdata(); ?>