<h1>Welcome, Admin!</h1>
@dump(Session::get('message'))
<a href="/admin">Dashboard</a>
<a href="/admin/customers">Customers</a>
<a href="/admin/providers">Providers</a>
<a href="/admin/history">History</a>
<a href="/logout">Logout</a>

<h2>Admin Log History</h2>
<form action="{{ url('/admin/history') }}" method="get">
    Date Filter
    <input type="date" name="search" value="{{ $search }}">
    <button>Search</button>
</form>
<table border="1">
    <thead>
        <tr>
            <th>Action</th>
            <th>Details</th>
            <th>Time</th>
        </tr>
    </thead>
    <tbody>
        @forelse ($logList as $log)
            <tr>
                <td>{{ $log->log_title }}</td>
                <td>{{ $log->log_desc }}</td>
                <td>{{ $log->log_datetime }}</td>
            </tr>
        @empty
            <tr>
                <td colspan="3">Tidak ada history saat ini!</td>
            </tr>
        @endforelse
    </tbody>
</table>
