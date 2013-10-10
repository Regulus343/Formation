<?php

return array(

	/*
	|--------------------------------------------------------------------------
	| Field Container and Field
	|--------------------------------------------------------------------------
	|
	| The field container element, class, and clear setting which will append
	| a <div> tag with a class of "clear" to the end of the form elements
	| within the container.
	|
	*/
	'fieldContainer'      => 'div',
	'fieldContainerClass' => 'form-group',
	'fieldContainerClear' => false,

	'fieldClass'          => 'form-control',

	/*
	|--------------------------------------------------------------------------
	| Automatic Field Labels
	|--------------------------------------------------------------------------
	|
	| Automatically create a label in field() method.
	|
	*/
	'autoFieldLabel' => true,

	/*
	|--------------------------------------------------------------------------
	| Automatic Field Placeholder
	|--------------------------------------------------------------------------
	|
	| Automatically set a "placeholder" attribute for the field according to
	| the name of the field's label.
	|
	*/
	'autoFieldPlaceholder' => true,

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
	| Label Suffix
	|--------------------------------------------------------------------------
	|
	| Set an automatic suffix for labels such as a colon (":"). If set, you may
	| disable for specific fields by passing a false "suffix" attribute to the
	| label() method or a false "suffix-label" attribute to the field macro.
	|
	*/
	'labelSuffix' => false,

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
	| Encoding
	|--------------------------------------------------------------------------
	|
	| The encoding for form fields.
	|
	*/
	'encoding' => 'UTF-8',

);