<?php

return array(

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
	'fieldContainer' => array(
		'element' => 'div',
		'class'   => 'form-group',
		'error'   => true,
		'clear'   => false,
	),

	'field' => array(
		'class'           => 'form-control',
		'idPrefix'        => 'field-',
		'autoLabel'       => true,
		'autoPlaceholder' => true,
	),

	'label' => array(
		'class'  => 'control-label',
		'suffix' => false,
	),

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
	'error' => array(
		'element'             => 'div',
		'elementClass'        => 'error',
		'class'               => 'has-error',
		'icon'                => 'remove-circle',
		'typeLabelTooltip'    => true,

		'typeLabelAttributes' => array(
			'data-toggle'    => 'tooltip',
			'data-placement' => 'top',
			'class'          => 'error-tooltip',
		),
	),

	/*
	|--------------------------------------------------------------------------
	| Automatic Button Icons
	|--------------------------------------------------------------------------
	|
	| Automatically add Glyphicon icons to buttons when using submitResource()
	| to create their labels.
	|
	*/
	'autoButtonIcon' => true,

	/*
	|--------------------------------------------------------------------------
	| CSRF Token
	|--------------------------------------------------------------------------
	|
	| The name of the Cross-Site Request Forgery token for forms. Setting
	| "Auto CSRF Token" will automatically add the CSRF token field to forms.
	|
	*/
	'csrfToken'     => 'csrf_token',
	'autoCsrfToken' => true,

	/*
	|--------------------------------------------------------------------------
	| Date Format
	|--------------------------------------------------------------------------
	|
	| The default date formats for populating fields.
	|
	*/
	'dateFormat'     => 'm/d/Y',
	'dateTimeFormat' => 'm/d/Y g:i A',

	/*
	|--------------------------------------------------------------------------
	| Pivot Timestamps
	|--------------------------------------------------------------------------
	|
	| Whether or not timestamps are present in the pivot tables. This is only
	| used if Formation's BaseModel is used to extend Eloquent models.
	|
	*/
	'pivotTimestamps' => true,

	/*
	|--------------------------------------------------------------------------
	| Encoding
	|--------------------------------------------------------------------------
	|
	| The encoding for form fields.
	|
	*/
	'encoding' => 'UTF-8',

);