# BigBlueButton Insights Moodle Plugin (`local_bbb_insights`)

**`BigBlueButton Insights`** is an enterprise-grade, open-source Moodle local plugin designed for **BigBlueButton (BBB) virtual classroom analytics**, **live and historical session attendance tracking**, **recording storage quota monitoring**, and **secure recording downloads**.

This plugin provides comprehensive academic reporting, engagement intelligence, and storage governance directly within Moodle:
* **Free & Open Source Edition**: Built-in native PHP analytics engine for session tracking, participant attendance calculation, storage quota management, and role-governed recording downloads under GPLv3.
* **On-Premise Privacy & Security**: Processes all metrics directly on your Moodle server and database with zero telemetry, external API dependencies, or third-party tracking.

---

## 🎧 Support & Contact Channels

* **Customer Support Email**: [wanyonyi.d.emanuel@gmail.com](mailto:wanyonyi.d.emanuel@gmail.com)
* **Repository**: [https://github.com/ewanyonyi/moodle-local_bbb_insights](https://github.com/ewanyonyi/moodle-local_bbb_insights)

---

## 📜 Licensing

`local_bbb_insights` is distributed as a free Moodle local plugin project:

* **Native On-Premise Engine (Free / Community)**: Virtual classroom session analytics, attendee participation monitoring, recording quota enforcement, secure recording access controls, and scheduled sync jobs running entirely on your Moodle server under GPLv3.
* **Local Processing Model**: Computes attendance metrics, aggregates recording usage, and generates reports directly against Moodle core without sending any user, student, or institutional data outside your infrastructure.

---

## 🚀 Key Features

* **📊 Executive Analytics Dashboard**: Real-time KPI summaries displaying total virtual classroom sessions, total attendees, average attendance durations, and recording storage consumption with visual quota progress bars.
* **👥 Session Attendance Tracking**: Granular participant tracking including join time, leave time, total attended duration, participant roles (moderator vs. viewer), and participation engagement scores.
* **💾 Recording Quota & Storage Management**: Monitor disk storage footprint across courses, track recording counts, set institutional storage thresholds, and enforce retention policies.
* **🔒 Secure Recording Downloads**: Role-governed, time-expiring signed tokens that restrict recording downloads to authorized faculty, administrators, or enrolled students, preventing unauthorized media redistribution.
* **⚡ Automated Background Synchronisation**: Background scheduled tasks (`\local_bbb_insights\task\sync_insights`) ensure continuous data synchronization without impacting real-time teaching or server responsiveness.
* **📑 Multi-Format Exporting**: One-click CSV and printable reports for academic audits, accreditation compliance, and departmental attendance records.

---

## Local Development Setup (`~/dev/moodle` — Ubuntu 26.04 LTS)

This setup runs Moodle **v5.2.2** and your plugin directly out of your user development directory (`~/dev`), eliminating all `sudo` permission conflicts during development.

### 1. Install & Configure LAMP Dependencies

Ensure Apache, MariaDB, and PHP dependencies are installed:

```bash
sudo apt update && sudo apt install -y \
    apache2 mariadb-server php php-cli php-mysql \
    php-xml php-gd php-curl php-zip php-mbstring \
    php-intl php-soap git unzip
```

Configure PHP execution limits in `/etc/php/8.5/apache2/php.ini` (or `/etc/php/8.3/apache2/php.ini`) and `cli/php.ini`:

```ini
max_execution_time = 300
memory_limit = 512M
post_max_size = 100M
upload_max_filesize = 100M
max_input_vars = 5000
```

Restart Apache:
```bash
sudo systemctl restart apache2
```

---

### 2. Log in & Create MariaDB Database & User

1. **Log into MariaDB via Root Unix Socket:**
   On Ubuntu, MariaDB uses `unix_socket` authentication for administrative access. Log into the MariaDB shell using `sudo`:

   ```bash
   sudo mariadb
   ```

   > [!NOTE]
   > **Resolving `ERROR 1698 (28000): Access denied for user`**:
   > Running `mariadb` directly without `sudo` from your standard user shell will fail with:
   > ```text
   > ERROR 1698 (28000): Access denied for user 'username'@'localhost'
   > ```
   > Always run `sudo mariadb` (or `sudo mysql`) to authenticate via the local root socket.

2. **Execute Database & User Creation Statements:**
   Inside the MariaDB prompt, create a clean database and dedicated user for Moodle 5.2.2:

   ```sql
   CREATE DATABASE moodle DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
   CREATE USER 'moodleuser'@'localhost' IDENTIFIED BY 'YOUR_DATABASE_PASSWORD';
   GRANT ALL PRIVILEGES ON moodle.* TO 'moodleuser'@'localhost';
   FLUSH PRIVILEGES;
   EXIT;
   ```

---

### 3. Install Moodle 5.2.2 Core & Plugin Directory in `~/dev`

1. **Clone Moodle Production Release (v5.2.2):**
   ```bash
   mkdir -p ~/dev
   cd ~/dev
   git clone -b v5.2.2 git://git.moodle.org/moodle.git moodle
   mkdir -p ~/dev/moodledata
   ```

2. **Create Plugin Folder Directly in the Dev Tree:**
   ```bash
   mkdir -p ~/dev/moodle/local/bbb_insights
   ```

3. **Run Non-Interactive Automated Moodle CLI Installation:**
   ```bash
   cd ~/dev/moodle
   php admin/cli/install.php \
     --lang=en \
     --wwwroot=http://localhost \
     --dataroot=$HOME/dev/moodledata \
     --dbtype=mariadb \
     --dbhost=localhost \
     --dbname=moodle \
     --dbuser=moodleuser \
     --dbpass='YOUR_DATABASE_PASSWORD' \
     --fullname="Moodle Dev Site" \
     --shortname="moodle-dev" \
     --adminuser=admin \
     --adminpass='YOUR_ADMIN_PASSWORD' \
     --adminemail=admin@example.com \
     --non-interactive \
     --agree-license
   chmod 644 ~/dev/moodle/config.php
   ```

> [!TIP]
> **If `config.php` already exists**:
> If `config.php` was already created (either via web installer or prior configuration), use `install_database.php` to populate database tables directly without recreating `config.php`:
> ```bash
> cd ~/dev/moodle
> php admin/cli/install_database.php \
>   --agree-license \
>   --fullname="Moodle Dev Site" \
>   --shortname="moodle-dev" \
>   --adminpass='YOUR_ADMIN_PASSWORD' \
>   --adminemail=admin@example.com
> ```

> [!TIP]
> **Resetting Admin Password**:
> To reset the development environment admin password via CLI, run:
> ```bash
> cd ~/dev/moodle
> php admin/cli/reset_password.php --username=admin --password=Password123!
> ```

---

### 4. Configure Apache Virtual Host for `~/dev/moodle`

1. **Create Virtual Host File:**
   ```bash
   sudo nano /etc/apache2/sites-available/moodle-dev.conf
   ```

2. **Insert Virtual Host Block (HTTP & HTTPS Support):**
   ```apache
   <VirtualHost *:80>
       ServerName localhost
       DocumentRoot /home/username/dev/moodle/public

       <Directory /home/username/dev/moodle/public>
           Options Indexes FollowSymLinks
           AllowOverride All
           Require all granted
       </Directory>

       ErrorLog ${APACHE_LOG_DIR}/moodle_error.log
       CustomLog ${APACHE_LOG_DIR}/moodle_access.log combined
   </VirtualHost>

   <IfModule mod_ssl.c>
   <VirtualHost *:443>
       ServerName localhost
       DocumentRoot /home/username/dev/moodle/public

       <Directory /home/username/dev/moodle/public>
           Options Indexes FollowSymLinks
           AllowOverride All
           Require all granted
       </Directory>

       SSLEngine on
       SSLCertificateFile /etc/ssl/certs/ssl-cert-snakeoil.pem
       SSLCertificateKeyFile /etc/ssl/private/ssl-cert-snakeoil.key

       ErrorLog ${APACHE_LOG_DIR}/moodle_error.log
       CustomLog ${APACHE_LOG_DIR}/moodle_access.log combined
   </VirtualHost>
   </IfModule>
   ```

3. **Set Permissions & Enable Virtual Host:**
   ```bash
   # Grant directory traversal & file permissions to Apache (www-data)
   chmod 755 $HOME
   chmod 755 $HOME/dev
   chmod 644 ~/dev/moodle/config.php
   chmod -R 777 ~/dev/moodledata

   # Enable SSL module & Virtual Host
   sudo a2enmod ssl
   sudo a2ensite moodle-dev.conf
   sudo systemctl restart apache2
   ```

> [!NOTE]
> **Troubleshooting HTTP 500 Errors & Connection Refused (`https://localhost`)**:
> - **HTTP 500 (`config.php` Permission Denied)**: Check `/var/log/apache2/moodle_error.log`. Ensure read permissions are granted to Apache (`www-data`):
>   ```bash
>   chmod 644 ~/dev/moodle/config.php
>   ```
> - **`ERR_CONNECTION_REFUSED` (`https://localhost`)**: Modern browsers auto-redirect `localhost` to `https://`. Ensure you use `http://localhost` or enable SSL support in Apache:
>   ```bash
>   sudo a2enmod ssl
>   sudo a2ensite default-ssl
>   sudo systemctl restart apache2
>   ```

---

### 5. CLI Installation & Testing Workflow (No `sudo` Required)

Once web setup is complete at `http://localhost/`, manage database upgrades, CLI tasks, and coding directly in your user environment:

* **Run Moodle Upgrade / Schema Install:**
  ```bash
  cd ~/dev/moodle
  php admin/cli/upgrade.php
  ```

* **Execute Scheduled Insights Background Task:**
  ```bash
  cd ~/dev/moodle
  php admin/cli/scheduled_task.php --execute=\\local_bbb_insights\\task\\sync_insights
  ```

---

## Moodle CodeSniffer (`phpcs`) Global Setup Guide

Moodle core's `composer install` does not include `phpcs` in its local `vendor/bin` directory. Follow these steps to install Moodle's official CodeSniffer tools globally on your Ubuntu machine.

---

### Step 1: Install Moodle CodeSniffer Globally

Run these commands in your terminal to download `phpcs` and the official Moodle CodeSniffer rules (`moodle-cs`):

```bash
composer global config minimum-stability dev
composer global require moodlehq/moodle-cs
```

### Step 2: Add Global Composer Binaries to Environment PATH
Ensure your terminal can execute commands installed by global Composer packages:

Open your shell configuration file:
```bash
nano ~/.bashrc
```
Add the following line at the end of the file:
```bash
export PATH="$HOME/.composer/vendor/bin:$PATH"
```

Save the file and reload your shell configuration:
```bash
source ~/.bashrc
```

### Step 3: Verify & Run CodeSniffer
Confirm that `phpcs` is accessible globally:

```bash
phpcs --version
```

Run the Moodle standard code check against your local plugin workspace:

```bash
# Standard check
phpcs --standard=moodle ~/dev/moodle/local/bbb_insights

# Extended check (optional)
phpcs --standard=moodle-extra ~/dev/moodle/local/bbb_insights
```

---

## ⚖️ Software License

This plugin is free software: you can redistribute it and/or modify it under the terms of the **GNU General Public License (GPL)** as published by the Free Software Foundation, either version 3 of the License, or (at your option) any later version.

* **License**: GNU General Public License v3.0 or later ([GPL-3.0-or-later](http://www.gnu.org/copyleft/gpl.html))
* **Copyright**: © 2026 Emanuel Dickson Wanyonyi <wanyonyi.d.emanuel@gmail.com>
* **Moodle Directory Compliance**: Fully compliant with Moodle Plugins Directory open-source publishing guidelines.
