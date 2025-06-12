<?php

declare(strict_types=1);

namespace Tests\Support\Helper;

use lucatume\WPBrowser\Module\WPDb;
use function Private_Post_Share\generate_key;

/**
 * Helper for handling posts in functional/end-to-end tests.
 */
class PostHelper extends \Codeception\Module {

    public function havePasswordProtectedPostInDatabase( array $overrides, string $password = 'password' ): int {
        $I = $this->getModule( WPDb::class );
        $post_id = $I->havePostInDatabase(
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
        $I = $this->getModule( WPDb::class );
        $post_id = $I->havePostInDatabase(
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
        $I = $this->getModule( WPDb::class );
        $post_id = $this->havePasswordProtectedPostInDatabase( $overrides, $password );
        $I->havePostmetaInDatabase( $post_id, '_sppp_enabled', '1' );
        $I->havePostmetaInDatabase( $post_id, '_sppp_key', generate_key() );
        return $post_id;
    }

    public function havePrivatePostWithSharableLinkInDatabase( array $overrides ): int {
        $I = $this->getModule( WPDb::class );
        $post_id = $this->havePrivatePostInDatabase( $overrides );
        $I->havePostmetaInDatabase( $post_id, '_sppp_enabled', '1' );
        $I->havePostmetaInDatabase( $post_id, '_sppp_key', generate_key() );
        return $post_id;
    }

    public function updatePostInDatabase( int $post_id, array $overrides ): void {
        $I = $this->getModule( WPDb::class );
        $tableName = $I->grabPostsTableName();
        $I->updateInDatabase(
            $tableName,
            $overrides,
            [ 'ID' => $post_id ]
        );
    }

    public function updatePostMetaInDatabase( int $post_id, string $meta_key, string $meta_value ): void {
        $I = $this->getModule( WPDb::class );
        $tableName = $I->grabPostMetaTableName();
        $I->updateInDatabase(
            $tableName,
            [ 'meta_value' => $meta_value ],
            [ 'post_id' => $post_id, 'meta_key' => $meta_key ]
        );
    }

    public function grabSecretKeyFromDatabase( int $post_id ): string {
        $I = $this->getModule( WPDb::class );
        return $I->grabPostmetaFromDatabase( $post_id, '_sppp_key', true );
    }
}
