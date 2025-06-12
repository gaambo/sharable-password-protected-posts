<?php

declare(strict_types=1);

namespace Tests\Support;

use Tests\Support\Helper\PostHelper;

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

    public function amOnSharableLink( int $post_id ): void {
        $this->amOnPage( add_query_arg( '_sppp_key', $this->grabSecretKeyFromDatabase( $post_id ), '/?p=' . $post_id ) );
    }
}
