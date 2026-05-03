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
