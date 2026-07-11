<?php

use App\Livewire\Homepage;
use App\Models\Entry;
use App\Models\User;
use App\Services\CalorieEstimator;
use Livewire\Livewire;

// ── Guest behaviour ──────────────────────────────────────────────────────────

it('renders the landing page for guests', function () {
    $this->get('/')->assertStatus(200)->assertSee('Log a meal before you forget it');
});

it('redirects authenticated users from landing to home', function () {
    $user = User::factory()->create();
    $this->actingAs($user)->get('/')->assertRedirect(route('home'));
});

it('renders the homepage for guests', function () {
    $this->get('/home')->assertStatus(200)->assertSeeLivewire(Homepage::class);
});

it('shows zero calories for guests on mount', function () {
    Livewire::test(Homepage::class)
        ->assertSet('todayCalories', 0);
});

it('allows guests to estimate without auth', function () {
    $this->mock(CalorieEstimator::class)
        ->shouldReceive('estimate')
        ->with('an apple', \Mockery::any())
        ->once()
        ->andReturn(['not_food' => false, 'calories' => 95, 'protein' => 0, 'carbs' => 25, 'fat' => 0, 'explanation' => 'Assumed one medium apple.', 'breakdown' => []]);

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

    expect($component->viewData('todayEntries')->first()->id)->toBe($second->id);
});

it('estimates calories using the AI service', function () {
    $this->mock(CalorieEstimator::class)
        ->shouldReceive('estimate')
        ->with('2 scrambled eggs', \Mockery::any())
        ->once()
        ->andReturn(['not_food' => false, 'calories' => 180, 'protein' => 12, 'carbs' => 1, 'fat' => 14, 'explanation' => 'Assumed two large eggs, scrambled in butter.', 'breakdown' => []]);

    Livewire::test(Homepage::class)
        ->set('food', '2 scrambled eggs')
        ->call('estimate')
        ->assertSet('calories', 180)
        ->assertSet('explanation', 'Assumed two large eggs, scrambled in butter.');
});

it('shows an error and clears a stale estimate when the input is not food', function () {
    $this->mock(CalorieEstimator::class)
        ->shouldReceive('estimate')
        ->with('test', \Mockery::any())
        ->once()
        ->andReturn(['not_food' => true]);

    // Start with a leftover estimate from a previous valid food, then re-estimate
    // with a non-food input — the old value must not linger (or be saveable).
    Livewire::test(Homepage::class)
        ->set('calories', 95)
        ->set('protein', 5)
        ->set('food', 'test')
        ->call('estimate')
        ->assertHasErrors('food')
        ->assertSet('calories', 0)
        ->assertSet('protein', 0);
});

it('clears a stale estimate when the food text is edited', function () {
    // A prior estimate exists; editing the food text must invalidate it so the
    // Save button (shown when food && calories) can't apply the old numbers.
    Livewire::test(Homepage::class)
        ->set('food', 'apple')
        ->set('calories', 95)
        ->set('food', 'banana')
        ->assertSet('calories', 0);
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

it('saves macros with an entry', function () {
    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test(Homepage::class)
        ->set('food', 'grilled chicken breast')
        ->set('calories', 300)
        ->set('protein', 55)
        ->set('carbs', 0)
        ->set('fat', 7)
        ->call('save');

    $entry = Entry::where('user_id', $user->id)->first();

    expect($entry->protein)->toBe(55);
    expect($entry->carbs)->toBe(0);
    expect($entry->fat)->toBe(7);
});

it('resets macros after saving', function () {
    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test(Homepage::class)
        ->set('food', 'banana')
        ->set('calories', 90)
        ->set('protein', 1)
        ->set('carbs', 23)
        ->set('fat', 0)
        ->call('save')
        ->assertSet('protein', 0)
        ->assertSet('carbs', 0)
        ->assertSet('fat', 0);
});

// ── Manual entry ─────────────────────────────────────────────────────────────

it('logs a manual entry with just calories', function () {
    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test(Homepage::class)
        ->call('toggleManual', true)
        ->set('manualCalories', 650)
        ->call('saveManual')
        ->assertHasNoErrors()
        ->assertSet('manualCalories', null);

    $entry = Entry::where('user_id', $user->id)->first();
    expect($entry->calories)->toBe(650);
    expect($entry->food)->toBe('Quick add'); // blank name falls back to a label
    expect($entry->protein)->toBe(0);
});

it('keeps the name and optional macros on a manual entry', function () {
    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test(Homepage::class)
        ->set('manualFood', 'Al Baik broast meal')
        ->set('manualCalories', 780)
        ->set('manualProtein', 48)
        ->call('saveManual')
        ->assertHasNoErrors();

    $entry = Entry::where('user_id', $user->id)->first();
    expect($entry->food)->toBe('Al Baik broast meal');
    expect($entry->calories)->toBe(780);
    expect($entry->protein)->toBe(48);
    expect($entry->carbs)->toBe(0);
});

it('requires calories for a manual entry', function () {
    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test(Homepage::class)
        ->set('manualFood', 'mystery meal')
        ->call('saveManual')
        ->assertHasErrors(['manualCalories' => 'required']);

    expect(Entry::where('user_id', $user->id)->count())->toBe(0);
});

it('does not save a manual entry for guests', function () {
    Livewire::test(Homepage::class)
        ->set('manualCalories', 500)
        ->call('saveManual');

    expect(Entry::count())->toBe(0);
});

// ── Source tagging + reuse of manual entries ─────────────────────────────────

it('tags AI-saved entries as source ai and manual ones as manual', function () {
    $user = User::factory()->create();

    Livewire::actingAs($user)->test(Homepage::class)
        ->set('food', 'banana')->set('calories', 90)->call('save');
    Livewire::actingAs($user)->test(Homepage::class)
        ->set('manualCalories', 500)->call('saveManual');

    expect(Entry::where('food', 'banana')->first()->source)->toBe('ai');
    expect(Entry::where('source', 'manual')->count())->toBe(1);
});

it('reuses a past manual entry instead of calling the AI', function () {
    $user = User::factory()->create();
    Entry::create(['user_id' => $user->id, 'food' => 'Al Baik broast', 'calories' => 650, 'protein' => 35, 'carbs' => 0, 'fat' => 20, 'source' => 'manual']);

    // The estimator must NOT be called when a manual match exists.
    $this->mock(CalorieEstimator::class)->shouldNotReceive('estimate');

    Livewire::actingAs($user)->test(Homepage::class)
        ->set('food', '  al baik broast ') // different case/spacing still matches
        ->call('estimate')
        ->assertSet('calories', 650)
        ->assertSet('protein', 35)
        ->assertSet('reusedManual', true);
});

it('saves a reused manual estimate back as source manual', function () {
    $user = User::factory()->create();
    Entry::create(['user_id' => $user->id, 'food' => 'kabsa', 'calories' => 900, 'protein' => 40, 'carbs' => 90, 'fat' => 30, 'source' => 'manual']);

    Livewire::actingAs($user)->test(Homepage::class)
        ->set('food', 'kabsa')
        ->call('estimate')
        ->call('save');

    expect(Entry::where('food', 'kabsa')->where('calories', 900)->get()->pluck('source')->all())
        ->toBe(['manual', 'manual']);
});

it('does not reuse another user\'s manual entry', function () {
    $other = User::factory()->create();
    Entry::create(['user_id' => $other->id, 'food' => 'kabsa', 'calories' => 900, 'source' => 'manual']);

    $me = User::factory()->create();
    $this->mock(CalorieEstimator::class)->shouldReceive('estimate')->once()
        ->andReturn(['not_food' => false, 'calories' => 111, 'protein' => 0, 'carbs' => 0, 'fat' => 0, 'explanation' => null, 'breakdown' => []]);

    Livewire::actingAs($me)->test(Homepage::class)
        ->set('food', 'kabsa')
        ->call('estimate')
        ->assertSet('calories', 111)
        ->assertSet('reusedManual', false);
});
