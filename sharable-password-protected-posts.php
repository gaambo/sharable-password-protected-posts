<?php
/**
 * Plugin Name:       Private Post Share
 * Description:       Share password protected posts via secret URLs
 * Requires at least: 6.7
 * Requires PHP:      8.2
 * Version:           2.0.0
 * Author:            Fabian Todt
 * Author URI:        https://fabiantodt.at/
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       sharable-password-protected-posts
 * Domain Path:       /languages
 *
 * @package           Private_Post_Share
 */

namespace Private_Post_Share;

use Exception;
use WP_Post;
use WP_Query;

defined( 'ABSPATH' ) || exit;

require_once plugin_dir_path( __FILE__ ) . 'vendor/vendor-prefixed/autoload.php';
require_once plugin_dir_path( __FILE__ ) . 'vendor/autoload.php';


/**
 * Adds JS & CSS to editor
 *
 * @return void
 * @throws Exception If plugin assets are not built.
 */
function add_editor_assets(): void {
    $asset_file = plugin_dir_path( __FILE__ ) . 'build/index.asset.php';
    if ( ! file_exists( $asset_file ) ) {
        throw new Exception( 'You have to build the scripts before loading them' );
    }
    $assets_config = require $asset_file;

    $js_data = [
        'settings' => [
            'postTypes' => get_enabled_post_types(),
            'hasPermissions' => current_user_can( 'publish_posts' ),
        ],
        'newKey' => generate_key(),
    ];

    wp_enqueue_script(
        'private-post-share-editor',
        plugin_dir_url( __FILE__ ) . 'build/index.js',
        $assets_config['dependencies'],
        $assets_config['version']
    );

    wp_enqueue_style(
        'private-post-share-editor',
        plugin_dir_url( __FILE__ ) . 'build/index.css',
        [],
        $assets_config['version']
    );

    wp_add_inline_script( 'private-post-share-editor', 'window.privatePostShare = ' . json_encode( $js_data ) . ';', 'before' );
}
add_action( 'enqueue_block_editor_assets', __NAMESPACE__ . '\add_editor_assets' );

/**
 * Registers the two meta fields
 *
 * @return void
 */
function register_meta(): void {
    $enabled_meta_field = [
        // 'show_in_rest' => false, // Add a custom rest field to better check permissions.
        'show_in_rest' => [
            'schema' => [
                'context' => [ 'edit' ],
            ],
            'prepare_callback' => function ( $value, $request, $args ) {
                global $post;
                // WP_REST_Posts_Controller set global post instance.
                $post_id = $post ? $post->ID : $request['id'];

                $allowed = $post_id ? current_user_can( 'publish_posts', $post_id ) : current_user_can( 'publish_posts' );
                return $allowed ? $value : null;
            },
        ],
        'single' => true,
        'type' => 'boolean',
        'default' => false,
        'auth_callback' => function ( $allowed, $meta_key, $object_id, $user_id ) {
            if ( $user_id ) {
                return user_can( $user_id, 'publish_posts', $object_id );
            }
            return current_user_can( 'publish_posts', $object_id );
        },
        'sanitize_callback' => function ( $value ) {
            if ( $value === true || $value === 1 || $value === '1' || $value === 'true' ) {
                $value = true;
            } else {
                $value = false;
            }
            return $value;
        },
    ];

    $key_meta_field = [
        // 'show_in_rest' => false, // Add a custom rest field to better check permissions.
        'show_in_rest' => [
            'schema' => [
				'type' => 'string',
                'context' => [ 'edit' ],
            ],
            'prepare_callback' => function ( $value, $request, $args ) {
                global $post;
                // WP_REST_Posts_Controller set global post instance.
                $post_id = $post ? $post->ID : $request['id'];

                $allowed = $post_id ? current_user_can( 'publish_posts', $post_id ) : current_user_can( 'publish_posts' );
                return $allowed ? $value : null;
            },
        ],
        'single' => true,
        'type' => 'string',
        'default' => '',
        'auth_callback' => function ( $allowed, $meta_key, $object_id, $user_id ) {
            if ( $user_id ) {
                return user_can( $user_id, 'publish_posts', $object_id );
            }
            return current_user_can( 'publish_posts', $object_id );
        },
        'sanitize_callback' => function ( $value ) {
            $value = sanitize_text_field( trim( $value ) );
            if ( empty( $value ) ) {
                return generate_key();
            }
            return $value;
        },
    ];

    $post_types = get_enabled_post_types();

    foreach ( $post_types as $post_type ) {
        register_post_meta( $post_type, '_sppp_enabled', $enabled_meta_field );
        register_post_meta( $post_type, '_sppp_key', $key_meta_field );
    }
}
add_action( 'init', __NAMESPACE__ . '\register_meta' );

/**
 * Filters the main queries posts_results - for PRIVATE posts
 * Sets the post elements status to 'publish' if it can be viewed with the key
 * this will let the rest of WP_Query handle the post as normal
 *
 * This only filters the main query's WP_Post instance, not all other objects
 * that may be get via get_post; but using get_queried_object will work
 *
 * @param WP_Post[] $posts
 * @param WP_Query  $query
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

    $main_post = $posts[0];
    $enabled_post_types = get_enabled_post_types();

    // If the main (single) post has a not-enabled post type, bail early.
    if ( ! in_array( $main_post->post_type, $enabled_post_types ) ) {
        return $posts;
    }

    // If a post-type was specifically queried, check if only allowed/enabled post-types where queried.
    $post_types = $query->get( 'post_type' );
    if ( ! empty( $post_types ) && ( is_array( $post_types ) || is_string( $post_types ) ) ) {
        // If a post-type is queried (maybe one of multiples), that is not enabled bail early.
        if (
            ! empty( array_diff( (array) $post_types, $enabled_post_types ) )
        ) {
            return $posts;
        }
    }

    // Should only be 1 on singulars normally.
    foreach ( $posts as &$post ) {
        if ( can_post_be_viewed_with_key( $post ) ) {
            $post->post_status = 'publish';
        }
    }
    return $posts;
}
add_filter( 'posts_results', __NAMESPACE__ . '\filter_posts_query', 10, 2 );

/**
 * Filters whether a post-password form is required - for PASSWORD protected posts
 *
 * @param bool    $required
 * @param WP_Post $post
 * @return bool
 */
function filter_post_password_required( $required, $post ): bool {
    $enabled_post_types = get_enabled_post_types();

    // If the post has a not-enabled post type, bail early.
    if ( ! in_array( $post->post_type, $enabled_post_types ) ) {
        return $required;
    }

    if ( can_post_be_viewed_with_key( $post ) ) {
        return false;
    }
    return $required;
}
add_filter( 'post_password_required', __NAMESPACE__ . '\filter_post_password_required', 10, 2 );

/**
 * Removes the "Protected" from password protected/private posts
 * if they can be viewed with the current url key
 *
 * @param string      $prefix
 * @param WP_Post|int $post
 * @return string
 */
function filter_post_title_prefix( $prefix, $post ): string {
    if ( can_post_be_viewed_with_key( $post ) ) {
        return '%s';
    }
    return $prefix;
}
add_filter( 'protected_title_format', __NAMESPACE__ . '\filter_post_title_prefix', 10, 2 );
add_filter( 'private_title_format', __NAMESPACE__ . '\filter_post_title_prefix', 10, 2 );

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

    $is_key_valid = is_key_valid( $key, $post );

    /**
     * Filter whether a given post can be viewed with a given key
     *
     * @param bool    $is_key_valid
     * @param WP_Post $post
     * @param string  $key
     */
    return apply_filters( 'private_post_share/can_view', $is_key_valid, $post, $key );
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

function get_sharable_link( int $post_id ): ?string {
    if ( ! is_enabled_for_post( $post_id ) ) {
        return null;
    }
    $key = get_key_for_post( $post_id );
    return add_query_arg( '_sppp_key', $key, get_permalink( $post_id ) );
}

function is_enabled_for_post( int|WP_Post $post ): bool {
    $post_id = is_a( $post, 'WP_Post' ) ? $post->ID : $post;
    $is_enabled = get_post_meta( $post_id, '_sppp_enabled', true );
    return filter_var( $is_enabled, FILTER_VALIDATE_BOOLEAN );
}

function get_key_for_post( int|WP_Post $post ): ?string {
    $post_id = is_a( $post, 'WP_Post' ) ? $post->ID : $post;
    $value = get_post_meta( $post_id, '_sppp_key', true );
    return is_string( $value ) && ! empty( $value ) ? $value : null;
}
