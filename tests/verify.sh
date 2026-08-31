#!/usr/bin/env bash
# Verify spine/laravel-spine hook events end-to-end in a consumer app.
#
# Ad-hoc verification (no PHPUnit): runs real tinker checks in the consumer
# app where the package is installed. Lints the package source, then verifies
# dispatch order + veto points for the hook events.
#
# Usage:  CONSUMER=/www/wwwroot/spine.lan ./tests/verify.sh
# Defaults: CONSUMER=../.. (repo sibling), PHP=php 8.4 binary if found.
set -u

HERE="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
PACKAGE="$(cd "$HERE/.." && pwd)"
CONSUMER="${CONSUMER:-/www/wwwroot/spine.lan}"

PHP="${PHP:-}"
if [ -z "$PHP" ]; then
  for c in /www/server/php/84/bin/php php; do
    command -v "$c" >/dev/null 2>&1 && PHP="$c" && break
  done
fi
[ -n "$PHP" ] || { echo "FAIL: PHP binary tidak ditemukan (set PHP=...)"; exit 1; }
[ -f "$CONSUMER/artisan" ] || { echo "FAIL: konsumen $CONSUMER tidak punya artisan"; exit 1; }

FAIL=0

echo "== lint: semua src/ =="
BAD=$(find "$PACKAGE/src" -name '*.php' -exec "$PHP" -l {} \; 2>/dev/null | grep -v 'No syntax errors' || true)
if [ -n "$BAD" ]; then echo "$BAD"; FAIL=1; fi
echo "lint OK"

cd "$CONSUMER"

echo "== hook: FileUploading/FileUploaded =="
R=$("$PHP" artisan tinker --execute="
use Spine\Events\FileUploading; use Spine\Events\FileUploaded;
use Spine\Services\FileService; use Illuminate\Http\UploadedFile;
\$f = UploadedFile::fake()->create('v.txt', 10);
\$log=[];
Event::listen(FileUploading::class, function(\$e) use (&\$log) { \$log[]='uploading'; });
Event::listen(FileUploaded::class, function(\$e) use (&\$log) { \$log[]='uploaded'; });
\$path = app(FileService::class)->storeUpload(\$f, 'verify', 1);
echo implode('|',\$log) . '|exists=' . var_export(\Illuminate\Support\Facades\Storage::disk('local')->exists(\$path), true);
" 2>&1 | grep -E "^uploading\|uploaded" | tail -1)
echo "run: $R"
echo "$R" | grep -q "^uploading|uploaded|exists=true" || { echo "FAIL: upload"; FAIL=1; }

echo "== hook: FileDeleting/FileDeleted + veto =="
R=$("$PHP" artisan tinker --execute="
use Spine\Events\FileDeleting; use Spine\Events\FileDeleted;
use Spine\Services\FileService; use Spine\Models\Attachment;
use Illuminate\Http\UploadedFile; use Illuminate\Validation\ValidationException;
\$f = UploadedFile::fake()->create('w.txt', 10);
\$path = app(FileService::class)->storeUpload(\$f, 'verify', 1);
\$att = Attachment::create(['rel_type'=>'verify','rel_id'=>1,'tenant_id'=>null,'disk'=>'local','path'=>\$path,'original_name'=>'w.txt','mime_type'=>'text/plain','size'=>10,'extension'=>'txt']);
\$log=[];
Event::listen(FileDeleting::class, function(\$e) use (&\$log) { \$log[]='deleting'; });
Event::listen(FileDeleted::class, function(\$e) use (&\$log) { \$log[]='deleted'; });
app(FileService::class)->deleteUpload(\$att);
\$first = implode('|',\$log) . '|gone=' . var_export(!\Illuminate\Support\Facades\Storage::disk('local')->exists(\$path), true);
\$f2 = UploadedFile::fake()->create('x.txt', 10);
\$path2 = app(FileService::class)->storeUpload(\$f2, 'verify', 1);
\$att2 = Attachment::create(['rel_type'=>'verify','rel_id'=>1,'tenant_id'=>null,'disk'=>'local','path'=>\$path2,'original_name'=>'x.txt','mime_type'=>'text/plain','size'=>10,'extension'=>'txt']);
Event::listen(FileDeleting::class, fn(\$e) => throw ValidationException::withMessages(['file'=>'blocked']));
try { app(FileService::class)->deleteUpload(\$att2); \$veto='NO-VETO'; }
catch (ValidationException \$ex) { \$veto='VETOED'; }
\$veto .= '|kept=' . var_export(\Illuminate\Support\Facades\Storage::disk('local')->exists(\$path2), true);
echo \$first . '|' . \$veto;
" 2>&1 | grep -E "^deleting\|deleted" | tail -1)
echo "run: $R"
echo "$R" | grep -q "^deleting|deleted|gone=true|VETOED|kept=true" || { echo "FAIL: delete/veto"; FAIL=1; }

echo "== hook: PdfCreating/PdfCreated + veto =="
R=$("$PHP" artisan tinker --execute="
use Spine\Events\PdfCreating; use Spine\Events\PdfCreated; use Spine\Services\PdfService;
use Illuminate\Validation\ValidationException;
\$log=[];
Event::listen(PdfCreating::class, function(\$e) use (&\$log) { \$log[]='creating'; });
Event::listen(PdfCreated::class, function(\$e) use (&\$log) { \$log[]='created'; });
app(PdfService::class)->fromHtml(['html'=>'<h1>Test</h1>']);
\$first = implode('|',\$log);
Event::listen(PdfCreating::class, fn(\$e) => throw ValidationException::withMessages(['pdf'=>'blocked']));
try { app(PdfService::class)->fromHtml(['html'=>'<h1>x</h1>']); \$veto='NO-VETO'; }
catch (ValidationException \$ex) { \$veto='VETOED'; }
echo \$first . '|' . \$veto;
" 2>&1 | grep -E "^creating\|created" | tail -1)
echo "run: $R"
echo "$R" | grep -q "^creating|created|VETOED" || { echo "FAIL: pdf"; FAIL=1; }

echo "== cleanup: storage test =="
"$PHP" artisan tinker --execute="
\Illuminate\Support\Facades\Storage::disk('local')->deleteDirectory('tenants/global/verify');
echo 'cleaned';
" 2>&1 | grep -q "^cleaned" || { echo "FAIL: cleanup"; FAIL=1; }
echo "cleanup OK"

echo
[ $FAIL -eq 0 ] && echo "RESULT: ALL PASS" || echo "RESULT: FAIL"
exit $FAIL
