<?php
	namespace YetAnother\Laravel\Http;
	
	use Illuminate\Contracts\Support\Responsable;
	use Illuminate\Http\Request;
	use Symfony\Component\HttpFoundation\Response;
	use Throwable;
	use YetAnother\Laravel\Transaction;
	
	/**
	 * Represents a responder that performs a transaction and returns a
	 * valid response. This enables a controller to return nothing more
	 * than an instance of the appropriate TransactionResponder subclass
	 * to Laravel's response handling.
	 * @package YetAnother\Laravel\Http
	 */
	abstract class TransactionResponder implements Responsable
	{
		private ?Transaction $transaction;
		private ?Response $response = null;

		public function __construct(?Transaction $transaction = null)
		{
			$this->transaction = $transaction;
		}
		
		/**
		 * @param mixed $request
		 * @return mixed
		 * @throws Throwable
		 */
		public function toResponse($request): Response
		{
			if ($this->response)
				return $this->response;

			try
			{
				$this->executeTransaction($request);
				return $this->response = $this->getResponseAfterExecution($this->transaction);
			}
			catch(Throwable $exception)
			{
				report($exception);
				return $this->response = $this->exceptionToResponse($exception);
			}
		}

		/**
		 * @param Transaction $transaction
		 * @return mixed
		 */
		abstract protected function getResponseAfterExecution(Transaction $transaction): Response;

		/**
		 * @param Request $request
		 * @return Transaction
		 */
		abstract protected function createTransaction(Request $request): Transaction;
		
		/**
		 * @param Throwable $exception
		 * @return Response
		 * @throws Throwable
		 */
		protected function exceptionToResponse(Throwable $exception): Response
		{
			throw $exception;
		}

		/**
		 * @param Request $request
		 * @throws Throwable
		 */
		private function executeTransaction(Request $request): void
		{
			$this->transaction ??= $this->createTransaction($request);
			$this->transaction->execute();
		}
	}
