<?php

namespace App\Exports;

use App\Services\InteractiveQuizService;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Illuminate\Support\Collection;

class InteractiveLeaderboardExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithTitle
{
    protected $quizId;
    protected $quizName;

    public function __construct($quizId, $quizName = 'Interactive Quiz')
    {
        $this->quizId = $quizId;
        $this->quizName = $quizName;
    }

    /**
     * @return Collection
     */
    public function collection()
    {
        $leaderboard = InteractiveQuizService::getLeaderboard($this->quizId);
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
        ];
    }

    /**
     * @param mixed $row
     * @return array
     */
    public function map($row): array
    {
        return [
            $row['rank'],
            $row['name'],
            $row['email'],
            $row['score'],
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
                    'startColor' => ['rgb' => 'B4120D']
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
