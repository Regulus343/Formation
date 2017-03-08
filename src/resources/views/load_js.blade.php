{{-- Formation JS: Load --}}

<script type="text/javascript" src="{{ Site::js('formation', 'regulus/formation') }}"></script>

{{-- Formation JS: Initialize --}}

<script type="text/javascript">

	$(document).ready(function()
	{
		Formation.setErrorSettings({!! Form::getJsonErrorSettings() !!});

		Formation.setErrors({!! Form::getJsonErrors() !!});
	});

</script>