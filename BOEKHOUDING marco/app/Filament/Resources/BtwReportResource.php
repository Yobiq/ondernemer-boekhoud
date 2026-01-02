<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BtwReportResource\Pages;
use App\Filament\Resources\BtwReportResource\RelationManagers;
use App\Models\BtwReport;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class BtwReportResource extends Resource
{
    protected static ?string $model = BtwReport::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-chart-bar';
    
    protected static ?string $navigationLabel = 'BTW Rapporten';
    
    protected static ?string $navigationGroup = 'Financieel';
    
    protected static ?int $navigationSort = 12;
    
    protected static bool $shouldRegisterNavigation = false;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                //
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                //
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListBtwReports::route('/'),
            'create' => Pages\CreateBtwReport::route('/create'),
            'edit' => Pages\EditBtwReport::route('/{record}/edit'),
        ];
    }
}
