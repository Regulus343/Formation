Formation
=========

**A powerful form creation and form data saving composer package for Laravel 5.**

Formation makes it really easy to build a form with form building methods that automatically:

- populate forms with data (both default values and data provided by the POST array)
- add an `error` class to labels and form fields
- add IDs to form fields based on their names and add matching `for` attributes to the fields' labels
- provides the ability to validate specific arrays in the POST array as well as the entire form
- set up by default to use Twitter Bootstrap 3 classes for form elements but can be easily customized for other CSS frameworks
- allows use of combination of PHP and JS to allow automatic creation of Handlebars JS form templates

All of this can be achieved with a minimal amount of code:

	{!! Form::field('first_name') !!}

	{!! Form::field('password') !!}

	{!! Form::field('user.item', 'select', [
		'options' => prep_options(Item::all(), ['id', 'name'])
	]) !!}

Simply echoing `Form::field('first_name')` is the same as the following code:

```html
<div class="form-group" id="field-first-name-area">
	{!! Form::label('first_name') !!}

	{!! Form::text('first_name') !!}

	{!! Form::error('first_name') !!}
</div>
```

The above code may produce the following markup:

```html
<div class="form-group has-error" id="field-first-name-area">
	<label for="field-first-name" class="control-label has-error"><span class="access">F</span>irst Name</label>

	<input type="text" name="first_name" id="field-first-name" class="form-control has-error" placeholder="First Name" accesskey="f" value="Joe" />

	<div class="error">The First Name field is required.</div>
</div>
```

The above code is an example of how simple and versatile Formation is. The top 3 fields make use of Formation's simplified `field()` method, the middle section shows the long way to achieve the same markup as the first two text fields, and the final section shows the markup that may be produced from the above two examples (assuming a "required" form validation rule has been set for the "first_name" field and the form has been submitted). You may notice that the markup is quite comprehensive and complete. Accesskeys are automatically employed (unless you specify otherwise) and an "access" class is applied to the accesskey letter in the label. The label, field, and possibly error are all wrapped in a div tag with a Twitter Bootstrap "form-group" class. The IDs are based on the names but use hyphens instead of underscores and the labels are automatically created from the names as well (but can, again, be specified manually). All of the fields will be automatically repopulated when form data is posted to the page. The classes for fields, labels, errors, containers, and ID / class prefixes are customizable in the config file.

```html
<input name="user[name]" value="" />
<input name="user[email]" value="" />

<input name="other_field" value="" />
```

With this form, we can validate just the fields in the user array with `Form::isValid('user')`, the final field with `Form::isValid('root')`, or all of the fields in the form with `Form::isValid()`.

- [Installation](#installation)
- [Opening a Form](#opening-form)
- [Default Form Values](#default-values)
- [Validation Rules](#validation-rules)
- [Labels](#labels)
- [Text, Text Area, Password & Hidden Fields](#basic-fields)
- [File Input](#labels)
- [Checkboxes and Radio Buttons](#checkbox-radio)
- [Checkbox and Radio Button Sets](#checkbox-radio-sets)
- [Drop-Down Lists](#drop-down-lists)
- [File Input](#file-input)
- [Buttons](#buttons)
- [Field Method](#field-method)
- [Integrating Handlebars JS Templates](#js-templates)
- [Base Model and Trait](#model-trait)

> **Note:** All input data displayed within form elements are filtered through the entities method.

<a name="installation"></a>
## Installation

To install Formation, make sure `regulus/formation` has been added to Laravel 5's `composer.json` file.

	"require": {
		"regulus/formation": "1.2.*"
	},

Then run `php composer.phar update` from the command line. Composer will install the Formation package. Now, all you have to do is register the service provider and set up Formation's alias in `config/app.php`. Add this to the `providers` array:

	Regulus\Formation\FormationServiceProvider::class,

And add this to the `aliases` array:

	'Form' => Regulus\Formation\Facade::class,

You may use 'Formation', or another alias, but 'Form' is recommended for the sake of simplicity. Formation is now ready to go.

<a name="opening-form"></a>
## Opening a Form

**Opening a form to POST to the current URL:**

	{!! Form::open() !!}

**Opening a form using a given URI and request method:**

	{!! Form::open(['url' => user/profile']) !!}

**Opening a form that accepts file uploads:**

	{!! Form::openForFiles(['url' => 'user/profile', 'files' => true]) !!}

**Opening a form for a resource controller:**

	{!! Form::openResource() !!}

> **Note:** This method automatically creates the correct route for the form action, assuming a resource controller is being used.

**Closing a form:**

	{!! Form::close() !!}

<a name="default-values"></a>
## Default Form Values

One of the most useful features of Formation is its ability to take an array, object, or Eloquent model and use it to populate form fields automatically. When the form is posted, it will automatically make use of the values in the POST array instead.

```php
$defaults = [
	'name'  => 'Cody Jassman',
	'email' => 'me@codyjassman.com',
];

Form::setDefaults($defaults);
```

> **Note:** If you want to use array fields names instead, use, for example, `user.name` and `user.email` instead of `name` and `email`.

**Forcing default values even after form POST:**

	Form::resetDefaults();

<a name="validation-rules"></a>
## Validation Rules

Formation makes use Laravel's Validator class. Using `Form::setValidation()` will create an instance of the Validator class (or many instances if array field names are used in the form setup). The reason the form's validation rules are passed through Formation to Validator is because Formation automatically adds an "error" class to the label and form field if an error is triggered. To do this, Formation needs a copy of the validation rules that have been set.

```php
$rules = [
	'user.name' => ['required'], // 'user.name' can be used for an array field like "user[name]"
	'email'     => ['required', 'email']
];

Form::setValidationRules($rules);
```

**Validating all fields:**

```php
if (Form::isValid())
{
	return true;
}
```

**Validating fields in an array:**

```php
if (Form::isValid('user')) // validates array fields with names like "user[name]" and "user[email]"
{
	return true;
}

if (Form::isValid('user.')) // ending with a "." allows us to validate fields like "user[0][name]" and "user[1][name]"
{
	return true;
}
```

The last example can be used when you have many instances of an array field like `user` above. That example will validate all sub fields of `Input::get('user')`. Ending your name with a period lets Formation know that `user` is an array that may contain many sets of the same sub fields.

<a name="labels"></a>
## Labels

**Setting up labels with an array:**

```php
$labels = [
	'name'  =>  'Name',
	'email' => 'Email Address',
];

Form::setLabels($labels);
```

By setting up your labels with an array, you will be able to leave the second argument `null` in `Form::label()`.

**Generating a label element:**

	{!! Form::label('email', 'Email Address') !!}

If you do not pass a label for the second argument, it will be checked for in Formation's `$labels` array that can be set with `Form::setLabels()`. If it is not found here, it will be inferred from the field name in the first argument.

**Specifying extra HTML attributes for a label:**

	{!! Form::label('email', 'Email Address', ['class' => 'email']) !!}

> **Note:** After creating a label, any form element you create with a name matching the label name will automatically receive an ID matching the label name as well.

<a name="basic-fields"></a>
## Text, Text Area, Password & Hidden Fields

**Generate a text input element:**

	{!! Form::text('username') !!}

**Specifying a default value for a text input element:**

	{!! Form::text('email', ['value' => 'example@gmail.com']) !!}

**Setting attributes for a text field:**

	{!! Form::text('user.first_name', ['class' => 'short']) !!}

By using `Form::setDefaults()`, you will not need to pass a default value and can instead pass a `null` value or none at all as the second argument to let the field take advantage of the preset default value. When a form is posted, the values in the POST array will be used instead unless `Form::resetDefaults()` is used.

> **Note:** A field with a name attribute of `first_name` is automatically given an ID of `first-name`. Underscores in names are always replaced with dashes in IDs.

**Naming a text field with an unspecified array index while retaining a unique ID:**

	{!! Form::text('user.(0).username') !!}

The above example will create a text field with a name of `user[][username]` and an ID of `user-0-username`. If you wish to explicitly specify the index for the name, simply leave out the round brackets:

	{!! Form::text('user.0.username') !!}

**Generating a password input element:**

	{!! Form::password('password') !!}

<a name="checkbox-radio"></a>
## Checkboxes and Radio Buttons

**Generating a checkbox input element:**

	{!! Form::checkbox('name', ['value' => 'X']) !!}

**Generating a checkbox that is checked by default:**

	{!! Form::checkbox('name', ['checked' => true]) !!}

Please keep in mind that once again you will not need the third argument if you set up your default values with `Form::setDefaults()`.

> **Note:** The radio method has the same signature as the checkbox method. Two for one!

<a name="checkbox-radio-sets"></a>
## Checkbox and Radio Button Sets

**Creating a set of checkboxes:**

```php
<?php $checkboxes = simple_options(['Rain', 'Thunder', 'Lightning']); ?>
```

	{!! Form::checkboxSet($checkboxes) !!}

**Adding a prefix to the name of each checkbox:**

	{!! Form::checkboxSet($checkboxes, 'checkbox') !!}

**Adding attributes to checkboxes and/or unordered list container for checkboxes:

	{!! Form::checkboxSet($checkboxes, null, ['class' => 'weather', 'id-container' => 'checkbox-set-weather']) !!}

> **Note:** Attributes ending with "-container" will be added to the container itself rather than to each of the checkboxes.

**Creating a set of radio buttons:**

	{!! Form::radioSet('weather', simple_options(['Rain', 'Thunder', 'Lightning'])) !!}

> **Note:** The `simple_options()` method is just used in the above example to have the radio buttons' labels used also as the actual form field values instead of using the numerical indexes of the array items. `simple_options()` and some other methods for building options are further described in the upcoming **Drop-Down Lists** section of the documentation.

You may append "-container" to attribute names to assign them to the container element for radio button sets as well. The default container classes for radio buttons and checkboxes are `radio-set` and `checkbox-set`. The containers are unordered list elements and each item in the set is a list item in the list.

<a name="drop-down-lists"></a>
## Drop-Down Lists

**Generating a drop-down list from an array of items:**

	{!! Form::select('size', ['L' => 'Large', 'S' => 'Small']) !!}

**Using a label with a null value as the first option in the list:**

	{!! Form::select('size', ['L' => 'Large', 'S' => 'Small'], ['null-option' => Select a Size']) !!}

**Generating a drop-down list with an option selected by default:**

```php
<?php // you may pass either a "selected" or "value" attribute to select an option ?>
```

	{!! Form::select('size', ['L' => 'Large', 'S' => 'Small'], ['null-option' => 'Select a Size', 'value' => 'S') !!}

	{!! Form::select('size', ['L' => 'Large', 'S' => 'Small'], ['null-option' => 'Select a Size', 'selected' => 'S') !!}

Of course, you may use `Form::setDefaults()` to populate select boxes without the need for the third `selected` or `value` attribute.

**Turn an array, object, or Eloquent collection into a set of options:**

	$users = DB::table('users')->orderBy('username')->get();
	{!! Form::select('user', prep_options($users, ['id', 'username']), 'Select a User') !!}

**Turn a simple array into an options array with values the same as its labels:**

	{!! Form::select('animal', simple_options(['Tiger', 'Zebra', 'Elephant']), ['null-option' => Select an Animal']) !!}

**Turn a simple array into a simple options array with numeric values that start at one instead of zero:**

	{!! Form::select('animal', offset_options(['Tiger', 'Zebra', 'Elephant']), ['null-option' => Select an Animal']) !!}

**Turn a simple array into a simple options array with numeric values that start at one instead of zero:**

```php
<?php // display options from 0 to 180 incrementing by 10 each time ?>
```

	{!! Form::select('number', number_options(0, 180, 10)) !!}

The first argument is the starting number, the second is the ending number, and the third is the number to iterate by. If it is negative, you may count down instead of up. Finally, the fourth argument is used to denote the number of decimal places the numbers should have.

**Create an options array of months:**

```php
<?php // count up 12 months from current month ?>
```

	{!! Form::select('month', month_options('current', 12)) !!}

```php
<?php // count down 12 months from current month ?>
```

	{!! Form::select('month', month_options(true, -12)) !!}

```php
<?php // count up to a specific month from current month ?>
```

	{!! Form::select('month', month_options(true, '2013-04-08')) !!}

```php
<?php // count down to a specific month from another specific month ?>
```

	{!! Form::select('month', month_options('2013-11-11', '2013-04-08')) !!}

```php
<?php // count down 12 months from current month and use the last day of the month for "month_end" field ?>
```

	{!! Form::select('month_start', month_options(true, -12, false, 'M Y')) !!}
	{!! Form::select('month_end', month_options(true, -12, true, 'M Y')) !!}

The first argument is your start month. You can use `true`, `false`, `null`, or "current" to use the current month. The second argument can have a positive or negative integer to count up or down a specific number of months, or it can have a date string to count up or down months until a specific date. If the third argument, `endDate`, is set to true, the dates used for the options values will use the last day of the month instead of the first. Lastly, you can specify a date format for the options label as your fourth argument. The default is "F Y".

> **Note:** All of the above options array building functions can also be used for checkbox sets and radio button sets and are helper functions that wrap core Formation functions. For example, `prep_options()` uses `Form::prepOptions()`.

**Using field macro for a set of radio buttons:**

```php
<?php $options = simple_options(['T-Rex', 'Parasaurolophus', 'Triceratops']); ?>
```

	{!! Form::field('dinosaur', 'radio-set', ['label' => 'Favorite Dinosaur', 'options' => $options]) !!}

<a name="file-input"></a>
## File Input

**Generate a file input element:**

	{!! Form::file('image') !!}

<a name="buttons"></a>
## Buttons

**Generating a submit button element:**

	{!! Form::submit('Click Me!') !!}

If you do not set the first argument, "Submit" will be used as the label.

> **Note:** Need to create a button element? Try the button method. It has the same signature as submit.

<a name="field-method"></a>
## Field Method

You may use the built-in `Form::field()` method to turn this:

```html
<div class="form-group" id="user-email-area">
	{!! Form::label('user.email') !!}

	{!! Form::text('user.email') !!}

	{!! Form::error('user.email') !!}
</div>
```

Into this:

	{!! Form::field('user.email') !!}

The field container element can be changed from a div to another HTML element and the "form-group" class can be changed as well in `config.php`. If you prefer not to use a field container at all, you may use the following:

	{!! Form::field('name', 'text', ['field-container' => false]) !!}

**Using field method for a drop-down select box:**

	{!! Form::field('animal', 'select', [
		'options' => simple_options(['Tiger', 'Zebra', 'Elephant'])
	]) !!}

**Using field method for a set of radio buttons:**

```php
<?php $options = simple_options(['T-Rex', 'Parasaurolophus', 'Triceratops']); ?>
```

	{!! Form::field('dinosaur', 'radio-set', ['label' => 'Favorite Dinosaur', 'options' => $options]) !!}

**Using field method for a set of checkboxes:**

	{!! Form::field('number.', 'checkbox-set', [
		'options' => offset_options(['One', 'II', '3.0'])
	]) !!}

You will notice that the third parameter, `attributes`, has some options for special attributes such as `label` and `options` that don't work like any other attribute declaration. The combination of these into the attributes array makes sense because of the generic, many-use nature of the field method. This prevents simple fields from requiring a bunch of `null` parameters. In addition to `label` and `options`, you can use `null-option` for a prepended null option for a select box. Lastly, `value` can be used to manually set the value of the field. This is unnecessary if you are using the `setDefaults()` or `setup` methods to pre-populate your form with data.

```php
$attributes = [
	'class'       => 'select-number',
	'options'     => number_options(1, 10),
	'null-option' => 'Select a Number',
	'value'       => 3,
];
```
	{!! Form::field('number', 'select', $attributes) !!}

> **Note:** Attributes ending with "-container" will be added to the container itself rather than to the field. Attributes ending with "-label" will be added to the field's label.

<a name="js-templates"></a>
## Integrating Handlebars JS Templates

**Loading and initializing formation.js:**

	@include('formation::load_js')

This view automatically loads the `formation.js` script and executes the following javascript code:

```js
Formation.setErrorSettings($.parseJSON('{{ Form::getJsonErrorSettings() }}'));
Formation.setErrors($.parseJSON('{{ Form::getJsonErrors() }}'));
```

This will automatically pass Formation's error settings and any Formation errors to the `formation.js` library.

**Load a Handlebars JS template with automatically populated fields and error displaying:**

```js
var exampleTemplateCallback = function(item, data) // example callback function for each template item created
{
	item.hide().fadeIn();
};
```

	Formation.loadTemplates('#example-items', $.parseJSON('{{ Form::getJsonValues('example_items') }}'), exampleTemplateCallback);

Here is a simple container element and example template that can be used in conjunction with the `loadTemplates()` method:

```html
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
```

**Populate Errors for AJAX Forms:**

```js
Formation.populateErrors(response.data.errors, form);
```

<a name="model-trait"></a>
## Base Model and Trait

Formation includes a base model which simply extends the Eloquent `Model` class and uses a trait which contains many advanced model features. You may extend the `Base` model directly from your model (`Regulus\Formation\Models\Base`) or just use the `Extended` trait (`Regulus\Formation\Traits\Extended`) in an existing model.

**Types:**

You may add a `protected static $types` array to your model to allow data to be automatically formatted before being used to populate a form or before saving data to the database. The following types can be used:

- checkbox
- date
- date-time
- datetime
- timestamp
- date-not-null
- date-time-not-null
- timestamp-not-null
- slug
- unique-slug

> **Note:** You may refer to the `Regulus\Formation\Traits\Extended` trait for examples of this and all of the other assisting arrays. The trait contains commented out array examples for each of them.

**Formats:**

You may add `protected static $formats` and `protected static $formatsForDb` arrays to your model for some additional automatic data formatting prior to populating forms or data saving. The following formats can be used:

- false-if-null
- true-if-null
- false-if-not-null
- true-if-not-null
- false-if-blank
- true-if-blank
- null-if-blank
- false-if-not-blank
- true-if-not-blank
- null-if-not-blank
- json
- json-or-null
- trim
- uppercase-first
- uppercase-words
- uppercase
- lowercase

The formatting from `$types` and `$formats` will occur automatically before saving into the database when using the `saveData()` method. You may also run them by using the model or trait's own `setDefaults()` method. Alternately, you can use `getFormattedValues()` to get an array of formatted values.

**Array-Included Methods:**

You may add a `protected static $arrayIncludedMethods` array to your model to specify methods that you would like to include in the model when it is run through `toArray()` or `toJson()`. This can be very useful when using a front-end JS framework such as Vue. Laravel's Eloquent model system already contains the `appends` array for this purpose, but this is a more versatile approach as it allows you to pass parameters and name the field whatever you like:

```php
protected static $arrayIncludedMethods = [
	'name' => 'getName(true)',
	'url'  => 'getUrl', // parameters not required
];
```

**Attribute Sets:**

You may add a `protected static $attributeSets` array to your model to define specific sets of attributes to be returned using `toArray()` or `toJson()`, which can be obtained using `getAttributeSet()` and the name (the key in your array) of the set you would like to retrieve. This allows you to define different sets of attributes for different purposes You may also reference sets within related content. Here is a full example. Let's suppose we have a model called `Post` which belongs to a `User` using an `author()` relationship and the model has the following attribute sets:

```php
	protected static $attributeSets = [
		'standard' => [
			'id',
			'content',
			'url', // this could even be a key listed in $arrayIncludedMethods for a getUrl() method
		],
	];

	protected static $relatedAttributeSets = [
		'standard' => [
			'author'  => 'set:author', // this will use an attribute set from the model used for the "author" relationship
			'section' => 'class:'.Section::class, // this will look for an attribute set called "standard"
			'tags'    => 'class:'.Tag::class.';set', // this will look for an attribute set called "set"
		],
	];
```

In our user model, we have the following array-included method and attribute set defined:

```php
	protected static $arrayIncludedMethods = [
		'name' => 'getName',
	];

	protected static $attributeSets = [
		'author' => [
			'id',
			'username',
			'name',
		],
	];
```

Now, we may query our `Post` model with its `author` relationship and return it as JSON data:

```php
	$post = Post::select(Post::getAttributeSet('standard'))
		->with('author')
		->limitRelatedData('standard')
		->first();

	return $post->toJson();
```

> **Note:** Our example above doesn't really require the `standard` parameters as they are the assumed defaults for each of their respective functions.

This will allow us to drastically reduce the amount of data returned so that we may obtain just the data we need and nothing more:

```js
	{
		"id":343,
		"content":"Taxation is theft, purely and simply even though it is theft on a grand and colossal scale which no acknowledged criminals could hope to match. It is a compulsory seizure of the property of the State's inhabitants, or subjects.",
		"created_at":"1973-03-02 00:00:00",
		"author":{
			"id":1,
			"username":"ForANewLiberty",
			"name":"Murray Rothbard"
		}
	}
```