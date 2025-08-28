<!DOCTYPE html>
<html>
<head>
    <title>Minimal Users Test</title>
</head>
<body>
    <h1>MINIMAL USERS TEST</h1>
    
    <p>If you see this, basic view rendering works.</p>
    
    <h2>Users Count: {{ is_object($users) && method_exists($users, 'count') ? $users->count() : count($users) }}</h2>

    <h3>Users List:</h3>
    <ul>
        @if(is_object($users) && method_exists($users, 'count'))
            @foreach($users as $user)
                <li>{{ $user->name }} - {{ $user->email }} - {{ $user->role }}</li>
            @endforeach
        @else
            <li>No users or invalid data</li>
        @endif
    </ul>

    <h3>Pagination:</h3>
    <p>Total: {{ is_object($users) && method_exists($users, 'total') ? $users->total() : 'N/A' }}</p>
    
    <h3>Auth User:</h3>
    <p>Name: {{ auth()->user()->name }}</p>
    <p>Role: {{ auth()->user()->role }}</p>
    <p>Is Admin: {{ auth()->user()->isAdmin() ? 'YES' : 'NO' }}</p>
</body>
</html>
