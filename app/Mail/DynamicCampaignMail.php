<?php

namespace App\Mail;

use App\Models\Lead;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class DynamicCampaignMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Lead $lead,
        public string $mailSubject,
        public string $bodyHtml,
        public ?string $buttonLabel = null,
        public ?string $buttonUrl = null,
        public ?string $imagePath = null,
        public ?string $openTrackingToken = null,
        public ?string $unsubscribeToken = null,
    ) {}

    public function build(): static
    {
        return $this->subject($this->mailSubject)
            ->view('emails.campaign_template')
            ->with([
                'lead' => $this->lead,
                'bodyHtml' => $this->bodyHtml,
                'buttonLabel' => $this->buttonLabel,
                'buttonUrl' => $this->buttonUrl,
                'imagePath' => $this->imagePath,
                'openTrackingToken' => $this->openTrackingToken,
                'unsubscribeToken' => $this->unsubscribeToken,
            ]);
    }
}
