<?php

declare(strict_types=1);

namespace Tests\EndToEnd;

use Tests\Support\EndToEndTester;

class PluginActivationCest {

    public function try_activating_plugin( EndToEndTester $I ): void {
        $I->loginAsAdmin();
        $I->amOnPluginsPage();

        $I->seePluginActivated( 'sharable-password-protected-posts' );

        $I->deactivatePlugin( 'sharable-password-protected-posts' );

        $I->seePluginDeactivated( 'sharable-password-protected-posts' );

        $I->activatePlugin( 'sharable-password-protected-posts' );

        $I->seePluginActivated( 'sharable-password-protected-posts' );
    }
}
