<?php

declare(strict_types=1);

namespace Tests\Support;

use lucatume\WPBrowser\Module\WPBrowser;
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
class FunctionalTester extends \Codeception\Actor {

    use _generated\FunctionalTesterActions;

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
}
