<?php
	namespace YetAnother\Tests\Transactions;
	
	use InvalidArgumentException;
	
	class InvalidBooleanTransaction extends BooleanTransaction
	{
		protected function validate(): void
		{
			throw new InvalidArgumentException('The transaction is not valid.');
		}
	}
