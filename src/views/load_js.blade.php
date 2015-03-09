{{-- Formation JS: Load --}}
<script type="text/javascript" src="{{ Site::js('formation', 'regulus/formation') }}"></script>

{{-- Formation JS: Initialize --}}
<script type="text/javascript">
	$(document).ready(function(){

		Formation.setErrorSettings($.parseJSON('{!! Form::getJsonErrorSettings() !!}'));

		Formation.setErrors($.parseJSON('{!! Form::getJsonErrors() !!}'));

	});
</script>