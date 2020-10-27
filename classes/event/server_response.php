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

namespace local_badge_sync\event;

defined('MOODLE_INTERNAL') || die();

class server_response extends \core\event\base {

    protected function init() {
        $this->data['crud'] = 'u';
        $this->data['edulevel'] = self::LEVEL_PARTICIPATING;
        $this->data['objecttable'] = 'data_records';
    }

    public function get_description() {
        return "Post request (" . $this->other['requesttype'] . ") from this system has returned with HTTP " . $this->other['httpcode'] . " from target (" . $this->other['target'] .")";
    }

    public static function get_name() {
        return 'Sent POST request to target';
    }

    public function get_url() {
        return new \moodle_url('/course/view.php', array('id' => $this->courseid));
    }

    protected function get_legacy_eventdata() {
        $eventdata = new \stdClass();
        $eventdata->cmid       = $this->objectid;
        $eventdata->courseid   = $this->courseid;
        $eventdata->userid     = $this->userid;
        return $eventdacta;
    }

    protected function get_legacy_logdata() {
        return array ($this->courseid, "course", "HTTP Request", "view.php?id=" . $this->courseid,
                "", $this->objectid);
    }
}
