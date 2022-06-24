<?php

declare(strict_types=1);

namespace McMatters\LaravelTracking\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Config;
use McMatters\LaravelTracking\Models\Tracking;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;

use function in_array;

use const false;
use const null;
use const true;

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
     * @param \Illuminate\Http\Request $request
     * @param \Closure $next
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
     * @param \Illuminate\Http\Request $request
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
     * @param \Illuminate\Http\Request $request
     *
     * @return void
     */
    protected function track($user, Request $request): void
    {
        $input = Arr::except(
            $request->all(),
            $this->config['sanitize']['input'] ?? [],
        );

        $headers = Arr::except(
            $request->headers->all(),
            $this->config['sanitize']['headers'] ?? [],
        );

        $this->trackingModel = Tracking::query()->create([
            'user_id' => $user ? $user->getKey() : null,
            'uri' => $request->getPathInfo(),
            'method' => $request->method(),
            'input' => $input ?: null,
            'headers' => $headers ?: null,
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'created_at' => Carbon::now(),
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
        } elseif (
            $response instanceof Response &&
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
        return null === $user && ($this->config['skip']['anonymous'] ?? null);
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
     * @param \Illuminate\Http\Request $request
     *
     * @return bool
     */
    protected function shouldSkipUri(Request $request): bool
    {
        foreach ($this->config['skip']['uris'] ?? [] as $pattern) {
            if ($request->is($pattern)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param mixed $user
     *
     * @return bool
     */
    protected function shouldSkipUserByName($user): bool
    {
        return in_array(
            $user->getAttribute($this->config['user_fields']['name'] ?? 'name'),
            $this->config['skip']['names'] ?? [],
            true,
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
            $user->getAttribute($this->config['user_fields']['email'] ?? 'email'),
            $this->config['skip']['emails'] ?? [],
            true,
        );
    }
}
