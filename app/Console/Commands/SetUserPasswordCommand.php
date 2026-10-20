<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;

class SetUserPasswordCommand extends Command
{
    protected $signature = 'user:password {login : username yoki email} {password : yangi parol}';

    protected $description = 'Xodim parolini ozgartirish (deploy dan keyin admin uchun)';

    public function handle(): int
    {
        $login = trim((string) $this->argument('login'));
        $password = (string) $this->argument('password');
        if (strlen($password) < 8) {
            $this->error('Parol kamida 8 belgi bolsin.');

            return self::FAILURE;
        }

        $key = mb_strtolower($login);
        $user = User::query()
            ->where('email', $login)
            ->orWhere('username', $key)
            ->orWhereRaw('LOWER(name) = ?', [$key])
            ->first();

        if (! $user) {
            $this->error('Foydalanuvchi topilmadi: '.$login);

            return self::FAILURE;
        }

        $user->update(['password' => Hash::make($password)]);
        $loginLabel = $user->username ?: $user->email;
        $this->info('OK: '.$user->name.' ('.$loginLabel.') paroli yangilandi.');

        return self::SUCCESS;
    }
}
