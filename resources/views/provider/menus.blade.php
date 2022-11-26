<h1>Welcome, {{ auth()->user()->users_nama }}!</h1>
@dump(Session::get('message'))
<a href="/provider">Home</a>
<a href="/provider/menu">Menus</a>
<a href="/provider/history">History</a>
<a href="/logout">Logout</a>

<h2>List of Menus</h2>
Showing: {{ count($menuList) }} Menus
<form action="" method="get">

</form>
<table border="1">
    <tbody>
        @forelse ($menuList as $menu)
            <tr>
                <td>{{ $menu->menu_foto }}</td>
                <td>{{ $menu->menu_nama }}</td>
                <td>
                    <form action="" method="get">
                        <button>Detail</button>
                    </form>
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="3" style="text-align: center">Belum ada menu saat ini!</td>
            </tr>
        @endforelse
        <tr>
            <td>
                <a href="/provider/menu/add">Add New Menu</a>
            </td>
        </tr>
    </tbody>
</table>
