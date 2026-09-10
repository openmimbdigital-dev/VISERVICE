<?php

namespace App\Support\Presentation;

class ParentAppDemoData
{
    public const CHILD_NAME = 'Martina Mejía';

    public const GUARDIAN_NAME = 'Carolina Mejía';

    /** @return array<string, string> */
    public static function guardian(): array
    {
        return [
            'name' => self::GUARDIAN_NAME,
            'email' => 'carolina.mejia@mail.com',
            'phone' => '3001234567',
        ];
    }

    /** @return array<string, string> */
    public static function child(): array
    {
        return [
            'name' => self::CHILD_NAME,
            'grade' => '6°',
            'section' => 'B',
            'shift' => 'Mañana',
            'course_id' => 'art-6b',
            'enrollment_id' => 'enr-1',
        ];
    }

    /** @return list<array<string, mixed>> */
    public static function subjects(): array
    {
        $ids = ['sub-art-b', 'sub-mat-a', 'sub-sci-a', 'sub-mus-a'];

        return collect(AcademicDemoData::subjects())
            ->whereIn('id', $ids)
            ->map(function (array $subject) {
                $subject['shift_label'] = AcademicDemoData::shifts()[$subject['shift']] ?? $subject['shift'];
                $subject['activities_count'] = AcademicDemoData::activitiesCount($subject['id']);

                return $subject;
            })
            ->values()
            ->all();
    }

    /** @return list<array<string, mixed>> */
    public static function invoices(): array
    {
        return collect(BillingDemoData::invoices())
            ->where('student', self::CHILD_NAME)
            ->values()
            ->all();
    }

    /** @return list<array<string, mixed>> */
    public static function events(): array
    {
        return EventDemoData::events();
    }
}
