<?php
	/** @noinspection PhpUnused */

	namespace YetAnother\Laravel;

	use Exception;
	use Illuminate\Support\Facades\DB;
	use ReflectionException;
	use ReflectionMethod;
	use ReflectionNamedType;
	use Throwable;
	
	/**
	 * Represents the base class for a potentially complex or distributed
	 * transaction. Transaction subclasses should handle the cleanup of
	 * side effects in the case of failures.
	 */
	abstract class Transaction
	{
		/**
		 * @var string|null $event Specifies the event class to fire after
		 * the transaction is performed successfully.
		 */
		protected ?string $event = null;
		
		/**
		 * @var string|null $lockTableName Specifies the name of the table to
		 * optionally acquire a lock on for writing.
		 */
		protected ?string $lockTableName = null;
		
		/**
		 * Performs the action.
		 * @throws Throwable
		 * @return static
		 */
		private function validateAndPerform(): self
		{
			$this->validate();
			$this->perform();
			return $this;
		}
		
		/**
		 * @throws Throwable
		 */
		private function lockTableIfNecessary()
		{
			if (!$this->lockTableName)
				return;
			
			DB::raw("LOCK TABLES `$this->lockTableName` WRITE");
		}
		
		private function unlockTableIfNecessary()
		{
			if (!$this->lockTableName)
				return;
			
			DB::raw("UNLOCK TABLES");
		}

		/**
		 * Executes the transaction inside a database transaction context. If an
		 * exception is thrown, the database transaction is rolled back and cleanup
		 * is performed to undo any external side effects.
		 * @throws Throwable
		 * @return static
		 */
		public function execute(): self
		{
			try
			{
				$this->lockTableIfNecessary();
				DB::transaction(fn() => $this->validateAndPerform());
			}
			catch(Throwable $exception)
			{
				$this->cleanupAfterFailure();
				throw $exception;
			}
			finally
			{
				$this->unlockTableIfNecessary();
			}
			$this->fireEvent();
			return $this;
		}

		/**
		 * Performs the transaction after validation.
		 * @throws Throwable
		 */
		protected abstract function perform(): void;

		/**
		 * Validates the transaction before it is performed.
		 * @throws Throwable
		 */
		protected function validate(): void
		{
		}

		/**
		 * Handles the cleanup of the action's side effects
		 * if the action fails during a transaction. External data or
		 * changes, such as uploaded files, should be removed or reversed.
		 */
		public function cleanupAfterFailure(): void
		{
		}
		
		/**
		 * @throws Throwable
		 * @throws ReflectionException
		 */
		protected function fireEvent()
		{
			if ($this->event && $event = $this->createEvent())
				event($event);
		}
		
		/**
		 * Creates an instance of the event to dispatch.
		 * @return mixed
		 * @throws Exception
		 * @throws ReflectionException
		 */
		protected function createEvent()
		{
			$method = new ReflectionMethod($this->event, '__construct');
			$params = $method->getParameters();
			
			if (empty($params))
				return new $this->event();
			
			else if (count($params) == 1 && $param = $params[0])
			{
				if ($param->hasType() && $type = $param->getType())
				{
					/** @var ReflectionNamedType $type */
					if (!($type instanceof ReflectionNamedType) || !in_array($type->getName(), [
							static::class,
							self::class
						]))
					{
						$class = static::class;
						throw new Exception("$this->event constructor does not accept an instance of {$class}.");
					}
				}
				return new $this->event($this);
			}
			
			throw new Exception("$this->event constructor takes more than one parameter.");
		}
	}
