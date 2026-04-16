<?php

namespace App\Enums;

enum PlanType: string
{
    case Free    = 'free';
    case Plus    = 'plus';
    case Premium = 'premium';
}