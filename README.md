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

## Transaction Examples

The following is an example of a transaction that not only has to
create a new record in the database, it must also upload a file to
Amazon's S3 storage service.

In the case one or either processes fail, the database changes will
automatically be rolled back by the base class, but it is up to
the subclass to remove any external side effects, which in this case
means to delete the file from S3 if the database fails to update.

```php
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use YetAnother\Laravel\Transaction;
use App\Models\Attachment;

/**
 * Represents a transaction that must upload a file to a
 * storage destination and update the database.
 */
class UploadAttachmentTransaction extends Transaction
{
    private ?string $uploadedFilePath = null;
    private UploadedFile $file;
    
    public ?Attachment $model = null;
    
    public function __construct(UploadedFile $file)
    {
        $this->file = $file;
    }
    
    /**
     * Validates the action before it is performed.
     * @throws Throwable
     */
    protected function validate() : void
    {
        $extension = strtolower($this->file->getClientOriginalExtension());
        
        if (!in_array($extension, [ 'png', 'jpg', 'jpeg', 'gif' ]))
            throw new InvalidArgumentException('Uploaded file is not a valid file type.');
    }
    
    /**
     * Uploads the file to Amazon S3 then creates an
     * Attachment model in the database.
     * @throws Exception
     */
    protected function perform() : void
    {
        $this->uploadFileToS3();
        $this->createAttachment();
    }
    
    protected function uploadFileToS3(): void
    {
        $path = 'some/path/to/' . $this->file->getClientOriginalName();
        $s3 = Storage::disk('s3');
        
        if ($s3->put($this->file, $path))
            {$this->uploadedFilePath = $path;}
    }
    
    protected function createAttachment(): void
    {
        $this->model = Attachment::create([
            'disk' => 's3',
            'path' => $this->uploadedFilePath
        ]);
    }
    
    /**
     * Deletes the file from S3 if any processes afterwards
     * failed. The database transaction has already been rolled
     * back at this time. 
     */
    public function cleanupAfterFailure() : void
    {
        parent::cleanupAfterFailure();
        
        if ($this->uploadedFilePath)
        {
            $s3 = Storage::disk('s3');
            $s3->delete($this->uploadedFilePath);
        }
    }
}
```

### Transaction Event Firing

Transactions can additionally fire an event when they complete successfully.
To specify the type of event class to fire, override the `$event` property
as follows. The event will be fired after the database transaction has
committed and no exceptions have been thrown.

#### Define Transaction Event

```php
namespace App\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use YetAnother\Laravel\Transaction;

/**
 * This event is fired after the transaction completes.
 */
class TransactionComplete
{
    use Dispatchable, SerializesModels;
    
    public function __construct(Transaction $transaction)
    {
        //
    }
}
```

#### Specify Event Class To Fire

```php
use YetAnother\Laravel\Transaction;
use App\Events\TransactionComplete;

class EventFiringTransaction extends Transaction
{
    protected ?string $event = TransactionComplete::class; 
    
    protected function perform() : void
    {
        // 
    }
}
```

By default, this will try to pass the transaction instance to the
event's constructor if it accepts a parameter. If you wish to
create a custom event for dispatch, override the `createEventInstance()`
method as follows:

```php
use YetAnother\Laravel\Transaction;
use App\Events\TransactionComplete;

class EventFiringTransaction extends Transaction
{
    protected function perform() : void
    {
        // 
    }
    
    /**
     * Create a custom event to dispatch. 
     * @return mixed
     */
    protected function createEvent()
    {
        return new TransactionComplete($this);
    }
}
```
