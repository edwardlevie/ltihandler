<?php

require_once __DIR__.'/../OAuth.php';

/*** Much of this taken from http://www.dr-chuck.com/ims/php-simple/dist.zip ***/

/**
 * A Trivial memory-based store - no support for tokens
 */
class TrivialOAuthDataStore extends OAuthDataStore {
    private $consumers = array();

    public function __construct($consumers) { $this->consumers = $consumers; }

    public function lookup_consumer($consumer_key) {
      if (isset($this->consumers[$consumer_key])) {
        return new OAuthConsumer($consumer_key, $this->consumers[$consumer_key], NULL);
      }
      return null;
    }

    public function lookup_token($consumer, $token_type, $token) {
      // We don't use tokens since OAuth is only used for signing
      return new OAuthToken($consumer, "");
    }
}

abstract class LTIHandler {
  /* Runs application code to handle the request */
  abstract protected function doHandleRequest();
  
  private $consumers = array();
  protected $request;
  private $debug = false;

  public function __construct($request) {
    $this->request = $request;
  }

  /* Returns true if $request is an LTI request. */
  public static function isLTIRequest($request) {
    // If the request specifies an LTI message type or LTI version, we consider it an LTI request
    return is_array($request) && (isset($request['lti_message_type']) || isset($request['lti_version']));
  }

  public function addConsumers($consumers) {
    if (!is_array($consumers)) {
      throw new Exception("Invalid consumers array");
    }
    foreach ($consumers as $key => $secret) {
      $this->addConsumer($key, $secret);
    }
  }

  public function addConsumer($key, $secret) {
    if (!is_string($key) || !is_string($secret)) {
      throw new Exception("Invalid consumer key or secret");
    }
    $this->consumers[$key] = $secret;
  }

  /* Returns true if signature is valid and all required fields are valid.
     Takes $consumers of the form array(oauth_consumer_key1 => secret1, ...) */
  public function isValidRequest() {
    // Verify the message signature
    $server = new OAuthServer(new TrivialOAuthDataStore($this->consumers));
    // TODO: add other/all signature methods?
    $server->add_signature_method(new OAuthSignatureMethod_HMAC_SHA1());
    $request = OAuthRequest::from_request(null, null, $this->request);
     
    try {
      $server->verify_request($request);
      return true;
    } catch (Exception $e) {
      if ($this->debug) {
        error_log("Could not handle request: {$e->getMessage()}");
      }
    }
    return false;
  }

  /* Forces the request to be valid before child class can handle the request */
  final public function handleRequest() {
    if (!$this->isValidRequest()) {
      throw new Exception("Invalid LTI request");
    }

    return $this->doHandleRequest();
  }

  /* Enabled error logging of debug messages */
  public function enableDebug() { $this->debug = true; }
}

?>
