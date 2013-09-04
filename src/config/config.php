<?php

return array(

	/*
	|--------------------------------------------------------------------------
	| Field Container
	|--------------------------------------------------------------------------
	|
	| The field container element, class, and clear setting which will append
	| a <div> tag with a class of "clear" to the end of the form elements
	| within the container.
	|
	*/
	'fieldContainer'      => 'div',
	'fieldContainerClass' => 'form-group',
	'fieldContainerClear' => true,

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