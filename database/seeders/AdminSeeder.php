<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class AdminSeeder extends Seeder
{
    /**
     * Seed the admin/superadmin user for MailClick.
     * 
     * Usage: php artisan db:seed --class=AdminSeeder
     */
    public function run()
    {
        $email = 'office@alucard.ro';
        $password = 'Alucard2025@!';

        // Check if user already exists
        $existingUser = DB::table('users')->where('email', $email)->first();

        if ($existingUser) {
            // Update password for existing user
            DB::table('users')->where('id', $existingUser->id)->update([
                'password' => Hash::make($password),
                'activated' => true,
                'status' => 'active',
                'updated_at' => now(),
            ]);

            $this->command->info("✅ User {$email} already exists — password updated.");

            // Ensure admin record exists
            $adminExists = DB::table('admins')->where('user_id', $existingUser->id)->exists();
            if (!$adminExists) {
                $adminGroup = DB::table('admin_groups')->where('name', 'Administrator')->first();
                DB::table('admins')->insert([
                    'uid' => uniqid(),
                    'user_id' => $existingUser->id,
                    'creator_id' => $existingUser->id,
                    'admin_group_id' => $adminGroup ? $adminGroup->id : 1,
                    'status' => 'active',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                $this->command->info("✅ Admin role assigned to {$email}.");
            } else {
                $this->command->info("✅ Admin role already exists for {$email}.");
            }

            return;
        }

        // Get the default language
        $language = DB::table('languages')->where('is_default', true)->first();
        if (!$language) {
            $language = DB::table('languages')->first();
        }

        // Create new user
        $userId = DB::table('users')->insertGetId([
            'uid' => uniqid(),
            'email' => $email,
            'password' => Hash::make($password),
            'first_name' => 'Admin',
            'last_name' => 'MailClick',
            'activated' => true,
            'status' => 'active',
            'api_token' => \Illuminate\Support\Str::random(60),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Get Administrator group
        $adminGroup = DB::table('admin_groups')->where('name', 'Administrator')->first();

        // Create admin record
        DB::table('admins')->insert([
            'uid' => uniqid(),
            'user_id' => $userId,
            'creator_id' => $userId,
            'admin_group_id' => $adminGroup ? $adminGroup->id : 1,
            'status' => 'active',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Create customer record (needed for SaaS mode)
        $customerExists = DB::table('customers')->where('user_id', $userId)->exists();
        if (!$customerExists) {
            DB::table('customers')->insert([
                'uid' => uniqid(),
                'user_id' => $userId,
                'language_id' => $language ? $language->id : 1,
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $this->command->info("✅ Admin user created: {$email} / {$password}");
        $this->command->info("✅ Admin role: Administrator (full access)");
    }
}
