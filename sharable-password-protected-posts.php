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

defined( 'ABSPATH' ) || exit;

define( 'PRIVATE_POST_SHARE_PLUGIN_FILE', __FILE__ );
define( 'PRIVATE_POST_SHARE_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );

require_once PRIVATE_POST_SHARE_PLUGIN_DIR . 'vendor/vendor-prefixed/autoload.php';
require_once PRIVATE_POST_SHARE_PLUGIN_DIR . 'vendor/autoload.php';

require_once PRIVATE_POST_SHARE_PLUGIN_DIR . 'includes/functions.php';
require_once PRIVATE_POST_SHARE_PLUGIN_DIR . 'includes/editor.php';
require_once PRIVATE_POST_SHARE_PLUGIN_DIR . 'includes/meta.php';
require_once PRIVATE_POST_SHARE_PLUGIN_DIR . 'includes/posts.php';
