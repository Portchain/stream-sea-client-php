<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;
require __DIR__ . "/../../src/Portchain/StreamSea.php";
require __DIR__ . "/../../src/Portchain/SchemaDefinition.php";

/** These tests require a running instance of the server to pass successfully */
final class StreamSeaTest extends TestCase {

  /**
   * @doesNotPerformAssertions
   */
  public function testSchemaCanBeDefined(): void {

    $streamSea = new StreamSea('http://localhost:3104', 'app123', '01234567890123456789');
    
    $portCallSchema = new SchemaDefinition('1.0.0');
    $portCallSchema->addField('portCallKey', 'string');
    $portCallSchema->addField('arrival', 'date');
    $portCallSchema->addField('departure', 'date');
    $portCallSchema->addField('vesselImo', 'integer');
    $portCallSchema->addField('vesselDraft', 'float');
    $portCallSchema->addField('berthingSide', 'enum', ['starboard', 'port']);

    $streamSea->defineStream('portCall_test1', $portCallSchema);
    
  }
  
  public function testSchemaVersionsCanNotBeDowngraded(): void {
    $this->expectException(Exception::class);
    $this->expectExceptionCode(400);

    $streamSea = new StreamSea('http://localhost:3104', 'app123', '01234567890123456789');
    
    $portCallSchema = new SchemaDefinition('1.0.0');
    $portCallSchema->addField('portCallKey', 'string');
    $streamSea->defineStream('portCall_test2', $portCallSchema);

    $portCallSchema = new SchemaDefinition('0.1.0');
    $portCallSchema->addField('portCallKey', 'string');
    $streamSea->defineStream('portCall_test2', $portCallSchema);
    
  }

  /**
   * @doesNotPerformAssertions
   */
  public function testMessageCanBePublished(): void {
    $streamSea = new StreamSea('http://localhost:3104', 'app123', '01234567890123456789');
    
    $portCallSchema = new SchemaDefinition('1.0.0');
    $portCallSchema->addField('portCallKey', 'string');
    $portCallSchema->addField('arrival', 'date');
    $portCallSchema->addField('departure', 'date');
    $portCallSchema->addField('vesselImo', 'integer');
    $portCallSchema->addField('vesselDraft', 'float');
    $portCallSchema->addField('berthingSide', 'enum', ['starboard', 'port']);

    $streamSea->defineStream('portCall_test3', $portCallSchema);

    $streamSea->publish('portCall_test3',  array(
      'portCallKey' => 'XYZ123',
      'arrival' => (new DateTime('2019-01-23 10:00'))->format('Y-m-d\TH:i:s\Z'),
      'departure' => (new DateTime('2019-01-23 18:00'))->format('Y-m-d\TH:i:s\Z'),
      'vesselImo' => 9999999,
      'vesselDraft' => 12.4,
      'berthingSide' => 'starboard'
    ));
  }
}


