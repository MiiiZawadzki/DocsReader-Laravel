<?php

namespace App\Console\Commands;

use App\Models\Permission;
use Illuminate\Console\Command;
use Symfony\Component\Console\Command\Command as CommandAlias;

class SyncPermissions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:sync-permissions';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Synchronize system permissions';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $permissions = config('permissions');
        $existingPermissions = Permission::pluck('type')->toArray();

        $this->removePermissions($existingPermissions, $permissions);
        $this->addPermissions($permissions, $existingPermissions);

        return CommandAlias::SUCCESS;
    }

    /**
     * @param array $existingPermissions
     * @param array $permissions
     * @return void
     */
    public function removePermissions(array $existingPermissions, array $permissions): void
    {
        $permissionsToDelete = array_diff($existingPermissions, $permissions);
        if (!empty($permissionsToDelete)) {
            Permission::whereIn('type', $permissionsToDelete)->delete();
            $this->info('Deleted permissions: ' . implode(', ', $permissionsToDelete));
        }
    }

    /**
     * @param array $permissions
     * @param array $existingPermissions
     * @return void
     */
    public function addPermissions(array $permissions, array $existingPermissions): void
    {
        $permissionsToAdd = array_diff($permissions, $existingPermissions);

        foreach ($permissionsToAdd as $permission) {
            Permission::create(['type' => $permission]);
        }

        if (!empty($permissionsToAdd)) {
            $this->info('Added permissions: ' . implode(', ', $permissionsToAdd));
        }
    }
}
