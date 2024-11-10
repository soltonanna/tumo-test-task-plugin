<?php
/*
Plugin Name: Custom Book Plugin
Description: Adds a "Book" custom post type with custom fields and taxonomy.
Version: 1.0.0
Author: Anahit Sultanova
*/

if ( ! defined( 'ABSPATH' ) ) exit;

class Custom_Book_Plugin {
    
    public function __construct() {
        add_action( 'init', [ $this, 'register_book_post_type' ] );
        add_action( 'init', [ $this, 'register_book_genre_taxonomy' ] );
        add_action( 'add_meta_boxes', [ $this, 'add_book_meta_box' ] );
        add_action( 'save_post', [ $this, 'save_book_meta_box_data' ] );
        add_action( 'widgets_init', [ $this, 'books_plugin_widgets_init' ] );

        add_shortcode( 'book_list', [ $this, 'display_books_shortcode' ] );

        add_filter( 'single_template', [ $this, 'custom_single_template' ] );
    }


    /**** 
     * Register "Book" Custom Post Type 
     * 
    */
    public function register_book_post_type() {
        $args = [
            'labels' => [
                'name' => 'Books',
                'singular_name' => 'Book',
            ],
            'public' => true,
            'has_archive' => true,
            'supports' => [ 
                'title', 
                'editor', 
                'thumbnail',
            ],
        ];
        register_post_type( 'book', $args );
    }


    /****
     * Register "Book Genre" Taxonomy
     * 
    */
    public function register_book_genre_taxonomy() {
        register_taxonomy( 'book_genre', 'book', [
            'label' => 'Book Genres',
            'hierarchical' => true,
            'show_in_rest' => true,
        ] );
    }


    /**** 
     * Add Meta Box for Custom Fields 
     * 
    */
    public function add_book_meta_box() {
        add_meta_box( 
            'book_meta_box', 
            'Book Details', 
            [ 
                $this, 
                'book_meta_box_callback' 
            ], 
            'book' 
        );
    }


    /**** 
     * Meta Box Callback Function 
     * 
    */
    public function book_meta_box_callback( $post ) {
        wp_nonce_field( 'save_book_meta_box_data', 'book_meta_box_nonce' );

        $fields = [ 
            'author', 
            'publication_year', 
            'isbn' 
        ];
        
        foreach ($fields as $field) {
            $value = get_post_meta( $post->ID, '_book_' . $field, true );
            echo '<p><label for="' . $field . '">' . ucfirst(str_replace('_', ' ', $field)) . ':</label>';
            echo '<input type="text" id="' . $field . '" name="' . $field . '" value="' . esc_attr( $value ) . '" /></p>';
        }
    }


    /****
     * Save Meta Box Data 
     * 
    */
    public function save_book_meta_box_data( $post_id ) {
        if ( ! isset( $_POST['book_meta_box_nonce'] ) || ! wp_verify_nonce( $_POST['book_meta_box_nonce'], 'save_book_meta_box_data' ) ) return;

        $fields = [ 
            'author', 
            'publication_year', 
            'isbn' 
        ];
        
        foreach ($fields as $field) {
            if ( isset( $_POST[$field] ) ) {
                update_post_meta( $post_id, '_book_' . $field, sanitize_text_field( $_POST[$field] ) );
            }
        }
    }


    /**** 
     * Short-code to Display Books List 
     * 
    */
    public function display_books_shortcode($atts) {
        $atts = shortcode_atts([
            'posts_per_page' => 8,
        ], $atts, 'book_list');

        $output = '<h2>Books</h2>';
        
        $paged = (get_query_var('paged')) ? get_query_var('paged') : 1;
        $books = new WP_Query([
            'post_type'      => 'book',
            'posts_per_page' => intval($atts['posts_per_page']),
            'paged'          => $paged,
        ]);

        ob_start();
        
        include plugin_dir_path(__FILE__) . 'sections/section-books-list.php';
    
        $output .= ob_get_clean();
        $output .= '<div class="pagination">';
        $big = 999999999;
        $output .= paginate_links([
            'base'      => str_replace($big, '%#%', esc_url(get_pagenum_link($big))),
            'format'    => '?paged=%#%',
            'current'   => max(1, $paged),
            'total'     => $books->max_num_pages,
            'prev_text' => '<span class="prev"></span>',
            'next_text' => '<span class="next"></span>',
        ]);
        $output .= '</div>';
        
        return $output;
    }
    
    
    /**** 
     * Custom Single Template for "Book" Post Type 
     * 
    */
    public function custom_single_template( $single_template ) {
        global $post;
    
        if ( $post->post_type === 'book' ) {
            $plugin_template = plugin_dir_path( __FILE__ ) . 'single-book.php';
            if ( file_exists( $plugin_template ) ) {
                return $plugin_template;
            }
        }
    
        return $single_template;
    }

    /**** 
     * Register the "Books Sidebar" for the plugin
     * 
    */
    public function books_plugin_widgets_init() {
        register_sidebar( array(
            'name'          => 'Books Sidebar',
            'id'            => 'sidebar-1',
            'before_widget' => '<div class="widget %2$s">',
            'after_widget'  => '</div>',
            'before_title'  => '<h3 class="widget-title">',
            'after_title'   => '</h3>',
        ) );
    }
}

new Custom_Book_Plugin();


/***** 
 * Include the widget class file
 * 
*/
require_once plugin_dir_path( __FILE__ ) . 'includes/related-books-widget.php';

if ( ! function_exists( 'register_related_books_widget' ) ) {
    function register_related_books_widget() {
        register_widget( 'Related_Books_Widget' );
    }
}
add_action( 'widgets_init', 'register_related_books_widget' );

/**** 
 * Enqueue CSS files 
 * 
*/
function custom_book_plugin_enqueue_assets() {
    wp_enqueue_style(
        'custom-book-plugin-style',
        plugin_dir_url( __FILE__ ) . 'dist/css/styles.min.css',
        [],
        filemtime( plugin_dir_path( __FILE__ ) . 'dist/css/styles.min.css' ),
        'all'
    );
}
add_action( 'wp_enqueue_scripts', 'custom_book_plugin_enqueue_assets' );

function enqueue_admin_styles() {
    wp_enqueue_style( 
        'book-meta-box-admin-style', 
        plugin_dir_url( __FILE__ ) . 'styles/admin-style.css' 
    );
}
add_action( 'admin_enqueue_scripts', 'enqueue_admin_styles' );
