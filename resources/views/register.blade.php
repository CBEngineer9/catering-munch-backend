@dump(Session::get('message'))
<form action="{{ route('register') }}" method="post">
    @csrf
    <h1>Register</h1>
    {{-- NAME --}}
    <input type="text" name="name" value="{{ old('name') }}" placeholder="Name"><br>
    @error('name')
        <div style="color: red">{{ $message }}</div>
    @enderror
    {{-- EMAIL --}}
    <input type="email" name="email" value="{{ old('email') }}" placeholder="Email"><br>
    @error('email')
        <div style="color: red">{{ $message }}</div>
    @enderror
    {{-- PASSWORD --}}
    <input type="password" name="password" placeholder="Password"><br>
    @error('password')
        <div style="color: red">{{ $message }}</div>
    @enderror
    {{-- CONFIRMATION PASSWORD --}}
    <input type="password" name="password_confirmation" placeholder="Confirm Password"><br>
    @error('password_confirmation')
        <div style="color: red">{{ $message }}</div>
    @enderror
    {{-- ALAMAT --}}
    <input type="text" name="alamat" value="{{ old('alamat') }}" placeholder="Alamat"><br>
    @error('alamat')
        <div style="color: red">{{ $message }}</div>
    @enderror
    {{-- NOMOR TELEPON --}}
    <input type="text" name="telepon" value="{{ old('telepon') }}" placeholder="Nomor Telepon"><br>
    @error('telepon')
        <div style="color: red">{{ $message }}</div>
    @enderror
    {{-- ROLE --}}
    <select name="role">
        <option value="customer" @if (old('role') == 'customer') selected @endif>Customer</option>
        <option value="provider" @if (old('role') == 'provider') selected @endif>Provider</option>
    </select><br>
    {{-- TERMS & AGREEMENT --}}
    <input type="checkbox" name="tna" @if (old('tna')) checked="checked" @endif>I agree to the <a
        href="#">Terms &
        Agreement</a><br>
    @error('tna')
        <div style="color: red">{{ $message }}</div>
    @enderror
    <button>Register</button>
</form>
<a href="{{ route('view-login') }}">Login Now</a>
