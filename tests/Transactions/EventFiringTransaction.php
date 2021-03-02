<?php
	namespace YetAnother\Tests\Transactions;
	
	use YetAnother\Laravel\Transaction;
	use YetAnother\Tests\Events\TransactionFired;
	
	class EventFiringTransaction extends Transaction
	{
		protected ?string $event = TransactionFired::class;
		public string $eventKey;
		
		protected function perform(): void
		{
			$this->eventKey = bin2hex(random_bytes(8));
		}
	}
