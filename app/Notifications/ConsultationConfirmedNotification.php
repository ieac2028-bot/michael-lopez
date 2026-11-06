<?php

namespace App\Notifications;

use App\Models\CaseRecord;
use App\Models\Inquiry;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ConsultationConfirmedNotification extends Notification
{
    use Queueable;

    public function __construct(
        private readonly CaseRecord $caseRecord,
        private readonly Inquiry $inquiry,
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Your consultation has been confirmed')
            ->greeting('Consultation confirmed')
            ->line('The selected attorney accepted the consultation request.')
            ->line('Case reference: ' . $this->caseRecord->reference_no)
            ->line('Preferred date: ' . ($this->inquiry->preferred_date?->format('M j, Y') ?? 'To be arranged'))
            ->line('Preferred time: ' . ($this->inquiry->preferred_time ?? 'To be arranged'))
            ->line('Consultation method: ' . ucfirst(str_replace('_', ' ', $this->inquiry->consultation_method ?? 'To be arranged')))
            ->line('Please contact the firm if you need to adjust the consultation details.');
    }
}
