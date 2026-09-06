Mealo 🍽️

AI calorie and nutrition tracker: describe your meal in plain English (or Arabic), get calories and macros back.

Live app: mealo.up.railway.app

What it does

Instead of searching food databases, you type "2 eggs, toast with butter, and a glass of orange juice" and Mealo estimates calories, protein, carbs, and fat, then logs it against your daily targets.

How the AI integration works
OpenAI gpt-4o-mini via the Prism PHP library
Few-shot prompting with strict JSON output, responses are validated before anything reaches the user
Per-user rate limiting and a monthly AI spend cap, so costs stay bounded in production
Graceful fallbacks when the model or API fails
Stack
Laravel 12, Livewire 3, Tailwind CSS v4
MySQL, queued jobs for background work
Deployed on Railway (FrankenPHP), auto-deploy on push to main
~83 Pest feature tests running on every push via GitHub Actions
Full English/Arabic support, including RTL
Why I built it

I wanted a tracker that didn't make logging food feel like data entry, and a production playground for the real concerns of consuming LLM APIs: cost control, rate limiting, and failure handling.
