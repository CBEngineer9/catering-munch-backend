<h1>Welcome, {{ auth()->user()->users_nama }}!</h1>
@dump(Session::get('message'))
<a href="/provider">Home</a>
<a href="/provider/menu">Menus</a>
<a href="/provider/history">History</a>
<a href="/logout">Logout</a>

<h2>Add New Menu</h2>
<form action="{{ route('provider-add') }}" method="post" enctype="multipart/form-data">


</form>
