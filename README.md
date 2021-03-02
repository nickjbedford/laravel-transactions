# Transactions for Laravel

Provides structure for building complicated transactions in
Laravel project that handle failure. By deriving from the
`YetAnother\Laravel\Transaction` abstract class and implementing
the `perform()` and optional `validate()`  and `cleanupAfterFailure()`
methods, you can write complicated transactional actions that
maintain integrity of state not only within the database, but
in external systems as well.

For example, when dealing with file uploads, your `Transaction`
subclass should maintain a list of successfully uploaded files
that can be deleted when the `cleanupAfterFailure()` method is
called by the base class in the case of an exception.

## Transaction Execution Process

When `execute()` is called, a Laravel `DB` transaction context
is established that will catch any thrown exceptions and roll back
the changes to the database. External cleanup is handled as well,
if implemented correctly.

Exceptions should be thrown by both the subclass' implementations
of the `validate()` and `perform()` methods to rollback and cleanup
the transactions changes.

### `protected function validate() { }`

This is an optional override and allows the transaction to separate
validation checks before the actual work is performed.

If an action or parameter is not valid, an `Throwable` of any type
should be thrown for the `execute()` method to catch and handle roll
back and cleanup of the transaction.

### `protected abstract function perform()`

This method must be overridden by subclasses to perform the changes
necessary. Database changes are committed or rolled back automatically,
however if an exception is thrown, external side effects must be cleaned
up by the `cleanupAfterFailure()` method.


### `protected function cleanupAfterFailure() { }`

To handled cleanup of transaction side effects such as file uploads
or changes to external services, this method should be overridden
to do so. The subclass should maintain a list of reversible actions
it may need to take, such as file paths to delete on failure.

## Example

```php
use Symfony\Component\HttpFoundation\File\File;
use Illuminate\Http\UploadedFile;
use YetAnother\Laravel\Transaction;

class FileUploadTransaction extends Transaction
{
    private ?File $uploadedFile = null;
    private UploadedFile $file;
    
    public function __construct(UploadedFile $file)
    {
        $this->file = $file;
    }
    
    protected function perform() : void
    {
        $this->uploadedFile = $this->file->move('some/path/to', 'uploaded.file');
        
        // update database regarding file upload
        
        throw new Exception('Something failed after uploading the file.');
    }
    
    public function cleanupAfterFailure() : void
    {
        parent::cleanupAfterFailure();
        
        if ($this->uploadedFile)
            unlink($this->uploadedFile->getPath());
    }
}
```
