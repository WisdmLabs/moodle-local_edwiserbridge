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
 * Provides local_edwiserbridge\external\course_progress_data trait.
 *
 * @package     local_edwiserbridge
 * @category    external
 * @copyright   2021 WisdmLabs (https://wisdmlabs.com/) <support@wisdmlabs.com>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @author      Wisdmlabs
 */

namespace local_edwiserbridge\external;

defined('MOODLE_INTERNAL') || die();

use external_function_parameters;
use external_multiple_structure;
use external_single_structure;
use external_value;
use core_completion\progress;

// require_once($CFG->libdir.'/externallib.php');

/**
 * Trait implementing the external function local_edwiserbridge_course_progress_data
 */
trait eb_test_connection {

    /**
     * Request to test connection
     *
     * @param  string $wpurl   wpurl.
     * @param  string $wptoken wptoken.
     *
     * @return array
     */
    public static function eb_test_connection($wpurl, $wptoken) {
        $params = self::validate_parameters(
            self::eb_test_connection_parameters(),
            array(
                'wp_url' => $wpurl,
                "wp_token" => $wptoken
            )
        );

        $requestdata = array(
            'action'     => "test_connection",
            'secret_key' => $params["wp_token"]
        );

        $apihandler = api_handler_instance();
        $response   = $apihandler->connect_to_wp_with_args($params["wp_url"], $requestdata);

        $status = 0;
        $msg    = isset($response["msg"]) ? $response["msg"] : '';

        if (!$response["error"]) {
            $msg = isset($response["data"]->msg) ? $response["data"]->msg : get_string('wp_test_connection_failed', 'local_edwiserbridge') ;

            if (!isset($response["data"]->status) || !$response["data"]->status) {
                $status = isset($response["data"]->status) ? $response["data"]->status : 0;
                $msg = $response["data"]->msg . get_string('wp_test_connection_failed', 'local_edwiserbridge');
            }
            $status = $response["data"]->status;
        }

        return array("status" => $status, "msg" => $msg);
    }

    /**
     * Request to test connection parameter.
     */
    public static function eb_test_connection_parameters() {
        return new external_function_parameters(
            array(
                'wp_url'   => new external_value(PARAM_TEXT, get_string('web_service_wp_url', 'local_edwiserbridge')),
                'wp_token' => new external_value(PARAM_TEXT, get_string('web_service_wp_token', 'local_edwiserbridge'))
            )
        );
    }

    /**
     * paramters which will be returned from test connection function.
     */
    public static function eb_test_connection_returns() {
        return new external_single_structure(
            array(
                'status' => new external_value(PARAM_TEXT, get_string('web_service_test_conn_status', 'local_edwiserbridge')),
                'msg'    => new external_value(PARAM_RAW, get_string('web_service_test_conn_msg', 'local_edwiserbridge'))
            )
        );
    }
}
