# PractiLink V2 — Senior Audit and Remediation Report

## Executive summary

PractiLink is a Laravel-based practical-training application with two authenticated account domains: staff/admin users and students. The main process is implemented as a configurable workflow: a student selects an open application window, creates or updates a draft, uploads required documents, submits the application, and then follows workflow history through review, acceptance/rejection, and placement. Staff users manage windows, workflows, applications, organizations, placements, letters, roles, permissions, notifications, and audit records.

This review covered route composition, authentication and authorization middleware, student application and document flows, configurable workflow services, placement lifecycle, database constraints, migrations, controllers, models, views, and the shared AdminLTE-based presentation layer. The remediation focused on high-impact correctness and security risks without changing the product’s intended workflow model.

## Intended end-to-end workflow

1. A student authenticates through the `students` guard and is routed into the student workspace.
2. The student sees currently open, active application windows and submits a draft for one window. The database already enforces one application per student/window pair.
3. The student completes the application narrative, academic year, and training dates. The draft is editable while it is `DRAFT` or `RETURNED`.
4. The student uploads one document per active document type. Existing files are replaced by document type, and files are stored on the private local disk rather than a public web directory.
5. Submission validates the window, required fields, dates, and required documents, changes the application to `SUBMITTED`, starts the published workflow version, and records an audit event.
6. Staff users review applications according to permissions and configured workflow transitions. Each transition records the actor, comment, source stage, destination stage, and timestamp.
7. Accepted applications may be allocated to an active organization with matching training dates. Placement status then progresses from `ALLOCATED` to `ACTIVE` to `COMPLETED`, with cancellation available before completion.
8. Staff may issue placement letters. Students can view their placement letter through an ownership-checked route.

## Findings and remediation

| Area | Finding | Risk | Remediation |
|---|---|---:|---|
| Submission integrity | Application status was updated before workflow startup. If no published workflow existed or workflow creation failed, the transaction could leave a submitted application without a workflow. | High | Wrapped status update, workflow startup, and submission audit logging in one database transaction. A failed workflow startup now rolls the application back. |
| Concurrent submission | Two simultaneous submit requests could both pass the initial editable-state check. | High | Refresh and re-check the application state inside the transaction before final submission. The existing unique workflow constraint remains the final database guard. |
| Application window | Draft creation loaded any window by ID before checking whether it was active. | Medium | Draft creation now resolves only active windows and then verifies the time window is open. |
| File validation | The document service verified PDF, JPEG, and PNG signatures but treated DOCX as valid based only on extension/MIME. | High | DOCX now requires a valid ZIP container containing both `[Content_Types].xml` and `word/document.xml`; unknown extensions fail closed. |
| Document access | Admin preview/download routes used a route group permission of `applications.view`, while the controller required `applications.review`, causing authorized viewers to receive 403 responses. | Medium | Controller authorization now matches the route’s `applications.view` capability while retaining application ownership checks for students. |
| Placement organization | Placement creation accepted an organization that existed but was inactive. | Medium | Placement allocation now requires the selected organization to be active. |
| Placement races | Placement creation and placement history writes were separate operations, allowing inconsistent partial records under failure or concurrent requests. | High | Wrapped placement creation, initial history, and audit logging in one transaction and re-checked accepted/no-existing-placement state inside it. |
| Placement lifecycle | Any listed placement status could previously be submitted from any current status, including reopening completed or cancelled placements. | High | Enforced explicit transitions: `ALLOCATED → ACTIVE/CANCELLED`, `ACTIVE → COMPLETED/CANCELLED`, terminal states immutable. The state is re-checked inside the transaction. |
| Workflow races | Concurrent reviewer actions could read the same current stage and both apply transitions. | High | Workflow rows are now locked with `lockForUpdate()` during transition evaluation and write. |
| UI consistency | The shared application stylesheet is present and already provides a coherent blue/green visual system, responsive authentication pages, focus states, and reduced-motion support. | Low | Preserved the existing visual system rather than introducing a conflicting redesign. The audit confirms the shared layout uses the stylesheet and that page hierarchy, navigation grouping, cards, tables, and mobile breakpoints are aligned. |

## Security and operational review

The application has several sound foundations: CSRF protection is provided by Laravel’s web middleware; staff access is permission-gated; student application routes use the students guard; route model ownership is checked before viewing or mutating student records; uploaded files are stored on the private disk; uploaded files receive UUID-backed server names; file hashes support duplicate detection; audit logging exists for major state changes; password fields use Laravel’s hashed casts; and session serialization is configured to JSON.

The remediation strengthens those controls at the transaction boundary. The remaining production hardening checklist is as follows:

- Use HTTPS in every deployed environment and set `SESSION_SECURE_COOKIE=true` in production.
- Keep `APP_DEBUG=false` outside local development and rotate `APP_KEY` only through a planned credential-rotation procedure.
- Configure a durable queue and mail transport for notifications rather than relying on synchronous delivery.
- Move private application documents to a managed private object-storage disk with short-lived signed downloads when operating at scale.
- Add rate limiting to login, registration, document upload, and repeated workflow-action endpoints.
- Add malware scanning and quarantine for uploaded files before staff download or preview.
- Add database-level checks or domain services for any future status additions; do not rely on UI dropdowns for lifecycle integrity.
- Configure log retention and alerting for repeated authorization failures, upload failures, and unexpected workflow exceptions.
- Use a production CSP and remove or self-host CDN assets if the deployment security policy requires strict third-party isolation.

## Resource and performance observations

The code uses eager loading for the principal student and staff screens, which avoids the largest obvious N+1 paths. The current list controllers load complete collections with `get()`. This is acceptable for a small pilot but should be changed to pagination before production-scale data volume. The following follow-up improvements are recommended:

- Paginate applications, placements, notifications, organizations, and audit log views.
- Add filters by status, window, training type, organization, and date range before lists become large.
- Cache active reference data and published workflow definitions with explicit invalidation on updates.
- Queue notification delivery and document scanning.
- Add indexes for common operational filters such as application status plus submitted date and placement status plus start date.

## UX/UI review

The visual system communicates a professional operations product: dark blue navigation establishes information architecture, green accents signal progress/success, cards group tasks, and the authentication pages provide a distinct, polished entry experience. Navigation is separated into Dashboard, Notifications, Student, Operations, and Account sections. Student and staff menu items are conditionally shown according to the active account and permissions, reducing irrelevant choices.

The most important UX principle for the next iteration is to make workflow state the primary object on every application page. The student show page already includes the current stage and history; the next enhancement should be a compact progress stepper with the current stage highlighted, a clear “what happens next” explanation, and a single primary action. Operational pages should use filters and paginated tables rather than requiring staff to scan unbounded lists. Destructive actions should remain visually separated from primary actions and use accessible confirmation dialogs rather than inline browser prompts.

## Files changed

- `app/Http/Controllers/StudentApplicationController.php` — active-window enforcement and atomic submission.
- `app/Services/ApplicationDocumentService.php` — fail-closed file signatures and DOCX structure validation.
- `app/Http/Controllers/StudentApplicationDocumentController.php` — corrected admin view permission alignment.
- `app/Http/Controllers/AdminPlacementController.php` — active organization validation, transactional placement writes, and explicit lifecycle transitions.
- `app/Services/WorkflowService.php` — row locking during workflow transitions.
- `PRACTILINK_AUDIT_REPORT.md` — this report.

## Validation

- PHP syntax validation was run across application, route, migration, bootstrap, configuration, and test PHP files with no syntax errors.
- `git diff --check` passed.
- `npm ci` completed with no reported vulnerabilities and `npm run build` completed successfully. Vite emitted only an optional `fontaine` optimization notice.
- The repository lockfile requires PHP `>=8.4.1` through Symfony 8. The base sandbox PHP runtime is 8.3.6, so a normal `composer install` cannot be completed without either PHP 8.4+ or a dependency update. Installing with the platform requirement ignored reached Laravel’s Composer post-autoload hook, but the locked Symfony code uses PHP 8.4 syntax and cannot be executed by PHP 8.3.6. The application test suite therefore remains pending on a PHP 8.4+ runner; this is an environment compatibility issue, not a test failure in the changed code.

## Pull request scope

This pull request intentionally keeps the existing product architecture and visual language. It concentrates on state integrity, race-condition resistance, secure document validation, authorization consistency, and operational lifecycle correctness. Larger product enhancements—pagination, queued scanning, signed object-storage downloads, and a visual workflow stepper—are recorded as follow-up work rather than mixed into the safety-critical remediation.
