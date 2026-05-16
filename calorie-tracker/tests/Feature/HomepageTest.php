<?php

use App\Livewire\Homepage;
use App\Models\Entry;
use App\Services\CalorieEstimator;
use Livewire\Livewire;

it('renders the homepage successfully', function () {
    $this->get('/')->assertStatus(200)->assertSeeLivewire(Homepage::class);
});

it('loads todayCalories from the database on mount', function () {
    Entry::factory()->create(['calories' => 400, 'created_at' => today()]);
    Entry::factory()->create(['calories' => 250, 'created_at' => today()]);
    Entry::factory()->create(['calories' => 600, 'created_at' => today()->subDay()]);

    Livewire::test(Homepage::class)
        ->assertSet('todayCalories', 650);
});

it('loads todayEntries in descending order on mount', function () {
    $first = Entry::factory()->create(['created_at' => today()->setTime(8, 0)]);
    $second = Entry::factory()->create(['created_at' => today()->setTime(12, 0)]);

    $component = Livewire::test(Homepage::class);

    expect($component->get('todayEntries')->first()->id)->toBe($second->id);
});

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

it('shows a validation error when the AI service throws', function () {
    $this->mock(CalorieEstimator::class)
        ->shouldReceive('estimate')
        ->andThrow(new RuntimeException('API timeout'));

    Livewire::test(Homepage::class)
        ->set('food', 'a bowl of rice')
        ->call('estimate')
        ->assertHasErrors('food');
});

it('saves an entry and refreshes totals', function () {
    $component = Livewire::test(Homepage::class)
        ->set('food', 'grilled chicken')
        ->set('calories', 350)
        ->call('save');

    expect(Entry::whereDate('created_at', today())->count())->toBe(1);

    $component
        ->assertSet('todayCalories', 350)
        ->assertSet('food', '')
        ->assertSet('calories', 0)
        ->assertSet('explanation', null);
});

it('does not save when calories are zero', function () {
    Livewire::test(Homepage::class)
        ->set('food', 'grilled chicken')
        ->set('calories', 0)
        ->call('save');

    expect(Entry::count())->toBe(0);
});

it('deletes an entry and updates totals', function () {
    $entry = Entry::factory()->create(['calories' => 500, 'created_at' => today()]);

    Livewire::test(Homepage::class)
        ->assertSet('todayCalories', 500)
        ->call('delete', $entry->id)
        ->assertSet('todayCalories', 0);

    expect(Entry::find($entry->id))->toBeNull();
});

it('resets explanation after saving', function () {
    Livewire::test(Homepage::class)
        ->set('food', 'apple')
        ->set('calories', 95)
        ->set('explanation', 'Assumed one medium apple.')
        ->call('save')
        ->assertSet('explanation', null);
});
