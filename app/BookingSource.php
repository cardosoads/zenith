<?php

namespace App;

enum BookingSource: string
{
    case Widget = 'widget';
    case Dashboard = 'dashboard';
}
