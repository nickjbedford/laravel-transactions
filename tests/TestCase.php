<?php
	/**
	 * Project: laravel-transactions
	 * Created By: nickbedford
	 * Created: 2021-03-02 3:03 pm
	 */
	
	namespace YetAnother\Tests;
	
	use Illuminate\Support\Facades\Event;
	use Orchestra\Testbench\TestCase as OrchestraTestCase;
	use YetAnother\Laravel\Providers\TransactionsServiceProvider;
	use YetAnother\Tests\Events\TransactionFired;
	use YetAnother\Tests\Events\TransactionFiredListener;
	
	class TestCase extends OrchestraTestCase
	{
		protected function getEnvironmentSetUp($app)
		{
			$app['config']->set('database.default', 'testbench');
		    $app['config']->set('database.connections.testbench', [
		        'driver'   => 'sqlite',
		        'database' => ':memory:',
		        'prefix'   => '',
		    ]);
		    
		    Event::listen(TransactionFired::class, TransactionFiredListener::class);
		}
		
		protected function getPackageProviders($app): array
		{
			return [
				TransactionsServiceProvider::class
			];
		}
	}
