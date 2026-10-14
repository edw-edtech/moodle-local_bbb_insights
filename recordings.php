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
 * Recording quota monitoring and secure download management for local_bbb_insights.
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

$courseid = optional_param('courseid', 0, PARAM_INT);

$pageurl = new moodle_url('/local/bbb_insights/recordings.php', ['courseid' => $courseid]);
$PAGE->set_url($pageurl);
$PAGE->set_context($context);
$PAGE->set_title(get_string('nav_recordings', 'local_bbb_insights') . ' — ' . get_string('pluginname', 'local_bbb_insights'));
$PAGE->set_heading(get_string('pluginname', 'local_bbb_insights'));

$stats = \local_bbb_insights\analytics_manager::get_overview_stats();
$recordings = \local_bbb_insights\analytics_manager::get_recordings($courseid, 100);
$courses = $DB->get_records_select_menu('course', 'id > 1 AND visible = 1', null, 'fullname ASC', 'id, fullname');

$candownload = has_capability('local/bbb_insights:downloadrecordings', $context);

echo $OUTPUT->header();
echo \local_bbb_insights\output\renderer::render_nav_header('recordings');
?>

<!-- Quota Summary Banner -->
<div class="card border-0 shadow-sm rounded-4 mb-4 bg-white">
    <div class="card-body p-4">
        <div class="d-flex flex-wrap justify-content-between align-items-center mb-3">
            <div>
                <h4 class="font-weight-bold text-dark mb-1"><?php echo get_string('storage_quota_overview', 'local_bbb_insights'); ?></h4>
                <p class="text-muted small mb-0">Monitor server disk usage, recording retention, and quota capacity across academic courses.</p>
            </div>
            <span class="badge <?php echo $stats->is_quota_exceeded ? 'bg-danger' : 'bg-info'; ?> rounded-pill px-3 py-2 fs-6">
                <?php echo $stats->quota_percent; ?>% Quota Consumed
            </span>
        </div>

        <div class="progress rounded-pill mb-3" style="height: 12px; background-color: #f1f5f9;">
            <div class="progress-bar <?php echo $stats->is_quota_exceeded ? 'bg-danger' : 'bg-info'; ?> progress-bar-striped progress-bar-animated"
                 role="progressbar"
                 style="width: <?php echo min(100, $stats->quota_percent); ?>%;"
                 aria-valuenow="<?php echo $stats->quota_percent; ?>"
                 aria-valuemin="0"
                 aria-valuemax="100">
            </div>
        </div>

        <div class="row text-center pt-2 border-top">
            <div class="col-md-4">
                <span class="text-muted small text-uppercase"><?php echo get_string('quota_consumed', 'local_bbb_insights'); ?></span>
                <div class="fs-5 fw-bold text-dark"><?php echo $stats->storage_used_label; ?></div>
            </div>
            <div class="col-md-4">
                <span class="text-muted small text-uppercase"><?php echo get_string('quota_limit', 'local_bbb_insights'); ?></span>
                <div class="fs-5 fw-bold text-dark"><?php echo $stats->quota_label; ?></div>
            </div>
            <div class="col-md-4">
                <span class="text-muted small text-uppercase">Recordings Tracked</span>
                <div class="fs-5 fw-bold text-dark"><?php echo $stats->recording_count; ?> files</div>
            </div>
        </div>
    </div>
</div>

<!-- Course Filter -->
<div class="card border-0 shadow-sm rounded-4 mb-4 bg-white">
    <div class="card-body p-3">
        <form method="get" action="<?php echo new moodle_url('/local/bbb_insights/recordings.php'); ?>" class="row g-3 align-items-center">
            <div class="col-md-9">
                <select name="courseid" id="courseid" class="form-select" onchange="this.form.submit()">
                    <option value="0"><?php echo get_string('all_courses', 'local_bbb_insights'); ?></option>
                    <?php foreach ($courses as $cid => $cname): ?>
                        <option value="<?php echo (int)$cid; ?>" <?php echo ($courseid === (int)$cid) ? 'selected' : ''; ?>>
                            <?php echo s($cname); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3 d-flex gap-2">
                <button type="submit" class="btn btn-primary w-100 font-weight-bold"><?php echo get_string('filter_button', 'local_bbb_insights'); ?></button>
                <a href="<?php echo new moodle_url('/local/bbb_insights/recordings.php'); ?>" class="btn btn-outline-secondary"><?php echo get_string('reset_filter', 'local_bbb_insights'); ?></a>
            </div>
        </form>
    </div>
</div>

<!-- Recordings Inventory Table -->
<div class="card border-0 shadow-sm rounded-4 bg-white overflow-hidden">
    <div class="card-header bg-white border-bottom p-3 d-flex justify-content-between align-items-center">
        <span class="fw-bold text-dark"><?php echo get_string('recording_inventory', 'local_bbb_insights'); ?> (<?php echo count($recordings); ?> recordings)</span>
        <a href="<?php echo new moodle_url('/local/bbb_insights/export.php', ['type' => 'recordings', 'courseid' => $courseid]); ?>" class="btn btn-sm btn-outline-success font-weight-bold">
            <i class="fa fa-download me-1"></i> <?php echo get_string('export_recordings_csv', 'local_bbb_insights'); ?>
        </a>
    </div>
    <div class="card-body p-0 table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light small text-uppercase">
                <tr>
                    <th class="ps-3"><?php echo get_string('recording_name', 'local_bbb_insights'); ?></th>
                    <th><?php echo get_string('course_name', 'local_bbb_insights'); ?></th>
                    <th><?php echo get_string('date_recorded', 'local_bbb_insights'); ?></th>
                    <th><?php echo get_string('duration', 'local_bbb_insights'); ?></th>
                    <th><?php echo get_string('file_size', 'local_bbb_insights'); ?></th>
                    <th><?php echo get_string('status', 'local_bbb_insights'); ?></th>
                    <th class="text-end pe-3"><?php echo get_string('actions', 'local_bbb_insights'); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($recordings)): ?>
                    <tr>
                        <td colspan="7" class="text-center py-4 text-muted">
                            <?php echo get_string('no_records_found', 'local_bbb_insights'); ?>
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($recordings as $rec): ?>
                        <tr>
                            <td class="ps-3 fw-bold text-dark">
                                <?php echo s($rec->name); ?>
                                <div class="small text-muted font-monospace"><?php echo s($rec->recordingid); ?></div>
                            </td>
                            <td><span class="badge bg-light text-dark border"><?php echo s($rec->coursename ?? 'N/A'); ?></span></td>
                            <td class="small text-muted"><?php echo userdate($rec->timecreated, get_string('strftimedateshort', 'core_langconfig')); ?></td>
                            <td><?php echo \local_bbb_insights\analytics_manager::format_duration($rec->duration); ?></td>
                            <td class="fw-semibold"><?php echo \local_bbb_insights\analytics_manager::format_bytes($rec->filesize); ?></td>
                            <td>
                                <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill">
                                    <?php echo ucfirst(s($rec->status)); ?>
                                </span>
                            </td>
                            <td class="text-end pe-3">
                                <div class="d-flex justify-content-end gap-1">
                                    <?php if (!empty($rec->playurl)): ?>
                                        <a href="<?php echo s($rec->playurl); ?>" target="_blank" class="btn btn-sm btn-light border text-primary">
                                            <i class="fa fa-play me-1"></i> <?php echo get_string('view_playback', 'local_bbb_insights'); ?>
                                        </a>
                                    <?php endif; ?>
                                    <?php if ($candownload): ?>
                                        <?php $downloadurl = \local_bbb_insights\analytics_manager::generate_secure_download_url($rec->id, $USER->id); ?>
                                        <a href="<?php echo s($downloadurl); ?>" class="btn btn-sm btn-primary">
                                            <i class="fa fa-download me-1"></i> <?php echo get_string('download', 'local_bbb_insights'); ?>
                                        </a>
                                    <?php endif; ?>
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

