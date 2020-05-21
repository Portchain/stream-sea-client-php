<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;
require __DIR__ . "/../../src/Portchain/StreamSea.php";
require __DIR__ . "/../../src/Portchain/SchemaDefinition.php";

/** These tests require a running instance of the server to pass successfully */
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

    $err = $streamSea->defineStream('portCall_test1', $portCallSchema);
    $this->assertEquals($err, NULL);
    
  }

  public function testNestedSchemaCanBeDefined(): void {

    $streamSea = new StreamSea('http://localhost:3104', 'app123', '01234567890123456789');
    
    $portCallSchema = new SchemaDefinition('1.0.0');
    $portCallSchema->addField('topLevelProp1', 'string');
    $portCallSchema->addField('topLevelArrayOfObj','array<object>');
    $portCallSchema->addParentField('nestedObjProp1','string','topLevelArrayOfObj');
    $portCallSchema->addParentField('nestedObjProp2','date','topLevelArrayOfObj');
    $portCallSchema->addParentField('nestedObjProp3','integer','topLevelArrayOfObj');
  
    $streamSea->defineStream('testmoves_1', $portCallSchema);
    
  }
  
  public function testSchemaVersionsCanNotBeDowngraded(): void {

    $streamSea = new StreamSea('http://localhost:3104', 'app123', '01234567890123456789');
    
    $portCallSchema = new SchemaDefinition('1.0.0');
    $portCallSchema->addField('portCallKey', 'string');
    $err = $streamSea->defineStream('portCall_test2', $portCallSchema);
    $this->assertEquals($err, NULL);

    $portCallSchema = new SchemaDefinition('0.1.0');
    $portCallSchema->addField('portCallKey', 'string');
    $err = $streamSea->defineStream('portCall_test2', $portCallSchema);
    $this->assertNotEquals($err, NULL);
    $this->assertEquals($err->getCode(), 400);
    $this->assertEquals($err->getMessage(), 'The version pushed for the schema [portCall_test2] is [0.1.0] but there is already an older version defined [1.0.0]');
    
  }

  public function testMessageCanBePublished(): void {
    $streamSea = new StreamSea('http://localhost:3104', 'app123', '01234567890123456789');
    
    $portCallSchema = new SchemaDefinition('1.0.0');
    $portCallSchema->addField('portCallKey', 'string');
    $portCallSchema->addField('arrival', 'date');
    $portCallSchema->addField('departure', 'date');
    $portCallSchema->addField('vesselImo', 'integer');
    $portCallSchema->addField('vesselDraft', 'float');
    $portCallSchema->addField('berthingSide', 'enum', ['starboard', 'port']);

    $err = $streamSea->defineStream('portCall_test3', $portCallSchema);
    $this->assertEquals($err, NULL);

    $err = $streamSea->publish('portCall_test3',  array(
      'portCallKey' => 'XYZ123',
      'arrival' => (new DateTime('2019-01-23 10:00'))->format('Y-m-d\TH:i:s\Z'),
      'departure' => (new DateTime('2019-01-23 18:00'))->format('Y-m-d\TH:i:s\Z'),
      'vesselImo' => 9999999,
      'vesselDraft' => 12.4,
      'berthingSide' => 'starboard'
    ));
    $this->assertEquals($err, NULL);
  }
  
  public function testCredentialsInvalid(): void {

    $streamSea = new StreamSea('http://localhost:3104', 'foo', 'bar');
    
    $err = $streamSea->publish('portCall_test4', array(
      'foo' => 'bar',
    ));
    $this->assertNotEquals($err, NULL);
    $this->assertEquals($err->getCode(), 401);
    $this->assertEquals($err->getMessage(), 'The credentials are present and well formed but invalid.');

  }
  
  public function testDNSResolutionFailure(): void {

    $streamSea = new StreamSea('http://unknown-domain.stream-sea.com', 'app123', '01234567890123456789');
    
    $err = $streamSea->publish('portCall_test4', array(
      'foo' => 'bar',
    ));
    $this->assertNotEquals($err, NULL);
    $this->assertEquals($err->getCode(), 0);
    $this->assertEquals($err->getMessage(), 'Could not resolve host: unknown-domain.stream-sea.com');
    
  }

  public function testMoves(): void {
    $streamSea = new StreamSea('http://localhost:3104', 'app123', '01234567890123456789');

    $err = $streamSea->publish('moves',  array(
      'key' => 'XYZ123',
      'loadCountExpected' => 99,
      'dischargeCountExpected' => 99,
      'restowCountExpected' => 99,
      'loadCountAchieved' => 99,
      'dischargeCountAchieved' => 99,
      'restowCountAchieved' => 99,

      'moveCompletedTime' => (new DateTime('2019-01-23 10:00'))->format('Y-m-d\TH:i:s\Z'),
      'containerNumber' => 'MSKU8966700',
      'containerCategory' => 'TRSHP',
      'containerFreightKind' => 'MTY',
      'containerLineOperator' => 'MSK',
      'containerMoveKind' => 'LOAD',
      'containerMoveFromLocation' => 'YARD',
      'containerMoveFromLocationPosition' => '04C30E.1',
      'containerMoveToLocation' => 'VESSEL',
      'containerMoveToLocationPosition' => '421072',
      'containerMovePointOfWork' => 'QC17'
    ));
    $this->assertEquals($err, NULL);
  }

  public function testBatchMoves(): void {
    $streamSea = new StreamSea('http://localhost:3104', 'app123', '01234567890123456789');

    $err = $streamSea->publish('moves',  array(
      array(
        'key' => 'XYZ123',
        'loadCountExpected' => 99,
        'dischargeCountExpected' => 99,
        'restowCountExpected' => 99,
        'loadCountAchieved' => 99,
        'dischargeCountAchieved' => 99,
        'restowCountAchieved' => 99,

        'moveCompletedTime' => (new DateTime('2019-01-23 10:00'))->format('Y-m-d\TH:i:s\Z'),
        'containerNumber' => 'MSKU8966700',
        'containerCategory' => 'TRSHP',
        'containerFreightKind' => 'MTY',
        'containerLineOperator' => 'MSK',
        'containerMoveKind' => 'LOAD',
        'containerMoveFromLocation' => 'YARD',
        'containerMoveFromLocationPosition' => '04C30E.1',
        'containerMoveToLocation' => 'VESSEL',
        'containerMoveToLocationPosition' => '421072',
        'containerMovePointOfWork' => 'QC17'
      ),
      array(
        'key' => 'XYZ124',
        'loadCountExpected' => 99,
        'dischargeCountExpected' => 99,
        'restowCountExpected' => 99,
        'loadCountAchieved' => 99,
        'dischargeCountAchieved' => 99,
        'restowCountAchieved' => 99,

        'moveCompletedTime' => (new DateTime('2019-01-23 10:00'))->format('Y-m-d\TH:i:s\Z'),
        'containerNumber' => 'MSKU8966700',
        'containerCategory' => 'TRSHP',
        'containerFreightKind' => 'MTY',
        'containerLineOperator' => 'MSK',
        'containerMoveKind' => 'LOAD',
        'containerMoveFromLocation' => 'YARD',
        'containerMoveFromLocationPosition' => '04C30E.1',
        'containerMoveToLocation' => 'VESSEL',
        'containerMoveToLocationPosition' => '421072',
        'containerMovePointOfWork' => 'QC17'
      )
    ));
    $this->assertEquals($err, NULL);
  }
}


