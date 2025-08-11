<?php

namespace Private_Post_Share\Meta;

use function Private_Post_Share\get_enabled_post_types;

/**
 * Registers the two meta fields
 *
 * @return void
 */
function register_meta(): void {
    $enabled_meta_field = [
        'show_in_rest' => [
            'schema' => [
                'context' => [ 'edit' ], // Only show when editing.
            ],
            // Additionally, hide value when user is not allowed to edit the post.
            'prepare_callback' => function ( $value, $request, $args ) {
                global $post;
                // WP_REST_Posts_Controller set global post instance.
                $post_id = $post ? $post->ID : $request['id'];

                $allowed = $post_id ? current_user_can( 'edit_post', $post_id ) : current_user_can( 'edit_posts' );
                return $allowed ? filter_var( $value, FILTER_VALIDATE_BOOLEAN ) : null;
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
            return filter_var( $value, FILTER_VALIDATE_BOOLEAN );
        },
    ];

    $key_meta_field = [
        'show_in_rest' => [
            'schema' => [
                'context' => [ 'edit' ], // Only show when editing.
            ],
            // Additionally, hide value when user is not allowed to edit the post.
            'prepare_callback' => function ( $value, $request, $args ) {
                global $post;
                // WP_REST_Posts_Controller set global post instance.
                $post_id = $post ? $post->ID : $request['id'];

                $allowed = $post_id ? current_user_can( 'edit_post', $post_id ) : current_user_can( 'edit_posts' );
                return $allowed ? (string) $value : null;
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
            // Do not generate a key here, just validate.
            // Because if it was disabled, the key would be empty from the library
            // And we want to keep that.
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
