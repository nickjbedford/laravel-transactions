<?php
	namespace YetAnother\Tests\Transactions;
	
	use Illuminate\Support\Facades\DB;
	use YetAnother\Laravel\Transaction;
	
	class TableLockTransaction extends Transaction
	{
		const TableName = 'LockedTable';
		
		public function __construct()
		{
			$this->lockTableName = self::TableName;
		}
		
		protected function perform(): void
		{
			/** @noinspection SqlNoDataSourceInspection */
			DB::insert("INSERT INTO `$this->lockTableName` VALUES(1, 2, 3)");
		}
	}
