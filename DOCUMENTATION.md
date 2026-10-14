# BigBlueButton Insights Moodle Plugin — Administrator & User Guide

Welcome to the **BigBlueButton Insights** administrator and educator guide. This document provides step-by-step instructions for Moodle Administrators, Academic Directors, and Instructors to track live and historical virtual classroom attendance, monitor server recording quotas, securely manage recording downloads, and export institutional compliance reports.

---

## 📌 Table of Contents

1. [Introduction](#introduction)
2. [Quickstart Checklist](#quickstart-checklist)
3. [5-Step Insights Workflow](#5-step-insights-workflow)
   - [Step 1: BigBlueButton Connectivity & Server Sync](#step-1-bigbluebutton-connectivity--server-sync)
   - [Step 2: Session Attendance Tracking & Engagement](#step-2-session-attendance-tracking--engagement)
   - [Step 3: Storage Quota & Recording Lifecycle Management](#step-3-storage-quota--recording-lifecycle-management)
   - [Step 4: Secure Recording Downloads & Access Governance](#step-4-secure-recording-downloads--access-governance)
   - [Step 5: Insights Studio (Filter, Export & Print)](#step-5-insights-studio-filter-export--print)
4. [CSV Export Specifications](#csv-export-specifications)
5. [Automated Scheduled Sync Setup](#automated-scheduled-sync-setup)
6. [Frequently Asked Questions (FAQ) & Troubleshooting](#frequently-asked-questions-faq--troubleshooting)
7. [Support & Assistance](#support--assistance)

---

## 🌟 Introduction

**BigBlueButton Insights** (`local_bbb_insights`) is a central analytics and storage governance hub for Moodle institutions using BigBlueButton. It replaces disparate session logs and guesswork with clear, actionable metrics:
* **Session Attendance**: Exact timestamps for when students joined, departed, how long they participated, and their roles.
* **Recording Storage Quotas**: Prevents server out-of-disk crashes by tracking storage footprint per course and enforcing institutional limits.
* **Secure Downloads**: Allows educators and approved students to download recordings through protected, temporary access links without exposing direct server storage URLs.

---

## 📋 Quickstart Checklist

Before utilizing BBB Insights, verify the following prerequisites:
- [x] **BigBlueButton Installed**: The standard Moodle BigBlueButton activity (`mod_bigbluebuttonbn`) is enabled and connected to an active BBB server.
- [x] **Role Capabilities**: Administrators and teachers have the `local/bbb_insights:view` capability assigned.
- [x] **Storage Quota**: Institutional storage threshold is configured in **Site Administration ➔ Plugins ➔ Local plugins ➔ BigBlueButton Insights**.
- [x] **Scheduled Task**: The cron task `\local_bbb_insights\task\sync_insights` is enabled to periodically synchronize session records.

---

## 🚀 5-Step Insights Workflow

### Step 1: BigBlueButton Connectivity & Server Sync
Navigate to **Site Administration ➔ Plugins ➔ Local plugins ➔ BigBlueButton Insights**.
1. **Verify Connection**: The plugin natively synchronizes with the BigBlueButton server configured under `mod_bigbluebuttonbn`.
2. **Quota Limits**: Set the maximum allowed institutional storage quota (e.g., `50` GB).
3. **Retention Policy**: Configure the retention window (e.g. `180` days) after which old session attendance logs are archived.
4. **Manual Sync**: Click **Sync Insights** on the dashboard header at any time to immediately pull recent meeting metrics.

### Step 2: Session Attendance Tracking & Engagement
Navigate to **BBB Insights ➔ Attendance**.
1. **Select Course / Meeting**: Filter by a specific course or virtual classroom session.
2. **Participant Timestamps**: Inspect join and leave times for every student and moderator.
3. **Attended Duration**: Review actual participation duration against total session duration.
4. **Role Breakdown**: View attendee distribution (moderators, presenters, and viewers).

### Step 3: Storage Quota & Recording Lifecycle Management
Navigate to **BBB Insights ➔ Recordings & Quotas**.
1. **Storage Utilization Bar**: Check current disk consumption against the configured institutional quota.
2. **Per-Course Usage**: Identify which courses or academic departments occupy the largest storage volumes.
3. **Recording State**: Review recording statuses (*published*, *processing*, or *unpublished*).

### Step 4: Secure Recording Downloads & Access Governance
Navigate to **BBB Insights ➔ Recordings & Quotas**.
1. **Download Permissions**: Downloads require the `local/bbb_insights:downloadrecordings` capability.
2. **Signed Tokens**: When a teacher or manager clicks **Download**, a time-limited signed token is issued, verifying the user session before serving the media.
3. **Audit Trail**: All download attempts are logged with user IDs and timestamps.

### Step 5: Insights Studio (Filter, Export & Print)
Navigate to **BBB Insights ➔ Reports & Export**.
1. **Custom Date Windows**: Filter analytics by week, month, semester, or academic term.
2. **CSV Data Export**: Download raw attendance sheets or recording inventories for accreditation audits and reporting.
3. **Printable Views**: Generate clean, printer-friendly summary reports.

---

## 📊 CSV Export Specifications

### 1. Attendance CSV Export (`Export ➔ Attendance Logs`)
```csv
Session Name, Course Name, User Fullname, User Email, Role, Join Time, Leave Time, Duration (Minutes), Engagement Score (%)
Introduction to Biology, BIO101, Jane Doe, jane@example.com, viewer, 2026-10-14 09:00:15, 2026-10-14 10:28:40, 88, 95
Introduction to Biology, BIO101, Dr. John Smith, john@example.com, moderator, 2026-10-14 08:55:00, 2026-10-14 10:35:10, 100, 100
```

### 2. Recordings CSV Export (`Export ➔ Recording Inventory`)
```csv
Recording ID, Course Name, Meeting Name, Duration (Mins), File Size (MB), Status, Date Recorded
rec-98721, CS204 Algorithms, Lecture 4 Trees, 75, 450.5, published, 2026-10-12
rec-98722, MATH301 Calculus, Derivatives Review, 60, 310.2, published, 2026-10-13
```

---

## ⚡ Automated Scheduled Sync Setup

The plugin includes a background synchronization task to keep metrics up-to-date without administrative intervention.

1. **Verify Cron Execution**:
   Ensure Moodle core cron is running on your server (`* * * * * php /path/to/moodle/admin/cli/cron.php`).
2. **Run Sync Task Manually via CLI**:
   ```bash
   cd ~/dev/moodle
   php admin/cli/scheduled_task.php --execute=\\local_bbb_insights\\task\\sync_insights
   ```

---

## ❓ Frequently Asked Questions (FAQ) & Troubleshooting

#### Q1: Does this plugin modify existing BigBlueButton classroom activities?
*No. The plugin is completely non-intrusive and reads session events and recording states without interfering with live classroom operations.*

#### Q2: What happens when the storage quota is reached?
*The dashboard displays a warning banner when storage exceeds 90% and alerts administrators when 100% capacity is reached so old recordings can be archived.*

#### Q3: Can students download session recordings?
*By default, only instructors, course creators, and administrators possess the `local/bbb_insights:downloadrecordings` capability. Administrators can grant this capability to student roles if institutional policy permits.*

---

## 📞 Support & Assistance

- **Support Email**: [wanyonyi.d.emanuel@gmail.com](mailto:wanyonyi.d.emanuel@gmail.com)
- **Issue Tracker**: [https://github.com/ewanyonyi/moodle-local_bbb_insights/issues](https://github.com/ewanyonyi/moodle-local_bbb_insights/issues)

