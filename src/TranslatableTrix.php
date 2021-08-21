<?php
namespace Ahmetbedir\NovaTranslatableTrix;

use Laravel\Nova\Fields\Field;
use Laravel\Nova\Fields\Storable;
use Laravel\Nova\Fields\Deletable;
use Laravel\Nova\Fields\Expandable;
use Laravel\Nova\Http\Requests\NovaRequest;
use Laravel\Nova\Contracts\Storable as StorableContract;
use Ahmetbedir\NovaTranslatableTrix\Trix\DetachAttachment;
use Laravel\Nova\Contracts\Deletable as DeletableContract;
use Ahmetbedir\NovaTranslatableTrix\Trix\DeleteAttachments;
use Ahmetbedir\NovaTranslatableTrix\Trix\PendingAttachment;
use Ahmetbedir\NovaTranslatableTrix\Trix\StorePendingAttachment;
use Ahmetbedir\NovaTranslatableTrix\Trix\DiscardPendingAttachments;

class TranslatableTrix extends Field implements StorableContract, DeletableContract
{
    use Storable, Deletable, Expandable;
    /**
     * The field's component.
     *
     * @var string
     */
    public $component = 'translatable-trix';

    /**
     * Indicates if the element should be shown on the index view.
     *
     * @var bool
     */
    public $showOnIndex = false;

    /**
     * Indicates if the field should accept files.
     *
     * @var bool
     */
    public $withFiles = false;

    /**
     * The callback that should be executed to store file attachments.
     *
     * @var callable
     */
    public $attachCallback;

    /**
     * The callback that should be executed to delete persisted file attachments.
     *
     * @var callable
     */
    public $detachCallback;

    /**
     * The callback that should be executed to discard file attachments.
     *
     * @var callable
     */
    public $discardCallback;

    /**
     * Specify the callback that should be used to store file attachments.
     *
     * @param  callable  $callback
     * @return $this
     */
    public function attach(callable $callback)
    {
        $this->withFiles = true;

        $this->attachCallback = $callback;

        return $this;
    }

    /**
     * Specify the callback that should be used to delete a single, persisted file attachment.
     *
     * @param  callable  $callback
     * @return $this
     */
    public function detach(callable $callback)
    {
        $this->withFiles = true;

        $this->detachCallback = $callback;

        return $this;
    }

    /**
     * Specify the callback that should be used to discard pending file attachments.
     *
     * @param  callable  $callback
     * @return $this
     */
    public function discard(callable $callback)
    {
        $this->withFiles = true;

        $this->discardCallback = $callback;

        return $this;
    }

    /**
     * Specify the callback that should be used to delete the field.
     *
     * @param  callable  $deleteCallback
     * @return $this
     */
    public function delete(callable $deleteCallback)
    {
        $this->withFiles = true;

        $this->deleteCallback = $deleteCallback;

        return $this;
    }

    /**
     * Specify that file uploads should be allowed.
     *
     * @param  string  $disk
     * @param  string  $path
     * @return $this
     */
    public function withFiles($disk = null, $path = '/')
    {
        $this->withFiles = true;

        $this->disk($disk)->path($path);

        $this->attach(new StorePendingAttachment($this))
            ->detach(new DetachAttachment($this))
            ->delete(new DeleteAttachments($this))
            ->discard(new DiscardPendingAttachments($this))
            ->prunable();

        return $this;
    }

    /**
     * Hydrate the given attribute on the model based on the incoming request.
     *
     * @param  \Laravel\Nova\Http\Requests\NovaRequest  $request
     * @param  string  $requestAttribute
     * @param  object  $model
     * @param  string  $attribute
     * @return void|\Closure
     */
    protected function fillAttribute(NovaRequest $request, $requestAttribute, $model, $attribute)
    {
        $this->attribute = $requestAttribute = $attribute = $this->fixAttribute($this->attribute);

        $callbacks = [];

        $maybeCallback = parent::fillAttribute($request, $requestAttribute, $model, $attribute);
        if (is_callable($maybeCallback)) {
            $callbacks[] = $maybeCallback;
        }
        $draftIds = $request->get($this->attribute . 'DraftId');
        if ($draftIds && $this->withFiles) {
            foreach ($draftIds as $draftId) {
                $callbacks[] = function () use ($request, $model, $draftId) {
                    PendingAttachment::persistDraft(
                        $draftId,
                        $this,
                        $model
                    );
                };
            }
        }

        if (count($callbacks)) {
            return function () use ($callbacks) {
                collect($callbacks)->each->__invoke();
            };
        }
    }

    /**
     * @param $attribute
     */
    protected function fixAttribute($attribute)
    {
        return str_replace('.*', '', $attribute);
    }

    /**
     * Get the full path that the field is stored at on disk.
     *
     * @return string|null
     */
    public function getStoragePath() {}

    /**
     * Prepare the element for JSON serialization.
     *
     * @return array
     */
    public function jsonSerialize()
    {
        return array_merge(parent::jsonSerialize(), [
            'shouldShow' => $this->shouldBeExpanded(),
            'withFiles' => $this->withFiles,
        ]);
    }
}
