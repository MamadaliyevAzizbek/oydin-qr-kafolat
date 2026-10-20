<?php

namespace App\Services\Billz;

use App\Models\IntegrationSetting;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class BillzClient
{
    protected string $baseUrl;

    public function __construct()
    {
        $this->baseUrl = rtrim(config('billz.base_url'), '/');
    }

    public function secretToken(): ?string
    {
        return IntegrationSetting::getEncrypted('billz_secret_token')
            ?: config('billz.secret_token');
    }

    public function login(bool $force = false): string
    {
        if (! $force) {
            $cached = Cache::get('billz.access_token');
            if ($cached) {
                return $cached;
            }
        }

        $secret = $this->secretToken();
        if (! $secret) {
            throw new RuntimeException('Billz secret_token sozlanmagan.');
        }

        $this->throttle();

        $response = Http::acceptJson()
            ->asJson()
            ->post($this->baseUrl.'/v1/auth/login', [
                'secret_token' => $secret,
            ]);

        if (! $response->successful()) {
            throw new RuntimeException('Billz login xato: '.$response->body());
        }

        $data = $response->json('data') ?? [];
        $token = $data['access_token'] ?? null;
        if (! $token) {
            throw new RuntimeException('Billz access_token topilmadi.');
        }

        $ttl = max(60, (int) ($data['expires_in'] ?? 86400) - 300);
        Cache::put('billz.access_token', $token, $ttl);
        IntegrationSetting::setValue('billz_token_expires_at', now()->addSeconds($ttl)->toIso8601String());

        return $token;
    }

    public function get(string $path, array $query = []): array
    {
        return $this->request('get', $path, $query);
    }

    public function post(string $path, array $data = []): array
    {
        return $this->request('post', $path, $data);
    }

    protected function request(string $method, string $path, array $payload = []): array
    {
        $this->throttle();

        $response = $this->http()->{$method}(
            $this->baseUrl.'/'.ltrim($path, '/'),
            $payload
        );

        if ($response->status() === 401) {
            Cache::forget('billz.access_token');
            $this->login(true);
            $this->throttle();
            $response = $this->http()->{$method}(
                $this->baseUrl.'/'.ltrim($path, '/'),
                $payload
            );
        }

        if ($response->status() === 429) {
            usleep(1_000_000);
            $response = $this->http()->{$method}(
                $this->baseUrl.'/'.ltrim($path, '/'),
                $payload
            );
        }

        if (! $response->successful()) {
            throw new RuntimeException("Billz {$method} {$path} xato: ".$response->body());
        }

        return $response->json() ?? [];
    }

    protected function http(): PendingRequest
    {
        return Http::acceptJson()
            ->withToken($this->login())
            ->withHeaders([
                'Billz-Response-Channel' => 'HTTP',
            ])
            ->timeout(60);
    }

    protected function throttle(): void
    {
        $ms = (int) config('billz.rate_limit_ms', 550);
        $key = 'billz.last_request_at';
        $last = Cache::get($key);
        if ($last) {
            $elapsed = (int) ((microtime(true) - $last) * 1000);
            if ($elapsed < $ms) {
                usleep(($ms - $elapsed) * 1000);
            }
        }
        Cache::put($key, microtime(true), 10);
    }
}
