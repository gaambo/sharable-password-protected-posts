<?php

declare(strict_types=1);

namespace Tests\Support;

use Exception;
use function Private_Post_Share\generate_key;

/**
 * Inherited Methods
 *
 * @method void wantTo($text)
 * @method void wantToTest($text)
 * @method void execute($callable)
 * @method void expectTo($prediction)
 * @method void expect($prediction)
 * @method void amGoingTo($argumentation)
 * @method void am($role)
 * @method void lookForwardTo($achieveValue)
 * @method void comment($description)
 * @method void pause($vars = [])
 *
 * @SuppressWarnings(PHPMD)
 */
class EndToEndTester extends \Codeception\Actor {

    use _generated\EndToEndTesterActions;

    public function havePasswordProtectedPostInDatabase( array $overrides, string $password = 'password' ): int {
        $post_id = $this->havePostInDatabase(
            array_merge(
                [
					'post_status' => 'publish',
					'post_password' => $password,
				],
                $overrides
            )
        );
        return $post_id;
    }

    public function havePrivatePostInDatabase( array $overrides ): int {
        $post_id = $this->havePostInDatabase(
            array_merge(
                [
					'post_status' => 'private',
				],
                $overrides
            )
        );
        return $post_id;
    }

    public function havePasswordProtectedPostWithSharableLinkInDatabase( array $overrides, string $password = 'password' ): int {
        $post_id = $this->havePasswordProtectedPostInDatabase( $overrides, $password );
        $this->havePostmetaInDatabase( $post_id, '_sppp_enabled', 1 );
        $this->havePostmetaInDatabase( $post_id, '_sppp_key', generate_key() );
        return $post_id;
    }

    public function havePrivatePostWithSharableLinkInDatabase( array $overrides ): int {
        $post_id = $this->havePrivatePostInDatabase( $overrides );
        $this->havePostmetaInDatabase( $post_id, '_sppp_enabled', 1 );
        $this->havePostmetaInDatabase( $post_id, '_sppp_key', generate_key() );
        return $post_id;
    }

    public function haveEditorUserInDatabase( string $user_login = 'editor', string $password = 'password' ): array {
        $this->haveUserInDatabase(
            $user_login,
            'editor',
            [
				'user_pass' => $password,
			],
        );
        return [
            $user_login,
            $password,
        ];
    }

    public function seePrivatePostForbidden(): void {
        $this->see( 'Page not found' );
    }

    public function seePasswordProtectedPostForm(): void {
        $this->see( 'Protected:' );
        $this->see( 'This content is password protected. To view it please enter your password below:' );
        $this->seeElement( 'input[name="post_password"]' );
    }

    public function fillPasswordProtectedPostForm( string $password = 'password' ): void {
        $this->fillField( 'post_password', $password );
        $this->click( 'Enter' );
    }

    public function grabSecretKeyFromDatabase( int $post_id ): string {
        return $this->grabPostmetaFromDatabase( $post_id, '_sppp_key', true );
    }

    public function amOnSharableLink( int $post_id ): void {
        $this->amOnPage( add_query_arg( '_sppp_key', $this->grabSecretKeyFromDatabase( $post_id ), '/?p=' . $post_id ) );
    }
}
