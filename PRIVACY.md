# BigBlueButton Insights Moodle Plugin — Privacy Policy & Data Handling Disclosure

**Last Updated:** October 14, 2026  
**Plugin Identifier:** `local_bbb_insights`  
**Maintainer / Data Controller:** Emanuel Dickson Wanyonyi (<wanyonyi.d.emanuel@gmail.com>)

---

## 🔒 1. Overview & Commitment to Privacy

The **BigBlueButton Insights** Moodle plugin (`local_bbb_insights`) is designed to capture and display virtual classroom analytics, attendance records, and recording storage quotas. We are committed to protecting the privacy of educational institutions, teachers, and students.

This Privacy Policy explains how data is collected, stored, processed, and managed by the BigBlueButton Insights plugin.

---

## 💾 2. Local Data Storage (On-Premise Moodle Database)

All operational data collected by the plugin is stored strictly inside your local Moodle site database in dedicated custom tables:

- **`local_bbb_insights_sessions`**: Stores session identifier (`meetingid`), course reference, meeting title, start/end timestamps, attendee counts, and recording status.
- **`local_bbb_insights_attendance`**: Stores participant session attendance, including Moodle user ID (`userid`), display name, join time, leave time, attended duration, and role (moderator or viewer).
- **`local_bbb_insights_recordings`**: Stores recording identifiers, duration, file size in bytes, and publication status.
- **`local_bbb_insights_quotas`**: Stores aggregated storage calculations and course-level utilization summaries.

### Personally Identifiable Information (PII)
- **Students & Attendees**: The plugin logs user IDs, display names, and attendance timestamps associated with specific virtual classes to provide teachers with attendance records.
- **Instructors / Moderators**: The plugin tracks moderator activity and session hosting times.
- **Zero Third-Party Transmission**: No attendance data, user identifiers, or recording content is ever transmitted to external servers or third-party cloud services.

---

## ⚡ 3. Local Processing Model

The plugin operates on a 100% on-premise model:
- Runs natively on your Moodle server under GPLv3.
- **Zero data leaves your Moodle infrastructure.**
- Analytics calculations and quota summaries are computed locally using standard SQL queries via Moodle's database abstraction layer (`$DB`).

---

## 🛡️ 4. Compliance with Moodle Privacy API

The `local_bbb_insights` plugin implements Moodle's core Privacy API (`\core_privacy\local\metadata\provider`, `\core_privacy\local\request\core_user_data_provider`, and `\core_privacy\local\request\plugin\provider`):
- **Data Export**: Users can export their personal attendance logs and session participation history via Moodle's standard GDPR data export requests.
- **Data Deletion**: When a user or course data deletion request is processed, all associated attendance records in `local_bbb_insights_attendance` are purged in full compliance with GDPR and institutional data retention regulations.

---

## 📧 5. Contact & Inquiries

For questions regarding data protection, privacy disclosure, or security compliance, please contact:

- **Maintainer**: Emanuel Dickson Wanyonyi
- **Email**: [wanyonyi.d.emanuel@gmail.com](mailto:wanyonyi.d.emanuel@gmail.com)
- **GitHub Repository**: [https://github.com/ewanyonyi/moodle-local_bbb_insights](https://github.com/ewanyonyi/moodle-local_bbb_insights)

