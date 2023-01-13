@foreach ($attendees as $attendee)
    <tr id="attendee{{ $attendee->id }}tr">
        <td>
            <img class="rounded-full" style="width: 64px;" src="{{ $attendee->avatarURl() }}">
        </td>
        <td>
            <span>{{ $attendee->fullName() }}</span>
        </td>
        <td>
            @foreach ($statuses as $key => $title)
                @if ($key == $attendee->events->first()->pivot->status)
                    <span>{{ $title }}</span>
                @endif
            @endforeach
        </td>
        <td>
            @foreach ($permissions as $key => $title)
                @if ($key == $attendee->events->first()->pivot->permission)
                    <span>{{ $title }}</span>
                @endif
            @endforeach
        </td>
    </tr>
@endforeach
