<?php

namespace Private_Post_Share\Editor;

use Exception;
use function Private_Post_Share\generate_key;
use function Private_Post_Share\get_enabled_post_types;

/**
 * Adds JS & CSS to editor
 *
 * @return void
 * @throws Exception If plugin assets are not built.
 */
function add_editor_assets(): void {
    $asset_file = plugin_dir_path( PRIVATE_POST_SHARE_PLUGIN_FILE ) . 'build/index.asset.php';
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
        plugin_dir_url( PRIVATE_POST_SHARE_PLUGIN_FILE ) . 'build/index.js',
        $assets_config['dependencies'],
        $assets_config['version']
    );

    wp_enqueue_style(
        'private-post-share-editor',
        plugin_dir_url( PRIVATE_POST_SHARE_PLUGIN_FILE ) . 'build/index.css',
        [],
        $assets_config['version']
    );

    wp_add_inline_script( 'private-post-share-editor', 'window.privatePostShare = ' . json_encode( $js_data ) . ';', 'before' );
}
add_action( 'enqueue_block_editor_assets', __NAMESPACE__ . '\add_editor_assets' );
