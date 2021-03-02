<?php
	/** @noinspection PhpUnhandledExceptionInspection */
	
	namespace YetAnother\Tests;
	
	use InvalidArgumentException;
	use YetAnother\Tests\Events\TransactionFired;
	use YetAnother\Tests\Transactions\BooleanTransaction;
	use YetAnother\Tests\Transactions\EventFiringTransaction;
	use YetAnother\Tests\Transactions\InvalidBooleanTransaction;
	
	class TransactionTests extends TestCase
	{
		function testBooleanTransactionSucceeds()
		{
			$transaction = new BooleanTransaction();
			$transaction->execute();
			
			$this->assertTrue($transaction->done);
		}
		
		function testValidatingBooleanTransactionSucceeds()
		{
			self::expectException(InvalidArgumentException::class);
			
			$transaction = new InvalidBooleanTransaction();
			$transaction->execute();
		}
		
		function testEventFiringTransactionFires()
		{
			TransactionFired::$instances = [];
			$transaction = new EventFiringTransaction();
			
			$transaction->execute();
			$event = TransactionFired::$instances[$transaction->eventKey] ?? null;
			
			$this->assertNotNull($event);
			$this->assertSame($event->transaction, $transaction);
			$this->assertTrue($event->listenedTo);
		}
	}
