<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Process;
use Illuminate\View\View;

class WebSettingController extends Controller
{
    private function log(string $t, string $cmd, string $out, int $code): void
    {
        $dir = storage_path('logs');
        if (! is_dir($dir)) mkdir($dir, 0755, true);
        $b = '['.now()->format('Y-m-d H:i:s')."] {$t} (exit: {$code})\n$ {$cmd}\n{$out}\n".str_repeat('-', 70)."\n";
        file_put_contents($dir.'/web-terminal.log', $b, FILE_APPEND);
    }

    private function tail(int $n = 400): string
    {
        $f = storage_path('logs/web-terminal.log');
        if (! is_file($f)) return '';
        $a = file($f, FILE_IGNORE_NEW_LINES);

        return $a === false ? '' : implode("\n", array_slice($a, -$n));
    }

    private function githubToken(): string
    {
        $t = trim((string) env('GITHUB_TOKEN', ''));
        if ($t !== '') return trim($t, '"\'');
        $p = base_path('.env');
        if (is_file($p) && preg_match('/^GITHUB_TOKEN=(.*)$/m', (string) file_get_contents($p), $m)) {
            return trim(trim($m[1]), '"\'');
        }

        return '';
    }

    private function git(string $args, ?string $token = null): array
    {
        // ponytail: token via Basic x-access-token, upgrade ke queue bila repo besar
        $mask = $token ? 'git -c credential.helper= -c http.extraHeader="Authorization: Basic ***" '.$args : 'git '.$args;
        $real = $mask;
        if ($token) {
            $basic = base64_encode('x-access-token:'.$token);
            $real = 'git -c credential.helper= -c http.extraHeader="Authorization: Basic '.$basic.'" '.$args;
        }
        $r = Process::path(base_path())->env(['GIT_TERMINAL_PROMPT' => '0', 'GIT_ASKPASS' => 'echo'])->timeout(120)->run($real);
        $out = trim(($r->output() ?: '').($r->errorOutput() ? "\n".$r->errorOutput() : ''));
        if ($token && $token !== '') $out = str_replace([$token, $basic ?? ''], '***', $out);

        return [$r->successful() ? 0 : ($r->exitCode() ?? 1), $out === '' ? '(tanpa output)' : $out, $mask];
    }

    private static function setEnv(string $k, string $v): void
    {
        $p = base_path('.env');
        $c = is_file($p) ? file_get_contents($p) : '';
        $line = $k.'="'.$v.'"';
        $c = preg_match('/^'.$k.'=.*/m', $c) ? preg_replace('/^'.$k.'=.*/m', $line, $c) : rtrim($c)."\n".$line."\n";
        file_put_contents($p, $c);
    }

    public function index(): View
    {
        [$cb, $branch] = $this->git('status --short --branch');
        [$cl, $commits] = $this->git('log --oneline -5');
        $remote = trim((string) shell_exec('git -C '.escapeshellarg(base_path()).' remote get-url origin 2>&1'));
        $token = $this->githubToken();
        $drv = config('database.default');
        $backs = collect(glob(storage_path('app/backups/*.sql') ?: []))
            ->map(fn ($p) => ['name' => basename($p), 'size' => round(filesize($p) / 1024, 1).' KB', 'time' => date('Y-m-d H:i', filemtime($p))])
            ->sortByDesc('name')->take(5)->values();

        return view('web-settings.index', [
            'branch' => $cb === 0 ? $branch : '-',
            'commits' => $cl === 0 ? $commits : '-',
            'remote' => $remote ?: '-',
            'tokenMasked' => $token !== '' && strlen($token) > 14 ? substr($token, 0, 10).'***'.substr($token, -4) : ($token !== '' ? '***' : '-'),
            'hasToken' => $token !== '',
            'db' => $drv,
            'dbName' => config('database.connections.'.$drv.'.database'),
            'log' => $this->tail(),
            'backups' => $backs,
        ]);
    }

    public function updateToken(Request $r)
    {
        $d = $r->validate(['github_token' => ['required', 'string', 'regex:/^(github_pat_|ghp_)[A-Za-z0-9_]{10,}$/']],
            ['github_token.regex' => 'Format token harus github_pat_xxx atau ghp_xxx.']);
        self::setEnv('GITHUB_TOKEN', $d['github_token']);
        $this->log('Simpan token', 'PUT .env GITHUB_TOKEN=***', 'Tersimpan ('.substr($d['github_token'], 0, 10).'***).', 0);

        return back()->with('ok', 'Token GitHub tersimpan.');
    }

    public function pull()
    {
        $t = $this->githubToken();
        if ($t === '') {
            $msg = 'Token GitHub belum tersimpan. Simpan token dulu (github_pat_xxx / ghp_xxx dengan akses Contents read).';
            $this->log('Update GitHub', 'git pull --rebase --autostash', $msg, 1);

            return back()->with('web', $msg);
        }
        [$code, $out, $mask] = $this->git('pull --rebase --autostash', $t);
        if ($code !== 0 && preg_match('/could not read Username|authentication failed|Invalid username or token|403/i', $out)) {
            $out .= "\nToken ditolak/kadaluarsa. Buat token baru (classic: repo read) lalu Simpan Token ulang.";
        }
        $this->log('Update GitHub', $mask, $out, $code);

        return back()->with($code === 0 ? 'ok' : 'web', $code === 0 ? 'Update GitHub selesai.' : 'Update gagal: '.$out);
    }

    public function symlink()
    {
        $link = public_path('storage');
        if (is_link($link) || is_file($link)) unlink($link);
        elseif (is_dir($link)) {
            $this->log('Symlink storage', 'php artisan storage:link', 'Gagal: public/storage folder biasa, hapus manual dulu.', 1);

            return back()->withErrors(['web' => 'public/storage folder biasa, hapus manual dulu.']);
        }
        $code = Artisan::call('storage:link');
        $out = trim(Artisan::output()) ?: '(tanpa output)';
        $this->log('Symlink storage', 'php artisan storage:link', $out, $code);

        return back()->with($code === 0 ? 'ok' : 'web', $code === 0 ? 'Symlink storage OK.' : 'Symlink gagal: '.$out);
    }

    public function migrate()
    {
        $code = Artisan::call('migrate', ['--force' => true]);
        $out = trim(Artisan::output()) ?: '(tanpa output)';
        $this->log('Migrasi table', 'php artisan migrate --force', $out, $code);

        return back()->with('ok', 'Migrasi selesai, cek log terminal.');
    }

    public function clear()
    {
        $code = Artisan::call('optimize:clear');
        $out = trim(Artisan::output()) ?: '(tanpa output)';
        $this->log('Clear config & cache', 'php artisan optimize:clear', $out, $code);

        return back()->with('ok', 'Config & cache dibersihkan.');
    }

    public function export()
    {
        $drv = config('database.default');
        $dir = storage_path('app/backups');
        if (! is_dir($dir)) mkdir($dir, 0755, true);
        $file = $dir.'/backup-'.now()->format('Ymd-His').'.sql';
        if (! in_array($drv, ['mysql', 'mariadb'])) {
            copy(config("database.connections.{$drv}.database"), $file);
            $this->log('Export database', 'copy sqlite -> '.basename($file), 'OK '.round(filesize($file) / 1024, 1).' KB', 0);

            return response()->download($file);
        }
        $c = config("database.connections.{$drv}");
        $bin = PHP_OS_FAMILY === 'Windows' && is_file('C:\\xampp\\mysql\\bin\\mysqldump.exe') ? '"C:\\xampp\\mysql\\bin\\mysqldump.exe"' : 'mysqldump';
        $pass = $c['password'] ? ' --password='.escapeshellarg((string) $c['password']) : '';
        $cmd = "{$bin} -h ".escapeshellarg($c['host']).' -P '.escapeshellarg($c['port']).' -u '.escapeshellarg($c['username']).$pass.' '.escapeshellarg($c['database']).' > '.escapeshellarg($file).' 2>&1';
        $code = Process::timeout(300)->run($cmd)->successful() && is_file($file) && filesize($file) > 0 ? 0 : 1;
        $this->log('Export database', str_replace((string) $c['password'], '***', $cmd), $code === 0 ? 'OK '.round(filesize($file) / 1024, 1).' KB' : 'dump gagal', $code);
        if ($code !== 0) return back()->withErrors(['web' => 'Export gagal, cek log terminal.']);

        return response()->download($file);
    }

    public function import(Request $r)
    {
        $d = $r->validate(['sql_file' => 'required|file|mimes:sql,txt,dump|max:102400']);
        $full = storage_path('app/'.$d['sql_file']->storeAs('backups', 'import-'.now()->format('Ymd-His').'.sql'));
        $drv = config('database.default');
        if (! in_array($drv, ['mysql', 'mariadb'])) {
            DB::unprepared(file_get_contents($full));
            $this->log('Import database', 'sqlite import '.basename($full), 'OK', 0);

            return back()->with('ok', 'Import database selesai.');
        }
        $c = config("database.connections.{$drv}");
        $bin = PHP_OS_FAMILY === 'Windows' && is_file('C:\\xampp\\mysql\\bin\\mysql.exe') ? '"C:\\xampp\\mysql\\bin\\mysql.exe"' : 'mysql';
        $pass = $c['password'] ? ' --password='.escapeshellarg((string) $c['password']) : '';
        $cmd = "{$bin} -h ".escapeshellarg($c['host']).' -P '.escapeshellarg($c['port']).' -u '.escapeshellarg($c['username']).$pass.' '.escapeshellarg($c['database']).' < '.escapeshellarg($full).' 2>&1';
        $res = Process::timeout(300)->run($cmd);
        $code = $res->successful() ? 0 : ($res->exitCode() ?? 1);
        $out = trim(($res->output() ?: '')."\n".($res->errorOutput() ?: '')) ?: '(tanpa output)';
        $this->log('Import database', 'mysql < '.basename($full).' (password ***)', $out, $code);

        return back()->with($code === 0 ? 'ok' : 'web', $code === 0 ? 'Import selesai.' : 'Import gagal: '.$out);
    }

    public function clearLog()
    {
        file_put_contents(storage_path('logs/web-terminal.log'), '');

        return back()->with('ok', 'Log terminal dibersihkan.');
    }
}
