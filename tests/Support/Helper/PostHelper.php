<?php

declare(strict_types=1);

namespace Tests\Support\Helper;

use lucatume\WPBrowser\Module\WPDb;

/**
 * Helper for handling posts in functional/end-to-end tests.
 */
class PostHelper extends \Codeception\Module {

    public function updatePostInDatabase( int $post_id, array $overrides ): void {
        $I = $this->getModule( WPDb::class );
        $tableName = $I->grabPostsTableName();
        $I->updateInDatabase(
            $tableName,
            $overrides,
            [ 'ID' => $post_id ]
        );
    }
}
