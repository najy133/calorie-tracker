# Mealo — calorie tracker

Laravel + Livewire app. You describe a meal in plain English, the AI (OpenAI via
Prism) estimates calories + macros, and you log it. Also supports manual entry,
a personalised daily target set through an onboarding wizard, and a dashboard
with weekly history. Fully bilingual (English / Arabic RTL) and dark-mode.

## Stack

- **Laravel 12**, **Livewire 3**, **Alpine** (bundled with Livewire)
- **Tailwind CSS v4** (⚠️ see gotchas), Vite
- **SQLite** (dev)
- **Prism** (`Prism\Prism\Facades\Prism`) → OpenAI `gpt-4o-mini` for estimation
  and target advice. `usingTemperature(0)` for consistent output.

## Commands

- **`npm run build` — run after ANY change to Tailwind classes in `.blade.php`.**
  Tailwind v4 scans templates at build time; new/changed utility classes do not
  exist until you rebuild. Symptom of forgetting: a class silently does nothing.
- `php artisan test` — the full suite. Run before considering any change done.
- `php artisan migrate` — after adding a migration.
- `php artisan view:clear` — after Blade edits if a stale view is served.

## Where things live

- `app/Livewire/` — `Homepage` (log a meal), `Dashboard`, `Onboarding` (wizard).
- `app/Concerns/` — `HasStreak` (streak calc), `HasEntryActions` (shared inline
  edit + confirm-delete for Homepage & Dashboard — do NOT duplicate this logic).
- `app/Services/` — `CalorieEstimator`, `AITargetAdvisor`, `TargetCalculator`.
- `resources/views/components/` — shared Blade components. Reuse these; e.g.
  `<x-macros>`, `<x-entry-row>`, `<x-confirm-delete-modal>`, `<x-wordmark>`,
  `<x-flame>`. If markup is duplicated across two pages, extract a component.
- `lang/ar.json` — every user-facing string has an Arabic entry here.

## UI / UX conventions (this is a portfolio piece — design quality matters)

The whole app shares one restrained, editorial design language derived from the
landing page. Match it; don't invent new patterns per page.

- **Headings:** `font-serif` (Instrument Serif), zinc-900 / dark:zinc-50. Page
  titles ~`text-3xl md:text-4xl`; section titles ~`text-2xl md:text-[1.75rem]`.
  Headings are black, never emerald.
- **Section eyebrows / small labels:** `text-xs font-semibold uppercase
  tracking-widest text-emerald-600 dark:text-emerald-400` (rose for "Danger
  zone"). Numbers use `font-mono`.
- **Accent:** emerald-600 is the single brand/action colour. Reserve it for
  primary buttons, active states, and accents — if everything is emerald,
  nothing stands out. Green is never used for a normal data value.
- **Buttons:** one radius everywhere — `rounded-lg`. Primary = solid emerald;
  secondary = bordered/outline; low-priority = text link. `rounded-full` is only
  for genuine pills (filter chips, badges, the avatar), never action buttons.
- **Lists:** flat, separated by `border-t border-zinc-100 dark:border-zinc-800/70`
  — not nested cards.
- **Macros:** always via `<x-macros>`. Colour + icon are fixed: **P = rose
  drumstick, C = indigo baguette, F = amber cheese**, each prefixed with its
  letter (`P48g`). Keep this mapping consistent everywhere.
- **Cursors:** all real buttons show a pointer (a global base rule handles this);
  don't fight it.
- Prefer inline SVGs over emoji for anything that should look intentional.

## RTL & internationalisation — REQUIRED on every feature

Arabic is a first-class locale, not an afterthought. **Every new feature must
work in both LTR and RTL.**

- Wrap **all** user-facing strings in `__()` and add the Arabic to `lang/ar.json`
  (parameterised keys like `__('Step :n of :total', [...])` are fine). Validate
  the JSON after editing.
- Use **logical properties**: `ps-*/pe-*/ms-*/me-*`, `start-*/end-*`, `text-start/
  text-end` — never hard `left/right/pl/pr`. Mirror directional icons (chevrons,
  arrows) with `rtl:-scale-x-100`.
- Localise dates via `->locale(app()->getLocale())->translatedFormat(...)`, with
  an Arabic format variant where the pattern differs.
- After building any feature, sanity-check it in Arabic (the `عربي` toggle sets
  `dir="rtl"` on `<html>`).

## Mobile responsiveness — REQUIRED on every feature

Design mobile-first; every screen must be usable at ~375px.

- The nav collapses to a hamburger below `sm`; the onboarding stepper drops its
  labels and shows "Step n of N · Name" on mobile. Follow these patterns.
- Use `whitespace-nowrap` on inline nav actions so they don't wrap mid-word.
- Verify new layouts at mobile width, not just desktop. Touch targets should be
  comfortably tappable.

## Data model

- `entries`: `user_id`, `food`, `calories`, `protein`, `carbs`, `fat`,
  `source` (`ai` | `manual`), timestamps. Indexed on `(user_id, created_at)`.
  Manual entries are tagged and can be "remembered" (reused on exact-name match).
- All entry queries are **scoped by `user_id`** — never query entries without it.
- `users` also holds onboarding fields (`age`, `sex`, `weight_kg`, `height_cm`,
  `activity_level`, `goal`, `daily_goal`, macro targets, `onboarded_at`);
  `health_notes` is encrypted at rest.

## Gotchas learned the hard way

- **Tailwind v4 `transform` no longer creates a stacking context.** A `fixed`
  overlay can paint over a `transform`ed panel — give modals `relative z-10`.
- **Livewire property/method name collisions** break `wire:click`. Don't name a
  method the same as a public property (we hit this with `adjust`).
- `wire:model` is **deferred by default** in Livewire 3 (syncs on action, not per
  keystroke) — rely on that; don't add debounce expecting live sync.
- **Public Livewire properties are client-writable.** Re-validate/clamp on the
  server before persisting (e.g. `Onboarding::confirm()` clamps the target).
- When selecting specific columns with `->get([...])`, remember to include any
  column the view needs (e.g. `source`) or it reads as null.

## Testing

- Pest feature tests in `tests/Feature/` (Livewire component tests via
  `Livewire::test(...)`). Add tests for new behaviour, especially the estimate/
  save/edit/delete paths and per-user scoping. Keep the suite green.
- Mock `CalorieEstimator` in tests so they don't hit the network.
