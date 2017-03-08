@extends('vendor.formation.layout')

@section('content')

	<h1 class="display-3">Formation Examples</h1>

	<p>Below, you will find functional examples of what can be done with Formation. Be sure to open the view you published as <em>resources/views/vendor/formation/examples.blade.php</em> if you want to see the use of the Formation functions directly.</p>

	<h3 id="form-elements">Form Elements</h3>

	{!! Form::hidden('hidden_field', 'Hidden Field Value!') !!}

	{!! Form::hidden('another_hidden_field', ['value' => 'Another Hidden Field Value!']) !!}

	<fieldset id="form-elements-text">
		<legend>Texts / Textareas / Passwords</legend>

		<div class="row">
			<div class="col-md-6">
				{!! Form::field('text') !!}

				<pre><code>&#123;!! Form::field('text') !!&#125;</code></pre>

				<div class="item-info">
					<em>Form::field()</em> wraps <em>Form::text()</em> here but adds a label (and potentially an error div).
				</div>
			</div>

			<div class="col-md-6">
				<div class="form-group">
					{!! Form::label('more_text', 'Another Text Input!') !!}

					{!! Form::text('more_text', ['placeholder' => 'Placeholder Text!']) !!}

					{!! Form::error('more_text') !!}
				</div>

				<pre><code>&lt;div class="form-group"&gt;
	&#123;!! Form::label('more_text', 'Another Text Input!') !!&#125;

	&#123;!! Form::text('more_text', [
		'placeholder' => 'Placeholder Text!',
	]) !!&#125;

	&#123;!! Form::error('more_text') !!&#125;
&lt;/div&gt;
</code>
</pre>

				<div class="item-info">
					Here is the example that includes the whole <em>Form::field()</em> setup as individual elements. The broken out version that uses multiple functions can be useful if you need more customization than Form::field() offers, though Form::field() is quite versatile itself.
				</div>
			</div>
		</div>

		<div class="row">
			<div class="col-md-6">
				{!! Form::field('textarea', 'textarea', ['label' => 'Textarea Example', 'rows-field' => 4]) !!}

				<pre><code>&#123;!! Form::field('textarea', 'textarea', [
	'label'      => 'Textarea Example',
	'rows-field' => 4,
]) !!&#125;</code></pre>

				<div class="item-info">
					Because <em>Form::field()</em> contains a container element and a label, you can add the "<em>-field</em>" suffix to any attribute to make sure it gets added to the field element only and not the container or label. Likewise, "<em>-field-container</em>" and "<em>-label</em>" suffixes are available. In this case, a "<em>rows</em>" attribute gets added to the field element.
				</div>
			</div>

			<div class="col-md-6">
				{!! Form::field('colored_textarea', 'textarea', [
					'placeholder'           => 'This one has colors!',
					'rows-field'            => 4,
					'class-field-container' => 'form-group-dark',
					'class-field'           => 'red-bordered',
				]) !!}

				<pre><code>&#123;!! Form::field('colored_textarea', 'textarea', [
	'placeholder'           => 'This one has colors!',
	'rows-field'            => 4,
	'class-field-container' => 'form-group-dark',
	'class-field'           => 'red-bordered',
]) !!&#125;</code></pre>

				<div class="item-info">
					You can add classes (or any other attributes) to individual elements using their attribute suffixes, "-field", "-field-container", and "-label".
				</div>
			</div>
		</div>

		<div class="row">
			<div class="col-md-6">
				{!! Form::field('password') !!}

				<pre><code>&#123;!! Form::field('password_confirmation') !!&#125;</code></pre>

				<div class="item-info">
					The second parameter (the input type) is assumed to be "<em>password</em>" if name starts with "<em>password</em>" such as "<em>password</em>" or "<em>password_confirmation</em>".
				</div>
			</div>

			<div class="col-md-6">
				{!! Form::field('password', 'password', ['label' => 'Confirm Password']) !!}

				<pre><code>&#123;!! Form::field('password_confirmation', 'password', [
	'label' => 'Confirm Password',
]) !!&#125;
</code>
</pre>

				<div class="item-info">
					This example shows the input type as the second parameter. Normally, the default type is "<em>text</em>".
				</div>
			</div>
		</div>
	</fieldset>

	<fieldset id="form-elements-select-checkbox-radio">
		<legend>Selects / Checkboxes / Radio Buttons</legend>

		<div class="row">
			<div class="col-md-6">
				{!! Form::field('select', 'select', [
					'options' => simple_options([
						'Some Option',
						'Another Option',
						'Option III',
					]),
				]) !!}

				<pre><code>&#123;!! Form::field('select', 'select', [
	'options' => simple_options([
		'Some Option',
		'Another Option',
		'Option III',
	]),
]) !!&#125;</code></pre>

				<div class="item-info">
					You can use <em>simple_options()</em> if you pass a non-associative array and would like the values to be the same as the labels.
				</div>
			</div>

			<div class="col-md-6">
				<div class="form-group">
					{!! Form::label('number_select') !!}

					{!! Form::select('number_select', number_options(5, 100, 5), ['null-option' => 'Select a Number']) !!}
				</div>

				<pre><code>&lt;div class="form-group"&gt;
	&#123;!! Form::label('another_select') !!&#125;

	&#123;!! Form::select('number_select', number_options(5, 100, 5), [
		'null-option' => 'Select a Number',
	]) !!&#125;

	&#123;!! Form::error('another_select') !!&#125;
&lt;/div&gt;</code></pre>

				<div class="item-info">
					If you use the separated functions, note that the second parameter of <em>Form::select()</em> is the options array. In this case we also passed a <em>null-option</em> attribute to set the null option. The <em>number_options()</em> function creates an options array of numbers. The first parameter is the starting number, the second is the ending number, and the third is how much to go up by for each option (defaults to 1). If the end number is lower than the start number, it will count down.
				</div>
			</div>
		</div>

		<div class="row">
			<div class="col-md-6">
				{!! Form::field('checkboxes', 'checkbox-set', [
					'options' => offset_options([
						'One',
						'#2',
						'III',
					]),
					'associative' => true,
				]) !!}

				<pre><code>&#123;!! Form::field('checkboxes', 'checkbox-set', [
	'options' => offset_options([
		'One',
		'#2',
		'III',
	]),
	'associative' => true,
]) !!&#125;</code></pre>

				<div class="item-info">
					You can create a whole set of checkboxes with checkbox sets. The <em>offset_options()</em> function allows you to use a simple array but start the value for the first item at 1 instead of 0. Because the keys of the options array are numeric, Formation will read it as a non-associative array by default. To force it to be associative (key = name, value = label), you may set an "<em>associative</em>" attribute to <em>true</em>. The first parameter, "<em>checkboxes</em>" in this case, is the prefix for the checkbox names. If you would like the keys to the array field to be explicitly set ("<em>checkboxes[One]</em>" instead of "<em>checkboxes[]</em>"), you can either append a "<em>.</em>" to the prefix like "<em>checkboxes.</em>" or set an "<em>explicit-keys</em>" attribute to <em>true</em>.
				</div>
			</div>

			<div class="col-md-6">
				<div class="form-group">
					{!! Form::checkboxSet(offset_options([
						'One',
						'#2',
						'III',
					]),
					[
						'name-prefix' => 'more_checkboxes.',
						'associative' => true,
					]) !!}
				</div>

				<pre><code>&lt;div class="form-group"&gt;
	&#123;!! Form::checkboxSet(offset_options([
		'One',
		'#2',
		'III',
	]),
	[
		'name-prefix' => 'more_checkboxes.',
		'associative' => true,
	]) !!&#125;
&lt;/div&gt;</code></pre>

				<div class="item-info">
					If you use <em>Form::checkboxSet()</em> directly instead of using the <em>Form::field()</em> wrapper function, the first parameter is the options array (because nothing else is necessarily required). The second parameter, then, is your attributes array. If you would like to set a name prefix like in the first example, simply set a "<em>name-prefix</em>" attribute. In this case, the "<em>.</em>" on the end is used to set "<em>explicit-keys</em>" so that the checkbox names have their keys explicitly defined like "<em>more_checkboxes[1]</em>"
				</div>
			</div>
		</div>

		<div class="row">
			<div class="col-md-6">
				{!! Form::field('radio_set', 'radio-set', [
					'options' => offset_options([
						'Ron Paul',
						'Murray Rothbard',
						'Tom Woods',
					]),
				]) !!}

				<pre><code>&#123;!! Form::field('checkboxes', 'radio-set', [
	'options' => prep_options(User::all(), ['id', 'getName']),
]) !!&#125;</code></pre>

				<div class="item-info">
					Radio button sets work very much like select fields. In this example, we use <em>prep_options()</em> to convert a Laravel collection to a useable options array. The first item in the array is the attribute you wish to use as the option value, the second is the attribute (or in this case model method) you would like to use as the option label.
				</div>
			</div>

			<div class="col-md-6">
				<div class="form-group">
					{!! Form::label('another_radio_set', 'Storm!') !!}

					{!! Form::radioSet('another_radio_set', [
						'thunder' => 'Thunder Storm',
						'snow'    => 'Snow Storm',
						'sand'    => 'Sand Storm',
					]) !!}

					{!! Form::error('another_radio_set') !!}
				</div>

				<pre><code>&lt;div class="form-group"&gt;
	&#123;!! Form::label('another_radio_set', 'Storm!') !!&#125;

	&#123;!! Form::radioSet('another_radio_set', [
		'thunder' => 'Thunder Storm',
		'snow'    => 'Snow Storm',
		'sand'    => 'Sand Storm',
	]) !!&#125;

	&#123;!! Form::error('another_radio_set') !!&#125;
&lt;/div&gt;</code></pre>

				<div class="item-info">
					If you use <em>Form::radioSet()</em>, the first parameter is the name of the field, the second is the options array, and the third is the attributes array.
				</div>
			</div>
			</div>
		</div>
	</fieldset>

@stop