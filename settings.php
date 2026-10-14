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
 * Admin settings definition for local_bbb_insights.
 *
 * @package     local_bbb_insights
 * @copyright   2026 Emanuel Dickson Wanyonyi <wanyonyi.d.emanuel@gmail.com>
 * @author      Emanuel Dickson Wanyonyi <wanyonyi.d.emanuel@gmail.com>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

if ($hassiteconfig) {
    $settings = new admin_settingpage(
        'local_bbb_insights_settings',
        get_string('pluginname', 'local_bbb_insights')
    );

    if ($ADMIN->fulltree) {
        $settings->add(new admin_setting_heading(
            'local_bbb_insights/dashboard_heading',
            '',
            '<div class="alert alert-info d-flex align-items-center justify-content-between my-2">' .
            '<div><strong>BigBlueButton Insights</strong> is installed and tracking virtual classrooms.</div>' .
            '<a href="' . new moodle_url('/local/bbb_insights/index.php') . '" class="btn btn-primary font-weight-bold">Open BBB Insights Dashboard</a>' .
            '</div>'
        ));

        // Total institutional storage quota limit in GB.
        $settings->add(new admin_setting_configtext(
            'local_bbb_insights/storage_quota_gb',
            get_string('setting_storage_quota', 'local_bbb_insights'),
            get_string('setting_storage_quota_desc', 'local_bbb_insights'),
            50,
            PARAM_INT
        ));

        // Retention duration in days for session attendance logs.
        $settings->add(new admin_setting_configtext(
            'local_bbb_insights/retention_days',
            get_string('setting_retention_days', 'local_bbb_insights'),
            get_string('setting_retention_days_desc', 'local_bbb_insights'),
            180,
            PARAM_INT
        ));

        // Enable or disable background cron synchronization.
        $settings->add(new admin_setting_configcheckbox(
            'local_bbb_insights/enable_cron_sync',
            get_string('setting_enable_cron_sync', 'local_bbb_insights'),
            get_string('setting_enable_cron_sync_desc', 'local_bbb_insights'),
            1
        ));

        // Enforce capability check on recording downloads.
        $settings->add(new admin_setting_configcheckbox(
            'local_bbb_insights/enforce_download_capability',
            get_string('setting_enforce_download_cap', 'local_bbb_insights'),
            get_string('setting_enforce_download_cap_desc', 'local_bbb_insights'),
            1
        ));
    }

    $ADMIN->add('localplugins', $settings);

    $ADMIN->add('localplugins', new admin_externalpage(
        'local_bbb_insights_dashboard',
        get_string('manage_insights', 'local_bbb_insights'),
        new moodle_url('/local/bbb_insights/index.php'),
        'local/bbb_insights:view'
    ));
}

