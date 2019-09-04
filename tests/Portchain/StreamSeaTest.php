<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;
require __DIR__ . "/../../src/Portchain/StreamSea.php";
require __DIR__ . "/../../src/Portchain/SchemaDefinition.php";

final class StreamSeaTest extends TestCase {

  public function testSchemaCanBeDefined(): void {

    $streamSea = new StreamSea('http://localhost:3104', 'app123', '01234567890123456789');
    
    $portCallSchema = new SchemaDefinition('1.0.0');
    $portCallSchema->addField('portCallKey', 'string');
    $portCallSchema->addField('arrival', 'date');
    $portCallSchema->addField('departure', 'date');
    $portCallSchema->addField('vesselImo', 'integer');
    $portCallSchema->addField('vesselDraft', 'float');
    $portCallSchema->addField('berthingSide', 'enum', ['starboard', 'port']);

    $streamSea->defineStream('portCall_test', $portCallSchema);

    $portCallSchema = new SchemaDefinition('0.1.0');
    $portCallSchema->addField('portCallKey', 'string');
    $streamSea->defineStream('portCall', $portCallSchema);
    
  }

  public function dtestMessageCanBePublished(): void {
    $streamSea = new StreamSea('http://localhost:3104', 'app123', '01234567890123456789');
    $streamSea->publish('portCall_test',  array(
      'portCallKey' => 'XYZ123',
      'arrival' => date('2019-01-23 10:00'),
      'departure' => date('2019-01-23 18:00'),
      'vesselImo' => 9999999,
      'vesselDraft' => 12.4,
      'berthingSide' => 'starboard'
    ));
  }
}


