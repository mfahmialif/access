<?php

use Illuminate\Support\Facades\Broadcast;

// TV Devices channel — public, no authentication needed
Broadcast::channel('tv-devices', function () {
    return true;
});
