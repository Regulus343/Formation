/*
|------------------------------------------------------------------------------
| Formation.js
|------------------------------------------------------------------------------
|
| Last Updated: May 10, 2014
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

	errorCallback: null,

	errors:        null,
	errorsById:    null,

	setErrorSettings: function (errorSettings) {
		this.errorSettings = errorSettings;
	},

	setErrorCallback: function (errorCallback) {
		this.errorCallback = errorCallback;
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

	/* used with Handlebars.js to load form template items populate fields */
	loadTemplates: function(container, items, callbackFunction) {
		$(container).html('');

		//require "data-template-id" attribute for container
		var templateId = $(container).attr('data-template-id');
		if (templateId == null) {
			console.log('Container requires "data-template-id" attribute.');
			return;
		}

		var errorSettings = this.errorSettings;

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

			//populate fields based on data
			for (field in item) {
				var value = item[field];
				if (!Array.isArray(value)) {
					var fieldElement = $(container).find('[data-item-number="'+i+'"]').find('.field-'+field.replace('_', '-'));

					//set value for field
					fieldElement.val(value);

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

						if (this.errorCallback)
							window[this.errorCallback](containerElement);
					}
				}
			}

			if (callbackFunction !== undefined)
				callbackFunction($(container).find('[data-item-number="'+i+'"]'), item);
		}
	}

}