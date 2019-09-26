<?php

  require __DIR__ . "/ValidationException.php";

  class StreamSea {

    public function __construct(string $remoteUrl, string $appId, string $appSecret) {
      $this->remoteUrl = $remoteUrl;
      $this->appId = $appId;
      $this->appSecret = $appSecret;
    }
    public function publish(string $channel, array $payload) {
        
      $service_url = $this->remoteUrl . '/api/v1/streams/' . $channel . '/publish';
      $curl_post_data = array(
        'payload' => $payload
      );

      $curl = curl_init($service_url);

      StreamSea::setDefaultHeaders($curl);
      curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
      curl_setopt($curl, CURLOPT_POST, true);
      curl_setopt($curl, CURLOPT_POSTFIELDS, gzencode(json_encode($curl_post_data), 9));
      return $this->executeCallOutWithErrorHandling($curl);

    }

    public function defineStream(string $channel, SchemaDefinition $schema) {
        
      $service_url = $this->remoteUrl . '/api/v1/streams/' . $channel . '/define';

      $curl = curl_init($service_url);
      StreamSea::setDefaultHeaders($curl);
      curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
      curl_setopt($curl, CURLOPT_POST, true);
      curl_setopt($curl, CURLOPT_POSTFIELDS, gzencode(json_encode($schema), 9));
      return $this->executeCallOutWithErrorHandling($curl);

    }

    private function setDefaultHeaders($curl) {
      curl_setopt($curl, CURLOPT_HTTPHEADER, array(
        'Content-encoding: gzip',
        'Content-type: application/json',
        'Accept: application/json',
        'Authorization: Basic ' . base64_encode($this->appId . ':' . $this->appSecret)
      ));
    }

    private function executeCallOutWithErrorHandling($curl) {

      $curl_response = curl_exec($curl);
      $httpcode = curl_getinfo($curl, CURLINFO_HTTP_CODE);

      if ($httpcode !== 200 && $httpcode > 0) {
        $info = curl_getinfo($curl);
        curl_close($curl);
        if($httpcode > 0) {
          try {
            $decoded = json_decode($curl_response);
            $errorMessage = $decoded->message;
          } catch(Exception $err) {
            error_log('Error: failed to parse the Stream-Sea HTTP response as JSON. The HTTP response code was '.$httpcode . ' and the response content was: ' . $curl_response);
          }
          if($errorMessage) {
            if(isset($decoded->errors)) {
              return new PayloadValidationException($errorMessage, $decoded->errors);
            } else {
              return new Exception($errorMessage, $httpcode);
            }
          } else {
            return new Exception('Unknown error. HTTP return code was ' . $httpcode, $httpcode);
          }
        }
      } else if($httpcode == 0) {
        if (curl_errno($curl)) {
          $error_msg = curl_error($curl);
          error_log('Error during HTTP callout: ' . $error_msg);
          return new Exception($error_msg, 0);
        } else {
          error_log('Unknown error during HTTP callout');
          return new Exception('Unknown error during HTTP callout', 0);
        }
      } else {
        curl_close($curl);
      }
      return NULL;
    }
}