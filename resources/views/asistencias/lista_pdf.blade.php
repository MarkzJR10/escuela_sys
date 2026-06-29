<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Lista de Asistencia</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 12px; }
        .header { text-align: center; margin-bottom: 20px; }
        .title { font-size: 20px; font-weight: bold; }
        .subtitle { font-size: 14px; }
        .table { width: 100%; border-collapse: collapse; }
        .table th, .table td { border: 1px solid #000; padding: 4px; text-align: left; }
        .table th { background-color: #f2f2f2; text-align: center; }
        .col-dia { width: 25px; text-align: center; }
        .text-center { text-align: center !important; }
    </style>
</head>
<body>
    <div class="header">
        <div class="title">Lista de Asistencia - Mes: ______________</div>
        <div class="subtitle">Grado: {{ $grupo->grado ?? '' }} "{{ $grupo->grupo ?? '' }}" | Profesor: ___________________________</div>
    </div>

    <table class="table">
        <thead>
            <tr>
                <th style="width: 20px;">No.</th>
                <th style="width: 250px;">Nombre del Alumno</th>
                @for($i=1; $i<=25; $i++)
                    <th class="col-dia"></th>
                @endfor
            </tr>
        </thead>
        <tbody>
            @foreach($alumnos as $index => $alumno)
            <tr>
                <td class="text-center">{{ $index + 1 }}</td>
                <td>{{ $alumno->apellido_paterno }} {{ $alumno->apellido_materno }} {{ $alumno->nombre }}</td>
                @for($i=1; $i<=25; $i++)
                    <td></td>
                @endfor
            </tr>
            @endforeach
        </tbody>
    </table>

</body>
</html>
