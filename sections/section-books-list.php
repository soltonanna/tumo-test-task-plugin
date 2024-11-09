<?php
if ( isset( $books ) && $books->have_posts() ) : ?>
    <ul class="book-list">
        <?php while ( $books->have_posts() ) : $books->the_post(); ?>
            <?php $author = get_post_meta( get_the_ID(), '_book_author', true ); ?>
            
            <li class="book-item related-book-item">
                <div class="book-item__thumbnail">
                    <?php if ( has_post_thumbnail() ) : ?>
                        <?php the_post_thumbnail( 'full' ); ?>
                    <?php else : ?>
                        <img src="<?php echo plugin_dir_url(__FILE__) . '../images/no-book.jpg'; ?>" alt="No image available" />
                    <?php endif; ?>
                </div>
                
                <h3 class="book-item__title">
                    <a href="<?php the_permalink(); ?>" class="font_h3"><?php the_title(); ?></a>
                </h3>
                
                <?php if ( $author ) : ?>
                    <p class="book-item__author">by <?php echo esc_html( $author ); ?></p>
                <?php endif; ?>
            </li>
        <?php endwhile; ?>
    </ul>
<?php else : ?>
    <p class="notification">No books found.</p>
<?php endif; ?>

<?php
wp_reset_postdata();
