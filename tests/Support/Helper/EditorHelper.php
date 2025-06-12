<?php

declare(strict_types=1);

namespace Tests\Support\Helper;

use lucatume\WPBrowser\Module\WPWebDriver as WebDriver;
use Exception;

/**
 * Helper for the block editor in end-to-end tests.
 */
class EditorHelper extends \Codeception\Module {

    public function hideEditorModals(): void {
        $I = $this->getModule( WebDriver::class );
        // Close welcome guide if open
        if ( $this->tryToSee( 'Wähle eine Vorlage', '.components-modal__frame' ) ) {
            $I->click( '.components-modal__frame:not(.edit-post-welcome-guide) .components-modal__header button' );
        }

        // Close welcome guide if open
        if ( $this->tryToSee( 'Willkommen beim Block-Editor', '.edit-post-welcome-guide' ) ) {
            $I->click( '.edit-post-welcome-guide .components-modal__header button' );
        }
    }

    public function savePostInEditor(): void {
        $I = $this->getModule( WebDriver::class );
        $I->click( '.editor-post-publish-button__button' );
        // $I->waitForText('Post updated');
        $I->wait(3); // wait for 3 secs

    }

    public function setPostStatus( string $status ): void {
        $I = $this->getModule( WebDriver::class );
        // Open status panel
        $I->clickWithLeftButton( '.editor-post-status button' );
        $I->wait( 1 );
        $I->selectOption( '.editor-change-status__options input[type="radio"]', $status );
        // Click outside
        $I->click( '.editor-sidebar__panel' );
    }

    public function setPasswordProtected( bool $protected = true, string $password = 'password' ): void {
        $I = $this->getModule( WebDriver::class );
        // Open status panel
        $I->clickWithLeftButton( '.editor-post-status button' );
        $I->wait( 1 );

        if ( $protected ) {
            $I->checkOption( '.editor-change-status__password-fieldset input[type="checkbox"]' );
        } else {
            $I->uncheckOption( '.editor-change-status__password-fieldset input[type="checkbox"]' );
        }

        $I->wait( 1 );
        $I->fillField( '.editor-change-status__password-input input[type="text"]', $password );
        // Click outside
        $I->click( '.editor-sidebar__panel' );
    }

    protected function tryToSee( string $text, ?string $selector = null ): bool {
        $I = $this->getModule( WebDriver::class );
        try {
            $I->waitForText( $text, 1, $selector );
            return true;
        } catch ( Exception $exception ) {
            return false;
        }
    }
}
