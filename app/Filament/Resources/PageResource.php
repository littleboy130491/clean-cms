<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PageResource\Pages;
use App\Models\Page;
use App\Filament\Abstracts\BaseContentResource;
use Filament\Forms\Components\Builder;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Builder as FormsBuilder;
use Awcodes\Curator\Components\Forms\CuratorPicker;
use Filament\Forms\Components\RichEditor;

class PageResource extends BaseContentResource
{
    protected static ?string $model = Page::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    protected static ?string $navigationGroup = 'Contents';
    protected static ?int $navigationSort = 0;

    protected static function formSectionField(string $locale): array
    {
        return [
            FormsBuilder::make('section')
                ->collapsed(false)
                ->blocks([
                    FormsBuilder\Block::make('complete')
                        ->schema([
                            TextInput::make('heading'),
                            TextInput::make('group'),
                            RichEditor::make('description')
                                ->columnSpan('full'),
                            TextInput::make('cta-label')
                                ->label('CTA label'),
                            TextInput::make('cta-url')
                                ->label('CTA URL'),
                            CuratorPicker::make('media_id')
                                ->label('Media')
                                ->helperText('Accepted file types: image or document'),
                        ])
                        ->columns(2),
                    FormsBuilder\Block::make('simple')
                        ->schema([
                            TextInput::make('heading'),
                            RichEditor::make('description'),
                        ])
                        ->columns(1),
                    FormsBuilder\Block::make('video')
                        ->schema([
                            TextInput::make('heading'),
                            TextInput::make('group'),
                            RichEditor::make('description')
                                ->columnSpan('full'),
                            TextInput::make('video_url'),
                        ])
                        ->columns(2),
                ]),

        ];
    }
    protected static function formRelationshipsFields(): array
    {
        return [
            ...static::formParentRelationshipField(),
        ];
    }

    protected static function formFeaturedField(): array
    {
        return [];
    }

    protected static function tableFeaturedColumn(): array
    {
        return [];
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
            'index' => Pages\ListPages::route('/'),
            'create' => Pages\CreatePage::route('/create'),
            'edit' => Pages\EditPage::route('/{record}/edit'),
        ];
    }
}
