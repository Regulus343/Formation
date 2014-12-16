{{-- Formation JS: Load --}}
<script type="text/javascript" src="{{ Config::get('app.url') }}/packages/regulus/formation/js/formation.js"></script>

{{-- Formation JS: Initialize --}}
<script type="text/javascript">
	$(document).ready(function(){
		Formation.setErrorSettings($.parseJSON('{{ Form::getJsonErrorSettings() }}'));

		Formation.setErrors($.parseJSON('{{ Form::getJsonErrors() }}'));
	});
</script>