# Implementation Plan — MasjidOS Pro Architecture (Members, Donations, Accounts)

This plan details the architectural split of MasjidOS modules. The **Events Module** remains in the free core plugin, while the **Members**, **Donations**, and **Accounts** modules will be moved into a separate premium plugin: **MasjidOS Pro** (`masjidos-pro`).

To facilitate this, we will first introduce extensibility hooks (WordPress filters and global JS hooks) in the free `masjidos` plugin, and then build the `masjidos-pro` plugin from scratch.

## Proposed Changes

### Component 1: Free Core Plugin Extensibility Hooks

We will modify the core `masjidos` plugin to expose hooks for default settings, module definitions, dashboard stats, and admin script dependencies.

#### [MODIFY] class-itmms-settings.php
- Apply `masjidos_defaults` filter in `defaults()` so the Pro plugin can append its default settings.
- Apply `masjidos_module_definitions` filter in `module_definitions()` so the Pro plugin can register the details of its modules for the Module Manager UI.

#### [MODIFY] class-itmms-installer.php
- Remove the schema definitions for `itmms_members`, `itmms_donations`, and `itmms_accounts` tables (these will be moved to the Pro plugin installer).

#### [MODIFY] class-itmms-rest.php
- Apply `masjidos_dashboard_data` filter in `get_dashboard()` so the Pro plugin can inject custom stats and items into the main dashboard REST response.

#### [MODIFY] class-itmms-admin.php
- Apply `masjidos_admin_dependencies` filter to the main script registration so the Pro plugin can insert its JS modules as dependencies of the main SPA script.

#### [MODIFY] app.js
- Refactor the sidebar navigation and tab panels rendering to support dynamic items defined in a global `window.itmms.customTabs` array.
- Call custom tab lifecycle hooks: `bindEvents()`, `loadData()`, and render matching titles dynamically.

#### [MODIFY] dashboard.js
- Expose global arrays `window.itmms.dashboardStats` and `window.itmms.dashboardHealth` to let custom modules register dashboard statistics card details and system health items dynamically.

---

### Component 2: New Premium Plugin (MasjidOS Pro)

We will create a new plugin in the directory `wp-content/plugins/masjidos-pro` which registers itself, verifies that the free plugin is active, and loads the premium modules.

#### [NEW] masjidos-pro.php
- Main plugin file defining plugin headers and checking if `ITMMS_Core` class exists on `plugins_loaded`.
- If the free plugin is missing, render an admin notice and halt execution.
- Load the main Pro bootstrap class.

#### [NEW] class-itmms-pro.php
- Central singleton loader for MasjidOS Pro.
- Registers activations hooks, bootstraps Pro REST routes, and enqueues JS/CSS files.
- Registers filters on `masjidos_defaults`, `masjidos_module_definitions`, and `masjidos_admin_dependencies` to inject Pro modules.

#### [NEW] class-itmms-pro-installer.php
- Creates `itmms_members`, `itmms_donations`, and `itmms_accounts` tables on Pro activation using `dbDelta`.
- Configures custom capabilities and adds them to administrators.

#### [NEW] CRUD Repositories:
- class-itmms-members.php (Members CRUD operations)
- class-itmms-donations.php (Donation logger and summary statistics)
- class-itmms-accounts.php (Bookkeeping flow logger and calculation summaries)

#### [NEW] class-itmms-pro-rest.php
- Registers REST endpoints for Pro modules:
  - `/members` (GET/POST/PUT/DELETE)
  - `/donations` (GET/POST/PUT/DELETE)
  - `/accounts` (GET/POST/PUT/DELETE)
  - `/financials/public` (GET - Public income/expense statement)
- Hooks into `masjidos_dashboard_data` to append Pro statistics (total donations, active members, ledger balance) to the dashboard REST response.

#### [NEW] SPA Frontend UI Modules:
- members.js (UI forms, list, search for members directory)
- donations.js (UI forms, list, total donation stats card)
- accounts.js (UI forms, list, financial statement summaries)
- Registers each as a custom tab in `window.itmms.customTabs` and hooks stats into the dashboard.

#### [NEW] Public templates and Public shortcode loader:
- class-itmms-pro-public.php (Exposes public shortcode `[masjidos_financials]`)
- financials.php (Sleek HTML/CSS public bookkeeping report layout)

---

## Verification Plan

### Automated Verification
- Sequentially run PHP syntax check: `php -l`.

### Manual Verification
1. Activate the core **MasjidOS** plugin and verify the dashboard works.
2. Activate the **MasjidOS Pro** plugin. Check that no PHP warnings or errors occur.
3. Verify that the **Members**, **Donations**, and **Accounts** modules immediately appear in the **Module Manager** list in the admin page.
4. Enable the Pro modules in settings and verify that their navigation items appear in the sidebar.
5. Perform CRUD operations in each tab (Members, Donations, Accounts) and verify correct storage and updates.
6. Verify the dashboard stats cards update with the total donations and active members counts.
7. Test the public shortcode `[masjidos_financials]` on a frontend WordPress page.
