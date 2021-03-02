<?php
	namespace YetAnother\Tests\Transactions;
	
	use YetAnother\Laravel\Transaction;
	
	class BooleanTransaction extends Transaction
	{
		public bool $done = false;
		
		protected function perform(): void
		{
			$this->done = true;
		}
	}
