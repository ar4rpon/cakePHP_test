<?php
declare(strict_types=1);

namespace App\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

/**
 * TodosFixture
 */
class TodosFixture extends TestFixture
{
    /**
     * Init method
     *
     * @return void
     */
    public function init(): void
    {
        $this->records = [
            [
                'id' => 1,
                'todo_name' => 'Lorem ipsum dolor sit amet',
                'category' => 'Lorem ipsum dolor sit amet',
                'status' => 1,
            ],
        ];
        parent::init();
    }
}
