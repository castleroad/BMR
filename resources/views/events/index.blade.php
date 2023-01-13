<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Events page') }}

            <x-primary-button class="float-right openCreateEditViewModal" data-url="{{ route('events.create') }}">
                {{ __('Add event') }}
            </x-primary-button>
        </h2>
    </x-slot>
    <div class="flex flex-col">
        <div class="overflow sm:-mx-6 lg:-mx-8">
            <div class="py-2 inline-block min-w-full sm:px-6 lg:px-8">
                <div class="">
                    @if (count($userEvents))
                        <!-- Events list start-->
                        <table class="min-w-full">
                            <thead class="bg-white border-b">
                                <tr>
                                    <th scope="col" class="text-sm font-medium text-gray-900 px-6 py-4 text-left">
                                        №
                                    </th>
                                    <th scope="col" class="text-sm font-medium text-gray-900 px-6 py-4 text-left">
                                        Title
                                    </th>
                                    <th scope="col" class="text-sm font-medium text-gray-900 px-6 py-4 text-left">
                                        Date
                                    </th>
                                    <th scope="col" class="text-sm font-medium text-gray-900 px-6 py-4 text-left">
                                        Time
                                    </th>
                                    <th scope="col" class="text-sm font-medium text-gray-900 px-6 py-4 text-left">
                                        Permission
                                    </th>
                                    <th scope="col" class="text-sm font-medium text-gray-900 px-6 py-4 text-left">
                                        Status
                                    </th>
                                    <th scope="col" class="text-sm font-medium text-gray-900 px-6 py-4 text-left">
                                        Edited
                                    </th>
                                    <th scope="col" class="text-sm font-medium text-gray-900 px-6 py-4 text-left">
                                        Action
                                    </th>
                                </tr>
                            </thead>
                            <tbody id="user-index-event-table">
                                @foreach ($userEvents as $event)
                                    <tr id="event-{{ $event->id }}" data-url="{{ route('events.show', $event) }}" data-id="{{ $event->id }}"
                                        class="openCreateEditViewModal bg-white border-b transition duration-300 ease-in-out hover:bg-gray-100">

                                        <td class="text-sm text-gray-900 font-light px-6 py-4 whitespace-nowrap">
                                            {{ $event->id }}
                                        </td>
                                        <td class="text-sm text-gray-900 font-light px-6 py-4 whitespace-nowrap">
                                            {{ $event->title }}
                                        </td>
                                        <td class="text-sm text-gray-900 font-light px-6 py-4 whitespace-nowrap">
                                            <p>From : {{ $event->starts_at->format('m/d/Y') }}</p>
                                            <p>To: {{ $event->ends_at->format('m/d/Y') }}</p>
                                        </td>
                                        <td class="text-sm text-gray-900 font-light px-6 py-4 whitespace-nowrap">
                                            @if ($event->all_day)
                                                All day Event
                                            @else
                                                <p>From: {{ $event->time_from }}</p>
                                                <p>To: {{ $event->time_to }}</p>
                                            @endif
                                        </td>
                                        <td class="text-sm text-gray-900 font-light px-6 py-4 whitespace-nowrap">
                                            {{ $event->permissionTitle() }}
                                        </td>
                                        <td class="text-sm text-gray-900 font-light px-6 py-4 whitespace-nowrap">
                                            {{ $event->statusTitle() }}
                                        </td>
                                        <td class="text-sm text-gray-900 font-light px-6 py-4 whitespace-nowrap">
                                            @if ($event->created_at->eq($event->updated_at))
                                                <small class="text-sm text-gray-600">{{ __('No') }}</small>
                                            @else
                                                <small class="text-sm text-gray-600">{{ __('edited') }}</small>
                                            @endif
                                        </td>
                                        <td onclick="event.stopPropagation();"
                                            class="text-sm text-gray-900 font-light px-6 py-4 whitespace-nowrap">
                                            <x-dropdown>
                                                <x-slot name="trigger">
                                                    <button>
                                                        <svg xmlns="http://www.w3.org/2000/svg"
                                                            class="h-4 w-4 text-gray-400" viewBox="0 0 20 20"
                                                            fill="currentColor">
                                                            <path
                                                                d="M6 10a2 2 0 11-4 0 2 2 0 014 0zM12 10a2 2 0 11-4 0 2 2 0 014 0zM16 12a2 2 0 100-4 2 2 0 000 4z" />
                                                        </svg>
                                                    </button>
                                                </x-slot>
                                                <x-slot name="content">
                                                    <x-dropdown-link data-url="{{ route('events.show', $event) }}"
                                                        data-id="{{ $event->id }}" class="openCreateEditViewModal">
                                                        {{ __('View') }}
                                                    </x-dropdown-link>
                                                    <x-dropdown-link data-url="{{ route('events.edit', $event) }}"
                                                        data-id="{{ $event->id }}" class="openCreateEditViewModal">
                                                        {{ __('Edit') }}
                                                    </x-dropdown-link>
                                                    @if (in_array($event->id, $userOwnerEventsIds))
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
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @else
                        <div class="py-8">
                            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                                    <div class="ml-16 text-gray-900">
                                        {{ __('There is no events yet!') }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
