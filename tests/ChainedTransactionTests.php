<?php
	/** @noinspection PhpUnhandledExceptionInspection */
	
	namespace YetAnother\Tests;
	
	use Exception;
	use Throwable;
	use YetAnother\Laravel\ChainedTransaction;
	use YetAnother\Laravel\ClosureTransaction;
	use YetAnother\Tests\Transactions\BooleanTransaction;
	use YetAnother\Tests\Transactions\ValueChangeTransaction;
	
	class ChainedTransactionTests extends TestCase
	{
		function testChainedTransactionExecutesCorrectly()
		{
			$bool = new BooleanTransaction();
			$value = null;
			$self = $this;
			
			$this->assertFalse($bool->done);
			
			$transaction = (new ChainedTransaction())
				->add($bool)
				->add(function(array $transactions) use(&$value, $self)
				{
					/** @var BooleanTransaction $bool */
					$bool = $transactions[0];
					$self->assertTrue($bool->done);
					return $value = new ValueChangeTransaction('Hello', 'Goodbye');
				})
				->add([
					$bool2 = new BooleanTransaction(),
					$bool3 = new BooleanTransaction()
				])
				->add(collect([
					$value2 = new ValueChangeTransaction(1, 'Done')
				]))
				->add(function()
				{
					return new ValueChangeTransaction('Hello', 'Goodbye');
				});
			
			$this->assertFalse($bool->done);
			$this->assertNull($value);
			$this->assertFalse($bool2->done);
			$this->assertFalse($bool3->done);
			$this->assertEquals(1, $value2->value);
			
			$transaction->execute();
			
			$this->assertCount(6, $transaction->transactions);
			$this->assertTrue($bool->done);
			$this->assertNotNull($value);
			$this->assertEquals('Goodbye', $transaction->transactions[1]->value);
			$this->assertTrue($transaction->transactions[2]->done);
			$this->assertTrue($transaction->transactions[3]->done);
			$this->assertEquals('Done', $transaction->transactions[4]->value);
		}
		
		function testChainedTransactionCleanupOccurs()
		{
			$fail = false;
			$a = 'Hello';
			$b = 'Toyota';
			$rolledBack = 0;
			
			$t1 = ClosureTransaction::newWithRollback(function() use(&$a)
			{
				$a = 'Goodbye';
			}, function() use(&$a, &$rolledBack)
			{
				$a = 'Hello';
				$rolledBack++;
			});
			
			$t2 = ClosureTransaction::newWithRollback(function() use(&$b, &$fail)
			{
				$b = 'Holden';
				if ($fail)
					throw new Exception('');
			}, function() use(&$b, &$rolledBack)
			{
				$b = 'Toyota';
				$rolledBack++;
			});
			
			$transaction = (new ChainedTransaction())
				->add($t1)
				->add($t2);
			
			$this->assertEquals('Hello', $a);
			$this->assertEquals('Toyota', $b);
			
			$transaction->execute();
			
			$this->assertEquals('Goodbye', $a);
			$this->assertEquals('Holden', $b);
			$this->assertEquals(0, $rolledBack);
			
			$fail = true;
			
			try
			{
				$a = 'Not Hello';
				$b = 'Not Toyota';
			
				$transaction = (new ChainedTransaction())
					->add($t1)
					->add($t2);
				
				$transaction->execute();
			}
			catch(Throwable $exception)
			{
			}
			
			$this->assertEquals('Hello', $a);
			$this->assertEquals('Toyota', $b);
			$this->assertEquals(2, $rolledBack);
		}
	}
