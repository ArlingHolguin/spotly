<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\Url;
use App\Models\User;

uses(RefreshDatabase::class);

uses(RefreshDatabase::class);

it('permite al administrador listar todas las URLs', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    Url::factory()->count(10)->create();

    $this->actingAs($admin)
        ->getJson(route('api.urls.index'))
        ->assertStatus(200)
        ->assertJsonCount(10, 'data');
});


it('lista las últimas 10 URLs anónimas para usuarios no autenticados', function () {
    Url::factory()->count(15)->create(['user_id' => null]);

    $this->getJson(route('api.urls.index'))
        ->assertStatus(200)
        ->assertJsonCount(10, 'data'); // Solo las últimas 10
});

it('permite a un usuario no autenticado crear una URL', function () {
    $response = $this->postJson(route('api.urls.store'), [
        'original_url' => 'https://example.com',
    ]);

    dump($response->json()); // Verifica la estructura de la respuesta

    $response->assertStatus(201)
        ->assertJsonPath('data.original_url', 'https://example.com')
        ->assertJsonPath('data.user_id', null);
});




it('rechaza la creación de una URL con un formato no válido', function () {
    $this->postJson(route('api.urls.store'), [
            'original_url' => 'invalid-url',
        ])
        ->assertStatus(422);
});

it('permite mostrar una URL activa y redirige correctamente', function () {
    $url = Url::factory()->create([
        'is_active' => true,
        'expires_at' => now()->addDays(1),
    ]);

    $this->get(route('api.urls.show', $url->short_code))
        ->assertRedirect($url->original_url);
});

it('muestra un error cuando se intenta acceder a una URL expirada', function () {
    $url = Url::factory()->create([
        'is_active' => true,
        'expires_at' => now()->subDay(),
    ]);

    $this->get(route('api.urls.show', $url->short_code))
        ->assertStatus(410);
});

// it('permite al dueño de una URL actualizarla', function () {
//     $user = User::factory()->create();
//     $url = Url::factory()->create(['user_id' => $user->id]);

//     $this->actingAs($user)
//         ->putJson(route('api.urls.update', $url->short_code), [
//             'original_url' => 'https://updated.com',
//         ])
//         ->assertStatus(200)
//         ->assertJsonPath('data.original_url', 'https://updated.com');
// });

// it('bloquea a un usuario que intenta actualizar una URL ajena', function () {
//     $user = User::factory()->create();
//     $url = Url::factory()->create();

//     $this->actingAs($user)
//         ->putJson(route('api.urls.update', $url->short_code), [
//             'original_url' => 'https://updated.com',
//         ])
//         ->assertStatus(403);
// });

it('permite al administrador actualizar cualquier URL', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $url = Url::factory()->create();

    $this->actingAs($admin)
        ->putJson(route('api.urls.update', $url->short_code), [
            'original_url' => 'https://updated.com',
        ])
        ->assertStatus(200)
        ->assertJsonPath('data.original_url', 'https://updated.com');
});



it('permite al dueño eliminar su URL', function () {
    $user = User::factory()->create();
    $url = Url::factory()->create(['user_id' => $user->id]);

    $token = $user->createToken('TestToken')->plainTextToken;

    $this->withHeader('Authorization', 'Bearer ' . $token)
        ->deleteJson(route('api.urls.destroy', $url->short_code))
        ->assertStatus(200)
        ->assertJsonPath('message', 'URL eliminada correctamente');

    $this->assertDatabaseMissing('urls', ['short_code' => $url->short_code]);
});


// it('bloquea a un usuario que intenta eliminar una URL ajena', function () {
//     $user = User::factory()->create();
//     $url = Url::factory()->create();

//     $this->actingAs($user)
//         ->deleteJson(route('api.urls.destroy', $url->short_code))
//         ->assertStatus(403);
// });

it('permite al administrador eliminar cualquier URL', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $url = Url::factory()->create();

    $this->actingAs($admin)
        ->deleteJson(route('api.urls.destroy', $url->short_code))
        ->assertStatus(200)
        ->assertJsonPath('message', 'URL eliminada correctamente');

    $this->assertDatabaseMissing('urls', ['short_code' => $url->short_code]);
});
