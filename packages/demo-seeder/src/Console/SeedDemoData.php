<?php

namespace DocsReader\DemoSeeder\Console;

use Carbon\Carbon;
use DocsReader\DemoSeeder\DemoLibrary;
use DocsReader\DemoSeeder\SamplePdfWriter;
use Illuminate\Console\Command;
use Illuminate\Contracts\Hashing\Hasher;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Modules\Access\Api\AccessApiInterface;
use Modules\Access\Models\Permission;
use Modules\Document\Models\Document;
use Modules\Document\Models\UserDocument;
use Modules\Document\Services\PdfMetadataExtractor;
use Modules\Engagement\Models\DocumentPageProgress;
use Modules\Engagement\Models\ReadingPageTick;
use Modules\Engagement\Models\ReadingSession;
use Modules\Engagement\Services\EngagementRecorder;
use Modules\History\Models\DocumentRead;
use Modules\User\Api\UserApiInterface;
use RuntimeException;
use Symfony\Component\Console\Command\Command as CommandAlias;

/**
 * Bootstraps a believable in-use dataset: staff, mandatory reading with real PDFs, and the engagement trail behind it.
 */
class SeedDemoData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'demo:seed
        {--append : Add another generation of data instead of rebuilding from scratch}
        {--password=DemoPassword123! : Shared password for every seeded account}
        {--force : Allow the command to run in production}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Seed a demo company with staff, documents (real PDFs) and reading history';

    public function __construct(
        private readonly UserApiInterface $userApi,
        private readonly AccessApiInterface $accessApi,
        private readonly Hasher $hasher,
        private readonly SamplePdfWriter $pdfWriter,
        private readonly PdfMetadataExtractor $pdfMetadataExtractor,
    ) {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(): int
    {
        if ($this->getLaravel()->environment('production') && !$this->option('force')) {
            $this->error('Refusing to seed demo data in production. Pass --force if you really mean it.');

            return CommandAlias::FAILURE;
        }

        if (!$this->option('append')) {
            $this->purge();
        }

        $this->ensurePermissionExists();

        $people = $this->seedPeople();

        $managers = array_values(array_filter($people, fn(array $person) => $person['manages']));
        if (count($managers) !== 1) {
            $this->error(
                sprintf(
                    'DemoLibrary::people() must mark exactly one person as managing; found %d.',
                    count($managers)
                )
            );

            return CommandAlias::FAILURE;
        }

        $documents = $this->seedDocuments($managers[0]);

        // Everyone is assigned, the manager included, so signing in as them shows both sides of the app.
        $this->assignAndRead($documents, $people, $managers[0]['id']);

        $this->summarise($people, $documents);

        return CommandAlias::SUCCESS;
    }

    /**
     * Scoped to the demo email domain so a real dataset sharing this database is never touched.
     *
     * @return void
     */
    private function purge(): void
    {
        $userIds = DB::table('users')
            ->where('email', 'like', '%@' . DemoLibrary::EMAIL_DOMAIN)
            ->pluck('id');

        if ($userIds->isEmpty()) {
            $this->line('Nothing to purge.');

            return;
        }

        $expected = count(DemoLibrary::people());
        if ($userIds->count() > $expected) {
            $this->error(
                sprintf(
                    'Refusing to purge: found %d accounts on @%s but the seeder only defines %d.',
                    $userIds->count(),
                    DemoLibrary::EMAIL_DOMAIN,
                    $expected
                )
            );
            $this->line('Inspect those accounts by hand -- this command did not create all of them.');

            throw new RuntimeException('Demo purge aborted: more accounts than expected on the demo domain.');
        }

        $documents = Document::withTrashed()->whereIn('user_id', $userIds)->get(['id', 'uuid']);
        $documentIds = $documents->pluck('id');

        DB::transaction(function () use ($userIds, $documentIds) {
            ReadingPageTick::whereIn('user_id', $userIds)->delete();
            ReadingSession::whereIn('user_id', $userIds)->delete();
            DocumentPageProgress::whereIn('user_id', $userIds)->delete();
            DocumentRead::whereIn('user_id', $userIds)->orWhereIn('document_id', $documentIds)->delete();
            UserDocument::whereIn('user_id', $userIds)->orWhereIn('document_id', $documentIds)->delete();
            Document::withTrashed()->whereIn('id', $documentIds)->forceDelete();
            DB::table('user_permissions')->whereIn('user_id', $userIds)->delete();
            DB::table('users')->whereIn('id', $userIds)->delete();
        });

        foreach ($documents as $document) {
            Storage::disk('documents')->deleteDirectory('uploads/' . $document->uuid);
        }

        $this->line(
            sprintf(
                'Purged %d demo account(s) and %d document(s).',
                $userIds->count(),
                $documentIds->count()
            )
        );
    }

    /**
     * @return void
     */
    private function ensurePermissionExists(): void
    {
        foreach ((array)config('permissions') as $permissionKey) {
            Permission::firstOrCreate(['type' => $permissionKey]);
        }
    }

    /**
     * @return array<int, array{id: int, name: string, email: string, persona: string, title: string}>
     */
    private function seedPeople(): array
    {
        $password = $this->hasher->make((string)$this->option('password'));
        $people = [];

        foreach (DemoLibrary::people() as $person) {
            $email = $person['email'] . '@' . DemoLibrary::EMAIL_DOMAIN;
            $existing = DB::table('users')->where('email', $email)->first();

            if ($existing !== null) {
                $userId = (int)$existing->id;
            } else {
                $userId = $this->userApi->createUser([
                    'name' => $person['name'],
                    'email' => $email,
                    'password' => $password,
                ])->getId();
            }

            if ($person['manages']) {
                foreach ((array)config('permissions') as $permissionKey) {
                    $this->accessApi->grantPermission($userId, $permissionKey);
                }
            }

            $people[] = [
                'id' => $userId,
                'name' => $person['name'],
                'email' => $email,
                'manages' => $person['manages'],
                'persona' => $person['persona'],
                'title' => $person['title'],
            ];
        }

        $this->line(sprintf('Staff: %d account(s).', count($people)));

        return $people;
    }

    /**
     * @param  array{id: int}  $manager
     * @return array<int, Document>
     */
    private function seedDocuments(array $manager): array
    {
        $documents = [];

        foreach (DemoLibrary::documents() as $spec) {
            $uuid = (string)Str::uuid();

            $relativePath = sprintf('uploads/%s/%s.pdf', $uuid, Str::random(40));
            Storage::disk('documents')->put(
                $relativePath,
                $this->pdfWriter->render($spec['title'], $spec['pages'])
            );

            $documents[] = Document::create([
                'uuid' => $uuid,
                'name' => $spec['title'],
                'source_name' => Str::slug($spec['key']) . '.pdf',
                'description' => $spec['description'],
                'user_id' => $manager['id'],
                'file_path' => '/' . $relativePath,
                'total_pages' => $this->pdfMetadataExtractor->countPages($relativePath),
                'date_from' => Carbon::parse($spec['dateFrom'])->startOfDay(),
                'date_to' => $spec['dateTo'] !== null ? Carbon::parse($spec['dateTo'])->endOfDay() : null,
                'requires_confirmation' => $spec['requiresConfirmation'],
                'declaration_message' => $spec['declaration'],
                'delay' => $spec['delay'],
            ]);
        }

        $unparsed = array_filter($documents, fn(Document $d) => $d->total_pages === null);
        if ($unparsed !== []) {
            $this->warn(sprintf('%d document(s) could not be parsed for a page count.', count($unparsed)));
        }

        $this->line(sprintf('Documents: %d, with PDFs on the documents disk.', count($documents)));

        return $documents;
    }

    /**
     * @param  array<int, Document>  $documents
     * @param  array<int, array{id: int, persona: string}>  $readers
     * @param  int  $managerId
     */
    private function assignAndRead(array $documents, array $readers, int $managerId): void
    {
        $assignments = 0;
        $sessions = 0;
        $confirmations = 0;

        foreach (array_values($documents) as $position => $document) {
            foreach ($readers as $offset => $reader) {
                // Couple of holes so unassigned-document paths are testable,
                // but never for the manager: they own every document and should see
                // all of them on the reader side too. Keyed on position rather than
                // the autoincrement id so a rebuild puts the holes in the same places.
                if ($reader['id'] !== $managerId && ($offset + $position) % 11 === 0) {
                    continue;
                }

                UserDocument::firstOrCreate([
                    'user_id' => $reader['id'],
                    'document_id' => $document->id,
                ], [
                    'created_by' => $document->user_id,
                ]);
                $assignments++;

                $result = $this->simulateReading($document, $reader);
                $sessions += $result['sessions'];
                $confirmations += $result['confirmed'] ? 1 : 0;
            }
        }

        $this->line(
            sprintf(
                'Engagement: %d assignment(s), %d reading session(s), %d confirmation(s).',
                $assignments,
                $sessions,
                $confirmations
            )
        );
    }

    /**
     * @param  Document  $document
     * @param  array{id: int, persona: string}  $reader
     * @return array{sessions: int, confirmed: bool}
     */
    private function simulateReading(Document $document, array $reader): array
    {
        $totalPages = (int)($document->total_pages ?? 1);
        $threshold = max((int)$document->delay, 1);
        $plan = $this->planFor($reader['persona'], $totalPages, $threshold);

        if ($plan['sessions'] === 0) {
            return ['sessions' => 0, 'confirmed' => false];
        }

        $windowStart = Carbon::parse($document->date_from)->max(now()->subDays(30));
        if ($windowStart->isFuture()) {
            // A document that has not opened yet cannot have been read.
            return ['sessions' => 0, 'confirmed' => false];
        }

        $progress = [];
        $lastSessionId = null;
        $pagesPerSession = (int)ceil(count($plan['pages']) / $plan['sessions']);
        $pageChunks = array_chunk($plan['pages'], max($pagesPerSession, 1), true);

        foreach ($pageChunks as $chunkIndex => $chunk) {
            $startedAt = $windowStart->copy()->addDays($chunkIndex * 2)->addHours(9 + $chunkIndex);
            $session = ReadingSession::create([
                'uuid' => (string)Str::ulid(),
                'user_id' => $reader['id'],
                'document_id' => $document->id,
                'started_at' => $startedAt,
                'last_page' => 1,
                'total_active_seconds' => 0,
                'client_meta' => [
                    'ip' => '10.0.' . ($reader['id'] % 255) . '.' . ($document->id % 255),
                    'userAgent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) DocsReader/demo',
                ],
            ]);

            $ticks = [];
            $sessionSeconds = 0;
            $occurredAt = $startedAt->copy();
            $lastPage = 1;

            foreach ($chunk as $pageNumber => $seconds) {
                $remaining = (int)round($seconds * 1000);
                $pageMs = 0;

                while ($remaining > 0) {
                    $slice = min($remaining, EngagementRecorder::MAX_TICK_MS);
                    $occurredAt = $occurredAt->copy()->addMilliseconds($slice);

                    $ticks[] = [
                        'reading_session_id' => $session->id,
                        'user_id' => $reader['id'],
                        'document_id' => $document->id,
                        'page_number' => $pageNumber,
                        'client_event_id' => (string)Str::ulid(),
                        'active_ms' => $slice,
                        'occurred_at' => $occurredAt,
                        'created_at' => $occurredAt,
                    ];

                    $pageMs += $slice;
                    $remaining -= $slice;
                }

                // Mirrors EngagementRecorder: sum milliseconds per page, then truncate.
                $pageSeconds = intdiv($pageMs, 1000);
                $sessionSeconds += $pageSeconds;
                $progress[$pageNumber] = ($progress[$pageNumber] ?? 0) + $pageSeconds;
                $lastPage = $pageNumber;
            }

            if ($ticks !== []) {
                foreach (array_chunk($ticks, 200) as $batch) {
                    ReadingPageTick::insert($batch);
                }
            }

            $session->forceFill([
                'last_tick_at' => $occurredAt,
                'ended_at' => $occurredAt->copy()->addMinutes(1),
                'total_active_seconds' => $sessionSeconds,
                'last_page' => $lastPage,
            ])->save();

            $lastSessionId = $session->id;
        }

        foreach ($progress as $pageNumber => $seconds) {
            DocumentPageProgress::updateOrCreate([
                'user_id' => $reader['id'],
                'document_id' => $document->id,
                'page_number' => $pageNumber,
            ], [
                'total_active_seconds' => $seconds,
                'first_viewed_at' => $windowStart,
                'last_viewed_at' => $windowStart->copy()->addDays(count($pageChunks) * 2),
            ]);
        }

        // The reader's ConfirmSection only renders when the document requires
        // confirmation, so a document_read cannot exist for one that does not --
        // reading is still recorded, just never confirmed.
        $confirmed = false;
        if ($document->requires_confirmation
            && $plan['confirms']
            && $this->gateWouldAllow($progress, $totalPages, $threshold)
        ) {
            $confirmedAt = $plan['confirmsLate'] && $document->date_to !== null
                ? Carbon::parse($document->date_to)->subHours(3)
                : $windowStart->copy()->addDays(count($pageChunks) * 2)->addHours(11);

            DocumentRead::updateOrCreate([
                'document_id' => $document->id,
                'user_id' => $reader['id'],
            ], [
                'confirmed' => true,
                'certificate_id' => (string)Str::ulid(),
                'confirmed_at' => $confirmedAt->min(now()),
                'total_active_seconds' => array_sum($progress),
                'pages_viewed_count' => count($progress),
                'last_session_id' => $lastSessionId,
            ]);

            $confirmed = true;
        }

        return ['sessions' => count($pageChunks), 'confirmed' => $confirmed];
    }

    /**
     * Reading behaviour per persona: how many seconds land on which pages, over
     * how many sittings, and whether it ends in a confirmation.
     *
     * @param  string  $persona
     * @param  int  $totalPages
     * @param  int  $threshold
     * @return array{pages: array<int, int>, sessions: int, confirms: bool, confirmsLate: bool}
     */
    private function planFor(string $persona, int $totalPages, int $threshold): array
    {
        $allPages = range(1, max($totalPages, 1));

        return match ($persona) {
            DemoLibrary::PERSONA_GHOST => [
                'pages' => [],
                'sessions' => 0,
                'confirms' => false,
                'confirmsLate' => false,
            ],

            // Comfortably over the gate on every page.
            DemoLibrary::PERSONA_DILIGENT => [
                'pages' => $this->seconds($allPages, fn(int $p) => $threshold + 4 + ($p % 5)),
                'sessions' => 1,
                'confirms' => true,
                'confirmsLate' => false,
            ],

            // Reaches the last page, nowhere near the dwell requirement, so the
            // gate refuses the confirmation -- the case BUG-7 is about.
            DemoLibrary::PERSONA_SKIMMER => [
                'pages' => $this->seconds($allPages, fn() => max(1, (int)floor($threshold / 4))),
                'sessions' => 1,
                'confirms' => true,
                'confirmsLate' => false,
            ],

            // Stops around 60% of the way through.
            DemoLibrary::PERSONA_PARTIAL => [
                'pages' => $this->seconds(
                    array_slice($allPages, 0, max(1, (int)ceil($totalPages * 0.6))),
                    fn(int $p) => $threshold + 3
                ),
                'sessions' => 1,
                'confirms' => false,
                'confirmsLate' => false,
            ],

            DemoLibrary::PERSONA_LAST_MINUTE => [
                'pages' => $this->seconds($allPages, fn() => $threshold + 1),
                'sessions' => 1,
                'confirms' => true,
                'confirmsLate' => true,
            ],

            // Page one for an implausibly long time, then nothing.
            DemoLibrary::PERSONA_OBSESSIVE => [
                'pages' => [1 => 45 * 60],
                'sessions' => 1,
                'confirms' => false,
                'confirmsLate' => false,
            ],

            // Several sittings, eventually covering everything.
            DemoLibrary::PERSONA_RETURNER => [
                'pages' => $this->seconds($allPages, fn(int $p) => $threshold + 2 + ($p % 3)),
                'sessions' => min(3, max(1, $totalPages)),
                'confirms' => true,
                'confirmsLate' => false,
            ],

            default => [
                'pages' => [],
                'sessions' => 0,
                'confirms' => false,
                'confirmsLate' => false,
            ],
        };
    }

    /**
     * @param  array<int, int>  $pages
     * @param  callable(int): int  $seconds
     * @return array<int, int>
     */
    private function seconds(array $pages, callable $seconds): array
    {
        $plan = [];
        foreach ($pages as $page) {
            $plan[$page] = $seconds($page);
        }

        return $plan;
    }

    /**
     * The same condition EveryPageMeetsThresholdRule applies at confirm time.
     *
     * @param  array<int, int>  $progress
     * @param  int  $totalPages
     * @param  int  $threshold
     * @return bool
     */
    private function gateWouldAllow(array $progress, int $totalPages, int $threshold): bool
    {
        for ($page = 1; $page <= $totalPages; $page++) {
            if (($progress[$page] ?? 0) < $threshold) {
                return false;
            }
        }

        return true;
    }

    /**
     * @param  array<int, array{name: string, email: string, manages: bool, persona: string, title: string}>  $people
     * @param  array<int, Document>  $documents
     */
    private function summarise(array $people, array $documents): void
    {
        $this->newLine();
        $this->info('Demo company seeded: Kettlewick & Moor Ltd.');
        $this->newLine();

        $this->line('<comment>Sign in with any of these — password: ' . $this->option('password') . '</comment>');
        $this->table(
            ['Email', 'Name', 'Role', 'Reading behaviour'],
            array_map(
                fn(array $p) => [
                    $p['email'],
                    $p['name'],
                    $p['manages'] ? 'manager' : 'reader',
                    $p['title'],
                ],
                $people
            )
        );

        $this->line('<comment>Documents</comment>');
        $this->table(
            ['Title', 'Pages', 'Gate', 'Window', 'Confirm?'],
            array_map(fn(Document $d) => [
                Str::limit($d->name, 46),
                $d->total_pages ?? '?',
                $d->delay . 's/page',
                Carbon::parse($d->date_from)->toDateString()
                . ' → ' . ($d->date_to !== null ? Carbon::parse($d->date_to)->toDateString() : 'open'),
                $d->requires_confirmation ? 'yes' : 'no',
            ], $documents)
        );

        $this->line('Re-running rebuilds from scratch. Only accounts on');
        $this->line('@' . DemoLibrary::EMAIL_DOMAIN . ' and their documents are ever touched.');
    }
}
