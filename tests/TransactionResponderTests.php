<?php
	/** @noinspection PhpUnhandledExceptionInspection */
	
	namespace YetAnother\Tests;
	
	use Illuminate\Http\JsonResponse;
	use Illuminate\Http\Request;
	use YetAnother\Tests\Responders\RequestValueTransactionResponder;
	
	class TransactionResponderTests extends TestCase
	{
		function testBooleanTransactionResponderSucceeds()
		{
			$value = bin2hex(random_bytes(8));
			$request = new Request(compact('value'));
			$responder = new RequestValueTransactionResponder();
			
			/** @var JsonResponse $response */
			$response = $responder->toResponse($request);
			
			self::assertEquals($value, $response->getOriginalContent()['value']);
		}
	}
