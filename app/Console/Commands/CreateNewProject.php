<?php

namespace App\Console\Commands;

use App\Enums\ProjectStatuses;
use App\Models\Project;
use Illuminate\Console\Command;

class CreateNewProject extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'create:project {name} {domain}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Creates new project';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $project = new Project();
        $project->fill([
            'name' => $this->argument('name'),
            'domain' => $this->argument('domain'),
            'status' => ProjectStatuses::STATUS_ACTIVE
        ]);
        $project->save();
        return 0;
    }
}
