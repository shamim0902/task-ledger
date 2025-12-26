<?php

namespace TaskLedger\App\Utils\Auth;

use Exception;
use TaskLedger\App\App;
use TaskLedger\App\Utils\Support\Hash;
use TaskLedger\Framework\Http\Request\Request;

class Authorizer
{
    /**
     * The name of the app id header used to identify the app.
     *
     * @var string
     */
    protected static $appIdKey = 'X-FLUENT-APP-ID';
    
    /**
     * The name of the custom token header used for authorization.
     *
     * @var string
     */
    protected static $tokenKey = 'X-FLUENT-APP-TOKEN';

    /**
     * Get the currently logged in user.
     * 
     * @return mixed
     */
    public function user()
    {
        return App::user();
    }

    /**
     * Check if the request is authorized either by user capability or token.
     *
     * @param  \TaskLedger\Framework\Http\Request\Request $request
     * @param  string   $capability
     * @param  mixed    ...$args
     * @return bool
     */
    public function check(Request $request, $capability, ...$args)
    {
        try {
            if ($this->authorize($request, $capability, ...$args)) {
                return true;
            }

            return $this->authorizeUsingToken($request, ...$args);

        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Authorize the request based on user capabilities.
     *
     * @param  \TaskLedger\Framework\Http\Request\Request $request
     * @param  string   $capability
     * @param  mixed    ...$args
     * @return bool
     */
    public function authorize(Request $request, $capability, ...$args)
    {
        return $request->user()->can($capability);
    }

    /**
     * Attempt to authorize using a token provided in the request headers.
     *
     * @param  \TaskLedger\Framework\Http\Request\Request $request
     * @param  mixed    ...$args
     * @return bool
     */
    public function authorizeUsingToken(Request $request, ...$args)
    {
        // Plain $token: p0K2t2HG0cahu9ZRx0sZuZdUHyhfWmVug3ALJHxX
        if (!$token = $request->header(static::$tokenKey)) {
            return false;
        }

        return Hash::check($token, $this->getUserToken($request));
    }

    /**
     * Retrieve the user token to compare with the request token.
     *
     * @param  \TaskLedger\Framework\Http\Request\Request $request
     * @return string|null
     */
    protected function getUserToken(Request $request)
    {
        // Return the stored hash from the storage.

        // $appId = $request->header(static::$appIdKey);

        // $token = TokenModel::where('app_id', $appId)->firstOrFail();

        // return $token->app_key;
    }
}
