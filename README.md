# NXTM

A personal productivity web app (task tracker, link manager, memos, lists) built in plain PHP with MySQL. No framework, no build step — files are deployed directly to a shared hosting account at antonizick.com via FTP.

## Features

- **Work Tasks** — full task tracker with priority, urgency, deadlines, projects, and status
- **Personal Tasks** — separate task list for personal items
- **Links** — save and organize links with custom categories and status
- **Lists** — multi-item lists with custom list definitions
- **Memos** — quick notes and memos with archive support
- **Admin** — manage categories and statuses used across modules

## Tech Stack

| Layer | Technology |
|---|---|
| Backend | PHP (plain, no framework) |
| Database | MySQL (remote, 127.0.0.1:3308) |
| Frontend | Bootstrap 4 (CSS only), jQuery 3.6, DataTables (with Buttons) |
| Auth | Session-based, two-step login (password + TOTP/Google Authenticator) |

## Project Structure

```
├── dbcon.php              # Database connection
├── auth_check.php         # Session authentication (required on every page)
├── setup.php              # Initial account creation
├── login.php              # Login page
├── nxmenu.php             # Navigation bar (included on every page)
├── nxstyle.css            # Custom styles
├── CLAUDE.md              # Claude Code guidance
├── php.ini                # PHP settings (error suppression, timezone, upload limits)
├── dataTasks.php          # Work Tasks module
├── ptask.php              # Personal Tasks module
├── link.php               # Links module
├── listlist.php           # Lists module
├── memopage.php           # Memos module
├── mxcat.php              # Admin Categories module
├── mxstatus.php           # Admin Statuses module
├── *.php                  # Handlers (recordadd, action, data-fetcher, etc.)
├── lib/
│   ├── GoogleAuthenticator.php
│   └── UserStore.php
├── data/
│   └── users.json         # User accounts (blocked from web access)
└── DataTables/            # DataTables vendored assets
└── Bootstrap/             # Bootstrap CSS vendored
└── qrcodejs/              # QR code renderer vendored (used by setup.php)
```

## Module Pattern

Each feature follows a consistent file convention:

| File | Purpose |
|---|---|
| `<name>.php` | Main page — renders DataTables table and add modal |
| `<name>data-fetcher.php` | AJAX endpoint — returns `{"data": [...]}` for DataTables |
| `<name>action.php` | Edit/Delete form — opened from row action buttons |
| `<name>recordadd.php` | INSERT handler — POST only, then redirect |
| `<name>archive-fetcher.php` | Archive view data endpoint |

### Module Prefixes

| Prefix | Feature |
|---|---|
| *(none)* | Work Tasks |
| `p` | Personal Tasks |
| `l` | Links |
| `list`/`listList` | Lists |
| `memo` | Memos |
| `mx` | Admin Categories |
| `mx2` | Admin Statuses |

### Modal Form Pattern

Add/edit modals wrap the entire `<form>` around the modal div. The header button uses `data-toggle="modal" data-target="#id"`. The footer has a `data-dismiss="modal"` Cancel button and a submit `<input type="submit">`. Grid layout in `modal-body` (`grid-template-columns: 160px 1fr`) aligns labels with inputs.

### List Seed-Row Convention

New list items are created by inserting a placeholder row (`in1=999, in2=0, in3=0, Name='', dat1='', dat2='', dat3='.', dat4='.'`) with the user-supplied value as `lcode`. The `viewListList` view shows distinct `lcode` values, so the new list appears immediately.

## Database

- All queries use MySQLi prepared statements (`$conn->prepare()`)
- Main pages query DB views (e.g. `viewTasks`, `viewMemo`, `viewListList`) rather than tables directly
- Insert/update operations target the underlying tables (e.g. `datatasks`, `dataMemo`, `dataLists`)

## Authentication

Every page begins with `require_once 'auth_check.php'`, which enforces session authentication:

- AJAX requests get a `401` JSON response
- Regular requests redirect to `login.php`

Login is two-step: password verification followed by TOTP (Google Authenticator) code.

## Development

There is no local dev server configuration. To test changes:

1. Modify the `.php` files locally
2. Upload them to the hosting account via FTP

The database connection is configured in `dbcon.php` pointing to a remote MySQL instance.

## Deployment

- No git remote; no CI/CD pipeline
- Files are deployed directly via FTP to the hosting account
- `.htaccess` has hotlink prevention rules tied to antonizick.com
- `data/.htaccess` blocks direct web access to `users.json`

## Screenshots

<img src="screenshots/NXTM 2026-05-03 17_43_05-Llama Claude.png">
<img src="screenshots/NXTM 2026-05-03 17_43_37-Llama Claude.png">
<img src="screenshots/NXTM 2026-05-03 17_44_29-Llama Claude.png">






# TL;DR Quick Local Install

For anyone who just wants the commands. Full explanations, troubleshooting, and the manual (no-script) path are in **Local Deployment** below if anything here doesn't just work.

**In PowerShell** (only if you don't already have WSL):
```
wsl --install
```

**In PowerShell:**
```
wsl --install -d Ubuntu
```

**In your Ubuntu terminal** (log back in first if you closed it — see Local Deployment below):
```bash
sudo apt update && sudo apt upgrade -y
```

**Still in Ubuntu:**
```bash
sudo mkdir -p /var/www/nxtm && sudo chown $USER:$USER /var/www/nxtm
git clone https://github.com/antonizick/NX-Task-Manager.git /var/www/nxtm
cd /var/www/nxtm
bash install.sh
```

# Local Deployment

**Here are explicit, step-by-step instructions to install WSL (Windows Subsystem for Linux) and set up an Ubuntu instance on Windows 11.**

### Prerequisites
- You need Windows 11 (or Windows 10 version 2004/build 19041 or higher).
- Administrative privileges on your machine.
- An internet connection.

### Step 1: Install WSL (Recommended One-Command Method)
1. Open **PowerShell** as Administrator:
   - Click the Start button, type **PowerShell**, right-click **Windows PowerShell**, and select **Run as administrator**.

2. Run this command:
   ```
   wsl --install
   ```
   - This single command does the following:
     - Enables the required Windows features (WSL and Virtual Machine Platform).
     - Downloads and installs the latest Linux kernel.
     - Sets WSL 2 as the default version.
     - Installs the default Ubuntu distribution.

3. Restart your computer when prompted.

### Step 2: Complete Ubuntu Setup (First Launch)
1. After restarting, open the **Start menu** and search for **Ubuntu** (or launch it from the PowerShell output).
2. The first time it runs, it will download and install Ubuntu (this may take a few minutes).
3. You will be prompted to create a **UNIX username** (can be different from your Windows username) and a **password**.
   - Type the username and press Enter.
   - Type the password (it won't show as you type) and press Enter, then confirm it.
4. Once complete, you'll see the Ubuntu bash prompt (`$`). **You are now logged into your Ubuntu instance** — this is a real Linux shell, separate from PowerShell/Windows. Everything from here through the end of "Installing NXTM" below happens in this Ubuntu prompt, not in PowerShell.

**Logging out and back in**, if you close the window or want to start over:
- **To log out**: type `exit` (or press Ctrl+D) at the Ubuntu `$` prompt. This drops you back to PowerShell/Windows.
- **To log back in as your user** (not root, and into the right distro if you have more than one installed): don't just type bare `wsl` — if you have another WSL distro already installed (Docker Desktop's `docker-desktop` distro is a common one that sneaks in), plain `wsl` launches whichever one is currently the *default*, which may not be the Ubuntu you just set up, and a distro that's never had its first-launch wizard completed drops you in as **root** instead of your user.

  1. Check what's installed and see the exact name of your distro:
     ```
     wsl -l -v
     ```
     (**run in PowerShell**). Look for the `*` marking the default, and note the exact `NAME` column value (e.g. `Ubuntu` or `Ubuntu-24.04`).
  2. Launch that specific distro as your specific user (**in PowerShell**):
     ```
     wsl -d <NAME> -u <your-username>
     ```
     Replace `<NAME>` with what you saw above and `<your-username>` with the UNIX username you created in step 3.
  3. Optional — make that the default for plain `wsl`/`ubuntu` going forward: `wsl --set-default <NAME>` (**in PowerShell**) makes it the default *distro*; to make it always log in as your user instead of root, run `wsl.exe --manage <NAME> --set-default-user <your-username>` (**in PowerShell**, WSL 2.x+) — or, from inside the distro, add to `/etc/wsl.conf`:
     ```ini
     [user]
     default=your-username
     ```
     then from PowerShell run `wsl --shutdown` and relaunch.

  You do **not** need to redo Steps 1–2 for any of this; WSL keeps your Linux install persistent between launches.

### Alternative: Install a Specific Ubuntu Version
If you want a particular version (e.g., Ubuntu 24.04), run this **in Administrator PowerShell** (not Ubuntu):
  ```
  wsl --install -d Ubuntu
  ```
Or list available distributions first (also **in PowerShell**):
  ```
  wsl --list --online
  ```

### Step 3: Verify and Update Everything
1. **In your Ubuntu terminal** (log back in first if you closed it — see above), update the package list and upgrade packages:
   ```
   sudo apt update && sudo apt upgrade -y
   ```
2. **In PowerShell/Windows Terminal** (not Ubuntu), check the WSL version (should show 2):
   ```
   wsl --version
   ```

To confirm your distro, run this **in PowerShell**:
```
wsl -l -v
```

Install common tools — **in your Ubuntu terminal**:
```
sudo apt install build-essential git curl -y
```

### Useful Commands and Tips
- **Launch Ubuntu** (from Windows): Type `ubuntu` in Start, or type `wsl` in PowerShell/Terminal.
- **Open Windows files from inside Ubuntu**: your Windows files are at `/mnt/c/` (e.g., `cd /mnt/c/Users/YourName`) — but don't put the NXTM code there; see Step 2 of "Installing NXTM" below for why.
- **Set default distro** (if you have multiple) — run **in PowerShell**:
  ```
  wsl --set-default Ubuntu
  ```
- **Shutdown WSL** (when done) — run **in PowerShell**:
  ```
  wsl --shutdown
  ```
- **Update WSL kernel** — run **in PowerShell**:
  ```
  wsl --update
  ```

### Troubleshooting
- **Error about virtualization**: Ensure Virtualization is enabled in BIOS/UEFI (restart PC → enter BIOS → look for Intel VT-x or AMD SVM).
- **"WSL not installed"**: Rerun `wsl --install` or check Windows Updates.
- **Permission issues**: Always use Administrator PowerShell for setup commands.
- For more help: Run `wsl --help` or visit the official Microsoft docs.

You should now have a fully functional Ubuntu environment integrated with Windows 11. You can run Linux commands, tools, and even GUI apps (with WSLg on Windows 11). Enjoy!

---

# Installing NXTM

Everything below runs **inside your Ubuntu/WSL shell** (the `$` prompt from Step 2 above).

## Quick Install (recommended)

`install.sh`, at the root of this repo, automates every step below: installs Apache/PHP/MySQL, clones the code if it isn't already present, creates the database with a random password, imports the schema/seed, configures `dbcon.php`/`php.ini`/Apache, and locks down `data/` — with progress commentary as it goes, and retry/skip/abort prompts if any individual step fails instead of just dying.

```bash
sudo mkdir -p /var/www/nxtm && sudo chown $USER:$USER /var/www/nxtm
git clone https://github.com/antonizick/NX-Task-Manager.git /var/www/nxtm
cd /var/www/nxtm
bash install.sh
```

It's safe to re-run — every step checks current state first and skips what's already done (existing database, existing `dbcon.php`, etc.), so if it stops partway through (or you chose "skip" on a step), just run it again.

Before touching Apache, it checks a handful of common ports (80, 8080, 8081, 8000, 8888, 3000) and tells you which are already taken on your machine. If port 80 is free it's used by default; otherwise you're shown the free ones and asked to pick (or the first free one is picked automatically with `--yes`). Reruns reuse whatever port you picked the first time instead of asking again.

Flags:
- `--reset-db` — drop and re-import the schema/seed (destructive, asks to confirm)
- `--password=SECRET` — use this DB password instead of auto-generating one
- `--yes` — auto-confirm prompts
- `--app-dir=DIR` — install somewhere other than `/var/www/nxtm`
- `--port=NNNN` — serve on this port instead of being asked/recommended one

When it finishes, skip to **Step 8: Create your account (first run)** below — the rest is manual/reference documentation for what the script just did, useful if a step needs troubleshooting.

## Manual Install (step-by-step, or if the script fails)

## Step 1: Install Apache, PHP, and MySQL

```bash
sudo apt update
sudo apt install -y apache2 mysql-server php libapache2-mod-php php-mysqli php-cli git
```

- **PHP 8.1 or newer** is required (the `data/`-backed user store and TOTP library use typed properties). `apt install php` gives you whatever version ships with your Ubuntu release (8.1 on 22.04, 8.3 on 24.04) — either works.
- **MySQL, not MariaDB.** The schema uses the `utf8mb4_0900_ai_ci` collation, which is MySQL-8-specific and doesn't exist on MariaDB. `apt install mysql-server` on modern Ubuntu installs real MySQL 8, so this is already correct — just don't substitute `mariadb-server`.
- Confirm both installed and running:
  ```bash
  php -v
  sudo service mysql start
  sudo service apache2 start
  ```
  (WSL doesn't run services automatically on boot — `service <name> start` after opening a new shell.)

## Step 2: Get the code

```bash
sudo mkdir -p /var/www/nxtm
sudo chown $USER:$USER /var/www/nxtm
git clone https://github.com/antonizick/NX-Task-Manager.git /var/www/nxtm
cd /var/www/nxtm
```
Clone into the Linux filesystem under `/var/www` — not `/mnt/c/...` (file I/O through the Windows filesystem bridge is much slower) and not under your home directory either: Ubuntu creates home directories as `750`, which blocks Apache (`www-data`) from traversing into them at all and gets you a flat 403 regardless of anything inside. `/var/www` is world-traversable by default, which is why it's the standard place to put anything Apache serves.

## Step 3: Create the database

```bash
sudo mysql -e "CREATE DATABASE antonizi_nxtask CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci;"
sudo mysql -e "CREATE USER 'nxtm'@'127.0.0.1' IDENTIFIED BY 'change-this-password';"
sudo mysql -e "GRANT ALL PRIVILEGES ON antonizi_nxtask.* TO 'nxtm'@'127.0.0.1';"
sudo mysql -e "FLUSH PRIVILEGES;"

mysql -h 127.0.0.1 -u nxtm -p antonizi_nxtask < db/schema.sql
mysql -h 127.0.0.1 -u nxtm -p antonizi_nxtask < db/seed.sql
```
Two things to note about these commands:

- **The account host must be `127.0.0.1`, not `localhost`.** MySQL treats `'user'@'localhost'` as "only allow this login over the local Unix socket" — it will never match a TCP connection, even one to `127.0.0.1` from the same machine. The two `mysql -h 127.0.0.1 ...` import commands below connect over TCP (see next bullet for why), so the account has to be created for that exact host or every login attempt fails with "Access denied" regardless of password.
- **`-h 127.0.0.1` forces a TCP connection** instead of the local Unix socket — on some Ubuntu/WSL setups the socket directory `/var/run/mysqld` isn't world-traversable, which makes the plain `mysql -u nxtm -p` form fail with a permission error even though the credentials are correct.
- The last two commands will each prompt `Enter password:` — type the same password you used above (`change-this-password`, or whatever you changed it to). Nothing appears on screen as you type; that's normal, just type it and press Enter.

`db/schema.sql` creates the 8 base tables and 10 views the app queries (`viewTasks`, `viewMemo`, etc — see [Database](#database) above). `db/seed.sql` adds the color-lookup rows the views join against, plus a few generic Category/Status rows so the Add Task dropdowns aren't empty on first load — rename or delete them from Admin once you're logged in.

## Step 4: Point the app at your database

Edit `dbcon.php` and replace the placeholders with what you just created:

```php
$host     = '127.0.0.1';
$user     = 'nxtm';
$password = 'change-this-password';
$database = 'antonizi_nxtask';
```

(Production points at a remote tunnel, `127.0.0.1:3308` — locally, just `127.0.0.1` on MySQL's default port 3306.)

## Step 5: Match PHP's error reporting to production

This matters more than it looks: the `*data-fetcher.php` AJAX endpoints `echo json_encode(...)` with nothing else — if PHP prints so much as a stray deprecation notice above that, the response stops being valid JSON and every DataTable on the page silently breaks. Production's `php.ini` suppresses these; a stock Ubuntu PHP install doesn't. Find your php.ini and add the same settings:

```bash
sudo find /etc/php -name php.ini   # locate the apache2 SAPI's php.ini, e.g. /etc/php/8.3/apache2/php.ini
```

Add/edit these lines in that file:

```ini
error_reporting = E_ALL & ~E_STRICT & ~E_NOTICE & ~E_DEPRECATED
expose_php = Off
post_max_size = 60M
upload_max_filesize = 60M
memory_limit = 3000M
max_execution_time = 120
session.save_path = /tmp
date.timezone = America/New_York
```

(`date.timezone` — use whatever your own timezone is; it just needs to be a valid PHP timezone identifier, not left blank.)

Restart Apache to pick it up:
```bash
sudo service apache2 restart
```

## Step 6: Let the web server write to `data/`

First login creates `data/users.json`; Apache's user needs write access to the `data/` directory:

```bash
sudo chown -R www-data:www-data /var/www/nxtm/data
```

## Step 7: Serve the app

Point Apache's default site at the repo instead of the standard `/var/www/html`:

```bash
sudo sed -i "s#DocumentRoot .*#DocumentRoot /var/www/nxtm#" /etc/apache2/sites-available/000-default.conf
```

Ubuntu's default Apache config sets `AllowOverride None` for `/var/www/`, which silently disables `.htaccess` — including `data/.htaccess`, whose whole job is blocking direct access to `data/users.json` (your password hashes). Without this next step, that file is served as plain text to anyone who requests it:

```bash
sudo tee -a /etc/apache2/sites-available/000-default.conf > /dev/null <<'EOF'
<Directory /var/www/nxtm>
    AllowOverride All
</Directory>
EOF
sudo a2enmod rewrite
sudo service apache2 restart
```

Visit **http://localhost/** from your Windows browser — WSL2 forwards `localhost` automatically, no extra networking setup needed.

**Using a different port?** If port 80 is already taken by something else on your machine, pick another (e.g. `8080`): change the `<VirtualHost *:80>` line in `000-default.conf` to `<VirtualHost *:8080>`, add `Listen 8080` to `/etc/apache2/ports.conf` (and remove the stock `Listen 80` line there — leaving it in place makes Apache fail to start at all if something else holds port 80, not just fail to use it), then restart Apache and visit `http://localhost:8080/`. `install.sh` (see Quick Install above) does all of this automatically and will recommend a free port for you.

## Step 8: Create your account (first run)

With no `data/users.json` yet, you'll land on `setup.php` automatically:

1. Pick a username and password (8+ characters).
2. Choose one:
   - **Set up two-factor** — you'll get a QR code (rendered locally, no CDN/internet needed to display it) — scan it with an authenticator app (Google Authenticator, Authy, Aegis, etc.) and enter the 6-digit code it shows to confirm.
     - No phone handy? `sudo apt install oathtool` then `oathtool --totp -b <secret-from-the-page>` prints the same 6-digit code from the command line.
   - **Skip — use password only** — creates the account with no second factor. Login is then just username + password, no authenticator app needed. You can't add 2FA to that account later without editing `data/users.json` by hand.
3. You're redirected to `login.php` — log in with your new credentials, and you're in.

## Troubleshooting

- **Blank page / 500 error**: check `sudo tail -f /var/log/apache2/error.log` while reloading — almost always a missing PHP extension (`php-mysqli`) or a `dbcon.php` credential typo.
- **DataTables show "Ajax error" or won't load rows**: usually the `php.ini` error-reporting mismatch from Step 5 — a warning is leaking into the JSON response. Check the raw response of e.g. `data-fetcher.php` in the browser's Network tab.
- **`data/users.json` won't save / setup.php loops back to itself**: `data/` isn't writable by `www-data` — redo Step 6.
- **"Access denied for user"**: re-check the `GRANT` in Step 3 and the credentials in `dbcon.php` match exactly. If you created the account as `'nxtm'@'localhost'` instead of `'nxtm'@'127.0.0.1'`, that's the whole problem — see the note in Step 3; `localhost` accounts never work over the TCP connection these instructions use.
- **Port 3308 confusion**: that port is only used by the *production* tunnel in `dbcon.php`'s original placeholder comment. Locally you're talking to MySQL directly on its default port (3306), not through a tunnel.
- **Apache won't start / "Address already in use"**: something else already has that port. `sudo ss -ltn` shows what's listening where. If you're on a custom port, make sure the stock `Listen 80` line was actually removed from `/etc/apache2/ports.conf` (see Step 7) — leaving it in place makes Apache fail to start *at all* whenever port 80 is taken, even if your vhost itself uses a different port.
