<?php

use Carbon\Carbon;
use Illuminate\Support\Str;
use Illuminate\Support\Collection;

/**
 * Miscellaneous utility functions
 */

if (!function_exists('set_connector_property')) {
    function set_connector_property(\App\Property $property, \App\Kiosk $kiosk = null)
    {
        config(['services.connector.property_id' => $property->id ?? null]);
        config(['services.connector.ext_property_id' => $property->ext_property_id ?? null]);
        config(['services.connector.account_id' => $property->account_id ?? null]);
        if ($kiosk) {
            config(['services.connector.kiosk_identifier' => $kiosk->identifier]);
        }
    }
}

if (!function_exists('app_version')) {
    function app_version()
    {
        return preg_replace('/\s/', '', config('app.version'));
    }
}

if (!function_exists('app_routes')) {
    function app_routes()
    {
        if (\Cache::has('app:named:routes')) {
            return \Cache::get('app:named:routes');
        } else {
            $named_routes = collect(\Route::getRoutes())
                            ->mapWithKeys(function ($route) {
                                return [$route->getName() => $route->uri()];
                            });

            \Cache::forever('app:named:routes', $named_routes);

            return $named_routes;
        }

    }
}

if (!function_exists('monthsList')) {
    /**
     * Returns a collection of months
     * @return collection  list of months
     */
    function monthsList(bool $emptyFirst = false): Collection
    {
        $months = collect([
            ['number' => 0, 'name' => '', 'short' => ''],
            ['number' => 1, 'name' => 'January', 'short' => 'Jan'],
            ['number' => 2, 'name' => 'February', 'short' => 'Feb'],
            ['number' => 3, 'name' => 'March', 'short' => 'Mar'],
            ['number' => 4, 'name' => 'April', 'short' => 'Apr'],
            ['number' => 5, 'name' => 'May', 'short' => 'May'],
            ['number' => 6, 'name' => 'June', 'short' => 'Jun'],
            ['number' => 7, 'name' => 'July', 'short' => 'Jul'],
            ['number' => 8, 'name' => 'August', 'short' => 'Aug'],
            ['number' => 9, 'name' => 'September', 'short' => 'Sep'],
            ['number' => 10, 'name' => 'October', 'short' => 'Oct'],
            ['number' => 11, 'name' => 'November', 'short' => 'Nov'],
            ['number' => 12, 'name' => 'December', 'short' => 'Dec'],
        ]);
        if (!$emptyFirst) {
            $months = $months->splice(1);
        }

        return $months;
    }
}

if (!function_exists('daysOfWeekList')) {
    /**
     * Returns a collection of week days
     * @return collection  list of week days
     */
    function daysOfWeekList(array $opts = []): Collection
    {
        $opts = collect([
            'emptyFirst' => false,
            'startOnMonday' => true,
            'includeWeekend' => true,
        ])->merge($opts);

        $months = [];
        if ($opts['emptyFirst']) {
            $months[] = ['name' => '', 'short' => ''];
        }
        if (!$opts['startOnMonday'] && $opts['includeWeekend']) {
            $months[] = ['name' => 'Sunday', 'short' => 'Sun'];
        }
        $months[] = ['name' => 'Monday', 'short' => 'Mon'];
        $months[] = ['name' => 'Tuesday', 'short' => 'Tue'];
        $months[] = ['name' => 'Wednesday', 'short' => 'Wed'];
        $months[] = ['name' => 'Thursday', 'short' => 'Thu'];
        $months[] = ['name' => 'Friday', 'short' => 'Fri'];
        if ($opts['includeWeekend']) {
            $months[] = ['name' => 'Saturday', 'short' => 'Sat'];
            if ($opts['startOnMonday']) {
                $months[] = ['name' => 'Sunday', 'short' => 'Sun'];
            }
        }

        return collect($months);
    }
}

if (!function_exists('gendersList')) {
    /**
     * Returns a collection of genders
     * @return collection  list of genders
     */
    function genderList(bool $emptyFirst = false): Collection
    {
        $genders = collect([
            ['id' => '', 'name' => '', 'short' => ''],
            ['id' => 'F', 'name' => 'Female', 'short' => 'F'],
            ['id' => 'M', 'name' => 'Male', 'short' => 'M'],
        ]);
        if (!$emptyFirst) {
            $genders = $genders->splice(1);
        }

        return $genders;
    }
}

if (!function_exists('monthsMatch')) {
    /**
     * Compares two values representing dates or months to determine
     * if they match.
     * @param any $val1
     * @param any $val2
     * @return boolean if the months match
     */
    function monthsMatch($val1, $val2): bool
    {
        try {
            if (is_numeric($val1)) {
                if ($val1 > 0 && $val1 < 13) {
                    $val1 = Carbon::parse($val1 . '/1/2000');
                } elseif (strlen($val1) > 8) {
                    // Timestamp
                }
            } else {
                $val1 = Carbon::parse($val1);
            }
        } catch (Exception $ex) {
            $val1 = Carbon::parse($val1 . ' 1, 2000');
        }
        try {
            if (is_numeric($val2)) {
                if ($val2 > 0 && $val2 < 13) {
                    $val2 = Carbon::parse($val2 . '/1/2000');
                } elseif (strlen($val2) > 8) {
                    // Timestamp
                }
            } else {
                $val2 = Carbon::parse($val2);
            }
        } catch (Exception $ex) {
            $val2 = Carbon::parse($val2 . ' 1, 2000');
        }

        \Log::info('Month Check');
        \Log::info('Month 1 = ' . $val1->month);
        \Log::info('Month 2 = ' . $val2->month);
        \Log::info((bool)($val1->month == $val2->month) ? 'Yes' : 'No');

        return (bool)($val1->month == $val2->month);
    }
}

if (!function_exists('transformify')) {
    /**
     * Transform results with fractal
     *
     * @param mixed $data Data to transform
     * @param \League\Fractal\TransformerAbstract $transformer The transformer to use on this data.
     * @return Collection
     **/
    function transformify($data, $transformer)
    {
        if ($data instanceof Collection) {
            // Transform a collection.
            return fractal()->collection($data)->transformWith($transformer)->toArray();
        } else {
            /**
             * Transform a single item
             * @var array
             */
            $data = fractal()->item($data)->transformWith($transformer)->toArray();

            // Recursively convert the $data array to an object
            return json_decode(json_encode($data));
        }
    }
}

if (!function_exists('frameParams')) {
    /**
     * Merge in access_token with specified route params (for use in iFrame authentication)
     **/
    function frameParams($params = [])
    {
        if (request()->exists(['access_token', 'domain', 'userId'])) {
            $params['access_token'] = request()->input('access_token');
            $params['domain'] = request()->input('domain');
            $params['userId'] = request()->input('userId');
        }
        return $params;
    }
}

if (!function_exists('uniqIdInt')) {
    /**
     * Generate numeric uniqid
     **/
    function uniqidInt()
    {
        return (int)str_replace('.', '', microtime(true));
    }
}

if (!function_exists('splitName')) {
    /**
     * Generate numeric uniqid
     * 
     * @param string $name Name to split
     **/
    function splitName(string $name)
    {
        $parts = [];
        $ret = [];

        while (strlen(trim($name)) > 0) {
            $name = trim($name);
            $string = preg_replace('#.*\s([\w-]*)$#', '$1', $name);
            $parts[] = $string;
            $name = trim(preg_replace('#' . $string . '#', '', $name));
        }

        if (!empty($parts)) {
            $parts = array_reverse($parts);
            $ret['firstName'] = $parts[0];
            $ret['middleName'] = (isset($parts[2])) ? $parts[1] : '';
            $ret['lastName'] = (isset($parts[2])) ? $parts[2] : (isset($parts[1]) ? $parts[1] : '');
        }

        return $ret;
    }
}

if (!function_exists('tinker')) {
    function tinker(...$args)
    {
        // Because there is no way of knowing what variable names
        // the caller of this function used with the php run-time,
        // we have to get clever. My solution is to peek at the
        // stack trace, open up the file that called "tinker()"
        // and parse out any variable names, so I can load
        // them in the tinker shell and preserve their
        // names.

        $namedParams = collect(debug_backtrace())
            ->where('function', 'tinker')
            ->map(function ($slice) {
                return array_values($slice);
            })
            ->mapSpread(function ($filePath, $lineNumber, $function, $args) {
                return file($filePath)[$lineNumber - 1];
                // "    tinker($post, new User);"
            })->map(function ($carry) {
                return Str::before(Str::after($carry, 'tinker('), ');');
                // "$post, new User"
            })->flatMap(function ($carry) {
                return array_map('trim', explode(',', $carry));
                // ["post", "new User"]
            })->map(function ($carry, $index) {
                return strpos($carry, '$') === 0
                    ? Str::after($carry, '$')
                    : 'temp' . $index;
                // ["post", "temp1"]
            })
            ->combine($args)->all();
        // ["post" => $args[0], "temp1" => $args[1]]

        echo PHP_EOL;
        $sh = new \Psy\Shell();
        $sh->setScopeVariables($namedParams);
        if ($sh->has('ls')) {
            $sh->addInput('ls', true);
        }
        $sh->run();
    }
}

if (!function_exists('arrayKeysKabobToSnake')) {
    function arrayKeysKabobToSnake($arr)
    {
        $ret = collect($arr)->mapWithKeys(function($item, $key) {
            if (is_array($item)) {
                $item = arrayKeysKabobToSnake($item);
            }

            return [str_replace('-', '_', $key) => $item];
        });

        return $ret->toArray();
    }
}

if (!function_exists('passwordHelpText')) {
    function passwordHelpText()
    {
        $passwordSettings = json_decode(\App\Account::first()->password_settings);

        $msg = 'Passwords must be at minimum ' . $passwordSettings->password_length . ' characters long and must include at least';

        if ($passwordSettings->upper_case) {
            $msg .= ' 1 Uppercase,';
        }
        if ($passwordSettings->lower_case) {
            $msg .= ' 1 Lowercase,';
        }
        if ($passwordSettings->numbers) {
            $msg .= ' 1 Numeric,';
        }
        if ($passwordSettings->special_char) {
            $msg .= ' 1 special character,';
        }
        $msg = substr_replace($msg, ".", -1);
        $msg .= ' Password cannot contain words in name or email.';

        return $msg;
    }
}
