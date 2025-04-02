<?php

use Illuminate\Support\Facades\Auth;
use Livewire\Volt\Component;
use Mary\Traits\Toast;

new class extends Component {
    
    use Toast;

    public string $email = '';
    public string $password = '';
    public bool $remember = false;
    public string $website = ''; // Honeypot-Property

    public function login() {
        if (!empty($this->website)) {
            $this->error('bot', 'Bot erkannt');
            return;
        } 

        $this->validate([
            'email' => 'required|email',
            'password' => 'required|min:4',
        ]);
 
        if (Auth::attempt([
            'email' => $this->email,
            'password' => $this->password
        ], $this->remember)) {
            session()->regenerate();
            $this->success('Login erfolgreich!', position: 'toast-bottom');

            return redirect()->route('index');
        }

        $this->error('Die Zugangsdaten sind ungültig.', position: 'toast-bottom');
    }
};

?>

<div class="max-w-md mx-auto mt-10 p-6 rounded-lg shadow-md">
    <h2 class="text-2xl font-bold mb-6">Login</h2>

    <x-form wire:submit="login">
        <x-input placeholder="E-Mail" wire:model="email" type="email" />
        {{-- @error('email') <span class="text-error">{{ $message }}</span> @enderror --}}

        <x-input placeholder="Passwort" wire:model="password" type="password" />
        {{-- @error('password') <span class="text-error">{{ $message }}</span> @enderror --}}

        <div class="flex items-center my-4">
            <input type="checkbox" wire:model="remember" id="remember" class="mr-2">
            <label for="remember">Angemeldet bleiben</label>
        </div>

        <div class="hidden">
            <x-input wire:model="website" name="website" type="text" autocomplete="off" tabindex="-1" />
        </div>

        <x-button label="Einloggen" type="submit" class="btn-primary w-full" icon="o-check" spinner />
    </x-form>
</div>
