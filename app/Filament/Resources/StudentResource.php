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
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;
use PhpOffice\PhpSpreadsheet\IOFactory;
use App\Imports\StudentImport;
use Intervention\Image\ImageManagerStatic as Image;

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
                            // ->required()
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
                        FileUpload::make('photo')
                            ->label('Foto')
                            ->image()
                            ->disk('public')
                            ->directory('photos')
                            ->visibility('public')
                            ->maxSize(1024)
                            ->imageEditor()                // aktifkan image editor
                            ->imageEditorAspectRatios([    // user bisa pilih crop
                                '1:1',                     // square
                                '4:3',
                                '16:9',
                                null,                      // bebas
                            ])
                            ->imageEditorMode(2)           // 1: crop saja, 2: crop + resize
                            ->panelLayout('compact'), // lebih ringan dari 'integrated'
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
                ImageColumn::make('photo')
                    ->label('Foto')
                    ->disk('public')        
                    ->visibility('public')  
                    ->size(50)              
                    ->circular(),
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

                    Tables\Actions\BulkAction::make('Import Data')
                        ->form([
                            FileUpload::make('file')
                                ->label('File Excel')
                                ->acceptedFileTypes(['application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'])
                                ->required()
                                ->disk('local'),
                        ])
                        ->action(function (array $data) {
                            $path = Storage::disk('local')->path($data['file']);
                            $spreadsheet = IOFactory::load($path);
                            $sheet = $spreadsheet->getActiveSheet();
                            $rows = $sheet->toArray();

                            if (empty($rows) || empty($rows[0])) {
                                Notification::make()
                                    ->danger()
                                    ->title('Gagal Import')
                                    ->body('File Excel tidak memiliki data atau format tidak sesuai.')
                                    ->send();
                                return;
                            }

                            $header = array_map('strtolower', $rows[0]);
                            unset($rows[0]);

                            $errors = [];
                            foreach ($rows as $i => $row) {
                                $rowData = array_combine($header, $row);

                                // Cari ID kelas berdasarkan nama
                                $class = \App\Models\ClassModel::where('name', $rowData['class_name'] ?? null)->first();

                                if (!$class) {
                                    $errors[] = array_merge($rowData, ['error' => 'Class not found']);
                                    continue;
                                }

                                try {
                                    \App\Models\Student::create([
                                        'nis' => $rowData['nis'],
                                        'name' => $rowData['name'],
                                        'address' => $rowData['address'],
                                        'class_id' => $class->id,
                                        'birthday' => $rowData['birthday'],
                                        'phone' => $rowData['phone'] ?? null,
                                        'email' => $rowData['email'] ?? null,
                                        'is_active' => $rowData['is_active'] ?? true,
                                    ]);
                                } catch (\Throwable $e) {
                                    $errors[] = array_merge($rowData, ['error' => $e->getMessage()]);
                                }
                            }

                            if (!empty($errors)) {
                                $errorFilename = 'import-errors/students-import-error-' . now()->timestamp . '.xlsx';

                                $export = new \Maatwebsite\Excel\Excel();
                                $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
                                $sheet = $spreadsheet->getActiveSheet();

                                $headers = array_keys($errors[0]);
                                $sheet->fromArray([$headers], null, 'A1');
                                $sheet->fromArray($errors, null, 'A2');

                                $writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
                                Storage::put($errorFilename, '');
                                $writer->save(Storage::path($errorFilename));

                                Notification::make()
                                    ->danger()
                                    ->title('Sebagian data gagal diimport')
                                    ->body('Beberapa data tidak bisa disimpan. Klik link untuk mengunduh file kesalahan.')
                                    ->actions([
                                        \Filament\Notifications\Actions\Action::make('Download')
                                            ->url(route('students.import.errors', basename($errorFilename)))
                                            ->button()
                                            ->openUrlInNewTab()
                                    ])
                                    ->send();
                            } else {
                                Notification::make()
                                    ->success()
                                    ->title('Berhasil')
                                    ->body('Seluruh data berhasil diimport.')
                                    ->send();
                            }
                        })
                        ->label('Import Siswa')
                        ->icon('heroicon-o-arrow-down-tray'),
                ]),
            ])
            ->headerActions([
                Tables\Actions\Action::make('importData')
                    ->label('Import Data')
                    ->icon('heroicon-m-arrow-up-tray')
                    ->form([
                        FileUpload::make('file')
                            ->label('File Excel (.xlsx)')
                            ->acceptedFileTypes(['application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'])
                            ->directory('imports') // wajib kalau kamu mau hasil upload ke storage otomatis
                            ->required(),
                    ])
                    ->action(function (array $data) {
                        $path = $data['file']; // string path seperti 'imports/siswa.xlsx'
                        $fullPath = Storage::disk('public')->path($path);

                        Excel::import(new StudentImport, $fullPath);

                        Notification::make()
                            ->title('Import berhasil')
                            ->success()
                            ->send();
                    }),
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
