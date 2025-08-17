@props(['headers'])

<div class="overflow-x-auto shadow ring-1 ring-black ring-opacity-5 md:rounded-lg">
    <table class="min-w-full divide-y divide-gray-300">
        <thead class="bg-gray-50">
            <tr>
                @foreach($headers as $header)
                    <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">
                        {{ $header }}
                    </th>
                @endforeach
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-200 bg-white">
            {{ $slot }}
        </tbody>
    </table>
</div>


