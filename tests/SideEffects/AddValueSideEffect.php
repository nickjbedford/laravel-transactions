<?php
	
	namespace YetAnother\Tests\SideEffects;
	
	use Throwable;
	use YetAnother\Laravel\Contracts\ITransactionSideEffect;
	use YetAnother\Tests\Transactions\SideEffectTransaction;
	
	class AddValueSideEffect implements ITransactionSideEffect
	{
		private int $value;
		private SideEffectTransaction $transaction;
		
		public function __construct(int $value, SideEffectTransaction $transaction)
		{
			$this->value = $value;
			$this->transaction = $transaction;
		}
		
		function revert()
		{
			array_splice($this->transaction->values, array_search($this->value, $this->transaction->values, true), 1);
			$this->transaction->values = array_values($this->transaction->values);
		}
	}
