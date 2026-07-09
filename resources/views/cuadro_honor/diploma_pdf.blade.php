<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Diploma - {{ $alumno->nombre }} {{ $alumno->apellido_paterno }}</title>
    <style>
        @page {
            size: a4 landscape;
            margin: 0;
        }
        body {
            margin: 0;
            padding: 0;
            width: 297mm;
            height: 210mm;
            background-color: #fdfbf7;
            font-family: 'Times New Roman', Times, Georgia, serif;
            color: #2c3e50;
        }
        .certificate-container {
            width: 277mm;
            height: 190mm;
            margin: 10mm;
            border: 6px double #1b365d;
            padding: 5mm;
            box-sizing: border-box;
            background-color: #fdfcf7;
            position: relative;
        }
        .inner-border {
            width: 100%;
            height: 100%;
            border: 2px solid #c5a059;
            box-sizing: border-box;
            padding: 12mm 15mm;
        }
        .header {
            text-align: center;
        }
        .school-name {
            font-size: 20px;
            font-weight: bold;
            color: #1b365d;
            letter-spacing: 3px;
            text-transform: uppercase;
            margin: 0 0 5px 0;
        }
        .school-sub {
            font-size: 13px;
            color: #7f8c8d;
            letter-spacing: 1px;
            text-transform: uppercase;
            margin: 0 0 15px 0;
        }
        .presenta {
            font-size: 15px;
            font-style: italic;
            color: #555;
            margin: 15px 0 5px 0;
            text-align: center;
        }
        .title-diploma {
            font-size: 42px;
            font-weight: bold;
            color: #1b365d;
            letter-spacing: 5px;
            text-transform: uppercase;
            text-align: center;
            margin: 5px 0;
        }
        .otorgado-a {
            font-size: 14px;
            color: #7f8c8d;
            text-transform: uppercase;
            letter-spacing: 1px;
            text-align: center;
            margin: 15px 0 5px 0;
        }
        .student-name-container {
            text-align: center;
            margin: 10px 0;
        }
        .student-name {
            font-size: 30px;
            font-weight: bold;
            font-style: italic;
            color: #1b365d;
            border-bottom: 2px solid #c5a059;
            display: inline-block;
            padding: 2px 25px;
        }
        .reason {
            font-size: 16px;
            line-height: 1.6;
            text-align: center;
            max-width: 80%;
            margin: 15px auto;
            color: #34495e;
        }
        .lugar-highlight {
            font-size: 24px;
            font-weight: bold;
            color: #c5a059;
            letter-spacing: 1px;
            margin: 10px 0;
            text-transform: uppercase;
            display: block;
        }
        .info-academic {
            font-weight: bold;
            color: #1b365d;
        }
        .date-container {
            font-size: 13px;
            font-style: italic;
            color: #7f8c8d;
            text-align: center;
            margin-top: 15px;
        }
        .footer-table {
            width: 100%;
            margin-top: 30px;
            border-collapse: collapse;
        }
        .footer-cell {
            width: 40%;
            text-align: center;
            vertical-align: bottom;
        }
        .seal-cell {
            width: 20%;
            text-align: center;
            vertical-align: middle;
        }
        .signature-line {
            border-top: 1px solid #1b365d;
            width: 200px;
            margin: 0 auto 5px auto;
        }
        .signature-title {
            font-size: 12px;
            font-weight: bold;
            color: #1b365d;
            text-transform: uppercase;
        }
        .signature-sub {
            font-size: 10px;
            color: #7f8c8d;
        }
        .gold-seal {
            width: 55px;
            height: 55px;
            background-color: #c5a059;
            border-radius: 50%;
            margin: 0 auto;
            border: 2px dashed #ffffff;
            box-shadow: 0 0 0 3px #c5a059;
            text-align: center;
        }
        .seal-star {
            color: #ffffff;
            font-size: 26px;
            line-height: 51px;
        }
    </style>
</head>
<body>
    @php
        $meses = [
            '01' => 'enero', '02' => 'febrero', '03' => 'marzo', '04' => 'abril',
            '05' => 'mayo', '06' => 'junio', '07' => 'julio', '08' => 'agosto',
            '09' => 'septiembre', '10' => 'octubre', '11' => 'noviembre', '12' => 'diciembre'
        ];
        $dia = date('d');
        $mes = $meses[date('m')] ?? date('F');
        $anio = date('Y');
    @endphp

    <div class="certificate-container">
        <div class="inner-border">
            <div class="header">
                <div class="school-name">Colegio {{ config('app.name') }}</div>
                <div class="school-sub">Excelencia Académica y Formación Integral</div>
            </div>

            <div class="presenta">Otorga el presente</div>
            <div class="title-diploma">Diploma</div>

            <div class="otorgado-a">A la alumna / al alumno:</div>
            <div class="student-name-container">
                <div class="student-name">{{ $alumno->nombre }} {{ $alumno->apellido_paterno }} {{ $alumno->apellido_materno }}</div>
            </div>

            <div class="reason">
                Por haber obtenido el <span class="lugar-highlight">{{ $lugarTexto }}</span>
                de su grupo en el <span class="info-academic">Grado {{ $grado }}° - Grupo "{{ $grupo }}"</span>,
                con un promedio de <span class="info-academic">{{ $promedioFormateado }}</span>,
                durante el <span class="info-academic">{{ $trimestre }}° Trimestre</span>
                del Ciclo Escolar <span class="info-academic">{{ $cicloEscolar }}</span>.
            </div>

            <div class="date-container">
                Dado en la Ciudad de México, a los {{ $dia }} días del mes de {{ $mes }} de {{ $anio }}.
            </div>

            <table class="footer-table">
                <tr>
                    <td class="footer-cell">
                        <div class="signature-line"></div>
                        <div class="signature-title">Dirección Escolar</div>
                        <div class="signature-sub">Colegio {{ config('app.name') }}</div>
                    </td>
                    <td class="seal-cell">
                        <div class="gold-seal">
                            <div class="seal-star">★</div>
                        </div>
                    </td>
                    <td class="footer-cell">
                        <div class="signature-line"></div>
                        <div class="signature-title">Control Escolar</div>
                        <div class="signature-sub">Firma Autorizada</div>
                    </td>
                </tr>
            </table>
        </div>
    </div>
</body>
</html>
