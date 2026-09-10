<?php

namespace App\Support\Presentation;

class AcademicDemoData
{
    public const SESSION_SUBJECTS_KEY = 'presentation.academic.created_subjects';

    public const SESSION_ACTIVITIES_KEY = 'presentation.academic.created_activities';

    public const SESSION_ENROLLMENTS_KEY = 'presentation.academic.created_enrollments';

    public const SESSION_SUBMISSIONS_KEY = 'presentation.academic.activity_submissions';

    /** @return array<string, string> */
    public static function shifts(): array
    {
        return [
            'morning' => 'Mañana',
            'afternoon' => 'Tarde',
        ];
    }

    /** @return list<string> */
    public static function sections(): array
    {
        return ['A', 'B', 'C'];
    }

    /** @return list<string> */
    public static function teachers(): array
    {
        return [
            'Ana Pérez',
            'Laura Gómez',
            'Camila Ruiz',
            'Sofía Herrera',
            'Valentina Díaz',
            'Carolina López',
        ];
    }

    /** @return list<array<string, mixed>> */
    public static function courses(): array
    {
        return [
            [
                'id' => 'mat-10a',
                'code' => 'MAT-10A',
                'name' => 'Matemáticas 10°',
                'grade' => '10°',
                'shift' => 'morning',
                'section' => 'A',
                'teacher' => 'Ana Pérez',
                'schedule' => 'Lun · Mié · Vie  7:00 – 8:30',
                'room' => 'Aula 204',
            ],
            [
                'id' => 'eng-8b',
                'code' => 'ENG-8B',
                'name' => 'Inglés 8°',
                'grade' => '8°',
                'shift' => 'afternoon',
                'section' => 'B',
                'teacher' => 'Laura Gómez',
                'schedule' => 'Mar · Jue  14:00 – 16:00',
                'room' => 'Aula 112',
            ],
            [
                'id' => 'sci-9a',
                'code' => 'SCI-9A',
                'name' => 'Ciencias Naturales 9°',
                'grade' => '9°',
                'shift' => 'morning',
                'section' => 'A',
                'teacher' => 'Camila Ruiz',
                'schedule' => 'Lun · Jue  9:00 – 10:30',
                'room' => 'Lab. 3',
            ],
            [
                'id' => 'lit-11c',
                'code' => 'LIT-11C',
                'name' => 'Literatura 11°',
                'grade' => '11°',
                'shift' => 'morning',
                'section' => 'C',
                'teacher' => 'Sofía Herrera',
                'schedule' => 'Mar · Vie  8:30 – 10:00',
                'room' => 'Aula 301',
            ],
            [
                'id' => 'soc-7a',
                'code' => 'SOC-7A',
                'name' => 'Ciencias Sociales 7°',
                'grade' => '7°',
                'shift' => 'afternoon',
                'section' => 'A',
                'teacher' => 'Valentina Díaz',
                'schedule' => 'Mié · Vie  13:00 – 14:30',
                'room' => 'Aula 108',
            ],
            [
                'id' => 'art-6b',
                'code' => 'ART-6B',
                'name' => 'Artística 6°',
                'grade' => '6°',
                'shift' => 'morning',
                'section' => 'B',
                'teacher' => 'Carolina López',
                'schedule' => 'Jue  10:30 – 12:00',
                'room' => 'Taller de artes',
            ],
        ];
    }

    /** @return array<string, mixed>|null */
    public static function course(string $id): ?array
    {
        foreach (self::courses() as $course) {
            if ($course['id'] === $id) {
                $course['students'] = self::studentsForCourse($id);
                $course['students_count'] = count($course['students']);
                $course['shift_label'] = self::shifts()[$course['shift']] ?? $course['shift'];

                return $course;
            }
        }

        return null;
    }

    /** @return list<array<string, mixed>> */
    public static function coursesWithCounts(): array
    {
        return array_map(function (array $course) {
            $course['students_count'] = count(self::studentsForCourse($course['id']));
            $course['shift_label'] = self::shifts()[$course['shift']] ?? $course['shift'];

            return $course;
        }, self::courses());
    }

    /** @return list<array<string, mixed>> */
    public static function studentsForCourse(string $course_id): array
    {
        return self::studentsByCourse()[$course_id] ?? [];
    }

    /** @return array<string, list<array<string, mixed>>> */
    public static function studentsByCourse(): array
    {
        return [
            'mat-10a' => [
                ['id' => 1, 'name' => 'Santiago Morales', 'document' => '1003456789', 'email' => 'santiago.morales@colegio.edu'],
                ['id' => 2, 'name' => 'Mariana Castillo', 'document' => '1003456790', 'email' => 'mariana.castillo@colegio.edu'],
                ['id' => 3, 'name' => 'Daniel Restrepo', 'document' => '1003456791', 'email' => 'daniel.restrepo@colegio.edu'],
                ['id' => 4, 'name' => 'Valeria Gómez', 'document' => '1003456792', 'email' => 'valeria.gomez@colegio.edu'],
                ['id' => 5, 'name' => 'Juan Pablo Mejía', 'document' => '1003456793', 'email' => 'juan.mejia@colegio.edu'],
                ['id' => 6, 'name' => 'Isabella Torres', 'document' => '1003456794', 'email' => 'isabella.torres@colegio.edu'],
                ['id' => 7, 'name' => 'Andrés Felipe Ríos', 'document' => '1003456795', 'email' => 'andres.rios@colegio.edu'],
                ['id' => 8, 'name' => 'Camila Duarte', 'document' => '1003456796', 'email' => 'camila.duarte@colegio.edu'],
            ],
            'eng-8b' => [
                ['id' => 9, 'name' => 'Luciana Herrera', 'document' => '1004567801', 'email' => 'luciana.herrera@colegio.edu'],
                ['id' => 10, 'name' => 'Samuel Ortiz', 'document' => '1004567802', 'email' => 'samuel.ortiz@colegio.edu'],
                ['id' => 11, 'name' => 'Emma Vargas', 'document' => '1004567803', 'email' => 'emma.vargas@colegio.edu'],
                ['id' => 12, 'name' => 'Mateo Cárdenas', 'document' => '1004567804', 'email' => 'mateo.cardenas@colegio.edu'],
                ['id' => 13, 'name' => 'Antonella Ruiz', 'document' => '1004567805', 'email' => 'antonella.ruiz@colegio.edu'],
                ['id' => 14, 'name' => 'Nicolás Pineda', 'document' => '1004567806', 'email' => 'nicolas.pineda@colegio.edu'],
            ],
            'sci-9a' => [
                ['id' => 15, 'name' => 'Juliana Palacio', 'document' => '1005678901', 'email' => 'juliana.palacio@colegio.edu'],
                ['id' => 16, 'name' => 'Sebastián López', 'document' => '1005678902', 'email' => 'sebastian.lopez@colegio.edu'],
                ['id' => 17, 'name' => 'Sara Isabel Peña', 'document' => '1005678903', 'email' => 'sara.pena@colegio.edu'],
                ['id' => 18, 'name' => 'Tomás Aguilar', 'document' => '1005678904', 'email' => 'tomas.aguilar@colegio.edu'],
                ['id' => 19, 'name' => 'Mariana Ospina', 'document' => '1005678905', 'email' => 'mariana.ospina@colegio.edu'],
                ['id' => 20, 'name' => 'David Alejandro Cruz', 'document' => '1005678906', 'email' => 'david.cruz@colegio.edu'],
                ['id' => 21, 'name' => 'Elena Ramírez', 'document' => '1005678907', 'email' => 'elena.ramirez@colegio.edu'],
            ],
            'lit-11c' => [
                ['id' => 22, 'name' => 'Catalina Vélez', 'document' => '1006789012', 'email' => 'catalina.velez@colegio.edu'],
                ['id' => 23, 'name' => 'Felipe Andrade', 'document' => '1006789013', 'email' => 'felipe.andrade@colegio.edu'],
                ['id' => 24, 'name' => 'Laura Sofía Niño', 'document' => '1006789014', 'email' => 'laura.nino@colegio.edu'],
                ['id' => 25, 'name' => 'Gabriel Montoya', 'document' => '1006789015', 'email' => 'gabriel.montoya@colegio.edu'],
                ['id' => 26, 'name' => 'Paula Andrea Silva', 'document' => '1006789016', 'email' => 'paula.silva@colegio.edu'],
            ],
            'soc-7a' => [
                ['id' => 27, 'name' => 'Martina Giraldo', 'document' => '1007890123', 'email' => 'martina.giraldo@colegio.edu'],
                ['id' => 28, 'name' => 'Emiliano Castro', 'document' => '1007890124', 'email' => 'emiliano.castro@colegio.edu'],
                ['id' => 29, 'name' => 'Salomé Franco', 'document' => '1007890125', 'email' => 'salome.franco@colegio.edu'],
                ['id' => 30, 'name' => 'Ian Alejandro Mora', 'document' => '1007890126', 'email' => 'ian.mora@colegio.edu'],
                ['id' => 31, 'name' => 'Renata Quintero', 'document' => '1007890127', 'email' => 'renata.quintero@colegio.edu'],
                ['id' => 32, 'name' => 'Benjamín Soto', 'document' => '1007890128', 'email' => 'benjamin.soto@colegio.edu'],
                ['id' => 33, 'name' => 'Olivia Mendoza', 'document' => '1007890129', 'email' => 'olivia.mendoza@colegio.edu'],
                ['id' => 34, 'name' => 'Thiago Bernal', 'document' => '1007890130', 'email' => 'thiago.bernal@colegio.edu'],
            ],
            'art-6b' => [
                ['id' => 35, 'name' => 'Amelia Rojas', 'document' => '1008901234', 'email' => 'amelia.rojas@colegio.edu'],
                ['id' => 36, 'name' => 'Liam Castaño', 'document' => '1008901235', 'email' => 'liam.castano@colegio.edu'],
                ['id' => 37, 'name' => 'Mía Fernanda Gil', 'document' => '1008901236', 'email' => 'mia.gil@colegio.edu'],
                ['id' => 38, 'name' => 'Noah Esteban Díaz', 'document' => '1008901237', 'email' => 'noah.diaz@colegio.edu'],
                ['id' => 39, 'name' => 'Alessandra Vega', 'document' => '1008901238', 'email' => 'alessandra.vega@colegio.edu'],
            ],
        ];
    }

    /** @return list<array<string, mixed>> */
    public static function defaultSubjects(): array
    {
        return [
            [
                'id' => 'sub-mat-a',
                'name' => 'Matemáticas',
                'shift' => 'morning',
                'section' => 'A',
                'teacher' => 'Ana Pérez',
                'schedule' => 'Lun · Mié · Vie  7:00 – 8:30',
                'color' => 'indigo',
            ],
            [
                'id' => 'sub-eng-b',
                'name' => 'Inglés',
                'shift' => 'afternoon',
                'section' => 'B',
                'teacher' => 'Laura Gómez',
                'schedule' => 'Mar · Jue  14:00 – 16:00',
                'color' => 'sky',
            ],
            [
                'id' => 'sub-sci-a',
                'name' => 'Ciencias Naturales',
                'shift' => 'morning',
                'section' => 'A',
                'teacher' => 'Camila Ruiz',
                'schedule' => 'Lun · Jue  9:00 – 10:30',
                'color' => 'emerald',
            ],
            [
                'id' => 'sub-lit-c',
                'name' => 'Literatura',
                'shift' => 'morning',
                'section' => 'C',
                'teacher' => 'Sofía Herrera',
                'schedule' => 'Mar · Vie  8:30 – 10:00',
                'color' => 'violet',
            ],
            [
                'id' => 'sub-soc-a',
                'name' => 'Ciencias Sociales',
                'shift' => 'afternoon',
                'section' => 'A',
                'teacher' => 'Valentina Díaz',
                'schedule' => 'Mié · Vie  13:00 – 14:30',
                'color' => 'amber',
            ],
            [
                'id' => 'sub-art-b',
                'name' => 'Artística',
                'shift' => 'morning',
                'section' => 'B',
                'teacher' => 'Carolina López',
                'schedule' => 'Jue  10:30 – 12:00',
                'color' => 'rose',
            ],
            [
                'id' => 'sub-pe-c',
                'name' => 'Educación física',
                'shift' => 'afternoon',
                'section' => 'C',
                'teacher' => 'Laura Gómez',
                'schedule' => 'Vie  15:00 – 16:30',
                'color' => 'teal',
            ],
            [
                'id' => 'sub-mus-a',
                'name' => 'Música',
                'shift' => 'morning',
                'section' => 'A',
                'teacher' => 'Carolina López',
                'schedule' => 'Mar  11:00 – 12:00',
                'color' => 'fuchsia',
            ],
        ];
    }

    /** @return list<string> */
    public static function subjectColors(): array
    {
        return ['indigo', 'sky', 'emerald', 'violet', 'amber', 'rose', 'teal', 'fuchsia'];
    }

    /** @return list<array<string, mixed>> */
    public static function subjects(): array
    {
        $created = session(self::SESSION_SUBJECTS_KEY, []);

        if (! is_array($created)) {
            $created = [];
        }

        return array_values(array_merge(self::defaultSubjects(), $created));
    }

    /** @param array<string, mixed> $subject */
    public static function addSubject(array $subject): void
    {
        $created = session(self::SESSION_SUBJECTS_KEY, []);

        if (! is_array($created)) {
            $created = [];
        }

        $created[] = $subject;
        session([self::SESSION_SUBJECTS_KEY => $created]);
    }

    /** @return array<string, mixed>|null */
    public static function subject(string $id): ?array
    {
        foreach (self::subjects() as $subject) {
            if ($subject['id'] === $id) {
                $subject['shift_label'] = self::shifts()[$subject['shift']] ?? $subject['shift'];
                $subject['activities'] = self::activitiesForSubject($id);

                return $subject;
            }
        }

        return null;
    }

    /** @return array<string, string> */
    public static function activityTypes(): array
    {
        return [
            'evaluation' => 'Evaluación',
            'homework' => 'Tarea',
            'class_exercise' => 'Ejercicio en clase',
        ];
    }

    /** @return list<array<string, mixed>> */
    public static function defaultActivities(): array
    {
        return [
            ['id' => 'act-mat-1', 'subject_id' => 'sub-mat-a', 'type' => 'evaluation', 'title' => 'Parcial de álgebra', 'due_date' => '2026-09-18', 'max_score' => 5, 'description' => 'Ecuaciones lineales y sistemas.'],
            ['id' => 'act-mat-2', 'subject_id' => 'sub-mat-a', 'type' => 'homework', 'title' => 'Taller de factorización', 'due_date' => '2026-09-15', 'max_score' => 5, 'description' => 'Ejercicios 1 al 20 del cuadernillo.'],
            ['id' => 'act-mat-3', 'subject_id' => 'sub-mat-a', 'type' => 'class_exercise', 'title' => 'Quiz de operaciones', 'due_date' => '2026-09-12', 'max_score' => 5, 'description' => 'Ejercicio en clase de 20 minutos.'],
            ['id' => 'act-eng-1', 'subject_id' => 'sub-eng-b', 'type' => 'evaluation', 'title' => 'Listening test — Unit 3', 'due_date' => '2026-09-20', 'max_score' => 5, 'description' => 'Comprensión auditiva y vocabulario.'],
            ['id' => 'act-eng-2', 'subject_id' => 'sub-eng-b', 'type' => 'homework', 'title' => 'Reading: The Little Prince', 'due_date' => '2026-09-16', 'max_score' => 5, 'description' => 'Capítulos 1 a 4 y preguntas.'],
            ['id' => 'act-sci-1', 'subject_id' => 'sub-sci-a', 'type' => 'evaluation', 'title' => 'Quiz de célula', 'due_date' => '2026-09-17', 'max_score' => 5, 'description' => 'Orgánulos y funciones.'],
            ['id' => 'act-sci-2', 'subject_id' => 'sub-sci-a', 'type' => 'class_exercise', 'title' => 'Laboratorio: microscopio', 'due_date' => '2026-09-14', 'max_score' => 5, 'description' => 'Observación de células vegetales.'],
            ['id' => 'act-lit-1', 'subject_id' => 'sub-lit-c', 'type' => 'homework', 'title' => 'Ensayo sobre Cien años de soledad', 'due_date' => '2026-09-22', 'max_score' => 5, 'description' => 'Mínimo 400 palabras.'],
            ['id' => 'act-lit-2', 'subject_id' => 'sub-lit-c', 'type' => 'evaluation', 'title' => 'Parcial de literatura latinoamericana', 'due_date' => '2026-09-25', 'max_score' => 5, 'description' => 'Boom latinoamericano.'],
            ['id' => 'act-soc-1', 'subject_id' => 'sub-soc-a', 'type' => 'class_exercise', 'title' => 'Línea de tiempo de la Independencia', 'due_date' => '2026-09-13', 'max_score' => 5, 'description' => 'Trabajo en grupos de 4.'],
            ['id' => 'act-art-1', 'subject_id' => 'sub-art-b', 'type' => 'homework', 'title' => 'Boceto de paisaje', 'due_date' => '2026-09-19', 'max_score' => 5, 'description' => 'Técnica libre, formato carta.'],
            ['id' => 'act-pe-1', 'subject_id' => 'sub-pe-c', 'type' => 'evaluation', 'title' => 'Prueba de resistencia', 'due_date' => '2026-09-21', 'max_score' => 5, 'description' => 'Carrera de 12 minutos.'],
            ['id' => 'act-mus-1', 'subject_id' => 'sub-mus-a', 'type' => 'class_exercise', 'title' => 'Lectura rítmica', 'due_date' => '2026-09-11', 'max_score' => 5, 'description' => 'Figuras de negra y corchea.'],
        ];
    }

    /** @return list<array<string, mixed>> */
    public static function activities(): array
    {
        return array_values(array_merge(self::defaultActivities(), self::sessionList(self::SESSION_ACTIVITIES_KEY)));
    }

    /** @return list<array<string, mixed>> */
    public static function activitiesForSubject(string $subject_id, string $type = ''): array
    {
        $types = self::activityTypes();

        return collect(self::activities())
            ->where('subject_id', $subject_id)
            ->when($type !== '', fn ($items) => $items->where('type', $type))
            ->map(function (array $activity) use ($types) {
                $activity['type_label'] = $types[$activity['type']] ?? $activity['type'];
                $activity['submitted'] = self::submission($activity['id']) !== null;

                return $activity;
            })
            ->values()
            ->all();
    }

    /** @param array<string, mixed> $activity */
    public static function addActivity(array $activity): void
    {
        self::pushSessionItem(self::SESSION_ACTIVITIES_KEY, $activity);
    }

    /** @return array<string, mixed>|null */
    public static function activity(string $id): ?array
    {
        foreach (self::activities() as $activity) {
            if ($activity['id'] === $id) {
                $types = self::activityTypes();
                $activity['type_label'] = $types[$activity['type']] ?? $activity['type'];
                $activity['items'] = self::resolveItems($activity);
                $activity['submission'] = self::submission($id);
                $activity['submitted'] = $activity['submission'] !== null;

                return $activity;
            }
        }

        return null;
    }

    /** @return array<string, mixed>|null */
    public static function submission(string $activity_id): ?array
    {
        $submissions = session(self::SESSION_SUBMISSIONS_KEY, []);

        if (! is_array($submissions) || ! isset($submissions[$activity_id]) || ! is_array($submissions[$activity_id])) {
            return null;
        }

        return $submissions[$activity_id];
    }

    /** @param array<string, mixed> $submission */
    public static function saveSubmission(string $activity_id, array $submission): void
    {
        $submissions = session(self::SESSION_SUBMISSIONS_KEY, []);
        if (! is_array($submissions)) {
            $submissions = [];
        }
        $submissions[$activity_id] = $submission;
        session([self::SESSION_SUBMISSIONS_KEY => $submissions]);
    }

    public static function clearSubmission(string $activity_id): void
    {
        $submissions = session(self::SESSION_SUBMISSIONS_KEY, []);
        if (! is_array($submissions)) {
            return;
        }
        unset($submissions[$activity_id]);
        session([self::SESSION_SUBMISSIONS_KEY => $submissions]);
    }

    /**
     * @param  array<string, mixed>  $activity
     * @return list<array<string, mixed>>
     */
    public static function resolveItems(array $activity): array
    {
        $bank = self::itemBank();
        if (isset($bank[$activity['id']])) {
            return $bank[$activity['id']];
        }

        return match ($activity['type'] ?? '') {
            'evaluation' => self::fallbackEvaluationItems($activity),
            'homework' => self::fallbackHomeworkItems($activity),
            default => self::fallbackClassExerciseItems($activity),
        };
    }

    /**
     * @return array<string, list<array<string, mixed>>>
     */
    private static function itemBank(): array
    {
        return [
            'act-mat-1' => [
                ['id' => 'q1', 'kind' => 'choice', 'prompt' => '¿Cuál es la solución de 2x + 4 = 10?', 'options' => ['a' => 'x = 2', 'b' => 'x = 3', 'c' => 'x = 5', 'd' => 'x = 7'], 'correct' => 'b'],
                ['id' => 'q2', 'kind' => 'choice', 'prompt' => 'El sistema x + y = 5 y x − y = 1 tiene solución:', 'options' => ['a' => '(2, 3)', 'b' => '(3, 2)', 'c' => '(1, 4)', 'd' => '(5, 0)'], 'correct' => 'b'],
                ['id' => 'q3', 'kind' => 'choice', 'prompt' => 'Factorizar x² − 9 da como resultado:', 'options' => ['a' => '(x − 9)(x + 1)', 'b' => '(x − 3)(x + 3)', 'c' => '(x − 3)²', 'd' => 'x(x − 9)'], 'correct' => 'b'],
            ],
            'act-eng-1' => [
                ['id' => 'q1', 'kind' => 'choice', 'prompt' => 'En el audio, ¿adónde va la familia el sábado?', 'options' => ['a' => 'Al mercado', 'b' => 'Al museo', 'c' => 'Al parque', 'd' => 'Al cine'], 'correct' => 'c'],
                ['id' => 'q2', 'kind' => 'choice', 'prompt' => 'La palabra “umbrella” del diálogo significa:', 'options' => ['a' => 'Abrigo', 'b' => 'Paraguas', 'c' => 'Mochila', 'd' => 'Bufanda'], 'correct' => 'b'],
                ['id' => 'q3', 'kind' => 'choice', 'prompt' => '¿Qué tiempo verbal predomina en la conversación?', 'options' => ['a' => 'Present simple', 'b' => 'Past perfect', 'c' => 'Future continuous', 'd' => 'Past perfect continuous'], 'correct' => 'a'],
            ],
            'act-sci-1' => [
                ['id' => 'q1', 'kind' => 'choice', 'prompt' => '¿Qué organelo produce la mayor parte de la energía de la célula?', 'options' => ['a' => 'Núcleo', 'b' => 'Mitocondria', 'c' => 'Ribosoma', 'd' => 'Vacuola'], 'correct' => 'b'],
                ['id' => 'q2', 'kind' => 'choice', 'prompt' => 'La membrana celular se caracteriza por ser:', 'options' => ['a' => 'Rígida e impermeable', 'b' => 'Selectivamente permeable', 'c' => 'Solo presente en bacterias', 'd' => 'Igual a la pared celular'], 'correct' => 'b'],
                ['id' => 'q3', 'kind' => 'choice', 'prompt' => 'Los cloroplastos se encuentran principalmente en:', 'options' => ['a' => 'Células animales', 'b' => 'Células vegetales', 'c' => 'Hongos', 'd' => 'Glóbulos rojos'], 'correct' => 'b'],
            ],
            'act-mat-2' => [
                ['id' => 'development', 'kind' => 'essay', 'prompt' => 'Resuelve los ejercicios 1 al 20 del cuadernillo de factorización. Explica al menos dos casos (diferencia de cuadrados y trinomio cuadrado perfecto) con un ejemplo de cada uno.'],
            ],
            'act-eng-2' => [
                ['id' => 'development', 'kind' => 'essay', 'prompt' => 'Lee los capítulos 1 a 4 de The Little Prince y responde: ¿qué descubre el narrador sobre el principito? Relaciona una cita con una idea propia (mínimo 180 palabras).'],
            ],
            'act-mat-3' => [
                ['id' => 'e1', 'kind' => 'short', 'prompt' => 'Calcula 15 × 8 + 12.'],
                ['id' => 'e2', 'kind' => 'short', 'prompt' => 'Simplifica la fracción 18/24.'],
                ['id' => 'e3', 'kind' => 'short', 'prompt' => '¿Cuánto es 3² + 4²?'],
            ],
            'act-sci-2' => [
                ['id' => 'e1', 'kind' => 'short', 'prompt' => '¿Qué estructura observas primero al enfocar el microscopio?'],
                ['id' => 'e2', 'kind' => 'short', 'prompt' => 'Nombra un organelo visible en la célula vegetal.'],
                ['id' => 'e3', 'kind' => 'short', 'prompt' => '¿Qué diferencia notas entre célula vegetal y animal en esta práctica?'],
            ],
        ];
    }

    /**
     * @param  array<string, mixed>  $activity
     * @return list<array<string, mixed>>
     */
    private static function fallbackEvaluationItems(array $activity): array
    {
        $title = (string) ($activity['title'] ?? 'el tema');

        return [
            ['id' => 'q1', 'kind' => 'choice', 'prompt' => 'Sobre “'.$title.'”, ¿cuál afirmación es correcta?', 'options' => ['a' => 'Resume la idea principal trabajada en clase.', 'b' => 'No se relaciona con los contenidos del período.', 'c' => 'Solo aplica a otra asignatura.', 'd' => 'Es un procedimiento opcional sin evaluación.'], 'correct' => 'a'],
            ['id' => 'q2', 'kind' => 'choice', 'prompt' => 'Para resolver esta evaluación debes:', 'options' => ['a' => 'Ignorar la consigna y responder al azar.', 'b' => 'Aplicar lo visto en clase con un ejemplo.', 'c' => 'Copiar la definición sin interpretarla.', 'd' => 'Dejar las preguntas en blanco.'], 'correct' => 'b'],
            ['id' => 'q3', 'kind' => 'choice', 'prompt' => 'Una evidencia de comprensión del tema es:', 'options' => ['a' => 'Memorizar fechas sin contexto.', 'b' => 'Explicar el procedimiento con tus palabras.', 'c' => 'Cambiar de tema a mitad de la prueba.', 'd' => 'Usar solo ejemplos de otro curso.'], 'correct' => 'b'],
        ];
    }

    /**
     * @param  array<string, mixed>  $activity
     * @return list<array<string, mixed>>
     */
    private static function fallbackHomeworkItems(array $activity): array
    {
        $description = trim((string) ($activity['description'] ?? ''));
        $prompt = $description !== '' && $description !== 'Sin descripción'
            ? $description.' Desarrolla tu respuesta con argumentos y un ejemplo.'
            : 'Desarrolla la tarea “'.$activity['title'].'”. Incluye procedimiento, conclusión y un ejemplo.';

        return [
            ['id' => 'development', 'kind' => 'essay', 'prompt' => $prompt],
        ];
    }

    /**
     * @param  array<string, mixed>  $activity
     * @return list<array<string, mixed>>
     */
    private static function fallbackClassExerciseItems(array $activity): array
    {
        $title = (string) ($activity['title'] ?? 'el ejercicio');

        return [
            ['id' => 'e1', 'kind' => 'short', 'prompt' => 'En una frase, ¿cuál es el objetivo de “'.$title.'”?'],
            ['id' => 'e2', 'kind' => 'short', 'prompt' => 'Escribe el primer paso para resolverlo en clase.'],
            ['id' => 'e3', 'kind' => 'short', 'prompt' => 'Anota un resultado o evidencia de lo que observaste.'],
        ];
    }

    public static function activitiesCount(string $subject_id): int
    {
        return count(self::activitiesForSubject($subject_id));
    }

    /** @return list<array<string, mixed>> */
    public static function courseReports(): array
    {
        return array_map(function (array $course) {
            $students = $course['students_count'];
            $average = self::demoScore((int) crc32($course['id']));
            $passed = (int) round($students * 0.78);

            return [
                'id' => $course['id'],
                'code' => $course['code'],
                'name' => $course['name'],
                'grade' => $course['grade'],
                'section' => $course['section'],
                'teacher' => $course['teacher'],
                'students' => $students,
                'average' => $average,
                'passed' => min($passed, $students),
                'at_risk' => max($students - $passed, 0),
                'status' => $average >= 3.5 ? 'En meta' : 'Requiere apoyo',
            ];
        }, self::coursesWithCounts());
    }

    /** @return list<array<string, mixed>> */
    public static function studentReports(): array
    {
        $rows = [];

        foreach (self::coursesWithCounts() as $course) {
            foreach (self::studentsForCourse($course['id']) as $student) {
                $average = self::demoScore((int) $student['id'] * 13);
                $rows[] = [
                    'id' => $student['id'],
                    'name' => $student['name'],
                    'document' => $student['document'],
                    'email' => $student['email'],
                    'course_id' => $course['id'],
                    'course_name' => $course['name'],
                    'grade' => $course['grade'],
                    'section' => $course['section'],
                    'average' => $average,
                    'evaluations' => self::demoScore((int) $student['id'] * 7),
                    'homework' => self::demoScore((int) $student['id'] * 11),
                    'class_exercises' => self::demoScore((int) $student['id'] * 19),
                    'status' => $average >= 3.5 ? 'Aprobado' : 'En riesgo',
                ];
            }
        }

        return $rows;
    }

    /** @return array<string, mixed>|null */
    public static function courseReport(string $id): ?array
    {
        foreach (self::courseReports() as $row) {
            if ($row['id'] !== $id) {
                continue;
            }

            $course = self::course($id);
            $row['shift_label'] = $course['shift_label'] ?? '';
            $row['schedule'] = $course['schedule'] ?? '';
            $row['room'] = $course['room'] ?? '';
            $row['student_rows'] = array_values(array_filter(
                self::studentReports(),
                fn (array $student) => $student['course_id'] === $id
            ));

            return $row;
        }

        return null;
    }

    /** @return array<string, mixed>|null */
    public static function studentReport(int $id): ?array
    {
        foreach (self::studentReports() as $row) {
            if ((int) $row['id'] !== $id) {
                continue;
            }

            $row['items'] = self::gradeItemsForStudent($id);
            $course = self::course($row['course_id']);
            $row['teacher'] = $course['teacher'] ?? '';
            $row['shift_label'] = $course['shift_label'] ?? '';
            $row['period'] = 'Primer periodo 2026';

            return $row;
        }

        return null;
    }

    /** @return list<array<string, mixed>> */
    public static function gradeItemsForStudent(int $student_id): array
    {
        $types = self::activityTypes();
        $items = [
            ['type' => 'evaluation', 'name' => 'Parcial 1'],
            ['type' => 'evaluation', 'name' => 'Parcial 2'],
            ['type' => 'homework', 'name' => 'Taller 1'],
            ['type' => 'homework', 'name' => 'Taller 2'],
            ['type' => 'class_exercise', 'name' => 'Quiz en clase 1'],
            ['type' => 'class_exercise', 'name' => 'Quiz en clase 2'],
        ];

        return array_map(function (array $item, int $index) use ($student_id, $types) {
            $item['type_label'] = $types[$item['type']] ?? $item['type'];
            $item['score'] = self::demoScore($student_id * (11 + ($index * 5)));
            $item['max_score'] = 5;

            return $item;
        }, $items, array_keys($items));
    }

    /** @return array<string, string> */
    public static function documentTypes(): array
    {
        return [
            'birth_certificate' => 'Registro civil',
            'photo' => 'Foto tipo documento',
            'medical' => 'Certificado médico',
            'academic_record' => 'Boletín o certificado de notas',
            'guardian_id' => 'Copia del documento del acudiente',
        ];
    }

    /** @return array<string, string> */
    public static function paymentMethods(): array
    {
        return [
            'transfer' => 'Transferencia',
            'cash' => 'Efectivo',
            'card' => 'Tarjeta',
        ];
    }

    /** @return array<string, string> */
    public static function enrollmentStatuses(): array
    {
        return [
            'documents_pending' => 'Documentos pendientes',
            'pending_payment' => 'Pago pendiente',
            'completed' => 'Inscrito',
        ];
    }

    /** @return list<array<string, mixed>> */
    public static function defaultEnrollments(): array
    {
        return [
            [
                'id' => 'enr-1',
                'parent_name' => 'Carolina Mejía',
                'parent_document' => '52123456',
                'parent_email' => 'carolina.mejia@mail.com',
                'parent_phone' => '3001234567',
                'child_name' => 'Martina Mejía',
                'child_document' => '1090123456',
                'grade' => '6°',
                'shift' => 'morning',
                'documents' => [
                    ['type' => 'birth_certificate', 'label' => 'Registro civil', 'name' => 'registro-civil-martina.pdf', 'uploaded' => true],
                    ['type' => 'photo', 'label' => 'Foto tipo documento', 'name' => 'foto-martina.jpg', 'uploaded' => true],
                    ['type' => 'medical', 'label' => 'Certificado médico', 'name' => 'certificado-medico.pdf', 'uploaded' => true],
                    ['type' => 'academic_record', 'label' => 'Boletín o certificado de notas', 'name' => 'boletin-2025.pdf', 'uploaded' => true],
                    ['type' => 'guardian_id', 'label' => 'Copia del documento del acudiente', 'name' => 'cc-carolina.pdf', 'uploaded' => true],
                ],
                'payment_amount' => 850000,
                'payment_method' => 'transfer',
                'payment_reference' => 'TRX-90821',
                'status' => 'completed',
            ],
            [
                'id' => 'enr-2',
                'parent_name' => 'Andrés Castaño',
                'parent_document' => '79876543',
                'parent_email' => 'andres.castano@mail.com',
                'parent_phone' => '3109876543',
                'child_name' => 'Liam Castaño',
                'child_document' => '1090789012',
                'grade' => '8°',
                'shift' => 'afternoon',
                'documents' => [
                    ['type' => 'birth_certificate', 'label' => 'Registro civil', 'name' => 'registro-liam.pdf', 'uploaded' => true],
                    ['type' => 'photo', 'label' => 'Foto tipo documento', 'name' => 'foto-liam.jpg', 'uploaded' => true],
                    ['type' => 'medical', 'label' => 'Certificado médico', 'name' => null, 'uploaded' => false],
                    ['type' => 'academic_record', 'label' => 'Boletín o certificado de notas', 'name' => 'notas-liam.pdf', 'uploaded' => true],
                    ['type' => 'guardian_id', 'label' => 'Copia del documento del acudiente', 'name' => 'cc-andres.pdf', 'uploaded' => true],
                ],
                'payment_amount' => 850000,
                'payment_method' => 'card',
                'payment_reference' => '',
                'status' => 'documents_pending',
            ],
            [
                'id' => 'enr-3',
                'parent_name' => 'Paola Restrepo',
                'parent_document' => '43456789',
                'parent_email' => 'paola.restrepo@mail.com',
                'parent_phone' => '3154441122',
                'child_name' => 'Emma Restrepo',
                'child_document' => '1090456789',
                'grade' => '10°',
                'shift' => 'morning',
                'documents' => [
                    ['type' => 'birth_certificate', 'label' => 'Registro civil', 'name' => 'registro-emma.pdf', 'uploaded' => true],
                    ['type' => 'photo', 'label' => 'Foto tipo documento', 'name' => 'foto-emma.jpg', 'uploaded' => true],
                    ['type' => 'medical', 'label' => 'Certificado médico', 'name' => 'medico-emma.pdf', 'uploaded' => true],
                    ['type' => 'academic_record', 'label' => 'Boletín o certificado de notas', 'name' => 'boletin-emma.pdf', 'uploaded' => true],
                    ['type' => 'guardian_id', 'label' => 'Copia del documento del acudiente', 'name' => 'cc-paola.pdf', 'uploaded' => true],
                ],
                'payment_amount' => 920000,
                'payment_method' => 'transfer',
                'payment_reference' => '',
                'status' => 'pending_payment',
            ],
        ];
    }

    /** @return list<array<string, mixed>> */
    public static function enrollments(): array
    {
        $items = array_values(array_merge(self::defaultEnrollments(), self::sessionList(self::SESSION_ENROLLMENTS_KEY)));

        return array_map(fn (array $item) => self::decorateEnrollment($item), $items);
    }

    /** @return array<string, mixed>|null */
    public static function enrollment(string $id): ?array
    {
        foreach (self::enrollments() as $enrollment) {
            if ($enrollment['id'] === $id) {
                return $enrollment;
            }
        }

        return null;
    }

    /** @param array<string, mixed> $enrollment */
    public static function addEnrollment(array $enrollment): void
    {
        self::pushSessionItem(self::SESSION_ENROLLMENTS_KEY, $enrollment);
    }

    /** @param array<string, mixed> $enrollment */
    public static function decorateEnrollment(array $enrollment): array
    {
        $statuses = self::enrollmentStatuses();
        $methods = self::paymentMethods();
        $uploaded = collect($enrollment['documents'] ?? [])->where('uploaded', true)->count();
        $total_docs = count($enrollment['documents'] ?? []);

        $enrollment['shift_label'] = self::shifts()[$enrollment['shift']] ?? $enrollment['shift'];
        $enrollment['status_label'] = $statuses[$enrollment['status']] ?? $enrollment['status'];
        $enrollment['payment_method_label'] = $methods[$enrollment['payment_method']] ?? $enrollment['payment_method'];
        $enrollment['documents_uploaded'] = $uploaded;
        $enrollment['documents_total'] = $total_docs;

        return $enrollment;
    }

    public static function demoScore(int $seed): float
    {
        $value = 2.8 + fmod(abs($seed), 220) / 100;

        return round(min($value, 5.0), 1);
    }

    /** @return list<array<string, mixed>> */
    private static function sessionList(string $key): array
    {
        $created = session($key, []);

        return is_array($created) ? $created : [];
    }

    /** @param array<string, mixed> $item */
    private static function pushSessionItem(string $key, array $item): void
    {
        $created = self::sessionList($key);
        $created[] = $item;
        session([$key => $created]);
    }
}
