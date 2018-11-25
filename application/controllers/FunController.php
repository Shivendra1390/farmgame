<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class FunController extends CI_Controller {
    //To define the entities dynamically, if new entity added, just need to define here.
    private $game_component_def = array(
        'farmer' => array( 'count' => 1, 'feed_count' => 15, 'is_critical' => 1 ),
        'cow'    => array( 'count' => 2, 'feed_count' => 10, 'is_critical' => 0 ),
        'rabbit' => array( 'count' => 4, 'feed_count' => 8, 'is_critical' => 0 ),
    );

    public function __construct()
    {
        parent::__construct();
        $this->load->library('session');
        $this->load->helper('url');
    }

    public function index()
    {
        $this->load->view('index', array('component' => $this->game_component_def));
    }

    public function play_round()
    {
        // To define the respponse message after each round.
        $response_message = array(
            'message' => "",
            'round' => 1, // Incremental, round number.
            'game_result' => 0, //0: playing, 1: won, 2: lost
            'game_over_reason' => '',
            'component_died' => array(),
            'component_fed_name' => '',
            'component_fed_index' => 0,
        );
        
        // If current round is 0,initializing the commponents.
        if(!$this->session->userdata('entity') || isset($_POST['restart']))
        {
            $sess = $this->session->set_userdata('entity', array());
            $round = $this->session->set_userdata('round', 0);

            foreach ($this->game_component_def as $component => $component_data) {
                for($c = 0; $c < $component_data['count']; $c++){
                    $sess[$component][$c] = array(
                        'max_count' => $component_data['feed_count'],
                        'last_fed_round' => 0,
                        'is_critical' => $component_data['is_critical']
                    );
                }
            }
            $this->session->set_userdata('entity',$sess);
        }
        
        $var_session = $this->session->userdata('entity');

        // Incrementing the round.
        $round = $this->session->userdata('round');
        $round++;
        $this->session->set_userdata('round', $round);

        // Choosing component randomaly. First Choosing component type i.e. if farmer or cow or rabbit.
        $component = array_rand($var_session); 
        // Choose individual component, i.e. if its cow 1 or cow 2 etc.
        $element = array_rand($var_session[$component]);
        // Updating the last fed round number for selected component.
        $var_session[$component][$element]['last_fed_round'] = $round;

        $response_message['round'] = $round;
        $response_message['component_fed_name'] = $component;
        $response_message['component_fed_index'] = $element;
        $response_message['message'] = "Round: " . $round . " Fed to " . $component . "-" . $element;

        // Checking if any component individual died after every round.
        foreach ($var_session as $current_component => $current_component_data) {
            foreach ($current_component_data as $c => $element) {
                if(($round - $current_component_data[$c]['last_fed_round']) >= $current_component_data[$c]['max_count']){
                    // Checking if critical component died, in this case farmer, then game is over.
                    if($current_component_data[$c]['is_critical'] == 1){
                        $response_message['game_result'] = 2;
                        $response_message['game_over_reason'] = $current_component . " Died!";
                    }
                    // Removing died component individual from the list of the array.
                    unset($var_session[$current_component][$c]);

                    $response_message['component_died'][] = array('name' => $current_component, 'index' => ($c + 1));
                    $response_message['message'] .= " AND " . $current_component . "-" . $c . " Died.";
                }
            }
        }
        /*Checking if atleast one individual component in every component type (i.e. cow or rabbit) is still alive.
        And to check round must be less than or equal to 50.*/
        if($response_message['game_result'] == 0 && $round <= 50){
            foreach ($var_session as $current_component => $current_component_data) {
                if(count($current_component_data) == 0){
                    $response_message['game_result'] = 2;
                    $response_message['game_over_reason'] = "All " . $current_component . " Died!";
                    $response_message['message'] .= " Game Over!";
                }
            }
        }
        // To check if atleast one component individual is alive and round is 50, So Game Won.
        if($response_message['game_result'] == 0 && $round == 50){
            $response_message['game_result'] = 1;
            $response_message['message'] .= " You Won!";
        }
        // Updating the session variables.
        if($response_message['game_result'] != 0){
            $this->session->set_userdata('entity',array());
        }else{
            $this->session->set_userdata('entity',$var_session);
        }

        echo json_encode($response_message); exit;
    }
}
?>

