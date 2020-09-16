<?php

namespace GroundhoggGetEmails\Steps\Benchmarks;

use Groundhogg\Steps\Benchmarks\Benchmark;
use Groundhogg\Plugin;
use Groundhogg\Step;
use Groundhogg\Contact;
use Groundhogg\Html;

use function Groundhogg\get_contactdata;
use function GroundhoggGetEmails\ghmg_get_webhook_address;

if ( ! defined( 'ABSPATH' ) ) exit;

class New_Contact extends Benchmark
{
    public function __construct()
    {
        parent::__construct();
      
    }
    
    protected function get_complete_hooks()
    {
        return [ 
            'groundhogg/getemails/contact/added' => 1
        ];
    }

    protected function get_the_contact()
    {
        return get_contactdata( $this->get_data( 'email' ) );
    }
      
    public function setup($email)
    {
        
        $this->add_data( 'email', $email );
        
    }

    protected function can_complete_step()
    {   
        return true;
    }
    
    public function get_name()
    {
        return _x( 'New Contact', 'step_name', GROUNDHOGG_GETEMAILS_TEXT_DOMAIN );
    }

    public function get_type()
    {
        return 'getemails_new_contact';
    }

    public function get_description()
    {
        
        return _x( "Runs when a new contact is created from GetEmails", 'step_description', GROUNDHOGG_GETEMAILS_TEXT_DOMAIN );
    }
    
     /**
     * @param $step Step
     */
    public function settings( $step )
    {
        
        echo "<p>".$this->get_description()."</p>";
                         
    }
    
    /**
     * Save the step settings
     *
     * @param $step Step
     */
    public function save( $step )
    {
        //Silence
    }
    
  
    public function get_icon()
    {
        return GROUNDHOGG_GETEMAILS_ASSETS_URL . '/images/get-emails.png';
    }
    
}