<!-- resources/views/import.blade.php -->

<form method="post" action="{{ route('BaselineImport.create') }}" enctype="multipart/form-data">
    @csrf
    <input type="file" name="excel_file" accept=".xls, .csv">
    <button type="submit">Import Excel</button>
</form>


