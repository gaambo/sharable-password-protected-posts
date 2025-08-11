# Private Post Share

**Previous Name: Sharable Password Protected Posts**

WordPress plugin to share a link to anonymous users to view private and password protected posts (or any other public
post type).

This plugin generates secret URLs (similar to Google Docs and other cloud services) for posts so you can share them with
not-logged-in users without having to share an extra password with them.

![Screenshot of the Posts Edit Page](.wordpress-org/screenshot-1.png)

Inspired by [Public Post Preview](https://github.com/ocean90/public-post-preview)

[WordPress Plugin Repository](https://wordpress.org/plugins/sharable-password-protected-posts/)

## Contributing

Contributions are welcome. I just want to note, that the scope of this plugin is explicitly limited and I am not
planning on extending it with a lot of features.
Quality of live improvements, bug fixes, security fixes and code-quality improvements/tests are of course welcome.

### Local Development Environment

The local development environment is based on [DDEV](https://ddev.readthedocs.io/en/latest/).

After the first setup, remove the `##ddev-generated` header from the wp-config.php file and change the ddev-include code
to

```php
$ddev_settings = __DIR__ . '/wp-config-ddev.php';
if ( getenv( 'IS_DDEV_PROJECT' ) == 'true' && is_readable( $ddev_settings ) ) {
	require_once( $ddev_settings );
}
```

It is recommended to run all tasks inside the ddev container.

- PHP Code Style can be checked/fix with `composer run phpcs` / `composer run phpcs:fix
- PHPStan can be checked with `composer run phpstan`
- PHP Code can be checked for quality via `composer run code-quality`
- Tests are implemented with WP-Browser and Codeception
- Composer scripts exist for the different test suites and for fast/slow tests
- JS/CSS Code can be formatted via `npm run format`
- JS/CSS Code can be built via `npm run build`
- Translations can be collected/built via `composer run i18n`
- A full plugin-zip can be built via `composer run build`

## Releasing

1. Run linting, phpcs, phpcs:fix + phpstan
2. Update version
    - in main plugin file headers (`/sharable-password-protected-posts.php`)
    - in `package.json`
    - in `composer.json` + run `ddev composer update -- --lock`kk
    - stable tag in `readme.txt`
3. Add changelog entry in `readme.txt` and `CHANGELOG.md`
4. Optional: Update WordPress `Tested up to` and `Requires at least` in main plugin file headers (
   `sharable-password-protected-posts.php`/`readme.txt`)
5. Run `(ddev) composer i18n` to update translation files
6. Create PR / merge into main -> will trigger `build` and `deploy-wporg` CI jobs
7. Create a new release with a new tag in main `vX.Y.Z` with changelog
