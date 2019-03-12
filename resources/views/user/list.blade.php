<!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta http-equiv="X-UA-Compatible" content="ie=edge">
        <title>用户注册</title>
    </head>
    <body>
    <form action="userlist" method="POST">
        {{csrf_field()}}
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
    </form>
    </body>
    </html>
