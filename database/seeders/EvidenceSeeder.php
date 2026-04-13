<?php

namespace Database\Seeders;

use App\Models\ChainOfCustody;
use App\Models\Evidence;
use App\Models\EvidenceHash;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * EvidenceSeeder
 *
 * Seeds 15 realistic test evidence records across multiple categories,
 * case numbers, and users. Creates dummy files on the evidence disk so
 * that hash generation works correctly.
 *
 * Run: php artisan db:seed --class=EvidenceSeeder
 */
class EvidenceSeeder extends Seeder
{
    public function run(): void
    {
        // Fetch the seeded users
        $superAdmin = User::where('email', 'superadmin@sdems.local')->firstOrFail();
        $admin      = User::where('email', 'admin@sdems.local')->firstOrFail();
        $user       = User::where('email', 'user@sdems.local')->firstOrFail();

        $evidenceData = [
            // ── Case SDEMS-2026-001: Corporate Fraud ──────────────────────────
            [
                'case_number'   => 'SDEMS-2026-001',
                'title'         => 'Financial Spreadsheet - Q3 Transactions',
                'description'   => 'Excel spreadsheet containing suspicious Q3 financial transactions flagged by auditors.',
                'category'      => 'document',
                'tags'          => ['finance', 'fraud', 'spreadsheet'],
                'original_name' => 'q3_transactions.xlsx',
                'mime_type'     => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                'file_size'     => 245760,
                'uploaded_by'   => $superAdmin->id,
                'assigned_to'   => $admin->id,
                'status'        => 'active',
            ],
            [
                'case_number'   => 'SDEMS-2026-001',
                'title'         => 'CCTV Footage - Server Room 14 Feb 2026',
                'description'   => 'Security camera footage from server room on the date of alleged data exfiltration.',
                'category'      => 'video',
                'tags'          => ['cctv', 'server-room', 'exfiltration'],
                'original_name' => 'cctv_serverroom_20260214.mp4',
                'mime_type'     => 'video/mp4',
                'file_size'     => 524288000,
                'uploaded_by'   => $admin->id,
                'assigned_to'   => $admin->id,
                'status'        => 'active',
            ],
            [
                'case_number'   => 'SDEMS-2026-001',
                'title'         => 'Email Thread - CFO Communications',
                'description'   => 'Exported email thread between CFO and external party discussing fund transfers.',
                'category'      => 'email',
                'tags'          => ['email', 'cfo', 'wire-transfer'],
                'original_name' => 'cfo_email_thread.eml',
                'mime_type'     => 'message/rfc822',
                'file_size'     => 18432,
                'uploaded_by'   => $superAdmin->id,
                'assigned_to'   => null,
                'status'        => 'pending',
            ],

            // ── Case SDEMS-2026-002: Cybercrime Investigation ─────────────────
            [
                'case_number'   => 'SDEMS-2026-002',
                'title'         => 'Hard Drive Forensic Image - Suspect Workstation',
                'description'   => 'DD forensic image of suspect workstation hard drive, acquired using write-blocker.',
                'category'      => 'forensic_image',
                'tags'          => ['forensic', 'hard-drive', 'dd-image'],
                'original_name' => 'suspect_hdd_forensic.dd',
                'mime_type'     => 'application/octet-stream',
                'file_size'     => 107374182400,
                'uploaded_by'   => $admin->id,
                'assigned_to'   => $admin->id,
                'status'        => 'sealed',
            ],
            [
                'case_number'   => 'SDEMS-2026-002',
                'title'         => 'Network Packet Capture - Intrusion Event',
                'description'   => 'PCAP file capturing network traffic during the detected intrusion window.',
                'category'      => 'network_log',
                'tags'          => ['pcap', 'intrusion', 'network'],
                'original_name' => 'intrusion_capture.pcap',
                'mime_type'     => 'application/vnd.tcpdump.pcap',
                'file_size'     => 83886080,
                'uploaded_by'   => $admin->id,
                'assigned_to'   => $user->id,
                'status'        => 'active',
            ],
            [
                'case_number'   => 'SDEMS-2026-002',
                'title'         => 'Malware Sample - Ransomware Binary',
                'description'   => 'Isolated ransomware binary extracted from quarantine. Handle with extreme caution.',
                'category'      => 'other',
                'tags'          => ['malware', 'ransomware', 'binary', 'dangerous'],
                'original_name' => 'ransomware_sample.bin',
                'mime_type'     => 'application/octet-stream',
                'file_size'     => 512000,
                'uploaded_by'   => $superAdmin->id,
                'assigned_to'   => $admin->id,
                'status'        => 'sealed',
            ],
            [
                'case_number'   => 'SDEMS-2026-002',
                'title'         => 'System Event Logs - Windows Security Log',
                'description'   => 'Exported Windows Security event log covering the 72-hour intrusion window.',
                'category'      => 'database',
                'tags'          => ['windows', 'event-log', 'security'],
                'original_name' => 'security_events.evtx',
                'mime_type'     => 'application/octet-stream',
                'file_size'     => 10485760,
                'uploaded_by'   => $admin->id,
                'assigned_to'   => $user->id,
                'status'        => 'active',
            ],

            // ── Case SDEMS-2026-003: Insider Threat ───────────────────────────
            [
                'case_number'   => 'SDEMS-2026-003',
                'title'         => 'USB Drive Contents Archive',
                'description'   => 'ZIP archive of files found on USB drive left in parking lot.',
                'category'      => 'other',
                'tags'          => ['usb', 'physical', 'archive'],
                'original_name' => 'usb_contents.zip',
                'mime_type'     => 'application/zip',
                'file_size'     => 157286400,
                'uploaded_by'   => $user->id,
                'assigned_to'   => $admin->id,
                'status'        => 'active',
            ],
            [
                'case_number'   => 'SDEMS-2026-003',
                'title'         => 'Suspect Interview Recording',
                'description'   => 'Audio recording of formal interview with suspect, conducted 2026-03-15.',
                'category'      => 'audio',
                'tags'          => ['interview', 'audio', 'suspect'],
                'original_name' => 'interview_20260315.wav',
                'mime_type'     => 'audio/wav',
                'file_size'     => 52428800,
                'uploaded_by'   => $admin->id,
                'assigned_to'   => $superAdmin->id,
                'status'        => 'active',
            ],
            [
                'case_number'   => 'SDEMS-2026-003',
                'title'         => 'Access Control Database Export',
                'description'   => 'SQL dump of building access control system for the relevant time period.',
                'category'      => 'database',
                'tags'          => ['access-control', 'sql', 'building'],
                'original_name' => 'access_control_export.sql',
                'mime_type'     => 'application/sql',
                'file_size'     => 2097152,
                'uploaded_by'   => $superAdmin->id,
                'assigned_to'   => $admin->id,
                'status'        => 'active',
            ],

            // ── Case SDEMS-2026-004: Data Breach ─────────────────────────────
            [
                'case_number'   => 'SDEMS-2026-004',
                'title'         => 'Leaked Customer Database Sample',
                'description'   => 'Sample of leaked customer records found on dark web forum (anonymised for evidence).',
                'category'      => 'database',
                'tags'          => ['data-breach', 'customer-data', 'pii'],
                'original_name' => 'leaked_customers_sample.csv',
                'mime_type'     => 'text/csv',
                'file_size'     => 1048576,
                'uploaded_by'   => $superAdmin->id,
                'assigned_to'   => $superAdmin->id,
                'status'        => 'flagged',
            ],
            [
                'case_number'   => 'SDEMS-2026-004',
                'title'         => 'Web Server Access Logs',
                'description'   => 'Nginx access logs from the compromised web server covering the breach window.',
                'category'      => 'network_log',
                'tags'          => ['nginx', 'access-log', 'web-server'],
                'original_name' => 'nginx_access.log',
                'mime_type'     => 'text/plain',
                'file_size'     => 31457280,
                'uploaded_by'   => $admin->id,
                'assigned_to'   => $user->id,
                'status'        => 'active',
            ],
            [
                'case_number'   => 'SDEMS-2026-004',
                'title'         => 'Screenshot - Attacker Dashboard',
                'description'   => 'Screenshot captured from attacker C2 panel during live monitoring.',
                'category'      => 'image',
                'tags'          => ['screenshot', 'c2', 'attacker'],
                'original_name' => 'attacker_dashboard.png',
                'mime_type'     => 'image/png',
                'file_size'     => 2621440,
                'uploaded_by'   => $admin->id,
                'assigned_to'   => $superAdmin->id,
                'status'        => 'active',
            ],

            // ── Case SDEMS-2026-005: Physical Security Incident ───────────────
            [
                'case_number'   => 'SDEMS-2026-005',
                'title'         => 'Incident Report - Physical Breach',
                'description'   => 'Official incident report documenting the physical security breach on 2026-04-01.',
                'category'      => 'document',
                'tags'          => ['incident-report', 'physical', 'official'],
                'original_name' => 'incident_report_20260401.pdf',
                'mime_type'     => 'application/pdf',
                'file_size'     => 409600,
                'uploaded_by'   => $superAdmin->id,
                'assigned_to'   => $admin->id,
                'status'        => 'active',
            ],
            [
                'case_number'   => 'SDEMS-2026-005',
                'title'         => 'Fingerprint Analysis Report',
                'description'   => 'Forensic fingerprint analysis report from the physical evidence collected at scene.',
                'category'      => 'document',
                'tags'          => ['fingerprint', 'forensic', 'physical-evidence'],
                'original_name' => 'fingerprint_analysis.pdf',
                'mime_type'     => 'application/pdf',
                'file_size'     => 819200,
                'uploaded_by'   => $admin->id,
                'assigned_to'   => null,
                'status'        => 'pending',
            ],
        ];

        $disk = Storage::disk('evidence');

        foreach ($evidenceData as $data) {
            // ── Create a dummy file on the evidence disk ───────────────────────
            // In production, real files are uploaded via the controller.
            // Here we create placeholder files so hash_file() works correctly.
            $year     = now()->year;
            $month    = now()->format('m');
            $uuid     = Str::uuid()->toString();
            $ext      = pathinfo($data['original_name'], PATHINFO_EXTENSION);
            $filePath = "cases/{$data['case_number']}/{$year}/{$month}/{$uuid}.{$ext}";

            // Write a realistic dummy file with identifiable content
            $dummyContent = sprintf(
                "[SDEMS TEST EVIDENCE]\nCase: %s\nTitle: %s\nCategory: %s\nGenerated: %s\nUUID: %s\n\n%s",
                $data['case_number'],
                $data['title'],
                $data['category'],
                now()->toIso8601String(),
                $uuid,
                str_repeat('X', min($data['file_size'], 512)) // Simulate file content
            );

            $disk->put($filePath, $dummyContent);

            // ── Create the Evidence record ─────────────────────────────────────
            // Boot method will auto-generate hash + initial custody record.
            $evidence = Evidence::create([
                'case_number'   => $data['case_number'],
                'title'         => $data['title'],
                'description'   => $data['description'],
                'category'      => $data['category'],
                'tags'          => $data['tags'],
                'file_path'     => $filePath,
                'original_name' => $data['original_name'],
                'mime_type'     => $data['mime_type'],
                'file_size'     => $data['file_size'],
                'uploaded_by'   => $data['uploaded_by'],
                'assigned_to'   => $data['assigned_to'],
                'status'        => $data['status'],
            ]);

            // ── Add a transfer custody record for some items ───────────────────
            if ($data['assigned_to'] && $data['assigned_to'] !== $data['uploaded_by']) {
                $uploader = User::find($data['uploaded_by']);
                $assignee = User::find($data['assigned_to']);

                ChainOfCustody::transfer(
                    $evidence,
                    $uploader,
                    $assignee,
                    'transfer',
                    'Assigned for investigation.',
                    'Digital Evidence Lab'
                );
            }
        }

        $count = Evidence::count();
        $this->command->info("✅ {$count} evidence records seeded with hashes and custody chains.");
        $this->command->table(
            ['Case', 'Title', 'Category', 'Status'],
            Evidence::with('uploader')
                    ->get()
                    ->map(fn ($e) => [
                        $e->case_number,
                        substr($e->title, 0, 40),
                        $e->category,
                        $e->status,
                    ])
                    ->toArray()
        );
    }
}
