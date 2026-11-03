<?php

namespace App\Enums;

class UserType extends Enum{
    const SUPERADMIN ='Admin';
    const USER = 'customer';
    const DELIVERY = 'delivery';

    const USER_TYPE_NAME = [
    1 => 'Admin',
    0 => 'customer',
    2 => 'delivery',
];
}
