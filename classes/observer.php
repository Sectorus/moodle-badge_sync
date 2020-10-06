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
defined('MOODLE_INTERNAL') || die();

use moodle_url;

/**
 * Email signup notification event observers.
 *
 * @package    local_badge_sync
 * @author     Stephan Lorbek
 * @copyright  2020 Stephan Lorbek
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

class local_badge_sync_observer
{

    public static function request_handling($url, $payload)
    {
        if (!get_config('local_badge_sync', 'payload'))
        {
            $payload = array();
        }
        $ch = curl_init($url);
        $postString = http_build_query($payload, '', '&');
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postString);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($ch);
        $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        return $response;
    }

    public static function badge_awarded(\core\event\badge_awarded $event)
    {
        global $DB;
        $userid = $event->relateduserid;
        $result = array();

        $user_db = $DB->get_record('user', array(
            'id' => $userid
        ));
        
        $result['event'] = 'badge_awarded';
        $result['username'] = $user_db->username;
        $result['email'] = $user_db->email;
        $result['courseid'] = $event->courseid;
        $result['badgeid'] = $event->objectid;
		$result['expiredate'] = $event->other['dateexpire'];
		$result['issuedate'] = $event->timecreated;

        $payload = [
			'json' => json_encode((array) $event),
        ];

        $t = trim(json_encode($result) , '[]');
        $url = get_config('local_badge_sync', 'target_post') . urlencode($t);
        $response = local_badge_sync_observer::request_handling($url, $payload);
        return true;
    }

    public static function course_created(\core\event\course_created $event)
    {
		global $DB;
		$sql = "SELECT mcc.name FROM {course_categories} mcc INNER JOIN {course} mc ON(mcc.id = mc.category) WHERE mc.id = " . $event->courseid; 
		$data = $DB->get_record_sql($sql);

        $result = ['event' => "course_created", 'coursename' => $event->other['fullname'], 'course_id' => $event->courseid, 'course_category' => $data->name,];
        $t = trim(json_encode($result) , '[]');

        $url = get_config('local_badge_sync', 'target_post') . urlencode($t);
        $response = local_badge_sync_observer::request_handling($url, $payload);
        return true;
    }

    public static function badge_created(\core\event\badge_created $event)
    {
        global $DB;
        $data = $DB->get_record('badge', array(
            'id' => $event->objectid,
        ));
        $badgeurl = moodle_url::make_webservice_pluginfile_url($event->contextid, 'badges', 'badgeimage', $event->objectid, '/', 'f1')
            ->out(false);
        $badgeurl = str_replace("/webservice", "", $badgeurl);
		
		//Fields not used anymore 'courseid' => $data->courseid, 'expiredate' => $data->expiredate, 
        $result = [
        'event' => "badge_created", 'id' => $event->objectid, 'name' => $data->name, 'badgeurl' => $badgeurl, ];
        $t = trim(json_encode($result) , '[]');

        $url = get_config('local_badge_sync', 'target_post') . urlencode($t);
        $response = local_badge_sync_observer::request_handling($url, $payload);
        return true;
    }
}
