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
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle. If not, see <http://www.gnu.org/licenses/>.

/**
 *
 * @package    local_badge_sync
 * @author     Stephan Lorbek
 * @copyright  2020 Stephan Lorbek
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_badge_sync\task;

class resend_request extends \core\task\scheduled_task
{

    public function get_name()
    {
        return get_string('cron_retry', 'local_badge_sync');
    }

    public function execute()
    {
        global $DB;
        $records = $DB->get_records('local_badge_sync', array());
        foreach ($records as $record)
        {
            global $PAGE;
            $payload = $record->payload;
            if (!get_config('local_badge_sync', 'payload'))
            {
                $payload = array();
            }
            $ch = curl_init($record->url);
            $postString = http_build_query($payload, '', '&');
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $postString);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            $response = curl_exec($ch);
            $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            
            if($httpcode == 200)
            {
				$DB->delete_records('local_badge_sync',  array('id' => $record->id));
			}
        }
    }
}

