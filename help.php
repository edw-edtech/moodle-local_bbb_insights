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
 * Help and user documentation guide for local_bbb_insights.
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

$PAGE->set_url(new moodle_url('/local/bbb_insights/help.php'));
$PAGE->set_context($context);
$PAGE->set_title(get_string('nav_help', 'local_bbb_insights') . ' — ' . get_string('pluginname', 'local_bbb_insights'));
$PAGE->set_heading(get_string('pluginname', 'local_bbb_insights'));

echo $OUTPUT->header();
echo \local_bbb_insights\output\renderer::render_nav_header('help');
?>

<div class="row g-4 mb-4">
    <!-- Hero Banner Card -->
    <div class="col-12">
        <div class="card border-0 shadow-sm rounded-4 bg-dark text-white p-4 overflow-hidden position-relative"
             style="background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%) !important;">
            <div class="row align-items-center position-relative" style="z-index: 2;">
                <div class="col-lg-8">
                    <span class="badge bg-success text-white font-monospace text-uppercase px-3 py-1 rounded-pill mb-2">FREE LOCAL ENGINE</span>
                    <h2 class="fw-bold text-white mb-2">BigBlueButton Insights User & Administrator Guide</h2>
                    <p class="text-white-50 mb-0">Learn how to track virtual classroom attendance, monitor institutional recording storage quotas, generate secure download links, and export analytical compliance data.</p>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row g-4 mb-4">
    <!-- Quickstart Workflow -->
    <div class="col-md-4">
        <div class="card border-0 shadow-sm rounded-4 h-100 bg-white p-3">
            <div class="card-body">
                <div class="badge bg-primary rounded-pill mb-3">Step 1</div>
                <h5 class="fw-bold text-dark">Automatic Syncing</h5>
                <p class="text-muted small">The plugin reads session logs and recording states directly from Moodle's BigBlueButton module and background scheduled tasks.</p>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card border-0 shadow-sm rounded-4 h-100 bg-white p-3">
            <div class="card-body">
                <div class="badge bg-success rounded-pill mb-3">Step 2</div>
                <h5 class="fw-bold text-dark">Attendance Tracking</h5>
                <p class="text-muted small">View granular attendee entry/exit timestamps, participation duration, moderator roles, and engagement percentage scores.</p>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card border-0 shadow-sm rounded-4 h-100 bg-white p-3">
            <div class="card-body">
                <div class="badge bg-info rounded-pill mb-3">Step 3</div>
                <h5 class="fw-bold text-dark">Quota & Secure Downloads</h5>
                <p class="text-muted small">Track total disk footprint in GB against institutional thresholds and generate signed, time-limited tokens for downloading recordings.</p>
            </div>
        </div>
    </div>
</div>

<!-- FAQ Section -->
<div class="card border-0 shadow-sm rounded-4 bg-white p-4 mb-4">
    <h4 class="fw-bold text-dark mb-3">Frequently Asked Questions</h4>
    
    <div class="mb-3">
        <h6 class="fw-bold text-dark">How often does data synchronize?</h6>
        <p class="text-muted small">Data synchronizes automatically every hour via Moodle's core cron scheduled task (<code>\local_bbb_insights\task\sync_insights</code>). You can also click <strong>Sync Insights Now</strong> on the dashboard for an immediate refresh.</p>
    </div>

    <div class="mb-3">
        <h6 class="fw-bold text-dark">Are student records kept private and compliant with GDPR?</h6>
        <p class="text-muted small">Yes. All data is processed 100% on-premise inside your local Moodle database with zero external telemetry. The plugin fully implements Moodle's Privacy API for data export and erasure requests.</p>
    </div>

    <div>
        <h6 class="fw-bold text-dark">Who can download recordings?</h6>
        <p class="text-muted small">Only users with the capability <code>local/bbb_insights:downloadrecordings</code> (managers and teachers by default) can generate signed media download links.</p>
    </div>
</div>

<!-- Support Contact Card -->
<div class="card border-0 shadow-sm rounded-4 bg-light p-4 text-center">
    <h5 class="fw-bold text-dark mb-1">Need Technical Assistance?</h5>
    <p class="text-muted small mb-3">For assistance, questions, or bug reports, contact the maintainer or visit the GitHub repository.</p>
    <div>
        <a href="mailto:wanyonyi.d.emanuel@gmail.com" class="btn btn-outline-primary fw-bold me-2">Email Support</a>
        <a href="https://github.com/ewanyonyi/moodle-local_bbb_insights" target="_blank" class="btn btn-dark fw-bold">GitHub Repository</a>
    </div>
</div>

<?php
echo $OUTPUT->footer();

