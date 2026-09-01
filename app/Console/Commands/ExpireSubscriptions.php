<?php

namespace App\Console\Commands;

use App\Models\Subscription;
use Illuminate\Console\Command;

class ExpireSubscriptions extends Command
{
    protected $signature = 'subscriptions:expire';

    protected $description = 'Marca como "expired" las suscripciones activas/en prueba cuya fecha de vigencia ya pasó';

    public function handle(): int
    {
        $expired = 0;

        Subscription::whereIn('status', ['trial', 'active'])
            ->chunkById(100, function ($subscriptions) use (&$expired) {
                foreach ($subscriptions as $subscription) {
                    if (! $subscription->isCurrentlyValid()) {
                        $subscription->update(['status' => 'expired']);
                        $expired++;
                    }
                }
            });

        $this->info("Suscripciones marcadas como expiradas: {$expired}");

        return self::SUCCESS;
    }
}
