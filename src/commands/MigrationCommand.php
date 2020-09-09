<?php namespace Cevv13\Entrust;

/**
 * This file is part of Entrust,
 * a role & permission management solution for Laravel.
 *
 * @license MIT
 * @package Cevv13\Entrust
 */

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Config;

class MigrationCommand extends Command
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'entrust:migration';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Creates a migration following the Entrust specifications.';

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function fire()
    {
        $this->laravel->view->addNamespace('entrust', substr(__DIR__, 0, -8).'views');

        $rolesTable          = Config::get('entrust.roles_table');
        $roleUserTable       = Config::get('entrust.role_user_table');
        $permissionsTable    = Config::get('entrust.permissions_table');
        $permissionRoleTable = Config::get('entrust.permission_role_table');
        $userPermissionTable = Config::get('entrust.user_permission_table');

        $this->line('');
        $this->info( "Tables: $rolesTable, $roleUserTable, $permissionsTable, $permissionRoleTable" );

        //$message = "A migration that creates '$rolesTable', '$roleUserTable', '$permissionsTable', '$permissionRoleTable'".
        $message = "A migration that creates '$rolesTable', '$roleUserTable', '$permissionsTable', '$permissionRoleTable', '$userPermissionTable'".
        " tables will be created in database/migrations directory";

        $this->comment($message);
        $this->line('');

        if ($this->confirm("Proceed with the migration creation? [Yes|no]", "Yes")) {

            $this->line('');

            $this->info("Creating migration...");
            //if ($this->createMigration($rolesTable, $roleUserTable, $permissionsTable, $permissionRoleTable)) {
            if ($this->createMigration($rolesTable, $roleUserTable, $permissionsTable, $permissionRoleTable, $userPermissionTable)) {

                $this->info("Migration successfully created!");
            } else {
                $this->error(
                    "Couldn't create migration.\n Check the write permissions".
                    " within the database/migrations directory."
                );
            }

            $this->line('');

        }
    }

    /**
     * Create the migration.
     *
     * @param string $name
     *
     * @return bool
     */
    //protected function createMigration($rolesTable, $roleUserTable, $permissionsTable, $permissionRoleTable)
    protected function createMigration($rolesTable, $roleUserTable, $permissionsTable, $permissionRoleTable, $userPermissionTable)
    {
        $migrationFile = base_path("/database/migrations")."/".date('Y_m_d_His')."_entrust_setup_tables.php";

        $userModel = Config::get('auth.providers.users.model');
        $userModel = new $userModel;
        $userKeyName = $userModel->getKeyName();
        $usersTable  = $userModel->getTable();

        //$data = compact('rolesTable', 'roleUserTable', 'permissionsTable', 'permissionRoleTable', 'usersTable', 'userKeyName');
        $data = compact('rolesTable', 'roleUserTable', 'permissionsTable', 'permissionRoleTable', 'usersTable', 'userKeyName', 'userPermissionTable');

        $output = $this->laravel->view->make('entrust::generators.migration')->with($data)->render();

        if (!file_exists($migrationFile) && $fs = fopen($migrationFile, 'x')) {
            fwrite($fs, $output);
            fclose($fs);
            return true;
        }

        return false;
    }
}