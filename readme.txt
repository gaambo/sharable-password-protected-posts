=== Sharable Password Protected Posts ===
Contributors:      gaambo
Tags:              password protected, secret links, share private
Requires at least: 6.7
Tested up to:      6.8
Stable tag:        2.0.0
License:           GPL-2.0-or-later
License URI:       https://www.gnu.org/licenses/gpl-2.0.html

Share password protected posts via secret URLs

== Description ==

Share a link to anonymous users to view private and password protected posts (or any other public post type).

This plugin generates secret URLs (similar to Google Docs and other cloud services) for posts so you can share them with not-logged in users without having to share an extra password with them.

For bug reports, security vulnerabilities, feature requests please visit the [GitHub repository](https://github.com/gaambo/sharable-password-protected-posts).

== Installation ==

Note: There will be NO settings page.

For an automatic installation through WordPress:

1. Go to the 'Add New' plugins screen in your WordPress admin area
1. Search for 'Public Post Preview'
1. Click 'Install Now' and activate the plugin


For a manual installation via FTP:

1. Upload the `public-post-preview` directory to the `/wp-content/plugins/` directory
1. Activate the plugin through the 'Plugins' screen in your WordPress admin area


To upload the plugin through WordPress, instead of FTP:

1. Upload the downloaded zip file on the 'Add New' plugins screen (see the 'Upload' tab) in your WordPress admin area and activate.

== Installation ==

This section describes how to install the plugin and get it working.

e.g.

1. Upload the plugin files to the `/wp-content/plugins/sharable-password-protected-posts` directory, or install the plugin through the WordPress plugins screen directly.
1. Activate the plugin through the 'Plugins' screen in WordPress

== Screenshots ==

1. Edit Posts Page

== Usage ==

- To enable a sharable url check the box below the edit post box on a private or password protected post
- The link will be displayed if the checkbox is checked, just copy and share the link.
- To disable just uncheck the box.

By default, it's enabled for all public post types, but that can be changed via the `private_post_share/post_types` filter.

== Frequently Asked Questions ==

= I can’t find the option for to share the URL. Where is it?  =

The checkbox is only available for private or password protected posts. Please make sure the status is set to "Private" or "Password protected" and you have entered a password.

= Is it available for classic editor? =

No, the plugin only works in the new block editor.

= Which permissions do I need? =

The editor has to have the `publish_posts` capability.

= Can I use it for custom post types? =

Yes, by default all public post types are enabled. You can filter them with the `private_post_share/post_types` filter.

== Changelog ==

= 2.0.0 (2025-06-10): New Name & Structure =
This is a major release with breaking changes.
These breaking changes are only important for developers extending this plugin or using the plugins functions.
**If you are just using the plugin, you are fine and nothing will change for you.**

The old plugins name was a mouth full, so we changed it to better reflect what it does.
The old name was `Sharable Password Protected Posts` and the new name is `Private Post Share`.

* Breaking: New plugin structure and namespace
    * The plugin has been moved to a new namespace called `Private_Post_Share`
    * All hooks are prefixed with `private_post_share/`
    * Meta keys and query var stay the same `_spp` prefix for backwards compatibility
    * Plugin slug and textdomain stays `sharable-password-protected-posts` (for WP.org repository)
* Breaking: Update required PHP version to 8.1 and WP 6.7
* Dev: Add code quality tools and GitHub Actions for them
* Dev: Add automated tests via Codeception

= 1.1.1 (2025-06-11): =
* Security: Secret Key could be exposed via REST API for password protected posts. Thanks to WPScan for the report and disclosure.

= 1.1.0 (2024-09-02): =
* Add WordPress 6.6 compatibility

= 1.0.2 (2022-11-02): =
* Update plugin file versions and author uri

= 1.0.1 (2022-11-02): =
* Fix source code repo and deployment to WordPress.org plugin repo

= 1.0.0 (2022-11-02): =
* The first public release of the plugin 🥳

For more see [CHANGELOG.md](https://github.com/gaambo/sharable-password-protected-posts/blob/main/CHANGELOG.md).
