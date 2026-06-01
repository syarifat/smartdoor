<?php

namespace App\Mail\Transport;

use Symfony\Component\Mailer\SentMessage;
use Symfony\Component\Mailer\Transport\AbstractTransport;
use Symfony\Component\Mime\MessageConverter;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ResendTransport extends AbstractTransport
{
    protected string $apiKey;

    public function __construct(string $apiKey)
    {
        parent::__construct();
        $this->apiKey = $apiKey;
    }

    protected function doSend(SentMessage $message): void
    {
        $email = MessageConverter::toEmail($message->getOriginalMessage());

        $html = $email->getHtmlBody();
        $text = $email->getTextBody();
        
        $to = [];
        foreach ($email->getTo() as $address) {
            $to[] = $address->getAddress();
        }

        $fromAddress = null;
        $fromName = null;
        foreach ($email->getFrom() as $address) {
            $fromAddress = $address->getAddress();
            $fromName = $address->getName();
        }

        $from = $fromName ? "{$fromName} <{$fromAddress}>" : $fromAddress;

        // Kirim via HTTPS API Resend (Port 443, tidak akan terblokir oleh VPS)
        $response = Http::withToken($this->apiKey)
            ->post('https://api.resend.com/emails', [
                'from' => $from ?: 'onboarding@resend.dev',
                'to' => $to,
                'subject' => $email->getSubject(),
                'html' => $html,
                'text' => $text,
            ]);

        if (!$response->successful()) {
            Log::error('Gagal mengirim email via Resend API: ' . $response->body());
            throw new \Exception('Gagal mengirim email via Resend API: ' . $response->body());
        }
    }

    public function __toString(): string
    {
        return 'resend';
    }
}
