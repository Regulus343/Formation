/*
|------------------------------------------------------------------------------
| Formation JS
|------------------------------------------------------------------------------
|
| Last Updated: February 9, 2016
|
*/

var Formation = {

	errorSettings: {
		element:             'div',
		elementClass:        'error',
		classAttribute:      'has-error',
		typeLabelTooltip:    true,
		typeLabelAttributes: {
			dataToggle:     'tooltip',
			dataPlacement:  'top',
			classAttribute: 'error-tooltip',
		},
	},

	field: {
		classAttribute:        'form-control',
		prefix:                'field-',
		autoLabel:             true,
		autoPlaceholder:       true,
		defaultNullOption:     true,
		nullOptionAddEllipsis: false,
	},

	errorCallback:       'Formation.defaultErrorCallback',

	errors:              null,
	errorsById:          null,

	templateItems:       0,
	templateItemsLoaded: 0,
	itemNumber:          0,
	itemContainer:       null,

	setUpRemoveButtons:  true,

	setErrorSettings: function(errorSettings)
	{
		this.errorSettings = {};

		$.extend(String.prototype,
		{
			camelize: function()
			{
				var string = this.replace (/(?:^|[-_])(\w)/g, function (_, c)
				{
					return c ? c.toUpperCase () : '';
				});

				return string.substr(0, 1).toLowerCase() + string.substr(1);
			}
		});

		this.errorSettings = this.setErrorSettingsObject(errorSettings);
	},

	setErrorSettingsObject: function(errorSettings)
	{
		var errorSettingsObject = {};

		for (e in errorSettings)
		{
			var errorSetting = errorSettings[e];

			var key = e.camelize(true);

			// correct reserved JS attributes
			if (key == "class")
				key += "Attribute";

			if (typeof errorSetting == "object")
				errorSettingsObject[key] = this.setErrorSettingsObject(errorSetting);
			else
				errorSettingsObject[key] = errorSetting;
		}

		return errorSettingsObject;
	},

	setErrorCallback: function (errorCallback)
	{
		this.errorCallback = errorCallback;
	},

	defaultErrorCallback: function(fieldContainer)
	{
		if (typeof fieldContainer != "undefined")
			fieldContainer.find('[data-toggle="tooltip"]').tooltip({html: true});
	},

	setErrors: function(errors)
	{
		this.errors     = errors;
		this.errorsById = [];

		for (e in errors)
		{
			this.errorsById[this.field.prefix + e.replace(/\_/g, '-').replace(/\./g, '-')] = errors[e];
		}
	},

	getErrors: function()
	{
		return this.errors;
	},

	getError: function(field)
	{
		if (this.errors === null)
			return false;

		return this.errors[field] !== undefined ? this.errors[field] : false;
	},

	getErrorsById: function()
	{
		return this.errorsById;
	},

	getErrorById: function(field)
	{
		if (this.errorsById === null)
			return false;

		return this.errorsById[field] !== undefined ? this.errorsById[field] : false;
	},

	camelCaseToDashed: function(string)
	{
		return string.replace(/([A-Z])/g, function($1) {
			return '-' + $1.toLowerCase();
		});
	},

	// used with Handlebars.js to load form template items populate fields
	loadTemplates: function(container, items, callbackFunction, finalCallbackFunction)
	{
		if (typeof container != "object")
			container = $(container);

		// require "data-template-id" attribute for container
		var templateId = container.attr('data-template-id');
		if (templateId == null) {
			this.log('Container requires "data-template-id" attribute.');
			return;
		}

		this.templateItems = 0;
		for (i in items)
		{
			if (items.hasOwnProperty(i))
				this.templateItems ++;
		}

		for (i in items)
		{
			var item = items[i];

			this.loadTemplate(container, item);

			this.executeFunction(callbackFunction, this.itemContainer, item);
		}

		this.executeFunction(finalCallbackFunction);
	},

	loadTemplate: function(container, item, callbackFunction)
	{
		if (typeof container != "object")
			container = $(container);

		// require "data-template-id" attribute for container
		var templateId = container.attr('data-template-id');
		if (templateId == null) {
			this.log('Container requires "data-template-id" attribute.');
			return;
		}

		// set i to an unused number
		var i = 0;
		container.find('[data-item-number]').each(function()
		{
			if (parseInt($(this).attr('data-item-number')) > i)
				i = parseInt($(this).attr('data-item-number'));
		});

		i ++;

		this.itemNumber  = i;

		// create item template
		var source   = $('#'+templateId).html();
		var template = Handlebars.compile(source);
		var context  = {};

		if (item !== undefined && item !== null)
			context = item;

		context.number = i;

		var html = template(context);

		// append item to container
		container.append(html);

		// select item
		this.itemContainer = container.find('[data-item-number="'+i+'"]');

		// populate fields and set errors for item based on data
		this.setFieldsForItem(item);

		// add template item to loaded total
		this.templateItemsLoaded ++;

		// set up remove buttons
		this.setUpRemoveButtonsForTemplate();

		// load template sub-items
		this.itemContainer.find('[data-template-id]').each(function()
		{
			var subItems = [];

			if (item !== null && $(this).attr('data-template-item-name') !== undefined && item[$(this).attr('data-template-item-name')] !== undefined)
				subItems = item[$(this).attr('data-template-item-name')];

			if (!subItems.length && !Object.keys(subItems).length && $(this).attr('data-template-default-items') !== undefined)
			{
				for (s = 0; s < parseInt($(this).attr('data-template-default-items')); s++) {
					subItems[s] = {};
				}
			}

			// set parent number
			if (subItems.length || Object.keys(subItems).length)
			{
				for (s in subItems) {
					subItems[s].parentNumber = i;
				}
			}

			Formation.loadTemplates($(this), subItems);
		});

		// trigger callback function if one is set
		if (callbackFunction !== undefined)
			this.executeFunction(callbackFunction, this.itemContainer, item);

		return i;
	},

	loadNewTemplate: function(container, callbackFunction)
	{
		if (typeof container != "object")
			container = $(container);

		var item = null;

		// set parent number if possible
		if (container.parents('[data-item-number]').length)
			item = {parentNumber: container.parents('[data-item-number]').attr('data-item-number')};

		return this.loadTemplate(container, item, callbackFunction);
	},

	getTemplateHtml: function(container, item)
	{
		// require "data-template-id" attribute for container
		var templateId = $(container).attr('data-template-id');
		if (templateId == null) {
			this.log('Container requires "data-template-id" attribute.');
			return;
		}

		// set i to an unused number
		var i = 0;
		$(container).find('[data-item-number]').each(function(){
			if ($(this).attr('data-item-number') > i)
				i = $(this).attr('data-item-number');
		});

		i ++;

		this.itemNumber  = i;

		// create item template
		var source   = $('#'+templateId).html();
		var template = Handlebars.compile(source);
		var context  = {};

		if (item !== undefined && item !== null)
			context = item;

		context.number = i;
		var html       = template(context);

		return html;
	},

	setUpRemoveButtonsForTemplate: function()
	{
		if (this.setUpRemoveButtons)
		{
			this.itemContainer.find('.remove-template-item').off('click').on('click', function(e){
				e.preventDefault();

				var itemContainer = $(this).closest('[data-item-number]');

				itemContainer.slideUp(250, function(){
					itemContainer.remove();
				});
			});
		}
	},

	setFieldsForItem: function(item, parentField)
	{
		i = this.itemNumber;

		for (field in item)
		{
			var value = item[field];

			if (typeof value == "object")
			{
				this.setFieldsForItem(value, field);
			}
			else
			{
				var fieldClass   = this.getFieldClassFromName(field, parentField);
				var fieldElement = this.itemContainer.find('.'+fieldClass);

				// set value for field
				if (fieldElement.attr('type') == "checkbox")
				{
					fieldElement.prop('checked', parseInt(value));
				}
				else
				{
					// make sure value is not already set
					if (fieldElement.val() == "")
						fieldElement.val(value);
				}

				// set "value" data attribute as well in case fields are select boxes that have not yet been populated with options
				fieldElement.data('value', value);

				// add error class for field if an error exists
				var error = this.getErrorById(fieldElement.attr('id'));
				if (error !== false)
				{
					this.populateError(fieldElement, error);
				}
			}
		}
	},

	getFieldClassFromName: function(field, parentField)
	{
		var fieldClass = field.replace(/\_/g, '-');

		// if parent field is "pivot" array, add it to fieldElement
		if (typeof parentField != "undefined" && parentField === "pivot")
			fieldClass = parentField + "-" + fieldClass;

		return this.field.prefix + fieldClass;
	},

	getFieldIdFromName: function(field, parentField)
	{
		var fieldId = field.replace(/\_/g, '-').replace(/\./g, '-');

		// if parent field exists, add it to fieldElement
		if (typeof parentField != "undefined")
			fieldId = parentField + "-" + fieldId;

		console.log(this.field.prefix + fieldId)

		return this.field.prefix + fieldId;
	},

	populateErrors: function(errors, form)
	{
		this.setErrors(errors);

		if (typeof form == "undefined")
			form = $('body');

		form.find('.'+this.errorSettings.classAttribute).removeClass(this.errorSettings.classAttribute);

		form.find(this.errorSettings.element+'.'+this.errorSettings.elementClass).remove();

		for (field in errors)
		{
			var fieldId   = this.getFieldIdFromName(field);
			var fieldElement = form.find('#'+fieldId);

			this.populateError(fieldElement, errors[field]);
		}
	},

	populateError: function(fieldElement, error)
	{
		var errorSettings = this.errorSettings;

		var containerElement = fieldElement.parents('div.form-group');
		containerElement.addClass(errorSettings.classAttribute);

		var labelElement = containerElement.find('label');
		labelElement.addClass(errorSettings.classAttribute);
		fieldElement.addClass(errorSettings.classAttribute);

		if (errorSettings.typeLabelTooltip)
		{
			// add attributes to tooltip's label
			var attributes = errorSettings.typeLabelAttributes;

			for (a in attributes)
			{
				var attribute = this.camelCaseToDashed(a);
				var value     = attributes[a];

				labelElement.addClass(errorSettings.typeLabelAttributes.classAttribute);

				if (labelElement.attr(attribute) != undefined)
					labelElement.attr(attribute, labelElement.attr(attribute) + ' ' + value);
				else
					labelElement.attr(attribute, value);
			}

			// set tooltip error message
			labelElement.attr('title', error);
		}
		else
		{
			var errorElement = fieldElement.closest('div.form-group').find(errorSettings.element+'.'+errorSettings.elementClass);

			if (!fieldElement.length)
				errorElement = fieldElement.closest('div').find(errorSettings.element+'.'+errorSettings.elementClass);

			if (errorElement.length)
			{
				errorElement.html(error);
			}
			else
			{
				var errorHtml = '<'+errorSettings.element+' class="'+errorSettings.elementClass+'">' + error + '</'+errorSettings.element+'>';
				fieldElement.after(errorHtml);
			}
		}

		if (this.errorCallback)
			this.executeFunction(this.errorCallback);
	},

	allItemsLoaded: function()
	{
		return this.templateItemsLoaded == this.templateItems;
	},

	/*
		populateSelect
		--------------

		Populate a select box with a supplied options array.

		Example:

		Formation.populateSelect({
			targetSelect:         '#select-option',
			options:              [{id: 1, name: 'Option 1'}, {id: 2, name: 'Option 2'}],
			optionValue:          'id',
			optionLabel:          'name',
			nullOption:           'Select an option',
			optionsToggleElement: '#select-option-area',
			callbackFunction:     'updateSomething',
		});

		You may use an array like [{id: 1, name: 'Option 1'}, {id: 2, name: 'Option 2'}] and set settings.optionValue and settings.optionValue
		or you can just use a simple array in JSON like ['Option 1', 'Option 2']. If you set settings.optionsToggleElement, the element will be
		shown if there are select options and hidden if there are none.
	*/
	populateSelect: function(settings)
	{
		if (settings.optionLabel === undefined) settings.optionLabel = settings.optionValue;

		// if optionValue, optionLabel, and optionsAssociative are not set, assume array is intended to be associative
		if (settings.optionValue === undefined && settings.optionLabel === undefined && settings.optionsAssociative === undefined)
			settings.optionsAssociative = true;

		// build select options markup
		var options = "";
		if (settings.nullOption !== false && typeof settings.nullOption != "undefined")
			options += '<option value="">'+settings.nullOption+'</option>' + "\n";

		for (c in settings.options)
		{
			if (settings.optionValue === undefined)
			{
				if (settings.optionsAssociative === true)
				{
					options += '<option value="'+c+'">'+settings.options[c]+'</option>' + "\n";
				}
				else
				{
					options += '<option value="'+settings.options[c]+'">'+settings.options[c]+'</option>' + "\n";
				}
			}
			else
			{
				options += '<option value="'+settings.options[c][settings.optionValue]+'">'+settings.options[c][settings.optionLabel]+'</option>' + "\n";
			}
		}

		// set options for each target select field and attempt to set to original value
		$(settings.targetSelect).each(function()
		{
			var currentValue = $(this).val();
			if (currentValue == "" && $(this).data('value') != "")
				currentValue = $(this).data('value');

			var customOptions = "";

			if (typeof settings.nullOption == "undefined")
			{
				var nullOption = "Select an Option";

				if ($(this).data('null-option') != "")
					nullOption = $(this).data('null-option');

				customOptions = '<option value="">'+nullOption+'</option>' + "\n";
			}

			customOptions += options;

			$(this).html(customOptions);

			console.log(customOptions);

			$(this).val(currentValue);
		});

		// show or hide an element depending on whether options are available in select box
		if (typeof settings.optionsToggleElement != "undefined")
		{
			var optionsExist = false;

			if (typeof settings.options == "object")
				optionsExist = Object.keys(settings.options).length;
			else
				optionsExist = settings.options.length;

			if (optionsExist > 0)
				$(settings.optionsToggleElement).removeClass('hidden');
			else
				$(settings.optionsToggleElement).addClass('hidden');
		}

		this.executeFunction(settings.callbackFunction);
	},

	/*
		ajaxForSelect
		-------------

		Populate a select box with an options array provided by the result of an ajax post.

		Example:

		Formation.ajaxForSelect({
			url:                  baseUrl + 'ajax/select-options',
			postData:             { category_id: 1 },
			targetSelect:         '#select-option',
			nullOption:           'Select an option',
			optionsToggleElement: '#select-option-area',
		});

		You may return return an array like [{id: 1, name: 'Option 1'}, {id: 2, name: 'Option 2'}] and set settings.optionValue and settings.optionLabel
		or you can just return a simple array in JSON like ['Option 1', 'Option 2']. If you set settings.optionsToggleElement, the element will be shown
		if there are select options and hidden if there are none.
	*/
	ajaxForSelect: function(settings)
	{
		if (settings.type === undefined)
			settings.type = 'post';

		if (settings.postData === undefined)
			settings.postData = {};

		return $.ajax({
			url:      settings.url,
			type:     settings.type,
			data:     settings.postData,
			dataType: 'json',

			success: function(data)
			{
				settings.options = data;
				Formation.populateSelect(settings);
			},
			error: function()
			{
				Formation.log('Ajax For Select Failed');
			}
		});
	},

	executeFunction: function(functionItem, parameter1, parameter2)
	{
		if (functionItem === undefined)
			return null;

		if (typeof functionItem == "function")
			return functionItem(parameter1, parameter2);

		var functionArray = functionItem.split('.');

		if (functionArray.length == 2)
			return window[functionArray[0]][functionArray[1]](parameter1, parameter2);
		else
			return window[functionArray[0]](parameter1, parameter2);
	},

	log: function(string)
	{
		console.log('Formation.js: '+string);
	},

};