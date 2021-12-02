<?php

namespace Laravel\Horizon\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Laravel\Horizon\Contracts\SupervisorRepository;
use Laravel\Horizon\MasterSupervisor;

class PauseSupervisorCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'horizon:pause-supervisor
                            {name : The name of the supervisor to pause}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Pause a supervisor';

    /**
     * Execute the console command.
     *
     * @param  \Laravel\Horizon\Contracts\SupervisorRepository  $supervisors
     * @return void
     */
    public function handle(SupervisorRepository $supervisors)
    {
        
        $processId = optional(collect($supervisors->all())->first(function ($supervisor) {
            return Str::endsWith($supervisor->name, $this->argument('name'));
        }))->pid;
        if (is_null($processId)) {
            $this->error("Failed to find a supervisor with the name: {$this->argument('name')}");
            return 1;
        }

        $this->info("Sending USR2 Signal To Process: {$processId}");
        $usr2Sig = defined('SIGUSR2')? SIGUSR2:12;
        if (! posix_kill($processId, $usr2Sig)) {
            $this->error("Failed to send USR2 signal to process: {$processId} (".posix_strerror(posix_get_last_error()).')');
        }
    }
}
