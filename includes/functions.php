<?php

namespace GroundhoggGetEmails;

use Groundhogg\Contact;
use Groundhogg\Plugin as GHPlugin;

use function Groundhogg\generate_contact_with_map;
use function Groundhogg\get_contactdata;

if (!defined('ABSPATH')) exit;

/**
 * Poll the GetEmails API
 */

add_action('groundhogg/getemails/poll', __NAMESPACE__ . '\ghge_getemails_poll', 10);

function ghge_getemails_poll()
{
    $url = apply_filters('groundhogg/getemails/api/url', "https://app.getemails.com/api/v1/contacts/recent");

    $apiKey = GHPlugin::$instance->settings->get_option('gh_ge_api_key', false);
    $apiId = GHPlugin::$instance->settings->get_option('gh_ge_api_id', false);

    if (empty($apiKey) || empty($apiId)) {

        error_log('API Key or API ID is missing from GetEmails settings. Check they are entered, correct and saved.');

        return;
    }

    $query = apply_filters('groundhogg/getemails/api/url/query', ['api_id' => $apiId, 'api_key' => $apiKey]);

    $url = $url . '?' . http_build_query($query);

    $response = wp_remote_get($url, array(
            'method' => 'GET',
            'timeout' => 10,
            'headers' => array()
        )
    );

    if (is_wp_error($response)) {

        error_log($response->get_error_message());

    } else {

        $data = json_decode(wp_remote_retrieve_body($response));

        if (isset($data->contacts) && !empty($data->contacts)) {

            foreach ($data->contacts as $contact) {

                $email = isset($contact->email) ? $contact->email : false;

                if ($email) {

                    /* Check if contact exists in Groundhogg */
                    $ghContact = get_contactdata($email);

                    /* Contact exists, add some meta */
                    if ($ghContact) {
                        add_getemails_contact_meta($contact, $ghContact);


                    }
                    /* New contact, add them to Groundhogg */
                    else {

                        add_getemails_contact($contact);
                    }
                }

            }
        }
    }
}

/**
 * Add the new GetEmails contact to our Groundhogg Contacts
 *
 * @param $contact
 * @throws \Exception
 */
function add_getemails_contact($contact)
{

    /* Map in case of changes to either GetEmails or Groundhogg */

    $map = [
        'first_name' => 'first_name',
        'last_name' => 'last_name',
        'email' => 'email'
    ];

    $map = apply_filters('groundhogg/getemails/contact/map', $map);

    $ghContact = generate_contact_with_map($contact, $map);

    if($ghContact) {

        add_getemails_contact_meta($contact, $ghContact);

        do_action('groundhogg/getemails/contact/added', $ghContact->get_email());
    }
}

/**
 * Add extra meta data from GetEmails to our Groundhogg Contact
 *
 * @param $contact
 * @param $ghContact
 */
function add_getemails_contact_meta($contact, $ghContact) {

    $customMeta = ['landing_page_url', 'landing_page_domain', 'page_title', 'referrer', 'email_domain'];

    $customMeta = apply_filters('groundhogg/getemails/contact/meta', $customMeta);

    foreach($customMeta as $meta) {

        if(isset($contact->{$meta}) && !empty($contact->{$meta})) {

            $ghContact->update_meta($meta, $contact->{$meta});
        }
    }
}




