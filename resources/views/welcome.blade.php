<!doctype html>
<html lang="{{ app()->getLocale() }}">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>Futsala</title>

        <!-- Fonts -->
        <link href="https://fonts.googleapis.com/css?family=Raleway:100,600" rel="stylesheet" type="text/css">

        <!-- Styles -->
        <style>
            html, body {
                background-color: #fff;
                color: #636b6f;
                font-family: 'Raleway', sans-serif;
                font-weight: 100;
                height: 100vh;
                margin: 0;
            }

            .full-height {
                height: 100vh;
            }

            .flex-center {
                align-items: center;
                display: flex;
                justify-content: center;
            }

            .position-ref {
                position: relative;
            }

            .top-right {
                position: absolute;
                right: 10px;
                top: 18px;
            }

            .content {
                text-align: center;
            }

            .title {
                font-size: 84px;
            }

            .contents > a {
                color: #636b6f;
                padding: 0 25px;
                font-size: 12px;
                font-weight: 600;
                letter-spacing: .1rem;
                text-decoration: none;
                text-transform: uppercase;
            }

            .m-b-md {
                margin-bottom: 30px;
            }

            table {
                font-family: arial, sans-serif;
                border-collapse: collapse;
                width: 100%;
            }

            td, th {
                border: 1px solid #dddddd;
                text-align: left;
                padding: 8px;
            }

            tr:nth-child(even) {
                background-color: #dddddd;
            }
        </style>
    </head>
    <body>
        <div class="flex-center position-ref">
            @if (Route::has('login'))
                <div class="top-right links">
                    @auth
                        <a href="{{ url('/cms') }}">Home</a>
                    @else
                        <a href="{{ route('login') }}">Login</a>
                        <a href="{{ route('register') }}">Register</a>
                    @endauth
                </div>
            @endif

            <div class="content " style="width: 90%;">
                <div class="title m-b-md">
                    Euro Futsala
                </div>
                <br>
                <br>
                <h2>Chats</h2>
                <div class="contents">
                    <table>
                        <tr>
                            <th>Chat</th>
                            <th>Authorize</th>
                        </tr>
                        @foreach($chats as $chat)
                            <tr>
                                <td>{{ strlen($chat->title) > 0 ? $chat->title : (strlen($chat->username) > 0 ? $chat->username : $chat->id) }}</td>
                                <td>
                                    @if ($chat->authorized)
                                        Authorized
                                    @else
                                        <form action="{{ route('authorize') }}" method="post" enctype="multipart/form-data">
                                            {{ method_field('post') }}
                                            <input type="hidden" name="chat" value="{{ $chat->id }}">
                                            <button type="submit">Authorize</button>
                                        </form>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </table>
                </div>
                <br>
                <br>
                <br>
                <h2>Roles</h2>
                <div class="contents">
                    <table>
                        <tr>
                            <th>Chat</th>
                            <th>Admin</th>
                            <th>Permit</th>
                        </tr>
                        @foreach($users as $user)
                            <tr>
                                <td>{{ $user->first_name . " " . $user->last_name }}</td>
                                <td>
                                    @if (\App\User::hasRole($user, 'Admin'))
                                        Admin
                                    @else
                                        <form action="{{ route('addrole') }}" method="post" enctype="multipart/form-data">
                                            {{ method_field('post') }}
                                            <input type="hidden" name="user_id" value="{{ $user->id }}">
                                            <input type="hidden" name="role_id" value="1">
                                            <button type="submit">Make Admin</button>
                                        </form>
                                    @endif
                                </td>
                                <td>
                                    @if (\App\User::hasRole($user, 'Permitted'))
                                        Permitted
                                    @else
                                        <form action="{{ route('addrole') }}" method="post" enctype="multipart/form-data">
                                            {{ method_field('post') }}
                                            <input type="hidden" name="user_id" value="{{ $user->id }}">
                                            <input type="hidden" name="role_id" value="2">
                                            <button type="submit">Make Permitted</button>
                                        </form>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </table>
                </div>
            </div>
        </div>
    </body>
</html>
