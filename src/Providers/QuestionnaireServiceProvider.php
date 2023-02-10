<?php

namespace Questionnaire\Providers;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\ServiceProvider;
use Illuminate\Routing\Router;
use Illuminate\Support\Facades\App;

class QuestionnaireServiceProvider extends ServiceProvider
{

    /**
	 * Bootstrap any application services.
	 *
	 * @return void
	 */
	public function boot(Router $router)
	{
		error_reporting(E_ALL ^ E_STRICT ^ E_NOTICE ^ E_USER_NOTICE ^ E_DEPRECATED ^ E_WARNING );

        $this->publishes([
            __DIR__ . '/../config/questionnaire.php' => config_path('questionnaire.php'),
        ]);

		$this->loadMigrationsFrom(__DIR__ . '/../../database/migrations');

        $this->loadRoutesFrom(__DIR__ . '/../routes/web.php');

        $this->loadTranslationsFrom(__DIR__.'/../lang', 'bf');

//        $router->pushMiddlewareToGroup('web', \Questionnaire\Http\Middleware\QuestionnaireModels::class);

        // make sure the views can be loaded in each application
        // usage: return view('questionnaire::folder.file');
        $this->loadViewsFrom(__DIR__ . '/../views', 'questionnaire');

//		$this->mergeConfigFrom(__DIR__ . '/../../config/filesystems/disks.'.config('app.env').'.php', 'filesystems.disks');

//        Relation::morphMap([
//            'MorphOrder' => Order::class,
//        ]);
	}

	/**
	 * Register any application services.
	 *
	 * @return void
	 */
	public function register()
	{
		Carbon::setLocale('nl');
		setlocale(LC_TIME, 'nl_NL.utf8');

//        $router = app(\Illuminate\Routing\Router::class);
//        $router->pushMiddlewareToGroup('web', \Questionnaire\Http\Middleware\QuestionnaireModels::class);

//        App::middleware(\Questionnaire\Http\Middleware\QuestionnaireModels::class);
    }

}
