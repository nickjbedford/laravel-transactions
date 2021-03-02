<?php
	/** @noinspection PhpMissingParamTypeInspection */
	/** @noinspection PhpMissingReturnTypeInspection */
	/** @noinspection PhpMissingFieldTypeInspection */
	
	namespace YetAnother\Laravel\Console\Commands;
	
	use Illuminate\Console\GeneratorCommand;
	
	class MakeTransactionResponderCommand extends GeneratorCommand
	{
	    /**
	     * The name and signature of the console command.
	     *
	     * @var string
	     */
		protected $name = 'make:responder';
		
		/**
	     * The console command description.
	     *
	     * @var string
	     */
		protected $description = 'Creates a new transaction responder class.';

	    /**
	     * The type of class being generated.
	     *
	     * @var string
	     */
	    protected $type = 'TransactionResponder';
	
	    /**
	     * Get the stub file for the generator.
	     *
	     * @return string
	     */
	    protected function getStub()
	    {
	        return __DIR__ . '/stubs/responder.stub';
	    }
	
	    /**
	     * Get the default namespace for the class.
	     *
	     * @param  string  $rootNamespace
	     * @return string
	     */
	    protected function getDefaultNamespace($rootNamespace)
	    {
	        return $rootNamespace . '\Http\Responders';
	    }
	}
