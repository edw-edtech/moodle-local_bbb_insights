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
 * Analytical reporting and engagement trends for local_bbb_insights.
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

$PAGE->set_url(new moodle_url('/local/bbb_insights/reports.php'));
$PAGE->set_context($context);
$PAGE->set_title(get_string('nav_reports', 'local_bbb_insights') . ' — ' . get_string('pluginname', 'local_bbb_insights'));
$PAGE->set_heading(get_string('pluginname', 'local_bbb_insights'));

$topcourses = \local_bbb_insights\analytics_manager::get_top_courses(20);
$stats = \local_bbb_insights\analytics_manager::get_overview_stats();

echo $OUTPUT->header();
echo \local_bbb_insights\output\renderer::render_nav_header('reports');
?>

<div class="card border-0 shadow-sm rounded-4 mb-4 bg-white">
    <div class="card-body p-4">
        <h4 class="font-weight-bold text-dark mb-1"><?php echo get_string('analytics_reports', 'local_bbb_insights'); ?></h4>
        <p class="text-muted small mb-0">Institutional virtual classroom adoption, attendance density, and departmental BigBlueButton engagement metrics.</p>
    </div>
</div>

<div class="card border-0 shadow-sm rounded-4 bg-white overflow-hidden mb-4">
    <div class="card-header bg-white border-bottom p-3 d-flex justify-content-between align-items-center">
        <span class="fw-bold text-dark">Course Engagement & Attendance Matrix</span>
        <a href="<?php echo new moodle_url('/local/bbb_insights/export.php', ['type' => 'attendance']); ?>" class="btn btn-sm btn-outline-success font-weight-bold">
            <i class="fa fa-download me-1"></i> Export Attendance Data
        </a>
    </div>
    <div class="card-body p-0 table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light small text-uppercase">
                <tr>
                    <th class="ps-3"><?php echo get_string('course_name', 'local_bbb_insights'); ?></th>
                    <th class="text-center"><?php echo get_string('session_count', 'local_bbb_insights'); ?></th>
                    <th class="text-center"><?php echo get_string('attendee_count', 'local_bbb_insights'); ?></th>
                    <th class="text-center">Avg Attendance / Class</th>
                    <th class="text-end pe-3"><?php echo get_string('actions', 'local_bbb_insights'); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($topcourses)): ?>
                    <tr>
                        <td colspan="5" class="text-center py-4 text-muted">
                            <?php echo get_string('no_records_found', 'local_bbb_insights'); ?>
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($topcourses as $c): ?>
                        <?php
                        $avgattendees = ($c->totalsessions > 0)
                            ? round($c->totalparticipants / $c->totalsessions, 1)
                            : 0;
                        ?>
                        <tr>
                            <td class="ps-3">
                                <div class="fw-bold text-dark"><?php echo s($c->fullname); ?></div>
                                <div class="small text-muted font-monospace"><?php echo s($c->shortname); ?></div>
                            </td>
                            <td class="text-center">
                                <span class="badge bg-primary-subtle text-primary rounded-pill px-3 py-1.5 fw-bold">
                                    <?php echo (int)$c->totalsessions; ?>
                                </span>
                            </td>
                            <td class="text-center">
                                <span class="badge bg-success-subtle text-success rounded-pill px-3 py-1.5 fw-bold">
                                    <?php echo (int)$c->totalparticipants; ?>
                                </span>
                            </td>
                            <td class="text-center fw-semibold text-muted">
                                <?php echo $avgattendees; ?> students
                            </td>
                            <td class="text-end pe-3">
                                <a href="<?php echo new moodle_url('/local/bbb_insights/attendance.php', ['courseid' => $c->id]); ?>" class="btn btn-sm btn-light border">
                                    Course Attendance &rarr;
                                </a>
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

