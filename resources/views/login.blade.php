@dump(Session::get('message'))
<form action="{{ route('login') }}" method="post">
    @csrf
    <h1>Login</h1>
    <input type="email" name="email" value="{{ old('email') }}" placeholder="Email"><br>
    @error('email')
        <div style="color: red">{{ $message }}</div>
    @enderror
    <input type="password" name="password" placeholder="Password"><br>
    @error('password')
        <div style="color: red">{{ $message }}</div>
    @enderror
    <button>Login</button>
</form>
<a href="{{ route('view-register') }}">Register Now</a>
