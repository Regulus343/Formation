<?php

/*
|--------------------------------------------------------------------------
| Helper Functions
|--------------------------------------------------------------------------
|
| A number of helper functions for the various options methods.
|
*/

if (!function_exists('prep_options'))
{
	/**
	 * Prepare an options array from a set of records
	 * for a select field, checkbox set, or radio button set.
	 *
	 * @param  mixed   $records
	 * @param  mixed   $labelValueFields
	 * @return array
	 */
	function prep_options($records = [], $labelValueFields = [])
	{
		return \Regulus\Formation\Facade::prepOptions($records, $labelValueFields);
	}
}

if (!function_exists('simple_options'))
{
	/**
	 * Create an associative array from a simple array for a select field, checkbox set, or radio button set.
	 *
	 * @param  array   $options
	 * @return array
	 */
	function simple_options($options = [])
	{
		return \Regulus\Formation\Facade::simpleOptions($options);
	}
}

if (!function_exists('checkbox_options'))
{
	/**
	 * Create an associative array from a simple array for a checkbox set. The field name will be lowercased and underscored.
	 *
	 * @param  array   $options
	 * @return array
	 */
	function checkbox_options($options = [])
	{
		return \Regulus\Formation\Facade::checkboxOptions($options);
	}
}

if (!function_exists('offset_options'))
{
	/**
	 * Offset a simple array by 1 index to prevent any options from having an
	 * index (value) of 0 for a select field, checkbox set, or radio button set.
	 *
	 * @param  array   $options
	 * @return array
	 */
	function offset_options($options = [])
	{
		return \Regulus\Formation\Facade::offsetOptions($options);
	}
}

if (!function_exists('number_options'))
{
	/**
	 * Create an options array of numbers within a specified range
	 * for a select field, checkbox set, or radio button set.
	 *
	 * @param  integer $start
	 * @param  integer $end
	 * @param  integer $increment
	 * @param  integer $decimals
	 * @return array
	 */
	function number_options($start = 1, $end = 10, $increment = 1, $decimals = 0)
	{
		return \Regulus\Formation\Facade::numberOptions($start, $end, $increment, $decimals);
	}
}

if (!function_exists('time_options'))
{
	/**
	 * Get an options array of times.
	 *
	 * @param  string  $minutes
	 * @return array
	 */
	function time_options($minutes = 'half')
	{
		return \Regulus\Formation\Facade::timeOptions($minutes);
	}
}

if (!function_exists('month_options'))
{
	/**
	 * Create an options array of months. You may use an integer to go a number of months back from your start month
	 * or you may use a date to go back or forward to a specific date. If the end month is later than the start month,
	 * the select options will go from earliest to latest. If the end month is earlier than the start month, the select
	 * options will go from latest to earliest. If an integer is used as the end month, use a negative number to go back
	 * from the start month. Setting $endDate to true will use the last day of the month instead of the first day.
	 *
	 * @param  mixed   $start
	 * @param  mixed   $end
	 * @param  boolean $endDate
	 * @param  string  $format
	 * @return array
	 */
	function month_options($start = 'current', $end = -12, $endDate = false, $format = 'F Y')
	{
		return \Regulus\Formation\Facade::monthOptions($start, $end, $endDate, $format);
	}
}

if (!function_exists('boolean_options'))
{
	/**
	 * Create a set of boolean options (Yes/No, On/Off, Up/Down...)
	 * You may pass a string like "Yes/No" or an array with just two options.
	 *
	 * @param  mixed   $options
	 * @param  boolean $startWithOne
	 * @return array
	 */
	function boolean_options($options = ['Yes', 'No'], $startWithOne = true)
	{
		return \Regulus\Formation\Facade::booleanOptions($options, $startWithOne);
	}
}