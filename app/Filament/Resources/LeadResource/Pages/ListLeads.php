<?php

namespace App\Filament\Resources\LeadResource\Pages;

use App\Enums\LeadStatus;
use App\Filament\Resources\LeadResource;
use App\Models\Lead;
use Filament\Actions;
use Filament\Resources\Components\Tab;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ListLeads extends ListRecords
{
    protected static string $resource = LeadResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('exportCsv')
                ->label('Exportar CSV')
                ->icon('heroicon-o-arrow-down-tray')
                ->color('gray')
                ->action(fn () => $this->exportCsv()),

            Actions\CreateAction::make()->label('Novo Lead Manual'),
        ];
    }

    public function getTabs(): array
    {
        return [
            'todos' => Tab::make('Todos')
                ->badge(Lead::count()),

            'em_fluxo_1' => Tab::make('Em Teste (Fluxo 1)')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', LeadStatus::EmFluxo1))
                ->badge(Lead::where('status', LeadStatus::EmFluxo1)->count())
                ->badgeColor('info'),

            'em_fluxo_2' => Tab::make('Pós-Teste (Fluxo 2)')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', LeadStatus::EmFluxo2))
                ->badge(Lead::where('status', LeadStatus::EmFluxo2)->count())
                ->badgeColor('warning'),

            'engajados' => Tab::make('Engajados')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', LeadStatus::Engajado))
                ->badge(Lead::where('status', LeadStatus::Engajado)->count())
                ->badgeColor('purple'),

            'assinou' => Tab::make('Assinaram 🎉')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', LeadStatus::Assinou))
                ->badge(Lead::where('status', LeadStatus::Assinou)->count())
                ->badgeColor('success'),

            'nao_assinou' => Tab::make('Não Assinou')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', LeadStatus::NaoAssinou))
                ->badge(Lead::where('status', LeadStatus::NaoAssinou)->count())
                ->badgeColor('danger'),

            'descadastrado' => Tab::make('Descadastrados')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', LeadStatus::Descadastrado))
                ->badge(Lead::where('status', LeadStatus::Descadastrado)->count())
                ->badgeColor('secondary'),
        ];
    }

    protected function exportCsv(): StreamedResponse
    {
        $fileName = 'leads-documentalista-' . date('Y-m-d-His') . '.csv';

        return response()->streamDownload(function () {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['ID', 'Nome', 'E-mail', 'Telefone', 'Status', 'Score', 'Dias_Restantes_Trial', 'Data_Cadastro']);

            Lead::chunk(200, function ($leads) use ($handle) {
                foreach ($leads as $lead) {
                    fputcsv($handle, [
                        $lead->id,
                        $lead->name,
                        $lead->email,
                        $lead->phone,
                        $lead->status->label(),
                        $lead->score,
                        $lead->days_remaining_trial,
                        $lead->created_at->format('d/m/Y H:i:s'),
                    ]);
                }
            });

            fclose($handle);
        }, $fileName, ['Content-Type' => 'text/csv']);
    }
}
