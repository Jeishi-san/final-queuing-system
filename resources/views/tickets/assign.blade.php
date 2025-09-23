<h2 class="text-xl font-bold mb-4">Assign IT Personnel</h2>
<form method="POST" action="{{ route('tickets.update', $ticket) }}">
    @csrf
    @method('PATCH')
    <input type="text" name="it_personnel_name" value="{{ $ticket->it_personnel_name }}"
           placeholder="Enter IT personnel name"
           class="border rounded p-2 w-full">
    <button type="submit" class="mt-4 px-4 py-2 bg-green-500 text-white rounded">Assign</button>
</form>
