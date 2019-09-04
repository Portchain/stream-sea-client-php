
<?php

class PayloadValidationException extends Exception
{
  // Redefine the exception so message isn't optional
  public function __construct($message, $detailedErrors, Exception $previous = null) {
    // some code
    // make sure everything is assigned properly
    parent::__construct($message . ' ' . implode(",", $detailedErrors), 400, $previous);
  }
}
