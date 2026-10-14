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

namespace local_bbb_insights;

/**
 * Analytics and storage manager for BigBlueButton Insights.
 *
 * @package     local_bbb_insights
 * @copyright   2026 Emanuel Dickson Wanyonyi <wanyonyi.d.emanuel@gmail.com>
 * @author      Emanuel Dickson Wanyonyi <wanyonyi.d.emanuel@gmail.com>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class analytics_manager {

    /**
     * Retrieve high-level executive statistics for dashboard cards.
     *
     * @return object Aggregated statistics object.
     */
    public static function get_overview_stats(): object {
        global $DB;

        // Total sessions count.
        $sessioncount = $DB->count_records('local_bbb_insights_sessions');

        // Total unique participants tracked.
        $attendeecount = $DB->count_records_select(
            'local_bbb_insights_attendance',
            'id > 0',
            null,
            'COUNT(DISTINCT userid)'
        );

        // Total recordings count and storage consumption in bytes.
        $recstats = $DB->get_record_sql(
            "SELECT COUNT(id) AS total_count, COALESCE(SUM(filesize), 0) AS total_bytes
               FROM {local_bbb_insights_recordings}"
        );
        $recordingcount = (int)($recstats->total_count ?? 0);
        $storageusedbytes = (int)($recstats->total_bytes ?? 0);

        // Average duration in minutes.
        $avgdurationseconds = (int)$DB->get_field_sql(
            "SELECT COALESCE(AVG(duration), 0) FROM {local_bbb_insights_attendance} WHERE duration > 0"
        );
        $avgdurationmins = round($avgdurationseconds / 60);

        // Storage quota configuration in GB (default 50 GB).
        $quotagb = (int)get_config('local_bbb_insights', 'storage_quota_gb');
        if ($quotagb <= 0) {
            $quotagb = 50;
        }
        $quotabytes = $quotagb * 1024 * 1024 * 1024;
        $quotapercent = ($quotabytes > 0) ? min(100, round(($storageusedbytes / $quotabytes) * 100, 1)) : 0;
        $isquotaexceeded = ($storageusedbytes >= $quotabytes);

        return (object)[
            'session_count'        => $sessioncount,
            'attendee_count'       => $attendeecount,
            'recording_count'      => $recordingcount,
            'storage_used_bytes'   => $storageusedbytes,
            'storage_used_label'   => self::format_bytes($storageusedbytes),
            'quota_bytes'          => $quotabytes,
            'quota_label'          => $quotagb . ' GB',
            'quota_percent'        => $quotapercent,
            'is_quota_exceeded'    => $isquotaexceeded,
            'avg_duration_minutes' => $avgdurationmins,
            'avg_duration_label'   => self::format_duration($avgdurationseconds),
        ];
    }

    /**
     * Get recent virtual classroom sessions.
     *
     * @param int $limit Number of sessions to retrieve.
     * @return array List of session records with course details.
     */
    public static function get_recent_sessions(int $limit = 10): array {
        global $DB;

        $sql = "SELECT s.*, c.fullname AS coursename, c.shortname AS courseshortname
                  FROM {local_bbb_insights_sessions} s
             LEFT JOIN {course} c ON c.id = s.courseid
              ORDER BY s.starttime DESC";

        return $DB->get_records_sql($sql, null, 0, $limit);
    }

    /**
     * Get top courses by session count and attendance.
     *
     * @param int $limit Number of top courses to retrieve.
     * @return array List of top courses.
     */
    public static function get_top_courses(int $limit = 5): array {
        global $DB;

        $sql = "SELECT c.id, c.fullname, c.shortname,
                       COUNT(s.id) AS totalsessions,
                       COALESCE(SUM(s.participantcount), 0) AS totalparticipants
                  FROM {course} c
                  JOIN {local_bbb_insights_sessions} s ON s.courseid = c.id
              GROUP BY c.id, c.fullname, c.shortname
              ORDER BY totalsessions DESC, totalparticipants DESC";

        return $DB->get_records_sql($sql, null, 0, $limit);
    }

    /**
     * Get session records filtered by course ID with pagination.
     *
     * @param int $courseid Target course ID or 0 for all.
     * @param int $limit Max records.
     * @param int $offset Offset.
     * @return array List of sessions.
     */
    public static function get_sessions(int $courseid = 0, int $limit = 50, int $offset = 0): array {
        global $DB;

        $params = [];
        $where = "1=1";
        if ($courseid > 0) {
            $where = "s.courseid = :courseid";
            $params['courseid'] = $courseid;
        }

        $sql = "SELECT s.*, c.fullname AS coursename, c.shortname AS courseshortname
                  FROM {local_bbb_insights_sessions} s
             LEFT JOIN {course} c ON c.id = s.courseid
                 WHERE {$where}
              ORDER BY s.starttime DESC";

        return $DB->get_records_sql($sql, $params, $offset, $limit);
    }

    /**
     * Get attendance records filtered by course ID and session ID.
     *
     * @param int $courseid Target course ID or 0 for all.
     * @param int $sessionid Target session ID or 0 for all.
     * @param int $limit Max records.
     * @param int $offset Offset.
     * @return array List of attendance records.
     */
    public static function get_attendance_logs(int $courseid = 0, int $sessionid = 0, int $limit = 100, int $offset = 0): array {
        global $DB;

        $params = [];
        $conditions = ["1=1"];

        if ($courseid > 0) {
            $conditions[] = "a.courseid = :courseid";
            $params['courseid'] = $courseid;
        }
        if ($sessionid > 0) {
            $conditions[] = "a.sessionid = :sessionid";
            $params['sessionid'] = $sessionid;
        }

        $where = implode(" AND ", $conditions);

        $sql = "SELECT a.*, s.name AS sessionname, c.fullname AS coursename
                  FROM {local_bbb_insights_attendance} a
             LEFT JOIN {local_bbb_insights_sessions} s ON s.id = a.sessionid
             LEFT JOIN {course} c ON c.id = a.courseid
                 WHERE {$where}
              ORDER BY a.jointime DESC";

        return $DB->get_records_sql($sql, $params, $offset, $limit);
    }

    /**
     * Get recording records filtered by course ID.
     *
     * @param int $courseid Target course ID or 0 for all.
     * @param int $limit Max records.
     * @param int $offset Offset.
     * @return array List of recordings.
     */
    public static function get_recordings(int $courseid = 0, int $limit = 50, int $offset = 0): array {
        global $DB;

        $params = [];
        $where = "1=1";
        if ($courseid > 0) {
            $where = "r.courseid = :courseid";
            $params['courseid'] = $courseid;
        }

        $sql = "SELECT r.*, c.fullname AS coursename, c.shortname AS courseshortname
                  FROM {local_bbb_insights_recordings} r
             LEFT JOIN {course} c ON c.id = r.courseid
                 WHERE {$where}
              ORDER BY r.timecreated DESC";

        return $DB->get_records_sql($sql, $params, $offset, $limit);
    }

    /**
     * Synchronize session metrics and recordings from BigBlueButton activities or populate demo baseline.
     *
     * @return array Sync result summary.
     */
    public static function sync_insights(): array {
        global $DB;

        $syncedsessions = 0;
        $syncedrecordings = 0;
        $now = time();

        // Check if Moodle core BigBlueButton activity module is installed.
        $dbman = $DB->get_manager();
        if ($dbman->table_exists('bigbluebuttonbn')) {
            $bbbactivities = $DB->get_records('bigbluebuttonbn');
            foreach ($bbbactivities as $act) {
                // Check if session record exists.
                $existing = $DB->get_record('local_bbb_insights_sessions', [
                    'bigbluebuttonbnid' => $act->id,
                ]);

                if (!$existing) {
                    $session = new \stdClass();
                    $session->meetingid         = 'bbb-' . $act->id . '-' . substr(md5($act->name), 0, 8);
                    $session->courseid          = (int)$act->course;
                    $session->bigbluebuttonbnid = (int)$act->id;
                    $session->name              = $act->name;
                    $session->starttime         = $act->timecreated > 0 ? (int)$act->timecreated : $now - 3600;
                    $session->endtime           = $session->starttime + 3600;
                    $session->participantcount  = 1;
                    $session->moderatorcount    = 1;
                    $session->maxconcurrent     = 1;
                    $session->recordingid       = null;
                    $session->isrecorded        = !empty($act->record) ? 1 : 0;
                    $session->status            = 'completed';
                    $session->timecreated       = $now;
                    $session->timemodified      = $now;

                    $sessionid = $DB->insert_record('local_bbb_insights_sessions', $session);
                    $syncedsessions++;
                }
            }
        }

        // If no sessions exist in dev environment, seed sample data to enable immediate verification.
        if ($DB->count_records('local_bbb_insights_sessions') === 0) {
            $courses = $DB->get_records_select('course', 'id > 1', null, 'id ASC', 'id, fullname', 0, 3);
            if (empty($courses)) {
                $courses = [(object)['id' => 1, 'fullname' => 'Campus Virtual Hub']];
            }

            $counter = 1;
            foreach ($courses as $c) {
                $starttime = $now - (86400 * $counter);
                $session = (object)[
                    'meetingid'         => 'bbb-demo-room-' . $counter,
                    'courseid'          => (int)$c->id,
                    'bigbluebuttonbnid' => $counter,
                    'name'              => $c->fullname . ' — Live Lecture',
                    'starttime'         => $starttime,
                    'endtime'           => $starttime + 3600,
                    'participantcount'  => 12 * $counter,
                    'moderatorcount'    => 1,
                    'maxconcurrent'     => 12 * $counter,
                    'recordingid'       => 'rec-demo-' . $counter,
                    'isrecorded'        => 1,
                    'status'            => 'completed',
                    'timecreated'       => $now,
                    'timemodified'      => $now,
                ];
                $sessid = $DB->insert_record('local_bbb_insights_sessions', $session);
                $syncedsessions++;

                // Add sample attendees.
                $adminuser = $DB->get_record('user', ['id' => 2]);
                $adminname = $adminuser ? fullname($adminuser) : 'Administrator';
                $adminemail = $adminuser ? $adminuser->email : 'admin@example.com';

                $DB->insert_record('local_bbb_insights_attendance', (object)[
                    'sessionid'       => $sessid,
                    'courseid'        => (int)$c->id,
                    'userid'          => $adminuser ? (int)$adminuser->id : 2,
                    'userfullname'    => $adminname,
                    'useremail'       => $adminemail,
                    'role'            => 'moderator',
                    'jointime'        => $starttime,
                    'leavetime'       => $starttime + 3600,
                    'duration'        => 3600,
                    'engagementscore' => 95,
                    'timecreated'     => $now,
                ]);

                // Add recording.
                $filesize = 125829120 * $counter; // ~120MB per session
                $DB->insert_record('local_bbb_insights_recordings', (object)[
                    'recordingid'  => 'rec-demo-' . $counter,
                    'meetingid'    => 'bbb-demo-room-' . $counter,
                    'courseid'     => (int)$c->id,
                    'name'         => $c->fullname . ' — Lecture Recording',
                    'duration'     => 3600,
                    'filesize'     => $filesize,
                    'playurl'      => 'https://demo.bigbluebutton.org/playback/presentation/2.3/demo',
                    'status'       => 'published',
                    'timecreated'  => $now - (86400 * $counter),
                    'timemodified' => $now,
                ]);
                $syncedrecordings++;
                $counter++;
            }
        }

        return [
            'sessions_synced'   => $syncedsessions,
            'recordings_synced' => $syncedrecordings,
            'timestamp'         => $now,
        ];
    }

    /**
     * Generate a signed temporary recording download URL.
     *
     * @param int $recordingid Recording ID.
     * @param int $userid Current user ID.
     * @return string Signed download URL.
     */
    public static function generate_secure_download_url(int $recordingid, int $userid): string {
        $timestamp = time();
        $secret = get_site_identifier();
        $token = hash_hmac('sha256', "rec:{$recordingid}:user:{$userid}:ts:{$timestamp}", $secret);

        return (new \moodle_url('/local/bbb_insights/download.php', [
            'id'    => $recordingid,
            'ts'    => $timestamp,
            'token' => $token,
        ]))->out(false);
    }

    /**
     * Validate signed download token.
     *
     * @param int $recordingid Recording ID.
     * @param int $userid User ID.
     * @param int $timestamp Request timestamp.
     * @param string $token HMAC token.
     * @return bool True if valid and unexpired (within 2 hours).
     */
    public static function validate_secure_download_token(int $recordingid, int $userid, int $timestamp, string $token): bool {
        // Token valid for 7200 seconds (2 hours).
        if (time() - $timestamp > 7200 || $timestamp > time() + 300) {
            return false;
        }

        $secret = get_site_identifier();
        $expected = hash_hmac('sha256', "rec:{$recordingid}:user:{$userid}:ts:{$timestamp}", $secret);

        return hash_equals($expected, $token);
    }

    /**
     * Format bytes into human-readable string.
     *
     * @param int $bytes Number of bytes.
     * @param int $decimals Decimal points.
     * @return string Formatted byte string.
     */
    public static function format_bytes(int $bytes, int $decimals = 2): string {
        if ($bytes <= 0) {
            return '0 B';
        }

        $units = ['B', 'KB', 'MB', 'GB', 'TB', 'PB'];
        $power = floor(log($bytes, 1024));
        $power = min($power, count($units) - 1);

        return round($bytes / pow(1024, $power), $decimals) . ' ' . $units[$power];
    }

    /**
     * Format duration in seconds into human-readable string.
     *
     * @param int $seconds Duration in seconds.
     * @return string Formatted duration.
     */
    public static function format_duration(int $seconds): string {
        if ($seconds <= 0) {
            return '0m';
        }

        $hours = floor($seconds / 3600);
        $minutes = floor(($seconds % 3600) / 60);

        if ($hours > 0) {
            return "{$hours}h {$minutes}m";
        }
        return "{$minutes}m";
    }
}

