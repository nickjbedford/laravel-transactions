<?php
	/** @noinspection PhpUnhandledExceptionInspection */
	
	namespace YetAnother\Tests;
	
	use Illuminate\Database\Schema\Blueprint;
	use Illuminate\Support\Facades\DB;
	use Illuminate\Support\Facades\Schema;
	use YetAnother\Tests\Transactions\TableLockTransaction;
	
	class TableLockTransactonTests extends TestCase
	{
		protected function setUp(): void
		{
			parent::setUp();
			
			Schema::create(TableLockTransaction::TableName, function(Blueprint $table)
			{
				$table->integer('a');
				$table->integer('b');
				$table->integer('c');
			});
		}
		
		protected function tearDown(): void
		{
			Schema::drop(TableLockTransaction::TableName);
			
			parent::tearDown();
		}
		
		function testLockedTableInsertDoesNotThrow()
		{
			(new TableLockTransaction())->execute();
			
			/** @noinspection SqlNoDataSourceInspection */
			$row = DB::selectOne('SELECT * FROM `' . TableLockTransaction::TableName . '`');
			
			$this->assertEquals(1, $row->a);
			$this->assertEquals(2, $row->b);
			$this->assertEquals(3, $row->c);
		}
	}
