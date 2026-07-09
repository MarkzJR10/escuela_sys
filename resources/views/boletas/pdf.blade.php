<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Boleta de Calificaciones - {{ $alumno->matricula }}</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 14px; }
        .header { text-align: center; border-bottom: 2px solid #000; padding-bottom: 10px; margin-bottom: 20px; }
        .logo { max-height: 80px; }
        .title { font-size: 24px; font-weight: bold; margin: 10px 0; }
        .subtitle { font-size: 16px; color: #555; }
        .info-table { width: 100%; margin-bottom: 20px; }
        .info-table td { padding: 5px; }
        .grades-table { width: 100%; border-collapse: collapse; margin-bottom: 30px; }
        .grades-table th, .grades-table td { border: 1px solid #000; padding: 8px; text-align: center; }
        .grades-table th { background-color: #f2f2f2; }
        .text-left { text-align: left !important; }
        .footer { position: fixed; bottom: 30px; width: 100%; text-align: center; font-size: 12px; }
        .signature-line { width: 250px; border-top: 1px solid #000; margin: 50px auto 10px auto; }
    </style>
</head>
<body>
    <div class="header">
        <div class="title">Colegio {{ config('app.name') }}</div>
        <div class="subtitle">Boleta Oficial de Calificaciones</div>
    </div>

    <table class="info-table">
        <tr>
            <td><strong>Matrícula:</strong> {{ $alumno->matricula }}</td>
            <td><strong>Grado:</strong> {{ $alumno->gradoGrupo->grado ?? '' }} "{{ $alumno->gradoGrupo->grupo ?? '' }}"</td>
        </tr>
        <tr>
            <td><strong>Nombre del Alumno:</strong> {{ $alumno->apellido_paterno }} {{ $alumno->apellido_materno }} {{ $alumno->nombre }}</td>
            <td>
                <strong>Maestro(a) de Planta:</strong> 
                @if($alumno->gradoGrupo && $alumno->gradoGrupo->maestro)
                    {{ $alumno->gradoGrupo->maestro->nombre }} {{ $alumno->gradoGrupo->maestro->apellido_paterno }} {{ $alumno->gradoGrupo->maestro->apellido_materno }}
                @else
                    <span style="color: #7f8c8d; font-style: italic;">Sin asignar</span>
                @endif
            </td>
        </tr>
    </table>

    <table class="grades-table">
        <thead>
            <tr>
                <th class="text-left">Asignatura</th>
                <th>Trimestre 1</th>
                <th>Trimestre 2</th>
                <th>Trimestre 3</th>
                <th>Promedio Final</th>
            </tr>
        </thead>
        <tbody>
            @forelse($alumno->boletas as $boleta)
            <tr>
                <td class="text-left">{{ $boleta->materia }}</td>
                <td>{{ $boleta->p1 ?? '-' }}</td>
                <td>{{ $boleta->p2 ?? '-' }}</td>
                <td>{{ $boleta->p3 ?? '-' }}</td>
                <td style="font-weight: bold;">{{ $boleta->p_final ?? '-' }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="5">No hay calificaciones registradas.</td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <table style="width: 100%; margin-top: 80px; border-collapse: collapse;">
        <tr>
            <td style="width: 50%; text-align: center; vertical-align: top;">
                <div class="signature-line"></div>
                <strong>
                    @if($alumno->gradoGrupo && $alumno->gradoGrupo->maestro)
                        {{ $alumno->gradoGrupo->maestro->nombre }} {{ $alumno->gradoGrupo->maestro->apellido_paterno }}
                    @else
                        Tutor / Maestro de Planta
                    @endif
                </strong>
                <div style="font-size: 11px; color: #555; margin-top: 5px;">Maestro(a) de Planta</div>
            </td>
            <td style="width: 50%; text-align: center; vertical-align: top;">
                <div class="signature-line"></div>
                <strong>Director Escolar</strong>
                <div style="font-size: 11px; color: #555; margin-top: 5px;">Colegio {{ config('app.name') }}</div>
            </td>
        </tr>
    </table>

    <div class="footer">
        Documento generado el {{ date('d/m/Y') }} a las {{ date('H:i') }} - Válido solo con sello oficial.
    </div>
</body>
</html>
