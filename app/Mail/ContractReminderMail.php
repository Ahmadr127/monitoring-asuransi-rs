<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ContractReminderMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public array $expiring;

    public array $expired;

    public string $subjectText;

    public string $purpose;

    public function __construct(array $expiring, array $expired, string $purpose = 'admin')
    {
        $this->expiring = $expiring;
        $this->expired = $expired;
        $this->purpose = $purpose;

        $expiringCount = count($expiring);
        $expiredCount = count($expired);

        if ($expiredCount > 0 && $expiringCount > 0) {
            $this->subjectText = "[REMINDER] {$expiredCount} Kontrak Berakhir & {$expiringCount} Akan Berakhir";
        } elseif ($expiredCount > 0) {
            $this->subjectText = "[REMINDER] {$expiredCount} Kontrak Telah Berakhir";
        } elseif ($expiringCount > 1) {
            $this->subjectText = "[REMINDER] {$expiringCount} Kontrak Akan Berakhir";
        } else {
            $first = $expiring[0] ?? null;
            $this->subjectText = "[REMINDER] Kontrak: " . ($first['nama_asuransi'] ?? 'Unknown');
        }
    }

    public function envelope(): Envelope
    {
        return new Envelope(subject: $this->subjectText);
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.contract-reminder',
            with: [
                'expiring' => $this->expiring,
                'expired' => $this->expired,
                'purpose' => $this->purpose,
            ],
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
