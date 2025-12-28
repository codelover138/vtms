<?php defined('BASEPATH') OR exit('No direct script access allowed');


use Twilio\Rest\Client;

class Tec_twilio
{
    protected $CI;
    protected $client;

    public function __construct() {
        $this->CI =& get_instance();
        $this->CI->load->config('twilio');

        $account_sid = $this->CI->config->item('twilio_account_sid');
        $auth_token = $this->CI->config->item('twilio_auth_token');

        $this->client = new Client($account_sid, $auth_token);
    }
    public function send($to, $from, $body)

    {
        try {
            $this->client->messages->create($to, [
                'messagingServiceSid' => "MGcd4f7f97a78c5439720957911c3c9cd3",
                'body' => $body
            ]);
            return true;
        } catch (\Twilio\Exceptions\TwilioException $e) {
            log_message('error', 'Twilio Error: ' . $e->getMessage());
            return false;
        }
    }
}
