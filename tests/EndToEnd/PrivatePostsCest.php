<?php

declare(strict_types=1);


namespace Tests\EndToEnd;

use Tests\Support\EndToEndTester;
use function Private_Post_Share\get_sharable_link;

final class PrivatePostsCest {

    public function _before( EndToEndTester $I ): void {
        // Code here will be executed before each test.
    }

    public function _after( EndToEndTester $I ): void {
        $I->logout();
    }

    public function try_viewing_private_post_without_sharable_link( EndToEndTester $I ): void {
        $post_id = $I->havePrivatePostInDatabase(
            [
				'post_title' => 'Private Post',
			]
        );
        $I->amOnPage( '/?p=' . $post_id );
        $I->dontSee( 'Private Post' );
        $I->seePrivatePostForbidden();
    }

    public function try_viewing_private_post_without_sharable_link_as_editor( EndToEndTester $I ): void {
        $post_id = $I->havePrivatePostInDatabase(
            [
				'post_title' => 'Private Post',
				'post_content' => 'private content',
			]
        );

        [$user, $password] = $I->haveEditorUserInDatabase();
        $I->loginAs( $user, $password );

        $I->amOnPage( '/?p=' . $post_id );
        $I->see( 'Private Post' );
        $I->see( 'private content' );
    }

    public function try_viewing_private_post_with_sharable_link( EndToEndTester $I ): void {
        $post_id = $I->havePrivatePostWithSharableLinkInDatabase(
            [
				'post_title' => 'Private Post',
			]
        );
        $I->amOnSharableLink( $post_id );
        $I->see( 'Private Post' );
    }

    public function try_viewing_private_post_with_sharable_link_as_editor( EndToEndTester $I ): void {
        $post_id = $I->havePrivatePostWithSharableLinkInDatabase(
            [
				'post_title' => 'Private Post',
                'post_content' => 'private content',
			]
        );

        [$user, $password] = $I->haveEditorUserInDatabase();
        $I->loginAs( $user, $password );

        $I->amOnSharableLink( $post_id );
        $I->see( 'Private Post' );
        $I->see( 'private content' );
    }
}
