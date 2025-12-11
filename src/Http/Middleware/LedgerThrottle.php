<?php

namespace MartinLechene\LedgerManager\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Cache\RateLimiter;

class LedgerThrottle
{
    public function __construct(protected RateLimiter $limiter) {}

    public function handle(Request $request, Closure $next)
    {
        $key = $this->resolveRequestSignature($request);
        $limits = $this->getLimits($request);

        foreach ($limits as $limit) {
            if ($this->limiter->tooManyAttempts($key, $limit['attempts'], $limit['decay'])) {
                return response()->json([
                    'success' => false,
                    'error' => 'Too many requests. Please slow down.',
                    'retry_after' => $this->limiter->availableIn($key),
                ], 429);
            }

            $this->limiter->hit($key, $limit['decay']);
        }

        return $next($request);
    }

    protected function resolveRequestSignature(Request $request): string
    {
        return 'ledger:' . $request->ip() . '|' . ($request->user()?->id ?? 'guest');
    }

    protected function getLimits(Request $request): array
    {
        $path = $request->path();

        // Limites strictes pour la signature
        if (str_contains($path, 'sign')) {
            return [
                ['attempts' => 10, 'decay' => 60], // 10 par minute
                ['attempts' => 50, 'decay' => 3600], // 50 par heure
            ];
        }

        // Limites modérées pour la découverte
        if (str_contains($path, 'discover')) {
            return [
                ['attempts' => 5, 'decay' => 60],
                ['attempts' => 30, 'decay' => 3600],
            ];
        }

        // Limites standard
        return [
            ['attempts' => 60, 'decay' => 60],
            ['attempts' => 500, 'decay' => 3600],
        ];
    }
}

