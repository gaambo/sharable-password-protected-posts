<?php

declare(strict_types=1);


namespace Tests\Functional;

use Tests\Support\FunctionalTester;

final class RestApiCest {

    public function _before( FunctionalTester $I ): void {
        // Code here will be executed before each test.
    }

    /**
     * UNAUTHENTICATED REST API REQUESTS
     */

    /**
     * Test that showing a private post in the public rest api does not expose the key.
     */
    public function try_to_not_see_key_for_private_post_in_unauthenticated_request( FunctionalTester $I ): void {
        $post_id = $I->havePrivatePostWithSharableLinkInDatabase(
            [
                'post_title' => 'Private Post',
                'post_content' => 'private content',
            ]
        );
        $I->makeRestRequest( 'GET', '/wp-json/wp/v2/posts/' . $post_id );
        $I->seeResponseCodeIs( 401 );
    }

    /**
     * Test that showing a password-protected post in the public rest api does not expose the key.
     */
    public function try_to_not_see_key_for_password_protected_post_in_unauthenticated_request( FunctionalTester $I ): void {
        $post_id = $I->havePasswordProtectedPostWithSharableLinkInDatabase(
            [
                'post_title' => 'Password protected Post',
                'post_content' => 'private content',
            ]
        );
        $I->makeRestRequest( 'GET', '/wp-json/wp/v2/posts/' . $post_id );
        $I->seeResponseCodeIs( 200 );
        $response = $I->grabResponse();
        $I->assertArrayHasKey( 'meta', $response );
        $I->assertArrayNotHasKey( '_sppp_enabled', $response['meta'] );
        $I->assertArrayNotHasKey( '_sppp_key', $response['meta'] );
        $I->assertTrue( $response['excerpt']['protected'] );
        $I->assertTrue( $response['content']['protected'] );
    }

    /**
     * Test that showing a password-protected post that was (re-)published later
     * does not expose the key.
     */
    public function try_to_not_see_key_for_password_protected_post_in_unauthenticated_request_after_it_was_republished( FunctionalTester $I ): void {
        $post_id = $I->havePasswordProtectedPostWithSharableLinkInDatabase(
            [
                'post_title' => 'Password protected Post',
                'post_content' => 'private content',
            ]
        );

        $I->updatePostInDatabase(
            $post_id,
            [
                'post_status' => 'publish',
            ]
        );

        $I->makeRestRequest( 'GET', '/wp-json/wp/v2/posts/' . $post_id );
        $I->seeResponseCodeIs( 200 );
        $response = $I->grabResponse();
        $I->assertArrayHasKey( 'meta', $response );
        $I->assertArrayNotHasKey( '_sppp_enabled', $response['meta'] );
        $I->assertArrayNotHasKey( '_sppp_key', $response['meta'] );
        $I->assertTrue( $response['excerpt']['protected'] );
        $I->assertTrue( $response['content']['protected'] );
    }

    /**
     * Test that showing a private post that was (re-)published later
     * does not expose the key.
     */
    public function try_to_not_see_key_for_private_post_in_unauthenticated_request_after_it_was_republished( FunctionalTester $I ): void {
        $post_id = $I->havePrivatePostWithSharableLinkInDatabase(
            [
                'post_title' => 'Private Post',
                'post_content' => 'private content',
            ]
        );

        $I->updatePostInDatabase(
            $post_id,
            [
                'post_status' => 'publish',
            ]
        );

        $I->makeRestRequest( 'GET', '/wp-json/wp/v2/posts/' . $post_id );
        $I->seeResponseCodeIs( 200 );
        $response = $I->grabResponse();
        $I->assertArrayHasKey( 'meta', $response );
        $I->assertArrayNotHasKey( '_sppp_enabled', $response['meta'] );
        $I->assertArrayNotHasKey( '_sppp_key', $response['meta'] );
    }

    /**
     * EDITOR-USER AUTHENTICATED REST API REQUESTS WITH VIEW CONTEXT
     */
    /**
     * Test that showing a private post in the rest api as an editor user with the context "view" does not expose the key.
     */
    public function try_to_not_see_key_for_private_post_in_authenticated_request_with_view_context( FunctionalTester $I ): void {
        $post_id = $I->havePrivatePostWithSharableLinkInDatabase(
            [
                'post_title' => 'Private Post',
                'post_content' => 'private content',
            ]
        );

        $this->loginAsEditor( $I );

        $I->makeAuthenticatedRestRequest( 'GET', '/wp-json/wp/v2/posts/' . $post_id );
        $I->seeResponseCodeIs( 200 );
        $response = $I->grabResponse();
        $I->assertArrayHasKey( 'meta', $response );
        $I->assertArrayNotHasKey( '_sppp_enabled', $response['meta'] );
        $I->assertArrayNotHasKey( '_sppp_key', $response['meta'] );
    }

    /**
     * Test that showing a private post in the rest api as an editor user with the context "view" does not expose the key.
     */
    public function try_to_not_see_key_for_password_protected_post_in_authenticated_request_with_view_context( FunctionalTester $I ): void {
        $post_id = $I->havePasswordProtectedPostWithSharableLinkInDatabase(
            [
                'post_title' => 'Password protected Post',
                'post_content' => 'private content',
            ]
        );

        $this->loginAsEditor( $I );

        $I->makeAuthenticatedRestRequest( 'GET', '/wp-json/wp/v2/posts/' . $post_id );
        $I->seeResponseCodeIs( 200 );
        $response = $I->grabResponse();
        $I->assertArrayHasKey( 'meta', $response );
        $I->assertArrayNotHasKey( '_sppp_enabled', $response['meta'] );
        $I->assertArrayNotHasKey( '_sppp_key', $response['meta'] );
    }

    /**
     * Test that showing a private post that was (re-)published later in the rest api as an editor user with the context "view" does not expose the key.
     */
    public function try_to_not_see_key_for_password_protected_post_in_authenticated_request_with_view_context_after_it_was_republished( FunctionalTester $I ): void {
        $post_id = $I->havePasswordProtectedPostWithSharableLinkInDatabase(
            [
                'post_title' => 'Password protected Post',
                'post_content' => 'private content',
            ]
        );

        $I->updatePostInDatabase(
            $post_id,
            [
                'post_status' => 'publish',
            ]
        );

        $this->loginAsEditor( $I );

        $I->makeAuthenticatedRestRequest( 'GET', '/wp-json/wp/v2/posts/' . $post_id );
        $I->seeResponseCodeIs( 200 );
        $response = $I->grabResponse();
        $I->assertArrayHasKey( 'meta', $response );
        $I->assertArrayNotHasKey( '_sppp_enabled', $response['meta'] );
        $I->assertArrayNotHasKey( '_sppp_key', $response['meta'] );
    }

    /**
     * Test that showing a private post that was (re-)published later in the rest api as an editor user with the context "view" does not expose the key.
     */
    public function try_to_not_see_key_for_private_post_in_authenticated_request_with_view_context_after_it_was_republished( FunctionalTester $I ): void {
        $post_id = $I->havePrivatePostWithSharableLinkInDatabase(
            [
                'post_title' => 'Private Post',
                'post_content' => 'private content',
            ]
        );

        $I->updatePostInDatabase(
            $post_id,
            [
                'post_status' => 'publish',
            ]
        );

        $this->loginAsEditor( $I );

        $I->makeAuthenticatedRestRequest( 'GET', '/wp-json/wp/v2/posts/' . $post_id );
        $I->seeResponseCodeIs( 200 );
        $response = $I->grabResponse();
        $I->assertArrayHasKey( 'meta', $response );
        $I->assertArrayNotHasKey( '_sppp_enabled', $response['meta'] );
        $I->assertArrayNotHasKey( '_sppp_key', $response['meta'] );
    }

    /**
     * EDITOR-USER AUTHENTICATED REST API REQUESTS WITH EDIT CONTEXT
     */

    /**
     * Test that showing a private post in the rest api as an editor user with the context "edit" does not expose the key.
     */
    public function try_to_see_key_for_private_post_in_authenticated_request_with_edit_context( FunctionalTester $I ): void {
        $post_id = $I->havePrivatePostWithSharableLinkInDatabase(
            [
                'post_title' => 'Private Post',
                'post_content' => 'private content',
            ]
        );
        $key = $I->grabSecretKeyFromDatabase( $post_id );

        $this->loginAsEditor( $I );

        $I->makeAuthenticatedRestRequest( 'GET', '/wp-json/wp/v2/posts/' . $post_id, [ 'context' => 'edit' ] );
        $I->seeResponseCodeIs( 200 );
        $response = $I->grabResponse();
        $I->assertArrayHasKey( 'meta', $response );
        $I->assertArrayHasKey( '_sppp_enabeled', $response['meta'] );
        $I->assertArrayHasKey( '_sppp_key', $response['meta'] );
        $I->assertTrue( $response['meta']['_sppp_enabeled'] );
        $I->assertEquals( $response['meta']['_sppp_key'], $key );
    }

    /**
     * Test that showing a private post in the rest api as an editor user with the context "edit" does not expose the key.
     */
    public function try_to_see_key_for_password_protected_post_in_authenticated_request_with_edit_context( FunctionalTester $I ): void {
        $post_id = $I->havePasswordProtectedPostWithSharableLinkInDatabase(
            [
                'post_title' => 'Password protected Post',
                'post_content' => 'private content',
            ]
        );
        $key = $I->grabSecretKeyFromDatabase( $post_id );

        $this->loginAsEditor( $I );

        $I->makeAuthenticatedRestRequest( 'GET', '/wp-json/wp/v2/posts/' . $post_id, [ 'context' => 'edit' ] );
        $I->seeResponseCodeIs( 200 );
        $response = $I->grabResponse();
        $I->assertArrayHasKey( 'meta', $response );
        $I->assertArrayHasKey( '_sppp_enabled', $response['meta'] );
        $I->assertArrayHasKey( '_sppp_key', $response['meta'] );

        $I->assertTrue( $response['meta']['_sppp_enabled'] );
        $I->assertEquals( $response['meta']['_sppp_key'], $key );
    }

    /**
     * Test that showing a private post that was (re-)published later in the rest api as an editor user with the context "edit" does not expose the key.
     */
    public function try_to_not_see_key_for_password_protected_post_in_authenticated_request_with_edit_context_after_it_was_republished( FunctionalTester $I ): void {
        $post_id = $I->havePasswordProtectedPostWithSharableLinkInDatabase(
            [
                'post_title' => 'Password protected Post',
                'post_content' => 'private content',
            ]
        );
        $key = $I->grabPostMetaFromDatabase( $post_id, '_sppp_key', true );

        $I->updatePostInDatabase(
            $post_id,
            [
                'post_status' => 'publish',
            ]
        );

        $this->loginAsEditor( $I );

        $I->makeAuthenticatedRestRequest( 'GET', '/wp-json/wp/v2/posts/' . $post_id, [ 'context' => 'edit' ] );
        $I->seeResponseCodeIs( 200 );
        $response = $I->grabResponse();
        $I->assertArrayHasKey( 'meta', $response );
        $I->assertArrayNotHasKey( '_sppp_enabled', $response['meta'] );
        $I->assertTrue( $response['meta']['_sppp_enabled'] );
        $I->assertArrayHasKey( '_sppp_key', $response['meta'] );
        $I->assertEquals( $response['meta']['_sppp_key'], $key );
    }

    /**
     * Test that showing a private post that was (re-)published later in the rest api as an editor user with the context "edit" does not expose the key.
     */
    public function try_to_not_see_key_for_private_post_in_authenticated_request_with_edit_context_after_it_was_republished( FunctionalTester $I ): void {
        $post_id = $I->havePrivatePostWithSharableLinkInDatabase(
            [
                'post_title' => 'Private Post',
                'post_content' => 'private content',
            ]
        );
        $key = $I->grabPostMetaFromDatabase( $post_id, '_sppp_key', true );

        $I->updatePostInDatabase(
            $post_id,
            [
                'post_status' => 'publish',
            ]
        );

        $this->loginAsEditor( $I );

        $I->makeAuthenticatedRestRequest( 'GET', '/wp-json/wp/v2/posts/' . $post_id, [ 'context' => 'edit' ] );
        $I->seeResponseCodeIs( 200 );
        $response = $I->grabResponse();
        $I->assertArrayHasKey( 'meta', $response );
        $I->assertArrayHasKey( '_sppp_enabled', $response['meta'] );
        $I->assertTrue( $response['meta']['_sppp_enabled'] );
        $I->assertArrayHasKey( '_sppp_key', $response['meta'] );
        $I->assertEquals( $response['meta']['_sppp_key'], $key );
    }

    private function loginAsEditor( FunctionalTester $I ): void {
        [$user, $password] = $I->haveEditorUserInDatabase();

        $I->loginAs( $user, $password );
    }
}
