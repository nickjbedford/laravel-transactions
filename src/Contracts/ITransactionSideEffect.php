<?php
	
	namespace YetAnother\Laravel\Contracts;
	
	use Throwable;
	
	/**
	 * Represents a side effect of a transaction that must be
	 * rolled back upon failure.
	 */
	interface ITransactionSideEffect
	{
		/**
		 * @return mixed
		 * @throws Throwable
		 */
		function revert();
	}
