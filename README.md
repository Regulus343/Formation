Formation
=========

**A powerful form creation composer package for Laravel 4 built on top of Laravel 3's Form class.**

Formation makes it really easy to populate form fields with default values and to build a form with powerful form field building methods that automatically add an "error" class to labels and form fields and provides the ability to validate specific arrays in the POST array.

	<input name="user[name]" value="" />
	<input name="user[email]" value="" />

	<input name="other_field" value="" />

With this form, we can validate just the fields in the user array with `Form::validated('user')`, the final field with `Form::validated('root')`, or all of the fields in the form with `Form::validated()`.

This and many other features make Formation a useful addition to any Laravel 4 project that makes any reasonable use of forms and a great aid in transitioning a web application from Laravel 3 to Laravel 4.

> You may notice much of this documentation is borrowed from Taylor Otwell's Laravel 3 documentation. This is because Formation was built with Laravel 3's Form class as a starting point. If you are familiar with the Form class for Laravel 3, you will adapt to Formation very easily.

- [Installation](#installation)


- [Opening a Form](#opening-form)
- [CSRF Protection](#csrf-protection)
- [Labels](#labels)
- [Text, Text Area, Password & Hidden Fields](#basic-fields)
- [File Input](#labels)
- [Checkboxes and Radio Buttons](#checkbox-radio)
- [Drop-Down Lists](#drop-down)
- [Buttons](#buttons)
- [Custom Macros](#macros)

> **Note:** All input data displayed in form elements is filtered through the entities method.

<a name="installation"></a>
## Installation

To install Formation, make sure "aquanode/formation" has been added to Laravel 4's config.json file.

	"require": {
		"aquanode/formation": "dev-master"
	},

Then run `php composer.phar update` from the command line. Composer will install the Formation package. Now, all you have to do is register the service provider and set up Formation's alias in `app/config/app.php`. Add this to the `providers` array:

	'Aquanode\Formation\FormationServiceProvider',

And add this to the `aliases` array:

	'Form' => 'Aquanode\Formation\Formation',

You may use 'Formation', or another alias, but 'Form' is recommended for the sake of simplicity. Formation is now ready to go.

<a name="opening-form"></a>
## Opening a Form

**Opening a form to POST to the current URL:**

	echo Form::open();

**Opening a form using a given URI and request method:**

	echo Form::open('user/profile', 'PUT');

**Opening a Form that POSTS to an HTTPS URL:**

	echo Form::open_secure('user/profile');

**Specifying extra HTML attributes on a form open tag:**

	echo Form::open('user/profile', 'POST', array('class' => 'awesome'));

**Opening a form that accepts file uploads:**

	echo Form::open_for_files('users/profile');

**Opening a form that accepts file uploads and uses HTTPS:**

	echo Form::open_secure_for_files('users/profile');

**Closing a form:**

	echo Form::close();

<a name="csrf-protection"></a>
## CSRF Protection

Laravel provides an easy method of protecting your application from cross-site request forgeries. First, a random token is placed in your user's session. Don't sweat it, this is done automatically. Next, use the token method to generate a hidden form input field containing the random token on your form:

**Generating a hidden field containing the session's CSRF token:**

	echo Form::token();

**Attaching the CSRF filter to a route:**

	Route::post('profile', array('before' => 'csrf', function()
	{
    	//
	}));

**Retrieving the CSRF token string:**

	$token = Session::token();

> **Note:** You must specify a session driver before using the Laravel CSRF protection facilities. Please see the L4 docs for this.

<a name="labels"></a>
## Labels

**Generating a label element:**

	echo Form::label('email', 'Email Address');

If you do not pass a label for the second argument, it will be inferred from the field name in the first argument.

**Specifying extra HTML attributes for a label:**

	echo Form::label('email', 'E-Mail Address', array('class' => 'awesome'));

> **Note:** After creating a label, any form element you create with a name matching the label name will automatically receive an ID matching the label name as well.

<a name="basic-fields"></a>
## Text, Text Area, Password & Hidden Fields

**Generate a text input element:**

	echo Form::text('username');

**Explicitly specifying a default value for a text input element:**

	echo Form::text('email', 'example@gmail.com');

By using `Form::setDefaults()`, you will not need to pass a default value and can instead pass a `null` value or none at all as the second argument to let the field take advantage of the preset default value.

> **Note:** When a form is posted, the values in the POST array will be used instead unless `Form::resetDefaults()` is used.

**Generating a password input element:**

	echo Form::password('password');