<?php

namespace App\Filament\Resources;

use Filament\Forms;
use Filament\Tables;
use App\Models\Tools;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Resources\Resource;
use Filament\Forms\Components\Card;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Textarea;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
use Illuminate\Database\Eloquent\Builder;
use App\Filament\Resources\ToolResource\Pages;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\ToolResource\RelationManagers;

class ToolResource extends Resource
{
    protected static ?string $model = Tools::class;

    protected static ?string $pluralLabel = 'Data Alat';

    protected static ?string $navigationGroup = 'Master Data';
    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?int $navigationSort = 3;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Card::make()
                ->schema([
                    TextInput::make('code')->label('Kode Alat')
                        ->required()
                        ->maxLength(20)
                        ->unique(ignorable: fn($record) => $record)
                        ->reactive()
                        ->hint(fn ($state) => strlen($state) . ' / 20 karakter'),
                    TextInput::make('name')
                        ->label('Nama Alat')
                        ->required()
                        ->maxLength(250)
                        ->reactive()
                        ->hint(fn ($state) => strlen($state) . ' / 250 karakter'),
                    Textarea::make('description')->label('Deskripsi'),
                    Select::make('status')
                        ->label('Status')
                        ->options([
                            0 => 'Tidak Aktif',
                            1 => 'Scan RFID Baru',
                            2 => 'Scan Presensi',
                        ])
                        ->required(),
                ])
                ->columns(1),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('code')->label('Kode Alat')->sortable()->searchable(),
                TextColumn::make('name')->label('Nama Alat')->sortable()->searchable(),
                TextColumn::make('status')
                    ->label('Status')
                    ->sortable()
                    ->formatStateUsing(function ($state) {
                        return match ($state) {
                            0 => 'Tidak Aktif',
                            1 => 'Scan RFID Baru',
                            2 => 'Scan Presensi',
                            default => 'Tidak Diketahui',
                        };
                    }),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
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
            'index' => Pages\ListTools::route('/'),
            'create' => Pages\CreateTool::route('/create'),
            'edit' => Pages\EditTool::route('/{record}/edit'),
        ];
    }
}
