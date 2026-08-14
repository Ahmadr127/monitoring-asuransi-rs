<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class ApprovalApproveRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * Lampiran wajib hanya jika step persetujuan saat ini membutuhkannya.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $step = $this->route('approval')?->currentStepRecord()->first();

        $attachmentRule = $step?->requires_attachment
            ? ['required', 'file', 'mimes:pdf', 'max:5120']
            : ['nullable', 'file', 'mimes:pdf', 'max:5120'];

        return [
            'catatan_keputusan' => ['nullable', 'string', 'max:500'],
            'attachment' => $attachmentRule,
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'attachment.required' => 'Lampiran PDF wajib diunggah untuk menyetujui langkah ini.',
            'attachment.mimes' => 'Lampiran harus berupa file PDF.',
            'attachment.max' => 'Ukuran lampiran maksimal 5MB.',
        ];
    }
}
