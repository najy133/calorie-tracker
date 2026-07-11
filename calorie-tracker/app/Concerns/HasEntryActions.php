<?php

namespace App\Concerns;

use App\Models\Entry;
use App\Services\CalorieEstimator;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;

/**
 * Inline edit + confirm-delete for meal entries, shared by the Homepage and
 * Dashboard Livewire components. Previously copy-pasted into both, which let
 * the two copies drift (missing locale / untranslated errors on one side).
 *
 * Components may override refreshAfterEntryChange() to refresh any cached state
 * they hold (e.g. the Homepage keeps today's total + streak as properties).
 */
trait HasEntryActions
{
    public ?int $editingId = null;
    public string $editFood = '';

    public ?int $confirmingDeleteId = null;
    public string $confirmingDeleteFood = '';

    public function startEdit(int $id): void
    {
        $entry = Entry::where('id', $id)->where('user_id', auth()->id())->firstOrFail();

        $this->editingId = $id;
        $this->editFood  = $entry->food;
    }

    public function saveEdit(): void
    {
        if (!$this->editingId || blank($this->editFood)) return;

        $key = $this->estimateRateKey();

        if (RateLimiter::tooManyAttempts($key, maxAttempts: 10)) {
            $seconds = RateLimiter::availableIn($key);
            $this->addError('editFood', __('Too many requests. Please wait :seconds seconds.', ['seconds' => $seconds]));
            return;
        }

        RateLimiter::hit($key, decaySeconds: 60);

        try {
            $result = app(CalorieEstimator::class)->estimate($this->editFood, app()->getLocale());

            if ($result['not_food']) {
                $this->addError('editFood', __("That doesn't look like food. Try something like \"chicken sandwich\" or \"2 eggs and toast\"."));
                return;
            }

            Entry::where('id', $this->editingId)
                ->where('user_id', auth()->id())
                ->update([
                    'food'     => $this->editFood,
                    'calories' => $result['calories'],
                    'protein'  => $result['protein'],
                    'carbs'    => $result['carbs'],
                    'fat'      => $result['fat'],
                    'source'   => 'ai', // editing re-runs the AI, so it's an estimate now
                ]);

            $this->cancelEdit();
            $this->refreshAfterEntryChange();
        } catch (\Throwable $e) {
            $this->addError('editFood', __('Could not estimate calories. Please try again.'));
            Log::error('CalorieEstimator failed on edit', ['error' => $e->getMessage(), 'food' => $this->editFood]);
        }
    }

    public function cancelEdit(): void
    {
        $this->editingId = null;
        $this->editFood  = '';
    }

    public function confirmDelete(int $id): void
    {
        $entry = Entry::where('id', $id)->where('user_id', auth()->id())->first();
        if (!$entry) return;

        $this->confirmingDeleteId   = $entry->id;
        $this->confirmingDeleteFood = $entry->food;
    }

    public function deleteConfirmed(): void
    {
        if ($this->confirmingDeleteId === null) return;

        $this->delete($this->confirmingDeleteId);
        $this->confirmingDeleteId   = null;
        $this->confirmingDeleteFood = '';
    }

    public function delete(int $id): void
    {
        Entry::where('id', $id)
            ->where('user_id', auth()->id())
            ->delete();

        $this->refreshAfterEntryChange();
    }

    /** Key the estimate rate limiter per authenticated user, falling back to IP for guests. */
    protected function estimateRateKey(): string
    {
        return 'estimate:' . (auth()->id() ?? request()->ip());
    }

    /** Override to refresh cached component state after an entry is edited or deleted. */
    protected function refreshAfterEntryChange(): void
    {
        //
    }
}
