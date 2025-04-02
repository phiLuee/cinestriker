<?php

use App\Models\User;
use Illuminate\Pagination\LengthAwarePaginator;
use Livewire\Volt\Component;
use Livewire\WithPagination;
use Mary\Traits\Toast;


new class extends Component {
    use Toast, WithPagination;

    public string $search = '';
    public bool $drawer = false;

    public int $page = 1;
    public int $perPage = 10;

    protected $queryString = ['page'];

    public array $sortBy = ['column' => 'name', 'direction' => 'asc'];

    // Clear filters
    public function clear(): void
    {
        $this->reset();
        $this->success('Filters cleared.', position: 'toast-bottom');
    }

    // Delete action
    public function delete($id): void
    {
        $user = User::find($id);
        
        if (!$user) {
            $this->error("Benutzer nicht gefunden.");
            return;
        }

        if ($user->hasRole('admin')) {
            $this->error("Administratoren dürfen nicht gelöscht werden.");
            return;
        }

        if (auth()->id() === $user->id) {
            $this->error("Du kannst dich nicht selbst löschen.");
            return;
        }

        $user->delete();

        $this->success("Benutzer #$id wurde gelöscht.", position: 'toast-bottom');
    }

    /**
     * 
     */
    public function headers(): array
    {
        return [
            ['key' => 'id', 'label' => '#', 'class' => 'w-1'],
            ['key' => 'name', 'label' => 'Name', 'class' => 'w-64'],
            ['key' => 'email', 'label' => 'E-mail', 'sortable' => false],
            ['key' => 'roles', 'label' => 'Rollen', 'class' => 'w-64'],
        ];
    }

    /**
     * 
     */
    public function users(): LengthAwarePaginator
    {
        $query = User::with('roles');

        // Suche nach Name oder E-Mail
        if (!empty($this->search)) {
            $query->where(function ($q) {
                $q->where('name', 'like', '%' . $this->search . '%')
                ->orWhere('email', 'like', '%' . $this->search . '%');
            });
        }

        // Sortierung
        if (!empty($this->sortBy['column']) && in_array($this->sortBy['direction'], ['asc', 'desc'])) {
            $query->orderBy($this->sortBy['column'], $this->sortBy['direction']);
        }

        $perPage = $this->perPage === -1 ? $query->count() : $this->perPage;

        return $query->paginate($perPage);
    }

    public function with(): array
    {
        return [
            'users' => $this->users(),
            'headers' => $this->headers()
        ];
    }
}; ?>

<div>
    <!-- HEADER -->
    <x-header title="Hello" separator progress-indicator>
        <x-slot:middle class="!justify-end">
            <x-input placeholder="Search..." wire:model.live.debounce="search" clearable icon="o-magnifying-glass" />
        </x-slot:middle>
        <x-slot:actions>
            <x-button label="Filters" @click="$wire.drawer = true" responsive icon="o-funnel" />
        </x-slot:actions>
    </x-header>

    <!-- TABLE  -->
    <x-card shadow>
        <x-table 
            :headers="$headers" 
            :rows="$users" 
            :sort-by="$sortBy"  
            with-pagination
            per-page="perPage"
            :per-page-values="[10, 25, 50, 100, -1]" 
        >
            @scope('cell_roles', $user)
                <div class="flex flex-wrap gap-1">
                    @foreach ($user['roles'] ?? [] as $role)
                        <x-badge color="primary" small value="{{ $role['name'] }}" />
                    @endforeach
                </div>
            @endscope
            @scope('actions', $user)
            <x-button icon="o-trash" wire:click="delete({{ $user['id'] }})" wire:confirm="Are you sure?" spinner class="btn-ghost btn-sm text-error" />
            @endscope


        </x-table>
    </x-card>

    <!-- FILTER DRAWER -->
    <x-drawer wire:model="drawer" title="Filters" right separator with-close-button class="lg:w-1/3">
        <x-input placeholder="Search..." wire:model.live.debounce="search" icon="o-magnifying-glass" @keydown.enter="$wire.drawer = false" />

        <x-slot:actions>
            <x-button label="Reset" icon="o-x-mark" wire:click="clear" spinner />
            <x-button label="Done" icon="o-check" class="btn-primary" @click="$wire.drawer = false" />
        </x-slot:actions>
    </x-drawer>
</div>
