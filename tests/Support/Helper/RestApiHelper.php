<?php

declare(strict_types=1);

namespace Tests\Support\Helper;

// here you can define custom actions
// all public methods declared in helper class will be available in $I

use lucatume\WPBrowser\Module\WPBrowser;

class RestApiHelper extends \Codeception\Module {

    /**
     * Need to be logged in before so cookie auth works.
     *
     * @return string
     * @throws \Codeception\Exception\ModuleException
     */
    public function grabRestNonce(): string {
        $I = $this->getModule( WPBrowser::class );
        $I->sendAjaxRequest( 'GET', '/wp-admin/admin-ajax.php?action=rest-nonce' );

        $rawResponse = $this->getModule( WPBrowser::class )->_getResponseContent();
        return $rawResponse;
    }

    public function makeRestRequest( string $method, string $uri, array $params = [] ) {
        if ( $method === 'GET' && ! empty( $params ) ) {
            $uri .= '?' . http_build_query( $params );
            $params = [];
        }
        $I = $this->getModule( WPBrowser::class );
        $I->sendAjaxRequest(
            $method,
            $uri,
            $params
        );
    }

    public function makeAuthenticatedRestRequest( string $method, string $uri, array $params = [], ?string $nonce = null ) {
        if ( ! $nonce ) {
            $nonce = $this->grabRestNonce();
        }

        $I = $this->getModule( WPBrowser::class );
        $I->haveHttpHeader( 'X-WP-Nonce', $nonce );
        $this->makeRestRequest( $method, $uri, $params );
    }

    public function grabResponse(): array {
        $rawResponse = $this->getModule( WPBrowser::class )->_getResponseContent();
        $response = json_decode( $rawResponse, true );
        return $response;
    }
}
