<h1>Welcome, Admin!</h1>
@dump(Session::get('message'))
<a href="/admin">Dashboard</a>
<a href="/admin/customers">Customers</a>
<a href="/admin/providers">Providers</a>
<a href="/admin/history">History</a>
<a href="/logout">Logout</a>

<div>
    Showing : {{ count($customerList) }} Customers
</div>
<form action="{{ route('view-admin-customers') }}" method="get">
    <input type="text" name="search" placeholder="Search...">
    <button>Search</button>
</form>

<h2>List of Customer</h2>
<table border="1">
    <thead>
        <tr>
            <th>Nama</th>
            <th>Email</th>
            <th>Status</th>
            <th>Action</th>
        </tr>
    </thead>
    <tbody>
        @forelse ($customerList as $customer)
            <tr>
                <td>{{ $customer->users_nama }}</td>
                <td>{{ $customer->users_email }}</td>
                <td>{{ $customer->users_status }}</td>
                <td>
                    <form action="{{ route('admin-ban', [$customer->users_id]) }}" method="post">
                        @csrf
                        @if ($customer->users_status == 'aktif')
                            <button name="ban" value="ban">Ban</button>
                        @else
                            <button name="unban" value="unban">Unban</button>
                        @endif
                    </form>
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="4">Tidak ada customer saat ini!</td>
            </tr>
        @endforelse
    </tbody>
</table>
