<?php
defined('BASEPATH') or exit('No direct script access allowed');
require(APPPATH . 'third_party/vendor/autoload.php');
require_once(APPPATH . 'third_party/vendor/telnyx/telnyx-php/init.php');

use Twilio\TwiML\VoiceResponse;
use Twilio\TwiML\MessagingResponse;
// use Telnyx;

class Webhook extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->helper('url');
        $this->load->model('M_property');
        \Telnyx\Telnyx::setApiKey(TELNYX_API_KEY);

        session_destroy();
    }

    function sms_receive()
    {
        if ($this->input->method() == 'post') {
            $response = new MessagingResponse();
            $requests = json_decode(file_get_contents('php://input'), true);
            // $this->load->helper('file');

            $sentByDiraLeads = "\nThis SMS was sent by DiraLeads";
            $virtual_number = $requests['data']['payload']['to'];
            $text = $requests['data']['payload']['text'] . $sentByDiraLeads;

            $result = $this->db->select('*')
                ->from('virtual_numbers')
                ->where('virtual_numbers.number', $virtual_number)
                ->join('properties', 'properties.vn_id = virtual_numbers.id', 'left')
                ->join('users', 'users.id = properties.user_id', 'left')
                ->get()->result_array();

            $owner_number = $result[0]['country_code'] . $result[0]['mobile'];

            // $data = json_encode($requests);
            // if (!write_file(FCPATH . 'webhook.txt', $data, 'a')) {
            //     // echo 'Unable to write the file';
            // } else {
            //     // echo $data;
            // }

            \Telnyx\Call::Create([
                "from" => "+15166361518", // Your Telnyx number
                "to" => $owner_number,
                "text" => $text
            ]);

            //return texml
            // $response->message(
            //     "hello",
            //     [
            //         "to" => '+17606165259'
            //     ]
            // );

            // return $this->output
            //     ->set_content_type('text/xml')
            //     ->set_output($response);
        }
    }

    public function email($token)
    {
        $token = explode(':', base64_decode(urldecode($token)));

        $this->db->where('token', $token[1]);

        switch ($token[0]) {
            case 'agent':
                $this->db->from('agents');
                break;
            default:
                $this->db->from('users');
                break;
        }

        $user = $this->db
            ->select('id, name, email, mobile')
            ->get()
            ->row_array();

        if (is_null($user)) {
            show_404();
        } else {
            if (
                $this->input->method() == 'post'
                && ($this->input->post('password')
                    === $this->input->post('cnf_password'))
            ) {
                $this->db->where('token', $token[1]);
                $this->db->set('token', null);
                $this->db->set('password', sha1($this->input->post('password')));
                $this->db->update('users');
                redirect('login');
            } else {
                if (($this->input->method() == 'post') && ($this->input->post('password')
                    != $this->input->post('cnf_password')))
                    $error = "Password Not matched";
                else if (($this->input->method() == 'post') && ($this->input->post('password') == '' || $this->input->post('cnf_password') == ''))
                    $error = "Password Required";
                $this->load->view('new_password', compact('error'));
            }
        }
    }

    public function incoming_call() // manage all incoming calls from customers
    {

        $requests = $this->input->post();
        // $requests = json_decode(file_get_contents('php://input'), true);

        // return $this->output
        //     ->set_content_type('application/json')
        //     ->set_output($requests['To']);


        $virtual_number = $this->input->post('To');
        // $virtual_number = $requests['To'];
        $query = $this->db->select('*')
            ->from('virtual_numbers')
            ->where('virtual_numbers.number', $virtual_number)
            ->join('properties', 'properties.vn_id = virtual_numbers.id', 'left')
            ->join('users', 'users.id = properties.user_id', 'left')
            ->get();

        // $data = json_encode($requests);
        if (!write_file(FCPATH . 'webhook.txt', $virtual_number, 'a')) {
            // echo 'Unable to write the file';
        } else {
            // echo $data;
        }

        $voiceRes = new VoiceResponse();
        if ($query !== FALSE && $query->num_rows() > 0) {
            $result = $query->result_array();
            $owner_number = $result[0]['country_code'] . $result[0]['mobile'];
            if (!write_file(FCPATH . 'webhook.txt', $owner_number, 'a')) {
                // echo 'Unable to write the file';
            } else {
                // echo $data;
            }

            $isOwnerAvailable = true;

            //Make a response for the incoming call
            if ($isOwnerAvailable) {
                // $voiceRes->say("Thanks for choosing DiraLeads, we are now connecting you with the rental's owner");
                $voiceRes->play('https://diraleads.com/assets/audios/Youre%20being%20connected.mp3');

                $roomName = "diraLeads2020";

                $dial = $voiceRes->dial('');
                $dial->number(
                    $owner_number,
                    [
                        'url' => base_url() . 'webhook/call_receive',
                    ]
                );
            } else {
                // $voiceRes->say("The rental owner is not available now, please try to call him when he is available");
                $voiceRes->play('https://diraleads.com/assets/audios/Error.mp3');
            }
        } else {
            // $voiceRes->say("This is number is not assigned to property");
            $voiceRes->play('https://diraleads.com/assets/audios/Error.mp3');
        }

        //return response to Telnyx
        return $this->output
            ->set_content_type('text/xml')
            ->set_output($voiceRes);
    }

    public function call_receive() // manage actions when the customer receives outbounding calls
    {
        $voiceRes = new VoiceResponse();

        // $voiceRes->say("this is a caller from DiraLeads");
        $voiceRes->play('https://diraleads.com/assets/audios/Caller%20from%20DiraLeads.mp3');

        return $this->output
            ->set_content_type('text/xml')
            ->set_output($voiceRes);
    }

    public function subscriber_receive() // manage actions when the subscriber receives outbounding calls
    {

        // $job_id = $this->input->get('job_id');
        // $job = $this->db->where('id', $job_id)->get('rental_call_queue');
        // $subscriber = $this->db->select('country_code, mobile')
        //     ->from('users')
        //     ->where('id', $job['user_id'])
        //     ->get()->row();
        // $number = $subscriber['country_code'] . $subscriber['mobile'];

        $voiceRes = new VoiceResponse();

        $voiceRes->say("New renal is listed for you");
        // $voiceRes->play('https://diraleads.com/assets/audios/Caller%20from%20DiraLeads.mp3');

        return $this->output
            ->set_content_type('text/xml')
            ->set_output($voiceRes);
    }
}
