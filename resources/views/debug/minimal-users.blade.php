<!DOCTYPE html>
<html>
<head>
    <title>Minimal Users Test</title>
</head>
<body>
    <h1>MINIMAL USERS TEST</h1>
    
    <p>If you see this, basic view rendering works.</p>
    
    <h2>Users Count: {{ $users->count() }}</h2>
    
    <h3>Users List:</h3>
    <ul>
        @foreach($users as $user)
            <li>{{ $user->name }} - {{ $user->email }} - {{ $user->role }}</li>
        @endforeach
    </ul>
    
    <h3>Pagination:</h3>
    <p>Total: {{ $users->total() }}</p>
    
    <h3>Auth User:</h3>
    <p>Name: {{ auth()->user()->name }}</p>
    <p>Role: {{ auth()->user()->role }}</p>
    <p>Is Admin: {{ auth()->user()->isAdmin() ? 'YES' : 'NO' }}</p>
</body>
</html>
