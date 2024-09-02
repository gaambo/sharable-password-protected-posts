<?php

/**
 * Plugin Name:       Sharable Password Protected Posts
 * Description:       Share password protected posts via secret URLs
 * Requires at least: 6.0
 * Requires PHP:      7.4
 * Version:           1.1.0
 * Author:            Fabian Todt
 * Author URI:        https://fabiantodt.at/en/
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       sharable-password-protected-posts
 * Domain Path:       /languages
 *
 * @package           SPPP
 */

namespace SPPP;

/**
 * Adds JS & CSS to editor
 *
 * @return void
 */
function addEditorAssets()
{
    $assetFile = plugin_dir_path(__FILE__) . 'build/index.asset.php';
    if (!file_exists($assetFile)) {
        throw new \Exception('You have to build the scripts before loading them');
    }
    $assetsConfig = require($assetFile);

    $jsData = [
        'settings' => [
            'postTypes' => getEnabledPostTypes(),
            'hasPermissions' => current_user_can('publish_posts'),
        ],
        'newKey' => generateKey(),
    ];

    $scriptDependencies = $assetsConfig['dependencies'];
    // For WordPress < 6.6 the editor script depends on wp-edit-post (because of PluginPostStatusInfo)
    // but the script is built against 6.6, therefore only requiring the wp-editor script.
    require ABSPATH . WPINC . '/version.php';
    if(version_compare($wp_version, '6.6', '<')) {
        $scriptDependencies[] = 'wp-edit-post';
    }

    wp_enqueue_script(
        'sppp',
        plugin_dir_url(__FILE__) . 'build/index.js',
        $scriptDependencies,
        $assetsConfig['version']
    );

    wp_enqueue_style(
        'sppp',
        plugin_dir_url(__FILE__) . 'build/index.css',
        [],
        $assetsConfig['version']
    );

    wp_add_inline_script('sppp', 'window.sppp = ' . json_encode($jsData) . ';', 'before');
}
add_action('enqueue_block_editor_assets', __NAMESPACE__ . '\addEditorAssets');

function loadLanguages()
{
    load_plugin_textdomain(
        'sharable-password-protected-posts',
        false,
        dirname(plugin_dir_path(__FILE__)) . 'languages'
    );
}
add_action('init', __NAMESPACE__ . '\loadLanguages');

/**
 * Registers the two meta fields
 *
 * @return void
 */
function registerMeta()
{
    $enabledMetaField = [
        'show_in_rest' => true,
        'single' => true,
        'type' => 'boolean',
        'default' => false,
        'auth_callback' => function ($allowed, $meta_key, $objectId, $userId) {
            if ($userId) {
                return user_can($userId, 'publish_posts', $objectId);
            }
            return current_user_can('publish_posts', $objectId);
        },
        'sanitize_callback' => function ($value) {
            if ($value === true || $value === 1 || $value === '1' || $value === 'true') {
                $value = true;
            } else {
                $value = false;
            }
            return $value;
        }
    ];

    $keyMetaField = [
        'show_in_rest' => true,
        'single' => true,
        'type' => 'string',
        'default' => '',
        'auth_callback' => function ($allowed, $meta_key, $objectId, $userId) {
            if ($userId) {
                return user_can($userId, 'publish_posts', $objectId);
            }
            return current_user_can('publish_posts', $objectId);
        },
        'sanitize_callback' => function ($value) {
            $value = sanitize_text_field(trim($value));
            if (empty($value)) {
                return generateKey();
            }
            return $value;
        }
    ];

    $postTypes = getEnabledPostTypes();

    foreach ($postTypes as $postType) {
        register_post_meta($postType, '_sppp_enabled', $enabledMetaField);
        register_post_meta($postType, '_sppp_key', $keyMetaField);
    }
}
add_action('init', __NAMESPACE__ . '\registerMeta');

/**
 * Filters the main queries posts_results - for PRIVATE posts
 * Sets the post elements status to 'publish' if it can be viewed with the key
 * this will let the rest of WP_Query thandle the post as normal
 *
 * This only filters the main query's WP_Post instance, not all other objects
 * that may be get via get_post; but using get_queried_object will work
 *
 * @param \WP_Post[] $posts
 * @param \WP_Query $query
 * @return void
 */
function filterPostsQuery($posts, $query)
{
    // only handle the main query
    if (!$query->is_main_query()) {
        return $posts;
    }

    // only handle single views, don't handle not-found posts
    if (count($posts) !== 1) {
        return $posts;
    }

    $mainPost = $posts[0];
    $enabledPostTypes = getEnabledPostTypes();

    // if the main (single) post has a not-enabled post type, bail early
    if (!in_array($mainPost->post_type, $enabledPostTypes)) {
        return $posts;
    }

    // if a post type was specifically queried, check if only allowed/enabled post types where queried
    $postTypes = $query->get('post_type');
    if (!empty($postTypes) && (is_array($postTypes) || is_string($postTypes))) {
        // if a post type is queried (maybe one of multiple), that is not enabled bail early
        if (
            !empty($postTypes) &&
            !empty(array_diff((array)$postTypes, $enabledPostTypes))
        ) {
            return $posts;
        }
    }

    // should only be 1 on singulars normally
    foreach ($posts as &$post) {
        if (canPostBeViewedWithKey($post)) {
            $post->post_status = 'publish';
        }
    }
    return $posts;
}
add_filter('posts_results', __NAMESPACE__ . '\filterPostsQuery', 10, 2);

/**
 * Filters whether a post password form is required - for PASSWORD protected posts
 *
 * @param bool $required
 * @param \WP_Post $post
 * @return bool
 */
function filterPostPasswordRequired($required, $post)
{
    $enabledPostTypes = getEnabledPostTypes();

    // if the post has a not-enabled post type, bail early
    if (!in_array($post->post_type, $enabledPostTypes)) {
        return $required;
    }

    if (canPostBeViewedWithKey($post)) {
        return false;
    }
    return $required;
}
add_filter('post_password_required', __NAMESPACE__ . '\filterPostPasswordRequired', 10, 2);

/**
 * Removes the "Protected" from password protected/private posts
 * if they can be viewd with the current url key
 *
 * @param string $prefix
 * @param WP_Post|int $post
 * @return string
 */
function filterPostTitlePrefix($prefix, $post)
{
    if (canPostBeViewedWithKey($post)) {
        return '%s';
    }
    return $prefix;
}
add_filter('protected_title_format', __NAMESPACE__ . '\filterPostTitlePrefix', 10, 2);
add_filter('private_title_format', __NAMESPACE__ . '\filterPostTitlePrefix', 10, 2);

/**
 * Whether a given post can be viewed with a given key
 *
 * @param \WP_Post $post The post to check for
 * @param string $key A key to check against, defaults to the $_GET parameters
 * @return bool False if the post is not private/protected or if SPPP is not enabled;
 *              True if key can be used to view the private post
 */
function canPostBeViewedWithKey($post, $key = null)
{
    if (!$key) {
        if (empty($_GET['_sppp_key'])) {
            return false;
        }
        $key = sanitize_text_field($_GET['_sppp_key']);
    }

    if (empty($key)) {
        return false;
    }

    if (empty($post->post_password) && $post->post_status !== 'private') {
        return false;
    }

    $isKeyValid = isKeyValid($key, $post);

    if ($isKeyValid) {
        return true;
    }
    return false;
}

/**
 * Checks the given key by the user against the stored key
 *
 * @param string $userKey
 * @param WP_Post|int $post
 * @return bool False if key is not valid or SPPP is not enabled for this post
 */
function isKeyValid($userKey, $post)
{
    if (empty($userKey)) {
        return false;
    }

    $postId = is_a($post, 'WP_Post') ? $post->ID : $post;

    $isEnabled = get_post_meta($postId, '_sppp_enabled', true);
    if (!$isEnabled) {
        return false;
    }
    $savedKey = get_post_meta($postId, '_sppp_key', true);

    if (empty($savedKey)) {
        return false;
    }

    return $savedKey === $userKey;
}

/**
 * Get enabled post types for SPPP
 *
 * @return string[] name of post types
 */
function getEnabledPostTypes()
{
    /**
     * Allows filtering the post types for which SPPP is enabled
     * By default it's enabled for all public post types
     *
     * @param string[] $postTypes array of post type names (slugs)
     */
    return apply_filters('sppp/postTypes', array_keys(get_post_types([
        'public' => true,
    ], 'names')));
}

/**
 * Generate a new secret key
 *
 * @return string
 */
function generateKey()
{
    return wp_generate_password(15, false);
}
