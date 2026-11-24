<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

class KodeOtpMail extends Mailable
{
    public function __construct(public User $user, public string $kode, public string $tujuan) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: $this->tujuan === 'registrasi' ? 'Kode Verifikasi Akun Sidang Sarjana' : 'Kode Atur Ulang Kata Sandi Sidang Sarjana');
    }

    public function content(): Content
    {
        return new Content(view: 'emails.otp');
    }
}
