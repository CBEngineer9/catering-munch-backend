<h1>Welcome, {{ auth()->user()->users_nama }}!</h1>
@dump(Session::get('message'))
<a href="/provider">Home</a>
<a href="/provider/menu">Menus</a>
<a href="/provider/history">History</a>
<a href="/logout">Logout</a>

<h2>Overview</h2>
<h4>Monthly Review</h4>
<div>
    Customer bulan ini:
</div>
<div>
    Total Orders:
</div>
<div>
    Made Deliveries:
</div>
<h3>Now's Delivery</h3>
<table border="1">
    <thead>
        <tr>
            <th>Nama</th>
            <th>Alamat</th>
            <th>Delivery Status</th>
            <th>Delivery Date</th>
            <th>Action</th>
        </tr>
    </thead>
    <tbody>
        {{-- @forelse (as)
            <tr>
                <td></td>
            </tr>
        @empty
        @endforelse --}}
    </tbody>
</table>
