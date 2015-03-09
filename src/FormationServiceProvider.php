<?php namespace Regulus\Formation;

use Illuminate\Support\ServiceProvider;

class FormationServiceProvider extends ServiceProvider {

	/**
	 * Indicates if loading of the provider is deferred.
	 *
	 * @var bool
	 */
	protected $defer = true;

	/**
	 * Bootstrap the application events.
	 *
	 * @return void
	 */
	public function boot()
	{
		$this->publishes([
			__DIR__.'/config/form.php' => config_path('form.php'),
			__DIR__.'/assets'          => assets_path('regulus/formation'),
		]);

		$this->loadTranslationsFrom(__DIR__.'/lang', 'formation');

		$this->loadViewsFrom(__DIR__.'/views', 'formation');
	}

	/**
	 * Register the service provider.
	 *
	 * @return void
	 */
	public function register()
	{
		$this->app->singleton('Regulus\Formation\Formation', function($app)
		{
			return new Formation($app['url'], csrf_token());
		});
	}

	/**
	 * Get the services provided by the provider.
	 *
	 * @return array
	 */
	public function provides()
	{
		return ['Regulus\Formation\Formation'];
	}

}