<?php
	namespace YetAnother\Tests\Transactions;
	
	use Exception;
	use YetAnother\Laravel\Transaction;
	
	class BooleanTransaction extends Transaction
	{
		public bool $done = false;
		
		protected function validate(): void
		{
			if ($this->done)
				throw new Exception('Transaction is already done.');
		}
		
		protected function perform(): void
		{
			$this->done = true;
		}
	}
