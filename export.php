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
 * CSV and data exporter for local_bbb_insights.
 *
 * @package     local_bbb_insights
 * @copyright   2026 Emanuel Dickson Wanyonyi <wanyonyi.d.emanuel@gmail.com>
 * @author      Emanuel Dickson Wanyonyi <wanyonyi.d.emanuel@gmail.com>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . '/../../config.php');

require_login();
$context = context_system::instance();
require_capability('local/bbb_insights:export', $context);

$type      = optional_param('type', '', PARAM_ALPHA);
$courseid  = optional_param('courseid', 0, PARAM_INT);
$sessionid = optional_param('sessionid', 0, PARAM_INT);

if ($type === 'attendance') {
    $filename = 'bbb_attendance_' . date('Ymd_His') . '.csv';

    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename=' . $filename);

    $output = fopen('php://output', 'w');
    // BOM for Excel compatibility.
    fprintf($output, chr(0xEF) . chr(0xBB) . chr(0xBF));

    fputcsv($output, [
        'Session Name',
        'Course Name',
        'User Fullname',
        'User Email',
        'Role',
        'Join Time',
        'Leave Time',
        'Duration (Minutes)',
        'Engagement Score (%)',
    ]);

    $logs = \local_bbb_insights\analytics_manager::get_attendance_logs($courseid, $sessionid, 5000);
    foreach ($logs as $log) {
        $durationmins = round($log->duration / 60);
        fputcsv($output, [
            $log->sessionname ?? 'N/A',
            $log->coursename ?? 'N/A',
            $log->userfullname,
            $log->useremail ?? '',
            $log->role,
            userdate($log->jointime, '%Y-%m-%d %H:%M:%S'),
            userdate($log->leavetime, '%Y-%m-%d %H:%M:%S'),
            $durationmins,
            $log->engagementscore,
        ]);
    }
    fclose($output);
    exit;
}

if ($type === 'recordings') {
    $filename = 'bbb_recordings_' . date('Ymd_His') . '.csv';

    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename=' . $filename);

    $output = fopen('php://output', 'w');
    fprintf($output, chr(0xEF) . chr(0xBB) . chr(0xBF));

    fputcsv($output, [
        'Recording ID',
        'Meeting ID',
        'Course Name',
        'Recording Name',
        'Duration (Minutes)',
        'File Size (MB)',
        'Status',
        'Date Created',
    ]);

    $recordings = \local_bbb_insights\analytics_manager::get_recordings($courseid, 5000);
    foreach ($recordings as $rec) {
        $durationmins = round($rec->duration / 60);
        $sizemb = round($rec->filesize / (1024 * 1024), 2);
        fputcsv($output, [
            $rec->recordingid,
            $rec->meetingid,
            $rec->coursename ?? 'N/A',
            $rec->name,
            $durationmins,
            $sizemb,
            $rec->status,
            userdate($rec->timecreated, '%Y-%m-%d %H:%M:%S'),
        ]);
    }
    fclose($output);
    exit;
}

// Display export hub UI if no download parameter was executed.
$PAGE->set_url(new moodle_url('/local/bbb_insights/export.php'));
$PAGE->set_context($context);
$PAGE->set_title(get_string('nav_export', 'local_bbb_insights') . ' — ' . get_string('pluginname', 'local_bbb_insights'));
$PAGE->set_heading(get_string('pluginname', 'local_bbb_insights'));

echo $OUTPUT->header();
echo \local_bbb_insights\output\renderer::render_nav_header('export');
?>

<div class="card border-0 shadow-sm rounded-4 mb-4 bg-white">
    <div class="card-body p-4">
        <h4 class="font-weight-bold text-dark mb-1"><?php echo get_string('export_data', 'local_bbb_insights'); ?></h4>
        <p class="text-muted small mb-0">Download raw CSV spreadsheets for academic audit trails, accreditation documentation, and recording inventory reports.</p>
    </div>
</div>

<div class="row g-4">
    <div class="col-md-6">
        <div class="card border-0 shadow-sm rounded-4 h-100 bg-white">
            <div class="card-body p-4 d-flex flex-column justify-content-between">
                <div>
                    <div class="d-flex align-items-center gap-2 mb-3">
                        <div class="p-2 rounded-circle bg-success-subtle text-success d-flex align-items-center justify-content-center" style="width: 44px; height: 44px;">
                            <i class="fa fa-users fs-5"></i>
                        </div>
                        <h5 class="mb-0 fw-bold text-dark">Participant Attendance Logs</h5>
                    </div>
                    <p class="text-muted small">Export full attendance sheets containing participant names, email addresses, roles, join/leave timestamps, attended minutes, and engagement scores.</p>
                </div>
                <div class="mt-4 pt-3 border-top">
                    <a href="<?php echo new moodle_url('/local/bbb_insights/export.php', ['type' => 'attendance']); ?>" class="btn btn-success font-weight-bold w-100">
                        <i class="fa fa-download me-1"></i> <?php echo get_string('export_attendance_csv', 'local_bbb_insights'); ?>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-6">
        <div class="card border-0 shadow-sm rounded-4 h-100 bg-white">
            <div class="card-body p-4 d-flex flex-column justify-content-between">
                <div>
                    <div class="d-flex align-items-center gap-2 mb-3">
                        <div class="p-2 rounded-circle bg-primary-subtle text-primary d-flex align-items-center justify-content-center" style="width: 44px; height: 44px;">
                            <i class="fa fa-hdd-o fs-5"></i>
                        </div>
                        <h5 class="mb-0 fw-bold text-dark">Recording Storage Inventory</h5>
                    </div>
                    <p class="text-muted small">Export complete inventory of classroom recordings with file sizes (MB), durations, meeting IDs, publication statuses, and creation timestamps.</p>
                </div>
                <div class="mt-4 pt-3 border-top">
                    <a href="<?php echo new moodle_url('/local/bbb_insights/export.php', ['type' => 'recordings']); ?>" class="btn btn-primary font-weight-bold w-100">
                        <i class="fa fa-download me-1"></i> <?php echo get_string('export_recordings_csv', 'local_bbb_insights'); ?>
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
echo $OUTPUT->footer();

