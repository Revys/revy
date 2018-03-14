<?php

namespace Revys\Revy\App\Console\Commands\Overrides;

use Illuminate\Console\Command;
use Revys\Revy\App\Overrides;

class IndexOverridesClass extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'index-overrides';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Index overrides';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $overrides = app()->make(Overrides::class);
        $overrides->indexOverrides();

        return false;
    }
}
