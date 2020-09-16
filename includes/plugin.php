<?php

namespace GroundhoggGetEmails;

use Groundhogg\Extension;
use GroundhoggGetEmails\Steps\Benchmarks\New_Contact;

class Plugin extends Extension
{

    /**
     * Override the parent instance.
     *
     * @var Plugin
     */
    public static $instance;

    public $license_manager;

    /**
     * Extension constructor.
     */
    public function __construct()
    {
        if ($this->dependent_plugins_are_installed()) {

            $this->register_autoloader();

            if (!did_action('groundhogg/init/v2')) {
                add_action('groundhogg/init/v2', [$this, 'init']);
            } else {
                $this->init();
            }
        }
    }

    /**
     * Include any files.
     *
     * @return void
     */
    public function includes()
    {
        require GROUNDHOGG_GETEMAILS_PATH . '/includes/functions.php';

        if (!class_exists('ActionScheduler_Versions')) {
            require GROUNDHOGG_GETEMAILS_PATH . '/includes/lib/action-scheduler/action-scheduler.php';
        }

    }

    /**
     * Init any components that need to be added.
     *
     * @return void
     */
    public function init_components()
    {
        /* By default, we poll each night at midnight */
        $start = apply_filters('groundhogg/getemails/polling/start', strtotime('today'));

        $interval = apply_filters('groundhogg/getemails/polling/interval', DAY_IN_SECONDS);

        /* We use Action Schedule if available, or fall back to WP Scheduler */

        if(function_exists('as_next_scheduled_action')) {

            $scheduled = as_next_scheduled_action('groundhogg/getemails/poll');

            if(!$scheduled) as_schedule_recurring_action($start, $interval, 'groundhogg/getemails/poll');
        }

        else {

            $scheduled = wp_next_scheduled('groundhogg/getemails/poll');

            if(!$scheduled) wp_schedule_event($start, $interval, 'groundhogg/getemails/poll');
        }

    }

    /**
     * Register additional replacement codes.
     *
     * @param \Groundhogg\Replacements $replacements
     */
    public function add_replacements($replacements)
    {
        $wc_replacements = new Replacements();

        foreach ($wc_replacements->get_replacements() as $replacement) {

            $replacements->add($replacement['code'], $replacement['callback'], $replacement['description']);
        }
    }

    /**
     * @param \Groundhogg\Steps\Manager $manager
     */
    public function register_funnel_steps($manager)
    {
        $manager->add_step(new New_Contact());

    }

    /**
     * Get the ID number for the download in EDD Store
     *
     * @return int
     */
    public function get_download_id()
    {
        //Silence
    }

    /**
     * Get the version #
     *
     * @return mixed
     */
    public function get_version()
    {
        return GROUNDHOGG_GETEMAILS_VERSION;
    }

    /**
     * @return string
     */
    public function get_plugin_file()
    {
        return GROUNDHOGG_GETEMAILS__FILE__;
    }

    /**
     * Add settings to the settings page
     *
     * @param $settings array[]
     * @return array[]
     */
    public function register_settings($settings)
    {
        $settings['gh_ge_api_key'] = array(
            'id' => 'gh_ge_api_key',
            'section' => 'getemails_settings',
            'label' => _x('API Key', 'settings', 'groundhogg'),
            'desc' => _x('Your API Key is available in your GetEmails dashboard under Account -> API Credentials', 'settings', 'groundhogg'),
            'type' => 'input',
            'atts' => array(
                'id' => 'gh_ge_api_key',
                'name' => 'gh_ge_api_key',
                'placeholder' => 'xxxx976d783a9231ea1512xxxx'
            ),
        );

        $settings['gh_ge_api_id'] = array(
            'id' => 'gh_ge_api_id',
            'section' => 'getemails_settings',
            'label' => _x('API ID', 'settings', 'groundhogg'),
            'desc' => _x('Your API ID is available in your GetEmails dashboard under Account -> API Credentials', 'settings', 'groundhogg'),
            'type' => 'input',
            'atts' => array(
                'id' => 'gh_ge_api_id',
                'name' => 'gh_ge_api_id',
                'placeholder' => 'xxxxdfabc0c747xxxx'
            ),
        );

        return $settings;

    }

    /**
     * Add settings sections to the settings page
     *
     * @param $sections array[]
     * @return array[]
     */
    public function register_settings_sections($sections)
    {

        $sections['getemails_settings'] = array(

            'id' => 'getemails_settings',

            'title' => _x('GetEmails Settings', 'settings_tabs', 'groundhogg'),

            'tab' => 'getemails'
        );

        return $sections;

    }

    /**
     * Add settings tabs to the settings page
     *
     * @param $tabs array[]
     * @return array[]
     */
    public function register_settings_tabs($tabs)
    {

        $tabs['getemails'] = array(

            'id' => 'getemails',

            'title' => _x('GetEmails', 'settings_tabs', 'groundhogg')

        );

        return $tabs;

    }

    /**
     * Register autoloader.
     *
     * Groundhogg autoloader loads all the classes needed to run the plugin.
     *
     * @since 1.6.0
     * @access private
     */
    protected function register_autoloader()
    {
        require GROUNDHOGG_GETEMAILS_PATH . 'includes/autoloader.php';
        Autoloader::run();
    }
}

Plugin::instance();