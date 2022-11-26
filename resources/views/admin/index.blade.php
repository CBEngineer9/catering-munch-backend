<h1>Welcome, Admin!</h1>
@dump(Session::get('message'))
<a href="/admin">Dashboard</a>
<a href="/admin/customers">Customers</a>
<a href="/admin/providers">Providers</a>
<a href="/admin/history">History</a>
<a href="/logout">Logout</a>

<div>
    Registered Munch Accounts: {{ $countRegistered }}
</div>
<div>
    Customers: {{ $countCustomer }}
</div>
<div>
    Providers: {{ $countProvider }}
</div>
<div>
    Unverified Accounts: {{ $countUnverified }}
</div>
<h4>Recent Provider Registration</h4>
<table border="1">
    <thead>
        <tr>
            <th>Nama</th>
            <th>Email</th>
            <th>Alamat</th>
            <th>Nomor Telepon</th>
            <th>Action</th>
        </tr>
    </thead>
    <tbody>
        @forelse ($waitingList as $provider)
            <tr>
                <td>{{ $provider->users_nama }}</td>
                <td>{{ $provider->users_email }}</td>
                <td>{{ $provider->users_alamat }}</td>
                <td>{{ $provider->users_telepon }}</td>
                <td>
                    <form action="{{ route('admin-approve', [$provider->users_id]) }}" method="post">
                        @csrf
                        <button>Approve</button>
                    </form>
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="5" style="text-align: center">Tidak ada yang sedang menunggu!</td>
            </tr>
        @endforelse
    </tbody>
</table>
