<?php

use App\Models\Cliente;
use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

it('allows the client to update their profile without logging out when email stays the same', function () {
    $role = Role::create([
        'name' => 'Cliente',
        'description' => 'Cliente del taller',
    ]);

    $user = User::factory()->create([
        'role_id' => $role->id,
        'phone' => '5512345678',
    ]);

    $cliente = Cliente::create([
        'user_id' => $user->id,
        'direccion' => null,
        'rfc' => null,
        'fecha_nacimiento' => null,
    ]);

    $response = $this
        ->actingAs($user)
        ->patch(route('cliente.perfil.update'), [
            'name' => 'Cliente Demo',
            'email' => $user->email,
            'phone' => '5598765432',
            'direccion' => 'Av. Reforma 123',
            'rfc' => 'XAXX010101000',
            'fecha_nacimiento' => '1990-05-10',
        ]);

    $response
        ->assertSessionHas('success', 'Perfil actualizado correctamente.')
        ->assertRedirect(route('cliente.perfil'));

    $this->assertAuthenticatedAs($user);

    $user->refresh();
    $cliente->refresh();

    expect($user->name)->toBe('Cliente Demo');
    expect($user->phone)->toBe('5598765432');
    expect($user->force_logout_at)->toBeNull();
    expect($cliente->direccion)->toBe('Av. Reforma 123');
    expect($cliente->rfc)->toBe('XAXX010101000');
    expect($cliente->fecha_nacimiento)->toBe('1990-05-10');
});

it('logs the client out when the email is changed', function () {
    $role = Role::create([
        'name' => 'Cliente',
        'description' => 'Cliente del taller',
    ]);

    $user = User::factory()->create([
        'role_id' => $role->id,
        'phone' => '5512345678',
    ]);

    Cliente::create([
        'user_id' => $user->id,
    ]);

    $response = $this
        ->actingAs($user)
        ->patch(route('cliente.perfil.update'), [
            'name' => 'Nuevo Nombre',
            'email' => 'nuevo-correo@example.com',
            'phone' => '5512345678',
            'direccion' => null,
            'rfc' => null,
            'fecha_nacimiento' => null,
        ]);

    $response
        ->assertSessionHas('status', 'Tu información fue actualizada. Inicia sesión nuevamente.')
        ->assertRedirect(route('login'));

    $this->assertGuest();

    $user->refresh();

    expect($user->email)->toBe('nuevo-correo@example.com');
    expect($user->email_verified_at)->toBeNull();
    expect($user->force_logout_at)->not->toBeNull();
});

it('logs the client out when the password is changed', function () {
    $role = Role::create([
        'name' => 'Cliente',
        'description' => 'Cliente del taller',
    ]);

    $user = User::factory()->create([
        'role_id' => $role->id,
        'phone' => '5512345678',
    ]);

    Cliente::create([
        'user_id' => $user->id,
    ]);

    $response = $this
        ->actingAs($user)
        ->from(route('cliente.perfil'))
        ->put(route('cliente.perfil.password'), [
            'current_password' => 'password',
            'password' => 'nuevo-password-123',
            'password_confirmation' => 'nuevo-password-123',
        ]);

    $response
        ->assertSessionHas('status', 'Tu contraseña fue actualizada. Inicia sesión con los nuevos datos.')
        ->assertRedirect(route('login'));

    $this->assertGuest();

    expect(Hash::check('nuevo-password-123', $user->refresh()->password))->toBeTrue();
    expect($user->force_logout_at)->not->toBeNull();
});
