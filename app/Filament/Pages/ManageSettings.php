<?php

namespace App\Filament\Pages;

use App\Models\User;
use App\Settings\PlatformSettings;
use Filament\Forms;
use Filament\Forms\Components\Card;
use Filament\Forms\Components\MarkdownEditor;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Pages\Actions\Action;
use Filament\Pages\SettingsPage;

class ManageSettings extends SettingsPage
{
    protected static ?string $navigationIcon = 'heroicon-o-cog';

    protected static string $settings = PlatformSettings::class;
    protected static function shouldRegisterNavigation(): bool
    {
        return auth()->user()->isAdmin();
    }

    public function mount(): void
    {
        parent::mount();
        abort_unless(auth()->user()->isAdmin(), 403);
    }

    protected function getFormSchema(): array
    {
        return [

            // Affiliate settings
            // Card::make()->columns(3)->schema([
            //     TextInput::make('affiliate_percentage')->label('Affiliate Percentage %')->numeric()->suffix('%'),
            //     TextInput::make('affiliate_minimum_withdrawal')->label('Affiliate Minimum Withdrawal')->mask(fn (TextInput\Mask $mask) => $mask->money(prefix: '₦')),
            //     TextInput::make('minimum_withdrawal')->label('Minimum Withdrawal')->mask(fn (TextInput\Mask $mask) => $mask->money(prefix: '₦')),
            // ]),

            Card::make()->label("Product One Settings")->schema([
                TextInput::make('product_one_title')->label('Product One Title'),
                TextInput::make('product_one_price')->label('Product One Price')->numeric(),
                // ->mask(fn (TextInput\Mask $mask) => $mask->money(prefix: '₦')),
                RichEditor::make('product_one_description')->label('Product One Description'),

            ]),
            Card::make()->label("Product Two settings")->schema([
                TextInput::make('product_two_title')->label('Product Two Title'),
                TextInput::make('product_two_price')->label('Product Two Price')->numeric(),
                // ->mask(fn (TextInput\Mask $mask) => $mask->money(prefix: '₦')),
                RichEditor::make('product_two_description')->label('Product Two Description'),
            ]),

            Card::make()->label("Product Three settings")->schema([
                TextInput::make('product_three_title')->label('Product Three Title'),
                TextInput::make('product_three_price')->label('Product Three Price')->numeric(),
                // ->mask(fn (TextInput\Mask $mask) => $mask->money(prefix: '₦')),
                RichEditor::make('product_three_description')->label('Product Three Description'),
            ]),
        ];
    }

    protected function getActions(): array
    {
        return [

            Action::make('downloadAllEmails')->action('exportAllEmails')->color('primary')->label('Download All Emails'),
            Action::make('downloadActiveEmails')->action('exportActiveEmails')->color("primary")->label('Download Active Emails'),
            Action::make('downloadInactiveEmails')->action('exportInactiveEmails')->color("primary")->label('Download Inactive Emails'),



        ];
    }

    public function exportAllEmails()
    {

        $reports = User::all();
        $rows = [];
        foreach ($reports as $key => $value) {
            $rows[$key]['id'] = $value->id;
            $rows[$key]['name'] = $value->name;
            $rows[$key]['email'] = $value->email;
        }

        $fileName = 'file.csv';
        $headers = [
            "Content-type" => "text/csv",
            "Content-Disposition" => "attachment; filename=" . $fileName,
            "Pragma" => "no-cache",
            "Cache-Control" => "must-revalidate, post-check=0, pre-check=0",
            "Expires" => "0"
        ];

        $columnNames = [
            'ID',
            'name',
            'email'
        ];
        $callback = function () use ($columnNames, $rows) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $columnNames);
            foreach ($rows as $row) {
                fputcsv($file, $row);
            }
            fclose($file);
        };
        return response()->stream($callback, 200, $headers);
    }

    //export user emails where status is active
    public function exportActiveEmails()
    {
        $reports = User::where('status', 'active')->get();
        $rows = [];
        foreach ($reports as $key => $value) {
            $rows[$key]['id'] = $value->id;
            $rows[$key]['name'] = $value->name;
            $rows[$key]['email'] = $value->email;
        }

        $fileName = 'file.csv';
        $headers = [
            "Content-type" => "text/csv",
            "Content-Disposition" => "attachment; filename=" . $fileName,
            "Pragma" => "no-cache",
            "Cache-Control" => "must-revalidate, post-check=0, pre-check=0",
            "Expires" => "0"
        ];

        $columnNames = [
            'ID',
            'name',
            'email'
        ];
        $callback = function () use ($columnNames, $rows) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $columnNames);
            foreach ($rows as $row) {
                fputcsv($file, $row);
            }
            fclose($file);
        };
        return response()->stream($callback, 200, $headers);
    }

    //export user emails where status is inactive
    public function exportInactiveEmails()
    {
        $reports = User::where('status', 'inactive')->orWhere('status', 'suspended')->get();
        $rows = [];
        foreach ($reports as $key => $value) {
            $rows[$key]['id'] = $value->id;
            $rows[$key]['name'] = $value->name;
            $rows[$key]['email'] = $value->email;
        }

        $fileName = 'file.csv';
        $headers = [
            "Content-type" => "text/csv",
            "Content-Disposition" => "attachment; filename=" . $fileName,
            "Pragma" => "no-cache",
            "Cache-Control" => "must-revalidate, post-check=0, pre-check=0",
            "Expires" => "0"
        ];

        $columnNames = [
            'ID',
            'name',
            'email'
        ];
        $callback = function () use ($columnNames, $rows) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $columnNames);
            foreach ($rows as $row) {
                fputcsv($file, $row);
            }
            fclose($file);
        };
        return response()->stream($callback, 200, $headers);
    }
}
