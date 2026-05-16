<?php

use App\Livewire\Homepage;
use App\Models\Entry;
use App\Models\User;
use App\Services\CalorieEstimator;
use Livewire\Livewire;

// ── Guest behaviour ──────────────────────────────────────────────────────────

it('renders the homepage for guests', function () {
    $this->get('/')->assertStatus(200)->assertSeeLivewire(Homepage::class);
});

it('shows zero calories for guests on mount', function () {
    Livewire::test(Homepage::class)
        ->assertSet('todayCalories', 0);
});

it('allows guests to estimate without auth', function () {
    $this->mock(CalorieEstimator::class)
        ->shouldReceive('estimate')
        ->with('an apple')
        ->once()
        ->andReturn(['calories' => 95, 'explanation' => 'Assumed one medium apple.']);

    Livewire::test(Homepage::class)
        ->set('food', 'an apple')
        ->call('estimate')
        ->assertSet('calories', 95);
});

it('redirects guests to login when saving', function () {
    Livewire::test(Homepage::class)
        ->set('food', 'grilled chicken')
        ->set('calories', 350)
        ->call('save')
        ->assertRedirect(route('login'));

    expect(Entry::count())->toBe(0);
});

// ── Validation ───────────────────────────────────────────────────────────────

it('validates that food is required before estimating', function () {
    Livewire::test(Homepage::class)
        ->set('food', '')
        ->call('estimate')
        ->assertHasErrors(['food' => 'required']);
});

it('validates food minimum length', function () {
    Livewire::test(Homepage::class)
        ->set('food', 'a')
        ->call('estimate')
        ->assertHasErrors(['food' => 'min']);
});

it('shows a validation error when the AI service throws', function () {
    $this->mock(CalorieEstimator::class)
        ->shouldReceive('estimate')
        ->andThrow(new RuntimeException('API timeout'));

    Livewire::test(Homepage::class)
        ->set('food', 'a bowl of rice')
        ->call('estimate')
        ->assertHasErrors('food');
});

// ── Authenticated behaviour ───────────────────────────────────────────────────

it('loads only the authenticated user\'s todayCalories on mount', function () {
    $user = User::factory()->create();
    $other = User::factory()->create();

    Entry::factory()->create(['user_id' => $user->id, 'calories' => 400, 'created_at' => today()]);
    Entry::factory()->create(['user_id' => $other->id, 'calories' => 900, 'created_at' => today()]);

    Livewire::actingAs($user)->test(Homepage::class)
        ->assertSet('todayCalories', 400);
});

it('loads todayEntries in descending order on mount', function () {
    $user = User::factory()->create();

    $first  = Entry::factory()->create(['user_id' => $user->id, 'created_at' => today()->setTime(8, 0)]);
    $second = Entry::factory()->create(['user_id' => $user->id, 'created_at' => today()->setTime(12, 0)]);

    $component = Livewire::actingAs($user)->test(Homepage::class);

    expect($component->get('todayEntries')->first()->id)->toBe($second->id);
});

it('estimates calories using the AI service', function () {
    $this->mock(CalorieEstimator::class)
        ->shouldReceive('estimate')
        ->with('2 scrambled eggs')
        ->once()
        ->andReturn(['calories' => 180, 'explanation' => 'Assumed two large eggs, scrambled in butter.']);

    Livewire::test(Homepage::class)
        ->set('food', '2 scrambled eggs')
        ->call('estimate')
        ->assertSet('calories', 180)
        ->assertSet('explanation', 'Assumed two large eggs, scrambled in butter.');
});

it('saves an entry scoped to the authenticated user and refreshes totals', function () {
    $user = User::factory()->create();

    $component = Livewire::actingAs($user)
        ->test(Homepage::class)
        ->set('food', 'grilled chicken')
        ->set('calories', 350)
        ->call('save');

    $entry = Entry::first();
    expect($entry->user_id)->toBe($user->id);
    expect($entry->calories)->toBe(350);

    $component
        ->assertSet('todayCalories', 350)
        ->assertSet('food', '')
        ->assertSet('calories', 0)
        ->assertSet('explanation', null);
});

it('does not save when calories are zero', function () {
    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test(Homepage::class)
        ->set('food', 'grilled chicken')
        ->set('calories', 0)
        ->call('save');

    expect(Entry::count())->toBe(0);
});

it('deletes only the authenticated user\'s entry', function () {
    $user  = User::factory()->create();
    $other = User::factory()->create();

    $mine  = Entry::factory()->create(['user_id' => $user->id, 'calories' => 500, 'created_at' => today()]);
    $theirs = Entry::factory()->create(['user_id' => $other->id, 'calories' => 300, 'created_at' => today()]);

    Livewire::actingAs($user)
        ->test(Homepage::class)
        ->call('delete', $mine->id)
        ->assertSet('todayCalories', 0);

    expect(Entry::find($mine->id))->toBeNull();
    expect(Entry::find($theirs->id))->not->toBeNull();
});

it('cannot delete another user\'s entry', function () {
    $user  = User::factory()->create();
    $other = User::factory()->create();
    $entry = Entry::factory()->create(['user_id' => $other->id, 'calories' => 500, 'created_at' => today()]);

    Livewire::actingAs($user)
        ->test(Homepage::class)
        ->call('delete', $entry->id);

    expect(Entry::find($entry->id))->not->toBeNull();
});

it('resets explanation after saving', function () {
    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test(Homepage::class)
        ->set('food', 'apple')
        ->set('calories', 95)
        ->set('explanation', 'Assumed one medium apple.')
        ->call('save')
        ->assertSet('explanation', null);
});
