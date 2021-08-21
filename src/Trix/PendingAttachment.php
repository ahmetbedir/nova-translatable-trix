<?php
namespace Ahmetbedir\NovaTranslatableTrix\Trix;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Ahmetbedir\NovaTranslatableTrix\TranslatableTrix;

class PendingAttachment extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'nova_pending_trix_attachments';

    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = [];

    /**
     * Persist the given draft's pending attachments.
     *
     * @param  string  $draftId
     * @param  \Ahmetbedir\NovaTranslatableTrix\TranslatableTrix  $field
     * @param  mixed  $model
     * @return void
     */
    public static function persistDraft($draftId, TranslatableTrix $field, $model)
    {
        static::where('draft_id', $draftId)->get()->each->persist($field, $model);
    }

    /**
     * Persist the pending attachment.
     *
     * @param  \Ahmetbedir\NovaTranslatableTrix\NovaTranslatableTrix  $field
     * @param  mixed  $model
     * @return void
     */
    public function persist(TranslatableTrix $field, $model)
    {
        $disk = $field->getStorageDisk();

        Attachment::create([
            'attachable_type' => $model->getMorphClass(),
            'attachable_id' => $model->getKey(),
            'attachment' => $this->attachment,
            'disk' => $disk,
            'url' => Storage::disk($disk)->url($this->attachment),
        ]);

        $this->delete();
    }

    /**
     * Purge the attachment.
     *
     * @return void
     */
    public function purge()
    {
        Storage::disk($this->disk)->delete($this->attachment);

        $this->delete();
    }
}
