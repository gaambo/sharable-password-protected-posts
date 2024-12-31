/**
 * WordPress dependencies
 */
import { test, expect } from '@wordpress/e2e-test-utils-playwright';

test.describe( 'Setting visible based on post-visibility', () => {
	[ 'Published', 'Draft', 'Scheduled' ].forEach( ( status ) => {
		test( `setting is not visible if post status is ${ status }`, async ( {
			page,
			admin,
			editor,
		} ) => {
			await admin.createNewPost();

			await editor.openDocumentSettingsSidebar();

			await page
				.getByRole( 'button', { name: 'Change status:' } )
				.click();
			await page.getByRole( 'radio', { name: status } ).click();

			await expect(
				page.getByLabel( 'Share post via secret URL' ).first()
			).toBeHidden();
		} );
	} );

	[ 'Private' ].forEach( ( status ) => {
		test( `setting is visible if post status is ${ status }`, async ( {
			page,
			admin,
			editor,
		} ) => {
			await admin.createNewPost();

			await editor.openDocumentSettingsSidebar();

			await page
				.getByRole( 'button', { name: 'Change status:' } )
				.click();
			await page.getByRole( 'radio', { name: status } ).click();

			await expect(
				page.getByLabel( 'Share post via secret URL' ).first()
			).toBeVisible();
		} );
	} );

	test( `setting is visible if post status is publish and password protected`, async ( {
		page,
		admin,
		editor,
	} ) => {
		await admin.createNewPost();

		await editor.openDocumentSettingsSidebar();

		await page.getByRole( 'button', { name: 'Change status:' } ).click();
		await page.getByRole( 'radio', { name: 'Published' } ).click();

		await page
			.getByRole( 'checkbox', { name: 'Password protected' } )
			.click();
		await page
			.getByRole( 'textbox', { name: 'Password' } )
			.fill( 'testpw' );

		await expect(
			page.getByLabel( 'Share post via secret URL' ).first()
		).toBeVisible();
	} );
} );
