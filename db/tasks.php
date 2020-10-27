<?php
/**
 * Cron tasks
 *
 * @package    local_badge_sync
 */

defined('MOODLE_INTERNAL') || die();
$tasks = array(
        array(
               'classname' => 'local_badge_sync\task\resend_request',
               'blocking' => 0,
               'minute' => '0',
               'hour' => '*',
               'day' => '*',
               'dayofweek' => '*',
               'month' => '*'
        )
        //NOTE add tasks as needed
);
