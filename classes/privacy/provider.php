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

namespace local_bbb_insights\privacy;

use core_privacy\local\metadata\collection;
use core_privacy\local\request\approved_contextlist;
use core_privacy\local\request\approved_userlist;
use core_privacy\local\request\contextlist;
use core_privacy\local\request\userlist;
use core_privacy\local\request\writer;

/**
 * Privacy API provider implementation for local_bbb_insights.
 *
 * @package     local_bbb_insights
 * @copyright   2026 Emanuel Dickson Wanyonyi <wanyonyi.d.emanuel@gmail.com>
 * @author      Emanuel Dickson Wanyonyi <wanyonyi.d.emanuel@gmail.com>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class provider implements
    \core_privacy\local\metadata\provider,
    \core_privacy\local\request\core_user_data_provider,
    \core_privacy\local\request\plugin\provider {

    /**
     * Document database tables and personal data disclosures.
     *
     * @param collection $collection Privacy metadata collection.
     * @return collection Updated collection.
     */
    public static function get_metadata(collection $collection): collection {
        $collection->add_database_table(
            'local_bbb_insights_attendance',
            [
                'sessionid'       => 'privacy:metadata:attendance:sessionid',
                'courseid'        => 'privacy:metadata:attendance:courseid',
                'userid'          => 'privacy:metadata:attendance:userid',
                'userfullname'    => 'privacy:metadata:attendance:userfullname',
                'useremail'       => 'privacy:metadata:attendance:useremail',
                'role'            => 'privacy:metadata:attendance:role',
                'jointime'        => 'privacy:metadata:attendance:jointime',
                'leavetime'       => 'privacy:metadata:attendance:leavetime',
                'duration'        => 'privacy:metadata:attendance:duration',
                'engagementscore' => 'privacy:metadata:attendance:engagementscore',
            ],
            'privacy:metadata:attendance'
        );

        return $collection;
    }

    /**
     * Get list of contexts containing personal user data for given user ID.
     *
     * @param int $userid Target user ID.
     * @return contextlist Contexts list.
     */
    public static function get_contexts_for_userid(int $userid): contextlist {
        $contextlist = new contextlist();
        $sql = "SELECT c.id
                  FROM {context} c
                  JOIN {course} cr ON cr.id = c.instanceid AND c.contextlevel = :contextlevel
                  JOIN {local_bbb_insights_attendance} a ON a.courseid = cr.id
                 WHERE a.userid = :userid";
        $contextlist->add_from_sql($sql, [
            'contextlevel' => CONTEXT_COURSE,
            'userid'       => $userid,
        ]);
        return $contextlist;
    }

    /**
     * Get list of users stored in given context.
     *
     * @param userlist $userlist Userlist object.
     * @return void
     */
    public static function get_users_in_context(userlist $userlist): void {
        $context = $userlist->get_context();
        if ($context->contextlevel != CONTEXT_COURSE) {
            return;
        }

        $sql = "SELECT userid
                  FROM {local_bbb_insights_attendance}
                 WHERE courseid = :courseid AND userid > 0";
        $userlist->add_from_sql('userid', $sql, ['courseid' => $context->instanceid]);
    }

    /**
     * Export personal user data for approved contexts.
     *
     * @param approved_contextlist $contextlist Approved context list.
     * @return void
     */
    public static function export_user_data(approved_contextlist $contextlist): void {
        global $DB;

        $userid = $contextlist->get_user()->id;
        foreach ($contextlist->get_contexts() as $context) {
            if ($context->contextlevel != CONTEXT_COURSE) {
                continue;
            }

            $records = $DB->get_records('local_bbb_insights_attendance', [
                'courseid' => $context->instanceid,
                'userid'   => $userid,
            ]);

            if (!empty($records)) {
                $exportdata = [];
                foreach ($records as $rec) {
                    $exportdata[] = (object)[
                        'session_id'       => $rec->sessionid,
                        'role'             => $rec->role,
                        'join_time'        => userdate($rec->jointime),
                        'leave_time'       => userdate($rec->leavetime),
                        'duration_seconds' => $rec->duration,
                        'engagement_score' => $rec->engagementscore,
                    ];
                }

                writer::with_context($context)->export_data(
                    [get_string('pluginname', 'local_bbb_insights'), get_string('attendance_logs', 'local_bbb_insights')],
                    (object)['attendance' => $exportdata]
                );
            }
        }
    }

    /**
     * Delete all personal user data in target context.
     *
     * @param \context $context Context object.
     * @return void
     */
    public static function delete_data_for_all_users_in_context(\context $context): void {
        global $DB;

        if ($context->contextlevel == CONTEXT_COURSE) {
            $DB->delete_records('local_bbb_insights_attendance', ['courseid' => $context->instanceid]);
        }
    }

    /**
     * Delete personal user data for user in approved contexts.
     *
     * @param approved_contextlist $contextlist Approved context list.
     * @return void
     */
    public static function delete_data_for_user(approved_contextlist $contextlist): void {
        global $DB;

        $userid = $contextlist->get_user()->id;
        foreach ($contextlist->get_contexts() as $context) {
            if ($context->contextlevel == CONTEXT_COURSE) {
                $DB->delete_records('local_bbb_insights_attendance', [
                    'courseid' => $context->instanceid,
                    'userid'   => $userid,
                ]);
            }
        }
    }

    /**
     * Delete personal user data for list of users in approved context.
     *
     * @param approved_userlist $userlist Approved user list.
     * @return void
     */
    public static function delete_data_for_users(approved_userlist $userlist): void {
        global $DB;

        $context = $userlist->get_context();
        if ($context->contextlevel != CONTEXT_COURSE) {
            return;
        }

        $userids = $userlist->get_userids();
        if (empty($userids)) {
            return;
        }

        [$insql, $inparams] = $DB->get_in_or_equal($userids, SQL_PARAMS_NAMED);
        $inparams['courseid'] = $context->instanceid;

        $DB->delete_records_select('local_bbb_insights_attendance', "courseid = :courseid AND userid {$insql}", $inparams);
    }
}

