<?php

use App\Livewire\Onboarding;
use App\Models\User;
use Livewire\Livewire;

it('redirects an already-onboarded user away from the wizard', function () {
    $user = User::factory()->create(['onboarded_at' => now()]);
    $this->actingAs($user)->get('/onboarding')->assertRedirect(route('home'));
});

it('walks forward and back through the steps and ignores invalid jumps', function () {
    $user = User::factory()->create(['onboarded_at' => null]);

    Livewire::actingAs($user)->test(Onboarding::class)
        ->call('jumpTo', 'goal')->assertSet('step', 'goal')
        ->call('back')->assertSet('step', 'activity')
        ->call('jumpTo', 'not-a-real-step')->assertSet('step', 'activity');
});

it('validates the details step before advancing', function () {
    $user = User::factory()->create(['onboarded_at' => null]);

    Livewire::actingAs($user)->test(Onboarding::class)
        ->set('step', 'details')
        ->call('next')
        ->assertHasErrors(['age', 'sex', 'weightKg', 'heightCm']);
});

it('clamps an out-of-range target and marks the user onboarded on confirm', function () {
    $user = User::factory()->create(['onboarded_at' => null]);

    Livewire::actingAs($user)->test(Onboarding::class)
        ->set('age', 30)->set('sex', 'M')->set('weightKg', 75)->set('heightCm', 180)
        ->set('activity', 'moderate')->set('goal', 'lose')->set('eatingHabit', 'mix')
        ->set('target', 99999) // tampered value well over the max
        ->call('confirm')
        ->assertHasNoErrors();

    expect($user->fresh()->daily_goal)->toBe(10000);           // clamped
    expect($user->fresh()->onboarded_at)->not->toBeNull();
});

it('refuses to confirm with an incomplete profile', function () {
    $user = User::factory()->create(['onboarded_at' => null]);

    Livewire::actingAs($user)->test(Onboarding::class)
        ->set('target', 2000)
        ->call('confirm')
        ->assertHasErrors(['age']);

    expect($user->fresh()->onboarded_at)->toBeNull();
});
