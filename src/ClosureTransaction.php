<?php
	namespace YetAnother\Laravel;
	
	use Closure;
	
	/**
	 * Represents a transaction made from executing a closure passed to it.
	 */
	class ClosureTransaction extends Transaction
	{
		private Closure $closure;
		private ?Closure $rollback = null;
		private array $arguments;

		/**
		 * Creates a new transaction from a closure and optional arguments.
		 * @param Closure $closure
		 * @param array $arguments
		 */
		function __construct(Closure $closure, array $arguments = [])
		{
			$this->closure = $closure->bindTo($this);
			$this->arguments = $arguments;
		}
		
		/**
		 * Specifies a closure to perform rollback in the case of failured.
		 * @param Closure $closure
		 * @return $this
		 */
		function withRollback(Closure $closure): self
		{
			$this->rollback = $closure->bindTo($this);
			return $this;
		}

		/**
		 * @inheritDoc
		 */
		protected function perform(): void
		{
			call_user_func_array($this->closure, $this->arguments);
		}
		
		/**
		 * @inheritDoc
		 */
		public function cleanupAfterFailure(): void
		{
			parent::cleanupAfterFailure();

			if ($this->rollback)
				call_user_func_array($this->rollback, $this->arguments);
		}
		
		/**
		 * Creates a new transaction from a closure and optional arguments.
		 * @param Closure $closure
		 * @param mixed ...$arguments
		 * @return ClosureTransaction
		 */
		static function new(Closure $closure, ...$arguments): self
		{
			return new self($closure, $arguments);
		}

		/**
		 * Creates a new transaction from a closure and optional arguments.
		 * @param Closure $closure
		 * @param Closure $rollback
		 * @param mixed ...$arguments
		 * @return ClosureTransaction
		 */
		static function newWithRollback(Closure $closure, Closure $rollback, ...$arguments): self
		{
			return (new self($closure, $arguments))
				->withRollback($rollback);
		}

		/**
		 * Creates a new transaction from a closure and optional arguments.
		 * @param $object
		 * @param string $method
		 * @param mixed ...$arguments
		 * @return ClosureTransaction
		 */
		static function fromInstance($object, string $method, ...$arguments): self
		{
			$closure = Closure::fromCallable([ $object, $method ]);
			return new self($closure, $arguments);
		}
	}
