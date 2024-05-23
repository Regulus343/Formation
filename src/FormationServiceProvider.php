<?php namespace Regulus\Formation;

use Illuminate\Support\ServiceProvider;

class FormationServiceProvider extends ServiceProvider {

	/**
	 * Indicates if loading of the provider is deferred.
	 *
	 * @var bool
	 */
	protected $defer = false;

	/**
	 * Bootstrap the application events.
	 *
	 * @return void
	 */
	public function boot(): void
	{
		$this->publishes([
			__DIR__.'/config/form.php'  => config_path('form.php'),
			__DIR__.'/resources/assets' => resource_path('assets/vendor/formation'),
			__DIR__.'/resources/lang'   => resource_path('lang/vendor/formation'),
			__DIR__.'/resources/views'  => resource_path('views/vendor/formation'),
		]);

		$this->loadRoutesFrom(__DIR__.'/routes.php');

		$this->loadTranslationsFrom(__DIR__.'/resources/lang', 'formation');

		$this->loadViewsFrom(__DIR__.'/resources/views', 'formation');
	}

	/**
	 * Register the service provider.
	 *
	 * @return void
	 */
	public function register(): void
	{
		require __DIR__.'/helpers.php';

		$this->app->singleton('Regulus\Formation\Formation', function($app)
		{
			$session = isset($app['session.store']) ? $app['session.store'] : null;

			return new Formation($app['url'], $session, csrf_token());
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