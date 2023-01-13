<?php

namespace App\Http\Requests\Event;

use Carbon\Carbon;
use App\Models\EventUser;
use Illuminate\Foundation\Http\FormRequest;
use Carbon\Exceptions\InvalidFormatException;

class StoreRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }
    
    /**
     * Operating with events all_day checkbox form input
     *
     * @return void
     */
    protected function prepareForValidation()
    {
        $requestData = $this->all();
        
        $requestData['attendees'][$this->user()->id]['permission'] = EventUser::PERMISSION_OWN;
        $requestData['all_day'] = array_key_exists('all_day', $requestData) ? 1 : 0;

        $this->merge([
            'all_day' => $requestData['all_day'],
            'attendees' => $requestData['attendees'],
        ]);
    }
    
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        $rules = [
            'attendees.*' => ['array'],
            'attendees.*.status' => ['integer', 'max:3'],
            'attendees.*.permission' => ['integer', 'max:2'],
            'title' => ['required', 'string', 'min:2', 'max:28'],
            'starts_at' => [
                'required',
                'date',
                'after:'.now()->subYear()->toDateString(),
                'before:'.now()->subYears(-2)->toDateString(),
            ],
            'ends_at' => [
                'required',
                'date',
                'after:starts_at',
                'before:'.now()->subYears(-2)->toDateString(),
            ],
            'all_day' => ['integer', 'max:1'],
            'time_from' => ['required_if:all_day,0'],
            'time_to' => ['required_if:all_day,0', 'after:time_from'],
        ];
        
        if ($this->starts_at) {
            $rules['ends_at'][] = 'before:'.Carbon::createFromFormat('Y-m-d', $this->starts_at)->subDays(-20);
        }

        if ($this->all_day == 1) {
            $rules = array_splice($rules, 0, -2);
        }

        return $rules;
    }
    
    /**
     * Get the validation messages that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function messages()
    {
        return [
            'time_from.required_if' => 'The :attribute is required when it\'s not an all day event.',
            'time_to.required_if' => 'The :attribute is required when it\'s not an all day event.',
            'time_to.after' => 'The :attribute must be after :date.',
        ];
    }
}
