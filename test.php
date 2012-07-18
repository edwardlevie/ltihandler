<?php

require_once 'LTIHandler.php';

function _LTIHandler_error_handler($errno, $errstr, $errfile, $errline) {
  if ($errno === E_RECOVERABLE_ERROR) {
    throw new Exception('Caught error: '.$errstr);
  }
}
set_error_handler('_LTIHandler_error_handler');

echo "<pre>Testing LTIHandler...\n";

class TestConcreteLTIHandler extends LTIHandler {
  protected function doHandleRequest() {
    // Do nothing
  }
}

$l = new TestConcreteLTIHandler();

try {
  // Test isLTIRequest() //
  if (LTIHandler::isLTIRequest('not an lti request')) {
    throw new Exception('isLTIRequest was wrong');
  }

  // Test addConsumers() //
  // Make sure we can provide a good consumers array
  $l->addConsumers(array('key1' => 'secret1', 'key2' => 'secret2'));
  $l->addConsumers(array('key3' => 'secret3', 'key4' => 'secret4'));
  // Make sure we can't provide a bad consumers array
  try {
    $l->addConsumers(null);
    throw new Exception('Was able to provide a null consumers array');
  } catch (Exception $e) {
    // Consume exception
  }

  // Test addConsumer() //
  // Make sure we can provide good consumers
  $l->addConsumer('good_key1', 'good_secret1');
  $l->addConsumer(12345, 67890);
  // Make sure we can't provide a bad consumer
  try {
    $l->addConsumer(array(), null);
    throw new Exception('Was able to provide a bad consumer');
  } catch (Exception $e) {
    // Consume exception
  }

  // Test isValidRequest() //
  // TODO: test with a good request
  // Make sure isValidRequest fails on a known bad request
  if ($l->isValidRequest()) {
    throw new Exception('Known bad request was reported valid');
  }

  // Test handleRequest() //
  // TODO: test with a good request
  // Make sure a known bad request fails
  try {
    $l->handleRequest();
    throw new Exception('Was able to handle known bad request');
  } catch (Exception $e) {
    // Consume exception
  }

  echo 'Success';
} catch(Exception $e) {
  echo 'Failure: ' . $e->getMessage();
}
echo "</pre>";
