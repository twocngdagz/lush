<?php

/**
 * Origin connector utility functions
 */

    if (!function_exists('appFeatures')) {
        function appFeatures($feature = '')
        {
            // We need to strip any leading and ending " or ' characters
            // as these are inserted in the "feature_available" custom
            // Blade directive.

            return Origin::supportedFeatures(str_replace(['"', "'"], "", $feature));
        }
    }

if (!function_exists('showProperties')) {
    function showProperties($property_list = null)
    {
        $should_show = appFeatures('global.multi-site') && \App\Property::count() > 1;
        if ($property_list) {
            return $should_show && collect($property_list)->isNotEmpty();
        }

        return $should_show;
    }
}

if (!function_exists('getActivePropertyRanks')) {
    /**
     * Get property ranks that have not been marked as excluded.
     *
     * @param null|int $property_id Property to retrieve rank exclusion list for (optional)
     * @return \Illuminate\Support\Collection
     */
    function getActivePropertyRanks()
    {
        $ranks = Origin::getPropertyRanks();

        $exclusions = App\Account::first()->rankExclusions()->pluck('ext_rank_id')->toArray();

        if (empty($exclusions)) {
            // by default we filter out anything named employee
            $ranks = $ranks->reject(function ($item) {
                return $item->name == 'Employee';
            });
        } else {
            // when configured we filter out everything on exclusion list
            $ranks = $ranks->reject(function ($item) use ($exclusions) {
                return in_array($item->id, $exclusions);
            }); 
        }

        return $ranks->values();
    }

    if (!function_exists('isCMSLush')) {
        function isCMSLush()
        {

            return env('ORIGIN_CONNECTOR_IDENTIFIER') === 'lush-cms-v1';
        }
    }
}
