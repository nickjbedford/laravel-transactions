<?php
	namespace YetAnother\Tests\Events;
	
	class TransactionFiredListener
	{
		function handle(TransactionFired $event)
		{
			$event->listenedTo = true;
		}
	}
