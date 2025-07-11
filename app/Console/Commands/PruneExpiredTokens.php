<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Laravel\Sanctum\PersonalAccessToken;

class PruneExpiredTokens extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sanctum:prune-expired';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Prune expired Sanctum tokens';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Pruning expired tokens...');

        $expiredTokens = PersonalAccessToken::where('expires_at', '<', now())->get();

        $count = $expiredTokens->count();

        foreach ($expiredTokens as $token) {
            $token->delete();
        }

        $this->info("Successfully pruned {$count} expired tokens.");

        return Command::SUCCESS;
    }
}
