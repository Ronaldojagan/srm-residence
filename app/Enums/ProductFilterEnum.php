<?php

namespace App\Enums;

class ProductFilterEnum extends Enum{
    const RECENTLY_ADDED = "recently_added";
    const LOW_QUANTITY = "low_quantity";
    const HIGH_QUANTITY = "high_quantity";
    const UNAPPROVED = "unapproved";
    const ACTIVE = "active";
    const INACTIVE = "inactive";
}
