<?php
	/** @noinspection PhpUnhandledExceptionInspection */
	
	namespace YetAnother\Tests;
	
	use InvalidArgumentException;
	use Throwable;
	use YetAnother\Tests\Events\TransactionFired;
	use YetAnother\Tests\Transactions\BooleanTransaction;
	use YetAnother\Tests\Transactions\EventFiringTransaction;
	use YetAnother\Tests\Transactions\InvalidBooleanTransaction;
	use YetAnother\Tests\Transactions\SideEffectTransaction;
	
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
			$this->assertFalse($transaction->finally);
			
			$transaction->execute();
			$event = TransactionFired::$instances[$transaction->eventKey] ?? null;
			
			$this->assertNotNull($event);
			$this->assertSame($event->transaction, $transaction);
			$this->assertTrue($event->listenedTo);
			$this->assertTrue($transaction->finally);
		}
		
		function testSideEffectsAreReverted()
		{
			$transaction = new SideEffectTransaction();
			
			try
			{
				$transaction->execute();
			}
			catch(Throwable $exception)
			{
				$this->assertCount($count = count(SideEffectTransaction::ExpectedResult), $transaction->values);
				for($i = 0; $i < $count; $i++)
					$this->assertEquals(SideEffectTransaction::ExpectedResult[$i], $transaction->values[$i]);
			}
		}
	}
