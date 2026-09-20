<?php

use App\Models\User;

test('unauthenticated users are redirected to panel login', function () {
    $response = $this->get('/panel');

    $response->assertRedirect('/panel/login');
});

test('login page is accessible', function () {
    $response = $this->get('/panel/login');

    $response->assertSuccessful();
});

test('authenticated user can access dashboard and all resources', function (string $uri) {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->get($uri);

    $response->assertSuccessful();
})->with([
    '/panel',
    '/panel/faq-categories',
    '/panel/faqs',
    '/panel/contacts',
    '/panel/conversations',
    '/panel/clinic-settings',
]);
