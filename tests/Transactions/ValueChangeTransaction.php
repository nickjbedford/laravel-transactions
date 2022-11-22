<?php
	namespace YetAnother\Tests\Transactions;
	
	use Exception;
	use YetAnother\Laravel\Transaction;
	
	class ValueChangeTransaction extends Transaction
	{
		/** @var mixed $value */
		public $value;
		
		/** @var mixed $value */
		public $to;
		
		public function __construct($value, $to)
		{
			$this->value = $value;
			$this->to = $to;
		}
		
		protected function validate(): void
		{
			if ($this->value == $this->to)
				throw new Exception('Value is already changed.');
		}
		
		protected function perform(): void
		{
			$this->value = $this->to;
		}
	}
