<?php

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
      curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($curl_post_data));
      $this->executeCallOutWithErrorHandling($curl);

    }

    public function defineStream(string $channel, SchemaDefinition $schema) {
        
      $service_url = $this->remoteUrl . '/api/v1/streams/' . $channel . '/define';

      $curl = curl_init($service_url);
      StreamSea::setDefaultHeaders($curl);
      curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
      curl_setopt($curl, CURLOPT_POST, true);
      curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($schema));
      $this->executeCallOutWithErrorHandling($curl);

    }

    private function setDefaultHeaders($curl) {
      curl_setopt($curl, CURLOPT_HTTPHEADER, array(
        'Content-type: application/json',
        'Accept: application/json',
        'Authorization: Basic ' . base64_encode($this->appId . ':' . $this->appSecret)
      ));
    }

    private function executeCallOutWithErrorHandling($curl) {

      $curl_response = curl_exec($curl);
      $httpcode = curl_getinfo($curl, CURLINFO_HTTP_CODE);

      if ($httpcode !== 200) {
        $info = curl_getinfo($curl);
        error_log('Error during HTTP callout. Additional info: ' . var_export($info));
        curl_close($curl);
        
        try {
          $decoded = json_decode($curl_response);
        } catch(Exception $err) {
          error_log('Error: failed to parse the Stream-Sea HTTP response as JSON.', 'The HTTP response code was '.$httpcode . ' and the response content was: ' . $curl_response);
        }
        if($decoded && $decoded->response && $decoded->response->message) {
          throw new Exception($decoded->response->message, $httpcode);
        } else {
          throw new Exception('Unknown error. HTTP return code was ' . $httpcode, $httpcode);
        }
      } else {
        curl_close($curl);
      }
    }
}