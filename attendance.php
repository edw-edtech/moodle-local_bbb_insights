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
 * Session attendance tracking page for local_bbb_insights.
 *
 * @package     local_bbb_insights
 * @copyright   2026 Emanuel Dickson Wanyonyi <wanyonyi.d.emanuel@gmail.com>
 * @author      Emanuel Dickson Wanyonyi <wanyonyi.d.emanuel@gmail.com>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . '/../../config.php');

require_login();
$context = context_system::instance();
require_capability('local/bbb_insights:view', $context);

$courseid  = optional_param('courseid', 0, PARAM_INT);
$sessionid = optional_param('sessionid', 0, PARAM_INT);

$pageurl = new moodle_url('/local/bbb_insights/attendance.php', [
    'courseid'  => $courseid,
    'sessionid' => $sessionid,
]);

$PAGE->set_url($pageurl);
$PAGE->set_context($context);
$PAGE->set_title(get_string('nav_attendance', 'local_bbb_insights') . ' — ' . get_string('pluginname', 'local_bbb_insights'));
$PAGE->set_heading(get_string('pluginname', 'local_bbb_insights'));

// Retrieve courses for filter dropdown.
$courses = $DB->get_records_select_menu('course', 'id > 1 AND visible = 1', null, 'fullname ASC', 'id, fullname');

// Retrieve available sessions for filter dropdown.
$sessionsparams = [];
$sessionsselect = 'id > 0';
if ($courseid > 0) {
    $sessionsselect .= ' AND courseid = :courseid';
    $sessionsparams['courseid'] = $courseid;
}
$sessions = $DB->get_records_select_menu(
    'local_bbb_insights_sessions',
    $sessionsselect,
    $sessionsparams,
    'starttime DESC',
    'id, name'
);

$attendancelogs = \local_bbb_insights\analytics_manager::get_attendance_logs($courseid, $sessionid, 100);

echo $OUTPUT->header();
echo \local_bbb_insights\output\renderer::render_nav_header('attendance');
?>

<div class="card border-0 shadow-sm rounded-4 mb-4 bg-white">
    <div class="card-body p-4">
        <h4 class="font-weight-bold text-dark mb-1"><?php echo get_string('attendance_logs', 'local_bbb_insights'); ?></h4>
        <p class="text-muted small mb-3">Monitor live and historical student attendance, participant entry/exit logs, and engagement duration.</p>

        <!-- Filter Form -->
        <form method="get" action="<?php echo new moodle_url('/local/bbb_insights/attendance.php'); ?>" class="row g-3 align-items-end">
            <div class="col-md-5">
                <label for="courseid" class="form-label small fw-bold text-muted"><?php echo get_string('course_name', 'local_bbb_insights'); ?></label>
                <select name="courseid" id="courseid" class="form-select" onchange="this.form.submit()">
                    <option value="0"><?php echo get_string('all_courses', 'local_bbb_insights'); ?></option>
                    <?php foreach ($courses as $cid => $cname): ?>
                        <option value="<?php echo (int)$cid; ?>" <?php echo ($courseid === (int)$cid) ? 'selected' : ''; ?>>
                            <?php echo s($cname); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-5">
                <label for="sessionid" class="form-label small fw-bold text-muted"><?php echo get_string('classroom_name', 'local_bbb_insights'); ?></label>
                <select name="sessionid" id="sessionid" class="form-select">
                    <option value="0"><?php echo get_string('all_sessions', 'local_bbb_insights'); ?></option>
                    <?php foreach ($sessions as $sid => $sname): ?>
                        <option value="<?php echo (int)$sid; ?>" <?php echo ($sessionid === (int)$sid) ? 'selected' : ''; ?>>
                            <?php echo s($sname); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2 d-flex gap-2">
                <button type="submit" class="btn btn-primary w-100 font-weight-bold"><?php echo get_string('filter_button', 'local_bbb_insights'); ?></button>
                <a href="<?php echo new moodle_url('/local/bbb_insights/attendance.php'); ?>" class="btn btn-outline-secondary"><?php echo get_string('reset_filter', 'local_bbb_insights'); ?></a>
            </div>
        </form>
    </div>
</div>

<div class="card border-0 shadow-sm rounded-4 bg-white overflow-hidden">
    <div class="card-header bg-white border-bottom p-3 d-flex justify-content-between align-items-center">
        <span class="fw-bold text-dark">Participant Attendance Records (<?php echo count($attendancelogs); ?> records)</span>
        <a href="<?php echo new moodle_url('/local/bbb_insights/export.php', ['type' => 'attendance', 'courseid' => $courseid, 'sessionid' => $sessionid]); ?>" class="btn btn-sm btn-outline-success font-weight-bold">
            <i class="fa fa-download me-1"></i> <?php echo get_string('export_attendance_csv', 'local_bbb_insights'); ?>
        </a>
    </div>
    <div class="card-body p-0 table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light small text-uppercase">
                <tr>
                    <th class="ps-3"><?php echo get_string('participant_name', 'local_bbb_insights'); ?></th>
                    <th><?php echo get_string('course_name', 'local_bbb_insights'); ?></th>
                    <th><?php echo get_string('classroom_name', 'local_bbb_insights'); ?></th>
                    <th><?php echo get_string('role', 'local_bbb_insights'); ?></th>
                    <th><?php echo get_string('join_time', 'local_bbb_insights'); ?></th>
                    <th><?php echo get_string('leave_time', 'local_bbb_insights'); ?></th>
                    <th><?php echo get_string('duration', 'local_bbb_insights'); ?></th>
                    <th class="text-center pe-3"><?php echo get_string('engagement_score', 'local_bbb_insights'); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($attendancelogs)): ?>
                    <tr>
                        <td colspan="8" class="text-center py-4 text-muted">
                            <?php echo get_string('no_records_found', 'local_bbb_insights'); ?>
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($attendancelogs as $log): ?>
                        <tr>
                            <td class="ps-3">
                                <div class="fw-bold text-dark"><?php echo s($log->userfullname); ?></div>
                                <div class="small text-muted"><?php echo s($log->useremail ?? ''); ?></div>
                            </td>
                            <td><span class="badge bg-light text-dark border"><?php echo s($log->coursename ?? 'N/A'); ?></span></td>
                            <td class="small fw-semibold"><?php echo s($log->sessionname ?? 'N/A'); ?></td>
                            <td>
                                <?php if ($log->role === 'moderator'): ?>
                                    <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill">Moderator</span>
                                <?php else: ?>
                                    <span class="badge bg-secondary-subtle text-dark border border-secondary-subtle rounded-pill">Viewer</span>
                                <?php endif; ?>
                            </td>
                            <td class="small text-muted"><?php echo userdate($log->jointime, get_string('strftimedatetimeshort', 'core_langconfig')); ?></td>
                            <td class="small text-muted"><?php echo userdate($log->leavetime, get_string('strftimedatetimeshort', 'core_langconfig')); ?></td>
                            <td class="fw-semibold"><?php echo \local_bbb_insights\analytics_manager::format_duration($log->duration); ?></td>
                            <td class="text-center pe-3">
                                <div class="d-inline-flex align-items-center gap-2">
                                    <div class="progress" style="width: 60px; height: 8px;">
                                        <div class="progress-bar bg-success" role="progressbar" style="width: <?php echo (int)$log->engagementscore; ?>%;"></div>
                                    </div>
                                    <span class="small fw-bold text-muted"><?php echo (int)$log->engagementscore; ?>%</span>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php
echo $OUTPUT->footer();

