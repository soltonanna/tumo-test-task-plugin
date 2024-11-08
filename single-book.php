<?php get_header(); ?>

<?php if ( have_posts() ) : while ( have_posts() ) : the_post(); ?>
    <?php
    
    
    
    ?>
    <div class="single-book-page">
        
        <div class="top-section">
            <div class="single-book-info left-part">
                <div>
                    <div class="book-thumbnail">
                        <?php if ( has_post_thumbnail() ) : ?>
                            <?php the_post_thumbnail( 'full' ); ?>
                        <?php else : ?>
                            <img src="<?php echo plugin_dir_url(__FILE__) . '/images/no-book.jpg'; ?>" alt="No image available" />
                        <?php endif; ?>
                    </div>

                    <div class="book-info">
                        <?php $isbn = get_post_meta( get_the_ID(), '_book_isbn', true );
                        if ( !empty( $isbn ) ) : ?>
                            <p><span>ISBN: </span><?php echo esc_html( $isbn ); ?></p>
                        <?php endif; ?>

                        <?php $publication_year = get_post_meta( get_the_ID(), '_book_publication_year', true );
                        if ( !empty( $publication_year ) ) : ?>
                            <p><span>Publication Year: </span><?php echo esc_html( $publication_year ); ?></p>
                        <?php endif; ?>

                        <?php $genres = wp_get_post_terms( get_the_ID(), 'book_genre', array( 'fields' => 'names' ) );
                        if ( !empty( $genres ) ) : ?>
                            <p><span>Genre: </span><?php echo esc_html( implode( ', ', $genres ) ); ?></p>
                        <?php endif; ?>
                    </div>
                </div>

                <div>
                    <h1 class="book-title">
                        <?php the_title(); ?>
                    </h1>

                    <p class="book-author">
                        by <?php echo esc_html( get_post_meta( get_the_ID(), '_book_author', true ) ); ?>
                    </p>

                    <div class="book-description">
                        <?php the_content(); ?>
                    </div>
                </div>
            </div>

            <div class="related-books right-part">
                <?php if ( is_active_sidebar( 'sidebar-1' ) ) : ?>
                    <div id="sidebar" class="widget-area">
                        <?php dynamic_sidebar( 'sidebar-1' ); ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <div class="bottom-section">
            <div class="find-more">
                <h2 class="section-title">You might also enjoy</h2>
                <?php
                $current_genres = wp_get_post_terms( get_the_ID(), 'book_genre', array( 'fields' => 'ids' ) );

                if ( ! empty( $current_genres ) ) {
                    $related_books = new WP_Query( array(
                        'post_type'      => 'book',
                        'posts_per_page' => 4,
                        'post__not_in'   => array( get_the_ID() ),
                        'tax_query'      => array(
                            array(
                                'taxonomy' => 'book_genre',
                                'field'    => 'term_id',
                                'terms'    => $current_genres,
                            ),
                        ),
                    ) );

                    $books = $related_books;
                    include plugin_dir_path(__FILE__) . 'sections/section-books-list.php';
                } else {
                    echo '<p>No genres assigned for this book.</p>';
                }
                ?>
            </div>
        </div>

    </div>
    
<?php endwhile; endif; ?>

<?php get_footer();
