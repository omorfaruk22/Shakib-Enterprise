<?php
namespace App\Services;

class WhatsAppService
{
    public static function link(string $message = 'Hello SAKIB ENTERPRISE, I want to know about your travel services.'): string
    {
        $number = preg_replace('/\D+/', '', setting('whatsapp', ''));
        if ($number === '') {
            return '#';
        }
        return 'https://wa.me/' . $number . '?text=' . rawurlencode($message);
    }

    public static function bookingLink(array $booking): string
    {
        $msg = "Hello SAKIB ENTERPRISE, I want to book.\n"
            . "Reference: " . ($booking['booking_reference'] ?? '') . "\n"
            . "Service: " . ($booking['service_type'] ?? '') . "\n"
            . "From: " . ($booking['departure_location'] ?? '') . "\n"
            . "To: " . ($booking['destination'] ?? '') . "\n"
            . "Date: " . ($booking['journey_date'] ?? '');
        return self::link($msg);
    }

    public static function enabled(): bool
    {
        return preg_replace('/\D+/', '', setting('whatsapp', '')) !== '';
    }
}
