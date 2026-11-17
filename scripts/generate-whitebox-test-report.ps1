$ErrorActionPreference = 'Stop'

$reportDirectory = Join-Path $PSScriptRoot '..\storage\app\test-reports'
$reportDirectory = [System.IO.Path]::GetFullPath($reportDirectory)
$junitPath = Join-Path $reportDirectory 'junit.xml'
$htmlPath = Join-Path $reportDirectory 'whitebox-test-report.html'

if (-not (Test-Path -LiteralPath $junitPath)) {
    throw "JUnit report not found: $junitPath. Run vendor/bin/phpunit --log-junit=storage/app/test-reports/junit.xml first."
}

[xml] $junit = Get-Content -LiteralPath $junitPath -Raw
$rootSuite = $junit.SelectSingleNode('/testsuites/testsuite')
$tests = [int] $rootSuite.GetAttribute('tests')
$assertions = [int] $rootSuite.GetAttribute('assertions')
$failures = [int] $rootSuite.GetAttribute('failures')
$errors = [int] $rootSuite.GetAttribute('errors')
$skipped = [int] $rootSuite.GetAttribute('skipped')
$passed = $tests - $failures - $errors - $skipped
$duration = [double] $rootSuite.GetAttribute('time')
$generatedAt = Get-Date -Format 'dd MMMM yyyy HH:mm:ss'
$phpunitScript = Join-Path $PSScriptRoot '..\vendor\bin\phpunit'
$phpunitOutput = (& php $phpunitScript --version | Select-Object -First 1)
$phpunitVersion = if ($phpunitOutput -match 'PHPUnit\s+([0-9.]+)') { $Matches[1] } else { 'versi tidak terdeteksi' }
$phpVersion = (& php -r 'echo PHP_VERSION;')
$status = if (($failures + $errors) -eq 0) { 'LULUS' } else { 'GAGAL' }
$statusClass = if (($failures + $errors) -eq 0) { 'pass' } else { 'fail' }

$rows = foreach ($case in $junit.SelectNodes('//testcase')) {
    $result = 'Lulus'
    $resultClass = 'pass'
    if ($case.SelectSingleNode('failure') -or $case.SelectSingleNode('error')) {
        $result = 'Gagal'
        $resultClass = 'fail'
    } elseif ($case.SelectSingleNode('skipped')) {
        $result = 'Dilewati'
        $resultClass = 'skip'
    }

    $testName = [System.Net.WebUtility]::HtmlEncode($case.GetAttribute('name'))
    $className = [System.Net.WebUtility]::HtmlEncode($case.GetAttribute('classname'))
    $testDuration = ([double] $case.GetAttribute('time')).ToString('0.00')
    "<tr><td>$className</td><td>$testName</td><td><span class='badge $resultClass'>$result</span></td><td class='number'>$testDuration dtk</td></tr>"
}

$suiteRows = foreach ($suite in $junit.SelectNodes('//testsuite[testcase]')) {
    $suiteName = [System.Net.WebUtility]::HtmlEncode($suite.GetAttribute('name'))
    $suiteTests = [int] $suite.GetAttribute('tests')
    $suiteAssertions = [int] $suite.GetAttribute('assertions')
    $suiteFailures = [int] $suite.GetAttribute('failures')
    $suiteErrors = [int] $suite.GetAttribute('errors')
    $suiteSkipped = [int] $suite.GetAttribute('skipped')
    $suitePassed = $suiteTests - $suiteFailures - $suiteErrors - $suiteSkipped
    "<tr><td>$suiteName</td><td class='number'>$suiteTests</td><td class='number'>$suitePassed</td><td class='number'>$suiteAssertions</td><td class='number'>$($suiteFailures + $suiteErrors)</td></tr>"
}

$html = @"
<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Laporan White-Box Testing &#8212; KawalBelajar</title>
  <style>
    :root { color-scheme: light; --ink:#142536; --muted:#5b6b7b; --line:#d8e1e8; --teal:#087f8c; --mint:#e8f8f5; --green:#087443; --red:#a11b2b; }
    * { box-sizing:border-box; } body { margin:0; background:#f3f7fa; color:var(--ink); font:15px/1.5 Inter,Segoe UI,Arial,sans-serif; }
    main { max-width:1160px; margin:36px auto; padding:0 22px; } .hero,.panel { background:#fff; border:1px solid var(--line); border-radius:18px; box-shadow:0 6px 24px #19364d0b; }
    .hero { padding:30px; border-top:6px solid var(--teal); } h1 { margin:0 0 8px; font-size:30px; } h2 { margin:0 0 18px; font-size:20px; }
    p { color:var(--muted); margin:5px 0; } .status { display:inline-block; padding:6px 12px; border-radius:999px; font-weight:800; letter-spacing:.04em; }
    .pass { color:var(--green); background:#e8f8ef; } .fail { color:var(--red); background:#fff0f1; } .skip { color:#705200; background:#fff5d8; }
    .cards { display:grid; grid-template-columns:repeat(4,1fr); gap:14px; margin:18px 0; } .card { background:#fff; border:1px solid var(--line); border-radius:14px; padding:17px; }
    .card span { display:block; color:var(--muted); font-size:12px; font-weight:700; text-transform:uppercase; letter-spacing:.05em; } .card strong { display:block; margin-top:5px; font-size:25px; }
    .panel { margin-top:18px; padding:24px; overflow:hidden; } .table-wrap { overflow:auto; } table { width:100%; border-collapse:collapse; min-width:700px; } th,td { text-align:left; padding:11px 12px; border-bottom:1px solid var(--line); vertical-align:top; }
    th { background:#f1f7f9; font-size:12px; text-transform:uppercase; letter-spacing:.04em; } .number { text-align:right; white-space:nowrap; font-variant-numeric:tabular-nums; }
    .badge { display:inline-block; border-radius:999px; padding:3px 9px; font-size:12px; font-weight:800; } footer { margin:18px 2px; color:var(--muted); font-size:12px; }
    @media(max-width:700px) { main{margin:18px auto;padding:0 12px}.hero,.panel{padding:19px}.cards{grid-template-columns:repeat(2,1fr)}h1{font-size:24px} }
  </style>
</head>
<body>
<main>
  <header class="hero">
    <div class="status $statusClass">$status</div>
    <h1>Laporan White-Box Testing</h1>
    <p><strong>KawalBelajar</strong> &#183; PHPUnit $phpunitVersion &#183; PHP $phpVersion</p>
    <p>Dibuat $generatedAt &#183; Seluruh suite Unit dan Feature</p>
  </header>
  <section class="cards" aria-label="Ringkasan hasil pengujian">
    <div class="card"><span>Test</span><strong>$tests</strong></div>
    <div class="card"><span>Lulus</span><strong>$passed</strong></div>
    <div class="card"><span>Assertion</span><strong>$assertions</strong></div>
    <div class="card"><span>Gagal / Error / Dilewati</span><strong>$failures / $errors / $skipped</strong></div>
  </section>
  <section class="panel">
    <h2>Ringkasan per kelas test</h2>
    <div class="table-wrap"><table><thead><tr><th>Kelas</th><th class="number">Test</th><th class="number">Lulus</th><th class="number">Assertion</th><th class="number">Gagal/Error</th></tr></thead><tbody>
    $($suiteRows -join "`n    ")
    </tbody></table></div>
  </section>
  <section class="panel">
    <h2>Rincian test</h2>
    <div class="table-wrap"><table><thead><tr><th>Kelas</th><th>Test case</th><th>Status</th><th class="number">Durasi</th></tr></thead><tbody>
    $($rows -join "`n    ")
    </tbody></table></div>
  </section>
  <footer>Sumber data: PHPUnit JUnit XML. Durasi keseluruhan: $([Math]::Round($duration, 2)) detik.</footer>
</main>
</body>
</html>
"@

$encoding = [System.Text.UTF8Encoding]::new($false)
[System.IO.File]::WriteAllText($htmlPath, $html, $encoding)
Write-Output "HTML report generated: $htmlPath"
