<?php

namespace App\Services;

use App\Models\ReportCard;
use App\Models\SchoolSettings;
use App\Models\Score;
use Barryvdh\DomPDF\Facade\Pdf;

class ReportCardRenderService
{
    public function view(ReportCard $reportCard, array $portal = [])
    {
        return view('admin.report-cards.nigerian-pdf', array_merge(
            $this->viewData($reportCard, 'browser'),
            ['portal' => $portal]
        ));
    }

    public function download(ReportCard $reportCard)
    {
        $pdf = Pdf::loadView('admin.report-cards.nigerian-pdf', $this->viewData($reportCard, 'pdf'));

        $pdf->setPaper('A4', 'portrait');
        $pdf->setOptions([
            'isHtml5ParserEnabled' => true,
            'isRemoteEnabled' => true,
            'defaultPaperSize' => 'a4',
        ]);

        $filenameBase = "Report_Card_{$reportCard->student->name}_{$reportCard->session->name}_{$reportCard->term->name}";
        $filename = preg_replace('/[^A-Za-z0-9_\-]/', '_', $filenameBase) . '.pdf';

        return response($pdf->output(), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            'Cache-Control' => 'private, max-age=0, must-revalidate',
            'Pragma' => 'public',
        ]);
    }

    private function viewData(ReportCard $reportCard, string $renderMode): array
    {
        $reportCard->loadMissing(['student.class', 'session', 'term']);

        $scores = $reportCard->scores();

        $schoolSettings = SchoolSettings::getSettings();
        $colorSchemes = $this->colorSchemes();
        $selectedColor = $colorSchemes[$reportCard->theme_color ?? 'blue'] ?? $colorSchemes['blue'];

        return compact('reportCard', 'scores', 'schoolSettings', 'selectedColor', 'renderMode');
    }

    private function colorSchemes(): array
    {
        return [
            'blue' => ['primary' => '#1E40AF', 'secondary' => '#3B82F6', 'light' => '#DBEAFE'],
            'green' => ['primary' => '#15803D', 'secondary' => '#22C55E', 'light' => '#DCFCE7'],
            'brown' => ['primary' => '#78350F', 'secondary' => '#A16207', 'light' => '#FEF3C7'],
            'pink' => ['primary' => '#BE123C', 'secondary' => '#F472B6', 'light' => '#FCE7F3'],
            'purple' => ['primary' => '#6B21A8', 'secondary' => '#A855F7', 'light' => '#F3E8FF'],
        ];
    }
}
