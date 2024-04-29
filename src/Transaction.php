<?php
	/** @noinspection PhpUnused */

	namespace YetAnother\Laravel;

	use Exception;
	use Illuminate\Support\Facades\DB;
	use ReflectionException;
	use ReflectionMethod;
	use ReflectionNamedType;
	use Throwable;
	use YetAnother\Laravel\Contracts\ITransactionSideEffect;
	
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
		 * @var ITransactionSideEffect[] $sideEffects Specifies the list of side effects to be
		 * reverted upon failure of the transaction.
		 */
		private array $sideEffects = [];
		
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
		protected function lockTableIfNecessary()
		{
			if (!$this->lockTableName)
				return;
			
			DB::raw("LOCK TABLES `$this->lockTableName` WRITE");
		}
		
		protected function unlockTableIfNecessary()
		{
			if (!$this->lockTableName)
				return;
			
			DB::raw("UNLOCK TABLES");
		}
		
		/**
		 * Adds a side effect to the transaction.
		 * @param ITransactionSideEffect $sideEffect
		 */
		protected function addSideEffect(ITransactionSideEffect $sideEffect)
		{
			$this->sideEffects[] = $sideEffect;
		}
		
		/**
		 * Executes before the transaction is started.
		 * @return void
		 */
		protected function beforeTransaction() { }
		
		/**
		 * Executes after the transaction is committed or rolled back.
		 * @return void
		 */
		protected function afterTransaction() { }

		/**
		 * Executes the transaction inside a database transaction context. If an
		 * exception is thrown, the database transaction is rolled back and cleanup
		 * is performed to undo any external side effects.
		 * @return static
		 * @throws Throwable
		 * @noinspection PhpMissingReturnTypeInspection
		 */
		public function execute()
		{
			try
			{
				$this->lockTableIfNecessary();
				$this->beforeTransaction();
				DB::transaction(fn() => $this->validateAndPerform());
				$this->afterTransaction();
			}
			catch(Throwable $exception)
			{
				$this->revertSideEffects();
				$this->cleanupAfterFailure();
				$this->throw($exception);
			}
			finally
			{
				$this->unlockTableIfNecessary();
				$this->finally();
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
		 * Executes any post-transaction operations regardless of the success or failure of the transaction.
		 * @throws Throwable
		 */
		protected function finally(): void
		{
		}
		
		/**
		 * Reverts all side effects in the transaction.
		 * @throws Throwable
		 */
		private function revertSideEffects(): void
		{
			while($sideEffect = array_pop($this->sideEffects))
				$sideEffect->revert();
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
		 * Throws the exception after all processes have reverted. If the exception needs to be handled in
		 * another way, this can be overriden.
		 * @param Throwable $exception
		 * @return mixed
		 * @throws Throwable
		 */
		protected function throw(Throwable $exception)
		{
			throw $exception;
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
