<?php

namespace App\Filament\Resources;

use Filament\Forms;
use Filament\Tables;
use App\Models\Student;
use Filament\Forms\Form;
use App\Models\ClassModel;
use App\Models\TempRfid;
use Filament\Tables\Table;
use Filament\Resources\Resource;
use Filament\Forms\Components\Card;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Textarea;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Columns\ImageColumn;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Illuminate\Database\Eloquent\Builder;
use Filament\Forms\Components\Actions\Action;
use App\Filament\Resources\StudentResource\Pages;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\StudentResource\RelationManagers;

class StudentResource extends Resource
{
    protected static ?string $model = Student::class;

    protected static ?string $pluralLabel = 'Data Siswa';

    protected static ?string $navigationGroup = 'Master Data';
    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Card::make()
                    ->schema([
                        TextInput::make('nis')->label('Nomor Induk Siswa')->required()->unique(ignorable: fn($record) => $record),
                        TextInput::make('name')->label('Nama Lengkap')->required(),
                        Textarea::make('address')->label('Alamat')->required(),
                        Select::make('class_id')
                            ->label('Kelas')
                            ->options(ClassModel::all()->pluck('name', 'id'))
                            ->searchable()
                            ->required(),
                        DatePicker::make('birthday')->label('Tanggal Lahir')->required(),
                        TextInput::make('phone')->label('Nomor Telepon'),
                        TextInput::make('email')->label('Email'),
                        TextInput::make('rfid_number')
                            ->label('Nomor RFID')
                            ->unique(ignorable: fn($record) => $record)
                            ->required()
                            ->readonly()
                            ->suffixAction(
                                Action::make('pilihRfid')
                                    ->label('Pilih')
                                    ->icon('heroicon-m-magnifying-glass')
                                    ->modalHeading('Pilih Nomor RFID')
                                    ->modalContent(function ($state, callable $set) {
                                        return view('filament.forms.components.rfid-modal-table', [
                                            'onSelect' => function ($value) use ($set) {
                                                $set('rfid_number', $value);
                                            },
                                        ]);
                                    })
                                    ->modalSubmitAction(false)
                                    ->modalCancelActionLabel('Tutup')
                                ),
                        FileUpload::make('photo')->label('Foto'),
                        Toggle::make('is_active')->label('Aktif')->default(true),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('nis')->label('Nomor Induk Siswa')->sortable()->searchable(),
                TextColumn::make('name')->label('Nama Lengkap')->sortable()->searchable(),
                TextColumn::make('address')->label('Alamat')->searchable(),
                TextColumn::make('class.name')->label('Kelas')->sortable(),
                TextColumn::make('birthday')->label('Tanggal Lahir'),
                ImageColumn::make('photo')->label('Foto'),
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
            'index' => Pages\ListStudents::route('/'),
            'create' => Pages\CreateStudent::route('/create'),
            'edit' => Pages\EditStudent::route('/{record}/edit'),
        ];
    }
}
