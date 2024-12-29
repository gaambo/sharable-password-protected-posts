/**
 * WordPress dependencies
 */
import { test, expect } from '@wordpress/e2e-test-utils-playwright';

async function savePrivatePost( editor, page ) {
	// Enter a title for this post - required for publishing.
	await editor.canvas
		.locator( 'role=textbox[name="Add title"i]' )
		.fill( 'Title' );
	// Publish post - button is called "save" when it's a private post.
	await page
		.getByRole( 'region', { name: 'Editor top bar' } )
		.getByRole( 'button', { name: 'Save', exact: true } )
		.click();
}

async function logout( page ) {
	const logoutLink = await page
		.locator( '#wp-admin-bar-logout > a' )
		.getAttribute( 'href' );
	return page.goto( logoutLink );
}

test.describe( 'Frontend viewing post', () => {
	test( `can view private post by visiting secret URL`, async ( {
		page,
		admin,
		editor,
		requestUtils,
	} ) => {
		await admin.createNewPost();

		await editor.openDocumentSettingsSidebar();

		await page.getByRole( 'button', { name: 'Change status:' } ).click();
		await page.getByRole( 'radio', { name: 'Private' } ).click();

		await page.getByLabel( 'Share post via secret URL' ).first().click();

		const link = await page
			.getByRole( 'textbox', { name: 'Secret URL:' } )
			.inputValue();

		await savePrivatePost( editor, page );
		await logout( page );

		const response = await page.goto( link );
		expect( response.status() ).not.toBe( 404 );

		// login again.
		await requestUtils.setupRest();
	} );

	test( `cannot view private post being logged out`, async ( {
		page,
		admin,
		editor,
		requestUtils,
	} ) => {
		await admin.createNewPost();

		await editor.openDocumentSettingsSidebar();

		await page.getByRole( 'button', { name: 'Change status:' } ).click();
		await page.getByRole( 'radio', { name: 'Private' } ).click();

		await page.getByLabel( 'Share post via secret URL' ).first().click();

		const link = await page.evaluate( () => {
			return window.wp.data.select( 'core/editor' ).getPermalink();
		} );

		await savePrivatePost( editor, page );
		await logout( page );

		const response = await page.goto( link );
		expect( response.status() ).toBe( 404 );

		// login again.
		await requestUtils.setupRest();
	} );

	test( `can view private post by being logged in`, async ( {
		page,
		admin,
		editor,
	} ) => {
		await admin.createNewPost();

		await editor.openDocumentSettingsSidebar();

		await page.getByRole( 'button', { name: 'Change status:' } ).click();
		await page.getByRole( 'radio', { name: 'Private' } ).click();

		await page.getByLabel( 'Share post via secret URL' ).first().click();

		// Enter a title for this post.
		await editor.canvas
			.locator( 'role=textbox[name="Add title"i]' )
			.fill( 'Title' );
		await page
			.getByRole( 'region', { name: 'Editor top bar' } )
			.getByRole( 'button', { name: 'Save', exact: true } )
			.click();

		const link = await page.evaluate( () => {
			return window.wp.data.select( 'core/editor' ).getPermalink();
		} );

		const response = await page.goto( link );
		expect( response.status() ).not.toBe( 404 );
	} );

	// TODO test copy button
} );
