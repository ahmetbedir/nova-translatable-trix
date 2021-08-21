<?php
namespace Ahmetbedir\NovaTranslatableTrix\Trix;

use Illuminate\Http\Request;

class DeleteAttachments
{
    /**
     * The field instance.
     *
     * @var \Ahmetbedir\NovaTranslatableTrix\TranslatableTrix
     */
    public $field;

    /**
     * Create a new class instance.
     *
     * @param  \Ahmetbedir\NovaTranslatableTrix\TranslatableTrix  $field
     * @return void
     */
    public function __construct($field)
    {
        $this->field = $field;
    }

    /**
     * Delete the attachments associated with the field.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  mixed  $model
     * @return array
     */
    public function __invoke(Request $request, $model)
    {
        Attachment::where('attachable_type', $model->getMorphClass())
            ->where('attachable_id', $model->getKey())
            ->get()
            ->each
            ->purge();

        return [$this->field->attribute => ''];
    }
}
