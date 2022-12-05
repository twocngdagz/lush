<?php

/**
 * Conversion functions
 */

if (!function_exists('byte_to_bits')) {

	/**
	 * Convert an ASCII byte to binary bits
	 * @param  string $byte ASCII character
	 * @return string       binary bits
	 */
	function byte_to_bits($byte)
	{
		$hex    = unpack('H*', $byte, 0)[1]; // ASCII to hex
		$binary = base_convert($hex, 16, 2); // hex to binary

		return $binary;
	}

	/**
	 * Convert a 2 digit ISO country code to 3 digit
	 * @param  string $cca2 Two digit ISO country code
	 * @return string       Three digit ISO country code
	 */
	function cca2_to_cca3($cca2) {
		if ($cca2 == '') throw new \Exception('Cannot convert null country code from cca2 to cca3.');
        if (\Cache::has("cca2:cca3:{$cca2}")) {
            return \Cache::get("cca2:cca3:{$cca2}");
        } else {
        	$cca3 = App\Country::where('code', $cca2)->first()->cca3;
            \Cache::forever("cca2:cca3:{$cca2}", $cca3);
            return $cca3;
        }
	}

	/**
	 * Convert a 3 digit ISO country code to 2 digit
	 * @param  string $cca3 Three digit ISO country code
	 * @return string       Two digit ISO country code
	 */
	function cca3_to_cca2($cca3) {
		if ($cca3 == '') throw new \Exception('Cannot convert null country code from cca3 to cca2.');
        if (\Cache::has("cca3:cca2:{$cca3}")) {
            return \Cache::get("cca3:cca2:{$cca3}");
        } else {
        	$cca2 = App\Country::where('cca3', $cca3)->first()->code;
            \Cache::forever("cca3:cca2:{$cca3}", $cca2);
            return $cca2;
        }
	}

}
