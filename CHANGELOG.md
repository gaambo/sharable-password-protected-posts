## 2.0.0 (2025-06-10): New Name & Structure

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

## 1.1.1 (2025-06-11):

* Security: Secret Key could be exposed via REST API for password protected posts. Thanks to WPScan for the report and
  disclosure.

## 1.1.0 (2024-09-02):

* Add WordPress 6.6 compatibility

## 1.0.2 (2022-11-02):

* Update plugin file versions and author uri

## 1.0.1 (2022-11-02):

* Fix source code repo and deployment to WordPress.org plugin repo

## 1.0.0 (2022-11-02):

* The first public release of the plugin 🥳
