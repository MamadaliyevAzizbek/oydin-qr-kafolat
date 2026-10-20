<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Claim;
use App\Models\User;
use App\Services\ClaimWorkflowService;
use App\Services\PanelBootService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ClaimController extends Controller
{
    public function update(
        Request $request,
        Claim $claim,
        ClaimWorkflowService $workflow,
        PanelBootService $panelBoot
    ) {
        $data = $request->validate([
            'action' => 'nullable|string|in:accept,approve,reject,assign,photos_viewed,dispute_retry,dispute_close,dispute_sanction',
            'status' => 'nullable|string',
            'assignee_id' => 'nullable|exists:users,id',
            'courier_id' => 'nullable|exists:users,id',
            'reject_reason' => 'nullable|string|max:500',
            'replacement_cost' => 'nullable|numeric|min:0',
            'replacement_product' => 'nullable|string|max:255',
            'replacement_qty' => 'nullable|integer|min:1|max:999',
            'pickup_shop_id' => 'nullable|exists:shops,id',
            'note' => 'nullable|string|max:1000',
            'address' => 'nullable|string|max:500',
        ]);

        $admin = $request->user();
        $action = $data['action'] ?? null;

        if ($action === 'photos_viewed') {
            $claim = $workflow->markPhotosViewed($claim);
        } elseif ($action === 'accept' || ($data['status'] ?? null) === 'review') {
            $claim = $workflow->accept($claim, $admin);
        } elseif ($action === 'approve' || ($data['status'] ?? null) === 'approved') {
            $extra = array_filter([
                'replacement_product' => $data['replacement_product'] ?? null,
                'replacement_qty' => $data['replacement_qty'] ?? null,
                'pickup_shop_id' => $data['pickup_shop_id'] ?? null,
                'replacement_cost' => $data['replacement_cost'] ?? null,
            ], fn ($v) => $v !== null);
            $claim = $workflow->approve($claim, $admin, $extra);
        } elseif ($action === 'reject' || ($data['status'] ?? null) === 'rejected') {
            $claim = $workflow->reject($claim, $admin, (string) ($data['reject_reason'] ?? $data['note'] ?? ''));
        } elseif ($action === 'assign' || ($data['status'] ?? null) === 'assigned') {
            $courier = User::query()->findOrFail((int) ($data['courier_id'] ?? 0));
            $claim = $workflow->assignCourier($claim, $admin, $courier);
        } elseif ($action === 'dispute_retry') {
            $claim = $workflow->resolveDispute($claim, $admin, 'retry', $data['note'] ?? null);
        } elseif ($action === 'dispute_close') {
            $claim = $workflow->resolveDispute($claim, $admin, 'close', $data['note'] ?? null);
        } elseif ($action === 'dispute_sanction') {
            $claim = $workflow->resolveDispute($claim, $admin, 'sanction', $data['note'] ?? null);
        } else {
            return response()->json(['message' => 'Nomaʼlum amal yoki holat.'], 422);
        }

        if (! empty($data['assignee_id'])) {
            $claim->update(['assignee_id' => $data['assignee_id']]);
        }
        if (! empty($data['address'])) {
            $claim->update(['address' => $data['address']]);
        }

        return response()->json([
            'ok' => true,
            'claim' => $panelBoot->panelClaimRow($claim->fresh([
                'customer', 'shop', 'seller', 'product', 'order', 'orderItem',
                'assignee', 'courier', 'botUser', 'logs.user', 'items.orderItem', 'items.product', 'pickupShop',
            ])),
        ]);
    }

    public function courierAction(Request $request, Claim $claim, ClaimWorkflowService $workflow, PanelBootService $panelBoot)
    {
        $user = $request->user();
        // Yoʻldaman / Yetkazdim / muammo — faqat tayinlangan kuryer (admin o‘rniga bosa olmaydi)
        if (! $user->isCourier()) {
            return response()->json(['message' => 'Faqat kuryer akkaunti.'], 403);
        }
        if ((int) $claim->courier_id !== (int) $user->id) {
            return response()->json(['message' => 'Bu topshiriq sizniki emas.'], 403);
        }

        $data = $request->validate([
            'action' => 'required|in:en_route,deliver,problem',
            'note' => 'nullable|string|max:500',
            'photo' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:10240',
        ]);

        $actor = $user;

        try {
            if ($data['action'] === 'en_route') {
                $claim = $workflow->enRoute($claim, $actor);
            } elseif ($data['action'] === 'deliver') {
                $file = $request->file('photo');
                if (! $file && ! $claim->delivery_photo) {
                    return response()->json(['message' => 'Topshirish fotosi majburiy.'], 422);
                }
                $path = $claim->delivery_photo;
                if ($file) {
                    $path = $file->store('claims/'.$claim->id.'/delivery', 'public');
                }
                $claim = $workflow->delivered($claim, $actor, (string) $path);
            } else {
                $note = trim((string) ($data['note'] ?? ''));
                if ($note === '') {
                    return response()->json(['message' => 'Muammo sababi majburiy.'], 422);
                }
                $claim = $workflow->reportProblem($claim, $actor, $note);
            }
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'message' => collect($e->errors())->flatten()->first() ?: $e->getMessage(),
                'errors' => $e->errors(),
            ], 422);
        }

        return response()->json([
            'ok' => true,
            'claim' => $panelBoot->panelClaimRow($claim->fresh([
                'customer', 'shop', 'seller', 'product', 'order', 'orderItem',
                'assignee', 'courier', 'botUser', 'logs.user', 'items.orderItem', 'items.product', 'pickupShop',
            ])),
        ]);
    }
}
