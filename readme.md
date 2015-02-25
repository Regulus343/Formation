Formation
=========

**A powerful form creation composer package for Laravel 4.**

Formation makes it really easy to build a form with form building methods that automatically:

- populate forms with data (both default values and data provided by the POST array)
- add an `error` class to labels and form fields
- add IDs to form fields based on their names and add matching `for` attributes to the fields' labels
- provides the ability to validate specific arrays in the POST array as well as the entire form
- set up by default to use Twitter Bootstrap 3 classes for form elements but can be easily customized for other CSS frameworks
- allows use of combination of PHP and JS to allow automatic creation of Handlebars JS form templates

All of this can be achieved with a minimal amount of code:

	<?php
	echo Form::field('first_name');
	echo Form::field('password');
	echo Form::field('user.item', 'select', [
		'options' => Form::prepOptions(Item::all(), ['id', 'name'])
	]);
	?>

	<?php //simply typing Form::field('first_name') is the same as the following code: ?>
	<div class="form-group" id="first-name-area">
		<?php echo Form::label('first_name');
		echo Form::text('first_name');
		echo Form::error('first_name'); ?>
	</div>

	<?php //which may produce the following markup: ?>
	<div class="form-group hass error" id="first-name-area">
		<label for="first-name" class="control-label has-error"><span class="access">F</span>irst Name</label>
		<input type="text" name="first_name" id="first-name" class="form-control has-error" placeholder="First Name" accesskey="f" value="" />
		<div class="error">The First Name field is required.</div>
	</div>

The above code is an example of how simple and versatile Formation is. The top 3 fields make use of Formation's simplified `field()` macro, the middle section shows the long way to achieve the same markup as the first two text fields, and the final section shows the markup that may be produced from the above two examples (assuming a "required" form validation rule has been set for the "first_name" field and the form has been submitted). You may notice that the markup is quite comprehensive and complete. Accesskeys are automatically employed (unless you specify otherwise) and an "access" class is applied to the accesskey letter in the label. The label, field, and possibly error are all wrapped in a div tag with a Twitter Bootstrap "form-group" class. The IDs are based on the names but use hyphens instead of underscores and the labels are automatically created from the names as well (but can, again, be specified manually). All of the fields will be automatically repopulated when form data is posted to the page.

	<input name="user[name]" value="" />
	<input name="user[email]" value="" />

	<input name="other_field" value="" />

With this form, we can validate just the fields in the user array with `Form::validated('user')`, the final field with `Form::validated('root')`, or all of the fields in the form with `Form::validated()`.

> You may notice much of this documentation is borrowed from Taylor Otwell's Laravel 3 documentation. This is because Formation was built with Laravel 3's Form class as a starting point. If you are familiar with the Form class for Laravel 3, you will adapt to Formation very easily.

- [Installation](#installation)
- [Opening a Form](#opening-form)
- [CSRF Protection](#csrf-protection)
- [Default Form Values](#default-values)
- [Validation Rules](#validation-rules)
- [Labels](#labels)
- [Full Array Setup for Labels, Validation Rules, and Default Values](#full-array-setup)
- [Text, Text Area, Password & Hidden Fields](#basic-fields)
- [File Input](#labels)
- [Checkboxes and Radio Buttons](#checkbox-radio)
- [Checkbox and Radio Button Sets](#checkbox-radio-sets)
- [Drop-Down Lists](#drop-down-lists)
- [File Input](#file-input)
- [Buttons](#buttons)
- [Integrating Handlebars JS Templates](#js-templates)
- [Field Macro](#field-macro)

> **Note:** All input data displayed in form elements is filtered through the entities method.

<a name="installation"></a>
## Installation

To install Formation, make sure `regulus/formation` has been added to Laravel 4's `composer.json` file.

	"require": {
		"regulus/formation": "0.9.3"
	},

Then run `php composer.phar update` from the command line. Composer will install the Formation package. Now, all you have to do is register the service provider and set up Formation's alias in `app/config/app.php`. Add this to the `providers` array:

	'Regulus\Formation\FormationServiceProvider',

And add this to the `aliases` array:

	'Form' => 'Regulus\Formation\Facade',

You may use 'Formation', or another alias, but 'Form' is recommended for the sake of simplicity. Formation is now ready to go.

<a name="opening-form"></a>
## Opening a Form

**Opening a form to POST to the current URL:**

	echo Form::open();

**Opening a form using a given URI and request method:**

	echo Form::open(['url' => user/profile']);

**Opening a form that accepts file uploads:**

	echo Form::openForFiles(['url' => 'user/profile', 'files' => true]);

**Opening a form for a resource controller:**

	echo Form::openResource();

> **Note:** This method automatically creates the correct route for the form action, assuming a resource controller is being used.

**Closing a form:**

	echo Form::close();

<a name="csrf-protection"></a>
## CSRF Protection

Laravel provides an easy method of protecting your application from cross-site request forgeries. First, a random token is placed in your user's session. Don't sweat it, this is done automatically. Next, use the token method to generate a hidden form input field containing the random token on your form:

**Generating a hidden field containing the session's CSRF token:**

	echo Form::token();

**Attaching the CSRF filter to a route:**

	Route::post('profile', ['before' => 'csrf', function()
	{
		//
	}]);

**Retrieving the CSRF token string:**

	$token = Session::token();

> **Note:** You must specify a session driver before using the Laravel CSRF protection facilities. Please see the L4 docs for this.

<a name="default-values"></a>
## Default Form Values

One of the most useful features of Formation is its ability to take an array, object, or Eloquent model and use it to populate form fields automatically. When the form is posted, it will automatically make use of the values in the POSt array instead.

	$defaults = [
		'name'  => 'Cody Jassman',
		'email' => 'me@codyjassman.com',
	];
	Form::setDefaults($defaults);

> **Note:** If you want to use array fields names instead, use, for example, `user.name` and `user.email` instead of `name` and `email`.

**Forcing default values even after form POST:**

	Form::resetDefaults();

<a name="validation-rules"></a>
## Validation Rules

Formation makes use Laravel 4's Validator class. Using `Form::setValidation()` will create an instance of the Validator class (or many instances if array field names are used in the form setup). The reason the form's validation rules are passed through Formation to Validator is because Formation automatically adds an "error" class to the label and form field if an error is triggered. To do this, Formation needs a copy of the validation rules that have been set.

	$rules = [
		'user.name' => ['required'], //'user.name' can be used for an array field like "user[name]"
		'email'     => ['required', 'email']
	];
	Form::setValidationRules($rules);

**Validating all fields:**

	if (Form::validated()) {
		return true;
	}

**Validating fields in an array:**

	if (Form::validated('user')) { //validates array fields with names like "user[name]" and "user[email]"
		return true;
	}

	if (Form::validated('user.')) { //ending with a "." allows us to validate fields like "user[0][name]" and "user[1][name]"
		return true;
	}

The last example can be used when you have many instances of an array field like `user` above. That example will validate all sub fields of `Input::get('user')`. Ending your name with a period lets Formation know that `user` is an array that may contain many sets of the same sub fields.

<a name="labels"></a>
## Labels

**Setting up labels with an array:**

	$labels = [
		'name'  =>  'Name',
		'email' => 'Email Address',
	];
	Form::setLabels($labels);

By setting up your labels with an array, you will be able to leave the second argument `null` in `Form::label()`.

**Generating a label element:**

	echo Form::label('email', 'Email Address');

If you do not pass a label for the second argument, it will be checked for in Formation's `$labels` array that can be set with `Form::setLabels()`. If it is not found here, it will be inferred from the field name in the first argument.

**Specifying extra HTML attributes for a label:**

	echo Form::label('email', 'E-Mail Address', ['class' => 'awesome']);

> **Note:** After creating a label, any form element you create with a name matching the label name will automatically receive an ID matching the label name as well.

<a name="full-array-setup"></a>
## Full Array Setup for Labels, Validation Rules, and Default Values

Setting up labels, validation rules, and default values all at once:

	$form = [
		'user.name'    => ['Name', 'required', 'Cody Jassman'],
		'user.website' => ['Website', '', 'http://'],
		'user.about'   => ['About You'],
		'user.number'  => ['Some Sort of Number'],
	];
	Form::setup($form);

<a name="basic-fields"></a>
## Text, Text Area, Password & Hidden Fields

**Generate a text input element:**

	echo Form::text('username');

**Specifying a default value for a text input element:**

	echo Form::text('email', ['value' => 'example@gmail.com']);

**Setting attributes for a text field:**

	echo Form::text('user.first_name', ['class' => 'short']);

By using `Form::setDefaults()`, you will not need to pass a default value and can instead pass a `null` value or none at all as the second argument to let the field take advantage of the preset default value. When a form is posted, the values in the POST array will be used instead unless `Form::resetDefaults()` is used.

> **Note:** A field with a name attribute of `first_name` is automatically given an ID of `first-name`. Underscores in names are always replaced with dashes in IDs.

**Naming a text field with an unspecified array index while retaining a unique ID:**

	echo Form::text('user.(0).username');

The above example will create a text field with a name of `user[][username]` and an ID of `user-0-username`. If you wish to explicitly specify the index for the name, simply leave out the round brackets:

	echo Form::text('user.0.username');

**Generating a password input element:**

	echo Form::password('password');

<a name="checkbox-radio"></a>
## Checkboxes and Radio Buttons

**Generating a checkbox input element:**

	echo Form::checkbox('name', ['value' => 'X']);

**Generating a checkbox that is checked by default:**

	echo Form::checkbox('name', ['checked' => true]);

Please keep in mind that once again you will not need the third argument if you set up your default values with `Form::setDefaults()`.

> **Note:** The radio method has the same signature as the checkbox method. Two for one!

<a name="checkbox-radio-sets"></a>
## Checkbox and Radio Button Sets

**Creating a set of checkboxes:**

	$checkboxes = Form::simpleOptions(['Rain', 'Thunder', 'Lightning']);
	echo Form::checkboxSet($checkboxes);

**Adding a prefix to the name of each checkbox:**

	echo Form::checkboxSet($checkboxes, 'checkbox');

**Adding attributes to checkboxes and/or unordered list container for checkboxes:

	echo Form::checkboxSet($checkboxes, null, ['class' => 'awesome', 'id-container' => 'checkbox-set-weather']);

> **Note:** Attributes ending with "-container" will be added to the container itself rather than to each of the checkboxes.

**Creating a set of radio buttons:**

	echo Form::radioSet('weather', Form::simpleOptions(['Rain', 'Thunder', 'Lightning']));

> **Note:** The `simpleOptions()` method is just used in the above example to have the radio buttons' labels used also as the actual form field values instead of using the numerical indexes of the array items. `simpleOptions()` and some other methods for building options are further described in the upcoming **Drop-Down Lists** section of the documentation.

You may append "-container" to attribute names to assign them to the container element for radio button sets as well. The default container classes for radio buttons and checkboxes are `radio-set` and `checkbox-set`. The containers are unordered list elements and each item in the set is a list item in the list.

<a name="drop-down-lists"></a>
## Drop-Down Lists

**Generating a drop-down list from an array of items:**

	echo Form::select('size', ['L' => 'Large', 'S' => 'Small']);

**Using a label with a null value as the first option in the list:**

	echo Form::select('size', ['L' => 'Large', 'S' => 'Small'], ['null-option' => Select a size']);

**Generating a drop-down list with an option selected by default:**

	//you may pass either a "selected" or "value" attribute to select an option
	echo Form::select('size', ['L' => 'Large', 'S' => 'Small'], ['null-option' => 'Select a size', 'value' => 'S');

	echo Form::select('size', ['L' => 'Large', 'S' => 'Small'], ['null-option' => 'Select a size', 'selected' => 'S');

Of course, you may use `Form::setDefaults()` to populate select boxes without the need for the third `selected` or `value` attribute.

**Turn an array, object, or Eloquent model into a set of options:**

	$users = DB::table('users')->orderBy('username')->get();
	echo Form::select('user', Form::prepOptions($users, ['id', 'username']), 'Select a user');

**Turn a simple array into an options array with values the same as its labels:**

	echo Form::select('animal', Form::simpleOptions(['Tiger', 'Zebra', 'Elephant']), ['null-option' => Select an animal']);

**Turn a simple array into a simple options array with numeric values that do start at one instead of zero:**

	echo Form::select('animal', Form::offsetOptions(['Tiger', 'Zebra', 'Elephant']), ['null-option' => Select an animal']);

**Turn a simple array into a simple options array with numeric values that start at one instead of zero:**

	//display options from 0 to 180 incrementing by 10 each time
	echo Form::select('number', Form::numberOptions(0, 180, 10));

The first argument is the starting number, the second is the ending number, and the third is the number to iterate by. If it is negative, you may count down instead of up. Finally, the fourth argument is used to denote the number of decimal places the numbers should have.

**Create an options array of months:**

	//count up 12 months from current month
	echo Form::select('month', Form::monthOptions('current', 12));

	//count down 12 months from current month
	echo Form::select('month', Form::monthOptions(true, -12));

	//count up to a specific month from current month
	echo Form::select('month', Form::monthOptions(true, '2013-04-08'));

	//count down to a specific month from another specific month
	echo Form::select('month', Form::monthOptions('2013-11-11', '2013-04-08'));

	//count down 12 months from current month and use the last day of the month for "month_end" field
	echo Form::select('month_start', Form::monthOptions(true, -12, false, 'M Y'));
	echo Form::select('month_end', Form::monthOptions(true, -12, true, 'M Y'));

The first argument is your start month. You can use `true`, `false`, `null`, or "current" to use the current month. The second argument can have a positive or negative integer to count up or down a specific number of months, or it can have a date string to count up or down months until a specific date. If the third argument, `endDate`, is set to true, the dates used for the options values will use the last day of the month instead of the first. Lastly, you can specify a date format for the options label as your fourth argument. The default is "F Y".

> **Note:** All of the above options array building functions can also be used for checkbox sets and radio buttons sets.

**Using field macro for a set of radio buttons:**

	$options = Form::simpleOptions(['T-Rex', 'Parasaurolophus', 'Triceratops']);
	echo Form::field('dinosaur', 'radio-set', ['label' => 'Favorite Dinosaur', 'options' => $options]);

<a name="file-input"></a>
## File Input

**Generate a file input element:**

	echo Form::file('image');

<a name="buttons"></a>
## Buttons

**Generating a submit button element:**

	echo Form::submit('Click Me!');

If you do not set the first argument, "Submit" will be used as the label.

> **Note:** Need to create a button element? Try the button method. It has the same signature as submit.

<a name="js-templates"></a>
## Integrating Handlebars JS Templates

**Loading and initializing formation.js:**

	@include('formation::load_js')

This view automatically loads the `formation.js` script and executes the following javascript code:

	Formation.setErrorSettings($.parseJSON('{{ Form::getJsonErrorSettings() }}'));
	Formation.setErrors($.parseJSON('{{ Form::getJsonErrors() }}'));

This will automatically pass Formation's error settings and any Formation errors to the `formation.js` library.

**Load a Handlebars JS template with automatically populated fields and error displaying:**

	var exampleTemplateCallback = function(item, data) { //example callback function for each template item created
		item.hide().fadeIn();
	};

	Formation.loadTemplates('#example-items', $.parseJSON('{{ Form::getJsonValues('example_items') }}'), exampleTemplateCallback);

Here is a simple container element and example template that can be used in conjunction with the `loadTemplates()` method:

	<div id="example-items" data-template-id="example-item-template"></div>

	<script id="example-item-template" type="text/x-handlebars-template">
		<fieldset id="example-item-{{number}}" data-item-number="{{number}}">
			<legend>Example Item</legend>

			<?=Form::hidden('example_items.{{number}}.id')?>

			<div class="row">
				<div class="col-md-12">
					<?=Form::field('example_items.{{number}}.title')?>
				</div>
			</div>
		</fieldset>
	</script>

> **Note:** The container element should have a `data-template-id` attribute and the item template should have a `data-item-number` attribute. If you are using the Blade templating engine, you should use `@include()` to load the template in another non-Blade file as Handlebars' `{{` and `}}` wrappers can conflict with Blade. To see an example of `loadTemplates()` in action, please refer to the Laravel 4 CMS which uses Formation, [Fractal](https://github.com/Regulus343/Fractal).

<a name="field-macro"></a>
## Field Macro

You may use the built-in `Form::field()` macro to turn this:

	<div class="form-group" id="user-email-area">
		<?php echo Form::label('user.email');
		echo Form::text('user.email');
		echo Form::error('user.email'); ?>
	</div>

Into this:

	echo Form::field('user.email');

The field container element can be changed from a div to another HTML element and the "form-group" class can be changed as well in `config.php`.

**Using field macro for a drop-down select box:**

	echo Form::field('animal', 'select', [
		'options' => Form::simpleOptions(['Tiger', 'Zebra', 'Elephant'])
	]);

**Using field macro for a set of radio buttons:**

	$options = Form::simpleOptions(['T-Rex', 'Parasaurolophus', 'Triceratops']);
	echo Form::field('dinosaur', 'radio-set', ['label' => 'Favorite Dinosaur', 'options' => $options]);

**Using field macro for a set of checkboxes:**

	echo Form::field('number.', 'checkbox-set', [
		'options' => Form::offsetOptions(['One', 'II', '3.0'])
	]);

You will notice that the third parameter, `attributes`, has some options for special attributes such as `label` and `options` that don't work like any other attribute declaration. The combination of these into the attributes array makes sense because of the generic, many-use nature of the field macro. This prevents simple fields from requiring a bunch of `null` parameters. In addition to `label` and `options`, you can use `nullOption` for a prepended null option for a select box. Lastly, `value` can be used to manually set the value of the field. This is unnecessary if you are using the `setDefaults()` or `setup` methods to pre-populate your form with data.

	$attributes = [
		'class'      => 'select-number',
		'options'    => Form::numberOptions(1, 10),
		'nullOption' => 'Select a number',
		'value'      => 3,
	];
	echo Form::field('number', 'select', $attributes);