<?php

namespace App\Enums;

enum ShopStatus: string
{
    case Pending = 'pending';
    case Approved = 'approved';
    case Rejected = 'rejected';
    case Suspended = 'suspended';

    public function canSell(): bool
    {
        return $this === self::Approved;
    }

    public function requiresReason(): bool
    {
        return in_array($this, [self::Rejected, self::Suspended], true);
    }

    /**
     * @return array<int, self>
     */
    public function allowedTransitions(): array
    {
        return match ($this) {
            self::Pending => [self::Approved, self::Rejected],
            self::Approved => [self::Suspended],
            self::Rejected => [self::Pending],
            self::Suspended => [self::Approved],
        };
    }

    public function canTransitionTo(self $target): bool
    {
        return in_array($target, $this->allowedTransitions(), true);
    }
}
