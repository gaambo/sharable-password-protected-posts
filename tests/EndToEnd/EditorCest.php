<?php

declare(strict_types=1);


namespace Tests\EndToEnd;

use Tests\Support\EndToEndTester;

final class EditorCest {

    public function _before( EndToEndTester $I ): void {
        // Code here will be executed before each test.
    }

    public function try_enable_sharable_link_on_password_protected_post( EndToEndTester $I ): void {
        $pageId = $I->havePageInDatabase(
            [
				'post_title' => 'Super secret page',
			]
        );

        $I->loginAsAdmin();
        $I->amEditingPostWithId( $pageId );
        // $I->wait(5);
        $I->hideEditorModals();

        // Set password protected
        $I->setPasswordProtected();

        // See PPS settings
        $I->see( 'Share post via secret URL' );
        $I->dontSeeElement( '.private-post-share .private-post-share__link' );
        $I->checkOption( '.private-post-share__checkbox input[type="checkbox"]' );
        $I->seeElement( '.private-post-share .private-post-share__link' );

        $link = $I->grabAttributeFrom( '.private-post-share .private-post-share__link input[type="text"]', 'value' );

        // Save
        $I->savePostInEditor();
        $I->wait( 5 );

        $I->logOut();

        $I->amOnPage( '/?p=' . $pageId );
        $I->see( 'Protected: Super secret page' );

        $I->amOnUrl( $link );
        $I->dontSee( 'Protected: Super secret page' );
        $I->see( 'Super secret page' );
    }

    public function try_enable_sharable_link_on_private_post( EndToEndTester $I ): void {
        $pageId = $I->havePageInDatabase(
            [
                'post_title' => 'Super secret page',
            ]
        );

        $I->loginAsAdmin();
        $I->amEditingPostWithId( $pageId );
        // $I->wait(5);
        $I->hideEditorModals();

        $I->setPostStatus( 'private' ); // Set private();

        // See PPS settings
        $I->see( 'Share post via secret URL' );
        $I->dontSeeElement( '.private-post-share .private-post-share__link' );
        $I->checkOption( '.private-post-share__checkbox input[type="checkbox"]' );
        $I->seeElement( '.private-post-share .private-post-share__link' );

        $link = $I->grabAttributeFrom( '.private-post-share .private-post-share__link input[type="text"]', 'value' );

        // Save
        $I->savePostInEditor();
        $I->wait( 5 );

        $I->logOut();

        $I->amOnPage( '/?p=' . $pageId );
        $I->seePrivatePostForbidden();

        $I->amOnUrl( $link );
        $I->see( 'Super secret page' );
    }

    public function try_cannot_enable_sharable_link_on_published_post( EndToEndTester $I ): void {
        $pageId = $I->havePageInDatabase(
            [
                'post_title' => 'Super secret page',
                'post_status' => 'draft',
            ]
        );

        $I->loginAsAdmin();
        $I->amEditingPostWithId( $pageId );
        // $I->wait(5);
        $I->hideEditorModals();

        $I->setPostStatus( 'publish' ); // Set private();

        // See PPS settings
        $I->dontSee( 'Share post via secret URL' );
        $I->dontSeeElement( '.private-post-share .private-post-share__link' );
    }

    public function try_cannot_enable_sharable_link_on_draft_post( EndToEndTester $I ): void {
        $pageId = $I->havePageInDatabase(
            [
                'post_title' => 'Super secret page',
                'post_status' => 'publish',
            ]
        );

        $I->loginAsAdmin();
        $I->amEditingPostWithId( $pageId );
        // $I->wait(5);
        $I->hideEditorModals();

        $I->setPostStatus( 'draft' ); // Set private();

        // See PPS settings
        $I->dontSee( 'Share post via secret URL' );
        $I->dontSeeElement( '.private-post-share .private-post-share__link' );
    }
}
