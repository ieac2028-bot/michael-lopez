<?php

namespace App\Services;

use App\Models\CaseRecord;
use App\Models\Inquiry;
use App\Models\InquiryMessage;
use App\Models\User;
use App\Notifications\ConsultationConfirmedNotification;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class ConsultationPaymentService
{
    /** Mark unpaid payment proposals expired when a client or staff member next visits. */
    public function expireDueProposals(): void
    {
        $due = Inquiry::query()
            ->where('payment_status', 'awaiting_client')
            ->whereNotNull('payment_expires_at')
            ->where('payment_expires_at', '<=', now())
            ->get();

        foreach ($due as $inquiry) {
            $updated = Inquiry::query()
                ->whereKey($inquiry->id)
                ->where('payment_status', 'awaiting_client')
                ->where('payment_expires_at', '<=', now())
                ->update(['payment_status' => 'expired', 'payment_expired_at' => now(), 'updated_at' => now()]);

            if ($updated) {
                InquiryMessage::create([
                    'inquiry_id' => $inquiry->id,
                    'sender_role' => 'assistant',
                    'sender_name' => 'GPMO Payment Desk',
                    'body' => 'The unpaid consultation payment request expired. No payment was recorded and the consultation was not confirmed. Please message the office if you would like to continue.',
                ]);
            }
        }
    }

    public function verifyAndConfirm(Inquiry $inquiry, User $admin, string $reference, ?string $accessUrl, string $accessDetails): CaseRecord
    {
        $case = DB::transaction(function () use ($inquiry, $admin, $reference, $accessUrl, $accessDetails) {
            $locked = Inquiry::query()->lockForUpdate()->findOrFail($inquiry->id);
            abort_unless($locked->type === Inquiry::TYPE_CONSULTATION && $locked->payment_status === 'verifying', 409, 'This payment is not awaiting verification.');
            abort_if(CaseRecord::where('inquiry_id', $locked->id)->exists(), 409, 'A case has already been confirmed for this consultation.');

            $attorney = $locked->attorney;
            abort_unless($attorney, 422, 'The assigned attorney is no longer available for this request.');

            do {
                $caseReference = 'SAFA-' . now()->format('Y') . '-' . Str::upper(Str::random(6));
            } while (CaseRecord::where('reference_no', $caseReference)->exists());

            $verifiedAt = now();
            $case = CaseRecord::create([
                'inquiry_id' => $locked->id,
                'reference_no' => $caseReference,
                'client_email' => $locked->email,
                'title' => Str::limit($locked->message, 120, ''),
                'attorney_id' => $attorney->id,
                'practice_area_id' => $locked->practice_area_id,
                'status' => 'active',
                'submitted_at' => $locked->created_at,
                'accepted_at' => $verifiedAt,
                'timeline' => [
                    ['label' => 'Client intake received', 'date' => $locked->created_at->toDateString(), 'state' => 'done'],
                    ['label' => 'Attorney accepted consultation', 'date' => $locked->fee_proposed_at?->toDateString(), 'state' => 'done'],
                    ['label' => 'Payment verified by firm', 'date' => $verifiedAt->toDateString(), 'state' => 'done'],
                    ['label' => 'Consultation confirmed', 'date' => $verifiedAt->toDateString(), 'state' => 'current'],
                ],
            ]);

            $locked->update([
                'status' => 'accepted',
                'payment_status' => 'verified',
                'payment_reference' => $reference,
                'payment_verified_by' => $admin->id,
                'payment_verified_at' => $verifiedAt,
                'meeting_access_url' => $accessUrl,
                'consultation_access_details' => $accessDetails,
            ]);

            InquiryMessage::create([
                'inquiry_id' => $locked->id,
                'sender_role' => 'admin',
                'sender_name' => $admin->name,
                'body' => 'Payment verified by the firm (reference ' . $reference . '). Your consultation is confirmed. Case reference: ' . $case->reference_no . '. Meeting access: ' . $accessDetails . ($accessUrl ? ' Join link: ' . $accessUrl : ''),
            ]);

            return $case;
        });

        $inquiry->refresh();
        $notification = new ConsultationConfirmedNotification($case, $inquiry);
        $recipients = collect([
            User::query()->whereRaw('LOWER(email) = ?', [mb_strtolower($inquiry->email)])->first(),
            $inquiry->attorney?->account,
        ])->filter()->unique('id');

        foreach ($recipients as $recipient) {
            try {
                if (in_array(config('mail.default'), ['log', 'array'], true)) {
                    Log::info('Consultation confirmed after manual payment verification; email transport is not configured.', ['inquiry_id' => $inquiry->id]);
                    break;
                }
                $recipient->notify($notification);
            } catch (\Throwable $exception) {
                Log::warning('Consultation was confirmed, but its email notification could not be sent.', [
                    'inquiry_id' => $inquiry->id,
                    'case_id' => $case->id,
                    'recipient_id' => $recipient->id,
                    'error' => $exception->getMessage(),
                ]);
            }
        }

        return $case;
    }
}
