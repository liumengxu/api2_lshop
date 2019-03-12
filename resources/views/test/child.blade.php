@extends('layout.mother')

@section('title', 'Page Title')


@section('content')
    <button class="btn btn-danger">Danger</button>
    <table border="1">
        <thead>
            <td>id</td>
            <td>name</td>
            <td>age</td>
            <td>email</td>
            <td>c_time</td>
        </thead>
        <tbody>
            @foreach($list as $v)
                <tr>
                    <td>{{$v['id']}}</td>
                    <td>{{$v['name']}}</td>
                    <td>{{$v['age']}}</td>
                    <td>{{$v['email']}}</td>
                    <td>{{$v['c_time']}}</td>
                </tr>

            @endforeach
        </tbody>
    </table>
@endsection
@section('footer')
    @parent
    <p>This is appended to the master sidebar.</p>
@endsection
