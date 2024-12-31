<?php
/**
 * Plugin Name:       Sharable Password Protected Posts
 * Description:       Share password protected posts via secret URLs
 * Requires at least: 6.5
 * Requires PHP:      8.1
 * Version:           1.1.0
 * Author:            Fabian Todt
 * Author URI:        https://fabiantodt.at/
 * License:           GPL-3.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       sharable-password-protected-posts
 * Domain Path:       /languages
 *
 * @package           SPPP
 */

namespace SPPP;

define( 'SPPP_PLUGIN_FILE', __FILE__ );

require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/editor.php';
require_once __DIR__ . '/includes/filters.php';

/**
 * Loads language files.
 *
 * @return void
 */
function load_languages(): void {
    load_plugin_textdomain(
        'sharable-password-protected-posts',
        false,
        dirname( plugin_dir_path( __FILE__ ) ) . 'languages'
    );
}
add_action( 'init', __NAMESPACE__ . '\load_languages' );

/**
 * Registers the two meta fields.
 *
 * @return void
 */
function register_meta(): void {
    $enabled_meta_field = [
        'show_in_rest'      => true,
        'single'            => true,
        'type'              => 'boolean',
        'default'           => false,
        'auth_callback'     => function ( $allowed, $meta_key, $object_id, $user_id ) {
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
        'show_in_rest'      => true,
        'single'            => true,
        'type'              => 'string',
        'default'           => '',
        'auth_callback'     => function ( $allowed, $meta_key, $object_id, $user_id ) {
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
