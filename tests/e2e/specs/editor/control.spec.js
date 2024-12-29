/**
 * WordPress dependencies
 */
import { test, expect } from  '@wordpress/e2e-test-utils-playwright' ;


test.describe("Document setting control", () => {
    test(`checking checkbox enables feature and creates link`,  async ({page, admin , editor}) => {
        await admin.createNewPost();

        await editor.openDocumentSettingsSidebar();

        await page
            .getByRole( 'button', { name: 'Change status:' } )
            .click();
        await page.getByRole( 'radio', { name: "Private" } ).click();

        await page.getByLabel("Share post via secret URL").first().click();

        await expect(page.getByRole("textbox", { name: "Secret URL:" })).toBeVisible();
        await expect(page.locator(".sppp__copy-button")).toBeVisible();

        const link = await page.getByRole("textbox", { name: "Secret URL:" }).inputValue();
        expect(link).not.toBeFalsy();
        const linkUrl = new URL(link);

        const meta = await page.evaluate(() => {
            return wp.data.select("core/editor").getEditedPostAttribute("meta");
        });
        expect(meta["_sppp_enabled"]).toBeTruthy();
        expect(meta["_sppp_key"]).toBe(linkUrl.searchParams.get("_sppp_key"));
    });

    test(`disabling checkbox removes key and disables link`,  async ({page, admin , editor}) => {
        await admin.createNewPost();

        await editor.openDocumentSettingsSidebar();

        await page
            .getByRole( 'button', { name: 'Change status:' } )
            .click();
        await page.getByRole( 'radio', { name: "Private" } ).click();

        await page.getByLabel("Share post via secret URL").first().click();

        // Disable again.
        await page.getByLabel("Share post via secret URL").first().click();

        // await expect(page.getByRole("textbox", { name: "Secret URL:" })).toBeHidden();

        const meta = await page.evaluate(() => {
            return wp.data.select("core/editor").getEditedPostAttribute("meta");
        });
        expect(meta["_sppp_enabled"]).toBeFalsy();
        expect(meta["_sppp_key"]).toBeFalsy();
    });

    // TODO test copy button
});