<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;
require __DIR__ . "/../../src/Portchain/StreamSea.php";

final class StreamSeaTest extends TestCase
{
    public function testCanBeUsedAsString(): void
    {
        $streamSea = new StreamSea();
        $this->assertEquals(
            '',
            $streamSea::publish('foobar', array(
              'message' => 'test message',
              'useridentifier' => 'agent@example.com',
              'department' => 'departmentId001',
              'subject' => 'My first conversation',
              'recipient' => 'recipient@example.com',
              'apikey' => 'key001'
            ))
        );
    }
}


