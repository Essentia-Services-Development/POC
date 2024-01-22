<?php
header('Content-Type: text/event-stream');
header('Cache-Control: no-cache');
header('X-Accel-Buffering: no');
header("Connection: Keep-Alive");
header("Keep-Alive: timeout=300");

ini_set('display_errors', 1);
ini_set('error_reporting', E_ALL);


class PeepSoSSE {

     private $dir = '../../peepso/sse/'; // wp-content/peepso/sse/

     private $user_id = 0;
     private $delay = 5000;                 // miliseconds
     private $timeout = 30000;              // miliseconds
     private $keepalive = 0;                // every N loop(s)
     private $iterations = 0;               // internal iteration count
     private $token = NULL;                 // admin-ajax.php?action=peepso_sse_token

     private $events_dir = NULL;            // directory to monitor

     private $token_expiry = 24 * 60 * 60;  // delete token dir after N seconds

     private $event_id = 1;                 // event count is unique to the user_id/token pair
     private $event_id_file = '';           // path to file that caches the last event id in case of reconnect

     public function __construct() {

         $this->user_id     = isset($_REQUEST['user_id'])   ? intval($_REQUEST['user_id'])  : $this->user_id    ;
         $this->timeout     = isset($_REQUEST['timeout'])   ? intval($_REQUEST['timeout'])  : $this->timeout    ;
         $this->delay       = isset($_REQUEST['delay'])     ? intval($_REQUEST['delay'])    : $this->delay      ;
         $this->keepalive   = isset($_REQUEST['keepalive']) ? intval($_REQUEST['keepalive']): $this->keepalive  ;
         $this->token       = isset($_REQUEST['token'])     ? intval($_REQUEST['token'])    : $this->token      ;

         // invalid user id?
         if(!$this->user_id) {
             $this->send('error_invalid_user_id');
             die();
         }

         // detect symlinked plugin
         if (isset($_SERVER['SCRIPT_FILENAME'])) {
             $basedir = dirname($_SERVER['SCRIPT_FILENAME']);
             if ($basedir !== dirname(__FILE__)) {
                $this->dir = preg_replace('/(\/[^\/]+){2}$/', '/peepso/sse/', $basedir);
             }
         }

         $this->events_dir = $this->dir.'events/'.$this->user_id.'/'.$this->token.'/';

         // token is a timestamp with 10000 - 99999 glued to it - expire it after {token_expiry} seconds
         if( file_exists($this->events_dir) && time() - substr($this->token, 0, 10) >= $this->token_expiry) {

             $events = scandir($this->events_dir);
             if(is_array($events) && count($events)) {
                 foreach($events as $event) {
                     @unlink($event);
                 }
             }

             @rmdir($this->events_dir);
         }

         // assume that token has expired if the directory does not exist
         if(!file_exists($this->events_dir)) {
             $this->send('error_invalid_token');
             die();
         }

         // last succesful event id
         $this->event_id_file = $this->events_dir.'last_event_id';
         if(file_exists($this->event_id_file)) {
             $h = fopen($this->event_id_file, 'r');
             $this->event_id = fread($h, filesize($this->event_id_file));
             $this->event_id++;
         }
         /**
          * Primary loop. Infinite but with built in self-termination based on config;
          */
         $this->send(
             'debug_start',
                 array(
                     'user_id'=>$this->user_id,
                     'delay'=>$this->delay,
                     'timeout'=>$this->timeout,
                     'keepalive'=>$this->keepalive,
                     'max_execution_time' => ini_get('max_execution_time'),
                 )
         );

         while (1) {

             $this->iterations++;

             if ($data = $this->check()) {
                 foreach($data as $event) {
                     $this->send($event);
                 }
             } elseif($this->keepalive>0 && !($this->iterations%$this->keepalive)) {
                 $this->send('keepalive');
             }

             if($this->timeout > 0 && $this->iterations*$this->delay >= $this->timeout ){
                 $this->send('timeout');
                 die();
             }

             if (connection_aborted()) { die(); };

             sleep(intval($this->delay/1000));

         }
     }

     private function check()
     {
         $response = array();

         $events = scandir($this->events_dir);
         if(is_array($events) && count($events)) {
             foreach ($events as $event) {
                 if(in_array($event, array('.','..','last_event_id'))) {
                     continue;
                 }

                 $response[] = $event;
                 unlink($this->events_dir.$event);
             }
         }

        return $response;
    }

     /**
      * Sends a message to buffer
      *
      * @param string $event name of the event
      * @param array $payload optional array of data to send along with the event
      */
     private function send($event, $payload = NULL)
     {

         $event = array(
             'event' => $event,
             'event_id' => $this->event_id,
         );

         if ($payload) {
             $event['payload'] = $payload;
         }

         // cache the event_id and increment
         $h = @fopen($this->event_id_file, 'w');
         @fwrite($h, $this->event_id);

         $this->event_id++;

         // send to buffer
         echo 'data: ' . json_encode($event) . PHP_EOL . PHP_EOL; // two newlines are required

         // flush the crap out of it
         while (ob_get_level() > 0) {
             @ob_end_flush();
         }

         @ob_end_flush();
         @flush();
     }
 }

 $sse = new PeepSoSSE();
// EOF