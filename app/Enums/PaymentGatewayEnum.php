<?php

namespace App\Enums;

enum PaymentGatewayEnum: string
{
    case RAZORPAY = 'Razorpay';
    case PHONEPE = 'PhonePe';
    case STRIPE = 'Stripe';

    /**
     * Fields required for each gateway
     */
    public function fields(): array
    {
        return match ($this) {
            self::RAZORPAY => [
                'key_id'     => 'Razorpay Key ID',
                'key_secret' => 'Razorpay Key Secret',
                'merchant_id' => 'Merchant ID',
            ],

            self::PHONEPE => [
                'client_id'  => 'Client ID',
                'salt_key'   => 'Salt Key',
                'merchant_id' => 'Merchant ID',
                'salt_index' => 'Salt Index',
                'webhook_username' => 'Webhook Username',
                'webhook_password' => 'Webhook Password',
            ],

            self::STRIPE => [
                'publishable_key' => 'Publishable Key',
                'secret_key'      => 'Secret Key',
                'webhook_secret'  => 'Webhook Secret',
            ],
        };
    }

    /**
     * Get payment scope (national/international)
     */
    public function scope(): string
    {
        return match ($this) {
            self::RAZORPAY, self::PHONEPE => 'NATIONAL',
            self::STRIPE => 'INTERNATIONAL',
        };
    }

    /**
     * Get gateways by scope
     */
    public static function getByScope(string $scope): array
    {
        return array_filter(self::cases(), function($gateway) use ($scope) {
            return $gateway->scope() === strtoupper($scope);
        });
    }

    /**
     * Get display name
     */
    public function displayName(): string
    {
        return match ($this) {
            self::RAZORPAY => 'Razorpay',
            self::PHONEPE => 'PhonePe',
            self::STRIPE => 'Stripe',
        };
    }
}