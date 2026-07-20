<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddDefaultRoles extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // find or create default admin role
        $adminRole = \Acelle\Model\Role::whereCode('organization_admin')->first();
        if (!$adminRole) {
            $adminRole = \Acelle\Model\Role::newGlobalRole();
            $adminRole->code = 'organization_admin';
            $adminRole->name = 'Full Access';
            $adminRole->description = 'The default role for new users who have full access to the customer account.';
            $adminRole->is_global = true;
            $adminRole->readonly = true;
            $adminRole->save();
        }
        // add permission
        $adminRole->assignDefaultAdminPermissions();

        // find or create default readonly role
        $readonlyRole = \Acelle\Model\Role::whereCode('organization_readonly')->first();
        if (!$readonlyRole) {
            $readonlyRole = \Acelle\Model\Role::newGlobalRole();
            $readonlyRole->code = 'organization_readonly';
            $readonlyRole->name = 'Read Only';
            $readonlyRole->description = 'The default role for new users who can view all customer account data without the ability to edit or change it.';
            $readonlyRole->is_global = true;
            $readonlyRole->readonly = true;
            $readonlyRole->save();
        }
        // add permission
        $readonlyRole->assignDefaultReadonlyPermissions();

        // Add administrator role to all old users that upgrade to rbac version
        foreach(\Acelle\Model\User::whereDoesntHave('roles')->get() as $user) {
            $user->setRole($adminRole);
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
