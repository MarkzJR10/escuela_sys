<?php

namespace App\Services;

use App\Models\Alumno;
use App\Models\Asistencia;
use App\Models\Boleta;
use App\Models\Configuracion;
use App\Models\Materia;

class BoletaPdfService
{
    /**
     * Determina la clave de plantilla según el grado del alumno.
     */
    public static function resolveTemplateKey(?string $grado): string
    {
        $g = strtoupper(trim((string) $grado));
        $gClean = preg_replace('/[^A-Z0-9]/', '', $g);

        if (in_array($gClean, ['BK', 'N', 'K', 'P', 'PRE', 'PREESCOLAR', 'KINDER', 'MATERNAL', 'NURSERY', 'Bambino'])) {
            return 'preescolar';
        }

        $num = preg_replace('/[^0-9]/', '', $gClean);
        return match ($num) {
            '1', '2' => 'primaria_1a2',
            '3' => 'primaria_3',
            '4', '5', '6' => 'primaria_4a6',
            '7' => 'secundaria_7',
            '8' => 'secundaria_8',
            '9' => 'secundaria_9',
            default => 'primaria_1a2',
        };
    }

    /**
     * Devuelve la configuración oficial de materias, campos formativos, inglés y extracurriculares por nivel.
     */
    public static function getTemplateConfig(string $templateKey): array
    {
        $qrPrimaria = "COLEGIO IBEROAMERICANO DE CADEREYTA\nNIVEL:  PRIMARIA\nAUTORIZACIÓN: IA-B-020/99-2000\nC.C.T.  19PPR0153D\t\tZONA ESCOLAR: 75\nSISTEMA:  ESTATAL\nUNIDAD REGIONAL: 8\nDIRECTORA GENERAL: MTRA. MARÍA GUADALUPE ROMERO TAMEZ\n";
        $qrPreescolar = "COLEGIO IBEROAMERICANO DE CADEREYTA\nNIVEL:  PREESCOLAR\nINCORPORACIÓN: R.V.0.E.A-036/99-2000\nC.C.T.  19PJN0340H\t\tZONA ESCOLAR: 24\nSISTEMA:  ESTATAL\nUNIDAD REGIONAL: 8\nDIRECTORA GENERAL: MTRA. MARÍA GUADALUPE ROMERO TAMEZ\n";
        $qrSecundaria = "COLEGIO IBEROAMERICANO DE CADEREYTA\nNIVEL:  SECUNDARIA\nACUERDO: IA-C-005/2005\nC.C.T.  19PES0269X\t\tZONA ESCOLAR: 91\nSISTEMA:  ESTATAL\nUNIDAD REGIONAL: 8\nDIRECTORA GENERAL: MTRA. MARÍA GUADALUPE ROMERO TAMEZ\n";

        $configs = [
            'preescolar' => [
                'page_size' => [303, 279],
                'level_title' => 'PRESCHOOL',
                'qr_text' => $qrPreescolar,
                'qr_x' => 220,
                'qr_y' => 170,
                'qr_w' => 50,
                'qr_h' => 50,
                'campos' => [
                    ['label' => "LENGUAJES", 'width' => 25, 'line_h' => 20],
                    ['label' => "SABERES Y\nPENSAMIENTO\nCIENTÍFICO", 'width' => 30, 'line_h' => 6.7],
                    ['label' => "ÉTICA,\nNATURALEZA Y\nSOCIEDADES", 'width' => 30, 'line_h' => 6.7],
                    ['label' => "DE LO HUMANO Y LO COMUNITARIO", 'width' => 91.2, 'line_h' => 20.1],
                ],
                'espanol' => [
                    ['key' => 'ESPAÑOL', 'label' => 'ESPAÑOL', 'width' => 25, 'line_h' => 10],
                    ['key' => 'MATEMÁTICAS', 'label' => 'MATEMÁTICAS', 'width' => 30, 'line_h' => 10],
                    ['key' => 'CUIDADO DEL MEDIO AMBIENTE', 'label' => "CUIDADO DEL\nMEDIO AMBIENTE", 'width' => 30, 'line_h' => 5],
                    ['key' => 'EDUCACIÓN SOCIOEMOCIONAL', 'label' => "EDUCACIÓN\nSOCIOEMOCIONAL", 'width' => 32, 'line_h' => 5],
                    ['key' => 'MÚSICA', 'label' => 'MÚSICA', 'width' => 25, 'line_h' => 10],
                    ['key' => 'EDUCACIÓN FÍSICA', 'label' => "EDUCACIÓN \nFÍSICA", 'width' => 34.2, 'line_h' => 5],
                ],
                'english' => [
                    ['key' => 'LANGUAGE SKILLS', 'label' => "LANGUAGE\nSKILLS", 'width' => 25, 'line_h' => 5, 'font_size' => 9],
                    ['key' => 'PRONUNCIATION LAB', 'label' => "PRONUNCIATION\nLAB", 'width' => 25, 'line_h' => 5, 'font_size' => 7],
                    ['key' => 'PRE-MATH', 'label' => 'PRE-MATH', 'width' => 25, 'line_h' => 10, 'font_size' => 9],
                    ['key' => 'WRITING SKILLS', 'label' => "WRITING\nSKILLS", 'width' => 25, 'line_h' => 5, 'font_size' => 9],
                    ['key' => 'GLOBAL CITIZENSHIP', 'label' => "GLOBAL\nCITIZENSHIP", 'width' => 25, 'line_h' => 5, 'font_size' => 9],
                    ['key' => 'ARTS AND CRAFTS', 'label' => "ARTS AND\nCRAFTS", 'width' => 25, 'line_h' => 5, 'font_size' => 9],
                ],
                'extra_x' => 180,
                'extra_total_w' => 101,
                'extra_classes_w' => 25,
                'extra_scale_w' => 26,
                'extra' => [
                    ['key' => 'ACTIVATION AND PHYSICAL DEV', 'label' => "ACTIVATION AND\nPHYSICAL DEV", 'width' => 25, 'line_h' => 5, 'font_size' => 7],
                    ['key' => 'INTEGRATED TRAINING', 'label' => "INTEGRATED\nTRAINING", 'width' => 25, 'line_h' => 5, 'font_size' => 9],
                ],
            ],
            'primaria_1a2' => [
                'page_size' => [290, 279],
                'level_title' => 'PRIMARY',
                'qr_text' => $qrPrimaria,
                'qr_x' => 220,
                'qr_y' => 170,
                'qr_w' => 50,
                'qr_h' => 50,
                'campos' => [
                    ['label' => "LENGUAJES", 'width' => 25, 'line_h' => 20],
                    ['label' => "SABERES Y\nPENSAMIENTO\nCIENTÍFICO", 'width' => 30, 'line_h' => 6.7],
                    ['label' => "ÉTICA,\nNATURALEZA Y\nSOCIEDADES", 'width' => 30, 'line_h' => 6.7],
                    ['label' => "DE LO HUMANO Y LO COMUNITARIO", 'width' => 149.2, 'line_h' => 20.1],
                ],
                'espanol' => [
                    ['key' => 'ESPAÑOL', 'label' => 'ESPAÑOL', 'width' => 25, 'line_h' => 10],
                    ['key' => 'MATEMÁTICAS', 'label' => 'MATEMÁTICAS', 'width' => 30, 'line_h' => 10],
                    ['key' => 'CONOCIMIENTO DEL MEDIO', 'label' => "CONOCIMIENTO\n DEL MEDIO", 'width' => 30, 'line_h' => 5],
                    ['key' => 'COMPUTACIÓN', 'label' => 'COMPUTACIÓN', 'width' => 30, 'line_h' => 10],
                    ['key' => 'ROBÓTICA', 'label' => 'ROBÓTICA', 'width' => 25, 'line_h' => 10],
                    ['key' => 'ARTES', 'label' => 'ARTES', 'width' => 20, 'line_h' => 10],
                    ['key' => 'EDUCACIÓN SOCIOEMOCIONAL', 'label' => "EDUCACIÓN \n SOCIOEMOCIONAL", 'width' => 40, 'line_h' => 5],
                    ['key' => 'EDUCACIÓN FÍSICA', 'label' => "EDUCACIÓN \nFÍSICA", 'width' => 34.2, 'line_h' => 5],
                ],
                'english' => [
                    ['key' => 'LANGUAGE', 'label' => 'LANGUAGE', 'width' => 25, 'line_h' => 10, 'font_size' => 9],
                    ['key' => 'ENGLISH SKILLS', 'label' => "ENGLISH \nSKILLS", 'width' => 25, 'line_h' => 5, 'font_size' => 9],
                    ['key' => 'SCIENCE', 'label' => 'SCIENCE', 'width' => 25, 'line_h' => 10, 'font_size' => 9],
                    ['key' => 'STEAM', 'label' => 'STEAM', 'width' => 25, 'line_h' => 10, 'font_size' => 9],
                    ['key' => 'GLOBAL CITIZENSHIP', 'label' => "GLOBAL \nCITIZENSHIP", 'width' => 25, 'line_h' => 5, 'font_size' => 9],
                    ['key' => 'THINK TANK', 'label' => 'THINK TANK', 'width' => 25, 'line_h' => 10, 'font_size' => 9],
                ],
                'extra_x' => 180,
                'extra_total_w' => 89.3,
                'extra_classes_w' => 20,
                'extra_scale_w' => 39.3,
                'extra' => [
                    ['key' => 'SOCCER / BASKETBALL', 'label' => "SOCCER / \nBASKETBALL", 'width' => 30, 'line_h' => 5, 'font_size' => 9],
                ],
            ],
            'primaria_3' => [
                'page_size' => [308, 279],
                'level_title' => 'PRIMARY',
                'qr_text' => $qrPrimaria,
                'qr_x' => 220,
                'qr_y' => 170,
                'qr_w' => 50,
                'qr_h' => 50,
                'campos' => [
                    ['label' => "LENGUAJES", 'width' => 25, 'line_h' => 20],
                    ['label' => "SABERES Y\nPENSAMIENTO\nCIENTÍFICO", 'width' => 60, 'line_h' => 6.7],
                    ['label' => "ÉTICA,\nNATURALEZA Y\nSOCIEDADES", 'width' => 30, 'line_h' => 6.7],
                    ['label' => "DE LO HUMANO Y LO COMUNITARIO", 'width' => 149.2, 'line_h' => 20.1],
                ],
                'espanol' => [
                    ['key' => 'ESPAÑOL', 'label' => 'ESPAÑOL', 'width' => 25, 'line_h' => 10],
                    ['key' => 'MATEMÁTICAS', 'label' => 'MATEMÁTICAS', 'width' => 30, 'line_h' => 10],
                    ['key' => 'CIENCIAS NATURALES', 'label' => "CIENCIAS\nNATURALES", 'width' => 30, 'line_h' => 5],
                    ['key' => 'HISTORIA DE NUEVO LEÓN', 'label' => "HISTORIA DE\nNUEVO LEÓN", 'width' => 30, 'line_h' => 5],
                    ['key' => 'COMPUTACIÓN', 'label' => 'COMPUTACIÓN', 'width' => 30, 'line_h' => 10],
                    ['key' => 'ROBÓTICA', 'label' => 'ROBÓTICA', 'width' => 25, 'line_h' => 10],
                    ['key' => 'ARTES', 'label' => 'ARTES', 'width' => 20, 'line_h' => 10],
                    ['key' => 'EDUCACIÓN SOCIOEMOCIONAL', 'label' => "EDUCACIÓN \n SOCIOEMOCIONAL", 'width' => 40, 'line_h' => 5],
                    ['key' => 'EDUCACIÓN FÍSICA', 'label' => "EDUCACIÓN \nFÍSICA", 'width' => 34.2, 'line_h' => 5],
                ],
                'english' => [
                    ['key' => 'LANGUAGE', 'label' => 'LANGUAGE', 'width' => 25, 'line_h' => 10, 'font_size' => 9],
                    ['key' => 'ENGLISH SKILLS', 'label' => "ENGLISH \nSKILLS", 'width' => 25, 'line_h' => 5, 'font_size' => 9],
                    ['key' => 'SCIENCE', 'label' => 'SCIENCE', 'width' => 25, 'line_h' => 10, 'font_size' => 9],
                    ['key' => 'STEAM', 'label' => 'STEAM', 'width' => 25, 'line_h' => 10, 'font_size' => 9],
                    ['key' => 'GLOBAL CITIZENSHIP', 'label' => "GLOBAL \nCITIZENSHIP", 'width' => 25, 'line_h' => 5, 'font_size' => 9],
                    ['key' => 'THINK TANK', 'label' => 'THINK TANK', 'width' => 25, 'line_h' => 10, 'font_size' => 9],
                ],
                'extra_x' => 200,
                'extra_total_w' => 89.3,
                'extra_classes_w' => 20,
                'extra_scale_w' => 39.3,
                'extra' => [
                    ['key' => 'SOCCER / BASKETBALL', 'label' => "SOCCER / \nBASKETBALL", 'width' => 30, 'line_h' => 5, 'font_size' => 9],
                ],
            ],
            'primaria_4a6' => [
                'page_size' => [338, 279],
                'level_title' => 'PRIMARY',
                'qr_text' => $qrPrimaria,
                'qr_x' => 220,
                'qr_y' => 170,
                'qr_w' => 50,
                'qr_h' => 50,
                'campos' => [
                    ['label' => "LENGUAJES", 'width' => 25, 'line_h' => 20],
                    ['label' => "SABERES Y\nPENSAMIENTO\nCIENTÍFICO", 'width' => 60, 'line_h' => 6.7],
                    ['label' => "ÉTICA, NATURALEZA Y\nSOCIEDADES", 'width' => 60, 'line_h' => 10],
                    ['label' => "DE LO HUMANO Y LO COMUNITARIO", 'width' => 149.2, 'line_h' => 20.1],
                ],
                'espanol' => [
                    ['key' => 'ESPAÑOL', 'label' => 'ESPAÑOL', 'width' => 25, 'line_h' => 10],
                    ['key' => 'MATEMÁTICAS', 'label' => 'MATEMÁTICAS', 'width' => 30, 'line_h' => 10],
                    ['key' => 'CIENCIAS NATURALES', 'label' => "CIENCIAS\nNATURALES", 'width' => 30, 'line_h' => 5],
                    ['key' => 'HISTORIA', 'label' => 'HISTORIA', 'width' => 30, 'line_h' => 10],
                    ['key' => 'GEOGRAFÍA', 'label' => 'GEOGRAFÍA', 'width' => 30, 'line_h' => 10],
                    ['key' => 'COMPUTACIÓN', 'label' => 'COMPUTACIÓN', 'width' => 30, 'line_h' => 10],
                    ['key' => 'INTELIGENCIA ARTIFICIAL', 'label' => "INTELIGENCIA\nARTIFICIAL", 'width' => 25, 'line_h' => 5],
                    ['key' => 'ROBÓTICA', 'label' => 'ROBÓTICA', 'width' => 20, 'line_h' => 10],
                    ['key' => 'EDUCACIÓN SOCIOEMOCIONAL', 'label' => "EDUCACIÓN \n SOCIOEMOCIONAL", 'width' => 40, 'line_h' => 5],
                    ['key' => 'EDUCACIÓN FÍSICA', 'label' => "EDUCACIÓN \nFÍSICA", 'width' => 34.2, 'line_h' => 5],
                ],
                'english' => [
                    ['key' => 'LANGUAGE', 'label' => 'LANGUAGE', 'width' => 25, 'line_h' => 10, 'font_size' => 9],
                    ['key' => 'ENGLISH SKILLS', 'label' => "ENGLISH \nSKILLS", 'width' => 25, 'line_h' => 5, 'font_size' => 9],
                    ['key' => 'SCIENCE', 'label' => 'SCIENCE', 'width' => 25, 'line_h' => 10, 'font_size' => 9],
                    ['key' => 'STEAM', 'label' => 'STEAM', 'width' => 25, 'line_h' => 10, 'font_size' => 9],
                    ['key' => 'GLOBAL CITIZENSHIP', 'label' => "GLOBAL \nCITIZENSHIP", 'width' => 25, 'line_h' => 5, 'font_size' => 9],
                    ['key' => 'THINK TANK', 'label' => 'THINK TANK', 'width' => 25, 'line_h' => 10, 'font_size' => 9],
                ],
                'extra_x' => 200,
                'extra_total_w' => 119.3,
                'extra_classes_w' => 20,
                'extra_scale_w' => 39.3,
                'extra' => [
                    ['key' => 'MUSIC', 'label' => 'MUSIC', 'width' => 30, 'line_h' => 10, 'font_size' => 9],
                    ['key' => 'SOCCER / BASKETBALL', 'label' => "SOCCER / \nBASKETBALL", 'width' => 30, 'line_h' => 5, 'font_size' => 9],
                ],
            ],
            'secundaria_7' => [
                'page_size' => [338, 279],
                'level_title' => 'SECONDARY',
                'qr_text' => $qrSecundaria,
                'qr_x' => 220,
                'qr_y' => 170,
                'qr_w' => 50,
                'qr_h' => 50,
                'campos' => [
                    ['label' => "LENGUAJES", 'width' => 25, 'line_h' => 20],
                    ['label' => "SABERES Y\nPENSAMIENTO\nCIENTÍFICO", 'width' => 30, 'line_h' => 6.7],
                    ['label' => "ÉTICA, NATURALEZA Y\nSOCIEDADES", 'width' => 90, 'line_h' => 10],
                    ['label' => "DE LO HUMANO Y LO COMUNITARIO", 'width' => 149.2, 'line_h' => 20.1],
                ],
                'espanol' => [
                    ['key' => 'ESPAÑOL', 'label' => 'ESPAÑOL', 'width' => 25, 'line_h' => 10],
                    ['key' => 'MATEMÁTICAS', 'label' => 'MATEMÁTICAS', 'width' => 30, 'line_h' => 10],
                    ['key' => 'HISTORIA', 'label' => 'HISTORIA', 'width' => 30, 'line_h' => 10],
                    ['key' => 'GEOGRAFÍA', 'label' => 'GEOGRAFÍA', 'width' => 30, 'line_h' => 10],
                    ['key' => 'CÍVICA Y ÉTICA', 'label' => 'CÍVICA Y ÉTICA', 'width' => 30, 'line_h' => 10],
                    ['key' => 'COMPUTACIÓN', 'label' => 'COMPUTACIÓN', 'width' => 30, 'line_h' => 10],
                    ['key' => 'INTELIGENCIA ARTIFICIAL', 'label' => "INTELIGENCIA\nARTIFICIAL", 'width' => 25, 'line_h' => 5],
                    ['key' => 'ROBÓTICA', 'label' => 'ROBÓTICA', 'width' => 20, 'line_h' => 10],
                    ['key' => 'EDUCACIÓN SOCIOEMOCIONAL', 'label' => "EDUCACIÓN \n SOCIOEMOCIONAL", 'width' => 40, 'line_h' => 5],
                    ['key' => 'EDUCACIÓN FÍSICA', 'label' => "EDUCACIÓN \nFÍSICA", 'width' => 34.2, 'line_h' => 5],
                ],
                'english' => [
                    ['key' => 'ENGLISH', 'label' => 'ENGLISH', 'width' => 25, 'line_h' => 10, 'font_size' => 9],
                    ['key' => 'GRAMMAR', 'label' => 'GRAMMAR', 'width' => 25, 'line_h' => 10, 'font_size' => 9],
                    ['key' => 'SPEAKING', 'label' => 'SPEAKING', 'width' => 25, 'line_h' => 10, 'font_size' => 9],
                    ['key' => 'BIOLOGY', 'label' => 'BIOLOGY', 'width' => 25, 'line_h' => 10, 'font_size' => 9],
                    ['key' => 'FRENCH', 'label' => 'FRENCH', 'width' => 25, 'line_h' => 10, 'font_size' => 9],
                ],
                'extra_x' => 200,
                'extra_total_w' => 119.3,
                'extra_classes_w' => 20,
                'extra_scale_w' => 39.3,
                'extra' => [
                    ['key' => 'MUSIC', 'label' => 'MUSIC', 'width' => 30, 'line_h' => 10, 'font_size' => 9],
                    ['key' => 'SOCCER / BASKETBALL', 'label' => "SOCCER / \nBASKETBALL", 'width' => 30, 'line_h' => 5, 'font_size' => 9],
                ],
            ],
            'secundaria_8' => [
                'page_size' => [338, 279],
                'level_title' => 'SECONDARY',
                'qr_text' => $qrSecundaria,
                'qr_x' => 220,
                'qr_y' => 170,
                'qr_w' => 50,
                'qr_h' => 50,
                'campos' => [
                    ['label' => "LENGUAJES", 'width' => 25, 'line_h' => 20],
                    ['label' => "SABERES Y\nPENSAMIENTO\nCIENTÍFICO", 'width' => 30, 'line_h' => 6.7],
                    ['label' => "ÉTICA, NATURALEZA Y\nSOCIEDADES", 'width' => 60, 'line_h' => 10],
                    ['label' => "DE LO HUMANO Y LO COMUNITARIO", 'width' => 168, 'line_h' => 20.1],
                ],
                'espanol' => [
                    ['key' => 'ESPAÑOL', 'label' => 'ESPAÑOL', 'width' => 25, 'line_h' => 10],
                    ['key' => 'MATEMÁTICAS', 'label' => 'MATEMÁTICAS', 'width' => 30, 'line_h' => 10],
                    ['key' => 'HISTORIA', 'label' => 'HISTORIA', 'width' => 30, 'line_h' => 10],
                    ['key' => 'CÍVICA Y ÉTICA', 'label' => 'CÍVICA Y ÉTICA', 'width' => 30, 'line_h' => 10],
                    ['key' => 'COMPUTACIÓN', 'label' => 'COMPUTACIÓN', 'width' => 30, 'line_h' => 10],
                    ['key' => 'INTELIGENCIA ARTIFICIAL', 'label' => "INTELIGENCIA\nARTIFICIAL", 'width' => 30, 'line_h' => 5],
                    ['key' => 'ROBÓTICA', 'label' => 'ROBÓTICA', 'width' => 25, 'line_h' => 10],
                    ['key' => 'EDUCACIÓN SOCIOEMOCIONAL', 'label' => "EDUCACIÓN\nSOCIOEMOCIONAL", 'width' => 33, 'line_h' => 5],
                    ['key' => 'EDUCACIÓN FÍSICA', 'label' => "EDUCACIÓN \nFÍSICA", 'width' => 50, 'line_h' => 5],
                ],
                'english' => [
                    ['key' => 'ENGLISH', 'label' => 'ENGLISH', 'width' => 25, 'line_h' => 10, 'font_size' => 9],
                    ['key' => 'GRAMMAR', 'label' => 'GRAMMAR', 'width' => 25, 'line_h' => 10, 'font_size' => 9],
                    ['key' => 'SPEAKING', 'label' => 'SPEAKING', 'width' => 25, 'line_h' => 10, 'font_size' => 9],
                    ['key' => 'WORLD HISTORY', 'label' => "WORLD\nHISTORY", 'width' => 25, 'line_h' => 5, 'font_size' => 9],
                    ['key' => 'PHYSICS', 'label' => 'PHYSICS', 'width' => 25, 'line_h' => 10, 'font_size' => 9],
                    ['key' => 'FRENCH', 'label' => 'FRENCH', 'width' => 25, 'line_h' => 10, 'font_size' => 9],
                ],
                'extra_x' => 200,
                'extra_total_w' => 119.3,
                'extra_classes_w' => 20,
                'extra_scale_w' => 39.3,
                'extra' => [
                    ['key' => 'MUSIC', 'label' => 'MUSIC', 'width' => 30, 'line_h' => 10, 'font_size' => 9],
                    ['key' => 'SOCCER / BASKETBALL', 'label' => "SOCCER / \nBASKETBALL", 'width' => 30, 'line_h' => 5, 'font_size' => 9],
                ],
            ],
            'secundaria_9' => [
                'page_size' => [338, 279],
                'level_title' => 'SECONDARY',
                'qr_text' => $qrSecundaria,
                'qr_x' => 220,
                'qr_y' => 170,
                'qr_w' => 50,
                'qr_h' => 50,
                'campos' => [
                    ['label' => "LENGUAJES", 'width' => 25, 'line_h' => 20],
                    ['label' => "SABERES Y\nPENSAMIENTO\nCIENTÍFICO", 'width' => 30, 'line_h' => 6.7],
                    ['label' => "ÉTICA, NATURALEZA Y\nSOCIEDADES", 'width' => 60, 'line_h' => 10],
                    ['label' => "DE LO HUMANO Y LO COMUNITARIO", 'width' => 168, 'line_h' => 20.1],
                ],
                'espanol' => [
                    ['key' => 'ESPAÑOL', 'label' => 'ESPAÑOL', 'width' => 25, 'line_h' => 10],
                    ['key' => 'MATEMÁTICAS', 'label' => 'MATEMÁTICAS', 'width' => 30, 'line_h' => 10],
                    ['key' => 'HISTORIA', 'label' => 'HISTORIA', 'width' => 30, 'line_h' => 10],
                    ['key' => 'CÍVICA Y ÉTICA', 'label' => 'CÍVICA Y ÉTICA', 'width' => 30, 'line_h' => 10],
                    ['key' => 'COMPUTACIÓN', 'label' => 'COMPUTACIÓN', 'width' => 30, 'line_h' => 10],
                    ['key' => 'INTELIGENCIA ARTIFICIAL', 'label' => "INTELIGENCIA\nARTIFICIAL", 'width' => 30, 'line_h' => 5],
                    ['key' => 'ROBÓTICA', 'label' => 'ROBÓTICA', 'width' => 25, 'line_h' => 10],
                    ['key' => 'EDUCACIÓN SOCIOEMOCIONAL', 'label' => "EDUCACIÓN\nSOCIOEMOCIONAL", 'width' => 33, 'line_h' => 5],
                    ['key' => 'EDUCACIÓN FÍSICA', 'label' => "EDUCACIÓN \nFÍSICA", 'width' => 50, 'line_h' => 5],
                ],
                'english' => [
                    ['key' => 'ENGLISH', 'label' => 'ENGLISH', 'width' => 25, 'line_h' => 10, 'font_size' => 9],
                    ['key' => 'GRAMMAR', 'label' => 'GRAMMAR', 'width' => 25, 'line_h' => 10, 'font_size' => 9],
                    ['key' => 'SPEAKING', 'label' => 'SPEAKING', 'width' => 25, 'line_h' => 10, 'font_size' => 9],
                    ['key' => 'WORLD HISTORY', 'label' => "WORLD\nHISTORY", 'width' => 25, 'line_h' => 5, 'font_size' => 9],
                    ['key' => 'GEOGRAPHY', 'label' => 'GEOGRAPHY', 'width' => 25, 'line_h' => 10, 'font_size' => 9],
                    ['key' => 'CHEMISTRY', 'label' => 'CHEMISTRY', 'width' => 25, 'line_h' => 10, 'font_size' => 9],
                    ['key' => 'FRENCH', 'label' => 'FRENCH', 'width' => 25, 'line_h' => 10, 'font_size' => 9],
                ],
                'extra_x' => 200,
                'extra_total_w' => 119.3,
                'extra_classes_w' => 20,
                'extra_scale_w' => 39.3,
                'extra' => [
                    ['key' => 'MUSIC', 'label' => 'MUSIC', 'width' => 30, 'line_h' => 10, 'font_size' => 9],
                    ['key' => 'SOCCER / BASKETBALL', 'label' => "SOCCER / \nBASKETBALL", 'width' => 30, 'line_h' => 5, 'font_size' => 9],
                ],
            ],
        ];

        return $configs[$templateKey] ?? $configs['primaria_1a2'];
    }

    /**
     * Normaliza el nombre de una materia quitando acentos y mapeando alias conocidos.
     */
    public static function normalizeSubjectKey(string $name): string
    {
        $upper = mb_strtoupper(trim($name), 'UTF-8');
        $unaccented = strtr($upper, [
            'Á' => 'A', 'É' => 'E', 'Í' => 'I', 'Ó' => 'O', 'Ú' => 'U', 'Ü' => 'U',
            'á' => 'A', 'é' => 'E', 'í' => 'I', 'ó' => 'O', 'ú' => 'U', 'ü' => 'U',
        ]);
        $unaccented = preg_replace('/\s+/', ' ', $unaccented);

        $aliases = [
            'ESPANOL' => 'ESPAÑOL',
            'ESPAÑOL' => 'ESPAÑOL',
            'MATE' => 'MATEMÁTICAS',
            'MATEMATICAS' => 'MATEMÁTICAS',
            'MATEMÁTICA' => 'MATEMÁTICAS',
            'COMPUTACION' => 'COMPUTACIÓN',
            'ROBOTICA' => 'ROBÓTICA',
            'EDUCACION SOCIOEMOCIONAL' => 'EDUCACIÓN SOCIOEMOCIONAL',
            'SOCIOEMOCIONAL' => 'EDUCACIÓN SOCIOEMOCIONAL',
            'EDUCACION FISICA' => 'EDUCACIÓN FÍSICA',
            'HISTORIA DE NUEVO LEON' => 'HISTORIA DE NUEVO LEÓN',
            'GEOGRAFIA' => 'GEOGRAFÍA',
            'CIVICA Y ETICA' => 'CÍVICA Y ÉTICA',
            'FORMACION CIVICA Y ETICA' => 'CÍVICA Y ÉTICA',
            'MUSICA' => 'MÚSICA',
            'IA' => 'INTELIGENCIA ARTIFICIAL',
            'HISTORY' => 'WORLD HISTORY',
            'SOCCER' => 'SOCCER / BASKETBALL',
            'BASKETBALL' => 'SOCCER / BASKETBALL',
            'SOCCER/BASKETBALL' => 'SOCCER / BASKETBALL',
            'SOCCER / BASKETBALL' => 'SOCCER / BASKETBALL',
            'HABITOS PERSONALES' => 'PERSONAL HABITS',
            'HABITOS DE CLASE' => 'CLASSROOM HABITS',
            'CONDUCTA' => 'CONDUCT',
            'FALTAS' => 'DAYS ABSENT',
            'INASISTENCIAS' => 'DAYS ABSENT',
        ];

        return $aliases[$unaccented] ?? ($aliases[$upper] ?? $upper);
    }

    /**
     * Determina si una materia es extracurricular (se evalúa con letras A, B, C, D).
     */
    public static function isExtracurricularSubject(?string $name): bool
    {
        if (!$name) {
            return false;
        }
        $norm = self::normalizeSubjectKey($name);
        $extraSubjects = [
            'MUSIC',
            'SOCCER / BASKETBALL',
            'SOCCER',
            'BASKETBALL',
            'ACTIVATION AND PHYSICAL DEV',
            'INTEGRATED TRAINING',
        ];

        return in_array($norm, $extraSubjects, true) || str_contains($norm, 'EXTRACURRICULAR');
    }

    /**
     * Convierte una calificación en letra (A, B, C, D) a su valor numérico interno de almacenamiento.
     */
    public static function extraLetterToScore($val): ?float
    {
        if (is_null($val) || trim((string) $val) === '') {
            return null;
        }
        $letter = strtoupper(trim((string) $val));
        return match ($letter) {
            'A' => 4.0,
            'B' => 3.0,
            'C' => 2.0,
            'D' => 1.0,
            default => null,
        };
    }

    /**
     * Trunca un número positivo a 2 decimales sin redondear, igual que en primaria_1a2.php.
     */
    public static function truncar2(float $num): float
    {
        return floor(($num + 1e-9) * 100) / 100;
    }

    /**
     * Convierte un puntaje numérico o cadena de extracurricular a letra (A, B, C, D).
     */
    public static function formatExtraGrade($val, bool $hasTrimesterActivity = false): string
    {
        if (is_string($val) && in_array(strtoupper(trim($val)), ['A', 'B', 'C', 'D'])) {
            return strtoupper(trim($val));
        }
        if (!is_null($val) && $val !== '' && is_numeric($val)) {
            $num = floatval($val);
            if ($num <= 0) {
                return ' ';
            }
            if ($num == 4.0 || $num >= 9.0) return 'A';
            if ($num == 3.0 || $num >= 8.0) return 'B';
            if ($num == 2.0 || $num >= 7.0) return 'C';
            return 'D';
        }
        return ' ';
    }

    /**
     * Construye todos los datos consolidados de la boleta oficial para el alumno.
     */
    public static function buildBoletaData(Alumno $alumno): array
    {
        $gradoActual = $alumno->gradoGrupo ? $alumno->gradoGrupo->grado : '1';
        $grupoActual = $alumno->gradoGrupo ? $alumno->gradoGrupo->grupo : 'A';
        $maestro = ($alumno->gradoGrupo && $alumno->gradoGrupo->maestro)
            ? $alumno->gradoGrupo->maestro->nombre_completo
            : null;
        $cicloActual = Configuracion::get('ciclo_escolar_actual', '2025-2026');

        // Calcular número de lista alfabético dentro de su grupo
        $listNumber = 1;
        if ($alumno->grado_grupo_id) {
            $companeros = Alumno::where('grado_grupo_id', $alumno->grado_grupo_id)
                ->where('activo', true)
                ->orderBy('apellido_paterno')
                ->orderBy('apellido_materno')
                ->orderBy('nombre')
                ->pluck('id')
                ->values();
            $pos = $companeros->search($alumno->id);
            if ($pos !== false) {
                $listNumber = $pos + 1;
            }
        }

        $templateKey = self::resolveTemplateKey($gradoActual);
        $config = self::getTemplateConfig($templateKey);

        // Mapa normalizado de calificaciones: [CLAVE_NORMALIZADA => ['p1' => ..., 'p2' => ..., 'p3' => ..., 'fuente' => ...]]
        $scoresMap = [];

        // 1. Primero leer de tabla boletas (histórico/base)
        $boletas = Boleta::where('matricula', $alumno->matricula)
            ->where(function ($q) use ($gradoActual) {
                $q->where('grado', $gradoActual)
                  ->orWhereNull('grado');
            })
            ->get();

        foreach ($boletas as $b) {
            if (!$maestro && !empty($b->maestro)) {
                $maestro = $b->maestro;
            }
            if (!empty($b->ciclo)) {
                $cicloActual = $b->ciclo;
            }
            $normKey = self::normalizeSubjectKey($b->materia);
            $p1 = (!is_null($b->p1) && floatval($b->p1) > 0) ? floatval($b->p1) : null;
            $p2 = (!is_null($b->p2) && floatval($b->p2) > 0) ? floatval($b->p2) : null;
            $p3 = (!is_null($b->p3) && floatval($b->p3) > 0) ? floatval($b->p3) : null;

            $scoresMap[$normKey] = [
                'materia' => $normKey,
                'p1' => $p1,
                'p2' => $p2,
                'p3' => $p3,
                'fuente' => 'boletas',
            ];
        }

        // 2. Sobrescribir/combinar con calificaciones en vivo capturadas por profesores
        $calificaciones = $alumno->calificaciones()->with('materia')->get();
        if ($calificaciones->isNotEmpty()) {
            $porMateria = $calificaciones->groupBy('materia_id');
            foreach ($porMateria as $materiaId => $califs) {
                $materiaObj = $califs->first()->materia;
                $rawNombre = $materiaObj ? $materiaObj->nombre : "Materia #{$materiaId}";
                $normKey = self::normalizeSubjectKey($rawNombre);

                $t1 = $califs->firstWhere('trimestre', 1);
                $t2 = $califs->firstWhere('trimestre', 2);
                $t3 = $califs->firstWhere('trimestre', 3);

                $p1 = ($t1 && !is_null($t1->puntaje) && floatval($t1->puntaje) > 0) ? floatval($t1->puntaje) : ($scoresMap[$normKey]['p1'] ?? null);
                $p2 = ($t2 && !is_null($t2->puntaje) && floatval($t2->puntaje) > 0) ? floatval($t2->puntaje) : ($scoresMap[$normKey]['p2'] ?? null);
                $p3 = ($t3 && !is_null($t3->puntaje) && floatval($t3->puntaje) > 0) ? floatval($t3->puntaje) : ($scoresMap[$normKey]['p3'] ?? null);

                // Caso especial para DAYS ABSENT donde 0 es válido
                if ($normKey === 'DAYS ABSENT') {
                    $p1 = ($t1 && !is_null($t1->puntaje)) ? floatval($t1->puntaje) : ($scoresMap[$normKey]['p1'] ?? null);
                    $p2 = ($t2 && !is_null($t2->puntaje)) ? floatval($t2->puntaje) : ($scoresMap[$normKey]['p2'] ?? null);
                    $p3 = ($t3 && !is_null($t3->puntaje)) ? floatval($t3->puntaje) : ($scoresMap[$normKey]['p3'] ?? null);
                }

                $scoresMap[$normKey] = [
                    'materia' => $normKey,
                    'p1' => $p1,
                    'p2' => $p2,
                    'p3' => $p3,
                    'fuente' => 'calificaciones',
                ];
            }
        }

        // Construir listas de Español e Inglés según la plantilla oficial del grado
        $espanolRows = [];
        foreach ($config['espanol'] as $spec) {
            $k = $spec['key'];
            $entry = $scoresMap[$k] ?? ['p1' => null, 'p2' => null, 'p3' => null, 'fuente' => 'catalogo'];
            $valid = array_values(array_filter([$entry['p1'], $entry['p2'], $entry['p3']], fn($v) => !is_null($v) && $v > 0));
            $pFinal = count($valid) > 0 ? round(array_sum($valid) / count($valid), 1) : null;

            $espanolRows[] = array_merge($spec, [
                'materia' => $k,
                'p1' => $entry['p1'],
                'p2' => $entry['p2'],
                'p3' => $entry['p3'],
                'p_final' => $pFinal,
                'fuente' => $entry['fuente'],
            ]);
        }

        $englishRows = [];
        foreach ($config['english'] as $spec) {
            $k = $spec['key'];
            $entry = $scoresMap[$k] ?? ['p1' => null, 'p2' => null, 'p3' => null, 'fuente' => 'catalogo'];
            $valid = array_values(array_filter([$entry['p1'], $entry['p2'], $entry['p3']], fn($v) => !is_null($v) && $v > 0));
            $pFinal = count($valid) > 0 ? round(array_sum($valid) / count($valid), 1) : null;

            $englishRows[] = array_merge($spec, [
                'materia' => $k,
                'p1' => $entry['p1'],
                'p2' => $entry['p2'],
                'p3' => $entry['p3'],
                'p_final' => $pFinal,
                'fuente' => $entry['fuente'],
            ]);
        }

        // Hábitos y Conducta (CHARACTERISTICS OF A SUCCESSFUL LEARNER)
        $learnerKeys = ['PERSONAL HABITS', 'CLASSROOM HABITS', 'CONDUCT'];
        $learnerRows = [];
        foreach ($learnerKeys as $lk) {
            $entry = $scoresMap[$lk] ?? ['p1' => null, 'p2' => null, 'p3' => null, 'fuente' => 'catalogo'];
            $valid = array_values(array_filter([$entry['p1'], $entry['p2'], $entry['p3']], fn($v) => !is_null($v) && $v > 0));
            $pFinal = count($valid) > 0 ? round(array_sum($valid) / count($valid), 1) : null;

            $learnerRows[$lk] = [
                'key' => $lk,
                'materia' => $lk,
                'p1' => $entry['p1'],
                'p2' => $entry['p2'],
                'p3' => $entry['p3'],
                'p_final' => $pFinal,
                'fuente' => $entry['fuente'],
            ];
        }

        // Detectar si hay actividad en cada trimestre
        $allAveragedItems = array_merge($espanolRows, $englishRows, array_values($learnerRows));
        $valsT1 = array_values(array_filter(array_column($allAveragedItems, 'p1'), fn($v) => !is_null($v) && $v > 0));
        $valsT2 = array_values(array_filter(array_column($allAveragedItems, 'p2'), fn($v) => !is_null($v) && $v > 0));
        $valsT3 = array_values(array_filter(array_column($allAveragedItems, 'p3'), fn($v) => !is_null($v) && $v > 0));

        $hasT1 = count($valsT1) > 0;
        $hasT2 = count($valsT2) > 0;
        $hasT3 = count($valsT3) > 0;

        // Extracurriculares
        $extraRows = [];
        foreach ($config['extra'] as $spec) {
            $k = $spec['key'];
            $entry = $scoresMap[$k] ?? ['p1' => null, 'p2' => null, 'p3' => null];
            $extraRows[] = array_merge($spec, [
                'materia' => $k,
                't1' => self::formatExtraGrade($entry['p1'], $hasT1),
                't2' => self::formatExtraGrade($entry['p2'], $hasT2),
                't3' => self::formatExtraGrade($entry['p3'], $hasT3),
            ]);
        }

        // Días de inasistencia (DAYS ABSENT)
        $daysEntry = $scoresMap['DAYS ABSENT'] ?? null;
        $daysAbsent = [
            't1' => ($daysEntry && !is_null($daysEntry['p1']) && $daysEntry['p1'] > 0) ? (string) intval($daysEntry['p1']) : '-',
            't2' => ($daysEntry && !is_null($daysEntry['p2']) && $daysEntry['p2'] > 0) ? (string) intval($daysEntry['p2']) : '-',
            't3' => ($daysEntry && !is_null($daysEntry['p3']) && $daysEntry['p3'] > 0) ? (string) intval($daysEntry['p3']) : '-',
        ];

        // Promedios por trimestre siguiendo la regla exacta de primaria_1a2.php
        $promT1Num = $hasT1 ? floatval(number_format(array_sum($valsT1) / count($valsT1), 1, '.', '')) : 0.0;
        $promT2Num = $hasT2 ? floatval(number_format(array_sum($valsT2) / count($valsT2), 1, '.', '')) : 0.0;
        $promT3Num = $hasT3 ? floatval(number_format(array_sum($valsT3) / count($valsT3), 1, '.', '')) : 0.0;

        $promT1Mostrar = $hasT1 ? number_format(self::truncar2($promT1Num), 2, '.', '') : '';
        $promT2Mostrar = $hasT2 ? number_format(self::truncar2($promT2Num), 2, '.', '') : '';
        $promT3Mostrar = $hasT3 ? number_format(self::truncar2($promT3Num), 2, '.', '') : '';

        if ($promT1Num > 0 && $promT2Num > 0 && $promT3Num > 0) {
            $pFinalRaw = round(($promT1Num + $promT2Num + $promT3Num) / 3, 5);
            $pFinalMostrar = number_format(self::truncar2($pFinalRaw), 2, '.', '');
            $promedioGeneral = floatval($pFinalMostrar);
        } else {
            $pFinalMostrar = '';
            $activeT = array_values(array_filter([$promT1Num, $promT2Num, $promT3Num], fn($v) => $v > 0));
            $promedioGeneral = count($activeT) > 0 ? floatval(number_format(self::truncar2(array_sum($activeT) / count($activeT)), 2, '.', '')) : null;
        }

        // Lista plana compatible con vistas existentes y Kardex
        $materiasCollection = collect(array_merge($espanolRows, $englishRows, array_values($learnerRows)));
        $aprobadas = $materiasCollection->filter(fn($m) => !is_null($m['p_final']) && $m['p_final'] >= 7.0)->count();
        $reprobadas = $materiasCollection->filter(fn($m) => !is_null($m['p_final']) && $m['p_final'] < 7.0)->count();

        // Nombre oficial en formato APELLIDO_PATERNO APELLIDO_MATERNO NOMBRE
        $nombreOficial = mb_strtoupper(trim("{$alumno->apellido_paterno} {$alumno->apellido_materno} {$alumno->nombre}"), 'UTF-8');
        $maestroOficial = mb_strtoupper(trim($maestro ?: 'SIN ASIGNAR'), 'UTF-8');

        return [
            'alumno' => $alumno,
            'nombre_oficial' => $nombreOficial,
            'grado' => $gradoActual,
            'grupo' => $grupoActual,
            'grado_grupo_label' => trim($gradoActual . $grupoActual),
            'list_number' => $listNumber,
            'maestro' => $maestro,
            'maestro_oficial' => $maestroOficial,
            'ciclo' => $cicloActual,
            'template_key' => $templateKey,
            'template_config' => $config,
            'level_title' => $config['level_title'],
            'espanol_materias' => $espanolRows,
            'english_subjects' => $englishRows,
            'extracurricular' => $extraRows,
            'learner_characteristics' => $learnerRows,
            'days_absent' => $daysAbsent,
            'promedio_t1' => $promT1Mostrar,
            'promedio_t2' => $promT2Mostrar,
            'promedio_t3' => $promT3Mostrar,
            'promedio_final_oficial' => $pFinalMostrar,
            'materias' => $materiasCollection->values(),
            'promedio_general' => $promedioGeneral,
            'total_materias' => $materiasCollection->count(),
            'materias_aprobadas' => $aprobadas,
            'materias_reprobadas' => $reprobadas,
        ];
    }

    /**
     * Genera el archivo PDF oficial de la boleta usando FPDF + phpqrcode con coordenadas idénticas a primaria_*.php.
     */
    public static function renderOfficialPdf(Alumno $alumno): string
    {
        require_once app_path('Libraries/fpdf/fpdf.php');
        require_once app_path('Libraries/phpqrcode/qrlib.php');

        $data = $alumno->getBoletaActualData();
        $config = $data['template_config'];

        $nombre = $data['nombre_oficial'];
        $matricula = (string) $alumno->matricula;
        $maestro = $data['maestro_oficial'];
        $gradoGrupo = $data['grado_grupo_label'];
        $listnumber = (string) $data['list_number'];
        $ciclo = $data['ciclo'];

        // Generar QR en archivo temporal
        $tempDir = storage_path('app/temp_qr');
        if (!is_dir($tempDir)) {
            mkdir($tempDir, 0775, true);
        }
        $qrFile = $tempDir . '/qrcode_' . preg_replace('/[^A-Za-z0-9]/', '', $matricula) . '.png';
        $qrContent = $nombre . '-' . $matricula . '-' . $data['grado'] . '-' . $config['qr_text'];
        \QRcode::png($qrContent, $qrFile, QR_ECLEVEL_L, 5);

        $pdf = new \FPDF('L', 'mm', $config['page_size']);
        $pdf->AddPage();
        $pdf->SetTitle('Calificaciones');

        // Encabezado y logotipo oficial
        $pdf->SetFont('Arial', 'B', 14);
        $logoPath = public_path('img/logoh.jpeg');
        if (file_exists($logoPath)) {
            $pdf->Image($logoPath, 60, 1, 160, 20, 'JPEG');
        }
        $pdf->Ln(10);

        // Nivel Escolar
        $pdf->Cell(0, 10, $config['level_title'], 0, 1, 'C');

        // Ciclo Escolar
        $pdf->SetFont('Arial', '', 10);
        $pdf->Cell(0, 3, 'SCHOOL YEAR ' . $ciclo, 0, 1, 'C');
        $pdf->Ln(2);

        // Datos Generales del Alumno
        $pdf->SetFont('Arial', 'B', 10);
        $pdf->Cell(35, 10, self::latin1('STUDENT´S NAME: '), 1, 0, 'L');
        $pdf->SetFont('Arial', '', 10);
        $pdf->Cell(95, 10, self::latin1($nombre), 1, 0, 'L');

        $pdf->SetFont('Arial', 'B', 10);
        $pdf->Cell(30, 10, 'ID NUMBER: ', 1, 0, 'L');
        $pdf->SetFont('Arial', '', 10);
        $pdf->Cell(0, 10, self::latin1($matricula), 1, 0, 'L');
        $pdf->Ln(10);

        $pdf->SetFont('Arial', 'B', 10);
        $pdf->Cell(45, 10, 'HOMEROOM TEACHER: ', 1, 0, 'L');
        $pdf->SetFont('Arial', '', 10);
        $pdf->Cell(85, 10, self::latin1($maestro), 1, 0, 'L');

        $pdf->SetFont('Arial', 'B', 10);
        $pdf->Cell(15, 10, 'GRADE: ', 1, 0, 'L');
        $pdf->SetFont('Arial', '', 10);
        $pdf->Cell(15, 10, self::latin1($gradoGrupo), 1, 0, 'C');

        $pdf->SetFont('Arial', 'B', 10);
        $pdf->Cell(26, 10, 'LIST NUMBER: ', 1, 0, 'L');
        $pdf->SetFont('Arial', '', 10);
        $pdf->Cell(0, 10, self::latin1($listnumber), 1, 0, 'L');
        $pdf->Ln(12);

        // ================= TABLA ESPAÑOL: CAMPO FORMATIVO =================
        $pdf->SetFont('Arial', 'B', 9);
        $pdf->MultiCell(25, 10, "CAMPO\nFORMATIVO", 1, 'C');

        $offsetX = 25.0;
        foreach ($config['campos'] as $idx => $campo) {
            $x = $pdf->GetX();
            $y = $pdf->GetY();
            $yShift = ($idx === count($config['campos']) - 1) ? 20.1 : 20.0;
            $pdf->SetXY($x + $offsetX, $y - $yShift);
            $pdf->MultiCell($campo['width'], $campo['line_h'], self::latin1($campo['label']), 1, 'C');
            $offsetX += $campo['width'];
        }

        // ================= TABLA ESPAÑOL: ASIGNATURA =================
        $pdf->SetFont('Arial', 'B', 9);
        $pdf->MultiCell(25, 10, 'ASIGNATURA', 1, 'C');

        $offsetX = 25.0;
        foreach ($data['espanol_materias'] as $mat) {
            $x = $pdf->GetX();
            $y = $pdf->GetY();
            $pdf->SetXY($x + $offsetX, $y - 10);
            $pdf->MultiCell($mat['width'], $mat['line_h'], self::latin1($mat['label']), 1, 'C');
            $offsetX += $mat['width'];
        }

        // ================= TABLA ESPAÑOL: TRIMESTRE 1, 2, 3 =================
        foreach ([1 => 'p1', 2 => 'p2', 3 => 'p3'] as $trimNum => $pField) {
            $pdf->SetFont('Arial', 'B', 9);
            $pdf->MultiCell(25, 10, "TRIMESTRE {$trimNum}", 1, 'C');

            $offsetX = 25.0;
            $pdf->SetFont('Arial', '', 10);
            foreach ($data['espanol_materias'] as $mat) {
                $x = $pdf->GetX();
                $y = $pdf->GetY();
                $pdf->SetXY($x + $offsetX, $y - 10);
                $val = (!is_null($mat[$pField]) && $mat[$pField] > 0)
                    ? number_format((float) $mat[$pField], 1, '.', '')
                    : ' ';
                $pdf->MultiCell($mat['width'], 10, $val, 1, 'C');
                $offsetX += $mat['width'];
            }
        }

        // ================= ENCABEZADO EXTRACURRICULAR =================
        $extraX = $config['extra_x'];
        $x = $pdf->GetX();
        $y = $pdf->GetY();
        $pdf->SetXY($x + $extraX, $y + 5);
        $pdf->SetFont('Arial', 'B', 10);
        $pdf->MultiCell($config['extra_total_w'], 5, 'EXTRACURRICULAR', 1, 'C');

        // ================= SUBJECTS + CLASSES HEADER =================
        $pdf->SetFont('Arial', 'B', 9);
        $pdf->MultiCell(25, 10, 'SUBJECTS', 1, 'C');

        $offsetX = 25.0;
        foreach ($data['english_subjects'] as $eng) {
            $x = $pdf->GetX();
            $y = $pdf->GetY();
            $pdf->SetXY($x + $offsetX, $y - 10);
            $pdf->SetFont('Arial', 'B', $eng['font_size'] ?? 9);
            $pdf->MultiCell($eng['width'], $eng['line_h'], self::latin1($eng['label']), 1, 'C');
            $offsetX += $eng['width'];
        }

        // Extracurricular: CLASSES + columnas + A = EXCELLENT
        $x = $pdf->GetX();
        $y = $pdf->GetY();
        $pdf->SetXY($x + $extraX, $y - 10);
        $pdf->SetFont('Arial', 'B', 9);
        $pdf->MultiCell($config['extra_classes_w'], 10, 'CLASSES', 1, 'C');

        $extraOffset = $extraX + $config['extra_classes_w'];
        foreach ($data['extracurricular'] as $ext) {
            $x = $pdf->GetX();
            $y = $pdf->GetY();
            $pdf->SetXY($x + $extraOffset, $y - 10);
            $pdf->SetFont('Arial', 'B', $ext['font_size'] ?? 9);
            $pdf->MultiCell($ext['width'], $ext['line_h'], self::latin1($ext['label']), 1, 'C');
            $extraOffset += $ext['width'];
        }

        $x = $pdf->GetX();
        $y = $pdf->GetY();
        $pdf->SetXY($x + $extraOffset, $y - 10);
        $fontA = ($data['template_key'] === 'preescolar' ? 8 : 9);
        $pdf->SetFont('Arial', 'B', $fontA);
        $pdf->MultiCell($config['extra_scale_w'], 10, 'A = EXCELLENT', 1, 'L');

        // ================= SUBJECTS + EXTRACURRICULAR TRIMESTER 1, 2, 3 =================
        $scaleLabels = [
            1 => ['text' => 'B = SATISFACTORY', 'font' => ($data['template_key'] === 'preescolar' ? 7 : 10)],
            2 => ['text' => 'C = AVERAGE', 'font' => ($data['template_key'] === 'preescolar' ? 9 : 10)],
            3 => ['text' => 'D = UNSATISFACTORY', 'font' => ($data['template_key'] === 'preescolar' ? 5.5 : 9)],
        ];

        foreach ([1 => 'p1', 2 => 'p2', 3 => 'p3'] as $trimNum => $pField) {
            $pdf->SetFont('Arial', 'B', 9);
            $pdf->MultiCell(25, 10, "TRIMESTER {$trimNum}", 1, 'C');

            $offsetX = 25.0;
            $pdf->SetFont('Arial', '', 10);
            foreach ($data['english_subjects'] as $eng) {
                $x = $pdf->GetX();
                $y = $pdf->GetY();
                $pdf->SetXY($x + $offsetX, $y - 10);
                $val = (!is_null($eng[$pField]) && $eng[$pField] > 0)
                    ? number_format((float) $eng[$pField], 1, '.', '')
                    : ' ';
                $pdf->MultiCell($eng['width'], 10, $val, 1, 'C');
                $offsetX += $eng['width'];
            }

            // Extracurricular row
            $x = $pdf->GetX();
            $y = $pdf->GetY();
            $pdf->SetXY($x + $extraX, $y - 10);
            $pdf->SetFont('Arial', 'B', 7);
            $pdf->MultiCell($config['extra_classes_w'], 10, "TRIMESTER {$trimNum}", 1, 'C');

            $extraOffset = $extraX + $config['extra_classes_w'];
            $pdf->SetFont('Arial', '', 10);
            $tKey = "t{$trimNum}";
            foreach ($data['extracurricular'] as $ext) {
                $x = $pdf->GetX();
                $y = $pdf->GetY();
                $pdf->SetXY($x + $extraOffset, $y - 10);
                $pdf->MultiCell($ext['width'], 10, $ext[$tKey] ?: ' ', 1, 'C');
                $extraOffset += $ext['width'];
            }

            $x = $pdf->GetX();
            $y = $pdf->GetY();
            $pdf->SetXY($x + $extraOffset, $y - 10);
            $pdf->SetFont('Arial', 'B', $scaleLabels[$trimNum]['font']);
            $align = ($trimNum === 2 && $data['template_key'] === 'primaria_1a2') ? 'C' : 'L';
            $pdf->MultiCell($config['extra_scale_w'], 10, $scaleLabels[$trimNum]['text'], 1, $align);
        }

        // ================= CHARACTERISTICS OF A SUCCESSFUL LEARNER =================
        $x = $pdf->GetX();
        $y = $pdf->GetY();
        $pdf->SetXY($x, $y + 5);
        $pdf->SetFont('Arial', 'B', 9);
        $pdf->MultiCell(125, 5, 'CHARACTERISTICS OF A SUCCESSFUL LEARNER', 1, 'C');

        $pdf->SetFont('Arial', 'B', 8.5);
        $pdf->MultiCell(25, 5, "RESPONSIBLE\nLEARNER", 1, 'C');

        $pdf->SetFont('Arial', 'B', 9);
        $x = $pdf->GetX();
        $y = $pdf->GetY();
        $pdf->SetXY($x + 25, $y - 10);
        $pdf->MultiCell(25, 5, "PERSONAL \nHABITS", 1, 'C');

        $x = $pdf->GetX();
        $y = $pdf->GetY();
        $pdf->SetXY($x + 50, $y - 10);
        $pdf->MultiCell(25, 5, "CLASSROOM \nHABITS", 1, 'C');

        $x = $pdf->GetX();
        $y = $pdf->GetY();
        $pdf->SetXY($x + 75, $y - 10);
        $pdf->MultiCell(25, 10, 'CONDUCT', 1, 'C');

        $x = $pdf->GetX();
        $y = $pdf->GetY();
        $pdf->SetXY($x + 100, $y - 10);
        $pdf->MultiCell(25, 10, 'DAYS ABSENT', 1, 'C');

        // OVERALL AVERAGE Header
        $x = $pdf->GetX();
        $y = $pdf->GetY();
        $pdf->SetXY($x + 150, $y - 10);
        $pdf->MultiCell(25, 5, "OVERALL \nAVERAGE", 1, 'C');

        // Código QR en posición oficial según plantilla
        $qrX = $config['qr_x'] ?? 220;
        $qrY = $config['qr_y'] ?? 170;
        $qrW = $config['qr_w'] ?? 50;
        $qrH = $config['qr_h'] ?? 50;
        if (file_exists($qrFile)) {
            $pdf->Image($qrFile, $qrX, $qrY, $qrW, $qrH);
        }

        $personal = $data['learner_characteristics']['PERSONAL HABITS'];
        $classroom = $data['learner_characteristics']['CLASSROOM HABITS'];
        $conduct = $data['learner_characteristics']['CONDUCT'];
        $days = $data['days_absent'];

        $overallByTrim = [
            1 => $data['promedio_t1'],
            2 => $data['promedio_t2'],
            3 => $data['promedio_t3'],
        ];

        foreach ([1 => 'p1', 2 => 'p2', 3 => 'p3'] as $trimNum => $pField) {
            $pdf->SetFont('Arial', 'B', 9);
            $pdf->MultiCell(25, 10, "TRIMESTER {$trimNum}", 1, 'C');

            $pdf->SetFont('Arial', '', 10);

            $x = $pdf->GetX();
            $y = $pdf->GetY();
            $pdf->SetXY($x + 25, $y - 10);
            $vPersonal = (!is_null($personal[$pField]) && $personal[$pField] > 0) ? number_format((float) $personal[$pField], 1, '.', '') : ' ';
            $pdf->MultiCell(25, 10, $vPersonal, 1, 'C');

            $x = $pdf->GetX();
            $y = $pdf->GetY();
            $pdf->SetXY($x + 50, $y - 10);
            $vClassroom = (!is_null($classroom[$pField]) && $classroom[$pField] > 0) ? number_format((float) $classroom[$pField], 1, '.', '') : ' ';
            $pdf->MultiCell(25, 10, $vClassroom, 1, 'C');

            $x = $pdf->GetX();
            $y = $pdf->GetY();
            $pdf->SetXY($x + 75, $y - 10);
            $vConduct = (!is_null($conduct[$pField]) && $conduct[$pField] > 0) ? number_format((float) $conduct[$pField], 1, '.', '') : ' ';
            $pdf->MultiCell(25, 10, $vConduct, 1, 'C');

            $x = $pdf->GetX();
            $y = $pdf->GetY();
            $pdf->SetXY($x + 100, $y - 10);
            $tKey = "t{$trimNum}";
            $pdf->MultiCell(25, 10, $days[$tKey] ?: '-', 1, 'C');

            // OVERALL AVERAGE value
            $x = $pdf->GetX();
            $y = $pdf->GetY();
            $pdf->SetXY($x + 150, $y - 10);
            $pdf->MultiCell(25, 10, $overallByTrim[$trimNum] ?: ' ', 1, 'C');

            if ($trimNum === 2) {
                $x = $pdf->GetX();
                $y = $pdf->GetY();
                $pdf->SetXY($x + 180, $y - 10);
                $pdf->SetFont('Arial', 'B', 9);
                $pdf->MultiCell(25, 10, 'FINAL', 1, 'C');
            } elseif ($trimNum === 3) {
                $x = $pdf->GetX();
                $y = $pdf->GetY();
                $pdf->SetXY($x + 180, $y - 10);
                $pdf->SetFont('Arial', '', 10);
                $pdf->MultiCell(25, 10, $data['promedio_final_oficial'] ?: ' ', 1, 'C');
            }
        }

        // ================= PIE DE PÁGINA OFICIAL =================
        $pdf->SetFont('Arial', '', 8);
        $pdf->Ln(3);
        $x = $pdf->GetX();
        $y = $pdf->GetY();
        $pdf->SetXY($x, $y);
        $pdf->MultiCell(0, 6, '1.- THIS REPORT CARD IS VALID FOR INTERNAL USE ONLY.');
        $x = $pdf->GetX();
        $y = $pdf->GetY();
        $pdf->SetXY($x + 150, $y - 6);
        $pdf->MultiCell(0, 6, self::latin1('INCORPORADO A LA SECRETARÍA DE EDUCACIÓN DEL ESTADO DE NUEVO LEÓN.'));

        $x = $pdf->GetX();
        $y = $pdf->GetY();
        $pdf->SetXY($x, $y - 4);
        $pdf->MultiCell(0, 8, '2.- THE GRADING SCALE RANGES FROM 6.0 TO A 10.0');
        $x = $pdf->GetX();
        $y = $pdf->GetY();
        $pdf->SetXY($x + 150, $y - 7);
        $pdf->MultiCell(0, 6, 'CAMINO A SANTA FE # 1901. COL. ARBOLEDAS.');

        $x = $pdf->GetX();
        $y = $pdf->GetY();
        $pdf->SetXY($x, $y - 3);
        $pdf->MultiCell(0, 6, '3.- THE MINIMUM PASSING GRADE FOR ALL SUBJECTS IS A 7.0');
        $x = $pdf->GetX();
        $y = $pdf->GetY();
        $pdf->SetXY($x + 150, $y - 6);
        $pdf->MultiCell(0, 6, self::latin1('CADEREYTA JIMÉNEZ, N.L.'));

        $x = $pdf->GetX();
        $y = $pdf->GetY();
        $pdf->SetXY($x, $y - 4);
        $pdf->MultiCell(0, 8, '4.- THE STUDENT MUST OBTAIN AN OVERALL AVERAGE OF 9.5 OR HIGHER, IN ADDITION');
        $x = $pdf->GetX();
        $y = $pdf->GetY();
        $pdf->SetXY($x + 150, $y - 7);
        $pdf->MultiCell(0, 6, 'TELS. 8282847121 / 8282847122');

        $x = $pdf->GetX();
        $y = $pdf->GetY();
        $pdf->SetXY($x + 4, $y - 3);
        $pdf->MultiCell(0, 6, 'TO AN AVERAGE OF 9.0 OR HIGHER TO QUALIFY FOR THE HONOR ROLL.');
        $x = $pdf->GetX();
        $y = $pdf->GetY();
        $pdf->SetXY($x + 150, $y - 6);
        $pdf->MultiCell(0, 6, 'E-MAIL: escolar@iberoamericanodecadereyta.edu.mx');

        return $pdf->Output('S', "Boleta_{$matricula}.pdf");
    }

    /**
     * Convierte UTF-8 a ISO-8859-1 para FPDF sin usar utf8_decode deprecado en PHP 8.2+.
     */
    private static function latin1(string $str): string
    {
        $converted = @iconv('UTF-8', 'ISO-8859-1//TRANSLIT', $str);
        return $converted !== false ? $converted : mb_convert_encoding($str, 'ISO-8859-1', 'UTF-8');
    }
}

