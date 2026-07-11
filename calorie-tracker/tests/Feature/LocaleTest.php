<?php

// Guards the i18n / RTL behaviour: switching locale, rejecting bad input,
// and that the layout flips direction between English and Arabic.

it('switches the locale to arabic and stores it in the session', function () {
    $this->post(route('locale.switch'), ['locale' => 'ar'])->assertRedirect();
    expect(session('locale'))->toBe('ar');
});

it('ignores an unsupported locale', function () {
    $this->withSession(['locale' => 'en'])
        ->post(route('locale.switch'), ['locale' => 'xx']);
    expect(session('locale'))->toBe('en');
});

it('renders the app RTL in arabic', function () {
    $this->withSession(['locale' => 'ar'])
        ->get('/home')
        ->assertStatus(200)
        ->assertSee('dir="rtl"', false);
});

it('renders the app LTR in english', function () {
    $this->withSession(['locale' => 'en'])
        ->get('/home')
        ->assertSee('dir="ltr"', false);
});
