<?php

/**
 * @package nyr
 * @copyright (C) Copyright Bobbing Wide 2023
 *
 * Functionality for the nyr theme for NJ.Architecture
 */

add_action( 'after_setup_theme', 'nyr_after_setup_theme');
add_action( 'wp_enqueue_scripts', 'nyr_enqueue_styles' );
//add_action( 'init', 'nyr_after_setup_theme');
add_post_type_support( 'page', 'excerpt' );
add_filter( 'render_block_core/post-terms', 'nyr_render_block_core_post_terms', 10, 3 );
add_filter( 'render_block_areoi/column', 'nyr_render_block_areoi_column', 10, 3 );
add_filter( 'render_block_core/term-description', 'nyr_render_block_core_term_description', 10, 3);

// We can't disable layout styles since this prevents the flowing of the social links icons into a single line.
// add_theme_support( 'disable-layout-styles' );


function nyr_after_setup_theme() {
    add_shortcode( 'nyr_carousel', 'nyr_carousel');
    add_shortcode( 'nyr_carousel_images', 'nyr_carousel_images');
}

function nyr_enqueue_styles() {
    //$theme_version = wp_get_theme()->get( 'Version' );
    if ( defined( 'SCRIPT_DEBUG') && SCRIPT_DEBUG ) {
        $theme_version = filemtime( get_stylesheet_directory() . "/style.css" );
    } else {
        $theme_version = wp_get_theme()->get( 'Version' );
    }
    wp_enqueue_style( 'nyr', get_stylesheet_uri(), array(), $theme_version );
}

/**
 * Returns the posts to display in the dynamic-carousel.
 *
 * When it's the front-page we need to load all the posts.
 * When it is_posts_page we need to load the posts for the current query
 * @return int[]|WP_Post[]
 */

function nyr_get_posts( $attributes=null ) {

    if ( is_front_page() ) {
        //gob();
        //global $wp_query;
        //bw_trace2( $wp_query, 'wp_query' );
    }

    $args = [ "post_type" => "post",
              "numberposts" =>16 ];

    $posts = get_posts( $args );
    bw_trace2( $posts, "posts" );

    /*
    if ( have_posts() ) {
        while (have_posts()) {
            the_post();
            $post = get_post();
            $id = get_the_ID();
        }
    }
    */
    return $posts;
}

/**
 * Note: Inherits the main query.
 *
 * @param $attributes
 * @param $content
 * @param $tag
 * @return string
 */
function nyr_carousel( $attributes, $content, $tag ) {
    $html = "<!-- carousel goes here -->";
    $html .= nyr_carousel_start();
    $active = true;
    $posts = nyr_get_posts( $attributes );
    foreach ( $posts as $post ) {
        $html .= nyr_carousel_item( $post, $active );
        $active = false;
    }
    $html .= nyr_carousel_end();
    return $html;
}

/**
 * Starts a fading carousel
 *
 * Note: Time interval is currently hardcoded as 10 seconds.
 * @return string
 */
function nyr_carousel_start( $fade=true )  {
    if ( $fade ) {
        if ( wp_is_mobile() ) {
            $html = '<div id="carousel" class="carousel slide mt-5" data-bs-ride="carousel" data-bs-touch="true" data-bs-interval="10000" data-bs-theme="dark" >';
            $html .= nyr_carousel_slider_buttons();
        } else {
            $html = '<div class="carousel carousel-fade slide mt-5" data-bs-ride="carousel" data-bs-touch="true" data-bs-interval="10000">';
        }
    } else {
        $html = '<div class="carousel slide vh-70" data-bs-ride="carousel" data-bs-touch="true" data-bs-interval="2000">';
    }
    $html .= '<div class="carousel-inner">';
    return $html;
}

function nyr_carousel_slider_buttons() {
    $html = '<button class="carousel-control-prev" type="button" data-bs-target="#carousel" data-bs-slide="prev">
        <span class="slider-control">&lt;</span>
  </button>
  <button class="carousel-control-next" type="button" data-bs-target="#carousel" data-bs-slide="next">
        <span class="slider-control">&gt;</span>
  </button>';
    return $html;
}

function nyr_carousel_end() {

    $html = '</div>';
    $html .= '</div>';
    return $html;
}

function nyr_carousel_item( $post, $active=false ) {
    if ( $active ) {
        $html = '<div class="carousel-item active">';
    } else {
        $html = '<div class="carousel-item">';
    }
    $html .= '<section class="row gx-0 ">';
    $html .= nyr_format_item( $post );
    $html .= '</section>';
    $html .= '</div>';
    return $html;
}


/**
 * Formats the slider item.
 *
 * Uses bootstrap flex
 * https://getbootstrap.com/docs/5.2/utilities/flex/#enable-flex-behaviors
 * @param $post
 * @return string
 */
function nyr_format_item( $post ) {
    $html = '';
    $html .= '<div class="col-lg-7 offset-lg-1 vh-70">';
    $html .= nyr_format_featured_image( $post );

    $html .= '</div>';
    $html .= '<div class="col-lg-4 d-flex flex-column order-lg-first">';
    $html .= '<h2>' .  $post->post_title .  '</h2>';
    $html .= '<p class="tags has-medium-font-size">' . nyr_format_tags( $post ). '</p>';
    $html .= '<p class="excerpt mt-auto has-medium-font-size d-none d-md-flex">' . $post->post_excerpt . '</p>';
    $html .=  '</div>';

    return $html;
}

/**
 * Returns text for each tag.
 *
 * - Not displayed as links.
 * - Sorted to display location before year.
 * - Blank separator.
 * - No Prefix nor suffix
 *
 * eg where the tags are 'London SE1' and '2016'
 * London SE1 2016
 *
 * @param $post
 * @return false|string|WP_Error
 */
function nyr_format_tags( $post ) {
    $tags = get_the_tags( $post );
    //bw_trace2( $tags, "tags");
    $tags_array = [];
    if ( $tags ) {
        foreach ($tags as $tag) {
            $tags_array[] = $tag->name;
        }
    }
    arsort( $tags_array );
    $taglist = implode( ' ', $tags_array );
    return $taglist;
}

function nyr_format_featured_image( $post ) {
    $thumbnail = get_the_post_thumbnail( $post, 'full');
    return $thumbnail;
}

/**
 * Displays the attached images carousel
 *
 * @param $attributes
 * @param $content
 * @param $tag
 * @return string
 */
function nyr_carousel_images( $attributes, $content, $tag ) {
    $parent = get_the_ID();
    $post_thumbnail_id = get_post_thumbnail_id( $parent );
    $html = "<!-- image carousel goes here -->";
    $html .= nyr_carousel_start( false );
    $active = true;
    $html .= nyr_carousel_image( $parent, $active );
    $active = false;
    $posts = nyr_get_attached_images( $attributes );
    foreach ( $posts as $post ) {
        if ( $post->ID !== $post_thumbnail_id ) {
            $html .= nyr_carousel_image($post, $active);
        }

    }
    $html .= nyr_carousel_end();
    return $html;
}

/**
 * Loads the attached images for the post.
 *
 * This may include the featured image, which we hope will also the first image in the carousel.
 * @param $attributes
 * @return int[]|WP_Post[]
 */
function nyr_get_attached_images( $attributes=null ) {
   $parent = get_the_ID();
   $args = [ "post_type" => "attachment",
        "post_parent" => $parent,
        "numberposts" =>12,
       "orderby" => "post_title",
        "order" => "ASC"
   ];
    $posts = get_posts( $args );
    bw_trace2( $posts, "posts" );
    return $posts;
}

/**
 * Formats the image to be displayed.
 *
 * Notes: Images are expected to be 800px x 715px
 *
 * @param $post
 * @return string
 */
function nyr_format_image( $post ) {
    $post_thumbnail_id = $post->ID;
    $size = 'full';
    $attr = [];
    $html = wp_get_attachment_image( $post_thumbnail_id, $size, false, $attr );
    return $html;
}

/**
 * Displays a carousel image item.
 *
 * @param $post
 * @param $active
 * @return string
 */
function nyr_carousel_image( $post, $active=false ) {
    if ( $active ) {
        $html = '<div class="carousel-item active">';
    } else {
        $html = '<div class="carousel-item">';
    }
    $html .= '<section class="row gx-0 ">';
    if ( $active ) {
        $html .= nyr_format_featured_image( $post );
    } else {
        $html .= nyr_format_image($post);
    }
    $html .= '</section>';
    $html .= '</div>';
    return $html;
}

/**
 * Renders tags without links and sorted.
 *
 * @param $content
 * @param $parsed_block
 * @param $block
 * @return mixed|string
 */
function nyr_render_block_core_post_terms( $content, $parsed_block, $block ) {
    $attributes = $block->attributes;
    /**
     * If there aren't any already don't try again.
     */
    if ( '' === $content ) {
        return $content;
    }
    if ( ! isset( $block->context['postId'] ) || ! isset( $attributes['term'] ) ) {
        return 'Error: No postId in context';
    }
    $content = '<p class="tags has-medium-font-size">' . nyr_format_tags( $block->context['postId'] ). '</p>';
    return $content;
}

function nyr_render_block_areoi_column( $content, $parsed_block, $block ) {
    $content = str_replace( 'col areoi-element', 'col-12 areoi-element', $content );
    return $content;
}

/**
 * Renders the default category description on the home page.
 *
 * @param $content
 * @param $parsed_block
 * @param $block
 * @return mixed|string
 */
function nyr_render_block_core_term_description( $content, $parsed_block, $block ) {
     if ( '' === $content && is_home() ) {
        $term_description = term_description( get_option( 'default_category', 0 ) );
        $content = '<div style="margin-top:var(--wp--preset--spacing--60);" class="wp-block-term-description">';
        $content .= $term_description;
        $content .= '</div>';
    }
    return $content;
}


if ( !function_exists( 'bw_trace2')) {
    function bw_trace2($value = null, $text = null, $show_args = true, $level = 0) {
        return $value;
    }
}