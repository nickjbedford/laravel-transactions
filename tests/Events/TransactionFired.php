<?php
	namespace YetAnother\Tests\Events;
	
	use Illuminate\Foundation\Events\Dispatchable;
	use YetAnother\Tests\Transactions\EventFiringTransaction;
	
	class TransactionFired
	{
        use Dispatchable;
        
        /** @var self[] $instances */
		public static array $instances = [];
		
		public bool $listenedTo = false;
		public EventFiringTransaction $transaction;
		
		public function __construct(EventFiringTransaction $transaction)
		{
			self::$instances[$transaction->eventKey] = $this;
			$this->transaction = $transaction;
		}
	}
