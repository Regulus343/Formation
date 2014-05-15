/*
|------------------------------------------------------------------------------
| Formation.js
|------------------------------------------------------------------------------
|
| Last Updated: May 14, 2014
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

	errorCallback:       'Formation.defaultErrorCallback',

	errors:              null,
	errorsById:          null,

	templateItems:       0,
	templateItemsLoaded: 0,

	setErrorSettings: function (errorSettings) {
		this.errorSettings = errorSettings;
	},

	setErrorCallback: function (errorCallback) {
		this.errorCallback = errorCallback;
	},

	defaultErrorCallback: function(fieldContainer) {
		fieldContainer.find('[data-toggle="tooltip"]').tooltip({html: true}); //by default, form errors are set up for Bootstrap 3
	},

	setErrors: function(errors) {
		this.errors     = errors;
		this.errorsById = [];

		for (e in errors) {
			this.errorsById[e.replace(/\_/g, '-').replace(/\./g, '-')] = errors[e];
		}
	},

	getErrors: function() {
		return this.errors;
	},

	getError: function(field) {
		if (this.errors === null)
			return false;

		return this.errors[field] !== undefined ? this.errors[field] : false;
	},

	getErrorsById: function() {
		return this.errorsById;
	},

	getErrorById: function(field) {
		if (this.errorsById === null)
			return false;

		return this.errorsById[field] !== undefined ? this.errorsById[field] : false;
	},

	camelCaseToDashed: function(string) {
		return string.replace(/([A-Z])/g, function($1) {
			return '-' + $1.toLowerCase(); 
		});
	},

	//used with Handlebars.js to load form template items populate fields
	loadTemplates: function(container, items, callbackFunction) {
		$(container).html('');

		//require "data-template-id" attribute for container
		var templateId = $(container).attr('data-template-id');
		if (templateId == null) {
			console.log('Container requires "data-template-id" attribute.');
			return;
		}

		this.templateItems = 0;
		for (i in items) {
			if (items.hasOwnProperty(i))
				this.templateItems ++;
		}

		for (i in items) {
			var item = items[i];

			//create item template
			var source     = $('#'+templateId).html();
			var template   = Handlebars.compile(source);
			var context    = item;
			context.number = i;
			var html       = template(context);

			//append item to container
			$(container).append(html);

			//populate fields and set errors for item based on data
			this.setFieldsForItem(item);

			this.templateItemsLoaded ++;

			//trigger callback function if one is set
			if (callbackFunction !== undefined)
				callbackFunction($(container).find('[data-item-number="'+i+'"]'), item);
		}
	},

	setFieldsForItem: function(item) {
		var errorSettings = this.errorSettings;

		for (field in item) {
			var value = item[field];

			if (typeof value == "object") {

				this.setFieldsForItem(value);

			} else {
				var fieldElement = $(container).find('[data-item-number="'+i+'"]').find('.field-'+field.replace('_', '-'));

				//set value for field
				fieldElement.val(value);

				//set "data-value" attribute as well in case fields are select boxes that have not yet been populated with options
				fieldElement.attr('data-value', value);

				//add error class for field if an error exists
				var error = this.getErrorById(fieldElement.attr('id'));
				if (error !== false) {
					var containerElement = fieldElement.parents('div.form-group');
					containerElement.addClass(errorSettings.classAttribute);

					var labelElement = containerElement.find('label');
					labelElement.addClass(errorSettings.classAttribute);

					fieldElement.addClass(errorSettings.classAttribute);

					if (this.errorSettings.typeLabelTooltip) {
						//add attributes to tooltip's label
						var attributes = errorSettings.typeLabelAttributes;
						for (a in attributes) {
							var attribute = this.camelCaseToDashed(a);
							var value     = attributes[a];

							labelElement.addClass(errorSettings.typeLabelAttributes.classAttribute);

							if (labelElement.attr(attribute) != undefined)
								labelElement.attr(attribute, labelElement.attr(attribute) + ' ' + value);
							else
								labelElement.attr(attribute, value);
						}

						//set tooltip error message
						labelElement.attr('title', error);

					} else {
						var errorHtml = '<'+errorSettings.element+' class="'+errorSettings.elementClass+'">' + error + '</'+errorSettings.element+'>';
						fieldElement.after(errorHtml);
					}

					if (this.errorCallback) {
						var errorCallbackArray = this.errorCallback.split('.');
						if (errorCallbackArray.length == 2)
							window[errorCallbackArray[0]][errorCallbackArray[1]](containerElement);
						else
							window[this.errorCallback](containerElement);
					}
				}
			}
		}
	},

	allItemsLoaded: function() {
		return this.templateItemsLoaded == this.templateItems;
	},

	/*
		populateSelect
		--------------

		Populate a select box with a supplied options array.

		Example:

		populateSelect({
			targetSelect:         '#select-option',
			options:              [{id: 1, name: 'Option 1'}, {id: 2, name: 'Option 2'}],
			optionValue:          'id',
			optionLabel:          'name',
			nullOption:           'Select an option',
			optionsToggleElement: '#select-option-area'
		});

		You may use an array like [{id: 1, name: 'Option 1'}, {id: 2, name: 'Option 2'}] and set settings.optionValue and settings.optionValue
		or you can just use a simple array in JSON like ['Option 1', 'Option 2']. If you set settings.optionsToggleElement, the element will be
		shown if there are select options and hidden if there are none.
	*/
	populateSelect: function(settings) {
		if (settings.nullOption === undefined) {
			if ($(settings.targetSelect).attr('data-null-option') != "")
				settings.nullOption = $(settings.targetSelect).attr('data-null-option');
			else
				settings.nullOption = "Select an option";
		}

		if (settings.optionLabel === undefined) settings.optionLabel = settings.optionValue;

		var currentValue = $(settings.targetSelect).val();
		if (settings.nullOption !== false) {
			$(settings.targetSelect).html('<option value="">'+settings.nullOption+'</option>' + "\n");
		}
		for (c=0; c < settings.options.length; c++) {
			if (settings.optionValue === undefined) {
				var option = '<option value="'+settings.options[c]+'">'+settings.options[c]+'</option>' + "\n";
			} else {
				var option = '<option value="'+settings.options[c][settings.optionValue]+'">'+settings.options[c][settings.optionLabel]+'</option>' + "\n";
			}
			$(settings.targetSelect).append(option);
		}
		$(settings.targetSelect).val(currentValue); //attempt to change to selected value (if it still exists)

		//show or hide an element depending on whether options are available in select box
		if (settings.optionsToggleElement !== undefined) {
			if (data.length > 0) {
				$(settings.optionsToggleElement).removeClass('hidden');
			} else {
				$(settings.optionsToggleElement).addClass('hidden');
			}
		}
	},

	/*
		ajaxForSelect
		-------------

		Populate a select box with an options array provided by the result of an ajax post.

		Example:

		ajaxForSelect({
			url:                  baseUrl + 'ajax/select-options',
			postData:             { category_id: 1 },
			targetSelect:         '#select-option',
			nullOption:           'Select an option',
			optionsToggleElement: '#select-option-area'
		});

		You may return return an array like [{id: 1, name: 'Option 1'}, {id: 2, name: 'Option 2'}] and set settings.optionValue and settings.optionValue
		or you can just return a simple array in JSON like ['Option 1', 'Option 2']. If you set settings.optionsToggleElement, the element will be shown
		if there are select options and hidden if there are none.
	*/
	ajaxForSelect: function(settings) {
		return $.ajax({
			url: settings.url,
			type: 'post',
			data: settings.postData,
			dataType: 'json',
			success: function(data) {
				settings.options = data;
				Formation.populateSelect(settings);
			},
			error: function() {
				console.log('Ajax For Select Failed');
			}
		});
	}

}