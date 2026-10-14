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

namespace local_bbb_insights\output;

use html_writer;
use moodle_url;
use plugin_renderer_base;
use local_bbb_insights\analytics_manager;

/**
 * Output renderer for local_bbb_insights.
 *
 * @package     local_bbb_insights
 * @copyright   2026 Emanuel Dickson Wanyonyi <wanyonyi.d.emanuel@gmail.com>
 * @author      Emanuel Dickson Wanyonyi <wanyonyi.d.emanuel@gmail.com>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class renderer extends plugin_renderer_base {

    /**
     * Render unified executive navigation header across plugin pages.
     *
     * @param string $activepage Active page key ('index', 'attendance', 'recordings', 'reports', 'export', 'help').
     * @param bool $showsync Whether to show the manual sync button.
     * @return string Rendered HTML header bar.
     */
    public static function render_nav_header(string $activepage = 'index', bool $showsync = true): string {
        $indexurl      = new moodle_url('/local/bbb_insights/index.php');
        $attendanceurl = new moodle_url('/local/bbb_insights/attendance.php');
        $recordingsurl = new moodle_url('/local/bbb_insights/recordings.php');
        $reportsurl    = new moodle_url('/local/bbb_insights/reports.php');
        $exporturl     = new moodle_url('/local/bbb_insights/export.php');
        $helpurl       = new moodle_url('/local/bbb_insights/help.php');

        $navitems = [
            'index'      => ['label' => get_string('nav_overview', 'local_bbb_insights'), 'url' => $indexurl],
            'attendance' => ['label' => get_string('nav_attendance', 'local_bbb_insights'), 'url' => $attendanceurl],
            'recordings' => ['label' => get_string('nav_recordings', 'local_bbb_insights'), 'url' => $recordingsurl],
            'reports'    => ['label' => get_string('nav_reports', 'local_bbb_insights'), 'url' => $reportsurl],
            'export'     => ['label' => get_string('nav_export', 'local_bbb_insights'), 'url' => $exporturl],
            'help'       => ['label' => get_string('nav_help', 'local_bbb_insights'), 'url' => $helpurl],
        ];

        $html = html_writer::start_div(
            'bg-white border rounded shadow-sm p-3 mb-4 d-flex align-items-center justify-content-between flex-wrap gap-3'
        );

        // Navigation Tabs (Pills).
        $html .= html_writer::start_div('nav nav-pills gap-2');
        foreach ($navitems as $key => $item) {
            $isactive = ($key === $activepage);
            $cls = $isactive
                ? 'nav-link active bg-primary font-weight-bold px-3 py-2 shadow-sm'
                : 'nav-link text-dark fw-semibold px-3 py-2 bg-light border border-secondary-subtle';
            $html .= html_writer::link($item['url'], $item['label'], ['class' => $cls]);
        }
        $html .= html_writer::end_div();

        // Action Buttons Group.
        $html .= html_writer::start_div('d-flex align-items-center gap-2 flex-wrap');
        if ($showsync) {
            $syncurl = new moodle_url('/local/bbb_insights/index.php', [
                'action'  => 'sync',
                'sesskey' => sesskey(),
            ]);
            $html .= html_writer::link($syncurl, get_string('sync_insights_now', 'local_bbb_insights'), [
                'class' => 'btn btn-outline-primary font-weight-bold px-3 py-2 shadow-sm',
            ]);
        }

        $exportactionurl = new moodle_url('/local/bbb_insights/export.php', ['type' => 'attendance']);
        $html .= html_writer::link($exportactionurl, get_string('quick_export_csv', 'local_bbb_insights'), [
            'class' => 'btn btn-success font-weight-bold px-3 py-2 shadow-sm',
        ]);
        $html .= html_writer::end_div();

        $html .= html_writer::end_div();

        return $html;
    }

    /**
     * Render main plugin dashboard interface.
     *
     * @param object $stats Aggregated stats from analytics_manager.
     * @param array $recentsessions Recent session records.
     * @param array $topcourses Top courses records.
     * @return string Rendered HTML content.
     */
    public function render_dashboard(object $stats, array $recentsessions, array $topcourses): string {
        $indexurl      = new moodle_url('/local/bbb_insights/index.php');
        $attendanceurl = new moodle_url('/local/bbb_insights/attendance.php');
        $recordingsurl = new moodle_url('/local/bbb_insights/recordings.php');
        $reportsurl    = new moodle_url('/local/bbb_insights/reports.php');
        $exporturl     = new moodle_url('/local/bbb_insights/export.php');

        $formatrecentsessions = [];
        foreach ($recentsessions as $s) {
            $formatrecentsessions[] = [
                'id'               => $s->id,
                'name'             => $s->name,
                'coursename'       => $s->coursename ?? 'N/A',
                'date_label'       => userdate($s->starttime, get_string('strftimedatetimeshort', 'core_langconfig')),
                'participantcount' => $s->participantcount,
                'moderatorcount'   => $s->moderatorcount,
                'isrecorded'       => !empty($s->isrecorded),
                'status'           => ucfirst($s->status),
                'session_url'      => (new moodle_url('/local/bbb_insights/attendance.php', ['sessionid' => $s->id]))->out(false),
            ];
        }

        $formattopcourses = [];
        foreach ($topcourses as $c) {
            $formattopcourses[] = [
                'id'                => $c->id,
                'fullname'          => $c->fullname,
                'shortname'         => $c->shortname,
                'totalsessions'     => $c->totalsessions,
                'totalparticipants' => $c->totalparticipants,
                'course_url'        => (new moodle_url('/local/bbb_insights/attendance.php', ['courseid' => $c->id]))->out(false),
            ];
        }

        $contextdata = [
            'tier_notice'        => get_string('engine_notice', 'local_bbb_insights'),
            'session_count'      => $stats->session_count,
            'attendee_count'     => $stats->attendee_count,
            'recording_count'    => $stats->recording_count,
            'storage_used_label' => $stats->storage_used_label,
            'quota_label'        => $stats->quota_label,
            'quota_percent'      => $stats->quota_percent,
            'is_quota_exceeded'  => $stats->is_quota_exceeded,
            'avg_duration_label' => $stats->avg_duration_label,
            'index_url'          => $indexurl->out(false),
            'attendance_url'     => $attendanceurl->out(false),
            'recordings_url'     => $recordingsurl->out(false),
            'reports_url'        => $reportsurl->out(false),
            'export_url'         => $exporturl->out(false),
            'recent_sessions'    => $formatrecentsessions,
            'has_recent'         => !empty($formatrecentsessions),
            'top_courses'        => $formattopcourses,
            'has_top_courses'    => !empty($formattopcourses),
        ];

        return $this->render_from_template('local_bbb_insights/dashboard', $contextdata);
    }
}

