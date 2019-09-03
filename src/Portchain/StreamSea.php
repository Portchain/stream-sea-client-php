<?php

  class StreamSea
  {

    public function publish($channel, $payload)
    {
      $STREAM_SEA_URL = $_ENV["STREAM_SEA_URL"];
      $STREAM_SEA_CREDENTIALS = $_ENV["STREAM_SEA_CREDENTIALS"];
        
      $service_url = $STREAM_SEA_URL . '/api/v1/streams/' . $channel . '/publish';
      $curl = curl_init($service_url);
      $curl_post_data = array(
        'schemaVersion' => 1,
        'payload' => $payload
      );
      curl_setopt($curl, CURLOPT_HTTPHEADER, array(
        'Content-type: application/json',
        'Accept: application/json',
        'Authorization: Basic ' . base64_encode($STREAM_SEA_CREDENTIALS)
      ));
      curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
      curl_setopt($curl, CURLOPT_POST, true);
      curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($curl_post_data));
      $curl_response = curl_exec($curl);
      if ($curl_response === false) {
        $info = curl_getinfo($curl);
        curl_close($curl);
        die('error occured during curl exec. Additional info: ' . var_export($info));
      }
      curl_close($curl);
      // $decoded = json_decode($curl_response);
      return $curl_response;
      // if (isset($decoded->response->status) && $decoded->response->status == 'ERROR') {
      //     die('error occured: ' . $decoded->response->errormessage);
      // }
      // echo 'response ok!';
      // return var_export($decoded->response);

    }

    public function defineSchema($channel, $schema)
    {
      $STREAM_SEA_URL = $_ENV["STREAM_SEA_URL"];
      $STREAM_SEA_CREDENTIALS = $_ENV["STREAM_SEA_CREDENTIALS"];
        
      $service_url = $STREAM_SEA_URL . '/api/v1/streams/' . $channel . '/define';
      $curl = curl_init($service_url);
      curl_setopt($curl, CURLOPT_HTTPHEADER, array(
        'Content-type: application/json',
        'Accept: application/json',
        'Authorization: Basic ' . base64_encode($STREAM_SEA_CREDENTIALS)
      ));
      curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
      curl_setopt($curl, CURLOPT_POST, true);
      curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($schema));
      $curl_response = curl_exec($curl);
      $httpcode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
      
      if ($httpcode !== 200) {
        $info = curl_getinfo($curl);
        die('error occured during curl exec. Additional info: ' . var_export($info));
        curl_close($curl);
        
        try {
          $decoded = json_decode($curl_response);
        } catch(Exception $err) {
          
        }
        if($decoded && $decoded->response && $decoded->response->message) {
          throw new Exception($decoded->response->message, $httpcode);
        } else {
          throw new Exception('Unknown error', $httpcode);
        }
      }
      curl_close($curl);
      
      return $curl_response;
      // if (isset($decoded->response->status) && $decoded->response->status == 'ERROR') {
      //     die('error occured: ' . $decoded->response->errormessage);
      // }
      // echo 'response ok!';
      // return var_export($decoded->response);

    }
}