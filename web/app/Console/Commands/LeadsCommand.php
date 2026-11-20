<?php

namespace App\Console\Commands;

use App\Models\Lead;
use App\Support\SignupSource;
use Illuminate\Console\Attributes\AsCommand;
use Illuminate\Console\Command;

/**
 * Who asked for a link to Loft on their phone (decision 50), whether they came back to start a
 * studio, and who said Loft could follow up. Run it in Render's shell: php artisan loft:leads.
 */
#[AsCommand(name: 'loft:leads', description: 'People who asked for a link to Loft, and whether they started a studio')]
class LeadsCommand extends Command
{
    protected $signature = 'loft:leads {--days=30 : How many days back}';

    public function handle(): int
    {
        $days = (string) $this->option('days');
        if (! ctype_digit($days) || (int) $days < 1) {
            $this->error('--days must be a whole number of days, 1 or more.');

            return self::FAILURE;
        }
        $leads = Lead::with('studio')->where('created_at', '>=', now()->subDays((int) $days))->latest()->get();
        $people = $leads->unique('email');
        $this->info("Link requests in the last {$days} days: {$leads->count()}, from {$people->count()} ".($people->count() === 1 ? 'address' : 'addresses').'.');
        $this->line('Started a studio: '.$people->whereNotNull('studio_id')->count().'. Said we could follow up: '.$people->whereNotNull('follow_up_consent_at')->count().'.');
        if ($leads->isEmpty()) {
            return self::SUCCESS;
        }
        $this->table(['Asked', 'Email', 'Page', 'Visit began', 'Follow up?', 'Started a studio'], $people->map(fn (Lead $l) => [
            $l->created_at->format('M j, H:i'),
            $l->email,
            $l->page,
            SignupSource::describe($l->signup_source),
            $l->follow_up_consent_at ? 'yes' : 'no',
            $l->studio ? $l->studio->name.' ('.$l->studio->slug.')' : '',
        ])->all());
        $this->line('<comment>Only email people who said yes to following up.</comment>');

        return self::SUCCESS;
    }
}
