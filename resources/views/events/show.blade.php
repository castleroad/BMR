<div class="appended-form">
    <!-- View event start -->
    <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
        <div class="sm:items-start">
            <div class="sm:flex sm:flex-wrap mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                <div class="w-full">
                    <x-dropdown>
                        <x-slot name="trigger">
                            <button>
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-gray-400" viewBox="0 0 20 20"
                                    fill="currentColor">
                                    <path
                                        d="M6 10a2 2 0 11-4 0 2 2 0 014 0zM12 10a2 2 0 11-4 0 2 2 0 014 0zM16 12a2 2 0 100-4 2 2 0 000 4z" />
                                </svg>
                            </button>
                        </x-slot>
                        <x-slot name="content">
                            <x-dropdown-link data-url="{{ route('events.edit', $event) }}" data-id="{{ $event->id }}"
                                class="openCreateEditViewModal">
                                {{ __('Edit') }}
                            </x-dropdown-link>
                            @if ($isOwner)
                                <x-dropdown-link data-url="{{ route('events.destroy', $event) }}"
                                    data-title="{{ $event->title }}" class="deleteEvent">
                                    {{ __('Delete') }}
                                </x-dropdown-link>
                            @else
                                <x-dropdown-link data-url="{{ route('events.detach', $event) }}"
                                    data-title="{{ $event->title }}" class="detachEvent">
                                    {{ __('Remove me') }}
                                </x-dropdown-link>
                            @endif
                        </x-slot>
                    </x-dropdown>
                    <h3 class="text-lg leading-6 font-medium text-gray-900"> View event </h3>
                </div>

                <div class="w-full">
                    <x-input-label value="Event title" />
                    <x-text-input type="text" name="title" placeholder="Maximum 28 symbols"
                        value="{{ $event->title }}" class="" disabled />
                </div>

                <div class="w-1/2">
                    <x-input-label value="Event start date" />
                    <x-text-input type="date" format="m/d/Y" name="starts_at" value="{{ $event->starts_at->format('Y-m-d') }}"
                        class="" disabled />
                </div>

                <div class="w-1/2">
                    <x-input-label value="Event end date" />
                    <x-text-input type="date" format="m/d/Y" name="ends_at" value="{{ $event->ends_at->format('Y-m-d') }}"
                        class="" disabled />
                </div>

                <div class="w-full">
                    <x-input-label value="Is all day event?" />
                    <x-text-input id="allDayBox" type="checkbox" name="all_day" value="{{ $event->all_day }}"
                        class="" disabled />
                </div>

                <div class="time-block {{ $event->all_day ? 'invisible' : '' }} flex flex-wrap w-full">
                    <div class="w-1/2">
                        <x-input-label value="Event time from" />
                        <x-text-input type="time" name="time_from" value="{{ $event->time_from }}" class=""
                            disabled />
                    </div>

                    <div class="w-1/2">
                        <x-input-label value="Event time to" />
                        <x-text-input type="time" name="time_to" value="{{ $event->time_to }}" class=""
                            disabled />
                    </div>
                </div>
                <div class="w-full mt-3 mb-3">
                    <h3 class="text-lg leading-6 font-medium text-gray-900 mt-3 mb-3">Attendees:</h3>
                </div>
                <div class="w-full">
                    <div class="attendees-block">
                        <div class="table-wrapper">
                            <table class="w-full">
                                <thead class="border-b">
                                    <tr>
                                        <th>{{ __('Avatar') }}</th>
                                        <th>{{ __('Name') }}</th>
                                        <th>{{ __('Status') }}</th>
                                        <th>{{ __('Permission') }}</th>
                                    </tr>
                                </thead>
                                <tbody class="paginate-data" data-url="{{ route('users.paginate', $event) }}">
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

            </div>

            <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                <button type="button" class="closeModal mt-2 mb-2 mr-8">Cancel</button>
            </div>
        </div>
    </div>
    <!-- View event modal end -->

    <!-- View event form scripts start -->
    <script type="text/javascript">
        if ($('#allDayBox')[0].value == 1) {
            $('#allDayBox').prop('checked', true);
        }

        $.ajax({
            url: $('.paginate-data')[0].dataset.url + '?page=1',
            type: "GET",
        })
        .done(function(data) {
            $('.paginate-data').append(data.attendees);
        })
        .fail(function(thrownError) {
            // console.log(thrownError);
        })
  
        usersPaginateOnScroll();
        
    </script>
    <!-- View event form scripts end -->
</div>
