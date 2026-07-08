<?php

use App\Livewire\Dashboard;
use App\Models\Entry;
use App\Models\User;
use Livewire\Livewire;

it('redirects guests to login', function () {
    $this->get('/dashboard')->assertRedirect(route('login'));
});

it('renders the dashboard for authenticated users', function () {
    $user = User::factory()->create();

    $this->actingAs($user)->get('/dashboard')
        ->assertStatus(200)
        ->assertSeeLivewire(Dashboard::class);
});

it('shows correct weekly calorie totals', function () {
    \Illuminate\Support\Carbon::setTestNow('2024-01-07'); // a Sunday — today + yesterday sit in one Mon–Sun week
    $user = User::factory()->create(['daily_goal' => 2000]);

    Entry::factory()->create(['user_id' => $user->id, 'calories' => 800, 'created_at' => today()]);
    Entry::factory()->create(['user_id' => $user->id, 'calories' => 400, 'created_at' => today()]);
    Entry::factory()->create(['user_id' => $user->id, 'calories' => 600, 'created_at' => today()->subDay()]);

    $component = Livewire::actingAs($user)->test(Dashboard::class);

    $weeklyData = $component->viewData('weeklyData');

    $today     = $weeklyData->firstWhere('isToday', true);
    $yesterday = $weeklyData->firstWhere('date', today()->subDay()->toDateString());

    expect($today['calories'])->toBe(1200);
    expect($yesterday['calories'])->toBe(600);
    \Illuminate\Support\Carbon::setTestNow();
});

it('shows weekly average over elapsed days', function () {
    \Illuminate\Support\Carbon::setTestNow('2024-01-07'); // a Sunday — all 7 days of the week have elapsed
    $user = User::factory()->create(['daily_goal' => 2000]);

    Entry::factory()->create(['user_id' => $user->id, 'calories' => 1400, 'created_at' => today()]);
    Entry::factory()->create(['user_id' => $user->id, 'calories' => 700, 'created_at' => today()->subDays(2)]);

    $component = Livewire::actingAs($user)->test(Dashboard::class);

    // 2100 total over 7 elapsed days = 300 avg
    expect($component->viewData('weeklyAverage'))->toBe(300);
    \Illuminate\Support\Carbon::setTestNow();
});

it('calculates streak correctly', function () {
    $user = User::factory()->create();

    Entry::factory()->create(['user_id' => $user->id, 'created_at' => today()]);
    Entry::factory()->create(['user_id' => $user->id, 'created_at' => today()->subDay()]);
    Entry::factory()->create(['user_id' => $user->id, 'created_at' => today()->subDays(2)]);

    Livewire::actingAs($user)->test(Dashboard::class)
        ->assertSet('streak', 3);
});

it('does not count other users data in weekly totals', function () {
    $user  = User::factory()->create(['daily_goal' => 2000]);
    $other = User::factory()->create();

    Entry::factory()->create(['user_id' => $user->id, 'calories' => 500, 'created_at' => today()]);
    Entry::factory()->create(['user_id' => $other->id, 'calories' => 9000, 'created_at' => today()]);

    $component  = Livewire::actingAs($user)->test(Dashboard::class);
    $today      = $component->viewData('weeklyData')->firstWhere('isToday', true);

    expect($today['calories'])->toBe(500);
});

it('can delete an entry from the dashboard', function () {
    $user  = User::factory()->create();
    $entry = Entry::factory()->create(['user_id' => $user->id, 'created_at' => today()]);

    Livewire::actingAs($user)
        ->test(Dashboard::class)
        ->call('delete', $entry->id);

    expect(Entry::find($entry->id))->toBeNull();
});

it('cannot delete another user\'s entry from the dashboard', function () {
    $user  = User::factory()->create();
    $other = User::factory()->create();
    $entry = Entry::factory()->create(['user_id' => $other->id, 'created_at' => today()]);

    Livewire::actingAs($user)
        ->test(Dashboard::class)
        ->call('delete', $entry->id);

    expect(Entry::find($entry->id))->not->toBeNull();
});
