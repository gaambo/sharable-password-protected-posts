<?php

namespace SPPP\Editor;

use function SPPP\generate_key;
use function SPPP\get_enabled_post_types;

/**
 * Adds JS & CSS to editor.
 *
 * @global string $wp_version Included from $WPINC/version.php
 * @return void
 */
function add_editor_assets(): void {
    $asset_file = plugin_dir_path( SPPP_PLUGIN_FILE ) . 'build/index.asset.php';
    assert( file_exists( $asset_file ) );

    $assets_config = require $asset_file;

    $js_data = [
        'settings' => [
            'postTypes'      => get_enabled_post_types(),
            'hasPermissions' => current_user_can( 'publish_posts' ),
        ],
        'newKey'   => generate_key(),
    ];

    $script_dependencies = $assets_config['dependencies'];
    // For WordPress < 6.6 the editor script depends on wp-edit-post (because of PluginPostStatusInfo)
    // but the script is built against 6.6, therefore only requiring the wp-editor script.
    require ABSPATH . WPINC . '/version.php';
    /**
     * Included from $WPINC/version.php
     *
     * @var string $wp_version
     */
    if ( version_compare( $wp_version, '6.6', '<' ) ) {
        $script_dependencies[] = 'wp-edit-post';
    }

    wp_enqueue_script(
        'sppp',
        plugin_dir_url( SPPP_PLUGIN_FILE ) . 'build/index.js',
        $script_dependencies,
        $assets_config['version'],
        true
    );

    wp_enqueue_style(
        'sppp',
        plugin_dir_url( SPPP_PLUGIN_FILE ) . 'build/index.css',
        [],
        $assets_config['version']
    );

    wp_add_inline_script( 'sppp', 'window.sppp = ' . wp_json_encode( $js_data ) . ';', 'before' );
}
add_action( 'enqueue_block_editor_assets', __NAMESPACE__ . '\add_editor_assets' );
