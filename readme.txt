=== Sharable Password Protected Posts ===
Contributors:      gaambo
Tags:              password protected, secret links, share private
Requires at least: 6.0
Tested up to:      6.3
Stable tag:        1.0.2
License:           GPL-2.0-or-later
License URI:       https://www.gnu.org/licenses/gpl-2.0.html

Share password protected posts via secret URLs

== Description ==

Share a link to anonymous users to view private and password protected posts (or any other public post type).

This plugin generates secret URLs (similar to Google Docs and other cloud services) for posts so you can share them with not-logged in users without having to share an extra password with them.

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

By default it's enabled for all public post types, but that can be changed via the `sppp/postTypes` filter.

== Frequently Asked Questions ==

= I can’t find the option for to share the URL. Where is it?  =

The checkbox is only available for private or password protected posts. Please make sure the status is set to "Private" or "Password protected" and you have entered a password.

= Is it available for classic editor? =

No, the plugin only works in the new block editor.

= Which permissions do I need? =

The editor has to have the `publish_posts` capability.

= Can I use it for custom post types? =

Yes, by default all public post types are enabled. You can filter them with the `sppp/postTypes` filter.

== Changelog ==

= 1.0.2 (2022-11-02): =
* Update plugin file versions and author uri

= 1.0.1 (2022-11-02): =
* Fix source code repo and deployment to WordPress.org plugin repo

= 1.0.0 (2022-11-02): =
* The first public release of the plugin 🥳

For more see [CHANGELOG.md](https://github.com/gaambo/sharable-password-protected-posts/blob/main/CHANGELOG.md).
