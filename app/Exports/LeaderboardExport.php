<?php

namespace App\Exports;

use App\Services\QuizCacheService;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Illuminate\Support\Collection;

class LeaderboardExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithTitle
{
    protected $quizId;
    protected $quizName;

    public function __construct($quizId, $quizName = 'Quiz')
    {
        $this->quizId = $quizId;
        $this->quizName = $quizName;
    }

    /**
     * @return Collection
     */
    public function collection()
    {
        $leaderboard = QuizCacheService::getLeaderboard($this->quizId);
        return collect($leaderboard);
    }

    /**
     * @return array
     */
    public function headings(): array
    {
        return [
            'Rank',
            'Participant Name',
            'Email',
            'Score',
            'Fullscreen Violations',
        ];
    }

    /**
     * @param mixed $row
     * @return array
     */
    public function map($row): array
    {
        // Format violations summary
        $violationCount = $row['violations_count'] ?? 0;
        $violationSummary = $violationCount . ' violation(s)';
        if ($violationCount > 0 && !empty($row['violations'])) {
            $details = [];
            foreach ($row['violations'] as $v) {
                $typeMap = [
                    'fullscreen_exit' => 'Fullscreen Exit',
                    'tab_switch' => 'Tab Switch',
                    'focus_lost' => 'Focus Lost (2nd Screen)',
                    'screenshot_attempt' => 'Screenshot Attempt',
                ];
                $type = $typeMap[$v['type'] ?? 'unknown'] ?? $v['type'] ?? 'Unknown';
                $time = $v['recorded_at'] ?? date('Y-m-d H:i:s', $v['timestamp'] ?? 0);
                $q = isset($v['question_number']) ? "Q{$v['question_number']}" : '';
                $details[] = "{$type} at {$time}" . ($q ? " ({$q})" : '');
            }
            $violationSummary .= ': ' . implode('; ', $details);
        }

        return [
            $row['rank'],
            $row['name'],
            $row['email'],
            $row['score'],
            $violationSummary,
        ];
    }

    /**
     * @param Worksheet $sheet
     * @return array
     */
    public function styles(Worksheet $sheet)
    {
        return [
            1 => [
                'font' => [
                    'bold' => true,
                    'size' => 12,
                    'color' => ['rgb' => 'FFFFFF']
                ],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '4472C4']
                ],
            ],
        ];
    }

    /**
     * @return string
     */
    public function title(): string
    {
        return 'Leaderboard';
    }
}
