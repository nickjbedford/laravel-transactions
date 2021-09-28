<?php
	
	namespace YetAnother\Tests\Transactions;
	
	use Exception;
	use YetAnother\Laravel\Transaction;
	use YetAnother\Tests\SideEffects\AddValueSideEffect;
	
	class SideEffectTransaction extends Transaction
	{
		public array $values = [];
		
		const ExpectedResult = [ 3, 4 ];
		
		/**
		 * @inheritDoc
		 */
		protected function perform(): void
		{
			$this->values = [ 1, 2, 3 ];
			$this->addSideEffect(new AddValueSideEffect(1, $this));
			$this->addSideEffect(new AddValueSideEffect(2, $this));
			throw new Exception();
		}
		
		public function cleanupAfterFailure(): void
		{
			$this->values[] = 4;
		}
	}
