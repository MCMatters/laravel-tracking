<?php

declare(strict_types = 1);

namespace McMatters\LaravelTracking\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Config;
use McMatters\LaravelTracking\Models\Tracking;
use const null, true;
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

        return $next($request);
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

        Tracking::query()->create([
            'user_id'    => $user ? $user->getKey() : null,
            'uri'        => $request->getPathInfo(),
            'method'     => $request->method(),
            'input'      => $input ? json_encode($input) : null,
            'ip'         => $request->ip(),
            'user_agent' => $request->userAgent(),
            'created_at' => (string) Carbon::now(),
        ]);
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
