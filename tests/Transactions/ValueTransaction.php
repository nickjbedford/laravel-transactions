<?php
	namespace YetAnother\Tests\Transactions;
	
	use YetAnother\Laravel\Transaction;
	
	class ValueTransaction extends Transaction
	{
		/** @var mixed $value */
		public $value;
		
		public function __construct($value)
		{
			$this->value = $value;
		}
		
		protected function perform(): void
		{
		}
	}
