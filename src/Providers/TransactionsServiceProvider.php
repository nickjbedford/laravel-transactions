<?php
	/**
	 * Project: laravel-transactions
	 * Created By: nickbedford
	 * Created: 2021-03-03 8:20 am
	 */
	
	namespace YetAnother\Laravel\Providers;

	use Illuminate\Support\Facades\App;
	use Illuminate\Support\ServiceProvider;
	use YetAnother\Laravel\Console\Commands\MakeTransactionCommand;
	use YetAnother\Laravel\Console\Commands\MakeTransactionResponderCommand;
	
	class TransactionsServiceProvider extends ServiceProvider
	{
		function boot()
		{
			if ($this->app->runningInConsole() || App::environment() == 'testing')
			{
				$this->commands([
					MakeTransactionCommand::class,
					MakeTransactionResponderCommand::class,
				]);
			}
		}
	}
