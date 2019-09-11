# stream-sea-client-php
This project provides the ability to communicate with a Stream-Sea server.


## Setup

We recommend that you use [composer](https://getcomposer.org) to download the latest version of the Stream-Sea client library.


## Usage


### Define new streams

```php
  // Instantiate StreamSea
  $streamSea = new StreamSea(
    'http://localhost:3104', // remote server URL
    'app123', // your app identifier
    '01234567890123456789' // your app secret
  );
  
  $portCallSchema = new SchemaDefinition('1.0.0');
  $portCallSchema->addField('portCallKey', 'string');
  $portCallSchema->addField('arrival', 'date');
  $portCallSchema->addField('departure', 'date');
  $portCallSchema->addField('vesselImo', 'integer');
  $portCallSchema->addField('vesselDraft', 'float');
  $portCallSchema->addField('berthingSide', 'enum', ['starboard', 'port']);

  // returns NULL or an Exception object
  $err = $streamSea->defineStream('portCall', $schema); 
```

### Publish data to a stream

```php
  // Instantiate StreamSea
  $streamSea = new StreamSea(
    'http://localhost:3104', // remote server URL
    'app123', // your app identifier
    '01234567890123456789' // your app secret
  );

  // returns NULL or an Exception object
  $err = $streamSea->publish('portCall', array(  
    'portCallKey' => 'XYZ123',
    'arrival' => date('2019-01-23 10:00'),
    'departure' => date('2019-01-23 18:00'),
    'vesselImo' => 9999999,
    'vesselDraft' => 12.4,
    'berthingSide' => 'starboard'
  ));
```
