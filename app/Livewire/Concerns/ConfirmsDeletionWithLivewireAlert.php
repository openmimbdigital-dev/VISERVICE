<?php

namespace App\Livewire\Concerns;

use App\Support\DeleteConfirmationAlert;
use Jantinnerezo\LivewireAlert\LivewireAlert;
use Livewire\Attributes\On;

trait ConfirmsDeletionWithLivewireAlert
{
    use LivewireAlert;

    public ?int $delete_id = null;

    protected function askDeleteConfirmation(int $id, string $message): void
    {
        $this->delete_id = $id;

        $this->confirm($message, DeleteConfirmationAlert::options());
    }

    #[On('confirmed')]
    public function handleLivewireAlertConfirmed(mixed $data = null): void
    {
        if (! $this->delete_id) {
            return;
        }

        $this->onDeleteConfirmed();
    }

    protected function onDeleteConfirmed(): void
    {
        // Sobrescribir en cada componente.
    }

    protected function alertDeleteSuccess(string $message): void
    {
        $this->alert('success', $message, [
            'position' => 'top-end',
            'timer'    => 3000,
            'toast'    => true,
        ]);
    }

    protected function alertDeleteError(string $message): void
    {
        $this->alert('error', $message, [
            'position' => 'top-end',
            'timer'    => 4000,
            'toast'    => true,
        ]);
    }

    protected function alertDeleteWarning(string $message): void
    {
        $this->alert('warning', $message, [
            'position' => 'top-end',
            'timer'    => 4000,
            'toast'    => true,
        ]);
    }
}
