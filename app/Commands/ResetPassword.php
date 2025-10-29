<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use App\Models\UserModel;

class ResetPassword extends BaseCommand
{
    protected $group       = 'Auth';
    protected $name        = 'auth:reset-password';
    protected $description = 'Reset a user password';
    protected $usage       = 'auth:reset-password <email> <new_password>';
    protected $arguments   = [
        'email'        => 'User email address',
        'new_password' => 'New password (plain text)',
    ];

    public function run(array $params)
    {
        $email = $params[0] ?? CLI::prompt('User email');
        $newPassword = $params[1] ?? CLI::prompt('New password');

        if (empty($email) || empty($newPassword)) {
            CLI::error('Email and password are required');
            return;
        }

        $userModel = new UserModel();
        $user = $userModel->where('email', $email)->first();

        if (!$user) {
            CLI::error("User not found: $email");
            return;
        }

        CLI::write("User found:", 'yellow');
        CLI::write("  ID: " . $user['id_users']);
        CLI::write("  Name: " . $user['nombre']);
        CLI::write("  Email: " . $user['email']);
        CLI::write("  Role: " . $user['id_roles']);
        CLI::write("  Status: " . $user['estado']);
        CLI::newLine();

        // Verify if current password matches
        CLI::write("Current hash: " . substr($user['password_hash'], 0, 60));

        if (password_verify($newPassword, $user['password_hash'])) {
            CLI::write("✓ Password already matches the hash!", 'green');
        } else {
            CLI::write("✗ Password does NOT match current hash", 'red');
            CLI::newLine();

            // Generate new hash
            $newHash = password_hash($newPassword, PASSWORD_DEFAULT);
            CLI::write("New hash: " . substr($newHash, 0, 60));

            // Update database
            $updated = $userModel->update($user['id_users'], [
                'password_hash' => $newHash
            ]);

            if ($updated) {
                CLI::write("✓ Password updated successfully!", 'green');

                // Verify the new hash works
                $userUpdated = $userModel->find($user['id_users']);
                if (password_verify($newPassword, $userUpdated['password_hash'])) {
                    CLI::write("✓ Verification: New hash works correctly!", 'green');
                } else {
                    CLI::error("✗ Verification failed: New hash doesn't work");
                }
            } else {
                CLI::error("Failed to update password");
            }
        }

        CLI::newLine();
        CLI::write("You can now login with:", 'yellow');
        CLI::write("  Email: $email");
        CLI::write("  Password: $newPassword");
    }
}
