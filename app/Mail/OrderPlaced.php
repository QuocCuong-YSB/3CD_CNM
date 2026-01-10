<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class OrderPlaced extends Mailable
{
    use Queueable, SerializesModels;

    public $orderData;

    public function __construct($orderData)
    {
        $this->orderData = $orderData;
    }

    public function build()
    {
        return $this->subject('Xác nhận đặt hàng thành công - ' . $this->orderData['orderCode'])
            ->view('emails.order_placed');
    }
}
