<?php

return [

	/*
	|--------------------------------------------------------------------------
	| Field Containers, Fields, Labels and Errors: Elements and Classes
	|--------------------------------------------------------------------------
	|
	| The field container element, class, and clear setting which will append
	| a <div> tag with a class of "clear" to the end of the form elements
	| within the container.
	|
	*/
	'field_container' => [
		'element' => 'div',
		'class'   => 'form-group',
		'error'   => true,
		'clear'   => false,
	],

	'field' => [
		'class'                    => 'form-control',
		'id_prefix'                => 'field-',
		'auto_label'               => true,
		'auto_placeholder'         => true,
		'default_null_option'      => true,
		'null_option_add_ellipsis' => false,
	],

	'label' => [
		'class'  => 'control-label',
		'suffix' => false,
	],

	/*
	|--------------------------------------------------------------------------
	| Errors
	|--------------------------------------------------------------------------
	|
	| Choose whether the error type should be a title attribute in a label or
	| if it should be its own standalone element that appears after the field.
	| You may also set a glyphicon for the tooltip and attributes for the
	| tooltip's label. If the attribute values you would like to set already
	| exist for the label, the new ones will simply be appended to the
	| existing values.
	|
	*/
	'error' => [
		'element'               => 'div',
		'element_class'         => 'error',
		'class'                 => 'has-error',
		'icon'                  => 'exclamation-circle',
		'type_label_tooltip'    => true,

		'type_label_attributes' => [
			'data_toggle'    => 'tooltip',
			'data_placement' => 'top',
			'class'          => 'error-tooltip',
		],
	],

	/*
	|--------------------------------------------------------------------------
	| Icons
	|--------------------------------------------------------------------------
	|
	| Automatically add icons to buttons when using submitResource()
	| to create their labels. By default, Font Awesome is used, but if the
	| Elemental package is installed, you may configure it to use Glyphicons or
	| another icon set.
	|
	*/
	'auto_button_icon' => true,

	'icons' => [
		'create' => 'plus-circle',
		'update' => 'check-circle',
	],

	/*
	|--------------------------------------------------------------------------
	| CSRF Token
	|--------------------------------------------------------------------------
	|
	| The name of the Cross-Site Request Forgery token for forms. Setting
	| "Auto CSRF Token" will automatically add the CSRF token field to forms.
	|
	*/
	'csrf_token'      => 'csrf_token',
	'auto_csrf_token' => true,

	/*
	|--------------------------------------------------------------------------
	| Date & Date/Time Formats
	|--------------------------------------------------------------------------
	|
	| The default date formats for populating fields.
	|
	*/
	'format' => [
		'date'     => 'm/d/Y',
		'datetime' => 'm/d/Y g:i A',
	],

	/*
	|--------------------------------------------------------------------------
	| Auto Trim
	|--------------------------------------------------------------------------
	|
	| Whether or not to automatically trim field values before insertion into
	| database.
	|
	*/
	'auto_trim' => true,

	/*
	|--------------------------------------------------------------------------
	| Pivot Timestamps
	|--------------------------------------------------------------------------
	|
	| Whether or not timestamps are present in the pivot tables. This is only
	| used if Formation's BaseModel is used to extend Eloquent models.
	|
	*/
	'pivot_timestamps' => true,

	/*
	|--------------------------------------------------------------------------
	| Encoding
	|--------------------------------------------------------------------------
	|
	| The encoding for form fields.
	|
	*/
	'encoding' => 'UTF-8',

];
