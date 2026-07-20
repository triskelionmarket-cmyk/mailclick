<?php

/**
 * SendingServerTwilio class.
 *
 * Twilio sending server integration for SMS sending.
 *
 * @category   MVC Model
 * @author     MailClick Team <support@mailclick.dev>
 * @copyright  TRISKELION MARKET SRL
 * @license    TRISKELION MARKET SRL
 * @version    1.0
 */

namespace Acelle\Model;

use Acelle\Library\Log as MailLog;

class SendingServerTwilio extends SendingServer
{
    protected $table = 'sending_servers';
    public $twilioClient = null;

    /**
     * Initiate a Twilio client session and return the client object.
     *
     * @return \Twilio\Rest\Client
     */
    public function twilioClient()
    {
        if (!$this->twilioClient) {
            $this->twilioClient = new \Twilio\Rest\Client(
                trim($this->twilio_account_sid),
                trim($this->twilio_auth_token)
            );
        }
        return $this->twilioClient;
    }

    /**
     * Send an SMS message using Twilio.
     *
     * @param string $to
     * @param string $message
     * @return mixed
     */
    public function sendSms($to, $message)
    {
        $response = [
            'debug' => [],
            'result' => null,
            'original_message' => $message,
            'error' => null,
        ];

        try {
            MailLog::debug('Twilio SMS send initiated.', [
                'to' => $to,
                'from' => $this->twilio_from_number,
                'body' => $message,
                'account_sid' => $this->twilio_account_sid,
            ]);

            $response['debug'][] = "[Twilio Debug] Sending SMS to: {$to}, from: {$this->twilio_from_number}, body: {$message}";

            $client = $this->twilioClient();
            $result = $client->messages->create(
                $to,
                [
                    'from' => $this->twilio_from_number,
                    'body' => $message,
                ]
            );

            // Fetch the message status from Twilio after sending
            $messageStatus = $client->messages($result->sid)->fetch();

            MailLog::debug('Twilio SMS send response.', [
                'sid' => $messageStatus->sid,
                'status' => $messageStatus->status,
                'error_code' => $messageStatus->errorCode,
                'error_message' => $messageStatus->errorMessage,
                'to' => $to,
                'body' => $message,
            ]);

            $response['debug'][] = "[Twilio Debug] SMS status: {$messageStatus->status}";
            if ($messageStatus->errorCode !== null) {
                $response['debug'][] = "[Twilio Debug] Twilio error: {$messageStatus->errorCode} - {$messageStatus->errorMessage}";
                $response['error'] = "{$messageStatus->errorCode}: {$messageStatus->errorMessage}";
            }

            $response['result'] = $messageStatus;

            return $response;
        } catch (\Exception $e) {
            MailLog::error('Twilio SMS send failed: ' . $e->getMessage(), [
                'to' => $to,
                'from' => $this->twilio_from_number,
                'body' => $message,
            ]);
            $response['debug'][] = "[Twilio Debug] SMS send failed: " . $e->getMessage();
            $response['error'] = $e->getMessage();
            return $response;
        }
    }

    /**
     * Test Twilio connection for this instance.
     */
    public function test()
    {
        return self::testConnection(
            $this->twilio_account_sid,
            $this->twilio_auth_token
        );
    }

    /**
     * Dummy test connection for Twilio.
     */
    public static function testConnection($sid, $token)
    {
        try {
            $client = new \Twilio\Rest\Client(trim($sid), trim($token));
            $client->api->v2010->accounts($sid)->fetch();
            return true;
        } catch (\Exception $e) {
            throw new \Exception("Twilio connection failed: " . $e->getMessage());
        }
    }

    /**
     * Sync phone numbers (identities) that are authorized to send SMS through Twilio.
     * For Twilio SMS, the identities are the phone numbers that the account is authorized to send from.
     */
    public function syncIdentities()
    {
        try {
            $client = $this->twilioClient();
            $numbers = $client->incomingPhoneNumbers->read();
            
            $identities = [];
            foreach ($numbers as $number) {
                $phoneNumber = $number->phoneNumber;
                // Consider all Twilio phone numbers as verified since they are already owned by the account
                $identities[$phoneNumber] = ['VerificationStatus' => true];
            }
            
            // Add the from number if it's not in the list already
            if (!empty($this->twilio_from_number) && !array_key_exists($this->twilio_from_number, $identities)) {
                $identities[$this->twilio_from_number] = ['VerificationStatus' => true];
            }
            
            $identityStore = $this->getIdentityStore();
            $identityStore->update($identities);
            
            $options = $this->getOptions();
            $options['identities'] = $identityStore->get();
            $this->setOptions($options);
            $this->save();
            
            return true;
        } catch (\Exception $e) {
            MailLog::error('Twilio syncIdentities failed: ' . $e->getMessage());
            return false;
        }
    }
}
