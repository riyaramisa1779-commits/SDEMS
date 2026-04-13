<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

/**
 * Handles bulk CSV import and export of users.
 */
class UserImportExportService
{
    /**
     * Export all users to a CSV string.
     */
    public function exportCsv(): string
    {
        $headers = ['ID', 'Name', 'Email', 'Rank', 'Status', 'Roles', 'Created At'];
        $rows    = [implode(',', $headers)];

        User::withTrashed()->with('roles')->get()->each(function (User $user) use (&$rows) {
            $rows[] = implode(',', [
                $user->id,
                '"' . str_replace('"', '""', $user->name) . '"',
                $user->email,
                $user->rank,
                $user->is_active ? 'active' : 'inactive',
                '"' . $user->roles->pluck('name')->implode('|') . '"',
                $user->created_at->toDateTimeString(),
            ]);
        });

        return implode("\n", $rows);
    }

    /**
     * Import users from an uploaded CSV file.
     * Returns ['imported' => int, 'errors' => array].
     */
    public function importCsv(UploadedFile $file, User $importedBy): array
    {
        $imported = 0;
        $errors   = [];
        $lines    = array_filter(explode("\n", file_get_contents($file->getRealPath())));

        // Skip header row
        array_shift($lines);

        foreach ($lines as $lineNumber => $line) {
            $row = str_getcsv(trim($line));

            if (count($row) < 3) {
                $errors[] = "Line " . ($lineNumber + 2) . ": insufficient columns.";
                continue;
            }

            [$name, $email, $rank] = $row;
            $rank = (int) ($rank ?? 1);

            // Validate
            $validator = Validator::make(
                ['name' => $name, 'email' => $email, 'rank' => $rank],
                [
                    'name'  => 'required|string|max:255',
                    'email' => 'required|email|unique:users,email',
                    'rank'  => 'required|integer|between:1,10',
                ]
            );

            if ($validator->fails()) {
                $errors[] = "Line " . ($lineNumber + 2) . ": " . $validator->errors()->first();
                continue;
            }

            // Prevent privilege escalation: importer cannot assign rank higher than their own
            if ($rank > $importedBy->rank) {
                $errors[] = "Line " . ($lineNumber + 2) . ": Cannot assign rank higher than your own ({$importedBy->rank}).";
                continue;
            }

            $tempPassword = Str::random(16);

            $user = User::create([
                'name'     => $name,
                'email'    => $email,
                'password' => Hash::make($tempPassword),
                'rank'     => $rank,
                'is_active' => true,
            ]);

            $user->assignRole('user');

            activity('user_management')
                ->causedBy($importedBy)
                ->performedOn($user)
                ->log('User imported via CSV');

            $imported++;
        }

        return ['imported' => $imported, 'errors' => $errors];
    }
}
