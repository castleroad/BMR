<div class="appended-form" data-action="edit" >
    <div id="appended-info" type="hidden"
        data-requser="{{ $requestUser->id }}"></div>
    <!-- Edit event form start -->
    <form id="eventForm" action="{{ route('events.update', $event) }}">
        <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
            <div class="sm:items-start">
                <div class="sm:flex sm:flex-wrap mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                    <div class="w-full">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="closeModal close-cross w-6 h-6">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                      
                        <h3 class="text-lg leading-6 font-medium text-gray-900">Edit event</h3>
                    </div>
                    @csrf
                    @method('put')

                    <div class="w-full mt-3 mb-3">
                        <x-input-label value="Event title" />
                        <x-text-input type="text" name="title" placeholder="Maximum 28 symbols" maxLength="28" minLength="2"
                            value="{{ old('title', $event->title) }}" class="" />
                        <x-input-label id="title" value="" class="error text-sm text-red-600 space-y-1" />
                    </div>

                    <div class="w-1/2">
                        <x-input-label value="Event start date" />
                        <x-text-input type="date" format="m/d/Y" name="starts_at" value="{{ old('starts_at', $event->starts_at->format('Y-m-d')) }}"
                            class="" />
                        <x-input-label id="starts_at" value="" class="error text-sm text-red-600 space-y-1" />
                    </div>

                    <div class="w-1/2">
                        <x-input-label value="Event end date" />
                        <x-text-input type="date" format="m/d/Y" name="ends_at" value="{{ old('ends_at', $event->ends_at->format('Y-m-d')) }}"
                            class="" />
                        <x-input-label id="ends_at" value="" class="error text-sm text-red-600 space-y-1" />
                    </div>

                    <div class="w-full mt-3 mb-3">
                        <x-input-label value="Is all day event?" />
                        <x-text-input id="allDayBox" type="checkbox" name="all_day" value="{{ $event->all_day }}"
                            class="" />
                    </div>

                    <div class="time-block {{ $event->all_day ? 'invisible' : '' }} flex flex-wrap w-full">
                        <div class="w-1/2">
                            <x-input-label value="Event time from" />
                            <x-text-input type="time" name="time_from"
                                value="{{ old('time_from', $event->time_from) }}" class="" />
                            <x-input-label id="time_from" value="" class="error text-sm text-red-600 space-y-1" />
                        </div>

                        <div class="w-1/2">
                            <x-input-label value="Event time to" />
                            <x-text-input type="time" name="time_to" value="{{ old('time_to', $event->time_to) }}"
                                class="" />
                            <x-input-label id="time_to" value="" class="error text-sm text-red-600 space-y-1" />
                        </div>
                    </div>
                    <div class="w-full mt-3 mb-3">
                        <h3>Invite somebody:</h3>
                    </div>
                    <div class="w-full">
                            <div class="dropdown-mul-1 dropdown-multiple-label">
                                <select style="display:none" multiple="" placeholder="Select">
                                </select>
                            </div>
                            <h3 class="text-lg leading-6 font-medium text-gray-900 mt-3 mb-3">Attendees:</h3>
                            <div class="attendees-block">
                                <table class="w-full">
                                    <thead class="border-b">
                                        <tr>
                                            <th>{{ __('Avatar') }}</th>
                                            <th>{{ __('Name') }}</th>
                                            <th>{{ __('Status') }}</th>
                                            <th>{{ __('Permission') }}</th>
                                            <th>{{ __('Action') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr id="attendee{{ $owner->id }}tr">
                                            <td>
                                                <img class="rounded-full" style="width: 64px;" src="{{ $owner->avatarURl() }}">
                                            </td>
                                            <td>
                                                <span>{{ $owner->fullName() }}</span>
                                            </td>
                                            <td>
                                                @if ($requestUser->id == $owner->id)
                                                    <main>
                                                        <div class="menu">
                                                            @foreach ($statuses as $index => $title)
                                                                @if ($index != 0)
                                                                    @if ($owner->pivot->status == $index)
                                                                        <a class="attendee-status-button selected" data-id="{{ $owner->id }}" data-value="{{ $index }}">{{ $title }}</a>
                                                                        <input type="hidden" id="selected-user-status" name="attendees[{{ $owner->id }}][status]" value="{{ $owner->pivot->status }}"/>
                                                                    @else
                                                                        <a class="attendee-status-button"  data-id="{{ $owner->id }}" data-value="{{ $index }}">{{ $title }}</a>
                                                                    @endif
                                                                @endif
                                                            @endforeach
                                                        </div>
                                                    </main>
                                                @else
                                                    <span>{{ $owner->statusTitle() }}</span>
                                                @endif
                                            </td>
                                            <td>
                                                <span>{{ __('Owner') }}</span>
                                            </td>
                                            <td>
                                                @if ($requestUser->id == $owner->id)
                                                    <span class="btn btn-danger" onclick="removeAttendeeAllTr();">{{ __('Delete all') }}</span>
                                                @endif
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
            <x-primary-button class="mt-2 mb-2">{{ __('Save') }}</x-primary-button>
            <button type="button" class="closeModal mt-2 mb-2 mr-8">Cancel</button>
        </div>
    </form>
    <!-- Edit event form end -->

    <!-- Edit event form scripts start -->
    <script type="text/javascript">
        if(!$('.dropdown-display-label').length) {
            $('.dropdown-mul-1').dropdown({
                data: JSON.parse('{!! $jsonAttendees !!}'),
                multipleMode: 'label',
                choice: function () {
                    if (arguments) {
                        multiSelectDataToAttendees(arguments[1]);
                    }
                }
            });
        }

        if ($('#allDayBox')[0].value == 1) {
            $('#allDayBox').prop('checked', true);
        }
    </script>
    <!-- Edit event form scripts end -->
</div>
