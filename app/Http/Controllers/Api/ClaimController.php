<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Claim;
use App\Models\ClaimStatusLog;
use App\Services\PanelBootService;
use App\Services\Telegram\TelegramBotService;
use Illuminate\Http\Request;

class ClaimController extends Controller
{
    public function update(Request $request, Claim $claim, TelegramBotService $bot, PanelBootService $panelBoot)
    {
        $data = $request->validate([
            'status' => 'required|in:new,review,approved,rejected,replaced',
            'assignee_id' => 'nullable|exists:users,id',
            'reject_reason' => 'nullable|string|max:500',
            'replacement_cost' => 'nullable|numeric|min:0',
            'replacement_product' => 'nullable|string|max:255',
            'note' => 'nullable|string|max:1000',
        ]);

        $from = $claim->status;
        $claim->fill(collect($data)->except(['note', 'assignee_id'])->all());
        if (empty($data['assignee_id']) && ! $claim->assignee_id) {
            $claim->assignee_id = $request->user()->id;
        } elseif (! empty($data['assignee_id'])) {
            $claim->assignee_id = $data['assignee_id'];
        }
        if (in_array($data['status'], ['rejected', 'replaced'], true)) {
            $claim->closed_at = now();
        } elseif (in_array($data['status'], ['new', 'review', 'approved'], true)) {
            $claim->closed_at = null;
        }
        $claim->save();

        ClaimStatusLog::query()->create([
            'claim_id' => $claim->id,
            'from_status' => $from,
            'to_status' => $claim->status,
            'user_id' => $request->user()->id,
            'note' => $data['note'] ?? null,
        ]);

        $claim->loadMissing(['botUser', 'orderItem', 'product']);
        if ($claim->botUser?->telegram_id) {
            try {
                $bot->sendMessage(
                    $claim->botUser->telegram_id,
                    $this->customerClaimStatusMessage($claim)
                );
            } catch (\Throwable) {
            }
        }

        $fresh = $claim->fresh([
            'customer', 'shop', 'seller', 'product', 'order', 'orderItem',
            'assignee', 'botUser', 'logs.user',
        ]);

        return response()->json([
            'ok' => true,
            'claim' => $panelBoot->panelClaimRow($fresh),
        ]);
    }

    /** Mijozga tushunarli bot xabari (ichki holat nomi emas). */
    protected function customerClaimStatusMessage(Claim $claim): string
    {
        $number = $claim->number;
        $product = $claim->product?->name ?? $claim->orderItem?->name ?? 'mahsulotingiz';

        return match ($claim->status) {
            'new' => "Shikoyatingiz qabul qilindi ✅\n"
                ."So‘rov: <b>{$number}</b>\n"
                ."Mahsulot: {$product}\n\n"
                .'Tez orada ko‘rib chiqamiz.',

            'review' => "Shikoyatingiz ko‘rib chiqilmoqda 🔎\n"
                ."So‘rov: <b>{$number}</b>\n"
                ."Mahsulot: {$product}\n\n"
                .'Servis mutaxassisi tekshiruvni boshladi. Natija shu botda xabar qilinadi.',

            'approved' => "Brak tasdiqlandi ✅\n"
                ."So‘rov: <b>{$number}</b>\n"
                ."Mahsulot: {$product}\n\n"
                .'Almashtirish uchun siz bilan bog‘lanamiz.',

            'rejected' => "So‘rov yopildi\n"
                ."So‘rov: <b>{$number}</b>\n"
                ."Mahsulot: {$product}\n\n"
                .($claim->reject_reason
                    ? "Sabab: {$claim->reject_reason}\n\n"
                    : '')
                .'Savollaringiz bo‘lsa, do‘kon yoki servisga murojaat qiling.',

            'replaced' => "Mahsulot almashtirildi ✅\n"
                ."So‘rov: <b>{$number}</b>\n"
                ."Mahsulot: {$product}\n\n"
                .'Rahmat! Agar yana muammo bo‘lsa, <b>Shikoyat</b> orqali yozing.',

            default => "So‘rov <b>{$number}</b> bo‘yicha yangilanish bor.",
        };
    }
}
