<?php

namespace Private_Post_Share;

use WP_Post;

/**
 * Get enabled post types for Private Post Share.
 *
 * @return string[] name of post types
 */
function get_enabled_post_types(): array {
    $post_types = array_keys(
        get_post_types(
            [
                'public' => true,
            ],
            'names'
        )
    );

    /**
     * Allows filtering the post types for which Private Post Share is enabled
     * By default it's enabled for all public post types
     *
     * @deprecated 2.0.0
     *
     * @param string[] $post_types Array of post type names (slugs).
     */
    $post_types = apply_filters_deprecated( 'sppp/postTypes', [ $post_types ], '2.0.0', 'private-post-share/post_types' );

    /**
     * Allows filtering the post types for which Private Post Share is enabled
     * By default it's enabled for all public post types
     *
     * @since 2.0.0
     *
     * @param string[] $post_types Array of post type names (slugs).
     */
    return apply_filters( 'private_post_share/post_types', $post_types );
}

/**
 * Generate a new secret key
 *
 * @return string
 */
function generate_key(): string {
    return wp_generate_password( 15, false );
}

/**
 * Checks the given key by the user against the stored key
 *
 * @param string      $user_key
 * @param WP_Post|int $post
 * @return bool False if key is not valid or SPPP is not enabled for this post
 */
function is_key_valid( $user_key, $post ): bool {
    if ( empty( $user_key ) ) {
        return false;
    }
    if ( ! is_enabled_for_post( $post ) ) {
        return false;
    }
    $saved_key = get_key_for_post( $post );

    if ( empty( $saved_key ) ) {
        return false;
    }

    return $saved_key === $user_key;
}

/**
 * Whether a given post can be viewed with a given key
 *
 * @param WP_Post $post The post to check for.
 * @param string  $key A key to check against, defaults to the $_GET parameters.
 * @return bool False if the post is not private/protected or if Private Post Share is not enabled;
 *              True if key can be used to view the private post
 */
function can_post_be_viewed_with_key( $post, $key = null ): bool {
    if ( ! $key ) {
        if ( empty( $_GET['_sppp_key'] ) ) {
            return false;
        }
        $key = sanitize_text_field( $_GET['_sppp_key'] );
    }

    if ( empty( $key ) ) {
        return false;
    }

    if ( empty( $post->post_password ) && $post->post_status !== 'private' ) {
        return false;
    }

    $is_key_valid = \Private_Post_Share\is_key_valid( $key, $post );

    /**
     * Filter whether a given post can be viewed with a given key
     *
     * @param bool    $is_key_valid
     * @param WP_Post $post
     * @param string  $key
     */
    return apply_filters( 'private_post_share/can_view', $is_key_valid, $post, $key );
}

function get_sharable_link( int $post_id ): ?string {
    if ( ! is_enabled_for_post( $post_id ) ) {
        return null;
    }
    $key = get_key_for_post( $post_id );
    return add_query_arg( '_sppp_key', $key, get_permalink( $post_id ) );
}

function is_enabled_for_post( int|WP_Post $post ): bool {
    $post_id = is_a( $post, 'WP_Post' ) ? $post->ID : $post;
    $meta_value = get_post_meta( $post_id, '_sppp_enabled', true );
    return filter_var( $meta_value, FILTER_VALIDATE_BOOLEAN );
}

function get_key_for_post( int|WP_Post $post ): ?string {
    $post_id = is_a( $post, 'WP_Post' ) ? $post->ID : $post;
    $meta_value = get_post_meta( $post_id, '_sppp_key', true );

    return is_string( $meta_value ) && ! empty( $meta_value ) ? $meta_value : null;
}
