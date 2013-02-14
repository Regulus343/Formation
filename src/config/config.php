<?php

return array(

	'encoding'            => 'UTF-8',

	'fieldContainer'      => 'div',        //the field container HTML element type (generally 'div' or 'li')
	'fieldContainerClass' => 'field',      //the class applied to the field container
	'fieldContainerClear' => true,         //add a div to the end of the field container with class "clear"

	'csrfToken'           => 'csrf_token', //the name of the CSRF token
	'autoCsrfToken'       => true,         //automatically add a CSRF token to forms

);