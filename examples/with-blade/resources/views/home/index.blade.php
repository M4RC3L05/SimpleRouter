@extends("layouts.layout")

@section("title", "Index")

@section('content')
    <h1>Home</h1>
    <h2>User</h2>
    @if (isset($users) && count($users) > 0)
        <ul>
            @foreach ($users as $u)
                <li>{{$u}}</li>
            @endforeach
        </ul>
    @else
        <p>No users</p>
    @endif
@endsection