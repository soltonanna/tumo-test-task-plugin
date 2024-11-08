<?php
class Related_Books_Widget extends WP_Widget {
    function __construct() {
        parent::__construct(
            'related_books_widget',
            'Related Books',
            array( 'description' => 'Displays related books by genre or latest added.' )
        );
    }

    public function widget( $args, $instance ) {
        echo $args['before_widget'];

        if ( ! empty( $instance['title'] ) ) {
            echo $args['before_title'] . apply_filters( 'widget_title', $instance['title'] ) . $args['after_title'];
        }

        $num_books = ! empty( $instance['num_books'] ) ? $instance['num_books'] : 3;
        $show_by = ! empty( $instance['show_by'] ) ? $instance['show_by'] : 'latest';

        if ( $show_by === 'genre' ) {
            if ( is_singular( 'book' ) ) {
                $current_genres = wp_get_post_terms( get_the_ID(), 'book_genre', array( 'fields' => 'ids' ) );
            }

            $related_books = new WP_Query( array(
                'post_type'      => 'book',
                'posts_per_page' => $num_books,
                'post__not_in'   => array( get_the_ID() ),
                'tax_query'      => array(
                    array(
                        'taxonomy' => 'book_genre',
                        'field'    => 'term_id',
                        'terms'    => $current_genres,
                    ),
                ),
            ) );
        } else {
            $related_books = new WP_Query( array(
                'post_type'      => 'book',
                'posts_per_page' => $num_books,
                'orderby'        => 'date',
                'order'          => 'DESC',
            ) );
        }

        if ( $related_books->have_posts() ) : ?>
            <ul class="related-books-list">
                <?php while ( $related_books->have_posts() ) : $related_books->the_post(); ?>
                    <li class="related-book-item">
                        <div class="book-thumbnail">
                            <?php if ( has_post_thumbnail() ) : ?>
                                <?php the_post_thumbnail( 'full' ); ?>
                            <?php else : ?>
                                <img src="<?php echo plugin_dir_url( __FILE__ ) . '../images/no-book.jpg'; ?>" alt="No image available">
                            <?php endif; ?>
                        </div>
                        <div class="book-details">
                            <h4 class="book-title">
                                <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
                            </h4>
                            <?php
                            $author = get_post_meta( get_the_ID(), '_book_author', true );
                            if ( $author ) :
                                echo '<p class="book-author">by ' . esc_html( $author ) . '</p>';
                            endif;
                            ?>
                            <a href="<?php the_permalink(); ?>" class="more-button">more</a>
                        </div>
                    </li>
                <?php endwhile; ?>
            </ul>
        <?php else : ?>
            <p>No related books found.</p>
        <?php endif;

        wp_reset_postdata();

        echo $args['after_widget'];
    }

    public function form( $instance ) {
        $title = ! empty( $instance['title'] ) ? $instance['title'] : 'Related Books';
        $num_books = ! empty( $instance['num_books'] ) ? $instance['num_books'] : 5;
        $show_by = ! empty( $instance['show_by'] ) ? $instance['show_by'] : 'latest'; // 'genre' or 'latest'
        ?>
        <p>
            <label for="<?php echo $this->get_field_id( 'title' ); ?>">Title:</label>
            <input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>" />
        </p>
        <p>
            <label for="<?php echo $this->get_field_id( 'num_books' ); ?>">Number of books to show:</label>
            <input class="widefat" id="<?php echo $this->get_field_id( 'num_books' ); ?>" name="<?php echo $this->get_field_name( 'num_books' ); ?>" type="number" value="<?php echo esc_attr( $num_books ); ?>" min="1" max="10" />
        </p>
        <p>
            <label for="<?php echo $this->get_field_id( 'show_by' ); ?>">Show books by:</label>
            <select class="widefat" id="<?php echo $this->get_field_id( 'show_by' ); ?>" name="<?php echo $this->get_field_name( 'show_by' ); ?>">
                <option value="latest" <?php selected( $show_by, 'latest' ); ?>>Latest Added</option>
                <option value="genre" <?php selected( $show_by, 'genre' ); ?>>Same Genre as Current Book</option>
            </select>
        </p>
        <?php
    }

    public function update( $new_instance, $old_instance ) {
        $instance = array();
        $instance['title'] = ! empty( $new_instance['title'] ) ? strip_tags( $new_instance['title'] ) : '';
        $instance['num_books'] = ! empty( $new_instance['num_books'] ) ? absint( $new_instance['num_books'] ) : 5;
        $instance['show_by'] = ! empty( $new_instance['show_by'] ) ? strip_tags( $new_instance['show_by'] ) : 'latest';
        return $instance;
    }
}
