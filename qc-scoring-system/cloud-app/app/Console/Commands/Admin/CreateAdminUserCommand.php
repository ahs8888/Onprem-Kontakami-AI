<?php

namespace App\Console\Commands\Admin;

use App\Models\Account\Admin;
use Illuminate\Console\Command;
use function Laravel\Prompts\text;
use Illuminate\Support\Facades\Hash;

class CreateAdminUserCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:make-admin';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create user for admin internal';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $name = text(
            label: 'What is admin name?',
            required: true
        );

        $email = text(
            label: 'Enter an email to login?',
            placeholder: 'E.g: yourname@example.com',
            hint: 'We will use this email to create your admin account.',
            validate: [
                'email' => 'email'
            ],
            required: true
        );
        $password = text(
            label: 'Enter your account password?',
            placeholder: 'password',
            hint: 'You will use this for next login.',
            validate: ['password' => 'min:8'],
            required: true
        );

        Admin::create([
            'name' => $name,
            'email' => $email,
            'password' => Hash::make($password),
        ]);
    }
}
