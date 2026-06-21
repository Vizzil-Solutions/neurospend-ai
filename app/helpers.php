<?php

use Carbon\Carbon;

if (!function_exists('format_currency')) {
    function format_currency($amount, $currency = 'USD') {
        $amount = (float)$amount;
        $symbols = [
            'USD' => '$',
            'EUR' => '€',
            'GBP' => '£',
            'PKR' => '₨',
            'INR' => '₹',
            'JPY' => '¥',
            'AUD' => 'A$',
            'CAD' => 'C$',
            'AED' => 'د.إ',
            'SGD' => 'S$',
        ];
        $symbol = $symbols[$currency] ?? $currency;
        return $symbol . number_format($amount, 2);
    }
}

if (!function_exists('format_date')) {
    function format_date($dateStr) {
        if (!$dateStr) return '';
        try {
            return Carbon::parse($dateStr)->format('M d, Y');
        } catch (\Exception $e) {
            return $dateStr;
        }
    }
}
