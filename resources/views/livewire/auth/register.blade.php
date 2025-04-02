<?php

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Livewire\Volt\Component;
use Mary\Traits\Toast;
use Illuminate\Validation\Rules\Password;


new
#[Layout('layouts.app')]
#[Title('Registrieren')]
class extends Component
{
    use Toast;

    public string $name = '';
    public string $email = '';
    public string $password = '';
    public string $password_confirmation = '';
    public string $website = ''; // Honeypot-Property

    public function register()
    {
        if (!empty($this->website)) {
            $this->error('bot', 'Bot erkannt');
            return;
        }

        $validated = $this->validate([
            'name' => 'required|min:2|max:50',
            'email' => 'required|email|unique:users,email',
            'password' => ['required', 'confirmed', Password::min(8)
                ->mixedCase()
                ->letters()
                ->numbers()
                ->symbols()],
        ]);

        $validated['name'] = trim($validated['name']);
        $validated['email'] = strtolower(trim($validated['email']));

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($this->password),
        ]);

        $user->assignRole('user');

        Auth::login($user);
        session()->regenerate();
        $user->sendEmailVerificationNotification();

        $this->success('Registrierung erfolgreich. Bitte bestätige deine E-Mail-Adresse!', position: 'toast-bottom');

        return redirect()->route('index'); 
    }
}

?>


<div class="max-w-md mx-auto mt-10 p-6 rounded-lg shadow-md">
    <h2 class="text-2xl font-bold mb-6">Registrieren</h2>

    <x-form wire:submit="register" class="space-y-4">
        <x-input placeholder="Name" wire:model="name" icon="o-user" />
        {{-- @error('name') <span class="text-error">{{ $message }}</span> @enderror --}}

        <x-input placeholder="E-Mail" wire:model="email" type="email" icon="o-envelope" />
        {{-- @error('email') <span class="text-error">{{ $message }}</span> @enderror --}}

        <x-input placeholder="Passwort" wire:model="password" type="password" icon="o-key" />
        {{-- @error('password') <span class="text-error">{{ $message }}</span> @enderror --}}

        <x-input placeholder="Passwort bestätigen" wire:model="password_confirmation" type="password" icon="o-key" />

        <div class="hidden">
            <x-input wire:model="website" name="website" type="text" autocomplete="off" tabindex="-1" />
        </div>

        <x-button
            wire:loading.attr="disabled"
            type="button"
            wire:click="register"
            label="Registrieren"
            class="btn-primary w-full"
            icon="o-check"
            spinner
        />
    </x-form>
</div>