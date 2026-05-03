# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## What this is

NXTM is a personal productivity web app (task tracker, link manager, memos, lists) built in plain PHP with MySQL. No framework, no build step — files are deployed directly to a shared hosting account at antonizick.com via FTP.

## Development

There is no local dev server configuration in the repo. To test changes, upload the modified `.php` files to the hosting account. The database is a remote MySQL instance defined in `dbcon.php` (`127.0.0.1:3308`, database `antonizi_nxtask`).

`php.ini` in the root is picked up by the hosting server and controls PHP settings (error suppression, timezone, upload limits).

## Authentication

Every page begins with `require_once 'auth_check.php'`, which enforces session authentication — AJAX requests get a 401 JSON response, regular requests redirect to `login.php`.

Login is two-step: password verification → TOTP (Google Authenticator). User accounts are stored in `data/users.json` via `lib/UserStore.php`. The first run redirects to `setup.php` to create the initial account.

## Module pattern

Each feature module follows the same file convention:

| File | Purpose |
|---|---|
| `<name>.php` | Main page — renders the DataTables table and modal |
| `<name>data-fetcher.php` | AJAX endpoint — returns `{"data": [...]}` for DataTables |
| `<name>action.php` | Edit/Delete form — opened as a page from row action buttons |
| `<name>recordadd.php` | INSERT handler — POST only, then `header('Location: ...')` redirect |
| `<name>archive-fetcher.php` | Like data-fetcher but queries the archive view |

Module prefixes: *(none)* = W.Tasks, `p` = P.Tasks, `l` = Links, `list`/`listList` = Lists, `memo` = Memos, `mx` = Admin Categories, `mx2` = Admin Statuses.

**Bootstrap 4 modal pattern** (used for Add Task, Add Item, etc.): the `<form>` wraps the entire modal div; the button in the header uses `data-toggle="modal" data-target="#id"`; the footer has a `data-dismiss="modal"` Cancel and a submit `<input type="submit">`. Bootstrap JS is loaded from CDN at the bottom of every page.

**`dataLists` seed-row convention**: a new list is created by inserting one placeholder row with `in1=999, in2=0, in3=0, Name='', dat1='', dat2='', dat3='.', dat4='.'` and the user-supplied value as `lcode`. The `viewListList` view (which powers `listdata-fetcher.php`) shows distinct `lcode` values, so the new list appears immediately. Handler: `listnewlist.php`.

## Database

All queries use MySQLi prepared statements (`$conn->prepare()`). The connection is opened by including `dbcon.php`. The main pages query DB views (e.g. `viewTasks`, `viewMemo`) rather than tables directly. Insert/update operations target the underlying tables (e.g. `datatasks`, `dataMemo`).

## Frontend

- jQuery 3.6 + DataTables (with Buttons extension for CSV/PDF export) — all vendored in `DataTables/`
- Bootstrap (CSS only, no JS) — vendored in `Bootstrap/`
- Custom styles in `nxstyle.css`
- Navigation bar is `nxmenu.php` — included on every page with `$active_page` set before the include to highlight the current tab

## Deployment notes

- `.htaccess` has hotlink prevention rules tied to antonizick.com — don't remove them
- `data/.htaccess` blocks direct web access to `users.json`
- No git remote; no CI/CD pipeline
