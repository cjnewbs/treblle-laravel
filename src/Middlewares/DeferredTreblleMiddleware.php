<?php

declare(strict_types=1);

namespace Treblle\Middlewares;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;
use Treblle\Exceptions\ConfigurationException;
use Treblle\Exceptions\TreblleApiException;
use Treblle\Jobs\PushToTreblle;

use function config;
use function microtime;

class DeferredTreblleMiddleware extends \Treblle\Middlewares\TreblleMiddleware
{
    /**
     * @throws ConfigurationException|TreblleApiException
     */
    public function terminate(Request $request, JsonResponse|Response|SymfonyResponse $response): void
    {
        if (strlen((string) $response->getContent()) > 2 * 1024 * 1024) {
            if (! app()->environment('production')) {
                Log::error(
                    message: 'Cannot send response over 2MB to Treblle.',
                    context: [
                        'url' => $request->fullUrl(),
                        'date' => now()->toDateTimeString(),
                    ]
                );
            }

            return;
        }

        $payload = $this->factory->make(
            request: $request,
            response: $response,
            loadTime: microtime(true) - self::$start,
        );

        PushToTreblle::dispatch(
            $payload,
            self::$project ?? (string) config('treblle.project_id'),
        );
    }
}
