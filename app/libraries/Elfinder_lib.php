<?php

if (!defined('BASEPATH')) exit('No direct script access allowed');


class Elfinder_lib
{
  public function __construct($opts)
  {
      

        // Start output buffering
        ob_start();

        // Initialize and run the connector
        try {
            $connector = new elFinderConnector(new elFinder($opts));
            $connector->run();
        } catch (Exception $e) {
            // Handle exceptions and prepare error response
            $response_data = array('error' => $e->getMessage());
            $json_response = json_encode($response_data);
        }

        // Get the buffered output
        if (!isset($json_response)) {
            $json_response = ob_get_clean();
        } else {
            ob_end_clean();
        }

        // Clean the output buffer
        ob_clean();

        // Send the JSON response with proper headers
        header('Content-Type: application/json');
        echo $json_response;
        exit;
    
  }
}
