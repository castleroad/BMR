<?php

namespace App\Http\Requests\Api\V1\Event;

use Carbon\Carbon;
use App\Models\EventUser;
use Illuminate\Foundation\Http\FormRequest;

class UpdateRequest extends FormRequest
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
        
        $this->merge([
            'starts_at' => $requestData['startsAt'],
            'ends_at' => $requestData['endsAt'],
            'time_from' => $requestData['timeFrom'],
            'time_to' => $requestData['timeTo'],
            'all_day' => $requestData['allDay'],
        ]);
    }
    
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        if ($this->method() == 'PUT') {

            $rules = [
                'attendees.*' => ['required', 'array'],
                'attendees.*.userId' => ['required', 'integer'],
                'attendees.*.attributes' => ['required', 'array'],
                'attendees.*.attributes.status' => ['required', 'integer', 'max:3'],
                'attendees.*.attributes.permission' => ['required', 'integer', 'max:2'],
                'title' => ['required', 'string', 'min:2', 'max:28'],
                'starts_at' => ['required', 'date',
                    'after:'.now()->subYear()->toDateString(),
                    'before:'.now()->subYears(-2)->toDateString(),
                ],
                'ends_at' => ['required', 'date',
                    'after:starts_at',
                    'before:'.now()->subYears(-2)->toDateString(),
                ],
                'all_day' => ['required', 'integer', 'max:1'],
                'time_from' => ['required_if:all_day,0'],
                'time_to' => ['required_if:all_day,0', 'after:time_from'],
            ];

            if ($this->all_day == 1) {
                $rules = array_splice($rules, 0, -2);
            }

        } else {

            $rules = [
                'attendees.*' => ['array'],
                'attendees.*.userId' => ['required', 'integer'],
                'attendees.*.attributes' => ['required', 'array'],
                'attendees.*.attributes.status' => ['integer', 'max:3'],
                'attendees.*.attributes.permission' => ['integer', 'max:2'],
                'title' => ['string', 'min:2', 'max:28'],
                'starts_at' => ['date',
                    'after:'.now()->subYear()->toDateString(),
                    'before:'.now()->subYears(-2)->toDateString(),
                ],
                'ends_at' => ['date',
                    'after:starts_at',
                    'before:'.now()->subYears(-2)->toDateString(),
                ],
                'all_day' => ['integer', 'max:1'],
                'time_from' => ['required_if:all_day,0'],
                'time_to' => ['required_if:all_day,0', 'after:time_from'],
            ];

        }
            
        if ($this->starts_at) {
            $rules['ends_at'][] = 'before:'.Carbon::createFromFormat('m/d/Y', $this->starts_at)->subDays(-20);
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
