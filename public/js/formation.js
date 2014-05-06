/*
|------------------------------------------------------------------------------
| Formation.js
|------------------------------------------------------------------------------
|
| Last Updated: May 5, 2014
|
*/

var Formation = {

	/* used with Handlebars.js to load form template items populate fields */
	loadTemplates: function(container, items, callbackFunction) {
		$(container).html('');

		//require "data-template-id" attribute for container
		var templateId = $(container).attr('data-template-id');
		if (templateId == null) {
			console.log('Container requires "data-template-id" attribute.');
			return;
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

			//populate fields based on data
			for (field in item) {
				var value = item[field];
				if (!Array.isArray(value)) {
					$(container).find('[data-item-number="'+i+'"]').find('.field-'+field.replace('_', '-')).val(value);
				}
			}

			if (callbackFunction !== undefined)
				callbackFunction($(container).find('[data-item-number="'+i+'"]'), item);
		}
	}

}