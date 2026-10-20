<?php

namespace App\Console\Commands;

use App\Services\Billz\BillzClient;
use Illuminate\Console\Command;

class BillzLoginCommand extends Command
{
    protected $signature = 'billz:login';

    protected $description = 'Billz JWT tokenini tekshirish / yangilash';

    public function handle(BillzClient $client): int
    {
        try {
            $token = $client->login(true);
            $this->info('Billz login OK. Token: '.substr($token, 0, 24).'...');

            return self::SUCCESS;
        } catch (\Throwable $e) {
            $this->error($e->getMessage());

            return self::FAILURE;
        }
    }
}
