# AGENTS.md — Guidelines for AI Agents (Moodle Plugin)

This document defines architectural conventions, Moodle coding standards, and development practices for AI coding assistants (including Antigravity, Claude, Copilot) operating on the `local_bbb_insights` Moodle plugin codebase.

---

## 🎯 1. Project Purpose

`local_bbb_insights` is an enterprise Moodle local plugin designed for tracking and visualizing BigBlueButton (BBB) virtual classroom metrics:
- **Session Attendance Tracking**: Records live & historical attendance, entry/exit timestamps, total duration, participant roles (moderator vs. viewer), and engagement scores.
- **Recording Quota & Storage Monitoring**: Calculates storage footprint per course and site-wide, monitoring against institutional limits and retention thresholds.
- **Secure Recording Downloads**: Generates permission-checked, signed temporary URLs for media downloads to prevent unauthorized redistribution.
- **Automated Synchronization**: Utilizes Moodle scheduled tasks to aggregate BBB session metrics and recording data without impacting live teaching.

---

## 🛠️ 2. Tech Stack Specification

- **Platform**: Moodle LMS 4.4+ (PHP 8.1+, MySQL / MariaDB / PostgreSQL)
- **UI Framework**: Bootstrap 5 + Moodle `html_writer`, Mustache templates (`templates/dashboard.mustache`), & class renderers
- **Architecture**: PSR-4 autoloading (`local_bbb_insights\...`), Moodle DB Abstraction Layer (`$DB`), Moodle Privacy API

---

## 📐 3. Key Directory Structure

```
local/bbb_insights/
├── index.php         <-- Executive dashboard overview & KPI metrics
├── attendance.php    <-- Session-by-session & participant attendance logs
├── recordings.php    <-- Storage quota monitoring & recording download manager
├── reports.php       <-- Course-level and site-level analytical reports
├── export.php        <-- CSV and printable data report exporter
├── download.php      <-- Secure, tokenized recording download controller
├── help.php          <-- Integrated administrator & educator documentation
├── settings.php      <-- Site administration settings (quota, retention, sync)
├── version.php       <-- Plugin release version & dependency metadata
├── styles.css        <-- Plugin UI styles, cards, and progress bar widgets
├── classes/          <-- Core OOP classes & business logic
│   ├── analytics_manager.php <-- Attendance calculation & quota aggregation
│   ├── output/       <-- Navigation header & UI renderers
│   ├── privacy/      <-- Moodle Privacy API provider (GDPR compliance)
│   └── task/         <-- Scheduled background sync tasks
├── db/               <-- Database schema (install.xml), capabilities, tasks, upgrade
│   ├── access.php    <-- Capability definitions (view, manage, download, export)
│   ├── install.xml   <-- XMLDB table definitions
│   ├── tasks.php     <-- Scheduled task registrations
│   └── upgrade.php   <-- Database upgrade functions
├── lang/en/          <-- Localization language strings (local_bbb_insights.php)
├── templates/        <-- Mustache frontend templates (dashboard.mustache)
└── tests/            <-- PHPUnit automated tests
```

---

## ⚠️ 4. Rules for AI Agents

1. **Moodle Coding Standards & Security**:
   - Always enforce capability checks:
     - `require_capability('local/bbb_insights:view', $context)` for viewing dashboards and attendance.
     - `require_capability('local/bbb_insights:manage', $context)` for changing quotas and triggering manual synchronizations.
     - `require_capability('local/bbb_insights:downloadrecordings', $context)` for accessing recording download endpoints.
   - Never use raw SQL string concatenation; always use parameter binding with Moodle `$DB`.
   - Sanitize all parameters using `required_param()` or `optional_param()` with explicit types (`PARAM_INT`, `PARAM_ALPHA`, `PARAM_ALPHANUMEXT`, `PARAM_TEXT`).

2. **CodeSniffer (`phpcs`) Linting & Compliance**:
   - Verify all modified PHP files against Moodle coding standards:
     `phpcs --standard=moodle <filepath>`
   - **Variable Naming Rule**: Variable names in procedural/script files must be alphanumeric without underscores (e.g. `$courseid` instead of `$course_id`, `$activepage` instead of `$active_page`).
   - **Line Length**: Ensure line lengths do not exceed 180 characters.
   - **Docblocks**: Provide full PHPDoc docblocks for functions, methods, classes, and file headers.

3. **Moodle Plugin Versioning Policy**:
   - **Only update the Moodle plugin version (`version.php`) when generating or packaging a `.zip` release file for distribution.**
   - Do NOT bump `version.php` version numbers during routine code edits, UI polish, or feature development.

4. **UI & Navigation Consistency**:
   - The top navigation bar is generated via `\local_bbb_insights\output\renderer::render_nav_header($activepage)`.
   - Maintain uniform styling across all procedural views using Bootstrap 5 utility classes and standard Moodle card containers.

