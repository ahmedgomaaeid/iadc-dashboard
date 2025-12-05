<?php

namespace App\Exports;

use App\Models\DynamicForm;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Illuminate\Support\Collection;

class DynamicFormSubmissionExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithTitle
{
    protected DynamicForm $form;
    protected array $fieldNames;

    public function __construct(DynamicForm $form)
    {
        $this->form = $form;
        $this->fieldNames = array_keys($form->getOrderedFields());
    }

    /**
     * @return Collection
     */
    public function collection()
    {
        return $this->form->submissions()->latest()->get();
    }

    /**
     * @return array
     */
    public function headings(): array
    {
        $headings = ['#', 'Submitted At'];
        
        foreach ($this->fieldNames as $fieldName) {
            $fieldConfig = DynamicForm::AVAILABLE_FIELDS[$fieldName] ?? null;
            $headings[] = $fieldConfig['label'] ?? ucfirst(str_replace('_', ' ', $fieldName));
        }

        return $headings;
    }

    /**
     * @param mixed $submission
     * @return array
     */
    public function map($submission): array
    {
        $row = [
            $submission->id,
            $submission->created_at->format('Y-m-d H:i:s'),
        ];

        foreach ($this->fieldNames as $fieldName) {
            $row[] = $submission->data[$fieldName] ?? '';
        }

        return $row;
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
        return 'Form Submissions';
    }
}
