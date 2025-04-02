<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Mary\Traits\Toast;
use Livewire\Volt\Component;

new
#[Layout('layouts.app')]
#[Title('Profil')]
class extends Component
{
    use Toast;

    public string $name = '';
    public string $current_password = '';
    public string $new_password = '';
    public string $new_password_confirmation = '';

    public function mount()
    {
        $this->name = auth()->user()->name;
    }

    public function updateName()
    {
        $this->validate([
            'name' => 'required|min:2|max:50'
        ]);

        auth()->user()->update([
            'name' => $this->name
        ]);

        $this->success('Name erfolgreich aktualisiert.', position: 'toast-bottom');
    }

    public function updatePassword()
    {
        $this->validate([
            'current_password' => 'required',
            'new_password' => ['required', 'confirmed', Password::min(8)->mixedCase()->numbers()],
        ]);

        if (!Hash::check($this->current_password, auth()->user()->password)) {
            $this->addError('current_password', 'Das aktuelle Passwort ist falsch.');
            return;
        }

        auth()->user()->update([
            'password' => Hash::make($this->new_password),
        ]);

        $this->reset(['current_password', 'new_password', 'new_password_confirmation']);
        $this->success('Passwort erfolgreich geändert.', position: 'toast-bottom');
    }

    public function resendVerification()
    {
        if (auth()->user()->hasVerifiedEmail()) {
            $this->info('E-Mail bereits verifiziert.');
            return;
        }

        auth()->user()->sendEmailVerificationNotification();
        $this->success('Bestätigungsmail wurde gesendet.');
    }
}

?>

<div class="max-w-xl mx-auto space-y-8 mt-10">

    <!-- NAME -->
    <x-card title="Name ändern">
        <x-input label="Name" wire:model="name" />
        <x-button wire:click="updateName" class="btn-primary mt-4" label="Speichern" icon="o-check" spinner />
    </x-card>

    <!-- PASSWORT -->
    <x-card title="Passwort ändern">
        <x-input label="Aktuelles Passwort" type="password" wire:model="current_password" />
        <x-input label="Neues Passwort" type="password" wire:model="new_password" />
        <x-input label="Passwort bestätigen" type="password" wire:model="new_password_confirmation" />
        <x-button wire:click="updatePassword" class="btn-primary mt-4" label="Passwort speichern" icon="o-key" spinner />
    </x-card>

    <!-- EMAIL VERIFIKATION -->
    @if (!auth()->user()->hasVerifiedEmail())
        <x-card title="E-Mail-Bestätigung" subtitle="Deine E-Mail-Adresse ist noch nicht bestätigt.">
            <x-button wire:click="resendVerification" label="Erneut senden" icon="o-envelope" class="mt-4" />
        </x-card>
    @endif
</div>