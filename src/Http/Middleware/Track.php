<?php

declare(strict_types = 1);

namespace McMatters\LaravelTracking\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\{Arr, Carbon, Facades\Config};
use McMatters\LaravelTracking\Models\Tracking;
use Symfony\Component\HttpFoundation\{JsonResponse, RedirectResponse, Response};
use const false, null, true;
use function in_array, json_encode;

/**
 * Class Track
 *
 * @package McMatters\LaravelTracking\Http\Middleware
 */
class Track
{
    /**
     * @var string
     */
    protected $configName = 'tracking';

    /**
     * @var array
     */
    protected $config = [];

    /**
     * @var \McMatters\LaravelTracking\Models\Tracking
     */
    protected $trackingModel;

    /**
     * Track constructor.
     */
    public function __construct()
    {
        $this->config = Config::get($this->configName, []);
    }

    /**
     * @param Request $request
     * @param Closure $next
     * @param string|null $guard
     *
     * @return mixed
     */
    public function handle(Request $request, Closure $next, string $guard = null)
    {
        $user = $request->user($guard);

        if ($this->shouldSkipTracking($user, $request)) {
            return $next($request);
        }

        $this->track($user, $request);

        $response = $next($request);

        $this->trackResponse($response);

        return $response;
    }

    /**
     * @param mixed $user
     * @param Request $request
     *
     * @return bool
     */
    protected function shouldSkipTracking($user, Request $request): bool
    {
        return $this->shouldSkipAnonymous($user) ||
            $this->shouldSkipUser($user) ||
            $this->shouldSkipUri($request);
    }

    /**
     * @param mixed $user
     * @param Request $request
     *
     * @return void
     */
    protected function track($user, Request $request): void
    {
        $input = $request->all();

        $this->trackingModel = Tracking::query()->create([
            'user_id' => $user ? $user->getKey() : null,
            'uri' => $request->getPathInfo(),
            'method' => $request->method(),
            'input' => $input ? json_encode($input) : null,
            'headers' => $request->headers->all(),
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'created_at' => (string) Carbon::now(),
        ]);
    }

    /**
     * @param mixed $response
     *
     * @return void
     */
    protected function trackResponse($response): void
    {
        $data = [];

        if ($response instanceof JsonResponse) {
            $data = $response->getContent();
        } elseif ($response instanceof RedirectResponse) {
            $data = ['redirect' => $response->getTargetUrl()];
        } elseif ($response instanceof Response &&
            false !== ($content = $response->getContent())
        ) {
            $data = ['html' => $content];
        }

        if ($data) {
            $this->trackingModel->update(['response' => $data]);
        }
    }

    /**
     * @param mixed $user
     *
     * @return bool
     */
    protected function shouldSkipAnonymous($user): bool
    {
        return null === $user && Arr::get($this->config, 'skip.anonymous');
    }

    /**
     * @param mixed $user
     *
     * @return bool
     */
    protected function shouldSkipUser($user): bool
    {
        return null !== $user &&
            (
                $this->shouldSkipUserByName($user) ||
                $this->shouldSkipUserByEmail($user)
            );
    }

    /**
     * @param Request $request
     *
     * @return bool
     */
    protected function shouldSkipUri(Request $request): bool
    {
        return in_array(
            $request->getRequestUri(),
            Arr::get($this->config, 'skip.uris', []),
            true
        );
    }

    /**
     * @param mixed $user
     *
     * @return bool
     */
    protected function shouldSkipUserByName($user): bool
    {
        return in_array(
            $user->getAttribute(Arr::get($this->config, 'user_fields.name')),
            Arr::get($this->config, 'skip.names', []),
            true
        );
    }

    /**
     * @param mixed $user
     *
     * @return bool
     */
    protected function shouldSkipUserByEmail($user): bool
    {
        return in_array(
            $user->getAttribute(Arr::get($this->config, 'user_fields.email')),
            Arr::get($this->config, 'skip.emails', []),
            true
        );
    }
}
