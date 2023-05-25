<?php

namespace App\Console\Commands;

use App\Services\ComplianceService;
use App\Services\SumSubService;
use Illuminate\Console\Command;

class DocumentsAutoDelete extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'documents:auto-delete';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Deleting documents from SumSub';

    protected $complianceService;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->complianceService = new ComplianceService();
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->complianceService->autoDeleteDocuments();
    }
}
