<?php

declare(strict_types=1);


namespace Tests\EndToEnd;

use Tests\Support\EndToEndTester;
use function Private_Post_Share\get_sharable_link;

final class PasswordProtectedPostsCest {

    public function _before( EndToEndTester $I ): void {
        // Code here will be executed before each test.
    }

    public function _after( EndToEndTester $I ): void {
        $I->logout();
    }

    public function try_viewing_password_protected_post_without_sharable_link( EndToEndTester $I ): void {
        $post_id = $I->havePasswordProtectedPostInDatabase(
            [
				'post_title' => 'Password Protected Post',
                'post_content' => 'private content',
			]
        );
        $I->amOnPage( '/?p=' . $post_id );
        $I->see( 'Protected: Password Protected Post' );
        $I->dontSee( 'private content' );
        $I->seePasswordProtectedPostForm();
    }

    public function try_viewing_password_protected_post_without_sharable_link_as_editor( EndToEndTester $I ): void {
        $post_id = $I->havePasswordProtectedPostInDatabase(
            [
				'post_title' => 'Password Protected Post',
				'post_content' => 'private content',
			]
        );

        [$user, $password] = $I->haveEditorUserInDatabase();
        $I->loginAs( $user, $password );

        $I->amOnPage( '/?p=' . $post_id );
        $I->seePasswordProtectedPostForm();
        $I->fillPasswordProtectedPostForm();

        $I->see( 'Password Protected Post' );
        $I->see( 'private content' );
    }

    public function try_viewing_password_protected_post_with_sharable_link( EndToEndTester $I ): void {
        $post_id = $I->havePasswordProtectedPostWithSharableLinkInDatabase(
            [
				'post_title' => 'Password Protected Post',
			]
        );
        $I->amOnSharableLink( $post_id );
        $I->see( 'Password Protected Post' );
    }

    public function try_viewing_password_protected_post_with_sharable_link_as_editor( EndToEndTester $I ): void {
        $post_id = $I->havePasswordProtectedPostWithSharableLinkInDatabase(
            [
				'post_title' => 'Password Protected Post',
                'post_content' => 'private content',
			]
        );

        [$user, $password] = $I->haveEditorUserInDatabase();
        $I->loginAs( $user, $password );

        $I->amOnSharableLink( $post_id );
        $I->see( 'Password Protected Post' );
        $I->see( 'private content' );
    }
}
