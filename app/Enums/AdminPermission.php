<?php

namespace App\Enums;

enum AdminPermission: string
{
    case ApprovePayout = 'payout.approve';
    case ApproveRefund = 'refund.approve';
}
