<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use App\Http\Controllers\UrlRequestController;

class SiteChangedNotifier extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'link:diff';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Iterate through a list of sites and find which ones have changed and the changes';

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
        $urlObj = new UrlRequestController();
        $urlObj->findDiff();
    }
}
