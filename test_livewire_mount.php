<?php

require __DIR__ . '/vendor/autoload.php';

$app = require __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$user = App\Models\User::first();
auth()->login($user);

try {
    Livewire\Livewire::test('admin.settings.equipment.attributes.datatable-attributes');
    echo "datatable mount OK\n";
} catch (Throwable $e) {
    echo "datatable ERR: {$e->getMessage()}\n";
}

try {
    Livewire\Livewire::test('admin.settings.equipment.attributes.index');
    echo "index mount OK\n";
} catch (Throwable $e) {
    echo "index ERR: {$e->getMessage()}\n{$e->getTraceAsString()}\n";
}
