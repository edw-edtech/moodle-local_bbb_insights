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

namespace local_bbb_insights\task;

use core\task\scheduled_task;
use local_bbb_insights\analytics_manager;

/**
 * Scheduled task for synchronizing BigBlueButton insights, sessions, and recording quotas.
 *
 * @package     local_bbb_insights
 * @copyright   2026 Emanuel Dickson Wanyonyi <wanyonyi.d.emanuel@gmail.com>
 * @author      Emanuel Dickson Wanyonyi <wanyonyi.d.emanuel@gmail.com>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class sync_insights extends scheduled_task {

    /**
     * Return the task name for administrative displays.
     *
     * @return string Task name.
     */
    public function get_name(): string {
        return get_string('task_sync_insights', 'local_bbb_insights');
    }

    /**
     * Execute synchronization process.
     *
     * @return void
     */
    public function execute(): void {
        mtrace('== Starting BigBlueButton Insights Synchronisation ==');

        $result = analytics_manager::sync_insights();

        mtrace("Synced {$result['sessions_synced']} sessions.");
        mtrace("Synced {$result['recordings_synced']} recordings.");
        mtrace('== BigBlueButton Insights Synchronisation Completed ==');
    }
}

