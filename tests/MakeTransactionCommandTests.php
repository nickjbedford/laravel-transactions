<?php
	/** @noinspection PhpUnhandledExceptionInspection */
	
	namespace YetAnother\Tests;
	
	use Illuminate\Http\Request;
	use Illuminate\Support\Facades\Artisan;
	use Illuminate\Support\Facades\File;
	use YetAnother\Laravel\Http\TransactionResponder;
	use YetAnother\Laravel\Transaction;
	
	class MakeTransactionCommandTests extends TestCase
	{
		const DirectoriesToClean = [
			'Transactions',
			'Http/Responders',
		];
		
		protected function setUp(): void
		{
			parent::setUp();
			$this->cleanAppFolder();
		}
		
		protected function tearDown(): void
		{
			$this->cleanAppFolder();
			parent::tearDown();
		}
		
		protected function cleanAppFolder(): void
		{
			foreach(self::DirectoriesToClean as $path)
			{
				if (File::exists($directory = app_path($path)))
				{
					File::cleanDirectory($directory);
					File::deleteDirectory($path);
				}
			}
		}
		
		function testMakeTransactionCommandCreatesFile()
		{
			$this->assertFalse(class_exists($className = 'App\\Transactions\\Custom\\TestTransaction'));
			$filePath = app_path('Transactions/Custom/TestTransaction.php');
			
			$exitCode = Artisan::call('make:transaction', [
				'name' => 'Custom/TestTransaction'
			]);
			
			$this->assertEquals(0, $exitCode);
			$this->assertTrue(File::exists($filePath));
			
			include_once($filePath);
			
			$this->assertTrue(class_exists($className));
			
			/** @var Transaction $instance */
			$instance = new $className();
			$instance->execute();
		}
		
		function testMakeTransactionResponderCommandCreatesFile()
		{
			$this->assertFalse(class_exists($className = 'App\\Http\\Responders\\Custom\\TestTransactionResponder'));
			$filePath = app_path('Http/Responders/Custom/TestTransactionResponder.php');
			
			$exitCode = Artisan::call('make:responder', [
				'name' => 'Custom/TestTransactionResponder'
			]);
			
			$this->assertEquals(0, $exitCode);
			$this->assertTrue(File::exists($filePath));
			
			include_once($filePath);
			
			$this->assertTrue(class_exists($className));
		}
	}
