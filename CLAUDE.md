# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

<laravel-boost-guidelines>
=== foundation rules ===

# Laravel Boost Guidelines

The Laravel Boost guidelines are specifically curated by Laravel maintainers for this application. These guidelines should be followed closely to ensure the best experience when building Laravel applications.

## Foundational Context

This application is a Laravel application and its main Laravel ecosystems package & versions are below. You are an expert with them all. Ensure you abide by these specific packages & versions.

- php - 8.5
- laravel/fortify (FORTIFY) - v1
- laravel/framework (LARAVEL) - v13
- laravel/prompts (PROMPTS) - v0
- livewire/flux (FLUXUI_FREE) - v2
- livewire/livewire (LIVEWIRE) - v4
- laravel/boost (BOOST) - v2
- laravel/mcp (MCP) - v0
- laravel/pail (PAIL) - v1
- laravel/pint (PINT) - v1
- laravel/sail (SAIL) - v1
- pestphp/pest (PEST) - v4
- phpunit/phpunit (PHPUNIT) - v12
- tailwindcss (TAILWINDCSS) - v4

## Skills Activation

This project has domain-specific skills available in `**/skills/**`. You MUST activate the relevant skill whenever you work in that domain—don't wait until you're stuck.

## Conventions

- You must follow all existing code conventions used in this application. When creating or editing a file, check sibling files for the correct structure, approach, and naming.
- Use descriptive names for variables and methods. For example, `isRegisteredForDiscounts`, not `discount()`.
- Check for existing components to reuse before writing a new one.

## Verification Scripts

- Do not create verification scripts or tinker when tests cover that functionality and prove they work. Unit and feature tests are more important.

## Application Structure & Architecture

- Stick to existing directory structure; don't create new base folders without approval.
- Do not change the application's dependencies without approval.

## Frontend Bundling

- If the user doesn't see a frontend change reflected in the UI, it could mean they need to run `npm run build`, `npm run dev`, or `composer run dev`. Ask them.

## Documentation Files

- You must only create documentation files if explicitly requested by the user.

## Replies

- Be concise in your explanations - focus on what's important rather than explaining obvious details.

=== boost rules ===

# Laravel Boost

## Tools

- Laravel Boost is an MCP server with tools designed specifically for this application. Prefer Boost tools over manual alternatives like shell commands or file reads.
- Use `database-query` to run read-only queries against the database instead of writing raw SQL in tinker.
- Use `database-schema` to inspect table structure before writing migrations or models.
- Use `get-absolute-url` to resolve the correct scheme, domain, and port for project URLs. Always use this before sharing a URL with the user.
- Use `browser-logs` to read browser logs, errors, and exceptions. Only recent logs are useful, ignore old entries.

## Searching Documentation (IMPORTANT)

- Always use `search-docs` before making code changes. Do not skip this step. It returns version-specific docs based on installed packages automatically.
- Pass a `packages` array to scope results when you know which packages are relevant.
- Use multiple broad, topic-based queries: `['rate limiting', 'routing rate limiting', 'routing']`. Expect the most relevant results first.
- Do not add package names to queries because package info is already shared. Use `test resource table`, not `filament 4 test resource table`.

### Search Syntax

1. Use words for auto-stemmed AND logic: `rate limit` matches both "rate" AND "limit".
2. Use `"quoted phrases"` for exact position matching: `"infinite scroll"` requires adjacent words in order.
3. Combine words and phrases for mixed queries: `middleware "rate limit"`.
4. Use multiple queries for OR logic: `queries=["authentication", "middleware"]`.

## Artisan

- Run Artisan commands directly via the command line (e.g., `php artisan route:list`). Use `php artisan list` to discover available commands and `php artisan [command] --help` to check parameters.
- Inspect routes with `php artisan route:list`. Filter with: `--method=GET`, `--name=users`, `--path=api`, `--except-vendor`, `--only-vendor`.
- Read configuration values using dot notation: `php artisan config:show app.name`, `php artisan config:show database.default`. Or read config files directly from the `config/` directory.

## Tinker

- Execute PHP in app context for debugging and testing code. Do not create models without user approval, prefer tests with factories instead. Prefer existing Artisan commands over custom tinker code.
- Always use single quotes to prevent shell expansion: `php artisan tinker --execute 'Your::code();'`
  - Double quotes for PHP strings inside: `php artisan tinker --execute 'User::where("active", true)->count();'`

=== php rules ===

# PHP

- Always use curly braces for control structures, even for single-line bodies.
- Use PHP 8 constructor property promotion: `public function __construct(public GitHub $github) { }`. Do not leave empty zero-parameter `__construct()` methods unless the constructor is private.
- Use explicit return type declarations and type hints for all method parameters: `function isAccessible(User $user, ?string $path = null): bool`
- Use TitleCase for Enum keys: `FavoritePerson`, `BestLake`, `Monthly`.
- Prefer PHPDoc blocks over inline comments. Only add inline comments for exceptionally complex logic.
- Use array shape type definitions in PHPDoc blocks.

=== deployments rules ===

# Deployment

- Laravel can be deployed using [Laravel Cloud](https://cloud.laravel.com/), which is the fastest way to deploy and scale production Laravel applications.

=== tests rules ===

# Test Enforcement

- Every change must be programmatically tested. Write a new test or update an existing test, then run the affected tests to make sure they pass.
- Run the minimum number of tests needed to ensure code quality and speed. Use `php artisan test --compact` with a specific filename or filter.

=== laravel/core rules ===

# Do Things the Laravel Way

- Use `php artisan make:` commands to create new files (i.e. migrations, controllers, models, etc.). You can list available Artisan commands using `php artisan list` and check their parameters with `php artisan [command] --help`.
- If you're creating a generic PHP class, use `php artisan make:class`.
- Pass `--no-interaction` to all Artisan commands to ensure they work without user input. You should also pass the correct `--options` to ensure correct behavior.

### Model Creation

- When creating new models, create useful factories and seeders for them too. Ask the user if they need any other things, using `php artisan make:model --help` to check the available options.

## APIs & Eloquent Resources

- For APIs, default to using Eloquent API Resources and API versioning unless existing API routes do not, then you should follow existing application convention.

## URL Generation

- When generating links to other pages, prefer named routes and the `route()` function.

## Testing

- When creating models for tests, use the factories for the models. Check if the factory has custom states that can be used before manually setting up the model.
- Faker: Use methods such as `$this->faker->word()` or `fake()->randomDigit()`. Follow existing conventions whether to use `$this->faker` or `fake()`.
- When creating tests, make use of `php artisan make:test [options] {name}` to create a feature test, and pass `--unit` to create a unit test. Most tests should be feature tests.

## Vite Error

- If you receive an "Illuminate\Foundation\ViteException: Unable to locate file in Vite manifest" error, you can run `npm run build` or ask the user to run `npm run dev` or `composer run dev`.

=== livewire/core rules ===

# Livewire

- Livewire allow to build dynamic, reactive interfaces in PHP without writing JavaScript.
- You can use Alpine.js for client-side interactions instead of JavaScript frameworks.
- Keep state server-side so the UI reflects it. Validate and authorize in actions as you would in HTTP requests.

=== pint/core rules ===

# Laravel Pint Code Formatter

- If you have modified any PHP files, you must run `vendor/bin/pint --dirty --format agent` before finalizing changes to ensure your code matches the project's expected style.
- Do not run `vendor/bin/pint --test --format agent`, simply run `vendor/bin/pint --format agent` to fix any formatting issues.

=== pest/core rules ===

## Pest

- This project uses Pest for testing. Create tests: `php artisan make:test --pest {name}`.
- The `{name}` argument should not include the test suite directory. Use `php artisan make:test --pest SomeFeatureTest` instead of `php artisan make:test --pest Feature/SomeFeatureTest`.
- Run tests: `php artisan test --compact` or filter: `php artisan test --compact --filter=testName`.
- Do NOT delete tests without approval.

</laravel-boost-guidelines>

## Commands

```bash
# First-time setup (install, .env, key, migrate, npm build)
composer run setup

# Start full dev stack — server + queue worker (default,alerts) + pail logs + Vite
composer run dev

# Tests
php artisan test --compact
php artisan test --compact tests/Feature/UnlockServiceTest.php
php artisan test --compact --filter='unlock creates a conversation thread'

# Full pre-push gate (config:clear + pint --test + tests) — same as CI
composer run test

# Formatting / lint
vendor/bin/pint --dirty --format agent   # after editing PHP
composer run lint:check                  # check only, no writes

# Reset local DB with skills + credit bundles
php artisan migrate:fresh --seed
```

`composer run dev` starts `queue:listen --queue=default,alerts`. Without a worker running, published briefs will **not** send alerts locally — `MatchBriefToAlerts` is queued on the `alerts` queue.

## Domain Overview

Meshwork HQ is a two-sided marketplace connecting **clients** (who post project briefs) with **professionals** (who pay credits to unlock and pitch on briefs). The backend follows `Meshworkhq_Phase_2_Backend_Architecture.docx` at the repo root — service docblocks cite its section numbers (e.g. "Phase 2 §5.4", "§6.3"). Consult it when a rule's intent is unclear.

**Core flow:**
1. Client fills the brief wizard → `Brief` created as `Draft` → `BriefService::publish()` → `Published`
2. `BriefPublished` event → `MatchBriefToAlerts` listener (queue: `alerts`) → `AlertService::initiate()` fans out wave-based notifications
3. Professional pays 1 credit → `UnlockService::unlock()` deducts the credit, creates the `Unlock`, marks the `Alert` unlocked, creates a `Conversation`, fires `BriefUnlocked`
4. `NotifyClientOfUnlock` advances the brief `Published → ReceivingPitches` on the first unlock and notifies the client
5. Client hires (`BriefService::hire()`) or closes; briefs expire 30 days after publish via the hourly `ExpireOverdueBriefsJob`

## Architecture

### Roles and routing

Two roles (`App\Enums\Role`): `client` and `professional`. Each has its own register/login page and route prefix (`/client/*`, `/professional/*`). The `role:client` / `role:professional` middleware alias maps to `RoleMiddleware` (aliased in `bootstrap/app.php`), which `abort(403)`s on mismatch. `/dashboard` redirects to the role-appropriate dashboard; `RegisterResponse` does the same after signup. `User::isClient()` / `isProfessional()` are the canonical role checks.

The conversation page is one component (`pages::shared.conversation`) registered twice — once under each role prefix — so `client.conversation` and `professional.conversation` route names both exist and resolve to the same view. `pages::shared.messages` (the inbox) works the same way and branches internally on `isProfessional()`.

Each role has one page per job; the dashboards are an Overview among many, not a container for everything:

| Professional | Client | Guest |
|---|---|---|
| `/professional/dashboard` Overview | `/client/dashboard` Overview | `/` marketing |
| `/professional/alerts` Alert feed | `/client/briefs` My briefs | `/professionals` directory |
| `/professional/pitches` My pitches | `/client/brief/create` Post a brief | |
| `/professional/messages` Messages | `/client/messages` Messages | |
| `/professional/profile` Profile | `/client/profile` Company profile | |
| `/professional/wallet` Wallet | | |

Guest pages (`pages::welcome`, `pages::directory`) must declare `#[Layout('layouts::marketing')]`; the Livewire default layout is the authenticated sidebar shell and will fatal on a null user.

### Brief lifecycle (BriefStatus enum)

```
Draft → Published → ReceivingPitches → Shortlisting → Hired
                                                   ↘ Closed / Expired
```

- `BriefStatus::isActive()` = Published/ReceivingPitches/Shortlisting. `canReceivePitches()` = Published/ReceivingPitches (this is what `Brief::isAvailableForUnlock()` checks).
- `AiReview` exists in the enum and `publish()` accepts it, but **nothing transitions into it** — the wizard goes Draft → Published directly. There is no AI review step implemented yet.
- `BriefService::shortlist()` exists but is not wired to any UI.
- All transitions belong in `BriefService`; each throws `BriefNotAvailableException` when called from a wrong state. Do not `update(['status' => ...])` on a brief from a component.
- Briefs are addressed by **ULID** in every URL — `Brief::where('ulid', $ulid)->firstOrFail()`. `ulid` is auto-generated in `Brief::booted()`.

### Alert matching (wave system)

`MatchingService::findCandidates($brief, limit: 50)` hard-filters professionals: excludes anyone already alerted for that brief, requires skill-tag overlap (`orWhereJsonContains` per tag), and requires the profile to be **at least 70% complete**, via `User::scopeProfileReady()`.

That scope is the SQL twin of `User::isProfileReady()`: the matcher selects and limits in one query, so the rule has to run in the database rather than filtering afterwards in PHP, which would silently shrink the wave. Both derive their threshold from `minimumFilledProfileFields()`, so adding a profile field or moving the percentage updates both at once, and `tests/Feature/ProfileReadinessTest.php` asserts they agree for all 32 profile shapes. This matters because the dashboard tells professionals "alerts are paused below 70%" — the gate the matcher runs and the gate the UI promises must not drift apart. Use `User::factory()->alertReady()` for any test that expects a professional to be matched; the bare `professional()` state fills nothing beyond name and email and sits at 29%.

`splitIntoWaves()` → Wave 1 = top 10 (immediate), Wave 2 = 11–25 (+6 h), Wave 3 = 26–50 (+24 h), each dispatched as a delayed `DispatchAlertWaveJob`. The docblocks describe waves 2/3 as conditional on unlock counts; **that gating is not implemented** — later waves are always scheduled and only stopped by `AlertService::dispatchWave()`'s guards: brief no longer unlockable, 50 alerts/brief cap, 20 alerts/professional/24 h cap.

Geography filter caveat: a professional's location is read from `company_role` (`MatchingService` notes this as a stopgap), so location matching is unreliable.

### Credit system

`users.credits` is the balance of record; `credit_transactions` is the append-only ledger with `balance_after` and a unique `reference`. All mutations go through `CreditService::award()` / `deduct()`, which run in a DB transaction and are **idempotent** — an existing `reference` short-circuits and returns the original row (so `deduct()` can return without charging again).

- Welcome bonus: 3 credits, 30-day expiry, issued for professionals only in `App\Actions\Fortify\CreateNewUser`.
- `issueProfileBonus()` (2 credits) and `issueReferralBonus()` (5 credits), both 90-day expiry, exist but have no callers yet.
- Credit **expiry is recorded, not enforced**: `CreditTransaction::isExpired()` / `scopeActive()` exist, but no job reduces `users.credits` when a bonus lapses.
- `UnlockService::unlock()` is the single atomic entry point for spend → unlock → conversation. Never call `CreditService::deduct()` directly for an unlock; the unlock reference must come from `CreditService::unlockReference()`.
- `CreditBundle` prices are stored in **kobo** (NGN) with `price_ngn` / `price_per_credit` accessors. Bundles render in the wallet but there is **no payment gateway wired** — purchase is not implemented.

### Search visibility

`App\Support\Seo` is a request scoped object the layout reads when rendering `partials/head.blade.php`. Static pages declare copy in `config/seo.php` keyed by route name; dynamic pages override at runtime, which works because Livewire renders the page component **before** the layout that reads it.

- **Descriptions must stay unique per page.** A test fails the build if two public pages share one.
- **`config('seo.noindex_prefixes')` keeps the whole authenticated app and every auth screen out of the index.** Route name prefixes are matched with `str_starts_with`, so **a new route name that begins with an existing prefix is silently deindexed**. The public profile route is named `professionals.show` rather than `professional.profile.show` for exactly this reason.
- Canonicals use `url()->current()`, which drops the query string, so filtered and paginated directory views do not compete with the page they came from.
- A professional profile with no bio is `noindex`: thin pages drag the whole domain down.

`robots.txt` and `sitemap.xml` are **routes, not static files**, because a sitemap reference in robots.txt has to be absolute and a hardcoded host would be wrong in every environment but one. The sitemap lists only profiles with both a title and a bio.

Structured data is emitted as JSON-LD: `Organization` and `WebSite` on every public page via `<x-schema.organisation>`, `FAQPage` from `<x-faq-section>`, `BreadcrumbList` from `<x-breadcrumbs>`, and `ProfilePage` plus `Person` on a professional profile. **The FAQ and breadcrumb components build the visible markup and the schema from one array**, so a crawler can never be shown something a person cannot see, which is what gets a rich result dropped.

Analytics loads only when `GOOGLE_ANALYTICS_ID` is set **and** the environment is production, so local and test runs never pollute the property.

### Trust signals

Two mechanisms, both aimed at the same problem: a professional is about to spend money to reach a stranger.

**Track record** is derived, never stored, so it cannot drift from the records it describes. `User::hiresCount()` counts briefs where the professional was hired; `User::engagementCount()` counts distinct professionals who unlocked one of a client's briefs. `publicTrackRecord()` applies the owner's `show_hire_count` / `show_engagement_count` preference and returns null when hidden. `<x-track-record :user="...">` renders it and **deliberately shows nothing for a zero**, because "Hired 0 times" reads as a warning rather than a neutral fact.

**Verification** covers clients (`/client/verification`). Individuals verify by NIN, companies by CAC number plus certificate.

- **Never store a full NIN.** There is no column for one. `NinVerifier` receives the raw number, returns a `NinVerificationResult` carrying only the outcome, legal name and last four digits, and the raw value goes out of scope. The Livewire component also clears `$nin` from component state after submitting.
- The bound verifier is `ManualNinVerifier`, which reaches no external service and routes everything to manual review. It exists so the platform never hands out a badge it did not actually check. To go live, implement `NinVerifier` against a licensed provider and change the one binding in `AppServiceProvider::register()`.
- CAC certificates go on the **`local` (private) disk**, never `public` where avatars and logos live, and are **deleted once a decision is recorded**.
- Verification columns are **not fillable**. `VerificationService` force fills them, so no request payload can grant a badge. Anything writing them must go through that service.
- Review happens through `php artisan verification:review` until an admin panel exists.

### Payments (Paystack)

Credit purchases run through `PaymentService` and the `PaymentGateway` interface, bound to `PaystackGateway` in `AppServiceProvider`. Money is in **kobo**, and `payments.amount_kobo` snapshots the price so a later bundle change never rewrites what someone actually paid.

Three rules the code enforces and tests assert, none of which may be relaxed:

1. **The callback never grants credits.** `payment/callback` is where the browser lands, and it is not trusted. It settles by asking Paystack, and the webhook is still the authority if the user closes the tab.
2. **Webhooks are signature checked, then re-verified.** HMAC SHA512 of the raw body against the secret key, compared with `hash_equals`, then a separate `/transaction/verify` call. A body claiming success is never enough. The route is CSRF exempt in `bootstrap/app.php` because Paystack carries no session.
3. **Settling is idempotent.** `PaymentService::settle()` returns early on an already settled payment, and the credit award is keyed on the payment reference, so `CreditService::award()` refuses a second grant even if a webhook is replayed. Underpayment is rejected outright.

Purchasing is hidden in the wallet until `PAYSTACK_SECRET_KEY` is set, so the UI never offers a checkout that cannot work. The gateway has not been exercised against the live API; `tests/Feature/PaymentTest.php` covers it with `Http::fake()`.

### Notifications

Every notification implements `ShouldQueue` and sends on `['mail', 'database']`, so nothing depends on a user noticing the bell. `tests/Feature/NotificationCoverageTest.php` asserts both the channels and that each event actually fires one. **Locally nothing sends without a queue worker**, which `composer run dev` starts.

| Event | Notification | Recipient |
|---|---|---|
| Brief published | `BriefPublishedNotification` | client |
| Brief matched | `BriefAlertNotification` | matched professionals |
| Brief unlocked | `BriefUnlockedNotification` | client |
| Message sent | `NewPitchNotification` | the other party |
| Professional hired | `HiredNotification` | professional |

Mail templates are published to `resources/views/vendor/mail/`. The header renders `config('mail.logo_url')` as an image, **falling back to the wordmark as text whenever that URL is on localhost or empty**, because an inbox cannot load a private host and a broken image in every email is worse than plain type. `MAIL_LOGO_URL` overrides it; otherwise it derives from `APP_URL`, so the logo starts working the moment the app is deployed on a public domain. Covered by `tests/Feature/MailBrandingTest.php`.

Resend refuses to send from an unverified domain, and its `onboarding@resend.dev` sandbox sender only delivers to the account owner's own address. If alerts reach you but nobody else, that is the cause.

`User` implements `MustVerifyEmail`, so registration sends a verification link and `RegisterResponse` sends new accounts to the verification notice rather than a dashboard they cannot reach. `MAIL_MAILER=log` writes mail to the log instead of delivering it; a real transport is needed before any of this reaches an inbox.

### Service layer

Domain services (`BriefService`, `CreditService`, `UnlockService`, `AlertService`, `MatchingService`) are `final` classes in `app/Services/`, resolved via the container (constructor injection, or `app(X::class)` inside Livewire components). They throw typed domain exceptions — `InsufficientCreditsException` (carries `$required` / `$available`), `BriefNotAvailableException`, `AlreadyUnlockedException` — and callers are expected to catch them and flash a message (see `UnlockController::store()` for the canonical pattern).

### Livewire 4 single-file components

Pages and components with a `⚡` filename prefix (`resources/views/pages/**`, `resources/views/components/**`) are **native Livewire 4 single-file components** — not Volt (livewire/volt is not installed, don't add it). The class is an anonymous class declared inline before the template:

```php
<?php
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('My Wallet')] class extends Component {
    // properties, mount(), actions, optional render()
}; ?>

<div>…template…</div>
```

- The `⚡` is stripped when resolving the component name, so `pages/client/⚡brief-wizard.blade.php` is `pages::client.brief-wizard`.
- Routes: `Route::livewire('brief/create', 'pages::client.brief-wizard')`.
- Tests: `Livewire::test('pages::client.brief-wizard')`.
- If a component defines `render()` explicitly it must return its own view by full name, including the `⚡` (see `⚡wallet.blade.php`: `view('pages::professional.⚡wallet')`).
- Create new ones with `php artisan make:livewire …`; check a sibling for structure first.

### Authentication

Auth is entirely **Fortify** — no Breeze/Jetstream scaffolding. `FortifyServiceProvider` registers every auth view (all under the `pages::auth.*` namespace), swaps in `CreateNewUser` / `ResetUserPassword`, binds a custom `RegisterResponse`, and defines the `login`, `two-factor`, and `passkeys` rate limiters. `User` implements `PasskeyUser` and uses `PasskeyAuthenticatable` + `TwoFactorAuthenticatable`, so passkeys and 2FA are live.

Role-specific register/login pages (`pages::auth.register-client`, `register-professional`, etc.) are plain `Route::view` pages that post to Fortify's routes with a `role` field; `CreateNewUser` branches on it, validates role-conditional fields (`professional_title` + `skill_tags` for professionals, company fields for clients), and issues the welcome bonus.

### Validation Concerns

Shared rule sets live in `app/Concerns/` as traits (`ProfileValidationRules`, `PasswordValidationRules`). Both Livewire components and Fortify actions `use` them and spread the rule methods into `$this->validate(...)` / `Validator::make(...)`.

### Framework defaults set in AppServiceProvider

Worth knowing before debugging odd behavior:
- `Date::use(CarbonImmutable::class)` — date objects are immutable; `$date->addDay()` does not mutate.
- `URL::forceScheme('https')` in `local` **only when `APP_URL` is itself https**. That keeps the ngrok tunnel working without breaking a plain `php artisan serve` over http. To browse locally, point `APP_URL` at `http://127.0.0.1:<port>`.
- `Password::defaults()` only enforces strong rules in production.
- `DB::prohibitDestructiveCommands()` in production.
- Event wiring is explicit in `registerEventListeners()` (`BriefPublished → MatchBriefToAlerts`, `BriefUnlocked → NotifyClientOfUnlock`) — register new listeners there, not via auto-discovery.

### Uploads

`config/filesystems.php` sets the public disk `url` to a **root relative `/storage`**, deliberately. Laravel's default builds it from `APP_URL`, which points every avatar and logo at one fixed host and breaks them on localhost, behind a tunnel, and anywhere the app is reached by another domain. Do not restore the `env('APP_URL')` version.

Verification documents go on the **`local` (private) disk** instead, never `public`.

### Database

**MySQL in production.** `config/database.php` still falls back to sqlite when `DB_CONNECTION` is unset, but every deployed environment sets mysql, and connection details are Fly secrets, never `fly.toml`, which is committed.

- Local: MySQL. DBngin is the easiest option on this machine and already has engines installed. Create a `meshworkhq` schema, set the `DB_*` values in `.env`, then `php artisan migrate --seed`.
- Tests: in-memory **SQLite**, forced in `phpunit.xml`, because it needs no service and keeps the suite fast. All raw SQL in the app is deliberately portable (`LOWER`, `CASE WHEN`, `COALESCE`, `whereJsonContains`), but **this is a real parity gap**: a MySQL-only failure would not be caught by the suite. Add a MySQL service to CI before relying on tests alone for schema changes.
- Seeders: `SkillSeeder` (the taxonomy the wizard and matcher depend on) and `CreditBundleSeeder`. Both are idempotent and run on every production boot from `.fly/scripts/02-migrate.sh`.

### Deployment

Boot scripts in `.fly/scripts/` run in numeric order on every container start:

1. `01-storage.sh` points `storage/app` at the `/data` volume and links `public/storage`. **Only `/data` survives a deploy on Fly**, so without this every uploaded avatar, logo and verification document is destroyed on the next release.
2. `02-migrate.sh` waits for MySQL to accept connections, migrates, then seeds reference data.
3. `03-caches.sh` caches config, routes and views. Because config is cached at boot, **an env change needs a redeploy**, not just a secret update.

### Frontend

Tailwind CSS v4 via the Vite plugin (no PostCSS config), Flux UI (`<flux:*>`), Alpine for client-side bits. Custom Flux overrides live in `resources/views/flux/`. Vite entry points: `resources/css/app.css`, `resources/js/app.js`, `resources/js/passkeys.js` (passkeys are a separate bundle).

#### The Wave System (design system)

Tokens live in the `@theme` block of `resources/css/app.css`. Both core colours are taken from the logo: the near black of the wordmark and the steel blue of the HQ plate. **Blue is the only accent**, so it always means the same thing, a thing you can act on.

| Role | Tokens |
|---|---|
| Ink | `--color-ink` `#1D1F20`, `-deep`, `-soft`, `-faint` |
| Navy (dark sections) | `--color-navy` `#1E2A38`, `-deep`, `-raised` |
| Ground | `--color-chalk` `#F2F2F3` (the logo's own background), `-soft`, `--color-paper`, `--color-line`, `-soft` |
| Accent | `--color-brand` `#5980A6` for fills and marks, `--color-brand-deep` `#3F5F80` for **text and buttons**, plus `-lit` and `-wash` |
| Semantic | `--color-live`, `--color-critical`, plus `-wash` variants |

Use `brand-deep` wherever the blue carries text or sits behind white type: plain `brand` on chalk is about 3.6:1 and fails contrast, `brand-deep` is 5.9:1.

Fonts self-host at build time through the Vite plugin, which resolves **discrete weights only, no variable axes**: **Oswald** for display, **Archivo** for body, **Martian Mono** for data. Every number a user makes a decision on gets `font-data`, which carries `tabular-nums`.

Two display treatments, and the distinction matters:
- `.font-display` is Oswald 600 **sentence case**. The default. App headings use this, because uppercase on every heading shouts at people trying to work.
- `.font-display-caps` is Oswald 700 uppercase. **Marketing headlines only**: the hero, section headings, the auth panel, the footer statement.

Component classes are defined in `@layer components` rather than repeated as utility soup: `.wave-rail` / `.wave-track` / `.wave-seg`, `.seal`, `.pill`, `.fact`, `.tag`, `.panel`, `.eyebrow`. Reuse these before inventing new markup.

**The wave rail is the signature device.** `<x-wave-rail :brief="$brief" />` renders which alert wave a brief is in, how many professionals can see it, and how long until it opens wider. It is driven by `Brief::currentWave()`, `waveAudience()`, `nextWaveAt()` and `waveProgress()`, which derive the wave from elapsed time since `published_at` rather than the stored `alert_wave` column, so the countdown stays truthful even when a delayed wave job has not run. Covered by `tests/Feature/BriefWaveTest.php`.

Shared view components: `<x-page-header>`, `<x-nav-link>`, `<x-marketing-nav>` (sticky, with an Alpine mobile menu), `<x-marketing-footer>`, `<x-track-record>`, `<x-verified-badge>`.

#### Brand assets

`<x-app-logo>` exists for the mark, but the marketing chrome, sidebar and auth panels use the full lockup image directly. Never inline a placeholder mark.

| File | Use |
|---|---|
| `public/images/meshwork-lockup.png` | dark wordmark, transparent, for chalk and paper grounds |
| `public/images/meshwork-lockup-light.png` | light wordmark, transparent, for navy grounds |
| `public/images/meshwork-lockup-original.png` | the supplied source, kept so both can be re-derived |
| `public/images/meshwork-mark-dark.png`, `-light.png` | the MH monogram alone |
| `public/favicon-32.png`, `favicon-16.png`, `apple-touch-icon.png` | monogram composited on `#131A38` |

Both transparent lockups are derived from the original, which has a flat `#F2F2F3` background baked in. Two details make that non-obvious, and a naive attempt produces visibly ragged type:

1. **Alpha comes from an unmix, not a threshold.** For dark ink over a flat light background, `alpha = 1 - min(r/bg_r, g/bg_g, b/bg_b)`, and the true colour is recovered by dividing the background back out. A distance threshold instead yields a hard binary cut with almost no anti-aliased pixels, which is exactly what "badly cut out" looks like on screen. The correct derivation gives roughly 17,000 semi-transparent pixels; the thresholded one gave 1,020.
2. **The blue HQ plate must be excluded from that unmix.** It is a mid tone and would come out semi transparent, and the white letters inside it are the same colour as the outer background. Locate the plate by **column and row density** of blue pixels, never by a bounding box over loose colour matches: one stray anti-aliased pixel at each end of the artwork expands such a box across the entire lockup.

The light variant reuses the same alpha map and inverts only the ink colour, keeping bluish strokes in `#9DB6CE`. Both are cropped to the artwork bounds so spacing lives in CSS. If a new lockup is supplied, re-run that derivation rather than hand-keying.

The lockup's wordmark is near black, so it is for light grounds only; dark sections use the light variant. Its steel blue is the palette accent, so nothing clashes.

#### Marketing surfaces

`pages::welcome` and `pages::directory` are the only guest pages. Both carry `<x-marketing-nav>` and `<x-marketing-footer>` and must declare `#[Layout('layouts::marketing')]`; the Livewire default layout is the authenticated sidebar shell and will fatal on a null user.

Photography lives in `public/images/hero-professional.jpg` and `auth-professional.jpg`. **The supplied photographs already contain notification card artwork**, so do not lay another alert card over them, which was tried and read as duplication. On the auth split panel the photo sits under a navy wash plus a bottom gradient, because type over an unmodified photograph is not readable.

#### Copy rules (enforced across the product)

- **No en dashes or em dashes anywhere in user-facing copy**, including interface text, emails and notifications. Ranges read as "₦350,000 to ₦500,000"; asides become commas or separate sentences.
- **Naira only.** Never show a dollar figure, even illustratively. Use `&#8358;` in Blade with `number_format`, and never abbreviate (no "450k").
- Square corners. Rounded corners were removed deliberately; do not reintroduce `rounded-*` on panels, buttons or tags.

For phone testing through a tunnel, `vite.config.js` reads `VITE_TUNNEL_HOST`:
```bash
ngrok http 5173
VITE_TUNNEL_HOST=<tunnel-host> npm run dev
```

### Testing conventions

`tests/Pest.php` already applies `RefreshDatabase` to everything in `tests/Feature`, so a per-file `uses(RefreshDatabase::class)` is redundant (existing files include it — harmless). Feature tests are the norm; service tests live directly in `tests/Feature/` (e.g. `UnlockServiceTest`) and resolve services with `app(UnlockService::class)`.

Use factory states rather than hand-setting attributes: `User::factory()->client()`, `->professional()`, `->unverified()`, `->withTwoFactor()`; `Brief::factory()->published()`, `->receivingPitches()`. `Tests\TestCase::skipUnlessFortifyHas('feature')` skips a test when a Fortify feature is disabled. Fake events/notifications (`Event::fake([BriefUnlocked::class])`, `Notification::fake()`) when asserting the alert/unlock pipelines.

`tests/Feature/SmokeTest.php` renders every route as each role and asserts cross-role access is forbidden. **Add a case to it whenever you add a page**, so a broken view fails CI instead of being found by hand.

## CI and deployment

- `.github/workflows/tests.yml` — PHP 8.4 and 8.5 matrix, `npm run build`, then `./vendor/bin/pest`. Composer installs with `--ignore-platform-reqs` (and `composer.json` sets `platform-check: false`) because the app targets PHP 8.5.
- `.github/workflows/lint.yml` — `composer lint` (Pint, `laravel` preset).
- `.github/workflows/fly-deploy.yml` — every push to `main` runs `flyctl deploy --remote-only`. Treat merges to `main` as production deploys.

Fly.io setup (`Dockerfile`, `fly.toml`, `.fly/`): Ubuntu + nginx + php-fpm under supervisor, assets built in a Node stage. `.fly/entrypoint.sh` runs every script in `.fly/scripts/` on boot, in numeric order (see **Deployment** above).

Supervisor runs four programs, and the last two are load-bearing: `nginx`, `php` (fpm), `worker` (`queue:work --queue=alerts,default`) and `cron`. The image had always installed cron and written `/etc/cron.d/laravel`, but nothing started the daemon, so `schedule:run` never fired and `ExpireOverdueBriefsJob` never ran. If you add a supervisor program, add it to `.fly/supervisor/conf.d/`; the whole directory is copied to `/etc/supervisor/`.

Production config to keep in mind: `QUEUE_CONNECTION=database`. It **must never be `sync`** — sync runs a delayed job inline and immediately, so waves 2 and 3 (+6 h / +24 h) all fire at once and the wave system quietly stops existing. `min_machines_running = 1` for the same reason: a stopped machine has no worker, so a wave waits for a passing visitor to wake the app instead of firing when due. Both are guarded by `tests/Feature/QueueConfigurationTest.php`.

The scheduler also runs `queue:work --stop-when-empty` every minute. On Fly that finds nothing and exits, because the supervised worker already drained it; it is there so the app still delivers alerts on shared hosting, where a long-lived process is not available and a per-minute cron is all there is.

`SESSION_DRIVER=cookie`, logs go to stderr as JSON. Because config is cached at boot, `.env`-style changes require a redeploy.
