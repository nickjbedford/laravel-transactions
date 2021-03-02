<?php
	/** @noinspection PhpMissingParamTypeInspection */
	/** @noinspection PhpMissingReturnTypeInspection */
	/** @noinspection PhpMissingFieldTypeInspection */
	
	namespace YetAnother\Laravel\Console\Commands;
	
	use Illuminate\Console\GeneratorCommand;
	
	class MakeTransactionCommand extends GeneratorCommand
	{
	    /**
	     * The name and signature of the console command.
	     *
	     * @var string
	     */
		protected $name = 'make:transaction';
		
		/**
	     * The console command description.
	     *
	     * @var string
	     */
		protected $description = 'Creates a new transaction class.';

	    /**
	     * The type of class being generated.
	     *
	     * @var string
	     */
	    protected $type = 'Transaction';
	
	    /**
	     * Get the stub file for the generator.
	     *
	     * @return string
	     */
	    protected function getStub()
	    {
	        return __DIR__ . '/stubs/transaction.stub';
	    }
	
	    /**
	     * Get the default namespace for the class.
	     *
	     * @param  string  $rootNamespace
	     * @return string
	     */
	    protected function getDefaultNamespace($rootNamespace)
	    {
	        return $rootNamespace . '\Transactions';
	    }
	}
