<?php

namespace App\Http\Requests\Auth;

use App\Models\User;
use Illuminate\Auth\Events\Lockout;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class LoginRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'login' => ['required', 'string'],
            'password' => ['required', 'string'],
        ];
    }

    public function authenticate(): void
    {
        $this->ensureIsNotRateLimited();

        $login = trim((string) $this->input('login'));
        $password = (string) $this->input('password');

        $user = $this->resolveUser($login);

        if (! $user || ! Auth::attempt(['email' => $user->email, 'password' => $password], $this->boolean('remember'))) {
            RateLimiter::hit($this->throttleKey());

            throw ValidationException::withMessages([
                'login' => 'Login yoki parol noto\'g\'ri.',
            ]);
        }

        if (! $user->fresh()->is_active) {
            Auth::logout();
            RateLimiter::hit($this->throttleKey());

            throw ValidationException::withMessages([
                'login' => 'Bu akkaunt oʼchirilgan.',
            ]);
        }

        RateLimiter::clear($this->throttleKey());
    }

    protected function resolveUser(string $login): ?User
    {
        $aliases = [
            'admin' => 'admin@oydin.uz',
        ];

        $key = Str::lower($login);
        if (isset($aliases[$key])) {
            return User::query()->where('email', $aliases[$key])->first();
        }

        return User::query()
            ->where('email', $login)
            ->orWhere('username', $key)
            ->orWhereRaw('LOWER(name) = ?', [$key])
            ->first();
    }

    public function ensureIsNotRateLimited(): void
    {
        if (! RateLimiter::tooManyAttempts($this->throttleKey(), 5)) {
            return;
        }

        event(new Lockout($this));

        $seconds = RateLimiter::availableIn($this->throttleKey());

        throw ValidationException::withMessages([
            'login' => trans('auth.throttle', [
                'seconds' => $seconds,
                'minutes' => ceil($seconds / 60),
            ]),
        ]);
    }

    public function throttleKey(): string
    {
        return Str::transliterate(Str::lower($this->string('login')).'|'.$this->ip());
    }
}
