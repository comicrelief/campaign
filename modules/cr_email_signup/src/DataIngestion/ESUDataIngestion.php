<?php

namespace Drupal\cr_email_signup\DataIngestion;
use Drupal\Component\Serialization\Json.php;


/**
 * Email Sign up Data Ingestion class.
 */
class ESUDataIngestion
{

    protected $created_at = null;
    protected $email = null;
    protected $first_name = null;
    protected $last_name = null;
    protected $campaign = null;
    protected $trans_source = null;
    protected $trans_source_url = null;
    protected $trans_type = null;
    protected $list = null;
    protected $user_id = null;

    /**
     * Create a new instance.
     * @param array $data
     * @return void
     */
    public function __construct($data)
    {
        $this->user_id = uniqid();

        $this->created_at = date("Y-m-d");
        $this->campaign = $data['campaign'];

        $current_path = \Drupal::service('path.current')->getPath();
        $current_alias = \Drupal::service('path.alias_manager')->getAliasByPath($current_path);


        $this->trans_source_url = \Drupal::request()->getHost() . $current_alias;

        $this->trans_source = "{$data['campaign']}_ESU_[PageElementSource]";

        $source = (empty($data['source'])) ? "Unknown" : $data['source'];

        $this->trans_source = str_replace(
            ['[PageElementSource]'],
            [$source],
            $data['transSource']
        );

        $this->trans_type = $data['transType'];
        $this->email = $data['email'];
        $this->first_name = $data['firstName'];
        $this->last_name = $data['surname'];
        $this->list = $data['subscribeLists'];
    }

    protected function generateTransactionEvent() {

        return [
            'anonymous_id' => $this->user_id,
            'event' => 'email-subscribe',
            'created_at' => $this->created_at,
            'properties' => [
                'campaign' => $this->campaign,
                'trans_source' => $this->trans_source,
                'trans_source_url' => $this->trans_source_url,
                'trans_type' => $this->trans_type,
                'list' => $this->list
            ]
        ];
    }

    /**
     * Get the user from the transaction
     * @return
    */
    public function getUser() {
        return [
            'anonymous_id' => $this->user_id,
            'created_at' => $this->created_at,
            'traits' => [
                'first_name' => $this->first_name,
                'last_name' => $this->last_name,
                'email' => $this->email,
            ]
        ];
    }


    /**
     * Deliver a populated message to the queue.
     */
    public function deliver() {
        $this->post($this->getUser());
    }


    /**
     * Send a message to the queue service.
     *
     * @param string $name
     * @param array $queue_message
     */
    protected function send($name, $queue_message) {
        try {
            $queue_factory = \Drupal::service('queue');
            /* @var \Drupal\rabbitmq\Queue\Queue $queue */
            $queue = $queue_factory->get($name);

            if (FALSE === $queue->createItem($queue_message)) {
                throw new \Exception("createItem Failed. Check Queue.");
            }
        } catch (\Exception $e) {
            \Drupal::logger('cr_email_signup')->error(
                "Unable to queue message. Error was: " . $e->getMessage()
            );
        }
    }

    /**
     * Post data to endpoint.
     * @param array $data
     */
    protected function post($data) {
        $settings = \Drupal::config('cr_email_signup.settings');
        $data_ingestion_endpoint = $settings->get('endpoint.dev');

        $client = \Drupal::httpClient();
        try {
            $request = $client->post($data_ingestion_endpoint, Json::encode($data));

            $response = Json::decode($request->getBody());
            if ($response['statusCode'] !== 200) {
                throw new \Exception("Post message Failed.");
            }
        } catch (\Exception $e) {
            \Drupal::logger('cr_email_signup')->error(
                "Unable to send message to endpoint. Error was: " . $e->getMessage()
            );
        }


    }
}
