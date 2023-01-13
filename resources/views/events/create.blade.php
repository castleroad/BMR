<div class="appended-form" data-action="create">
    <!-- Create new event form start -->
    <form id="eventForm" action="{{ route('events.store') }}">
        <div class="bg-white px-4 pb-4 sm:p-6 sm:pb-4">
            <div class="sm:items-start">
                <div class="sm:flex sm:flex-wrap mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                    <div class="w-full">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="closeModal close-cross w-6 h-6">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                      
                        <h3 class="text-lg leading-6 font-medium text-gray-900">Create new event</h3>
                    </div>
                    @csrf
                    <div class="w-full mt-3 mb-3">
                        <x-input-label value="Event title" />
                        <x-text-input type="text" name="title" placeholder="Maximum 28 symbols" maxLength="28" minLength="2"
                            value="{{ old('title') ?? 'Title' }}" class="" />
                        <x-input-label id="title" value="" class="error text-sm text-red-600 space-y-1" />
                    </div>

                    <div class="w-1/2">
                        <x-input-label value="Event start date" />
                        <x-text-input type="date" format="m/d/Y" name="starts_at" value="{{ old('starts_at') }}" class="" />
                        <x-input-label id="starts_at" value="" class="error text-sm text-red-600 space-y-1" />
                    </div>

                    <div class="w-1/2">
                        <x-input-label value="Event end date" />
                        <x-text-input type="date" format="m/d/Y" name="ends_at" value="{{ old('ends_at') }}" class="" />
                        <x-input-label id="ends_at" value="" class="error text-sm text-red-600 space-y-1" />
                    </div>

                    <div class="w-full mt-3 mb-3">
                        <x-input-label value="Is all day event?" />
                        <x-text-input id="allDayBox" type="checkbox" name="all_day" value="{{ old('all_day') }}"
                            class="" />
                    </div>

                    <div class="time-block flex flex-wrap w-full">
                        <div class="w-1/2">
                            <x-input-label value="Event time from" />
                            <x-text-input type="time" name="time_from" value="{{ old('time_from') }}"
                                class="" />
                            <x-input-label id="time_from" value="" class="error text-sm text-red-600 space-y-1" />
                        </div>

                        <div class="w-1/2">
                            <x-input-label value="Event time to" />
                            <x-text-input type="time" name="time_to" value="{{ old('time_to') }}" class="" />
                            <x-input-label id="time_to" value="" class="error text-sm text-red-600 space-y-1" />
                        </div>
                    </div>
                    <div class="w-full mt-3 mb-3">
                        <h3>Invite somebody:</h3>
                    </div>
                    <div class="w-full">
                            <div class="dropdown-mul-1 dropdown-multiple-label">
                                <select style="display:none" multiple="" placeholder="Select">
                                    @foreach ($users as $user)
                                        <option value="{{ $user->id }}" >{{ $user->fullName() }}</option>
                                    @endforeach 
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
                                                <main>
                                                    <div class="menu">
                                                        @foreach ($statuses as $index => $title)
                                                            @if ($index != 0)
                                                                <a class="attendee-status-button"  data-id="{{ $owner->id }}" data-value="{{ $index }}">{{ $title }}</a>
                                                            @endif
                                                        @endforeach
                                                    </div>
                                                </main>
                                            </td>
                                            <td>
                                                <span>{{ __('Owner') }}</span>
                                            </td>
                                            <td>
                                                <span class="btn btn-danger" onclick="removeAttendeeAllTr();">{{ __('Delete all') }}</span>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                    </div>

                    <div type="hidden">
                        @foreach ($users as $user)
                            <input type="hidden" id="avatars{{ $user->id }}" value="{{ $user->avatarUrl() }}" />
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
            <x-primary-button class="mt-2 mb-2">
                {{ __('Create event') }}
            </x-primary-button>
            <button type="button" class="closeModal mt-2 mb-2 mr-8">{{ __('Cancel') }}</button>
        </div>
    </form>
    <!-- Create new event form end -->
</div>
