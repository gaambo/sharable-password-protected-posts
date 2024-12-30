<?php

namespace SPPP\Posts;

use WP_Post;
use WP_Query;
use function SPPP\can_post_be_viewed_with_key;
use function SPPP\get_enabled_post_types;

/**
 * Removes the "Protected" from password protected/private posts
 * if they can be viewed with the current url key
 *
 * @param string      $prefix The original post titles prefix calculated by WP core.
 * @param WP_Post|int $post The post object.
 * @return string
 */
function filter_post_title_prefix( $prefix, $post ) {
    if ( can_post_be_viewed_with_key( $post ) ) {
        return '%s';
    }
    return $prefix;
}
add_filter( 'protected_title_format', __NAMESPACE__ . '\filter_post_title_prefix', 10, 2 );
add_filter( 'private_title_format', __NAMESPACE__ . '\filter_post_title_prefix', 10, 2 );

/**
 * Filters whether a post password form is required - for PASSWORD protected posts
 *
 * @param bool    $required Whether a post password is required.
 * @param WP_Post $post The post object.
 * @return bool
 */
function filter_post_password_required( $required, $post ) {
    $enabled_post_types = get_enabled_post_types();

    // If the post has a not-enabled post type, bail early.
    if ( ! in_array( $post->post_type, $enabled_post_types, true ) ) {
        return $required;
    }

    if ( can_post_be_viewed_with_key( $post ) ) {
        return false;
    }
    return $required;
}
add_filter( 'post_password_required', __NAMESPACE__ . '\filter_post_password_required', 10, 2 );

/**
 * Filters the main queries posts_results - for PRIVATE posts
 * Sets the post elements status to 'publish' if it can be viewed with the key
 * this will let the rest of WP_Query handle the post as normal
 *
 * This only filters the main query's WP_Post instance, not all other objects
 * that may be got via get_post; but using get_queried_object will work
 *
 * @param WP_Post[] $posts The posts found by the query.
 * @param WP_Query  $query The query object.
 * @return WP_Post[]
 */
function filter_posts_query( $posts, $query ): array {
    // Only handle the main query.
    if ( ! $query->is_main_query() ) {
        return $posts;
    }

    // Only handle single views, don't handle not-found posts.
    if ( count( $posts ) !== 1 ) {
        return $posts;
    }

    $main_post          = $posts[0];
    $enabled_post_types = get_enabled_post_types();

    // If the main (single) post has a not-enabled post type, bail early.
    if ( ! in_array( $main_post->post_type, $enabled_post_types, true ) ) {
        return $posts;
    }

    // If a post type was specifically queried, check if only allowed/enabled post types where queried.
    $post_types = $query->get( 'post_type' );
    if ( ! empty( $post_types ) && ( is_array( $post_types ) || is_string( $post_types ) ) ) {
        // If a post type is queried (maybe one of multiple), that is not enabled bail early.
        if ( ! empty( array_diff( (array) $post_types, $enabled_post_types ) ) ) {
            return $posts;
        }
    }

    // Should only be 1 on singulars normally.
    foreach ( $posts as $post ) {
        if ( can_post_be_viewed_with_key( $post ) ) {
            $post->post_status = 'publish';
        }
    }
    return $posts;
}
add_filter( 'posts_results', __NAMESPACE__ . '\filter_posts_query', 10, 2 );
