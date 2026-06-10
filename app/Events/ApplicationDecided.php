<?php

namespace App\Events;

use App\Models\Application;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ApplicationDecided
{
    use Dispatchable, SerializesModels;

    public function __construct(public Application $application, public string $decision)
    {
    }
}
