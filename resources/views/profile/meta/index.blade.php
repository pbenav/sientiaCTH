<!DOCTYPE html>
<html>
<head>
    <title>Gestión de Metadatos</title>
</head>
<body>
    <h1>Metadatos de {{ $user->name }}</h1>

    @if (session('success'))
        <div>{{ session('success') }}</div>
    @endif
    @if (session('error'))
        <div>{{ session('error') }}</div>
    @endif
    @if ($errors->any())
        <div>
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('users.meta.store', $user->id) }}" method="POST">
        @csrf
        <div>
            <label for="meta_key">Clave:</label>
            <input type="text" name="meta_key" required>
        </div>
        <div>
            <label for="meta_value">Valor:</label>
            <input type="text" name="meta_value">
        </div>
        <button type="submit">Guardar Metadato</button>
    </form>

    <hr>

    <h2>Metadatos Existentes</h2>
    @if ($metaData->isEmpty())
        <p>Este usuario no tiene metadatos.</p>
    @else
        <ul>
            @foreach ($metaData as $meta)
                <li>
                    <strong>{{ $meta->meta_key }}:</strong> {{ $meta->meta_value }}
                    <form action="{{ route('users.meta.destroy', [$user->id, $meta->id]) }}" method="POST" style="display:inline;">
                        @csrf
                        @method('DELETE')
                        <button type="submit">Eliminar</button>
                    </form>
                </li>
            @endforeach
        </ul>
    @endif

</body>
</html>