<?php
	namespace YetAnother\Laravel;
	
	use Closure;
	use Exception;
	use Illuminate\Support\Collection;
	
	/**
	 * Represents a chained sequence of transactions.
	 */
	class ChainedTransaction extends Transaction
	{
		/** @var Transaction[]|Closure[]|Collection $chain Stores the chain of transactions to execute. */
		private Collection $chain;

		/** @var Transaction[] $transactions Stores the transactions executed thus far. */
		public array $transactions = [];
		
		public function __construct()
		{
			$this->chain = collect();
		}
		
		/**
		 * Adds a transaction to the chain of transactions.
		 * @param Transaction|Closure|Transaction[]|Closure[]|Collection $transactions A sequence of transactions to execute.
		 * If a closure is passed, this closure must return a Transaction to execute.
		 * @return self
		 */
		function add($transactions): self
		{
			if ($transactions instanceof Transaction || $transactions instanceof Closure)
				$this->chain[] = $transactions;
			else
				$this->chain = $this->chain->merge($transactions);
			return $this;
		}

		/**
		 * @inheritDoc
		 */
		protected function perform(): void
		{
			foreach($this->chain as $item)
			{
				if ($item instanceof Closure)
				{
					$item = $item($this->transactions);

					/** @var Transaction $item */
					if (!($item instanceof Transaction))
						throw new Exception('Value returned is not a transaction.');
				}

				$item->execute();
				$this->transactions[] = $item;
			}
		}
		
		public function cleanupAfterFailure(): void
		{
			while($transaction = array_pop($this->transactions))
			{
				if ($transaction instanceof Transaction)
					$transaction->cleanupAfterFailure();
			}
		}
	}
