<?php

/**
 * Base - Any modification needs to be approved, except the space inside the block of TODO
 */

namespace App\Providers;

use App\Http\Requests\Request;
use App\Utils\ConfigHelper;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;

class RouteServiceProvider extends ServiceProvider
{
    /**
     * The path to the "home" route for your application.
     *
     * This is used by Laravel authentication to redirect users after login.
     *
     * @var string
     */
    public const HOME = '/';
    /**
     * The controller namespace for the application.
     *
     * When present, controller route declarations will automatically be prefixed with this namespace.
     *
     * @var string|null
     */
    // protected $namespace = 'App\\Http\\Controllers';

    protected $throttleExcept = [
        'api' => [
            // TODO:
            'api/home/prerequisite',
            'api/home/device/current',
            'api/admin/prerequisite',
            'api/admin/device/current',

            // TODO
        ],
    ];

    /**
     * Define your route model bindings, pattern filters, etc.
     *
     * @return void
     */
    public function boot()
    {
        $this->configureRateLimiting();

        $this->routes(function () {
            Route::prefix('api')
                ->middleware('api')
                ->namespace($this->namespace)
                ->group(base_path('routes/api.php'));

            Route::middleware('web')
                ->namespace($this->namespace)
                ->group(base_path('routes/web.php'));
        });
    }

    /**
     * Configure the rate limiters for the application.
     *
     * @return void
     */
    protected function configureRateLimiting()
    {
        RateLimiter::for('api', function (Request $request) {
            if ($request->possiblyIs(...$this->throttleExcept['api'])) {
                return null;
            }
            $maxAttempts = ConfigHelper::get('throttle_request.max_attempts');
            $decayMinutes = ConfigHelper::get('throttle_request.decay_minutes');
            return ($decayMinutes == 1 ? Limit::perMinute($maxAttempts) : new Limit('', $maxAttempts, $decayMinutes))
                ->by(optional($request->user())->id ?: $request->ip());
        });
    }
}
