<?php

namespace App\Services;

use App\Models\Claim;
use App\Models\ClaimStatusLog;
use App\Models\IntegrationSetting;
use App\Models\User;
use App\Services\Telegram\ClaimNotifyService;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ClaimWorkflowService
{
    public const STATUSES = [
        'new', 'review', 'approved', 'assigned', 'en_route', 'delivered',
        'done', 'rejected', 'dispute', 'unconfirmed',
    ];

    public const CLOSED = ['done', 'rejected', 'unconfirmed'];

    /** @var array<string, list<string>> */
    protected array $transitions = [
        'new' => ['review', 'rejected'],
        'review' => ['approved', 'rejected'],
        'approved' => ['assigned'],
        'assigned' => ['en_route', 'assigned'],
        'en_route' => ['delivered'],
        'delivered' => ['done', 'dispute', 'unconfirmed'],
        'dispute' => ['en_route', 'done'],
        'done' => [],
        'rejected' => [],
        'unconfirmed' => [],
    ];

    public function __construct(protected ClaimNotifyService $notify) {}

    public static function courierSlaHours(): int
    {
        return max(1, min(720, (int) IntegrationSetting::getValue('courier_sla_hours', 72)));
    }

    public static function adminSlaHours(): int
    {
        return max(1, min(48, (int) IntegrationSetting::getValue('admin_sla_hours', 2)));
    }

    public function canTransition(string $from, string $to): bool
    {
        return in_array($to, $this->transitions[$from] ?? [], true);
    }

    /**
     * @param  array<string, mixed>  $extra
     */
    public function transition(
        Claim $claim,
        string $to,
        ?User $actorUser = null,
        string $actor = 'system',
        ?string $note = null,
        array $extra = [],
        bool $notify = true,
        string $source = 'panel',
    ): Claim {
        $from = $claim->status;
        if ($from !== $to && ! $this->canTransition($from, $to)) {
            throw ValidationException::withMessages([
                'status' => "Holat «{$from}» dan «{$to}» ga o‘tish mumkin emas.",
            ]);
        }

        $claim = DB::transaction(function () use ($claim, $from, $to, $actorUser, $actor, $note, $extra, $source) {
            foreach ($extra as $k => $v) {
                $claim->{$k} = $v;
            }

            if ($to === 'review' && ! $claim->admin_first_seen_at) {
                $claim->admin_first_seen_at = now();
            }
            if ($to === 'assigned') {
                $hours = self::courierSlaHours();
                $claim->assigned_at = now();
                $claim->courier_deadline_at = now()->addHours($hours);
                $claim->courier_sla_warned_at = null;
                $claim->courier_sla_overdue_at = null;
                $claim->en_route_at = null;
                $claim->delivered_at = null;
                $claim->delivery_photo = null;
                $claim->customer_confirm = null;
                $claim->confirm_reason = null;
                $claim->confirmed_at = null;
                $claim->confirm_reminded_at = null;
                $claim->courier_rating = null;
                $claim->courier_rated_at = null;
                $claim->closed_at = null;
            }
            if ($to === 'en_route') {
                $claim->en_route_at = now();
            }
            if ($to === 'delivered') {
                $claim->delivered_at = now();
            }
            if ($to === 'done') {
                $claim->confirmed_at = $claim->confirmed_at ?? now();
                $claim->customer_confirm = $claim->customer_confirm ?: 'yes';
                $claim->closed_at = now();
            }
            if ($to === 'rejected' || $to === 'unconfirmed') {
                $claim->closed_at = now();
            }
            if ($to === 'dispute') {
                $claim->customer_confirm = 'no';
                $claim->confirmed_at = now();
                $claim->closed_at = null;
            }
            if ($from === 'dispute' && $to === 'en_route') {
                $claim->delivered_at = null;
                $claim->delivery_photo = null;
                $claim->customer_confirm = null;
                $claim->confirm_reason = null;
                $claim->confirmed_at = null;
                $claim->confirm_reminded_at = null;
            }

            $claim->status = $to;
            if ($actorUser && ! $claim->assignee_id && $actor === 'admin') {
                $claim->assignee_id = $actorUser->id;
            }
            $claim->save();

            if ($from !== $to) {
                ClaimStatusLog::query()->create([
                    'claim_id' => $claim->id,
                    'from_status' => $from,
                    'to_status' => $to,
                    'user_id' => $actorUser?->id,
                    'actor' => $actor,
                    'source' => $source,
                    'note' => $note,
                ]);
            }

            return $claim->fresh([
                'customer', 'shop', 'seller', 'product', 'order', 'orderItem',
                'assignee', 'courier', 'botUser', 'items.orderItem', 'items.product',
                'pickupShop', 'logs.user',
            ]);
        });

        // Telegram xabarlari tranzaksiyadan tashqarida — API xatosi holatni orqaga qaytarmasin
        if ($notify) {
            try {
                $this->notify->afterTransition($claim, $from, $to);
            } catch (\Throwable $e) {
                report($e);
            }
        }

        return $claim;
    }

    public function accept(Claim $claim, User $admin): Claim
    {
        return $this->transition($claim, 'review', $admin, 'admin', 'Qabul qilindi');
    }

    public function approve(Claim $claim, User $admin, array $extra = []): Claim
    {
        if (! $claim->photos_viewed_at && count($claim->photos ?? []) > 0) {
            throw ValidationException::withMessages([
                'photos' => 'Avval mijoz fotosini ochib ko‘ring.',
            ]);
        }

        return $this->transition($claim, 'approved', $admin, 'admin', 'Brak tasdiqlandi', $extra);
    }

    public function reject(Claim $claim, User $admin, string $reason): Claim
    {
        if (trim($reason) === '') {
            throw ValidationException::withMessages(['reject_reason' => 'Rad sababi majburiy.']);
        }

        return $this->transition($claim, 'rejected', $admin, 'admin', $reason, [
            'reject_reason' => $reason,
        ]);
    }

    public function assignCourier(Claim $claim, User $admin, User $courier): Claim
    {
        if (! $courier->is_active) {
            throw ValidationException::withMessages(['courier_id' => 'Kuryer faol emas.']);
        }
        if (! $courier->isCourier()) {
            throw ValidationException::withMessages(['courier_id' => 'Tanlangan foydalanuvchi kuryer emas.']);
        }

        return $this->transition($claim, 'assigned', $admin, 'admin', 'Kuryer belgilandi: '.$courier->name, [
            'courier_id' => $courier->id,
        ]);
    }

    public function enRoute(Claim $claim, User $courier, string $source = 'panel'): Claim
    {
        $this->assertCourier($claim, $courier);

        return $this->transition($claim, 'en_route', $courier, 'courier', 'Yo‘ldaman', [], true, $source);
    }

    public function delivered(Claim $claim, User $courier, string $photoPath, string $source = 'panel'): Claim
    {
        $this->assertCourier($claim, $courier);
        if ($photoPath === '') {
            throw ValidationException::withMessages(['delivery_photo' => 'Topshirish fotosi majburiy.']);
        }

        return $this->transition($claim, 'delivered', $courier, 'courier', 'Yetkazildi', [
            'delivery_photo' => $photoPath,
        ], true, $source);
    }

    public function customerConfirm(Claim $claim, bool $yes, ?string $reason = null): Claim
    {
        if ($claim->status !== 'delivered') {
            throw ValidationException::withMessages(['status' => 'Tasdiq faqat yetkazilgan so‘rovda.']);
        }
        if ($claim->customer_confirm !== null) {
            throw ValidationException::withMessages(['confirm' => 'Allaqachon javob berilgan.']);
        }

        if ($yes) {
            return $this->transition($claim, 'done', null, 'customer', 'Mijoz tasdiqladi', [
                'customer_confirm' => 'yes',
                'confirmed_at' => now(),
            ], true, 'bot');
        }

        return $this->transition($claim, 'dispute', null, 'customer', $reason ?: 'Mijoz: almashtirilmadi', [
            'customer_confirm' => 'no',
            'confirm_reason' => $reason,
            'confirmed_at' => now(),
        ], true, 'bot');
    }

    public function rateCourier(Claim $claim, int $stars): Claim
    {
        if (! in_array($claim->status, ['done', 'unconfirmed'], true)) {
            throw ValidationException::withMessages(['status' => 'Baholash faqat yopilgan so‘rovda.']);
        }
        if (! $claim->courier_id) {
            throw ValidationException::withMessages(['courier' => 'Kuryer yo‘q.']);
        }
        if ($claim->courier_rating !== null) {
            throw ValidationException::withMessages(['rating' => 'Allaqachon baholagansiz.']);
        }
        $stars = max(1, min(5, $stars));
        $claim->update([
            'courier_rating' => $stars,
            'courier_rated_at' => now(),
        ]);

        return $claim->fresh(['courier', 'botUser']);
    }

    public function resolveDispute(Claim $claim, User $admin, string $action, ?string $note = null): Claim
    {
        if ($claim->status !== 'dispute') {
            throw ValidationException::withMessages(['status' => 'So‘rov nizo holatida emas.']);
        }

        return match ($action) {
            'retry' => $this->transition($claim, 'en_route', $admin, 'admin', $note ?: 'Kuryer qayta boradi'),
            'close' => $this->transition($claim, 'done', $admin, 'admin', $note ?: 'Mijoz bilan kelishildi', [
                'closed_at' => now(),
            ]),
            'sanction' => $this->transition($claim, 'done', $admin, 'admin', $note ?: 'Kuryerga chora', [
                'courier_sanction' => ((int) $claim->courier_sanction) + 1,
                'closed_at' => now(),
            ]),
            default => throw ValidationException::withMessages(['action' => 'Nomaʼlum amal.']),
        };
    }

    public function reportProblem(Claim $claim, User $courier, string $note): Claim
    {
        $this->assertCourier($claim, $courier);
        $claim->update(['problem_note' => $note]);
        $this->notify->courierProblem(
            $claim->fresh(['courier', 'customer', 'botUser', 'orderItem', 'product', 'items.orderItem']),
            $note
        );

        return $claim->fresh();
    }

    public function markPhotosViewed(Claim $claim): Claim
    {
        if (! $claim->photos_viewed_at) {
            $claim->update(['photos_viewed_at' => now()]);
        }

        return $claim->fresh();
    }

    protected function assertCourier(Claim $claim, User $courier): void
    {
        if ((int) $claim->courier_id !== (int) $courier->id) {
            throw ValidationException::withMessages([
                'courier' => 'Bu topshiriq boshqa kuryerga tayinlangan.',
            ]);
        }
    }
}
