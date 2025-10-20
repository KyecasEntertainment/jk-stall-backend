<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>AnyDesk Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <script>
        // Toggle show/hide for the AnyDesk ID
        function toggleVisibility(index) {
            const span = document.getElementById(`id-${index}`);
            const btn = document.getElementById(`btn-${index}`);
            const isMasked = span.dataset.masked === 'true';

            if (isMasked) {
                span.textContent = span.dataset.real;
                span.dataset.masked = 'false';
                btn.textContent = 'Hide ID';
            } else {
                span.textContent = '******';
                span.dataset.masked = 'true';
                btn.textContent = 'Show ID';
            }
        }

        // Open AnyDesk using stored ID
        function openAnyDesk(index) {
            const span = document.getElementById(`id-${index}`);
            const id = span.dataset.real;
            window.location.href = 'anydesk:' + id;
        }

        // Delete a PC record
        function deletePC(id, rowId) {
            if (!confirm('Are you sure you want to delete this PC record?')) return;

            fetch(`/api/moreventures/delete/${id}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Content-Type': 'application/json',
                },
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    document.getElementById(rowId).remove();
                    alert('PC deleted successfully.');
                } else {
                    alert('Failed to delete PC.');
                }
            })
            .catch(err => console.error(err));
        }
    </script>
</head>

<body class="bg-gray-100 text-gray-900">
    <div class="max-w-5xl mx-auto py-10">
        <h1 class="text-3xl font-bold mb-6 text-center">📺 AnyDesk Monitoring Dashboard</h1>
        {{-- ✅ Add New PC Form --}}
        <div class="bg-white shadow-md rounded-lg p-6 mb-6">
            <h2 class="text-xl font-semibold mb-4">➕ Add New PC</h2>

            @if(session('success'))
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-2 rounded mb-4">
                    {{ session('success') }}
                </div>
            @endif

            <form method="POST" action="{{ route('moreventures.submit-anydesk-id') }}" class="space-y-4">
                @csrf
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">PC Name / Info</label>
                        <input type="text" name="pc_info" required
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-red-500 focus:border-red-500 p-2"
                               placeholder="Example: Front Desk PC">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">AnyDesk ID / Alias</label>
                        <input type="text" name="anydesk_id" required
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-red-500 focus:border-red-500 p-2"
                               placeholder="Example: 123 456 789">
                    </div>
                </div>
                <div class="text-right">
                    <button type="submit"
                            class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-md transition">
                        Save PC
                    </button>
                </div>
            </form>
        </div>

        {{-- 📋 PC List Table --}}
        <table class="min-w-full bg-white shadow-lg rounded-lg overflow-hidden">
            <thead class="bg-red-600 text-white">
                <tr>
                    <th class="px-6 py-3 text-left">PC Name</th>
                    <th class="px-6 py-3 text-left">AnyDesk ID / Alias</th>
                    <th class="px-6 py-3 text-left">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($pcs as $index => $pc)
                    <tr id="row-{{ $index }}" class="border-b hover:bg-gray-50 transition">
                        <td class="px-6 py-4 font-medium">{{ $pc->name }}</td>
                        <td class="px-6 py-4 text-gray-700">
                            <span id="id-{{ $index }}"
                                  data-real="{{ $pc->anydesk_id }}"
                                  data-masked="true"
                                  class="select-none">
                                ******
                            </span>
                            <button id="btn-{{ $index }}"
                                    onclick="toggleVisibility('{{ $index }}')"
                                    class="ml-2 text-sm text-blue-600 hover:underline focus:outline-none">
                                Show ID
                            </button>
                        </td>
                        <td class="px-6 py-4 space-x-2">
                            <button onclick="openAnyDesk('{{ $index }}')"
                                    class="bg-red-500 hover:bg-red-700 text-white px-4 py-2 rounded-lg transition">
                                Connect
                            </button>

                            <button onclick="deletePC('{{ $pc->id }}', 'row-{{ $index }}')"
                                    class="bg-gray-400 hover:bg-gray-600 text-white px-4 py-2 rounded-lg transition">
                                Delete
                            </button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="3" class="text-center text-gray-500 py-6">
                            No PC records found.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</body>
</html>
