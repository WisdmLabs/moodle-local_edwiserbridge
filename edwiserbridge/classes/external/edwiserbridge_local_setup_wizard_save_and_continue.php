<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.
/**
 * Provides edwiserbridge_local\external\course_progress_data trait.
 *
 * @package     edwiserbridge_local
 * @category    external
 * @copyright   2018 Wisdmlabs
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_edwiserbridge\external;
defined('MOODLE_INTERNAL') || die();

use external_function_parameters;
use external_multiple_structure;
use external_single_structure;
use external_value;
use core_completion\progress;

require_once($CFG->dirroot.'/local/edwiserbridge/classes/class-setup-wizard.php');

/**
 * Trait implementing the external function edwiserbridge_local_course_progress_data
 */
trait edwiserbridge_local_setup_wizard_save_and_continue {

    /**
     * Returns description of edwiserbridge_local_get_course_enrollment_method() parameters
     *
     * @return external_function_parameters
     */
    public static function edwiserbridge_local_setup_wizard_save_and_continue_parameters() {
        return new external_function_parameters(
            array(
                // 'data' => new external_value(PARAM_RAW, get_string('web_service_name', 'local_edwiserbridge')),
                /*'current_step' => new external_value(PARAM_RAW, get_string('web_service_name', 'local_edwiserbridge')),
                'next_step' => new external_value(PARAM_RAW, get_string('web_service_name', 'local_edwiserbridge')),
                'is_next_sub_step' => new external_value(PARAM_RAW, get_string('web_service_name', 'local_edwiserbridge')),*/
                'data' => new external_value(PARAM_RAW, get_string('web_service_name', 'local_edwiserbridge')),
            )
        );
    }

    /**
     * Get list of active course enrolment methods for current user.
     *
     * @param int $courseid
     * @return array of course enrolment methods
     * @throws moodle_exception
     */
    public static function edwiserbridge_local_setup_wizard_save_and_continue( $data ) {
        global $DB,$CFG;

        /*// self::validate_context(context_system::instance());

        // Check if Moodle manual enrollment plugin is disabled.
        $enrolplugins = explode(',', $CFG->enrol_plugins_enabled);
        if (! in_array('manual', $enrolplugins)) {
            throw new \moodle_exception('plugininactive');
        }

        $response = array();
        $result = $DB->get_records('enrol', array('status'=> 0, 'enrol'=>'manual'), 'sortorder,id');

        foreach ($result as $instance) {
            $response[] = array(
                'courseid' => $instance->courseid,
                'enabled'  => 1
            );
        }

        return $response;*/

// var_dump($current_step);
// var_dump($next_step);
// var_dump($is_next_sub_step);



        $data = json_decode( $data );

        $current_step = $data->current_step;
        $next_step = $data->next_step;
        $is_next_sub_step = $data->is_next_sub_step;

        $setup_wizard_handler = new \eb_setup_wizard();
        $steps = $setup_wizard_handler->eb_setup_wizard_get_steps();


        // Check if there are any sub steps available. 
        $function = $steps[$next_step]['function'];




       switch ( $current_step ) {
           case 'web_service':
               // Create web service and update data in EB settings
                $settingshandler = new \eb_settings_handler();
                // Get main admin user

                $adminuser = get_admin();

                if ( isset( $data->service_name ) ) {
                    $response = $settingshandler->eb_create_externle_service( $data->service_name , $adminuser->id );
                }

               break;

            case 'wordpress_site_details':

                if ( isset( $data->site_name ) && isset( $data->url ) && ! $data->existing_service ) {

                    $token = isset($CFG->edwiser_bridge_last_created_token) ? $CFG->edwiser_bridge_last_created_token : ' - ';
                   // Update Moodle Wordpress site details.
                    $connectionsettings[$data->site_name] = array(
                        "wp_url"   => $data->url,
                        "wp_token" => $token,
                        "wp_name"  => $data->site_name
                    );

                    set_config( 'eb_connection_settings', serialize( $connectionsettings ) );
                    set_config( 'eb_setup_wp_site_name', $data->site_name );
                }


               break;


            case 'user_and_course_sync':
                if ( ! $data->existing_site ) {
                    global $CFG;

                   // Update Moodle Wordpress site details.
                    $existingsynchsettings = isset($CFG->eb_synch_settings) ? unserialize($CFG->eb_synch_settings) : array();
                    $synchsettings = $existingsynchsettings;
                    $sitename =  $CFG->eb_setup_wp_site_name;

                    $synchsettings[$sitename] = array(
                        "course_enrollment"    => $data->course_enrollment,
                        "course_un_enrollment" => $data->course_unenrollment,
                        "user_creation"        => $data->user_creation,
                        "user_deletion"        => $data->user_deletion,
                        "course_creation"      => $data->course_creation,
                        "course_deletion"      => $data->course_deletion,
                        "user_updation"        => $data->user_updation,
                    );

                    set_config( 'eb_synch_settings', serialize( $synchsettings ) );
                }

               break;


           
           default:

               break;
       }




// var_dump($function);

        // Check current step.
        // Check if there is any data to be saved.



        // get next step.

        // $next_step_html = $setup_wizard_handler->eb_setup_plugin_configuration( 1 );

// var_dump($next_step_html);


        /*
        * There are multiple steps inside 1 step which are listed below.
        * 1. Web sevice
        *    a. web service
        *    b. WP site details
        *
        * 2. user and course sync
        *    a. User and course sync
        *    b. success screens
        */



        $next_step_html = $setup_wizard_handler->$function( 1 );



// var_dump($next_step_html);



        // get next step html

        // check if we need to vhange step.

        // return html and step id.


        $response = array(
            'html_data' => $next_step_html,
        );

        return $response;

    }

    /**
     * Returns description of edwiserbridge_local_get_course_enrollment_method() result value
     *
     * @return external_description
     */
    public static function edwiserbridge_local_setup_wizard_save_and_continue_returns() {
        new external_single_structure(
            array(
                'html_data' => new external_value(PARAM_RAW, 'id of course'),
            )
        );
    }
}