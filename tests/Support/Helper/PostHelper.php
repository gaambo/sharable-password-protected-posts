<?php

declare(strict_types=1);

namespace Tests\Support\Helper;

// here you can define custom actions
// all public methods declared in helper class will be available in $I

use lucatume\WPBrowser\Module\WPDb;

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
