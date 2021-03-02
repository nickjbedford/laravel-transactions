<?php
	/** @noinspection PhpUndefinedFieldInspection */
	
	namespace YetAnother\Tests\Responders;
	
	use Illuminate\Http\JsonResponse;
	use Illuminate\Http\Request;
	use Symfony\Component\HttpFoundation\Response;
	use YetAnother\Laravel\Http\TransactionResponder;
	use YetAnother\Laravel\Transaction;
	use YetAnother\Tests\Transactions\ValueTransaction;
	
	class RequestValueTransactionResponder extends TransactionResponder
	{
		protected function getResponseAfterExecution(Transaction $transaction): Response
		{
			/** @var ValueTransaction $transaction */
			return new JsonResponse([
				'value' => $transaction->value
			]);
		}
		
		protected function createTransaction(Request $request): Transaction
		{
			return new ValueTransaction($request->value);
		}
	}
