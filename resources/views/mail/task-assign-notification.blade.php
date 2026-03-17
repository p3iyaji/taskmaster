<x-mail::message>

    # {{ $subject ?? 'You have been assigned a new task' }}

    {{ $task->title }} has been assigned to you.

    {{ $task->description }}

    <x-mail::button :url="''">
        Button Text
    </x-mail::button>

    Thanks,<br>
    {{ config('app.name') }}
</x-mail::message>