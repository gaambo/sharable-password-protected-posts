<?php

namespace SPPP;

use WP_Post;

/**
 * Get enabled post types for SPPP
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

    $post_types = apply_filters_deprecated( 'sppp/postTypes', [ $post_types ], '2.0.0', 'sppp/post_types' );

    /**
     * Allows filtering the post types for which SPPP is enabled
     * By default it's enabled for all public post types
     *
     * @param string[] $post_types array of post type names (slugs)
     */
    return apply_filters( 'sppp/post_types', $post_types );
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
 * Checks the given key by the user against the stored key.
 *
 * @param string      $user_key The key provided by the user/request.
 * @param int|WP_Post $post The post to check for.
 * @return bool False if key is not valid or SPPP is not enabled for this post
 */
function is_key_valid( string $user_key, int|WP_Post $post ): bool {
    if ( empty( $user_key ) ) {
        return false;
    }

    $post_id = is_a( $post, 'WP_Post' ) ? $post->ID : $post;

    $is_enabled = get_post_meta( $post_id, '_sppp_enabled', true );
    if ( ! $is_enabled ) {
        return false;
    }
    $saved_key = get_post_meta( $post_id, '_sppp_key', true );

    if ( empty( $saved_key ) ) {
        return false;
    }

    return $saved_key === $user_key;
}

/**
 * Whether a given post can be viewed with a given key
 *
 * @param WP_Post     $post The post to check for.
 * @param string|null $key A key to check against, defaults to the $_GET parameters.
 * @return bool False if the post is not private/protected or if SPPP is not enabled;
 *              True if key can be used to view the private post
 */
function can_post_be_viewed_with_key( WP_Post $post, ?string $key = null ): bool {
    if ( ! $key ) {
        // phpcs:ignore WordPress.Security.NonceVerification.Recommended, WordPress.Security.ValidatedSanitizedInput.MissingUnslash
        if ( empty( $_GET['_sppp_key'] ) ) {
            return false;
        }
        // phpcs:ignore WordPress.Security.NonceVerification.Recommended, WordPress.Security.ValidatedSanitizedInput.MissingUnslash
        $key = sanitize_text_field( $_GET['_sppp_key'] );
    }

    if ( empty( $key ) ) {
        return false;
    }

    if ( empty( $post->post_password ) && $post->post_status !== 'private' ) {
        return false;
    }

    $is_key_valid = is_key_valid( $key, $post );

    if ( $is_key_valid ) {
        return true;
    }
    return false;
}
