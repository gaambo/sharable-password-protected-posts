/**
 * External dependencies
 */
import { fileURLToPath } from 'url';
import { defineConfig, devices } from '@playwright/test';

/**
 * WordPress dependencies
 */
const baseConfig = require( '@wordpress/scripts/config/playwright.config' );

const config = defineConfig( {
    ...baseConfig,
    reporter: process.env.CI
        ? [ [ 'github' ] ]
        : 'list',
    workers: 1,
    globalSetup: fileURLToPath(
        new URL( './config/global-setup.js', 'file:' + __filename ).href
    ),
    projects: [
        {
            name: 'chromium',
            use: { ...devices[ 'Desktop Chrome' ] },
            grepInvert: /-chromium/,
        },
        {
            name: 'webkit',
            use: { ...devices[ 'Desktop Safari' ]},
            grep: /@webkit/,
            grepInvert: /-webkit/,
        },
        {
            name: 'firefox',
            use: { ...devices[ 'Desktop Firefox' ] },
            grep: /@firefox/,
            grepInvert: /-firefox/,
        },
    ],
} );

export default config;