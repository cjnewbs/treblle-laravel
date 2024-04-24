<?php

namespace Treblle\Jobs;

use Illuminate\Support\Arr;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Treblle\Http\Endpoint;
use Treblle\Treblle;

class PushToTreblle implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        private \Treblle\Utils\DataObjects\Data $payload,
        private ?string $projectId = null,
    ){}

    /**
     * Push log entry to Treblle.
     */
    public function handle(): void
    {
        Treblle::log(
            endpoint: Arr::random(Endpoint::cases()),
            data: $this->payload,
            projectId: $this->projectId,
        );
    }
}
